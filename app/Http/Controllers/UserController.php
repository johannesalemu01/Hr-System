<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use App\Models\User;

class UserController extends Controller
{
    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
             return back()->with('error', 'User not authenticated.');
        }

        // --- Store the new image and get the RELATIVE path ---
        // store() returns 'profile_pictures/filename.jpg'
        $newPath = $request->file('profile_picture')->store('profile_pictures', 'public');

        if (!$newPath) {
            return back()->with('error', 'Failed to store profile picture.');
        }

        // --- Get old RELATIVE path for deletion ---
        // Assumes paths in DB are relative after fixing potential issues
        $oldPathUser = $user->profile_picture;
        $oldPathEmployee = $user->employee?->profile_picture;

        // --- Update User record with the new RELATIVE PATH ---
        // $newPath IS the relative path like 'profile_pictures/...'
        $user->profile_picture = $newPath;
        $user->save(); // Saves the relative path unless a mutator/observer interferes

        // --- Update associated Employee record (if exists) ---
        $employee = $user->employee;
        if ($employee) {
            // $newPath IS the relative path like 'profile_pictures/...'
            $employee->profile_picture = $newPath;
            $employee->save(); // Saves the relative path unless a mutator/observer interferes
        }

        // --- Determine which old path to delete ---
        $pathToDelete = $oldPathUser;
        if ($employee && $oldPathEmployee && $oldPathEmployee !== $oldPathUser) {
            $pathToDelete = $oldPathEmployee;
        }
        else if (!$oldPathUser && $employee && $oldPathEmployee) {
             $pathToDelete = $oldPathEmployee;
        }


        // --- Delete the old file from storage using its RELATIVE path ---
        if ($pathToDelete && $pathToDelete !== $newPath && Storage::disk('public')->exists($pathToDelete)) {
            Storage::disk('public')->delete($pathToDelete);
        }

        return back()->with('success', 'Profile picture uploaded successfully!');
    }
}
