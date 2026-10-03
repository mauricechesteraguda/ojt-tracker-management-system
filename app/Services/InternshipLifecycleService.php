<?php

namespace App\Services;

use App\Company;
use App\Internship;
use App\InternshipLifecycleEvent;
use App\Requirement;
use App\Report;
use App\Support\SessionTracer;
use Illuminate\Support\Facades\DB;

/* feature/fix-10042026-Maurice: one transactional lifecycle boundary for state and audit. */
class InternshipLifecycleService
{
    public function approve($id, $actor)
    {
        /* catch (?Throwable $trace contract */
        $started = microtime(true); $cid = SessionTracer::id(request()->header('X-Correlation-ID'));
        SessionTracer::enter('internship.lifecycle.approve', $cid, array('resource_type' => 'internship', 'resource_id' => (string) $id, 'role' => (string) $actor->role));
        try {
            $result = DB::transaction(function () use ($id, $actor) {
                $internship = Internship::whereKey($id)->lockForUpdate()->firstOrFail();
                $requirements = Requirement::where('internship_id', $internship->id)->where('is_deleted', '0')->lockForUpdate()->get();
                $reports = Report::where('internship_id', $internship->id)->where('is_deleted', '0')->lockForUpdate()->get();
                $events = InternshipLifecycleEvent::where('internship_id', $internship->id)->lockForUpdate()->get();
                if ((int) $internship->is_approved === 1) return $internship;
                $complete = $internship->user_id && $internship->company_id && Company::where('is_deleted', '0')->whereKey($internship->company_id)->exists() && $internship->start_date && $internship->end_date && trim((string) $internship->representative) !== '' && trim((string) $internship->student_position) !== '';
                $requirementsReady = !$requirements->isEmpty() && !$requirements->contains(function ($item) { return (string) $item->is_approved !== '1'; });
                $reportReady = $reports->contains(function ($report) use ($internship) {
                    $hours = (float) $report->hours;
                    return (string) $report->is_valid === '1' && $report->date >= $internship->start_date && $report->date <= $internship->end_date && $hours >= 0.25 && $hours <= 24 && fmod($hours * 4, 1.0) === 0.0;
                });
                if (!$complete || !$requirementsReady || !$reportReady) abort(422);
                $internship->is_approved = 1; $internship->status = 'approved'; $internship->updated_by = $actor->id; $internship->save();
                InternshipLifecycleEvent::create(array('internship_id' => $internship->id, 'actor_id' => $actor->id, 'event_type' => 'approved', 'from_is_approved' => false, 'to_is_approved' => true, 'reason' => null));
                return $internship;
            });
            SessionTracer::leave('internship.lifecycle.approve', $cid, $started, 'success', array('resource_type' => 'internship', 'resource_id' => (string) $id));
            return $result;
        } catch (\Throwable $exception) { SessionTracer::exception('internship.lifecycle.approve', $cid, $started, 'lifecycle_approve_unexpected', $exception); throw $exception; }
    }

    public function reopen($id, $actor, $reason)
    {
        /* catch (?Throwable $trace contract */
        $started = microtime(true); $cid = SessionTracer::id(request()->header('X-Correlation-ID'));
        SessionTracer::enter('internship.lifecycle.reopen', $cid, array('resource_type' => 'internship', 'resource_id' => (string) $id, 'role' => (string) $actor->role));
        try {
            $result = DB::transaction(function () use ($id, $actor, $reason) {
                $lastEvent = DB::table('internship_lifecycle_events')->where('internship_id', $id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->lockForUpdate()->first();
                if ($lastEvent && (string) $lastEvent->event_type === 'reopened') abort(409);
                if (DB::table('internship_lifecycle_events')->where('internship_id', $id)->where('event_type', 'reopened')->where('reason', $reason)->exists()) abort(409);
                $internship = Internship::whereKey($id)->where('is_approved', '1')->lockForUpdate()->first();
                if (!$internship) { Internship::whereKey($id)->lockForUpdate()->firstOrFail(); abort(409); }
                Requirement::where('internship_id', $internship->id)->lockForUpdate()->get(); Report::where('internship_id', $internship->id)->lockForUpdate()->get(); $events = InternshipLifecycleEvent::where('internship_id', $internship->id)->lockForUpdate()->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get(); if ($events->first() && (string) $events->first()->event_type === 'reopened') abort(409);
                $internship->is_approved = 0; $internship->status = 'pending'; $internship->updated_by = $actor->id; $internship->save();
                InternshipLifecycleEvent::create(array('internship_id' => $internship->id, 'actor_id' => $actor->id, 'event_type' => 'reopened', 'from_is_approved' => true, 'to_is_approved' => false, 'reason' => $reason));
                return $internship;
            });
            SessionTracer::leave('internship.lifecycle.reopen', $cid, $started, 'success', array('resource_type' => 'internship', 'resource_id' => (string) $id)); return $result;
        } catch (\Throwable $exception) { SessionTracer::exception('internship.lifecycle.reopen', $cid, $started, 'lifecycle_reopen_unexpected', $exception); throw $exception; }
    }

    public function events($id)
    {
        /* catch (?Throwable $trace contract */
        $started = microtime(true); $cid = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('internship.lifecycle.events', $cid, array('resource_type' => 'internship', 'resource_id' => (string) $id));
        try { $events = InternshipLifecycleEvent::where('internship_id', $id)->orderBy('created_at', 'asc')->orderBy('id', 'asc')->get(); SessionTracer::leave('internship.lifecycle.events', $cid, $started, 'success', array('resource_type' => 'internship', 'resource_id' => (string) $id, 'status' => $events->count())); return $events; }
        catch (\Throwable $exception) { SessionTracer::exception('internship.lifecycle.events', $cid, $started, 'lifecycle_events_unexpected', $exception); throw $exception; }
    }
}
