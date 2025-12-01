<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillFeedback extends Model
{
    //
    protected $fillable = ['skill_id', 'visitor_name', 'visitor_email', 'feedback'];
}
