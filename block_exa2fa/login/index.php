<?php
require __DIR__.'/../../../config.php';

// avoid redirect to login form after login
if (isloggedin() and !isguestuser()) {
	redirect($CFG->wwwroot);
}

$CFG->alternateloginurl = null;

if (preg_match('!/login/!', $SESSION->wantsurl )) {
	$SESSION->wantsurl = null;
}

$PAGE->requires->jquery();
$PAGE->requires->js('/blocks/exa2fa/javascript/login.js', true);
$PAGE->requires->strings_for_js(['sessionerroruser'], 'error');

if (optional_param('ajax', false, PARAM_BOOL)) {
	ob_start(function($output){
		// disable the moodle redirect, rewrite location header
		$location = array_filter(headers_list(), function($header) { return strpos($header, "Location:") === 0; });
		$location = $location ? preg_replace('!Location:\s*!i', '', reset($location)) : null;
		header_remove('Location');
		
		// get error message
		global $SESSION, $A2FA_ERROR, $errormsg;
		if (empty($errormsg) && !empty($SESSION->loginerrormsg)) {
			$errormsg = $SESSION->loginerrormsg;
		}
		
		// moodle returns 303 redirect, we return 200 ok
		header('HTTP/1.1 200 OK');
		
		return json_encode(['error'=>$errormsg, 'a2fa-error' => !empty($A2FA_ERROR) ? $A2FA_ERROR : null, 'url'=>$location, 'original-output' => $output]);
	});
}

require __DIR__.'/../../../login/index.php';
