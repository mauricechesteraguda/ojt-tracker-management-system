<?php

namespace App\Classes;

/* fix-10032026-Maurice: legacy transport no longer carries embedded credentials or endpoints. */


/*
  ver 0.5
  BATANGAS STATE UNIVERSITY LIBRARY
  ICT Services
  5-6-2014
  dkposeidon

  This library utilize BatSU API for data processing
  
  *** REQUIRES INTERNET CONNECTIONS ***

  *** REQUEST METHOD USED ***
  	-> GET		

  *** LIST OF AVAILABLE FUNCTIONS ***
    -> fetch_cardtag_info($cardtag)
  	-> fetch_gate_entry($srcode,$date_from='',$date_to='')
	-> fetch_scholarships()
	-> fetch_scholar_subjects($schoolyear,$semester,$campus='',$scholarship)
	-> fetch_class_list($schoolyear,$semester,$campus='',$college='',$course='',$major='',$yearlevel='',$search_key='')
	-> fetch_class_students($schoolyear,$semester,$campus='',$subject_code='',$class_section='')
	-> fetch_student_list($schoolyear,$semester,$campus="",$college="",$course="",$major="",$yearlevel="",$search_keys="")
	-> fetch_pe_enrollees($schoolyear,$semester,$campus="",$college="",$course="",$major="",$yearlevel="")
	-> fetch_campuses()
	-> fetch_colleges()
	-> fetch_courses($college)
	-> fetch_majors($course)
	-> fetch_schoolyear()
	-> fetch_semester()
	-> fetch_enrollment_records($schoolyear,$semester,$srcode)
	-> fetch_enrollment_subjects($schoolyear,$semester,$srcode)	
	-> who()
	-> authenticate_student($user,$password)
	-> authenticate_faculty($user,$password)
	-> fetch_faculty_class_listing($schoolyear,$semester,$instructor_id)
	-> fetch_faculty_profile($faculty_id)

	-> fetch_student_profile($srcode)
	-> fetch_student_subjects($schoolyear,$semester,$srcode,$token)
	-> fetch_student_receipts($schoolyear,$semester,$srcode,$token)
	-> fetch_student_scholarships($schoolyear,$semester,$srcode,$token)
	-> fetch_student_grades(($schoolyear,$semester,$srcode,$token)
	-> fetch_student_liabilities($srcode,$token)
	-> fetch_student_photo_url($srcode,$token)
	-> fetch_curriculum_id($srcode,$token)

	-> fetch_curriculum_list($course,$major='')
	-> fetch_curriculum_subjects($course,$curriculum,$major='')

	-> fetch_faculty_listing($schoolyear,$semester,$campus,$college)
	
  Please take note that the returned data are in JSON format,
  you can use json_decode() function to convert the returned data into arrays

  Instructions :
  	->copy this file into your php scripts directory
  	->on your target script include or make a reference to this file by using  require_once php function
  		example : require_once("api.php");
  	->insert the api key provided to you in API_KEY variable
  		Please take note the the system will verify each api key for validity.  	
  	->on your target script, create an instance of batsu_api class
  		example : $api = new batsu_api(YOUR_API_KEY_HERE);
  	->you can call all functions listed above.
  		example : $api->fetch_colleges();

  Please take note that the system records all API request.	In case of system abuse, we have the rights 
  to revoke your system access.

  ------------------------------------------------------------------------------------------
  Sample Implementation:	
  Description : List all colleges
  ------------------------------------------------------------------------------------------
	<?php
  		require_once("api.php");

    	$api = new batsu_api(YOUR_API_KEY_HERE);
		
		foreach (json_decode($api->fetch_colleges(),true) as $college) {
			echo $college['code'] . " " . $college['description'] . "<br>";
		}
	?>

*/

class batsu_api
{	
    public $my_api_key = '';

    public function __construct($key = '') { $started = microtime(true); \App\Support\SessionTracer::enter('legacy.transport.construct', 'legacy', array('mode' => 'legacy')); try { $this->my_api_key = $key; \App\Support\SessionTracer::leave('legacy.transport.construct', 'legacy', $started, 'success'); } catch (\Throwable $exception) { \App\Support\SessionTracer::exception('legacy.transport.construct', 'legacy', $started, 'legacy_unexpected', $exception); throw $exception; } }

