<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SkillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillRequestController extends Controller {
    public function store(Request $request){
        $request->validate(['requested_skill_id'=>'required|exists:skills,id']);
        $skillRequest = SkillRequest::create([
            'requester_id'=>Auth::id(),
            'requested_skill_id'=>$request->requested_skill_id,
            'status'=>'pending'
        ]);
        return response()->json($skillRequest);
    }

    public function accept($id){
        $request = SkillRequest::findOrFail($id);
        $request->status = 'accepted';
        $request->save();
        return response()->json(['message'=>'Request accepted']);
    }

    public function reject($id){
        $request = SkillRequest::findOrFail($id);
        $request->status = 'rejected';
        $request->save();
        return response()->json(['message'=>'Request rejected']);
    }
}
