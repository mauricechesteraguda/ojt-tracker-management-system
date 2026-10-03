<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Requirement;
use App\RequirementCategory;
use App\Internship;
use App\Company;
use App\Http\Resources\Internship as InternshipResource;
use App\Http\Resources\InternshipCollection;

use \PDF;

use App\Contracts\AcademicProvider;
use App\Support\SessionTracer;

class InternshipController extends Controller
{
    /* fix-10032026-Maurice: academic catalog and enrollment access uses the provider seam. */
    protected $provider;

    public function __construct(AcademicProvider $provider)
    {
        SessionTracer::enter('internship.controller.construct', 'startup', array('mode' => config('academic.mode')));
        try {
        $this->provider = $provider;
        SessionTracer::leave('internship.controller.construct', 'startup', microtime(true), 'success');
        } catch (\Throwable $exception) {
            SessionTracer::exception('internship.controller.construct', 'startup', microtime(true), 'controller_unexpected', $exception);
            throw $exception;
        }
    }

    public function majors($course){
        $started = microtime(true); $correlationId = SessionTracer::id(); SessionTracer::enter('internship.majors', $correlationId, array('mode' => config('academic.mode'))); try { $result = $this->catalogResponse('majors', function ($id) use ($course) { return $this->provider->majors($course, $id); }); SessionTracer::leave('internship.majors', $correlationId, $started, 'success'); return $result; } catch (\Throwable $exception) { SessionTracer::exception('internship.majors', $correlationId, $started, 'academic_provider_unexpected', $exception); throw $exception; }

    }
    public function courses($college){
        $started = microtime(true); $correlationId = SessionTracer::id(); SessionTracer::enter('internship.courses', $correlationId, array('mode' => config('academic.mode'))); try { $result = $this->catalogResponse('courses', function ($id) use ($college) { return $this->provider->courses($college, $id); }); SessionTracer::leave('internship.courses', $correlationId, $started, 'success'); return $result; } catch (\Throwable $exception) { SessionTracer::exception('internship.courses', $correlationId, $started, 'academic_provider_unexpected', $exception); throw $exception; }

    }
    public function colleges(){
        $started = microtime(true); $correlationId = SessionTracer::id(); SessionTracer::enter('internship.colleges', $correlationId, array('mode' => config('academic.mode'))); try { $result = $this->catalogResponse('colleges', function ($id) { return $this->provider->colleges($id); }); SessionTracer::leave('internship.colleges', $correlationId, $started, 'success'); return $result; } catch (\Throwable $exception) { SessionTracer::exception('internship.colleges', $correlationId, $started, 'academic_provider_unexpected', $exception); throw $exception; }

    }
    public function semesters(){
        $started = microtime(true); $correlationId = SessionTracer::id(); SessionTracer::enter('internship.semesters', $correlationId, array('mode' => config('academic.mode'))); try { $result = $this->catalogResponse('semesters', function ($id) { return $this->provider->semesters($id); }); SessionTracer::leave('internship.semesters', $correlationId, $started, 'success'); return $result; } catch (\Throwable $exception) { SessionTracer::exception('internship.semesters', $correlationId, $started, 'academic_provider_unexpected', $exception); throw $exception; }

    }
    public function campuses(){
        $started = microtime(true); $correlationId = SessionTracer::id(); SessionTracer::enter('internship.campuses', $correlationId, array('mode' => config('academic.mode'))); try { $result = $this->catalogResponse('campuses', function ($id) { return $this->provider->campuses($id); }); SessionTracer::leave('internship.campuses', $correlationId, $started, 'success'); return $result; } catch (\Throwable $exception) { SessionTracer::exception('internship.campuses', $correlationId, $started, 'academic_provider_unexpected', $exception); throw $exception; }

    }
    public function schoolyears(){
        $started = microtime(true); $correlationId = SessionTracer::id(); SessionTracer::enter('internship.schoolyears', $correlationId, array('mode' => config('academic.mode'))); try { $result = $this->catalogResponse('schoolyears', function ($id) { return $this->provider->schoolYears($id); }); SessionTracer::leave('internship.schoolyears', $correlationId, $started, 'success'); return $result; } catch (\Throwable $exception) { SessionTracer::exception('internship.schoolyears', $correlationId, $started, 'academic_provider_unexpected', $exception); throw $exception; }

    }

