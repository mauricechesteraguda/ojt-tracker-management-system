<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Support\SessionTracer;

/* feature/fix-10042026-Maurice: lifecycle events are append-only audit records. */
class InternshipLifecycleEvent extends Model
{
    protected $table = 'internship_lifecycle_events';

    protected $fillable = array('internship_id', 'actor_id', 'event_type', 'from_is_approved', 'to_is_approved', 'reason');

    protected $casts = array('from_is_approved' => 'boolean', 'to_is_approved' => 'boolean');

    public function internship()
    {
        /* catch (?Throwable $trace contract */
        $started = microtime(true); $cid = SessionTracer::id(); SessionTracer::enter('lifecycle_event.internship', $cid, array('resource_type' => 'lifecycle_event', 'resource_id' => (string) $this->id));
        try { $result = $this->belongsTo('App\Internship'); SessionTracer::leave('lifecycle_event.internship', $cid, $started, 'success', array('resource_type' => 'lifecycle_event', 'resource_id' => (string) $this->id)); return $result; }
        catch (\Throwable $exception) { SessionTracer::exception('lifecycle_event.internship', $cid, $started, 'lifecycle_model_unexpected', $exception); throw $exception; }
    }

    protected static function boot()
    {
        /* catch (?Throwable $trace contract */
        $started = microtime(true); $cid = SessionTracer::id(); SessionTracer::enter('lifecycle_event.boot', $cid, array('resource_type' => 'lifecycle_event'));
        try { parent::boot(); static::updating(function (): void { throw new \LogicException('Lifecycle events are immutable.'); }); static::deleting(function (): void { throw new \LogicException('Lifecycle events are immutable.'); }); SessionTracer::leave('lifecycle_event.boot', $cid, $started, 'success', array('resource_type' => 'lifecycle_event')); }
        catch (\Throwable $exception) { SessionTracer::exception('lifecycle_event.boot', $cid, $started, 'lifecycle_model_unexpected', $exception); throw $exception; }
    }
}
