<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Update full profile (name, bio, image)
     * Email & password will NOT change
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'  => 'nullable|string|max:255',
            'bio'   => 'nullable|string',
            'image' => 'nullable|image|max:3000'
        ]);

        $user = Auth::user();

        // Update text fields
        $user->name = $request->name ?? $user->name;
        $user->bio  = $request->bio ?? $user->bio;

        // Update profile image if uploaded
        if ($request->hasFile('image')) {

            // delete old image
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // upload new image
            $path = $request->file('image')->store('profiles', 'public');
            $user->profile_image = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
            'image_url' => $user->profile_image ? asset('storage/'.$user->profile_image) : null
        ]);
    }
}
