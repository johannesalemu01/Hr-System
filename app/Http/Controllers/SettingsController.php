<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Company;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = Company::first(); 

        return Inertia::render('Settings/index', [
            'user' => $user,
            'company' => $company,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('settings')->with('success', 'Profile updated successfully.');
    }

    public function updateCompany(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $company = Company::first(); 
        if (!$company) {
            $company = new Company();
        }

        $company->fill($validated);
        $company->save();

        return redirect()->route('settings')->with('success', 'Company information updated successfully.');
    }
}
