<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller {
    public function index() {
        return Skill::with('user')->get();
    }
    public function store(Request $request) {
        $request->validate(['skill_name'=>'required']);
        $skill = Skill::create([
            'user_id' => Auth::id(),
            'skill_name' => $request->skill_name,
            'description' => $request->description,
            'category' => $request->category
        ]);
        return response()->json($skill);
    }
}
