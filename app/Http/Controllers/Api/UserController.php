<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            'name' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'image' => 'nullable|image'
        ]);

        $user = Auth::user();

        // Update text fields
        $user->name = $request->name ?? $user->name;
        $user->bio = $request->bio ?? $user->bio;

        // Update profile image if uploaded
        if ($request->hasFile('image')) {
            // delete old image
            if ($user->profile_pic && Storage::disk('public')->exists($user->profile_pic)) {
                Storage::disk('public')->delete($user->profile_pic);
            }

            // upload new image
            $path = $request->file('image')->store('profiles', 'public');
            $user->profile_pic = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
            'image_url' => $user->profile_pic ? asset('storage/' . $user->profile_pic) : null
        ]);
    }

    public function logout(Request $request)
    {
        // Delete the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }


    public function profile()
    {
        $user = Auth::user();

        return response()->json([
            'user' => $user,
            'image_url' => $user->profile_pic ? asset('storage/' . $user->profile_pic) : null,
        ]);
    }

}
