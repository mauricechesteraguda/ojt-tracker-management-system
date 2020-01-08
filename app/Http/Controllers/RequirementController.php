<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Requirement;
use App\RequirementCategory;
use App\Http\Resources\Requirement as RequirementResource;
use App\Http\Resources\RequirementCollection;

class RequirementController extends Controller
{
    public function index($id)
    {
        $user_id = Auth::user()->id;
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
                    //pass
                }else{
                    $requirement = Requirement::create(['requirement_category_id' => $rc->id,
                    'internship_id'=> $id,
                    'updated_by'=>$user_id]);
                    $requirement->save();
                }

                
            }

        return new RequirementCollection(Requirement::where('is_deleted', '=', '0')->where('internship_id','=',$id)->whereHas('requirement_category', function($q){
            $q->where('is_deleted','=','0')->orderBy('name', 'ASC');
        })->paginate(5));
    }
    public function search($value,$id)
    {
        return new RequirementCollection(Requirement::where('is_deleted','=','0')->where('internship_id','=',$id)->whereHas('requirement_category', function($q)use($value){
            $q->where('name', 'LIKE', '%'.$value.'%')->where('is_deleted','=','0')->orderBy('name', 'ASC');
        })->paginate(5));
        
        
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
