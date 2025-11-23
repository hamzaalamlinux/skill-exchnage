<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\SkillRequestController;
use App\Http\Controllers\Api\ChatController;

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::get('/skills',[SkillController::class,'index']);
Route::middleware('auth:sanctum')->group(function(){

    Route::post('/skills',[SkillController::class,'store']);

    Route::post('/skill-requests',[SkillRequestController::class,'store']);
    Route::put('/skill-requests/{id}/accept',[SkillRequestController::class,'accept']);
    Route::put('/skill-requests/{id}/reject',[SkillRequestController::class,'reject']);

    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/chat/messages/{otherUserId}', [ChatController::class, 'getMessages']);
    Route::get('/chat/messages/firebase/{otherUserId}', [ChatController::class, 'getMessagesFromFirebase']);
});
