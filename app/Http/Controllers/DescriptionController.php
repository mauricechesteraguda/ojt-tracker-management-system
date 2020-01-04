<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Description;
use App\Http\Resources\Description as DescriptionResource;
use App\Http\Resources\DescriptionCollection;

class DescriptionController extends Controller
{
    public function index()
    {
        return new DescriptionCollection(Description::where('is_deleted', '=', '0')->orderBy('id', 'ASC')->paginate(5));
    }

    public function by_internship_id($id)
    {
        return new DescriptionCollection(Description::where('internship_id', '=', $id)->where('is_deleted', '=', '0')->orderBy('description', 'ASC')->paginate(5));
    }

    public function search($value,$id)
    {
        return new DescriptionCollection(Description::where('internship_id','=',$id)->where('is_deleted','=','0')->where('description', 'LIKE', '%'.$value.'%')->orderBy('id', 'ASC')->paginate(5));
        
        
    }

    public function show($id)
    {
        return new DescriptionResource(Description::findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'internship_id' => 'required|max:255',
            'description' => 'required|max:255',

        ]);
        
        $description = Description::create($request->all());
        $description->save();

        return (new DescriptionResource($description))
                ->response()
                ->setStatusCode(201);
    
    }

    public function delete($id)
    {
        $description = Description::findOrFail($id);
        $description->is_deleted="1";
        
        $description->save();

        return response()->json(null, 204);
        // return new DescriptionCollection(Description::all());
    }

        
    public function update(Request $request, $id)
        {
            $this->validate($request, [
                'internship_id' => 'required|max:255',
                'description' => 'required|max:255',
            ]);
    
            $description = Description::findOrFail($id);
            
            $description->internship_id = request('internship_id');
            $description->description = request('description');
            $description->save();
    
            return response()->json([
                'message' => 'Description updated successfully!'
            ], 200);
        }
}
