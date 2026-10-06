<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    /**
     * Get company profile details.
     */
    public function company(): JsonResponse
    {
        $company = Company::current();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'alter_phone' => $company->alter_phone,
                'address' => $company->address,
                'city' => $company->city,
                'state' => $company->state,
                'pincode' => $company->pincode,
                'website' => $company->website,
                'logo_url' => $company->logo_url,
                'description' => $company->description,
                'full_address' => $company->full_address,
            ],
        ]);
    }

    /**
     * Get active banners (supports ?target=mobile or ?target=tablet).
     */
    public function banners(Request $request): JsonResponse
    {
        $target = $request->input('target', 'all');

        $banners = Banner::active()
            ->when($target !== 'all', fn ($q) => $q->forTarget($target))
            ->ordered()
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'image_url' => $banner->image_url,
                    'target' => $banner->target,
                    'link_url' => $banner->link_url,
                    'sort_order' => $banner->sort_order,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $banners->count(),
            'data' => $banners,
        ]);
    }
}
