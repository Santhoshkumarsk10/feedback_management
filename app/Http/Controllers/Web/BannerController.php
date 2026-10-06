<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    /**
     * Display a listing of CMS banners.
     */
    public function index(Request $request)
    {
        $statusTab = $request->input('tab', 'all');
        $targetFilter = $request->input('target', 'all');

        $query = Banner::query()
            ->when($statusTab === 'active', fn ($q) => $q->where('is_active', true))
            ->when($statusTab === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($targetFilter !== 'all' && in_array($targetFilter, ['mobile', 'tablet', 'web']), fn ($q) => $q->where('target', $targetFilter))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $v = $request->q;
                $w->where('title', 'like', "%$v%")
                  ->orWhere('subtitle', 'like', "%$v%")
                  ->orWhere('target', 'like', "%$v%")
                  ->orWhere('link_url', 'like', "%$v%");
            }));

        $totalCount = Banner::count();
        $activeCount = Banner::where('is_active', true)->count();
        $inactiveCount = Banner::where('is_active', false)->count();

        $banners = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->paginate(10)->withQueryString();

        // Search suggestions for search-suggest component
        $allBannerSuggestions = Banner::select('id', 'title', 'subtitle', 'target')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->title,
                    'code' => strtoupper($b->target),
                    'location' => $b->subtitle ?? 'General Banner',
                ];
            });

        return view('banners.index', [
            'banners' => $banners,
            'tabCounts' => [
                'all' => $totalCount,
                'active' => $activeCount,
                'inactive' => $inactiveCount,
            ],
            'currentTab' => $statusTab,
            'targetFilter' => $targetFilter,
            'allBannerSuggestions' => $allBannerSuggestions,
        ]);
    }

    /**
     * Show form for creating a new banner.
     */
    public function create()
    {
        return view('banners.form', [
            'banner' => new Banner([
                'is_active' => true,
                'sort_order' => (Banner::max('sort_order') ?? 0) + 1,
                'target' => 'all',
            ]),
        ]);
    }

    /**
     * Store a newly created banner.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,svg,gif|max:5120',
            'target' => ['required', Rule::in(['all', 'mobile', 'tablet', 'web'])],
            'link_url' => 'nullable|url|max:500',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // Handle file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'banner_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/banners');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $validated['image_path'] = 'uploads/banners/' . $filename;
        }

        unset($validated['image']);

        $banner = Banner::create($validated);

        AuditLog::record(
            'create',
            'banners',
            "Uploaded & created banner: {$banner->title} (Target: {$banner->target})",
            ['target' => $banner->target, 'sort_order' => $banner->sort_order]
        );

        return redirect()->route('banners.index')->with('success', 'Banner created and published successfully.');
    }

    /**
     * Show form for editing an existing banner.
     */
    public function edit(Banner $banner)
    {
        return view('banners.form', ['banner' => $banner]);
    }

    /**
     * Update existing banner.
     */
    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg,gif|max:5120',
            'target' => ['required', Rule::in(['all', 'mobile', 'tablet', 'web'])],
            'link_url' => 'nullable|url|max:500',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'banner_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/banners');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Remove old uploaded image if it's in uploads/banners
            if ($banner->image_path && str_starts_with($banner->image_path, 'uploads/banners/') && file_exists(public_path($banner->image_path))) {
                @unlink(public_path($banner->image_path));
            }

            $file->move($destinationPath, $filename);
            $validated['image_path'] = 'uploads/banners/' . $filename;
        }

        unset($validated['image']);

        $banner->update($validated);

        AuditLog::record(
            'update',
            'banners',
            "Updated banner: {$banner->title}",
            ['changes' => $banner->getChanges()]
        );

        return redirect()->route('banners.index')->with('success', 'Banner updated successfully.');
    }

    /**
     * Delete a banner.
     */
    public function destroy(Banner $banner)
    {
        $title = $banner->title;

        // Clean up file
        if ($banner->image_path && str_starts_with($banner->image_path, 'uploads/banners/') && file_exists(public_path($banner->image_path))) {
            @unlink(public_path($banner->image_path));
        }

        $banner->delete();

        AuditLog::record('delete', 'banners', "Deleted banner: {$title}");

        return redirect()->route('banners.index')->with('success', 'Banner removed successfully.');
    }

    /**
     * Toggle banner active status.
     */
    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);
        $status = $banner->is_active ? 'activated' : 'deactivated';

        AuditLog::record('toggle', 'banners', "Toggled banner status: '{$banner->title}' is now {$status}");

        return back()->with('success', "Banner '{$banner->title}' has been {$status}.");
    }
}
