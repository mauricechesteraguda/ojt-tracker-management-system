<?php

namespace App\Http\Controllers;

use App\Cluster;
use App\Company;
use App\Http\Resources\Cluster as ClusterResource;
use App\Http\Resources\ClusterCollection;
use App\Internship;
use App\Support\SessionTracer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/* security-10032026-Maurice: cluster mutations are privileged and atomic. */
class ClusterController extends Controller
{
    public function all()
    {
        $started=microtime(true); $id=SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('cluster.all',$id,array('resource_type'=>'cluster'));
        try { $result=\App\Support\PaginatedJson::response(Cluster::where('is_deleted','=','0')->orderBy('year','DSC')->paginate(5), ClusterResource::class, request(), 'cluster.pagination.all'); SessionTracer::leave('cluster.all',$id,$started,'success',array('resource_type'=>'cluster')); return $result; }
        catch (\Throwable $exception) { SessionTracer::exception('cluster.all',$id,$started,'cluster_unexpected',$exception); throw $exception; }
    }

    public function index()
    {
        $started=microtime(true); $id=SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('cluster.index',$id,array('resource_type'=>'cluster'));
        try { $result=\App\Support\PaginatedJson::response(Cluster::where('is_deleted','=','0')->orderBy('year','DSC')->paginate(5), ClusterResource::class, request(), 'cluster.pagination.index'); SessionTracer::leave('cluster.index',$id,$started,'success',array('resource_type'=>'cluster')); return $result; }
        catch (\Throwable $exception) { SessionTracer::exception('cluster.index',$id,$started,'cluster_unexpected',$exception); throw $exception; }
    }

    public function search($value)
    {
        $started=microtime(true); $id=SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('cluster.search',$id,array('resource_type'=>'cluster'));
        try { $result=\App\Support\PaginatedJson::response(Cluster::where('is_deleted','=','0')->where('year','LIKE','%'.$value.'%')->orderBy('id','DSC')->paginate(20), ClusterResource::class, request(), 'cluster.pagination.search'); SessionTracer::leave('cluster.search',$id,$started,'success',array('resource_type'=>'cluster')); return $result; }
        catch (\Throwable $exception) { SessionTracer::exception('cluster.search',$id,$started,'cluster_unexpected',$exception); throw $exception; }
    }

    public function show($id)
    {
        $started=microtime(true); $correlationId=SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('cluster.show',$correlationId,array('resource_type'=>'cluster','resource_id'=>(string)$id));
        try { $result=new ClusterResource(Cluster::where('is_deleted','=','0')->findOrFail($id)); SessionTracer::leave('cluster.show',$correlationId,$started,'success',array('resource_type'=>'cluster','resource_id'=>(string)$id)); return $result; }
        catch (\Throwable $exception) { SessionTracer::exception('cluster.show',$correlationId,$started,'cluster_unexpected',$exception); throw $exception; }
    }

    public function store(Request $request)
    {
        $started=microtime(true); $id=SessionTracer::id($request->header('X-Correlation-ID')); SessionTracer::enter('cluster.store',$id,array('resource_type'=>'cluster'));
        try {
            abort_unless(in_array(\Auth::user()->role,array('coordinator','superuser'),true),403);
            $request->validate(array('year'=>'required|max:255'));
            $result=DB::transaction(function () use ($request) {
                $internships=Internship::whereNull('cluster_id')->where('is_deleted','=','0')->where('schoolyear','LIKE','%'.$request->year.'%')->orderBy('id','ASC')->lockForUpdate()->get();
                if (!$internships->count()) return response()->json(array('message'=>'No more internships for the specified year!'),201);
                $locations=$internships->pluck('company_id')->unique()->values()->all(); $companies=Company::whereIn('id',$locations)->get()->sortBy(function($company){ return array($company->country,$company->province,$company->city,$company->address); });
                $cluster=null; $counter=0; $latestCity='';
                foreach ($companies as $company) {
                    $companyInternships=$internships->where('company_id',$company->id);
                    if (!$cluster || $counter >= 4 || ($counter >= 1 && $latestCity !== $company->city)) { $cluster=Cluster::create($request->only(array('year'))); $counter=0; }
                    foreach ($companyInternships as $internship) { $internship->cluster_id=$cluster->getKey(); $internship->save(); $latestCity=$company->city; }
                    $counter++;
                }
                return (new ClusterResource($cluster))->response()->setStatusCode(201);
            });
            SessionTracer::leave('cluster.store',$id,$started,'success',array('resource_type'=>'cluster')); return $result;
        } catch (\Throwable $exception) { SessionTracer::exception('cluster.store',$id,$started,'cluster_unexpected',$exception); throw $exception; }
    }

    public function delete($id)
    {
        $started=microtime(true); $correlationId=SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('cluster.delete',$correlationId,array('resource_type'=>'cluster','resource_id'=>(string)$id));
        try {
            $cluster=Cluster::findOrFail($id); abort_unless(in_array(\Auth::user()->role,array('coordinator','superuser'),true),403);
            DB::transaction(function () use ($id) { $cluster=Cluster::whereKey($id)->lockForUpdate()->firstOrFail(); $internships=Internship::where('cluster_id',$cluster->getKey())->lockForUpdate()->get(); foreach ($internships as $internship) { $internship->cluster_id=null; $internship->save(); } $cluster->setAttribute('is_deleted','1'); $cluster->save(); });
            SessionTracer::leave('cluster.delete',$correlationId,$started,'success',array('resource_type'=>'cluster','resource_id'=>(string)$id)); return response()->json(null,204);
        } catch (\Throwable $exception) { SessionTracer::exception('cluster.delete',$correlationId,$started,'cluster_unexpected',$exception); throw $exception; }
    }

    public function update(Request $request,$id)
    {
        $started=microtime(true); $correlationId=SessionTracer::id($request->header('X-Correlation-ID')); SessionTracer::enter('cluster.update',$correlationId,array('resource_type'=>'cluster','resource_id'=>(string)$id));
        try { abort_unless(in_array(\Auth::user()->role,array('coordinator','superuser'),true),403); $request->validate(array('name'=>'required|max:255')); $cluster=Cluster::findOrFail($id); $cluster->year=$request->input('year'); $cluster->updated_by=\Auth::id(); $cluster->save(); SessionTracer::leave('cluster.update',$correlationId,$started,'success',array('resource_type'=>'cluster','resource_id'=>(string)$id)); return response()->json(array('message'=>'Cluster updated successfully!'),200); }
        catch (\Throwable $exception) { SessionTracer::exception('cluster.update',$correlationId,$started,'cluster_unexpected',$exception); throw $exception; }
    }
}
