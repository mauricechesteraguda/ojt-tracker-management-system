<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Cluster;
use App\Company;
use App\Internship;
use App\Http\Resources\Cluster as ClusterResource;
use App\Http\Resources\ClusterCollection;

class ClusterController extends Controller
{
    public function all()
    {
        return new ClusterCollection(Cluster::where('is_deleted', '=', '0')->orderBy('id', 'DSC')->get());
    }
    public function index()
    {
        return new ClusterCollection(Cluster::where('is_deleted', '=', '0')->orderBy('id', 'DSC')->paginate(5));
    }
    public function search($value)
    {
        return new ClusterCollection(Cluster::where('is_deleted', '=', '0')->where('year', 'LIKE', '%'.$value.'%')->orderBy('id', 'DSC')->paginate(20));
    }

    public function show($id)
    {
        return new ClusterResource(Cluster::findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|max:255',

        ]);

        
        $internships = Internship::whereNull('cluster_id')->where('is_deleted', '=', '0')->where('start_date', 'LIKE','%' . $request->year . '%' )->orderBy('id', 'ASC')->get();

        if ($internships->count()) {
            # code...
        
            $locations = array();

            foreach ($internships as $i) {
                array_push($locations, $i->company->id);
            }
        
            array_unique($locations);

            $sorted_companies = Company::find($locations)->sortBy( function($company) {
                return [
                    $company->country,
                    $company->province,
                    $company->city,
                    $company->address
                ];
            });

            $company_visit_per_day = 4;
            $counter = 0;

            $latest_city = '';
            $cluster = null;

            foreach ($sorted_companies as $c) {
                if ($counter == 0) {
                    $cluster = Cluster::create($request->all());
                    $cluster->save();

                    $internships_by_company = Internship::whereNull('cluster_id')->where('is_deleted', '=', '0')->where('start_date', 'LIKE', '%' . $request->year . '%')->where('company_id', '=', $c->id)->orderBy('id', 'ASC')->get();

                    foreach ($internships_by_company as $i) {
                        $i->cluster_id = $cluster->id;
                        $i->save();
                        $latest_city = $i->company->city;
                    }
                    $counter++;

                    continue;

                } elseif ($counter >= 1 && $counter <  $company_visit_per_day) {

                    $internships_by_company = Internship::whereNull('cluster_id')->where('is_deleted', '=', '0')->where('start_date', 'LIKE', '%' . $request->year . '%')->where('company_id', '=', $c->id)->orderBy('id', 'ASC')->get();

                    //check if the next internships is in the same city
                    foreach ($internships_by_company as $i) {
                        if ($latest_city != $i->company->city) {
                            $cluster = Cluster::create($request->all());
                            $cluster->save();
                        }
                        break;
                    }
                    

                    foreach ($internships_by_company as $i) {
                        $i->cluster_id = $cluster->id;
                        $i->save();
                        $latest_city = $i->company->city;
                    }
                    $counter++;

                    continue;
                    
                } elseif ($counter  == $company_visit_per_day) {
                    $cluster = Cluster::create($request->all());
                    $cluster->save();

                    $internships_by_company = Internship::whereNull('cluster_id')->where('is_deleted', '=', '0')->where('start_date', 'LIKE', '%' . $request->year . '%')->where('company_id', '=', $c->id)->orderBy('id', 'ASC')->get();

                    
                    foreach ($internships_by_company as $i) {
                        $i->cluster_id = $cluster->id;
                        $i->save();
                        $latest_city = $i->company->city;
                    }
                    $counter = 1;
                }
            }
                    
            
            return (new ClusterResource($cluster))
                    ->response()
                    ->setStatusCode(201);
        }else{
            return response()->json([
                'message' => 'No more internships for the specified year!'
            ], 201);
        }
        

        
    }

    public function delete($id)
    {
        $cluster = Cluster::findOrFail($id);
        $cluster->is_deleted="1";
        $cluster->save();
        return response()->json(null, 204);
        // return new ClusterCollection(Cluster::all());
    }

        
    public function update(Request $request, $id)
        {
            $this->validate($request, [
                'name' => 'required|max:255',
            ]);
    
            $cluster = Cluster::findOrFail($id);

            $cluster->year = request('year');
            $cluster->updated_by = request('updated_by');
            
            $cluster->save();
    
            return response()->json([
                'message' => 'Cluster updated successfully!'
            ], 200);
        }
}
