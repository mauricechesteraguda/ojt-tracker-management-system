<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'internship_id','description', 'date','hours','is_valid','is_deleted','updated_by',
    ];

    public function internship()
    {
        return $this->belongsTo('App\Internship');
    }
}
