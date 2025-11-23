<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'bio', 'profile_pic'];
    protected $hidden = ['password'];

    public function skills() { return $this->hasMany(Skill::class); }
    public function skillRequests() { return $this->hasMany(SkillRequest::class, 'requester_id'); }
}