    public function __call($name, $arguments) { $started = microtime(true); \App\Support\SessionTracer::enter('legacy.transport.call', 'legacy', array('mode' => 'legacy')); try { $result = $this->api_request(array('operation' => $name, 'arguments' => $arguments), 'legacy'); \App\Support\SessionTracer::leave('legacy.transport.call', 'legacy', $started, 'success'); return $result; } catch (\Throwable $exception) { \App\Support\SessionTracer::exception('legacy.transport.call', 'legacy', $started, 'academic_provider_unexpected', $exception); throw $exception; } }

    /* added 11/26/2018 */
    function fetch_cardtag_info($cardtag)
	{
			$param = array(
							'do'=>'fetch_cardtag_info',
							'cardtag'=>$cardtag
						);

			return $this->api_request($param);
	}

	/* added 9/28/2017 */
	function fetch_gate_entry($srcode,$date_from='',$date_to='')
	{
			$param = array(
							'do'=>'fetch_gate_entry',
							'date_from'=>$date_from,
							'date_to'=>$date_to,
							'srcode'=>$srcode
				  		);

			return $this->api_request($param);
	}

	/* added 9/1/2017 */
	function fetch_scholarships()
	{
			$param = array(
							'do'=>'fetch_scholarships'
				  		);

			return $this->api_request($param);
	}
	
	function fetch_scholar_subjects($schoolyear,$semester,$campus='',$scholarship,$faculty_id='')
	{
			$param = array(
							'do'=>'fetch_scholar_subjects',
							'schoolyear'=>$schoolyear,
							'semester'=>$semester,
							'campus'=>$campus,
							'scholarship'=>$scholarship,
							'faculty_id'=>$faculty_id
				  		);
		
			return $this->api_request($param);
	}
	
	function fetch_faculty_profile($faculty_id)
	{
			$param = array(
							'do'=>'fetch_faculty_profile',
							'faculty_id'=>$faculty_id
				  		);

			return $this->api_request($param);
	}

	/* added 7-24-2015*/

	function fetch_faculty_listing($schoolyear,$semester,$campus='',$college='',$search_key='')
	{
			$param = array(
							'do'=>'fetch_faculty_list','schoolyear'=>$schoolyear,
							'semester'=>$semester,'campus'=>$campus,'college'=>$college,'search_key'=>$search_key
				  		);

			return $this->api_request($param);
	}

	function fetch_enrollment_records($schoolyear,$semester,$srcode)
	{
			$param = array(
							'do'=>'fetch_enrollment_records','schoolyear'=>$schoolyear,
							'semester'=>$semester,'srcode'=>$srcode
				  		);

			return $this->api_request($param);
	}

	function fetch_enrollment_subjects($schoolyear,$semester,$srcode)
	{
			$param = array(
							'do'=>'fetch_enrollment_subjects','schoolyear'=>$schoolyear,
							'semester'=>$semester,'srcode'=>$srcode
				  		);

			return $this->api_request($param);
	}

	function fetch_class_list($schoolyear,$semester,$campus='',$college='',$course='',$major='',$yearlevel='',$search_key='')
	{
			$param = array(
							'do'=>'fetch_class_list','schoolyear'=>$schoolyear,
							'semester'=>$semester,'campus'=>$campus,
							'college'=>$college,'course'=>$course,
							'major'=>$major,'yearlevel'=>$yearlevel,
							'search_key'=>$search_key
				  		);
			return $this->api_request($param);
	}

	function fetch_schedule($schoolyear,$semester,$campus='',$college='',$course='',$major='',$yearlevel='',$search_key='')
	{
			$param = array(
							'do'=>'fetch_schedule','schoolyear'=>$schoolyear,
							'semester'=>$semester,'campus'=>$campus,
							'college'=>$college,'course'=>$course,
							'major'=>$major,'yearlevel'=>$yearlevel,
							'search_key'=>$search_key
				  		);
			return $this->api_request($param);
	}

	function fetch_schedule_open($schoolyear,$semester,$campus='',$college='',$course='',$major='',$yearlevel='',$search_key='')
	{
			$param = array(
							'do'=>'fetch_schedule_open','schoolyear'=>$schoolyear,
							'semester'=>$semester,'campus'=>$campus,
							'college'=>$college,'course'=>$course,
							'major'=>$major,'yearlevel'=>$yearlevel,
							'search_key'=>$search_key
				  		);
			return $this->api_request($param);
	}

