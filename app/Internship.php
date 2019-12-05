<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'company_id', 'start_date','representative','student_position','is_approved','status','comment','updated_by',
    ];
}
