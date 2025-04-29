<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Use Auth::user() to ensure the authenticated user is retrieved
        $user = Auth::user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfileInformation(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->fill($request->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'Profile updated successfully.');
    }

    /**
     * Upload or update the user's profile picture.
     */
    public function uploadProfilePicture(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_picture' => 'required|image|max:2048', // Validate the uploaded image
        ]);

        $user = Auth::user();

        // Store the uploaded file in the 'profile_pictures' directory
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        // Generate the full URL for the stored file
        $fullUrl = url("storage/{$path}");

        // Delete the old profile picture if it exists
        if ($user->profile_picture) {
            $oldPath = str_replace(url('storage/'), '', $user->profile_picture);
            Storage::disk('public')->delete($oldPath);
        }

        // Update the user's profile picture URL in the database
        $user->profile_picture = $fullUrl;
        $user->save();

        // Return with updated user data
        return Redirect::route('profile.edit')->with([
            'status' => 'Profile picture updated successfully.',
            'auth' => $user, // Pass the updated user data
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Use Auth::user() to ensure the authenticated user is retrieved
        $user = Auth::user();

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
