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
            'name' => ['required', 'string', 'min:2', 'max:150', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'email' => ['nullable', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'max:100'],
            'phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'alter_phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'address' => ['nullable', 'string', 'min:3', 'max:500', 'regex:~^[\p{L}\p{N}\s,\.\-/#()&\'’]+$~u'],
            'city' => ['nullable', 'string', 'min:2', 'max:50', 'regex:~^[\p{L}\s\.\-]+$~u'],
            'state' => ['nullable', 'string', 'min:2', 'max:50', 'regex:~^[\p{L}\s\.\-]+$~u'],
            'pincode' => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],
            'website' => ['nullable', 'url', 'max:200'],
            'description' => ['nullable', 'string', 'min:5', 'max:1000', 'regex:~^[\p{L}\p{N}\s\.\,\-\–\—\_\&\/\(\)\'\"\!\?\:\;\%\r\n]+$~u'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:3072'],
        ], [
            'name.required' => 'Company Name is required.',
            'name.regex' => 'Company Name may only contain letters, numbers, spaces, and allowed symbols (&, -, —, _, /, ., ,, (), \').',
            'email.email' => 'Please provide a valid company email address.',
            'email.regex' => 'Please provide a valid company email address with domain (e.g. info@company.com).',
            'phone.regex' => 'Primary Phone must be a valid 10-digit number starting with 6, 7, 8, or 9.',
            'alter_phone.regex' => 'Alternate Phone must be a valid 10-digit number starting with 6, 7, 8, or 9.',
            'address.regex' => 'Address may only contain letters, numbers, spaces, and valid address symbols (,, ., -, /, #, (), &, \').',
            'city.regex' => 'City name may only contain letters, spaces, hyphens, and dots.',
            'state.regex' => 'State name may only contain letters, spaces, hyphens, and dots.',
            'pincode.regex' => 'PIN Code must be a valid 6-digit postal code (e.g. 600123).',
            'description.regex' => 'Description contains unsupported special characters.',
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
