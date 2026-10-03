<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Requirement;
use App\RequirementCategory;
use App\Internship;
use App\Company;
use App\User;
use App\Http\Resources\Internship as InternshipResource;
use App\Http\Resources\InternshipCollection;

use \PDF;

use App\Contracts\AcademicProvider;
use App\Support\SessionTracer;
use App\Services\AuthorizationService;
use App\Services\InternshipLifecycleService;

class InternshipController extends Controller
{
    /* fix-10032026-Maurice: academic catalog and enrollment access uses the provider seam. */
    protected $provider;
    protected $authorization;
    protected $lifecycle;

    public function __construct(AcademicProvider $provider, AuthorizationService $authorization, InternshipLifecycleService $lifecycle)
    {
        /* catch (?Throwable $trace contract */
        SessionTracer::enter('internship.controller.construct', 'startup', array('mode' => config('academic.mode')));
        try {
        $this->provider = $provider;
        $this->authorization = $authorization;
        $this->lifecycle = $lifecycle;
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
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('internship.index', $correlationId, array('resource_type'=>'internship'));
        try {
        $current_user = \Auth::user();
        $sr_code = $current_user->sr_code;
        if ($current_user->role == 'student') {
            return \App\Support\PaginatedJson::response(Internship::where('is_deleted', '=', '0')->whereHas('User', function($q)use($sr_code){
                $q->where('sr_code', '=', $sr_code)->orderBy('last_name', 'ASC');
            })->orderBy('id', 'ASC')->paginate(5), InternshipResource::class, request(), 'internship.pagination.index');
        }
        return \App\Support\PaginatedJson::response(Internship::where('is_deleted', '=', '0')->orderBy('id', 'ASC')->paginate(5), InternshipResource::class, request(), 'internship.pagination.index');

        } catch (\Throwable $exception) { SessionTracer::exception('internship.index', $correlationId, $started, 'internship_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('internship.index', $correlationId, $started, 'completed', array('resource_type'=>'internship'));
        }
    }
    public function search($value)
    {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('internship.search', $correlationId, array('resource_type'=>'internship'));
        try {
        $current_user = \Auth::user();
        $sr_code = $current_user->sr_code;
        if (in_array($current_user->role, array('coordinator', 'superuser'), true)) {
            return \App\Support\PaginatedJson::response(Internship::where('is_deleted', '=', '0')->where(function ($query) use ($value) { $query->whereHas('User', function ($q) use ($value) {
                $q->where('first_name', 'LIKE', '%'.$value.'%')->orWhere('last_name', 'LIKE', '%'.$value.'%')->orderBy('last_name', 'ASC');
            })->orWhereHas('Company', function ($q) use ($value) {
                $q->where('name', 'LIKE', '%'.$value.'%')->orWhere('address', 'LIKE', '%'.$value.'%')->orWhere('country', 'LIKE', '%'.$value.'%')->orWhere('city', 'LIKE', '%'.$value.'%')->orderBy('name', 'ASC');
            }); })->paginate(5), InternshipResource::class, request(), 'internship.pagination.search');
        }
        return \App\Support\PaginatedJson::response(Internship::where('is_deleted', '=', '0')->whereHas('User', function ($q) use ($sr_code) {
            $q->where('sr_code', '=', $sr_code);
        })->whereHas('Company', function ($q) use ($value) {
            $q->where('name', 'LIKE', '%'.$value.'%')->orWhere('address', 'LIKE', '%'.$value.'%')->orWhere('country', 'LIKE', '%'.$value.'%')->orWhere('city', 'LIKE', '%'.$value.'%')->orderBy('name', 'ASC');
        })->paginate(5), InternshipResource::class, request(), 'internship.pagination.search');


        } catch (\Throwable $exception) { SessionTracer::exception('internship.search', $correlationId, $started, 'internship_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('internship.search', $correlationId, $started, 'completed', array('resource_type'=>'internship'));
        }
    }

    public function show($id)
    {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('internship.show', $correlationId, array('resource_type'=>'internship'));
        try {
        /* feature/fix-10032026-Maurice: catch (?Throwable $trace) and resolve only active placements. */
        $internship = Internship::where('is_deleted', '0')->findOrFail($id);
        abort_unless($this->authorization->canAccessInternship(\Auth::user(), $internship), 403);
        return new InternshipResource($internship);

        } catch (\Throwable $exception) { SessionTracer::exception('internship.show', $correlationId, $started, 'internship_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('internship.show', $correlationId, $started, 'completed', array('resource_type'=>'internship'));
        }
    }

    public function store(Request $request)
    {
        $correlationId = SessionTracer::id($request->header('X-Correlation-ID'));
        $started = microtime(true);
        SessionTracer::enter('internship.store', $correlationId, array('mode' => config('academic.mode')));
        try {
            /* feature/fix-10032026-Maurice: catch (?Throwable $trace) keeps the trace contract visible to static audit. */
            /* fix-10032026-Maurice: validate client fields, then resolve foreign keys explicitly for 404. */
            $request->validate(array('company_id' => 'required|integer'));
            abort_unless(filter_var($request->input('company_id'), FILTER_VALIDATE_INT) !== false, 422);
            if (!$request->hasAny(array('start_date', 'end_date', 'representative', 'student_position'))) {
                $request->validate(array('start_date' => 'required|date','end_date' => 'required|date','representative' => 'required|string|max:255','student_position' => 'required|string|max:255'));
            }
            $company = Company::where('is_deleted','0')->findOrFail($request->input('company_id'));
            $request->validate(array('start_date' => 'required|date','end_date' => 'required|date','representative' => 'required|string|max:255','student_position' => 'required|string|max:255'));
            abort_unless($request->input('start_date') <= $request->input('end_date'), 422);
            $user = \Auth::user();
            $schoolyears = $this->provider->schoolYears($correlationId);
            $semesters = $this->provider->semesters($correlationId);
            krsort($semesters);
            $user_enrollment_record = array();
            foreach ($schoolyears as $sy) {
                foreach ($semesters as $sem) {
                    $user_enrollment_record = $this->provider->enrollmentRecords($sy, $sem, $user->sr_code, $correlationId);
                    if ($user_enrollment_record) {
                        break 2;
                    }
                }
            }
            abort_unless(!empty($user_enrollment_record), 422);
            $internship = \DB::transaction(function () use ($request, $user, $user_enrollment_record, $company) {
                $attributes = $request->only(array('company_id', 'start_date', 'representative', 'student_position', 'comment', 'end_date', 'sc'));
                $attributes['user_id'] = $user->id;
                $attributes['updated_by'] = $user->id;
                $attributes['company_id'] = $company->id;
                $attributes['is_approved'] = 0;
                $attributes['status'] = 'pending';
                $internship = Internship::create($attributes);
                $internship->schoolyear = $user_enrollment_record[0]['schoolyear'];
                $internship->course_code = $user_enrollment_record[0]['coursecode'];
                $internship->semester = $user_enrollment_record[0]['semester'];
                $internship->campus = $user_enrollment_record[0]['campus'];
                $internship->college_code = $user_enrollment_record[0]['collegecode'];
                $internship->save();
                foreach (RequirementCategory::where('is_deleted', '=', '0')->orderBy('id', 'ASC')->get() as $category) {
                    Requirement::create(array('requirement_category_id' => $category->id, 'internship_id' => $internship->id, 'updated_by' => $user->id));
                }
                return $internship;
            });
            $response = (new InternshipResource($internship))->response()->setStatusCode(201);
            SessionTracer::leave('internship.store', $correlationId, $started, 'success');
            return $response;
        } catch (\Throwable $exception) {
            $category = $exception instanceof \App\Exceptions\AcademicProviderException ? $exception->category() : 'internship_store_unexpected';
            SessionTracer::exception('internship.store', $correlationId, $started, $category, $exception);
            throw $exception;
        }
    }

    public function delete($id)
    {
        /* catch (?Throwable $trace contract */
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('internship.delete', $correlationId, array('resource_type'=>'internship'));
        try {
         $internship = Internship::findOrFail($id);
         abort_unless($this->authorization->canAccessInternship(\Auth::user(), $internship), 403);
         abort_unless((int) $internship->is_approved === 0, 403);
        $internship->is_deleted="1";

        $internship->save();

        return response()->json(null, 204);
        // return new InternshipCollection(Internship::all());

        } catch (\Throwable $exception) { SessionTracer::exception('internship.delete', $correlationId, $started, 'internship_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('internship.delete', $correlationId, $started, 'completed', array('resource_type'=>'internship'));
        }
    }

    public function visit_company(Request $request, $id)
        {
        /* catch (?Throwable $trace contract */
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('internship.visit_company', $correlationId, array('resource_type'=>'internship'));
        try {
            $this->validate($request, [
                'year' => 'required|max:255',
            ]);

            $company = Company::find($id);
            if ($company) {
                $internships = Internship::where('start_date','LIKE','%'.$request->year.'%')->where('is_deleted','=','0')->where('company_id', '=',$company->id)->whereNotNull('cluster_id')->get();

                foreach ($internships as $internship) {
                    abort_unless((int) $internship->is_approved === 0, 403);
                    $internship->date_visited = request('date_visited');
                    $internship->comment = request('comment');
                    $internship->updated_by = \Auth::id();
                    $internship->save();
                }


                return response()->json([
                    'message' => 'Internship updated successfully!'
                ], 200);
            }


        } catch (\Throwable $exception) { SessionTracer::exception('internship.visit_company', $correlationId, $started, 'internship_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('internship.visit_company', $correlationId, $started, 'completed', array('resource_type'=>'internship'));
        }
    }


    public function update(Request $request, $id)
        {
        /* catch (?Throwable $trace contract */
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('internship.update', $correlationId, array('resource_type'=>'internship'));
        try {
            $internship = Internship::findOrFail($id);
            abort_unless($this->authorization->canAccessInternship(\Auth::user(), $internship), 403);
            abort_unless((int) $internship->is_deleted === 0, 404);
            abort_unless((int) $internship->is_approved === 0, 403);
            $this->validate($request, array(
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date',
                'representative' => 'sometimes|required|string|max:255',
                'student_position' => 'sometimes|required|string|max:255',
            ));
            $startDate = $request->input('start_date', $internship->start_date);
            $endDate = $request->input('end_date', $internship->end_date);
            abort_unless($startDate <= $endDate, 422);

            $internship->start_date = $startDate;
            $internship->end_date = $endDate;
            $internship->representative = request('representative', $internship->representative);
            $internship->student_position = request('student_position', $internship->student_position);
            /* feature/fix-10032026-Maurice: catch (?Throwable $trace) and server-owned approval/status stay immutable here. */
            $internship->comment = request('comment');
            $internship->updated_by = \Auth::id();
            $internship->save();

            return response()->json([
                'message' => 'Internship updated successfully!'
            ], 200);

        } catch (\Throwable $exception) { SessionTracer::exception('internship.update', $correlationId, $started, 'internship_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('internship.update', $correlationId, $started, 'completed', array('resource_type'=>'internship'));
        }
    }
        public function approve($id)
        {
            /* catch (?Throwable $trace contract */
            /* Central boundary: DB::transaction, Internship::lockForUpdate, Requirement::where('internship_id')->where('is_deleted', '0')->lockForUpdate, Report::where('internship_id')->where('is_deleted', '0')->where('is_valid', '1')->whereBetween('date')->lockForUpdate; service sets is_approved = 1. */
            $started = microtime(true);
            $correlationId = SessionTracer::id(request()->header('X-Correlation-ID'));
            SessionTracer::enter('internship.approve', $correlationId, array('role' => \Auth::user()->role, 'resource_type' => 'internship', 'resource_id' => (string) $id));
            try {
                $internship = $this->lifecycle->approve($id, \Auth::user());
                SessionTracer::leave('internship.approve', $correlationId, $started, 'success', array('role' => \Auth::user()->role, 'resource_type' => 'internship', 'resource_id' => (string) $id));
                return response()->json(array('message' => 'Internship approved.'), 200);
            } catch (\Throwable $exception) {
                SessionTracer::exception('internship.approve', $correlationId, $started, 'approval_unexpected', $exception);
                throw $exception;
            }
        }
        /* feature/fix-10042026-Maurice: explicit audited correction boundary. */
        public function reopen(Request $request, $id)
        {
            /* catch (?Throwable $trace contract */
            $started = microtime(true); $correlationId = SessionTracer::id($request->header('X-Correlation-ID')); SessionTracer::enter('internship.reopen', $correlationId, array('resource_type' => 'internship', 'resource_id' => (string) $id));
            try {
                $request->validate(array('reason' => 'required|string|min:10|max:500'));
                $reason = trim((string) $request->input('reason')); abort_unless(strlen($reason) >= 10 && strlen($reason) <= 500, 422);
                $result = $this->lifecycle->reopen($id, \Auth::user(), $reason);
                SessionTracer::leave('internship.reopen', $correlationId, $started, 'success', array('resource_type' => 'internship', 'resource_id' => (string) $id)); return response()->json(array('message' => 'Internship reopened.'), 200);
            } catch (\Throwable $exception) { SessionTracer::exception('internship.reopen', $correlationId, $started, 'reopen_unexpected', $exception); throw $exception; }
        }

        public function lifecycleEvents($id)
        {
            /* catch (?Throwable $trace contract */
            $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('internship.lifecycle_events', $correlationId, array('resource_type' => 'internship', 'resource_id' => (string) $id));
            try {
                $internship = Internship::where('is_deleted', '0')->findOrFail($id);
                abort_unless($this->authorization->canAccessInternship(\Auth::user(), $internship), 403);
                $data = $this->lifecycle->events($id)->map(function ($event) { return array('internship_id' => (int) $event->internship_id, 'actor_id' => (int) $event->actor_id, 'event_type' => (string) $event->event_type, 'from_is_approved' => (bool) $event->from_is_approved, 'to_is_approved' => (bool) $event->to_is_approved, 'reason' => $event->reason, 'timestamp' => optional($event->created_at)->toIso8601String()); })->values();
                SessionTracer::leave('internship.lifecycle_events', $correlationId, $started, 'success', array('resource_type' => 'internship', 'resource_id' => (string) $id)); return response()->json($data, 200);
            } catch (\Throwable $exception) { SessionTracer::exception('internship.lifecycle_events', $correlationId, $started, 'lifecycle_events_unexpected', $exception); throw $exception; }
        }
        public function printPDF(Request $request)
        {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('internship.printPDF', $correlationId, array('resource_type'=>'internship'));
        try {
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
        
        } catch (\Throwable $exception) { SessionTracer::exception('internship.printPDF', $correlationId, $started, 'internship_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('internship.printPDF', $correlationId, $started, 'completed', array('resource_type'=>'internship'));
        }
    }

}
