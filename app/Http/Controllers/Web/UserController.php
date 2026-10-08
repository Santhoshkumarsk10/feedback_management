<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Plant;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\AuditLog;

/**
 * superadmin -> manages superadmin/admin/organizer and all custom roles
 * admin      -> manages organizers only
 */
class UserController extends Controller
{
    private function manageableSlugs(): array
    {
        return auth()->user()->role === 'superadmin'
            ? Role::pluck('slug')->all()
            : ['supervisor', 'staff', 'organizer'];
    }

    private function authorizeTarget(User $user): void
    {
        abort_unless(in_array($user->role, $this->manageableSlugs(), true), 403);
    }

    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’@+]+$~u'],
            'role' => 'nullable|string|max:50',
            'plant_id' => 'nullable|integer|exists:plants,id',
            'status' => 'nullable|in:active,inactive',
        ]);

        $manageableSlugs = $this->manageableSlugs();

        // Calculate counts for quick filter tabs
        $roleCounts = User::whereIn('role', $manageableSlugs)
            ->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->all();
        $totalUsers = array_sum($roleCounts);

        $users = User::with(['plant', 'roleModel', 'assignedShift'])
            ->whereIn('role', $manageableSlugs)
            ->when($request->role, fn ($q, $v) => $q->where('role', $v))
            ->when($request->plant_id, fn ($q, $v) => $q->where('plant_id', $v))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active' ? 1 : 0))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%$v%")
                ->orWhere('email', 'like', "%$v%")
                ->orWhere('mobile', 'like', "%$v%")
                ->orWhere('department', 'like', "%$v%")))
            ->orderBy('role')->orderBy('name')->paginate(10)->withQueryString();

        $plants = Plant::orderBy('code')->get();
        $availableRoles = Role::whereIn('slug', $manageableSlugs)->get();

        $userSuggestions = User::whereIn('role', $manageableSlugs)
            ->select('id', 'name', 'email', 'role', 'department')
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return [
                    'code' => strtoupper(substr($u->role, 0, 4)),
                    'name' => $u->name,
                    'sub' => $u->email . ($u->department ? ' • ' . $u->department : ''),
                    'value' => $u->name,
                ];
            });

        return view('users.index', [
            'users' => $users,
            'roles' => $manageableSlugs,
            'roleModels' => $availableRoles,
            'plants' => $plants,
            'roleCounts' => $roleCounts,
            'totalUsers' => $totalUsers,
            'userSuggestions' => $userSuggestions,
        ]);
    }

    public function create()
    {
        $manageableSlugs = $this->manageableSlugs();
        $roles = Role::whereIn('slug', $manageableSlugs)->where('is_active', true)->orderBy('name')->get();
        $plants = Plant::where('is_active', true)->orderBy('code')->get();
        $shifts = Shift::active()->orderBy('start_time')->get();

        $defaultRole = Role::where('slug', 'organizer')->first();

        return view('users.form', [
            'user' => new User(['is_active' => true, 'role' => 'organizer', 'role_id' => $defaultRole?->id]),
            'roles' => $roles,
            'plants' => $plants,
            'shifts' => $shifts,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = User::create($data);

        AuditLog::record('create', 'users', "Registered user account: {$user->name} ({$user->role})", [
            'user_id' => $user->id,
            'plant_id' => $user->plant_id,
            'role' => $user->role,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorizeTarget($user);
        $manageableSlugs = $this->manageableSlugs();

        $roles = Role::whereIn('slug', $manageableSlugs)
            ->where(fn($q) => $q->where('is_active', true)->orWhere('id', $user->role_id))
            ->orderBy('name')
            ->get();

        $plants = Plant::where(fn($q) => $q->where('is_active', true)->orWhere('id', $user->plant_id))
            ->orderBy('code')
            ->get();
        $shifts = Shift::active()->orderBy('start_time')->get();

        return view('users.form', [
            'user' => $user,
            'roles' => $roles,
            'plants' => $plants,
            'shifts' => $shifts,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeTarget($user);
        $data = $this->validated($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        }
        // never let someone lock themselves out
        if ($user->id === auth()->id()) {
            unset($data['role'], $data['role_id'], $data['is_active']);
        }
        $user->update($data);

            AuditLog::record('update', 'users', "Updated user account profile: {$user->name}", [
            'user_id' => $user->id,
            'changes' => $user->getChanges(),
        ]);

        return redirect()->route('users.index')->with('success', 'User profile updated.');
    }

    public function toggle(User $user)
    {
        $this->authorizeTarget($user);
        abort_if($user->id === auth()->id(), 422, 'You cannot deactivate yourself.');
        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';

        AuditLog::record('toggle', 'users', "Toggled user active state: {$user->name} is now {$status}");

        return back()->with('success', 'Status updated.');
    }

    public function destroy(User $user)
    {
        $this->authorizeTarget($user);
        abort_if($user->id === auth()->id(), 422, 'You cannot delete yourself.');
        $name = $user->name;
        $role = $user->role;
        $user->delete();

        AuditLog::record('delete', 'users', "Deleted user account: {$name} ({$role})");

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:70', 'regex:~^[\p{L}\s\.\-’\']+$~u'],
            'email' => ['nullable', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'max:100', 'required_without:mobile', Rule::unique('users', 'email')->ignore($user?->id)],
            'mobile' => ['nullable', 'regex:/^[6-9][0-9]{9}$/', 'required_without:email', Rule::unique('users', 'mobile')->ignore($user?->id)],
            'role_id' => 'required|exists:roles,id',
            'plant_id' => 'nullable|exists:plants,id',
            'department' => ['nullable', 'string', 'min:2', 'max:60', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'shift_id' => 'nullable|exists:shifts,id',
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'max:60'],
        ], [
            'name.required' => 'Full Name is required.',
            'name.min' => 'Full Name must be at least :min characters.',
            'name.max' => 'Full Name cannot exceed :max characters.',
            'name.regex' => 'Full Name may only contain letters, spaces, hyphens, and dots.',

            'email.email' => 'Please provide a valid official email address.',
            'email.regex' => 'Please provide a valid official email address with domain (e.g. name@company.com).',
            'email.required_without' => 'Either Email Address or Mobile Number is required.',

            'mobile.regex' => 'Mobile number must be a valid 10-digit number starting with 6, 7, 8, or 9.',
            'mobile.required_without' => 'Either Mobile Number or Email Address is required.',

            'department.regex' => 'Department may only contain letters, numbers, spaces, and allowed symbols (&, -, /, .).',
            'department.min' => 'Department must be at least :min characters.',
            'department.max' => 'Department cannot exceed :max characters.',

            'password.min' => 'Password must be at least :min characters.',
            'password.max' => 'Password cannot exceed :max characters.',
        ]);

        $roleObj = Role::find($data['role_id']);
        $data['role'] = in_array($roleObj?->slug, ['superadmin', 'admin', 'supervisor', 'staff', 'organizer'], true) 
            ? $roleObj->slug 
            : ($roleObj?->name ?? 'staff');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
