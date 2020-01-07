<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'requirement_category_id', 'internship_id' ,'file', 'is_approved', 'is_deleted', 'updated_by',
    ];

    public function requirement_category()
    {
        return $this->belongsTo('App\RequirementCategory');
    }
    public function internship()
    {
        return $this->belongsTo('App\Internship');
    }
}
