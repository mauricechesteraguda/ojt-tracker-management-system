<?php

namespace App\Policies;

use App\Internship;
use App\User;
use App\Services\AuthorizationService;
use App\Support\SessionTracer;

class InternshipPolicy
{
    public function view(User $user, Internship $internship)
    {
        $started=microtime(true);$id=SessionTracer::id(request()->header('X-Correlation-ID'));SessionTracer::enter('authorization.internship.policy.view',$id,array('role'=>(string)$user->role,'resource_type'=>'internship','resource_id'=>(string)$internship->id));try{$result=app(AuthorizationService::class)->canAccessInternship($user,$internship);SessionTracer::leave('authorization.internship.policy.view',$id,$started,$result?'allowed':'denied',array('role'=>(string)$user->role,'resource_type'=>'internship','resource_id'=>(string)$internship->id));return $result;}catch(\Throwable $e){SessionTracer::exception('authorization.internship.policy.view',$id,$started,'authorization_unexpected',$e);throw $e;}
    }

    public function update(User $user, Internship $internship)
    {
        $started=microtime(true);$id=SessionTracer::id(request()->header('X-Correlation-ID'));SessionTracer::enter('authorization.internship.policy.update',$id,array('role'=>(string)$user->role,'resource_type'=>'internship','resource_id'=>(string)$internship->id));try{$result=$this->view($user,$internship);SessionTracer::leave('authorization.internship.policy.update',$id,$started,$result?'allowed':'denied',array('role'=>(string)$user->role,'resource_type'=>'internship','resource_id'=>(string)$internship->id));return $result;}catch(\Throwable $e){SessionTracer::exception('authorization.internship.policy.update',$id,$started,'authorization_unexpected',$e);throw $e;}
    }
}
