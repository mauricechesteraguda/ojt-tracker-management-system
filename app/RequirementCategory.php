<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequirementCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'file', 'is_deleted','updated_by',
    ];

    public function requirements()
    {
        return $this->hasMany('App\Requirement');
    }
}
