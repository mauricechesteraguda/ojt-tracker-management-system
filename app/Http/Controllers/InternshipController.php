<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Requirement;
use App\RequirementCategory;
use App\Internship;
use App\Http\Resources\Internship as InternshipResource;
use App\Http\Resources\InternshipCollection;

class InternshipController extends Controller
{
    public function index()
    {
        return new InternshipCollection(Internship::where('is_deleted', '=', '0')->orderBy('id', 'ASC')->paginate(5));
    }
    public function search($value)
    {
        return new InternshipCollection(Internship::where('is_deleted','=','0')->whereHas('User', function($q)use($value){
            $q->where('first_name', 'LIKE', '%'.$value.'%')->orWhere('last_name', 'LIKE', '%'.$value.'%')->orderBy('last_name', 'ASC');
        })->orWhereHas('Company', function($q)use($value){
            $q->where('name', 'LIKE', '%'.$value.'%')->orWhere('address', 'LIKE', '%'.$value.'%')->orWhere('country', 'LIKE', '%'.$value.'%')->orWhere('city', 'LIKE', '%'.$value.'%')->orderBy('name', 'ASC');
        })->paginate(5));
        
        
    }

    public function show($id)
    {
        return new InternshipResource(Internship::findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|max:255',
            'company_id' => 'required|max:255',

        ]);
        
        $internship = Internship::create($request->all());
        $internship->save();

        if ($internship->id) {

            $requirement_categories = RequirementCategory::where('is_deleted', '=', '0')->orderBy('name', 'ASC')->get();
            foreach($requirement_categories as $r){
                $requirement = Requirement::create(['requirement_category_id' => $r->id,
                'internship_id'=> $internship->id,
                'updated_by'=>$request->updated_by]);
                $requirement->save();
            }
            
        }

        

        return (new InternshipResource($internship))
                ->response()
                ->setStatusCode(201);
    
    }

    public function delete($id)
    {
        $internship = Internship::findOrFail($id);
        $internship->is_deleted="1";
        
        $internship->save();

        return response()->json(null, 204);
        // return new InternshipCollection(Internship::all());
    }

        
    public function update(Request $request, $id)
        {
            $this->validate($request, [
                'start_date' => 'required|max:255',
            ]);
    
            $internship = Internship::findOrFail($id);
            
            $internship->start_date = request('start_date');
            $internship->representative = request('representative');
            $internship->student_position = request('student_position');
            $internship->is_approved = request('is_approved');
            $internship->status = request('status');
            $internship->comment = request('comment');
            $internship->updated_by = request('updated_by');
            $internship->save();
    
            return response()->json([
                'message' => 'Internship updated successfully!'
            ], 200);
        }
}
