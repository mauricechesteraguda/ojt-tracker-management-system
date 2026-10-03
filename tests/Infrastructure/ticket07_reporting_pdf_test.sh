#!/bin/sh

# test-10032026-Maurice
# Ticket 07 RED contract: POST-only shared authorized reporting query, filtered
# API/PDF parity, safe errors, privacy, and observability.
set -eu
umask 077
REPO_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
COMPOSE_FILE=$REPO_ROOT/compose.yaml
PROJECT_NAME=${OJT_TEST_COMPOSE_PROJECT:-ojt-ticket07-$$}
if [ -n "${OJT_TEST_APP_PORT:-}" ]; then
  APP_PORT=$OJT_TEST_APP_PORT
else
  command -v python3 >/dev/null 2>&1 || { printf '%s\n' 'FAIL [TC0039]: python3 is required to allocate an isolated app port' >&2; exit 1; }
  APP_PORT=$(python3 -c 'import socket; s=socket.socket(); s.bind(("127.0.0.1", 0)); print(s.getsockname()[1]); s.close()')
fi
TMP_ROOT=${TMPDIR:-/tmp}/ojt-ticket07-$$
COMPOSE_STARTED=0
AGENT_SESSION_ID=${AGENT_SESSION_ID:-ticket07-$$}
mkdir -p "$TMP_ROOT"
trap 'if [ "$COMPOSE_STARTED" = 1 ]; then docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" down --volumes --remove-orphans >/dev/null 2>&1 || :; fi; rm -rf -- "$TMP_ROOT"' EXIT HUP INT TERM
logger -t ojt-ticket07 "event=startup status=begin test=ticket07_reporting_pdf_test" || :

# Static, fail-fast gates precede every Docker operation.
[ -f "$REPO_ROOT/app/Services/InternshipReportingService.php" ] || { printf '%s\n' 'FAIL [TC0013]: shared authorized reporting service is missing (Docker not started)' >&2; exit 1; }
grep -Eiq 'InternshipReportingService|ReportingQuery|authorized.*report|reporting.*query' "$REPO_ROOT/app/Services/InternshipReportingService.php" "$REPO_ROOT/app/Http/Controllers/InternshipReportingController.php" || { printf '%s\n' 'FAIL [TC0013]: API/PDF shared reporting query is not statically proven' >&2; exit 1; }
grep -Eiq 'campus|schoolyear|semester|college|course' "$REPO_ROOT/app/Services/InternshipReportingService.php" || { printf '%s\n' 'FAIL [TC0014]: five reporting filters are missing' >&2; exit 1; }
grep -Eiq 'orderBy.*first_name|first_name.*orderBy|orderByRaw' "$REPO_ROOT/app/Services/InternshipReportingService.php" || { printf '%s\n' 'FAIL [TC0014]: stable first_name,last_name,id ordering is missing' >&2; exit 1; }
grep -Eiq 'is_deleted.*0|where.*is_deleted' "$REPO_ROOT/app/Services/InternshipReportingService.php" || { printf '%s\n' 'FAIL [TC0014]: deleted-record exclusion is missing' >&2; exit 1; }
grep -Eiq '422|validate|ValidationException|safe.*json|ApiErrorNormalizer' "$REPO_ROOT/app/Services/InternshipReportingService.php" "$REPO_ROOT/app/Http/Controllers/InternshipReportingController.php" "$REPO_ROOT/app/Support/ApiErrorNormalizer.php" 2>/dev/null || { printf '%s\n' 'FAIL [TC0019]: safe 422 JSON filter validation is missing' >&2; exit 1; }
grep -Eiq "Route::post\([^;]*internships/report|Route::post\([^;]*internships/report/pdf" "$REPO_ROOT/routes/api.php" || { printf '%s\n' 'FAIL [TC0022]: POST reporting/PDF route contract is missing' >&2; exit 1; }
if grep -Eiq "Route::get\([^;]*internships(/report|/report/pdf|/print-pdf)?|Route::get\([^;]*report/pdf" "$REPO_ROOT/routes/api.php" "$REPO_ROOT/routes/web.php"; then printf '%s\n' 'FAIL [TC0022]: GET reporting/PDF route is forbidden' >&2; exit 1; fi
if grep -R -n -E 'legacyIndex|legacyGlobal|app\([^)]*InternshipController[^)]*\)->index|isMethod\('\''get'\''\).*first_name|t07-' "$REPO_ROOT/app" "$REPO_ROOT/routes" >/dev/null 2>&1; then printf '%s\n' 'FAIL [TC0013]: legacy reporting delegation/global or Ticket07 correlation special-case is forbidden' >&2; exit 1; fi
grep -Eiq 'auth:api|middleware.*auth' "$REPO_ROOT/routes/api.php" || { printf '%s\n' 'FAIL [TC0019]: API authentication is missing' >&2; exit 1; }
grep -Eiq 'middleware.*auth|auth' "$REPO_ROOT/routes/web.php" || { printf '%s\n' 'FAIL [TC0019]: web authentication is missing' >&2; exit 1; }
if grep -R -n -F 'xdebug_break' "$REPO_ROOT/app" "$REPO_ROOT/routes" "$REPO_ROOT/resources/views" >/dev/null 2>&1; then printf '%s\n' 'FAIL [TC0032]: xdebug_break is forbidden' >&2; exit 1; fi
grep -Eiq 'SessionTracer::enter|SessionTracer::leave|SessionTracer::exception|catch[[:space:]]*\([^)]*Throwable' "$REPO_ROOT/app/Services/InternshipReportingService.php" "$REPO_ROOT/app/Http/Controllers/InternshipReportingController.php" || { printf '%s\n' 'FAIL [TC0039]: validation/query/render trace envelope is missing' >&2; exit 1; }

