<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShiftController extends Controller
{
    /**
     * Display a listing of factory shifts.
     */
    public function index(Request $request)
    {
        $statusTab = $request->input('tab', 'all');

        if ($request->filled('q')) {
            $request->validate([
                'q' => ['nullable', 'string', 'min:1', 'max:60', 'regex:~^[\p{L}\p{N}\s\-_.,/@&()]*$~u'],
            ], [
                'q.regex' => 'Search contains unsupported special characters. Only letters, numbers, spaces, and safe symbols (- _ . , / @ & ()) are permitted.',
                'q.max' => 'Search query cannot exceed 60 characters.',
            ]);
        }

        $query = Shift::query()
            ->when($statusTab === 'active', fn ($q) => $q->where('is_active', true))
            ->when($statusTab === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $v = $request->q;
                $w->where('name', 'like', "%$v%")
                  ->orWhere('code', 'like', "%$v%")
                  ->orWhere('description', 'like', "%$v%");
            }));

        $totalCount = Shift::count();
        $activeCount = Shift::where('is_active', true)->count();
        $inactiveCount = Shift::where('is_active', false)->count();

        $shifts = $query->orderBy('start_time')->orderBy('code')->paginate(10)->withQueryString();

        $allShiftSuggestions = Shift::select('id', 'name', 'code', 'start_time', 'end_time', 'description')
            ->orderBy('start_time')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'code' => $s->code,
                    'location' => $s->formatted_24h_range . ' (' . $s->duration . ')',
                ];
            });

        return view('shifts.index', [
            'shifts' => $shifts,
            'tabCounts' => [
                'all' => $totalCount,
                'active' => $activeCount,
                'inactive' => $inactiveCount,
            ],
            'currentTab' => $statusTab,
            'allShiftSuggestions' => $allShiftSuggestions,
        ]);
    }

    /**
     * Show form for creating a new shift.
     */
    public function create()
    {
        $shift = new Shift([
            'is_active' => true,
            'start_time' => '06:00',
            'end_time' => '14:00',
        ]);

        return view('shifts.form', compact('shift'));
    }

    /**
     * Store a freshly created shift.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules(), $this->validationMessages());

        $validated['is_active'] = $request->boolean('is_active');
        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['start_time'] = $this->normalizeTime($validated['start_time']);
        $validated['end_time'] = $this->normalizeTime($validated['end_time']);

        $shift = Shift::create($validated);

        AuditLog::record('create', 'shifts', "Registered factory shift schedule: {$shift->code} — {$shift->name} ({$shift->formatted_24h_range})", [
            'code' => $shift->code,
            'name' => $shift->name,
            'start_time' => $shift->start_time,
            'end_time' => $shift->end_time,
            'duration' => $shift->duration,
        ]);

        return redirect()->route('shifts.index')->with('success', "Shift '{$shift->name}' ({$shift->code}) created successfully.");
    }

    /**
     * Show form for editing an existing shift.
     */
    public function edit(Shift $shift)
    {
        return view('shifts.form', compact('shift'));
    }

    /**
     * Update existing shift details.
     */
    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate($this->validationRules($shift), $this->validationMessages());

        $validated['is_active'] = $request->boolean('is_active');
        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['start_time'] = $this->normalizeTime($validated['start_time']);
        $validated['end_time'] = $this->normalizeTime($validated['end_time']);

        $shift->update($validated);

        AuditLog::record('update', 'shifts', "Updated factory shift details: {$shift->code} — {$shift->name}", [
            'changes' => $shift->getChanges(),
        ]);

        return redirect()->route('shifts.index')->with('success', "Shift '{$shift->name}' updated successfully.");
    }

    /**
     * Remove a shift from the system.
     */
    public function destroy(Shift $shift)
    {
        $code = $shift->code;
        $name = $shift->name;
        $shift->delete();

        AuditLog::record('delete', 'shifts', "Deleted factory shift: {$code} — {$name}");

        return redirect()->route('shifts.index')->with('success', "Shift '{$name}' removed successfully.");
    }

    /**
     * Toggle shift active status.
     */
    public function toggle(Shift $shift)
    {
        $shift->update(['is_active' => ! $shift->is_active]);
        $status = $shift->is_active ? 'activated' : 'deactivated';

        AuditLog::record('toggle', 'shifts', "Toggled shift status: {$shift->code} is now {$status}");

        return back()->with('success', "Shift {$shift->code} has been {$status}.");
    }

    /**
     * Validation rules for shifts.
     */
    private function validationRules(?Shift $shift = null): array
    {
        return [
            'code' => [
                'required',
                'string',
                'min:2',
                'max:30',
                'regex:~^[A-Za-z0-9\-_]+$~',
                $shift ? Rule::unique('shifts', 'code')->ignore($shift->id) : 'unique:shifts,code',
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u',
            ],
            'start_time' => [
                'required',
                'regex:/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/',
            ],
            'end_time' => [
                'required',
                'regex:/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/',
            ],
            'description' => [
                'nullable',
                'string',
                'min:3',
                'max:1000',
                'regex:~^[\p{L}\p{N}\s\.\,\-\–\—\_\&\/\(\)\'\"\!\?\:\;\%\r\n]+$~u',
            ],
            'is_active' => 'boolean',
        ];
    }

    /**
     * Custom validation error messages.
     */
    private function validationMessages(): array
    {
        return [
            'code.required' => 'Shift Code is required (e.g. SHIFT-A).',
            'code.min' => 'Shift Code must be at least :min characters.',
            'code.max' => 'Shift Code cannot exceed :max characters.',
            'code.regex' => 'Shift Code may only contain letters, numbers, hyphens (-), and underscores (_).',
            'code.unique' => 'This Shift Code has already been registered.',

            'name.required' => 'Shift Name is required (e.g. Shift A).',
            'name.min' => 'Shift Name must be at least :min characters.',
            'name.max' => 'Shift Name cannot exceed :max characters.',
            'name.regex' => 'Shift Name contains unsupported characters.',

            'start_time.required' => 'Shift Start Time is required.',
            'start_time.regex' => 'Start Time must be in valid 24-hour format (HH:MM).',

            'end_time.required' => 'Shift End Time is required.',
            'end_time.regex' => 'End Time must be in valid 24-hour format (HH:MM).',

            'description.min' => 'Description must be at least :min characters if provided.',
            'description.max' => 'Description cannot exceed :max characters.',
            'description.regex' => 'Description contains disallowed characters.',
        ];
    }

    /**
     * Standardize time to HH:MM:00.
     */
    private function normalizeTime(string $time): string
    {
        $time = trim($time);
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }
        return $time;
    }
}
