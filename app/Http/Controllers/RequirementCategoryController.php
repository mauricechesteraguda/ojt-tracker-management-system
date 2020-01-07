<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\RequirementCategory;
use App\Http\Resources\RequirementCategory as RequirementCategoryResource;
use App\Http\Resources\RequirementCategoryCollection;

class RequirementCategoryController extends Controller
{
    public function index()
    {
        return new RequirementCategoryCollection(RequirementCategory::where('is_deleted', '=', '0')->orderBy('id', 'ASC')->paginate(5));
    }
    public function search($value)
    {
        return new RequirementCategoryCollection(RequirementCategory::where('is_deleted','=','0')->where('name', 'LIKE', '%'.$value.'%')->orderBy('name', 'ASC')->paginate(5));
        
        
    }

    public function show($id)
    {
        return new RequirementCategoryResource(RequirementCategory::findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',

        ]);

        $requirement_category = RequirementCategory::create($request->all());

        try {
            $fileExtension = $request->file('file')->getClientOriginalExtension();

            $path = $request->file->move(public_path() . '/uploads',date('mdYHis') . uniqid() . '.' . $fileExtension);
            
            $new_path = explode("public",$path)[1];
    
            $requirement_category->file = $new_path;
    
        } catch (\Throwable $th) {
            //throw $th;
        }
    

        $requirement_category->save();

        return (new RequirementCategoryResource($requirement_category))
                ->response()
                ->setStatusCode(201);
    
    }

    public function delete($id)
    {
        $requirement_category = RequirementCategory::findOrFail($id);
        $requirement_category->is_deleted="1";
        
        $requirement_category->save();

        return response()->json(null, 204);
        // return new RequirementCategoryCollection(RequirementCategory::all());
    }

        
    public function update(Request $request, $id)
        {
            $this->validate($request, [
                'name' => 'required|max:255',
            ]);
    
            $requirement_category = RequirementCategory::findOrFail($id);
            
            $requirement_category->name = request('name');
            $requirement_category->file = request('file');
            $requirement_category->updated_by = request('updated_by');
            $requirement_category->save();
    
            return response()->json([
                'message' => 'Requirement Category updated successfully!'
            ], 200);
        }
}