    protected function catalogResponse($operation, callable $callback)
    {
        $correlationId = SessionTracer::id();
        $started = microtime(true);
        SessionTracer::enter('internship.catalog.' . $operation, $correlationId, array('mode' => config('academic.mode')));
        try {
            $result = call_user_func($callback, $correlationId);
            SessionTracer::leave('internship.catalog.' . $operation, $correlationId, $started, 'success');
            return response()->json($result, 200);
        } catch (\Throwable $exception) {
            $category = $exception instanceof \App\Exceptions\AcademicProviderException ? $exception->category() : 'academic_provider_unexpected';
            SessionTracer::exception('internship.catalog.' . $operation, $correlationId, $started, $category, $exception);
            throw $exception;
        }
    }
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
        $correlationId = SessionTracer::id($request->header('X-Correlation-ID'));
        $started = microtime(true);
        SessionTracer::enter('internship.store', $correlationId, array('mode' => config('academic.mode')));
        $request->validate([
            'user_id' => 'required|max:255',
            'company_id' => 'required|max:255',

        ]);

        $user = \Auth::user();
        
        try {
            $schoolyears = $this->provider->schoolYears($correlationId);
            $semesters = $this->provider->semesters($correlationId);
            krsort($semesters);
            foreach ($schoolyears as $sy) {
                foreach ($semesters as $sem) {
                    $user_enrollment_record = $this->provider->enrollmentRecords($sy, $sem, $user->sr_code, $correlationId);
                    if ($user_enrollment_record) {
                        break 2;
                    }
                }
            }
        } catch (\Throwable $exception) {
            $category = $exception instanceof \App\Exceptions\AcademicProviderException ? $exception->category() : 'academic_provider_unexpected';
            SessionTracer::exception('internship.store', $correlationId, $started, $category, $exception);
            throw $exception;
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

        

        $response = (new InternshipResource($internship))
                ->response()
                ->setStatusCode(201);
        SessionTracer::leave('internship.store', $correlationId, $started, 'success');
        return $response;
    
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
            $internship->end_date = request('end_date');
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
        public function printPDF(Request $request)
        {
            $campus = request('campus');
            $schoolyear = request('schoolyear');
            $semester = request('semester');
            $college = request('college');
            $course = request('course');

            // $campus = 'ALANGILAN';
            // $schoolyear = '2019-2020';
            // $semester = 'SECOND';
            // $college = 'CICS';
            // $course =  'BSCOSCI';

            $internships_result = Internship::join('users', 'users.id', '=', 'internships.user_id') // or leftJoin
            ->orderBy('users.first_name', 'asc')
            ->where('is_deleted','=','0')->where('campus', '=',$campus)->where('schoolyear', '=',$schoolyear)->where('semester', '=',$semester)->where('college_code', '=',$college)->where('course_code', '=',$course)->get(['internships.*']); 

            $final_internships = [];
            $internships_collection = [];
            $counter = 0;            
            foreach ($internships_result as $i) {
                if ($counter < 10) {
                    array_push($internships_collection,$i);
                    $counter++;
                }else{
                    array_push($final_internships,$internships_collection);
                    $internships_collection = [];
                    $counter=0;
                }
            }
            if ($internships_collection) {
                array_push($final_internships,$internships_collection);
            }
            // $a=0;
            // while ($a <= 3) {
            //     array_push($final_internships,$internships_collection);
            //     $a++;
            // }

            $data = ['campus' => $campus,
            'schoolyear' => $schoolyear,
            'semester' => $semester,
            'college' => $college,
            'course' => $course,
            'final_internships' => $final_internships,
        ];

        $pdf = \App::make('dompdf.wrapper');
        /* Careful: use "enable_php" option only with local html & script tags you control.
        used with remote html or scripts is a major security problem (remote php injection) */
        $pdf->getDomPDF()->set_option("isPhpEnabled", true)->set_paper('legal', 'landscape');

            $pdf->loadView('pdf_view', $data);  
            return $pdf->stream();
        }
        
}
