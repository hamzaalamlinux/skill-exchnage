<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SkillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SkillRequestController extends Controller {
    public function store(Request $request){

        $validator = Validator::make($request->all(),['requested_skill_id'=>'required|exists:skills,id']);
        if($validator->fails()){
            return response()->json([
                "status" => false,
                "message" => "validation failed",
                "errors" => $validator->errors()
            ]);
        }

        $skillRequest = SkillRequest::create([
            'requester_id'=>Auth::id(),
            'requested_skill_id'=>$request->requested_skill_id,
            'status'=>'requested'
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
