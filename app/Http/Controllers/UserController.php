<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use App\Models\User; // Import User model

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

        // --- Store the new image and get the path ---
        $newPath = $request->file('profile_picture')->store('profile_pictures', 'public');
        if (!$newPath) {
            return back()->with('error', 'Failed to store profile picture.');
        }

        // --- Get old path for deletion ---
        $oldPath = $user->profile_picture; // Get old path from user table

        // Update User record first with the new PATH
        $user->profile_picture = $newPath;
        $user->save();

        // --- Update associated Employee record (if exists) ---
        $employee = $user->employee;
        if ($employee) {
            // If the employee record had a different old path, use it for deletion
            if ($employee->profile_picture && $employee->profile_picture !== $oldPath) {
                 $oldPath = $employee->profile_picture; // Prioritize deleting employee's specific old path if different
            }
            $employee->profile_picture = $newPath; // Update employee with the same new PATH
            $employee->save();
        }

        // --- Delete the old file from storage using its path ---
        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return back()->with('success', 'Profile picture uploaded successfully!');
    }

    // Remove the getPathFromUrl helper function if it exists
    // private function getPathFromUrl(?string $url): ?string { ... }
}
