<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\SkillFeedback;
use App\Models\SkillRating;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SkillController extends Controller
{

    public function reteriveSkillsById($id){
        $skill = Skill::where('id', $id)
        ->firstOrFail();
        $skill->image_url = $skill->image ? asset('storage/' . $skill->image) : null;
        return response()->json($skill);
    }

    public function addFeedback(Request $request)
    {
        $request->validate([
            'skill_id' => 'required|exists:skills,id',
            'feedback' => 'required|string',
            'visitor_name' => 'nullable|string',
            'visitor_email' => 'nullable|email',
        ]);

        $feedback = SkillFeedback::create([
            'skill_id' => $request->skill_id,
            'visitor_name' => $request->visitor_name,
            'visitor_email' => $request->visitor_email,
            'feedback' => $request->feedback,
        ]);

        return response()->json(['message' => 'Feedback submitted', 'data' => $feedback]);
    }

    public function addRating(Request $request)
    {
        $request->validate([
            'skill_id' => 'required|exists:skills,id',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $rating = SkillRating::create([
            'skill_id' => $request->skill_id,
            'rating' => $request->rating
        ]);

        return response()->json(['message' => 'Rating submitted', 'data' => $rating]);
    }



    public function reteriveSkills()
    {
        $skills = Skill::whereHas('requests', function ($query) {
                $query->where('status', 'accepted');
            })
            ->with('requests') // optional: eager load the requests
            ->get();


        // Add image URLs and request status
        $skills->transform(function ($skill) {
            $skill->image_url = $skill->image ? asset('storage/' . $skill->image) : null;

            // If there is a request, get the status (assuming 1 request per user per skill)
            $skill->requested = $skill->requests->first()?->status ?? null;

            // Remove skillRequests if you only want status
            unset($skill->skillRequests);

            return $skill;
        });


        return response()->json($skills);
    }
    /**
     * User Dashboard: List logged-in user's skills
     */
    public function index()
    {
        $skills = Skill::where('user_id', Auth::id())
            ->with([
                'requests' => function ($query) {
                    $query->where('requester_id', Auth::id()); // optional: only for current user
                }
            ])
            ->get();

        // Add image URLs and request status
        $skills->transform(function ($skill) {
            $skill->image_url = $skill->image ? asset('storage/' . $skill->image) : null;

            // If there is a request, get the status (assuming 1 request per user per skill)
            $skill->requested = $skill->requests->first()?->status ?? null;

            // Remove skillRequests if you only want status
            unset($skill->skillRequests);

            return $skill;
        });

        return response()->json([
            'message' => 'Your skills',
            'skills' => $skills
        ]);

    }

    /**
     * User: Add a new skill
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'skill_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'image' => 'nullable|image'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "validation failed",
                "errors" => $validator->errors()
            ]);
        }

        $skillData = [
            'user_id' => Auth::id(), // Automatically assign logged-in user
            'skill_name' => $request->skill_name,
            'description' => $request->description,
            'category' => $request->category,
            'status' => 'pending', // By default pending
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('skills', 'public');
            $skillData['image'] = $path;
        }

        $skill = Skill::create($skillData);
        $skill->image_url = $skill->image ? asset('storage/' . $skill->image) : null;

        return response()->json([
            'message' => 'Skill created successfully and pending approval',
            'skill' => $skill
        ]);
    }

    /**
     * User: Update a skill (only their own skill)
     */
    public function update(Request $request, $id)
    {
        $skill = Skill::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail(); // Ensure only user's own skill

        $skill->update([
            'skill_name' => $request->skill_name ?? $skill->skill_name,
            'description' => $request->description ?? $skill->description,
            'category' => $request->category ?? $skill->category,
        ]);

        $skill->image_url = $skill->image ? asset('storage/' . $skill->image) : null;

        return response()->json([
            'message' => 'Skill updated successfully',
            'skill' => $skill
        ]);
    }

    /**
     * User: Delete a skill (only their own skill)
     */
    public function delete($id)
    {
        $skill = Skill::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($skill->image && Storage::disk('public')->exists($skill->image)) {
            Storage::disk('public')->delete($skill->image);
        }

        $skill->delete();

        return response()->json(['message' => 'Skill deleted successfully']);
    }

    /**
     * User: Upload or change skill image (only their own skill)
     */
    public function uploadSkillImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|max:3000'
        ]);

        $skill = Skill::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($skill->image && Storage::disk('public')->exists($skill->image)) {
            Storage::disk('public')->delete($skill->image);
        }

        $path = $request->file('image')->store('skills', 'public');
        $skill->image = $path;
        $skill->save();

        $skill->image_url = asset('storage/' . $path);

        return response()->json([
            'message' => 'Skill image uploaded successfully',
            'skill' => $skill
        ]);
    }
}
