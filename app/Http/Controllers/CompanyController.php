<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Company;
use App\Internship;
use App\Http\Resources\Company as CompanyResource;
use App\Http\Resources\CompanyCollection;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Support\SessionTracer;

class CompanyController extends Controller
{
    public function company_status($id,$year)
    {        
        $is_visited = false;
        $date_visited = '';
        $internships = Internship::where('is_deleted','=','0')->where('company_id','=',$id)->where('start_date','LIKE','%'.$year.'%')->get();

        foreach ($internships as $i) {
            if ($i->date_visited) {
                $date_visited = $i->date_visited;
                $is_visited = true;
                break;
            }
        }

        if ($is_visited) {
            return response()->json([
                'date_visited' => $date_visited
            ], 200);
        }
        return response()->json([
            'message' => 'Company visit not yet completed.'
        ], 201);
    }

    public function cluster_status($id)
    {        
        $is_visited = false;
        $internships = Internship::where('is_deleted','=','0')->where('cluster_id','=',$id)->get();

        foreach ($internships as $i) {
            if ($i->date_visited) {
                $is_visited = true;
                continue;
            }else{
                $is_visited = false;
                break;
            }
        }

        if ($is_visited) {
            return response()->json([
                'message' => 'Cluster visit completed.'
            ], 200);
        }
        return response()->json([
            'message' => 'Cluster visit not yet completed.'
        ], 201);
    }
    public function cluster($id)
    {        
        $companies = Company::selectRaw("*")->whereRaw("id in (SELECT company_id FROM internships WHERE is_deleted = 0 AND cluster_id =" . $id . ") ORDER BY country,province,city,address,name ASC")->paginate(20);

        return new CompanyCollection($companies);
    }
    public function all()
    {
        return new CompanyCollection(Company::where('is_deleted', '=', '0')->orderBy('name', 'ASC')->get());
    }
    public function index()
    {
        return new CompanyCollection(Company::where('is_deleted', '=', '0')->orderBy('name', 'ASC')->paginate(5));
    }
    public function search($value)
    {
        return new CompanyCollection(Company::where('is_deleted', '=', '0')->where('name', 'LIKE', '%'.$value.'%')->orWhere('address', 'LIKE', '%'.$value.'%')->orWhere('country', 'LIKE', '%'.$value.'%')->orWhere('province', 'LIKE', '%'.$value.'%')->orWhere('city', 'LIKE', '%'.$value.'%')->where('is_deleted', '=', '1')->orderBy('name', 'ASC')->paginate(20));
    }

    public function show($id)
    {
        return new CompanyResource(Company::findOrFail($id));
    }

    public function store(Request $request)
    {
        $started=microtime(true);$correlationId=SessionTracer::id($request->header('X-Correlation-ID'));SessionTracer::enter('company.store',$correlationId,array('resource_type'=>'company'));
        try { abort_unless(in_array(Auth::user()->role, array('coordinator', 'superuser'), true), 403);
        $request->validate([
            'name' => 'required|max:255',

        ]);
        $current_name = $request->input('name');
        $current_city = $request->input('city');

        $company = Company::where('name', '=', $current_name)->where('city', '=', $current_city)->first();
        if (!$company) {
            $company = Company::create($request->only(array('name','country','province','city','address','location_map','main_branch')));
            $company->save();

            $result=(new CompanyResource($company))
                    ->response()
                    ->setStatusCode(201);
            SessionTracer::leave('company.store',$correlationId,$started,'success',array('resource_type'=>'company')); return $result;
        }
        $result=response()->json([
            'message' => 'Company already exists!'
        ], 500);
        SessionTracer::leave('company.store',$correlationId,$started,'duplicate',array('resource_type'=>'company')); return $result;

        
        } catch (\Throwable $exception) { SessionTracer::exception('company.store',$correlationId,$started,'company_unexpected',$exception); throw $exception; }
    }

    public function delete($id)
    {
        $started=microtime(true);$correlationId=SessionTracer::id(request()->header('X-Correlation-ID'));SessionTracer::enter('company.delete',$correlationId,array('resource_type'=>'company','resource_id'=>(string)$id));
        try { abort_unless(in_array(Auth::user()->role, array('coordinator', 'superuser'), true), 403);
        $company = Company::findOrFail($id);
        $company->is_deleted="1";
        $company->save();
        $result=response()->json(null, 204); SessionTracer::leave('company.delete',$correlationId,$started,'success',array('resource_type'=>'company','resource_id'=>(string)$id)); return $result;
        // return new CompanyCollection(Company::all());
        } catch (\Throwable $exception) { SessionTracer::exception('company.delete',$correlationId,$started,'company_unexpected',$exception); throw $exception; }
    }

        
    public function update(Request $request, $id)
        {
            $started=microtime(true);$correlationId=SessionTracer::id($request->header('X-Correlation-ID'));SessionTracer::enter('company.update',$correlationId,array('resource_type'=>'company','resource_id'=>(string)$id));
            try { abort_unless(in_array(Auth::user()->role, array('coordinator', 'superuser'), true), 403);
            $this->validate($request, [
                'name' => 'required|max:255',
            ]);
    
            $company = Company::findOrFail($id);
            
            $company->name = request('name');
            $company->country = request('country');
            $company->province = request('province');
            $company->city = request('city');
            $company->address = request('address');
            $company->location_map = request('location_map');
            $company->save();
    
            $result=response()->json([
                'message' => 'Company updated successfully!'
            ], 200);
            SessionTracer::leave('company.update',$correlationId,$started,'success',array('resource_type'=>'company','resource_id'=>(string)$id)); return $result;
            } catch (\Throwable $exception) { SessionTracer::exception('company.update',$correlationId,$started,'company_unexpected',$exception); throw $exception; }
        }

}