	function fetch_class_students($schoolyear,$semester,$campus='',$subject_code='',$class_section='')
	{
			$param = array(
							'do'=>'fetch_class_students','schoolyear'=>$schoolyear,
							'semester'=>$semester,'campus'=>$campus,
							'code'=>$subject_code,'class_section'=>$class_section
				  		);

			return $this->api_request($param);
	}

	function fetch_campuses()
	{
			$param = array('do'=>'fetch_campuses');
			return $this->api_request($param);
	}

	function fetch_colleges()
	{
			$param = array('do'=>'fetch_colleges');
			return $this->api_request($param);
	}
	function fetch_courses($college)
	{
			$param = array('do'=>'fetch_courses','college'=>$college);
			return $this->api_request($param);
	}
	function fetch_majors($course)
	{
			$param = array('do'=>'fetch_majors','course'=>$course);
			return $this->api_request($param);
	}

	function fetch_schoolyear()
	{
			$param = array('do'=>'fetch_schoolyear');
			return $this->api_request($param);
	}

	function fetch_semester()
	{
			$param = array('do'=>'fetch_semester');
			return $this->api_request($param);
	}
	function who()
	{
			$param = array('do'=>'who');
			return $this->api_request($param);
	}

	function fetch_curriculum_list($course,$major='')
	{
			$param = array('do'=>'fetch_curriculum_list',
				'course'=>$course,
				'major'=>$major

				);
			return $this->api_request($param);
	}
	function fetch_curriculum_subjects($course,$curriculum,$major='')
	{
			$param = array('do'=>'fetch_curriculum_subjects',
				'course'=>$course,
				'major'=>$major,
				'curriculum'=>$curriculum

				);
			return $this->api_request($param);
	}

	function authenticate_student($user,$password)
	{
			$param = array('do'=>'authenticate_student',
				'auth_user'=>$user,
				'auth_password'=>$password
			);
			return $this->api_request($param);
			
	}
	function authenticate_faculty($user,$password)
	{
			$param = array('do'=>'authenticate_faculty',
				'auth_user'=>$user,
				'auth_password'=>$password
			);
			return $this->api_request($param);
			
	}
	
	function fetch_faculty_class_listing($schoolyear,$semester,$faculty_id)
	{
			$param = array(
							'do'=>'fetch_instructor_class_list',
							'schoolyear'=>$schoolyear,
							'semester'=>$semester,
							'instructor_id'=>$faculty_id
				  		);

			return $this->api_request($param);
	}
	function fetch_student_list($schoolyear,$semester,$campus="",$college="",$course="",$major="",$yearlevel="",$search_keys="")
	{
			$param = array(
							'do'=>'fetch_student_list',
							'schoolyear'=>$schoolyear,
							'semester'=>$semester,
							'campus'=>$campus,
							'college'=>$college,
							'course'=>$course,
							'major'=>$major,
							'yearlevel'=>$yearlevel,
							'search_keys'=>$search_keys
				  		);

			return $this->api_request($param);
	}
	function fetch_student_profile($srcode)
	{
			$param = array(
							'do'=>'fetch_student_profile',
							'srcode'=>$srcode
				  		);

			return $this->api_request($param);
	}

	function fetch_pe_enrollees($schoolyear,$semester,$campus="",$college="",$course="",$major="",$yearlevel="")
	{
			$param = array(
							'do'=>'fetch_pe_enrollees',
							'schoolyear'=>$schoolyear,
							'semester'=>$semester,
							'campus'=>$campus,
							'college'=>$college,
							'course'=>$course,
							'major'=>$major,
							'yearlevel'=>$yearlevel
							
				  		);

			return $this->api_request($param);
	}

	/* FUNCTIONS BELOW REQUIRES STUDENT AUTH TOKEN */

	/*
		Auth token can be taken  from  returned data of student auth process
		
		------------------------------------------------------------------------------------------
  		EXAMPLE
  		Description : View student grades
  		------------------------------------------------------------------------------------------
		<?php
  			require_once("api.php");

    		$api = new batsu_api;
		
			$user     	="STUDENT_SRCODE;
			$password 	="STUDENT_PASSWORD";
			$schoolyear ="2013-2014";
			$semester   ="FIRST";
			
			$data = json_decode($api->authenticate_student($user,$password),true);

			if(!empty($data))
			{
					$token = $data[0]['token'];

					echo $api->fetch_student_grades($schoolyear,$semester,$user,$token);
			}
		?>

	*/
	function fetch_student_subjects($schoolyear,$semester,$srcode,$token)
	{
			$param = array(
							'do'=>'fetch_student_subjects',
							'schoolyear'=>$schoolyear,
							'semester'=>$semester,
							'srcode'=>$srcode,
							'token'=>$token

				  		);

			return $this->api_request($param);
	}
	function fetch_student_receipts($schoolyear,$semester,$srcode,$token)
	{
			$param = array(
							'do'=>'fetch_student_receipts',
							'schoolyear'=>$schoolyear,
							'semester'=>$semester,
							'srcode'=>$srcode,
							'token'=>$token
				  		);

			return $this->api_request($param);
	}

