<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'country', 'city','address','location_map','main_branch',
    ];

    public function internships()
    {
        return $this->hasMany('App\Internship');
    }

    
}
