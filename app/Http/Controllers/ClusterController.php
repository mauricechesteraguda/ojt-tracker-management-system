<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Cluster;
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

        $locations = array();

        foreach ($internships as $i) {
            array_push($locations, $i->company->id);
        }
        
        array_unique($locations);

        $company_visit_per_day = 4;
        $counter = 0;
        // $latest_cluster_number = fetch from db 
        $current_year = $request->year;
        $cluster = null;

        foreach ($locations as $l) {
            

            if ($counter == 0){
                $cluster = Cluster::create($request->all());
                $cluster->save();

                $internships_by_company = Internship::whereNull('cluster_id')->where('is_deleted', '=', '0')->where('start_date', 'LIKE','%' . $request->year . '%' )->where('company_id','=',$l)->orderBy('id', 'ASC')->get();

                foreach ($internships_by_company as $i) {
                    $i->cluster_id = $cluster->id;
                    $i->save();
                }
                $counter++;
            }elseif ($counter >= 1 && $counter <  $company_visit_per_day) {
                foreach ($internships_by_company as $i) {
                    $i->cluster_id = $cluster->id;
                    $i->save();
                }
                $counter++;
            }elseif ($counter  == $company_visit_per_day) {
                $cluster = Cluster::create($request->all());
                $cluster->save();

                $internships_by_company = Internship::whereNull('cluster_id')->where('is_deleted', '=', '0')->where('start_date', 'LIKE','%' . $request->year . '%' )->where('company_id','=',$l)->orderBy('id', 'ASC')->get();

                foreach ($internships_by_company as $i) {
                    $i->cluster_id = $cluster->id;
                    $i->save();
                }
                $counter = 1;
            }
        
        }
                    
            
            return (new ClusterResource($cluster))
                    ->response()
                    ->setStatusCode(201);
        
        

        
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
