<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Requirement;
use App\RequirementCategory;
use App\Http\Resources\Requirement as RequirementResource;
use App\Http\Resources\RequirementCollection;

class RequirementController extends Controller
{
    public function index($id)
    {
        $user=auth()->user();
        $is_already_exist=false;
        $requirements = Requirement::where('is_deleted', '=', '0')->where('internship_id','=',$id)->orderBy('id', 'ASC')->get();

        $requirement_categories = RequirementCategory::where('is_deleted', '=', '0')->orderBy('name', 'ASC')->get();
            foreach($requirement_categories as $rc){
                $is_already_exist = false;
                foreach ($requirements as $rq) {
                    if ($rq->requirement_category_id == $rc->id) {
                        $is_already_exist = true;
                    break;
                    }else{
                        $is_already_exist = false;
                    }
                }

                if ($is_already_exist) {
                    break;
                }else{
                    $requirement = Requirement::create(['requirement_category_id' => $rc->id,
                    'internship_id'=> $id,
                    'updated_by'=>$user->id]);
                    $requirement->save();
                }

                
            }

        return new RequirementCollection(Requirement::where('is_deleted', '=', '0')->where('internship_id','=',$id)->orderBy('id', 'ASC')->paginate(5));
    }
    public function search($value,$id)
    {
        return new RequirementCollection(Requirement::where('is_deleted','=','0')->where('internship_id','=',$id)->whereHas('requirement_category', function($q)use($value){
            $q->where('name', 'LIKE', '%'.$value.'%')->where('is_deleted','=','0')->orderBy('name', 'ASC');
        })->paginate(5));
        
        
    }

    public function show($id)
    {
        return new RequirementResource(Requirement::findOrFail($id));
    }

    public function store(Request $request)
    {
        // $request->validate([
        //     'requirement_category_id' => 'required|max:255',
        //     'internship_id' => 'required|max:255',

        // ]);
        
        // $requirement = Requirement::create($request->all());
        // $requirement->save();

        // return (new RequirementResource($requirement))
        //         ->response()
        //         ->setStatusCode(201);
    
    }

    public function delete($id)
    {
        // $requirement = Requirement::findOrFail($id);
        // $requirement->is_deleted="1";
        
        // $requirement->save();

        // return response()->json(null, 204);
        // // return new RequirementCollection(Requirement::all());
    }

        
    public function update(Request $request, $id)
        {
            $this->validate($request, [
                'is_approved' => 'required|max:255',
            ]);
    
            $requirement = Requirement::findOrFail($id);

            $requirement->is_approved = request('is_approved');
            $requirement->updated_by = request('updated_by');
            $requirement->save();
    
            return response()->json([
                'message' => 'Requirement updated successfully!'
            ], 200);
        }
}
