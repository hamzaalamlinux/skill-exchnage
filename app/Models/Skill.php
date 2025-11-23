<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model {
    use HasFactory;
    protected $fillable = ['user_id', 'skill_name', 'description', 'category'];
    public function user() { return $this->belongsTo(User::class); }
    public function requests() { return $this->hasMany(SkillRequest::class, 'requested_skill_id'); }
}
