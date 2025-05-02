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


        
        $newPath = $request->file('profile_picture')->store('profile_pictures', 'public');

        if (!$newPath) {
            return back()->with('error', 'Failed to store profile picture.');
        }


        $oldPathUser = $user->profile_picture;
        $oldPathEmployee = $user->employee?->profile_picture;



        $user->profile_picture = $newPath;
        $user->save(); 


        $employee = $user->employee;
        if ($employee) {

            $employee->profile_picture = $newPath;
            $employee->save(); 
        }


        $pathToDelete = $oldPathUser;
        if ($employee && $oldPathEmployee && $oldPathEmployee !== $oldPathUser) {
            $pathToDelete = $oldPathEmployee;
        }
        else if (!$oldPathUser && $employee && $oldPathEmployee) {
             $pathToDelete = $oldPathEmployee;
        }



        if ($pathToDelete && $pathToDelete !== $newPath && Storage::disk('public')->exists($pathToDelete)) {
            Storage::disk('public')->delete($pathToDelete);
        }

        return back()->with('success', 'Profile picture uploaded successfully!');
    }
}
