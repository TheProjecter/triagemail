<?php
// brian suda
// triagemail.com
// 2008-06-03

start_session();

// Get and setup some variables
$lang = getLang();

// device type
// (desktop | mobile | iPhone)
$device = getDevice();

// browser type??
// possibly different hacks or widths?

?>
<html>
  <head>
	<title>Email Triage</title>
  </head>
  <body id="triagemail-com">
<?php
	echo '<h1>triagemail.com</h1>';
// Step #1
// display login

// Step #2
// fetch next email and request action

// Step #3
// deal with response and go to step #2 until empty


?>
  </body>
</html>

<?php

function pluralize($term,$count){
	if($count == 1){ return $term; }
	else {
		// do pluralization here
		return $term;
	}
}

function translaste($term,$lang){
	switch($lang){
		// copy this setup
		// icelandic
		case 'is':
		  switch($term){
			
			default:
			  return '???'.$term.'???';
			  break;
		  }
		  break;
		// basecase
		case 'en-us': case 'en': default:
		  return $term;
		  break;
	}
}

function getLang(){
	$lang = 'en';	
	$lang = strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	$lang = explode('-',$lang);
	$lang = $lang[0];

	return $lang;
}

function getDevice(){
	$devType = 'desktop';
	// clean this input
	$useragent = strtolower($GLOBALS['HTTP_USER_AGENT']);
	
	switch($useragent){
		default:
		  $devType = 'desktop';
		  break;
	}
	
	return $devType;
}

?>