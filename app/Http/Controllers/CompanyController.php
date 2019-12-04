<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Company;
use App\Http\Resources\Company as CompanyResource;
use App\Http\Resources\CompanyCollection;

class CompanyController extends Controller
{
    public function index()
    {
        return new CompanyCollection(Company::orderBy('name', 'ASC')->paginate(5));
    }
    public function search($value)
    {
        return new CompanyCollection(Company::where('name', 'LIKE', '%'.$value.'%')->orWhere('address', 'LIKE', '%'.$value.'%')->orWhere('country', 'LIKE', '%'.$value.'%')->orWhere('city', 'LIKE', '%'.$value.'%')->orderBy('name', 'ASC')->paginate(20));
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
        $company->delete();

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
            $company->city = request('city');
            $company->address = request('address');
            $company->location_map = request('location_map');
            $company->save();
    
            return response()->json([
                'message' => 'Company updated successfully!'
            ], 200);
        }

}
