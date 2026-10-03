<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Support\SessionTracer;

/* feature/fix-10042026-Maurice: immutable numeric lifecycle audit without PII. */
class CreateInternshipLifecycleEventsTable extends Migration
{
    public function up(): void
    {
        $started = microtime(true); $cid = SessionTracer::id(); SessionTracer::enter('migration.lifecycle_events.up', $cid, array('resource_type' => 'internship_lifecycle_events'));
        try {
            Schema::create('internship_lifecycle_events', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedInteger('internship_id');
                $table->unsignedInteger('actor_id');
                $table->string('event_type', 16);
                $table->boolean('from_is_approved');
                $table->boolean('to_is_approved');
                $table->string('reason', 500)->nullable();
                $table->timestamps();
                $table->index(array('internship_id', 'created_at', 'id'));
                $table->foreign('internship_id')->references('id')->on('internships')->onDelete('restrict');
                $table->foreign('actor_id')->references('id')->on('users')->onDelete('restrict');
            });
            DB::unprepared("CREATE TRIGGER internship_lifecycle_events_before_insert BEFORE INSERT ON internship_lifecycle_events FOR EACH ROW BEGIN
                IF NEW.event_type NOT IN ('approved', 'reopened') THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid lifecycle event type'; END IF;
                IF NEW.from_is_approved NOT IN (0, 1) OR NEW.to_is_approved NOT IN (0, 1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid lifecycle approval flags'; END IF;
                IF NEW.event_type = 'approved' AND (NEW.from_is_approved <> 0 OR NEW.to_is_approved <> 1 OR NEW.reason IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid approval lifecycle event'; END IF;
                IF NEW.event_type = 'reopened' AND (NEW.from_is_approved <> 1 OR NEW.to_is_approved <> 0 OR NEW.reason IS NULL OR NEW.reason <> TRIM(NEW.reason) OR CHAR_LENGTH(NEW.reason) < 10 OR CHAR_LENGTH(NEW.reason) > 500) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid reopen lifecycle event'; END IF;
            END");
            DB::unprepared("CREATE TRIGGER internship_lifecycle_events_before_update BEFORE UPDATE ON internship_lifecycle_events FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Lifecycle events are append-only'");
            DB::unprepared("CREATE TRIGGER internship_lifecycle_events_before_delete BEFORE DELETE ON internship_lifecycle_events FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Lifecycle events are append-only'");
            SessionTracer::leave('migration.lifecycle_events.up', $cid, $started, 'success', array('resource_type' => 'internship_lifecycle_events'));
        } catch (\Throwable $exception) { SessionTracer::exception('migration.lifecycle_events.up', $cid, $started, 'migration_unexpected', $exception); throw $exception; }
    }

    public function down(): void
    {
        $started = microtime(true); $cid = SessionTracer::id(); SessionTracer::enter('migration.lifecycle_events.down', $cid, array('resource_type' => 'internship_lifecycle_events'));
        try { DB::unprepared('DROP TRIGGER IF EXISTS internship_lifecycle_events_before_insert'); DB::unprepared('DROP TRIGGER IF EXISTS internship_lifecycle_events_before_update'); DB::unprepared('DROP TRIGGER IF EXISTS internship_lifecycle_events_before_delete'); Schema::dropIfExists('internship_lifecycle_events'); SessionTracer::leave('migration.lifecycle_events.down', $cid, $started, 'success', array('resource_type' => 'internship_lifecycle_events')); } catch (\Throwable $exception) { SessionTracer::exception('migration.lifecycle_events.down', $cid, $started, 'migration_unexpected', $exception); throw $exception; }
    }
}
