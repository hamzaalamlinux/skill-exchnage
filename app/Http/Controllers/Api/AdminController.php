<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Get all users
     */
    public function getUsers()
    {
        return User::where('role', '!=', 1)->get();
    }

    /**
     * Get all skills with user info and image URLs
     */
    public function getSkills()
    {
        $skills = Skill::with('user')->get();

        $skills->transform(function ($skill) {
            $skill->image_url = $skill->image ? asset('storage/'.$skill->image) : null;
            return $skill;
        });

        return response()->json($skills);
    }

    /**
     * Update skill
     */
    public function updateSkill(Request $request, $id)
    {
        $skill = Skill::findOrFail($id);

        $skill->update([
            'skill_name' => $request->skill_name ?? $skill->skill_name,
            'description' => $request->description ?? $skill->description,
            'category' => $request->category ?? $skill->category,
        ]);

        // Add image URL if exists
        $skill->image_url = $skill->image ? asset('storage/'.$skill->image) : null;

        return response()->json(['message' => 'Skill updated', 'skill' => $skill]);
    }

    /**
     * Upload / update skill image
     */
    public function uploadSkillImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|max:3000'
        ]);

        $skill = Skill::findOrFail($id);

        // Delete old image if exists
        if ($skill->image && Storage::disk('public')->exists($skill->image)) {
            Storage::disk('public')->delete($skill->image);
        }

        $path = $request->file('image')->store('skills', 'public');
        $skill->image = $path;
        $skill->save();

        $skill->image_url = asset('storage/'.$path);

        return response()->json([
            'message' => 'Skill image updated successfully',
            'skill' => $skill
        ]);
    }

    /**
     * Delete skill
     */
    public function deleteSkill($id)
    {
        $skill = Skill::findOrFail($id);

        if ($skill->image && Storage::disk('public')->exists($skill->image)) {
            Storage::disk('public')->delete($skill->image);
        }

        $skill->delete();

        return response()->json(['message' => 'Skill deleted successfully']);
    }

    /**
     * Approve skill
     */
    public function approveSkill($id)
    {
        $skill = Skill::findOrFail($id);
        $skill->status = 'approved';
        $skill->save();

        return response()->json(['message' => 'Skill approved']);
    }

    /**
     * Reject skill
     */
    public function rejectSkill($id)
    {
        $skill = Skill::findOrFail($id);
        $skill->status = 'rejected';
        $skill->save();

        return response()->json(['message' => 'Skill rejected']);
    }
}
