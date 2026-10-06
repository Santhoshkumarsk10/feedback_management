<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Show company profile edit screen.
     */
    public function edit()
    {
        $company = Company::current();
        return view('company.edit', compact('company'));
    }

    /**
     * Update company profile information.
     */
    public function update(Request $request)
    {
        $company = Company::current();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'alter_phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:3072',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'company_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/company');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Clean up old uploaded logo if it exists in uploads/company
            if ($company->logo && str_starts_with($company->logo, 'uploads/company/') && file_exists(public_path($company->logo))) {
                @unlink(public_path($company->logo));
            }

            $file->move($destinationPath, $filename);
            $validated['logo'] = 'uploads/company/' . $filename;
        }

        $company->update($validated);

        AuditLog::record(
            'update',
            'company',
            "Updated company profile details for: {$company->name}",
            ['changes' => $company->getChanges()]
        );

        return redirect()->route('company.edit')->with('success', 'Company profile details updated successfully.');
    }
}