	function fetch_student_scholarships($schoolyear,$semester,$srcode,$token)
	{
			$param = array(
							'do'=>'fetch_student_scholarships',
							'schoolyear'=>$schoolyear,
							'semester'=>$semester,
							'srcode'=>$srcode,
							'token'=>$token
				  		);

			return $this->api_request($param);
	}


	function fetch_student_grades($schoolyear,$semester,$srcode,$token)
	{
			$param = array(
							'do'=>'fetch_student_grades',
							'schoolyear'=>$schoolyear,
							'semester'=>$semester,
							'srcode'=>$srcode,
							'token'=>$token
				  		);

			return $this->api_request($param);
	}

	function fetch_student_photo_url($srcode,$token)
	{
			$param = array(
							'do'=>'fetch_student_photo_url',
							'srcode'=>$srcode,
							'token'=>$token
				  		);

			return $this->api_request($param);
	}

	function fetch_student_liabilities($srcode,$token)
	{
			$param = array(
							'do'=>'fetch_student_liabilities',
							'srcode'=>$srcode,
							'token'=>$token
				  		);

			return $this->api_request($param);
	}

	//added 4-17

	function fetch_curriculum_id($srcode,$token)
	{
			$param = array(
							'do'=>'fetch_curriculum_id',
							'srcode'=>$srcode,
							'token'=>$token
				  		);

			return $this->api_request($param);
	}

	function api_request($param, $correlationId = 'legacy')
	{
		$started = microtime(true);
		\App\Support\SessionTracer::enter('legacy.transport.request', $correlationId, array('mode' => 'legacy'));
		try {
		$url = config('academic.base_url', '');
		$parts = parse_url($url);
		$https = is_array($parts) && isset($parts['scheme']) && strtolower($parts['scheme']) === 'https' && !empty($parts['host']);
		$local = is_array($parts) && isset($parts['host']) && in_array(strtolower($parts['host']), array('127.0.0.1', '::1', 'localhost', 'host.docker.internal'), true);
		$localAllowed = config('academic.allow_local', false) && app()->environment('local', 'testing') && $local;
		if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL) || (!$https && !$localAllowed)) {
			\App\Support\SessionTracer::leave('legacy.transport.request', $correlationId, $started, 'unavailable', array('error_category' => 'academic_provider_unavailable'));
			return json_encode(array());
		}
		$result = $this->url_get_contents($url, $param, $correlationId);
		\App\Support\SessionTracer::leave('legacy.transport.request', $correlationId, $started, 'success');
		return $result;
		} catch (\Throwable $exception) {
		\App\Support\SessionTracer::exception('legacy.transport.request', $correlationId, $started, 'legacy_unexpected', $exception);
		throw $exception;
		}
	}

	function url_get_contents ($Url, array $param = array(), $correlationId = 'legacy') {
	    $started = microtime(true);
	    \App\Support\SessionTracer::enter('legacy.transport.curl', $correlationId, array('mode' => 'legacy'));
	    try {
	    if (!function_exists('curl_init')){ 
	        \App\Support\SessionTracer::leave('legacy.transport.curl', $correlationId, $started, 'unavailable', array('error_category' => 'academic_provider_unavailable'));
	        return json_encode(array());
	    }
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $Url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $output = curl_exec($ch);
	    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
	    if ($output === false || $status < 200 || $status >= 300) {
	        \App\Support\SessionTracer::leave('legacy.transport.curl', $correlationId, $started, 'unavailable', array('error_category' => 'academic_provider_unavailable'));
	        return json_encode(array());
	    }
	    \App\Support\SessionTracer::leave('legacy.transport.curl', $correlationId, $started, 'success');
	    return $output;
	    } catch (\Throwable $exception) {
	        \App\Support\SessionTracer::exception('legacy.transport.curl', $correlationId, $started, 'legacy_unexpected', $exception);
	        throw $exception;
	    }
	}
}



?>