command -v pdftotext >/dev/null 2>&1 || { printf '%s\n' 'FAIL [TC0022]: pdftotext is required before Docker starts' >&2; exit 1; }
[ -f "$COMPOSE_FILE" ] || { printf '%s\n' 'FAIL [TC0039]: compose.yaml is required' >&2; exit 1; }
command -v docker >/dev/null 2>&1 || { printf '%s\n' 'FAIL [TC0039]: docker is required' >&2; exit 1; }
command -v timeout >/dev/null 2>&1 || { printf '%s\n' 'FAIL [TC0039]: timeout is required' >&2; exit 1; }
command -v curl >/dev/null 2>&1 || { printf '%s\n' 'FAIL [TC0039]: curl is required' >&2; exit 1; }
if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$APP_PORT" >/dev/null 2>&1; then printf '%s\n' 'FAIL [TC0039]: selected app port is already in use' >&2; exit 1; fi

# Container-only Passport probe. Tokens stay in memory and are never emitted.
cat >"$TMP_ROOT/reporting_probe.php" <<'PHP'
<?php
$base=rtrim(getenv('OJT_PROBE_BASE_URL'),'/');$corr='ticket07-correlation';$u=array();
$u['a']=\App\User::firstOrCreate(array('sr_code'=>'T07-STUDENT-A'),array('name'=>'Ticket07 Same','first_name'=>'Same','last_name'=>'Alpha','email'=>'t07-a@example.invalid','password'=>bcrypt('ticket07-a'),'role'=>'student'));
$u['b']=\App\User::firstOrCreate(array('sr_code'=>'T07-STUDENT-B'),array('name'=>'Ticket07 Same','first_name'=>'Same','last_name'=>'Beta','email'=>'t07-b@example.invalid','password'=>bcrypt('ticket07-b'),'role'=>'student'));
$u['c']=\App\User::firstOrCreate(array('sr_code'=>'T07-COORDINATOR'),array('name'=>'Ticket07 Coordinator','email'=>'t07-c@example.invalid','password'=>bcrypt('ticket07-c'),'role'=>'coordinator'));
$u['s']=\App\User::firstOrCreate(array('sr_code'=>'T07-SUPERUSER'),array('name'=>'Ticket07 Superuser','email'=>'t07-s@example.invalid','password'=>bcrypt('ticket07-s'),'role'=>'superuser'));
$u['x']=\App\User::firstOrCreate(array('sr_code'=>'T07-UNKNOWN'),array('name'=>'Ticket07 Unknown','email'=>'t07-x@example.invalid','password'=>bcrypt('ticket07-x'),'role'=>'unknown'));
$pc=\Illuminate\Support\Facades\DB::table('oauth_personal_access_clients')->first();if(!$pc){$id=\Illuminate\Support\Facades\DB::table('oauth_clients')->insertGetId(array('user_id'=>null,'name'=>'T07 Probe','secret'=>hash('sha256','t07'),'redirect'=>'http://localhost','personal_access_client'=>1,'password_client'=>0,'revoked'=>0));\Illuminate\Support\Facades\DB::table('oauth_personal_access_clients')->insert(array('client_id'=>$id));}
$t=array();foreach($u as $k=>$v){$t[$k]=(string)app(\Laravel\Passport\PersonalAccessTokenFactory::class)->make($v->id,'ticket07')->accessToken;if(substr_count($t[$k],'.')!==2)throw new \RuntimeException('token-shape');}
$company=\App\Company::where('is_deleted','0')->firstOrFail();$make=function($v,$deleted,$startDate,$endDate)use($company){$i=\App\Internship::firstOrCreate(array('user_id'=>$v->id,'company_id'=>$company->id,'start_date'=>$startDate,'end_date'=>$endDate),array('representative'=>'T07 Representative','student_position'=>'T07 Position','is_approved'=>0,'is_deleted'=>$deleted,'status'=>'pending','campus'=>'MAIN','schoolyear'=>'2099','semester'=>'FIRST','college_code'=>'CICS','course_code'=>'BSCOSCI'));$i->campus='MAIN';$i->schoolyear='2099';$i->semester='FIRST';$i->college_code='CICS';$i->course_code='BSCOSCI';$i->is_deleted=$deleted;$i->save();return $i;};$mine=$make($u['a'],'0','2099-07-01','2099-07-31');$other=$make($u['b'],'0','2099-07-02','2099-08-01');$gone=$make($u['b'],'1','2099-07-03','2099-08-02');if((int)$other->id===(int)$gone->id)throw new \RuntimeException('active-deleted-ids-collide');
$filters=array('campus'=>'MAIN','schoolyear'=>'2099','semester'=>'FIRST','college'=>'CICS','course'=>'BSCOSCI');$json=function($page,$perPage=null)use($filters){$v=$filters;$v['page']=$page;if($perPage!==null)$v['per_page']=$perPage;return json_encode($v);};
$statuses=array();$call=function($who,$path,$body,$want)use($base,$corr,$t,&$statuses){$h=array('Accept: application/json','X-Correlation-ID: '.$corr,'Content-Type: application/json');if($who!=='guest')$h[]='Authorization: Bearer '.$t[$who];$c=curl_init($base.$path);curl_setopt_array($c,array(CURLOPT_CUSTOMREQUEST=>'POST',CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>$h,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_CONNECTTIMEOUT=>2,CURLOPT_TIMEOUT=>8));$raw=curl_exec($c);$status=curl_getinfo($c,CURLINFO_HTTP_CODE);$type=curl_getinfo($c,CURLINFO_CONTENT_TYPE);curl_close($c);if($status!==$want)throw new \RuntimeException('status');$statuses[]=$status;$parts=preg_split("/\r?\n\r?\n/",$raw,2);$payload=json_decode(isset($parts[1])?$parts[1]:'',true);if(is_array($payload)&&($payload['correlation_id']??null)!==$corr)throw new \RuntimeException('response-correlation-regenerated');if(!is_array($payload)&&!preg_match('/^X-Correlation-ID:\s*'.preg_quote($corr,'/').'\s*$/im',$parts[0]))throw new \RuntimeException('header-correlation-regenerated');if($status>=400&&preg_match('/token|password|email|phone|address|T07-|Same|Alpha|Beta/i',$raw))throw new \RuntimeException('unsafe-error');return array($raw,$type);};
$decode=function($raw){$p=preg_split("/\r?\n\r?\n/",$raw,2);$j=json_decode(isset($p[1])?$p[1]:'',true);if(!is_array($j))throw new \RuntimeException('json');return $j;};
$body=$json(1);$call('guest','/api/internships/report',$body,401);$call('x','/api/internships/report',$body,403);$call('a','/api/internships/report',$body,200);$default=$decode($call('c','/api/internships/report',$body,200)[0]);$page100=$decode($call('s','/api/internships/report',$json(1,100),200)[0]);foreach(array($default,$page100) as $result){if(($result['correlation_id']??'')!==$corr)throw new \RuntimeException('response-correlation');if((int)($result['meta']['current_page']??0)!==1)throw new \RuntimeException('default-page');}$defaultPerPage=(int)($default['meta']['per_page']??0);if($defaultPerPage!==15)throw new \RuntimeException('default-per-page');if((int)($page100['meta']['per_page']??0)!==100)throw new \RuntimeException('explicit-per-page-100');foreach($default['data'] as $r)if((int)($r['id']??0)===(int)$gone->id)throw new \RuntimeException('deleted');$call('c','/api/internships/report',json_encode(array_merge($filters,array('page'=>0))),422);$call('c','/api/internships/report',json_encode(array_merge($filters,array('page'=>1,'per_page'=>0))),422);$call('c','/api/internships/report',json_encode(array_merge($filters,array('page'=>1,'per_page'=>101))),422);foreach(array('A B','A-B','A_B','ABC123') as $allowed){foreach(array_keys($filters) as $field){$valid=$filters;$valid[$field]=$allowed;$call('c','/api/internships/report',json_encode(array_merge($valid,array('page'=>1))),200);}}foreach(array('CICS.IT','CICS/IT','CICS(IT)','CICS;DROP','CICS\' OR 1=1 --','CICS" OR "1"="1') as $bad){foreach(array_keys($filters) as $field){$invalid=$filters;$invalid[$field]=$bad;$call('c','/api/internships/report',json_encode(array_merge($invalid,array('page'=>1))),422);}}
 $pageOne=$decode($call('c','/api/internships/report',$json(1,1),200)[0]);if((int)($pageOne['meta']['current_page']??0)!==1||(int)($pageOne['meta']['per_page']??0)!==1||count($pageOne['data']??array())!==1)throw new \RuntimeException('api-page-one');$fields=array('id','first_name','last_name','sr_code','company','start_date','end_date','approval','status','campus','schoolyear','semester','college','course');$fieldFile='';foreach($pageOne['data'] as $record){$values=array();foreach($fields as $field){if(!array_key_exists($field,$record))throw new \RuntimeException('defined-fields');$values[]=(string)$record[$field];}$fieldFile.=implode('|',$values).PHP_EOL;}file_put_contents('/tmp/t07-api-fields.txt',$fieldFile);
 $pdf=$call('c','/api/internships/report/pdf',$json(1,1),200);if(stripos($pdf[1],'application/pdf')===false||!preg_match('/X-Reporting-Filter|Content-Disposition/i',$pdf[0])||!preg_match('/X-Correlation-ID:\s*'.$corr.'/i',$pdf[0]))throw new \RuntimeException('pdf-type-or-correlation-header');$p=preg_split("/\r?\n\r?\n/",$pdf[0],2);file_put_contents('/tmp/t07-report.pdf',$p[1]);file_put_contents('/tmp/t07-api-page.json',json_encode($pageOne));$none=$call('c','/api/internships/report/pdf',json_encode(array_merge($filters,array('page'=>1,'per_page'=>1,'course'=>'NO_SUCH_T07'))),200);$p=preg_split("/\r?\n\r?\n/",$none[0],2);file_put_contents('/tmp/t07-none.pdf',$p[1]);$call('guest','/api/internships/report/pdf',$body,401);$call('a','/api/internships/report/pdf',$body,403);$call('x','/api/internships/report/pdf',$body,403);$view='/var/www/html/resources/views/pdf_view.blade.php';$source=file_get_contents($view);file_put_contents($view,'@php throw new \\RuntimeException("render failure"); @endphp');$call('c','/api/internships/report/pdf',$body,503);file_put_contents($view,$source);foreach(array(200,401,403,422,503) as $expected)if(!in_array($expected,$statuses,true))throw new \RuntimeException('status-coverage');
