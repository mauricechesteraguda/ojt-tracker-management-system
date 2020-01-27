<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Requirement;
use App\RequirementCategory;
use App\Internship;
use App\Company;
use App\Http\Resources\Internship as InternshipResource;
use App\Http\Resources\InternshipCollection;

use App\Classes\batsu_api;

class InternshipController extends Controller
{
    public function index()
    {
        $current_user = \Auth::user();
        $sr_code = $current_user->sr_code;
        if ($current_user->role == 'student') {
            return new InternshipCollection(Internship::where('is_deleted', '=', '0')->whereHas('User', function($q)use($sr_code){
                $q->where('sr_code', '=', $sr_code)->orderBy('last_name', 'ASC');
            })->orderBy('id', 'ASC')->paginate(5));
        }
        return new InternshipCollection(Internship::where('is_deleted', '=', '0')->orderBy('id', 'ASC')->paginate(5));
    }
    public function search($value)
    {
        $current_user = \Auth::user();
        $sr_code = $current_user->sr_code;
        if (!$current_user->role == 'student') {
            return new InternshipCollection(Internship::where('is_deleted', '=', '0')->whereHas('User', function ($q) use ($value) {
                $q->where('first_name', 'LIKE', '%'.$value.'%')->orWhere('last_name', 'LIKE', '%'.$value.'%')->orderBy('last_name', 'ASC');
            })->orWhereHas('Company', function ($q) use ($value) {
                $q->where('name', 'LIKE', '%'.$value.'%')->orWhere('address', 'LIKE', '%'.$value.'%')->orWhere('country', 'LIKE', '%'.$value.'%')->orWhere('city', 'LIKE', '%'.$value.'%')->orderBy('name', 'ASC');
            })->paginate(5));
        }
        return new InternshipCollection(Internship::where('is_deleted', '=', '0')->whereHas('User', function ($q) use ($value,$sr_code) {
            $q->where('sr_code', '=', $sr_code);
        })->WhereHas('Company', function ($q) use ($value) {
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

        $user = \Auth::user();
        
        $api = new batsu_api('02f56c7e26b713ab877cff2fc5c3ea8a');
        $schoolyears = json_decode($api->fetch_schoolyear(),true);
        $semesters = json_decode($api->fetch_semester(),true);
        krsort($semesters);
        foreach ($schoolyears as $sy) {
            # code...
            foreach ($semesters as $sem) {
                $user_enrollment_record = json_decode($api->fetch_enrollment_records($sy,$sem,$user->sr_code),true);
                if ($user_enrollment_record) {
                    break 2;
                }
            }

        }

        $internship = Internship::create($request->all());
        $internship->schoolyear = $user_enrollment_record[0]['schoolyear'];
        $internship->course_code = $user_enrollment_record[0]['coursecode'];
        $internship->semester = $user_enrollment_record[0]['semester'];
        $internship->campus = $user_enrollment_record[0]['campus'];
        $internship->college_code = $user_enrollment_record[0]['collegecode'];

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

    public function visit_company(Request $request, $id)
        {
            $this->validate($request, [
                'year' => 'required|max:255',
            ]);
    
            $company = Company::find($id);
            if ($company) {
                $internships = Internship::where('start_date','LIKE','%'.$request->year.'%')->where('is_deleted','=','0')->where('company_id', '=',$company->id)->whereNotNull('cluster_id')->get();
            
                foreach ($internships as $internship) {
                    $internship->date_visited = request('date_visited');
                    $internship->comment = request('comment');
                    $internship->updated_by = request('updated_by');
                    $internship->save();
                }
                
        
                return response()->json([
                    'message' => 'Internship updated successfully!'
                ], 200);
            }
            
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
