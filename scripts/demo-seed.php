<?php

// feature-10032026-Maurice
// Ticket 03 local-only deterministic seed/inspection/auth operation. This file
// intentionally stays at top level: no user-defined functions or closures.

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$action = getenv('DEMO_ACTION') ?: 'seed';
$logger = app('log');
$logger->info('demo.operation.start', array('action' => $action, 'mode' => getenv('PROVIDER_MODE') ?: 'fake'));

$codes = array('student' => 'DEMO-STUDENT-001', 'coordinator' => 'DEMO-COORD-001', 'superuser' => 'DEMO-ADMIN-001');
$emails = array('student' => 'demo.student.001@test.example', 'coordinator' => 'demo.coord.001@test.example', 'superuser' => 'demo.admin.001@test.example');
$passwords = array('student' => 'DemoOnly-Student-001!', 'coordinator' => 'DemoOnly-Coord-001!', 'superuser' => 'DemoOnly-Admin-001!');

if ($action === 'auth') {
    $checks = array('student', 'coordinator', 'superuser');
    $results = array();
    foreach ($checks as $role) {
        $user = DB::table('users')->where('sr_code', $codes[$role])->first();
        $provided = getenv('DEMO_' . strtoupper($role) . '_PASSWORD');
        $results[$role] = $user && $provided && Hash::check($provided, $user->password) ? 'authenticated' : 'failed';
    }
    printf("student=%s coordinator=%s superuser=%s\n", $results['student'], $results['coordinator'], $results['superuser']);
    $logger->info('demo.operation.complete', array('action' => 'auth', 'status' => 'complete'));
    return;
}

if ($action === 'mutate') {
    DB::table('descriptions')->where('description', 'Demo placement description')->update(array('description' => 'Demo placement mutation'));
    $logger->info('demo.operation.complete', array('action' => 'mutate', 'status' => 'complete'));
    printf("event=operation status=complete action=mutate\n");
    return;
}

if ($action === 'inspect') {
    $keyPublic = storage_path('oauth-public.key');
    $keyPrivate = storage_path('oauth-private.key');
    $student = DB::table('users')->where('sr_code', $codes['student'])->first();
    $coordinator = DB::table('users')->where('sr_code', $codes['coordinator'])->first();
    $superuser = DB::table('users')->where('sr_code', $codes['superuser'])->first();
    $company = DB::table('companies')->where('name', 'Demo Local Company')->first();
    $internship = $company && $student ? DB::table('internships')->where('user_id', $student->id)->where('company_id', $company->id)->first() : null;
    $category = DB::table('requirement_categories')->where('name', 'Demo Requirement')->where('is_deleted', '0')->first();
    $requirement = $category && $internship ? DB::table('requirements')->where('requirement_category_id', $category->id)->where('internship_id', $internship->id)->where('is_deleted', '0')->first() : null;
    $description = $internship ? DB::table('descriptions')->where('internship_id', $internship->id)->where('description', 'Demo placement description')->first() : null;
    $report = $internship ? DB::table('reports')->where('internship_id', $internship->id)->where('description', 'Demo valid report')->where('is_valid', '1')->where('is_deleted', '0')->first() : null;
    $naturalKeys = $student && $coordinator && $superuser && $company && $internship && $category && $requirement && $description && $report && DB::table('users')->whereIn('sr_code', array_values($codes))->count() === 3 && DB::table('companies')->where('name', 'Demo Local Company')->count() === 1 && DB::table('internships')->where('user_id', $student->id)->where('company_id', $company->id)->count() === 1 && DB::table('requirement_categories')->where('name', 'Demo Requirement')->where('is_deleted', '0')->count() === 1 && DB::table('requirements')->where('requirement_category_id', $category->id)->where('internship_id', $internship->id)->where('is_deleted', '0')->count() === 1 && DB::table('descriptions')->where('internship_id', $internship->id)->where('description', 'Demo placement description')->count() === 1 && DB::table('reports')->where('internship_id', $internship->id)->where('description', 'Demo valid report')->where('is_valid', '1')->where('is_deleted', '0')->count() === 1;
    printf("users=%d user_student=%s role_student=%s user_coordinator=%s role_coordinator=%s user_superuser=%s role_superuser=%s companies=%d company=%s internships=%d internship_student=%s internship_company=%s internship_status=%s active_categories=%d category=%s requirements=%d requirement_category=%s requirement_internship_student=%s requirement_internship_company=%s descriptions=%d description=%s description_internship_student=%s reports=%d valid_reports=%d report=%s report_internship_student=%s natural_keys=%s fixture_graph=deterministic oauth_clients=%d passport_public_key_sha256=%s passport_private_key_sha256=%s\n", DB::table('users')->whereIn('sr_code', array_values($codes))->count(), $student ? $student->sr_code : 'missing', $student ? $student->role : 'missing', $coordinator ? $coordinator->sr_code : 'missing', $coordinator ? $coordinator->role : 'missing', $superuser ? $superuser->sr_code : 'missing', $superuser ? $superuser->role : 'missing', DB::table('companies')->where('name', 'Demo Local Company')->count(), $company ? $company->name : 'missing', DB::table('internships')->where('user_id', $student ? $student->id : 0)->count(), $internship && $student ? $student->sr_code : 'missing', $internship && $company ? $company->name : 'missing', $internship ? $internship->status : 'missing', DB::table('requirement_categories')->where('name', 'Demo Requirement')->where('is_deleted', '0')->count(), $category ? $category->name : 'missing', DB::table('requirements')->where('requirement_category_id', $category ? $category->id : 0)->where('internship_id', $internship ? $internship->id : 0)->where('is_deleted', '0')->count(), $category ? $category->name : 'missing', $requirement && $student ? $student->sr_code : 'missing', $requirement && $company ? $company->name : 'missing', $description ? 1 : 0, $description ? $description->description : 'missing', $description && $student ? $student->sr_code : 'missing', $internship ? DB::table('reports')->where('internship_id', $internship->id)->count() : 0, $internship ? DB::table('reports')->where('internship_id', $internship->id)->where('is_valid', '1')->where('is_deleted', '0')->count() : 0, $report ? $report->description : 'missing', $report && $student ? $student->sr_code : 'missing', $naturalKeys ? 'unique' : 'invalid', DB::table('oauth_clients')->count(), is_file($keyPublic) ? hash_file('sha256', $keyPublic) : 'missing', is_file($keyPrivate) ? hash_file('sha256', $keyPrivate) : 'missing');
    if (!$naturalKeys) {
        $logger->error('demo.operation.failure', array('action' => 'inspect', 'status' => 'fixture-contract-invalid'));
        exit(1);
    }
    $logger->info('demo.operation.complete', array('action' => 'inspect', 'status' => 'complete'));
    return;
}

