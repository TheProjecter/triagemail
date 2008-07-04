<?php
// brian suda
// triagemail.com
// 2008-06-03

session_start();

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
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
	// Step #1
	// display login
	
	if($_POST['logout']){
		// distroy all the variable and logout
		$_SESSION['inTriage'] = null;
		unset($_SESSION['inTriage']);		

		echo '<div class="message">'.translate('Logged out',$lang).'</div>';
		renderLogin($device,$lang); 
		
	} elseif($_SESSION['inTriage']){
		// get next message and continue
		echo 'connect to server, slowly loop through each email waiting for instructions';
		
		renderLogout($device,$lang);
				
	} elseif($_POST['login']) {
		if((trim($_POST['username']) != '') && (trim($_POST['password']) != '') && (trim($_POST['server']) != '')){
			// scrub the input
			$username = addslashes(trim($_POST['username']));
			$password = addslashes(trim($_POST['password']));
			$server   = addslashes(trim($_POST['server']));
			// need to build this better
			$connectString = '{pop.gmail.com:995/pop3/ssl/novalidate-cert/notls}INBOX';

			$mbox = imap_open($connectString, $username, $password);
			if($mbox){
				$num_msg = imap_num_msg($mbox);
				echo '<div class="message">'.$num_msg.' '.translate(pluralize('message',$num_msg),$lang).' '.translate(pluralize('is',$num_msg),$lang).' '.translate('waiting for your attention.',$lang).'</div>';
				
				renderLogout($device,$lang);
				
				// setup session variables so we can loop through emails individually w/o having to constantly login
				$_SESSION['inTriage'] = true;

				// clean-up
				imap_expunge($mbox);
				imap_close($mbox);
				
			} else {
				echo '<div class="error">'.translate('There was an error connecting to',$lang).' '.$server.'. '.translate('Please try again',$lang).'.</div>';				
				renderLogin($device,$lang);
			}
		} else {
			echo '<div class="error">'.translate('Username, password or server were blank',$lang).'</div>';
			renderLogin($device,$lang);
		}
	} else {
		renderLogin($device,$lang);
	}
	
	echo '</form>';

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
		switch($term){
			case 'is':
			  $term = 'are';
			  break;
			default:
			  $term .= 's';
			  break;
		}
		// do pluralization here
		return $term;
	}
}

function translate($term,$lang){
	switch($lang){
		// copy this setup
		// icelandic
		case 'is':
		  switch($term){
			case 'Logout':
			  return 'Skrá út';
			  break;
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

function renderLogin($device,$lang){
	// display login
	echo '<div><label>'.translate('Username',$lang).': <input type="text" name="username" /></label></div>';
	echo '<div><label>'.translate('Password',$lang).': <input type="password" name="password" /></label></div>';
	echo '<div><label>'.translate('Server',$lang).': <input type="text" name="server" /></label></div>';
	echo '<div><input type="submit" name="login" value="'.translate('Perform Triage!',$lang).'" /></div>';
}

function renderLogout($device,$lang){
	echo '<input type="submit" value="'.translate('Logout',$lang).'" name="logout" />';
}

?>