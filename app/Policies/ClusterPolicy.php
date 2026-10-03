<?php

namespace App\Policies;

use App\Cluster;
use App\User;
use App\Support\SessionTracer;

class ClusterPolicy
{
    public function delete(User $user, Cluster $cluster)
    {
        $started=microtime(true);$id=SessionTracer::id(request()->header('X-Correlation-ID'));SessionTracer::enter('authorization.cluster.delete',$id,array('role'=>(string)$user->role,'resource_type'=>'cluster','resource_id'=>(string)$cluster->id));
        try {$allowed=in_array($user->role,array('coordinator','superuser'),true);SessionTracer::leave('authorization.cluster.delete',$id,$started,$allowed?'allowed':'denied',array('role'=>(string)$user->role,'resource_type'=>'cluster','resource_id'=>(string)$cluster->id));return $allowed;}catch(\Throwable $e){SessionTracer::exception('authorization.cluster.delete',$id,$started,'authorization_unexpected',$e);throw $e;}
    }
}
