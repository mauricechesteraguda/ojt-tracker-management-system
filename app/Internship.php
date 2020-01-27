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
        'user_id', 'company_id', 'start_date','representative','student_position','is_approved','status','comment','updated_by','sc','schoolyear','course_code','end_date','semester','campus','college_code',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function company()
    {
        return $this->belongsTo('App\Company');
    }
    public function descriptions()
    {
        return $this->hasMany('App\Description');
    }
    public function requirements()
    {
        return $this->hasMany('App\Requirement');
    }
    public function reports()
    {
        return $this->hasMany('App\Report');
    }
    public function cluster()
    {
        return $this->belongsTo('App\Cluster');
    }
}
