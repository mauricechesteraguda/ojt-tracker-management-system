<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Company;
use App\Internship;
use App\Http\Resources\Company as CompanyResource;
use App\Http\Resources\CompanyCollection;

use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
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
        $request->validate([
            'name' => 'required|max:255',

        ]);
        $current_name = $request->input('name');
        $current_city = $request->input('city');

        $company = Company::where('name', '=', $current_name)->where('city', '=', $current_city)->first();
        if (!$company) {
            $company = Company::create($request->all());
            $company->save();

            return (new CompanyResource($company))
                    ->response()
                    ->setStatusCode(201);
        }
        return response()->json([
            'message' => 'Company already exists!'
        ], 500);

        
    }

    public function delete($id)
    {
        $company = Company::findOrFail($id);
        $company->is_deleted="1";
        $company->save();
        return response()->json(null, 204);
        // return new CompanyCollection(Company::all());
    }

        
    public function update(Request $request, $id)
        {
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
    
            return response()->json([
                'message' => 'Company updated successfully!'
            ], 200);
        }

}
