<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlantController extends Controller
{
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

        $query = Plant::withCount('users')
            ->when($statusTab === 'active', fn ($q) => $q->where('is_active', true))
            ->when($statusTab === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $v = $request->q;
                $w->where('name', 'like', "%$v%")
                  ->orWhere('code', 'like', "%$v%")
                  ->orWhere('location', 'like', "%$v%")
                  ->orWhere('contact_email', 'like', "%$v%");
            }));

        $totalCount = Plant::count();
        $activeCount = Plant::where('is_active', true)->count();
        $inactiveCount = Plant::where('is_active', false)->count();

        $plants = $query->orderBy('code')->paginate(10)->withQueryString();

        $allPlantSuggestions = Plant::select('id', 'name', 'code', 'location')
            ->orderBy('code')
            ->get();

        return view('plants.index', [
            'plants' => $plants,
            'tabCounts' => [
                'all' => $totalCount,
                'active' => $activeCount,
                'inactive' => $inactiveCount,
            ],
            'currentTab' => $statusTab,
            'allPlantSuggestions' => $allPlantSuggestions,
        ]);
    }

    public function create()
    {
        return view('plants.form', ['plant' => new Plant(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules(), $this->validationMessages());

        $validated['is_active'] = $request->boolean('is_active');
        $validated['code'] = strtoupper(trim($validated['code']));
        $plant = Plant::create($validated);

        \App\Models\AuditLog::record('create', 'plants', "Registered new plant facility: {$plant->code} — {$plant->name}", [
            'code' => $plant->code,
            'location' => $plant->location,
        ]);

        return redirect()->route('plants.index')->with('success', 'Plant facility registered successfully.');
    }

    public function edit(Plant $plant)
    {
        return view('plants.form', ['plant' => $plant]);
    }

    public function update(Request $request, Plant $plant)
    {
        $validated = $request->validate($this->validationRules($plant), $this->validationMessages());

        $validated['is_active'] = $request->boolean('is_active');
        $validated['code'] = strtoupper(trim($validated['code']));
        $plant->update($validated);

        \App\Models\AuditLog::record('update', 'plants', "Updated plant facility details: {$plant->code}", [
            'changes' => $plant->getChanges(),
        ]);

        return redirect()->route('plants.index')->with('success', 'Plant facility updated successfully.');
    }

    /**
     * Custom validation rules enforcing required special characters, min/max limits, and disallowing unsafe characters.
     */
    private function validationRules(?Plant $plant = null): array
    {
        return [
            'code' => [
                'required',
                'string',
                'min:2',
                'max:20',
                'regex:~^[A-Za-z0-9\-_]+$~',
                $plant ? Rule::unique('plants', 'code')->ignore($plant->id) : 'unique:plants,code',
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u',
            ],
            'location' => [
                'nullable',
                'string',
                'min:3',
                'max:200',
                'regex:~^[\p{L}\p{N}\s,\.\-/#()&\'’]+$~u',
            ],
            'contact_email' => [
                'nullable',
                'string',
                'min:5',
                'max:100',
                'email:rfc',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'contact_phone' => [
                'nullable',
                'regex:/^[6-9][0-9]{9}$/',
            ],
            'description' => [
                'nullable',
                'string',
                'min:5',
                'max:1000',
                'regex:~^[\p{L}\p{N}\s\.\,\-\–\—\_\&\/\(\)\'\"\!\?\:\;\%\r\n]+$~u',
            ],
            'is_active' => 'boolean',
        ];
    }

    /**
     * User-friendly custom validation error messages.
     */
    private function validationMessages(): array
    {
        return [
            'code.required' => 'Plant Code is required.',
            'code.min' => 'Plant Code must be at least :min characters.',
            'code.max' => 'Plant Code cannot exceed :max characters.',
            'code.regex' => 'Plant Code may only contain letters, numbers, hyphens (-), and underscores (_). Spaces and other special characters are not allowed.',
            'code.unique' => 'This Plant Code has already been registered.',

            'name.required' => 'Facility / Division Name is required.',
            'name.min' => 'Facility / Division Name must be at least :min characters.',
            'name.max' => 'Facility / Division Name cannot exceed :max characters.',
            'name.regex' => 'Facility / Division Name may only contain letters, numbers, spaces, and allowed symbols (&, -, —, _, /, ., ,, (), \'). Other special characters are not allowed.',

            'location.min' => 'Factory Location must be at least :min characters if provided.',
            'location.max' => 'Factory Location cannot exceed :max characters.',
            'location.regex' => 'Factory Location may only contain letters, numbers, spaces, and address symbols (,, ., -, /, #, (), &, \').',

            'contact_email.email' => 'Please provide a valid official email address.',
            'contact_email.regex' => 'Please provide a valid official email address with domain (e.g. plant@shibaura-machine.co.in).',
            'contact_email.min' => 'Operations Contact Email must be at least :min characters.',
            'contact_email.max' => 'Operations Contact Email cannot exceed :max characters.',

            'contact_phone.regex' => 'Desk / Helpdesk Phone must be a valid 10-digit number starting with 6, 7, 8, or 9.',

            'description.min' => 'Facility Description must be at least :min characters if provided.',
            'description.max' => 'Facility Description cannot exceed :max characters.',
            'description.regex' => 'Facility Description contains disallowed special characters (code/script tags like < > { } [ ] $ ^ * = \\ | are not permitted).',
        ];
    }

    public function destroy(Plant $plant)
    {
        if ($plant->users()->exists()) {
            return back()->with('error', 'Cannot delete plant "'.$plant->name.'" because users are currently assigned to it.');
        }

        $code = $plant->code;
        $name = $plant->name;
        $plant->delete();

        \App\Models\AuditLog::record('delete', 'plants', "Deleted plant facility: {$code} — {$name}");

        return redirect()->route('plants.index')->with('success', 'Plant removed successfully.');
    }

    public function toggle(Plant $plant)
    {
        $plant->update(['is_active' => ! $plant->is_active]);
        $status = $plant->is_active ? 'activated' : 'deactivated';

        \App\Models\AuditLog::record('toggle', 'plants', "Toggled plant status: {$plant->code} is now {$status}");

        return back()->with('success', "Plant {$plant->code} has been {$status}.");
    }
}