$trace=getenv('HOME').'/.cache/agent-trace/ojt-tracker-management-system/'.getenv('AGENT_SESSION_ID').'.jsonl';if(!is_file($trace)||strpos((string)file_get_contents($trace),$corr)===false)throw new \RuntimeException('trace-correlation');copy($trace,'/tmp/t07-trace.jsonl');echo 'runtime=ready post_api=verified post_pdf=verified'.PHP_EOL;
PHP

export COMPOSE_PROJECT_NAME="$PROJECT_NAME" APP_PORT OJT_PROBE_BASE_URL=${OJT_PROBE_BASE_URL:-http://nginx:8080} ACADEMIC_PROVIDER_MODE=fake OJT_TEST_NO_PROVIDER_CALLS=1 AGENT_SESSION_ID
timeout "${OJT_TEST_BUILD_TIMEOUT_SECONDS:-180}" docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" up -d --build >"$TMP_ROOT/compose.log" 2>&1
COMPOSE_STARTED=1
chmod 0644 "$TMP_ROOT/reporting_probe.php"
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "$TMP_ROOT/reporting_probe.php" php-fpm:/tmp/reporting_probe.php >"$TMP_ROOT/probe-copy.log" 2>&1
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T -e OJT_PROBE_BASE_URL="$OJT_PROBE_BASE_URL" php-fpm sh -c 'export APP_KEY=$(cat /run/ojt-secrets/app.key); export DB_PASSWORD=$(cat /run/ojt-secrets/db.app.password); php -r '\''require "/var/www/html/vendor/autoload.php"; $app=require "/var/www/html/bootstrap/app.php"; $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap(); require "/tmp/reporting_probe.php";'\''' >"$TMP_ROOT/probe.log" 2>&1 || { printf '%s\n' 'FAIL [TC0039]: reporting runtime probe failed' >&2; sed -n '1,30p' "$TMP_ROOT/probe.log" >&2; exit 1; }
if grep -Eiq 'token|password|email|phone|address|T07-|Same|Alpha|Beta|secret|api[_-]?key' "$TMP_ROOT/probe.log"; then printf '%s\n' 'FAIL [TC0039]: runtime output contains secret or fixture PII' >&2; exit 1; fi
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp php-fpm:/tmp/t07-report.pdf "$TMP_ROOT/report.pdf" >/dev/null 2>&1
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp php-fpm:/tmp/t07-none.pdf "$TMP_ROOT/none.pdf" >/dev/null 2>&1
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp php-fpm:/tmp/t07-api-page.json "$TMP_ROOT/api-page.json" >/dev/null 2>&1
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp php-fpm:/tmp/t07-api-fields.txt "$TMP_ROOT/api-fields.txt" >/dev/null 2>&1
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp php-fpm:/tmp/t07-trace.jsonl "$TMP_ROOT/trace.jsonl" >/dev/null 2>&1
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm sh -c 'cat /var/www/html/storage/logs/laravel.log 2>/dev/null || :' >"$TMP_ROOT/app.log" 2>&1
pdftotext -layout "$TMP_ROOT/report.pdf" "$TMP_ROOT/report.txt";pdftotext -layout "$TMP_ROOT/none.pdf" "$TMP_ROOT/none.txt"
grep -Fq 'ticket07-correlation' "$TMP_ROOT/trace.jsonl" || { printf '%s\n' 'FAIL [TC0039]: correlation id is missing from trace' >&2; exit 1; }
grep -Fq 'ticket07-correlation' "$TMP_ROOT/app.log" || { printf '%s\n' 'FAIL [TC0039]: correlation id is missing from application log' >&2; exit 1; }
[ "$(grep -F -c '"correlation_id":"ticket07-correlation"' "$TMP_ROOT/trace.jsonl")" -ge 5 ] || { printf '%s\n' 'FAIL [TC0039]: correlation id is not preserved across traced success/failure requests' >&2; exit 1; }
[ "$(grep -F -c '"correlation_id":"ticket07-correlation"' "$TMP_ROOT/app.log")" -ge 5 ] || { printf '%s\n' 'FAIL [TC0039]: correlation id is not preserved across logged success/failure requests' >&2; exit 1; }
while IFS='|' read -r id first last sr company start end approval status campus schoolyear semester college course; do
  [ "$(grep -F -c 'Internship ID:' "$TMP_ROOT/report.txt")" -eq 1 ] || { printf '%s\n' 'FAIL [TC0022]: PDF page count differs from POST API page=1,per_page=1' >&2; exit 1; }
  previous=0
  for field in "$first" "$last" "$sr" "$company" "$start" "$end" "$approval" "$status" "$campus" "$schoolyear" "$semester" "$college" "$course"; do
    [ -z "$field" ] || grep -Fq "$field" "$TMP_ROOT/report.txt" || { printf '%s\n' 'FAIL [TC0022]: PDF page is missing an API field' >&2; exit 1; }
    [ -z "$field" ] || { current=$(grep -n -F "$field" "$TMP_ROOT/report.txt" | awk -F: -v p="$previous" '$1 > p { print $1; exit }'); [ -n "$current" ] && previous=$current || { printf '%s\n' 'FAIL [TC0022]: PDF field order differs from POST API' >&2; exit 1; }; }
  done
done < "$TMP_ROOT/api-fields.txt"
expected=$(tr '|' '\n' < "$TMP_ROOT/api-fields.txt");actual=$(awk -F': ' '/Internship ID:|First Name:|Last Name:|SR Code:|Company:|Start Date:|End Date:|Approval:|Status:|Campus:|School Year:|Semester:|College:|Course:/{if (seen || /Internship ID:/) seen=1;if(seen){print $2}if(/Course:/) exit}' "$TMP_ROOT/report.txt");[ "$actual" = "$expected" ] || { printf '%s\n' 'FAIL [TC0022]: PDF fields, IDs, or order differ from POST API page=1,per_page=1' >&2; exit 1; }
grep -Eiq 't07-[a-z]|@example\.invalid|contact|address|parent' "$TMP_ROOT/report.txt" && { printf '%s\n' 'FAIL [TC0022]: PDF contains forbidden extra PII' >&2; exit 1; } || :
grep -Fxq 'No matching records' "$TMP_ROOT/none.txt" || { printf '%s\n' 'FAIL [TC0022]: empty report lacks exact No matching records text' >&2; exit 1; }
logger -t ojt-ticket07 'event=pass status=complete test=ticket07_reporting_pdf_test' || :
printf '%s\n' 'PASS: Ticket 07 POST reporting/API/PDF contract'
