<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillRequest extends Model
{
    protected $fillable = ['requester_id', 'requested_skill_id', 'status'];
    public function requester() { return $this->belongsTo(User::class, 'requester_id'); }
    public function skill() { return $this->belongsTo(Skill::class, 'requested_skill_id'); }
}