$logger->info('demo.database.start', array('operation' => 'upsert-fixtures'));
$now = date('Y-m-d H:i:s');
$ids = array();
foreach ($codes as $role => $code) {
    $existing = DB::table('users')->where('sr_code', $code)->first();
    $attributes = array('name' => 'Demo ' . ucfirst($role), 'email' => $emails[$role], 'role' => $role, 'sr_code' => $code, 'updated_at' => $now);
    if (!$existing) {
        $attributes['password'] = Hash::make($passwords[$role]);
        $attributes['created_at'] = $now;
        $ids[$role] = DB::table('users')->insertGetId($attributes);
    } else {
        DB::table('users')->where('id', $existing->id)->update($attributes);
        $ids[$role] = $existing->id;
    }
}

DB::table('companies')->updateOrInsert(array('name' => 'Demo Local Company'), array('country' => 'Demo Country', 'city' => 'Demo City', 'address' => 'Demo Address', 'location_map' => 'Demo Map', 'updated_at' => $now, 'created_at' => $now));
$company = DB::table('companies')->where('name', 'Demo Local Company')->first();
DB::table('internships')->updateOrInsert(array('user_id' => $ids['student'], 'company_id' => $company->id), array('start_date' => '2026-01-01', 'representative' => 'Demo Representative', 'student_position' => 'Demo Intern', 'is_approved' => 1, 'status' => 'active', 'comment' => 'Demo placement', 'updated_by' => $ids['coordinator'], 'updated_at' => $now, 'created_at' => $now));
$internship = DB::table('internships')->where('user_id', $ids['student'])->where('company_id', $company->id)->first();
DB::table('requirement_categories')->updateOrInsert(array('name' => 'Demo Requirement'), array('file' => null, 'is_deleted' => '0', 'updated_by' => $ids['coordinator'], 'updated_at' => $now, 'created_at' => $now));
$category = DB::table('requirement_categories')->where('name', 'Demo Requirement')->first();
DB::table('requirements')->updateOrInsert(array('requirement_category_id' => $category->id, 'internship_id' => $internship->id), array('file' => null, 'is_approved' => '1', 'is_deleted' => '0', 'updated_by' => $ids['coordinator'], 'updated_at' => $now, 'created_at' => $now));
DB::table('descriptions')->updateOrInsert(array('internship_id' => $internship->id, 'description' => 'Demo placement description'), array('updated_at' => $now, 'created_at' => $now));
DB::table('reports')->updateOrInsert(array('internship_id' => $internship->id, 'description' => 'Demo valid report'), array('date' => '2026-01-02', 'hours' => '8', 'is_valid' => '1', 'is_deleted' => '0', 'updated_by' => $ids['student'], 'comment' => null, 'updated_at' => $now, 'created_at' => $now));

if (!is_file(storage_path('oauth-private.key')) || !is_file(storage_path('oauth-public.key'))) {
    passthru('php artisan passport:keys', $status);
    if ($status !== 0) {
        $logger->error('demo.operation.failure', array('action' => 'passport-keys', 'status' => $status));
        throw new RuntimeException('Passport key initialization failed');
    }
}
if (DB::table('oauth_clients')->where('name', 'Demo Local Client')->count() === 0) {
    DB::table('oauth_clients')->insert(array('user_id' => null, 'name' => 'Demo Local Client', 'secret' => bin2hex(random_bytes(32)), 'redirect' => 'http://localhost', 'personal_access_client' => 1, 'password_client' => 0, 'revoked' => 0));
}
$logger->info('demo.database.complete', array('operation' => 'upsert-fixtures', 'identities' => 3));
$logger->info('demo.operation.complete', array('action' => 'seed', 'status' => 'complete'));
printf("event=completion status=complete fixture_graph=deterministic\n");
