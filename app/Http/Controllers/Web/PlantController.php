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

        return view('plants.index', [
            'plants' => $plants,
            'tabCounts' => [
                'all' => $totalCount,
                'active' => $activeCount,
                'inactive' => $inactiveCount,
            ],
            'currentTab' => $statusTab,
        ]);
    }

    public function create()
    {
        return view('plants.form', ['plant' => new Plant(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:plants,code',
            'location' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('plants', 'code')->ignore($plant->id)],
            'location' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $plant->update($validated);

        \App\Models\AuditLog::record('update', 'plants', "Updated plant facility details: {$plant->code}", [
            'changes' => $plant->getChanges(),
        ]);

        return redirect()->route('plants.index')->with('success', 'Plant facility updated successfully.');
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
