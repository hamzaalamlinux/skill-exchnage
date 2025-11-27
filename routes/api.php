<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\SkillRequestController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::get('/skills',[SkillController::class,'reteriveSkills']);


Route::middleware(['auth.api','auth:sanctum'])->prefix('user')->group(function () {

    // Profile
    Route::put('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/profile/upload-image', [UserController::class, 'uploadProfileImage']);

    // Skills
    Route::post('/skills', [SkillController::class, 'store']);
    Route::get('/skills', [SkillController::class, 'index']);
    Route::post('/skills/upload-image/{id}', [SkillController::class, 'uploadSkillImage']);

    // Skill Requests
    Route::post('/skill-requests', [SkillRequestController::class, 'store']);
});


Route::middleware(['auth.api','auth:sanctum', 'is_admin'])->prefix('admin')->group(function () {

    // Users
    Route::get('/users', [AdminController::class, 'getUsers']);
    // Skills
    Route::get('/skills', [AdminController::class, 'getSkills']);
    Route::put('/skills/{id}', [AdminController::class, 'updateSkill']);
    Route::delete('/skills/{id}', [AdminController::class, 'deleteSkill']);
    Route::put('/skills/{id}/approve', [AdminController::class, 'approveSkill']);
    Route::put('/skills/{id}/reject', [AdminController::class, 'rejectSkill']);
});

