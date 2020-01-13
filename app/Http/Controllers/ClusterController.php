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

            
            $cluster = Cluster::create($request->all());
            $cluster->save();

            $internships = Internship::where('is_deleted', '=', '0')->where('start_date', 'LIKE','%' . $cluster->year . '%' )->orderBy('id', 'ASC')->get();

            foreach ($internships as $i) {
                $i->cluster_id = $cluster->id;
                $i->save();
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
