<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'year', 'is_deleted', 'updated_by',
    ];

    public function internships()
    {
        return $this->hasMany('App\Internship');
    }
}
