<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Description extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'internship_id', 'description','is_deleted',
    ];

    public function internship()
    {
        return $this->belongsTo('App\Internship');
    }
}
