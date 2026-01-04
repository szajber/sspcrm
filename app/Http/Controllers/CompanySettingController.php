<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function edit()
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        $company = CompanySetting::firstOrFail();
        return view('company.edit', compact('company'));
    }

    public function update(Request $request)
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        $company = CompanySetting::firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $path = $request->file('logo')->store('company-logos', 'public');
            $validated['logo_path'] = $path;
        }

        $company->update($validated);

        return redirect()->route('company.edit')->with('status', 'settings-updated');
    }
}
