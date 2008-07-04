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
		unset($_SESSION['username']);
		unset($_SESSION['password']);
		unset($_SESSION['connectionString']);
		unset($_SESSION['inTriage']);
		unset($_SESSION['msg_num']);
		unset($_SESSION['msg_total']);
		
		
		echo '<div class="message">'.translate('Logged out',$lang).'</div>';
		renderLogin($device,$lang); 
		
	} elseif($_SESSION['inTriage']){
		// need to reconnect and create an $mbox
		$mbox = imap_open($_SESSION['connectString'], $_SESSION['username'], $_SESSION['password']);
		
		if($_POST['delete']){
			// delete
			imap_delete($mbox,$_SESSION['msg_num']);
			$_SESSION['msg_total']--;
			imap_expunge($mbox);
		} elseif ($_POST['act']) {
			echo 'reply box here';
		} elseif ($_POST['defer']){
			// do not mark as read, but skip it
			$_SESSION['msg_num']++;			
		} else {
			// ignore
			//mark as read THIS IS NOT working with POP3 Grrrr!
			$status = imap_setflag_full($mbox, $_SESSION['msg_num'], "\Seen");
			$_SESSION['msg_num']++;
		}
		
		// get next message
		$header = imap_header($mbox, $_SESSION['msg_num']);
		$from    = $header->fromaddress;
		$subject = $header->subject;
		
		renderMessageCount($_SESSION['msg_num'],$_SESSION['msg_total'],$lang);
		renderPreview($from,$subject,$lang);
		renderLogout($device,$lang);
		
		// clean-up
		imap_close($mbox);
	} elseif($_POST['login']) {
		if((trim($_POST['username']) != '') && (trim($_POST['password']) != '') && (trim($_POST['server']) != '')){
			// scrub the input
			$username = addslashes(trim($_POST['username']));
			$password = addslashes(trim($_POST['password']));
			$server   = addslashes(trim($_POST['server']));
			// need to build this better
			$connectString = '{pop.gmail.com:995/pop3/ssl/novalidate-cert/notls}INBOX';
			//$connectString = '{imap.gmail.com:993/imap/ssl}INBOX';

			$mbox = imap_open($connectString, $username, $password);
			if($mbox){
				//$num_msg = imap_num_msg($mbox);
				$num_msg = imap_status($mbox,$_SESSION['connectString'], SA_UNSEEN);
				$num_msg = $num_msg->unseen;

				if($num_msg > 0){
				  echo 'Begin';
				  renderMessageCount(1,$num_msg,$lang);
				  
				  $header = imap_header($mbox, 1);
				  $from    = $header->fromaddress;
				  $subject = $header->subject;
				  				  
				  renderPreview($from,$subject,$lang);
				} else {
				  echo '<div class="message">Your Inbox is empty!</div>';
				}
				
				renderLogout($device,$lang);
				
				// setup session variables so we can loop through emails individually w/o having to constantly login
				$_SESSION['inTriage'] = true;
				$_SESSION['msg_num']  = 1;
				$_SESSION['username'] = $username;
				$_SESSION['password'] = $password;
				$_SESSION['connectString']   = $connectString;
				$_SESSION['msg_total'] = $num_msg;
				
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

function renderPreview($from,$subject,$lang){
  echo '<div>FROM: '.htmlspecialchars($from).'</div>';
  echo '<div>SUBJECT: '.htmlspecialchars($subject).'</div>';

  echo '<input type="submit" name="delete" value="DELETE" />';
  echo '<input type="submit" name="act" value="ACT" />';
  echo '<input type="submit" name="defer" value="DEFER" />';
  echo '<input type="submit" name="ignore" value="IGNORE" />';
}

function renderMessageCount($pos,$total,$lang){
  echo '<div class="message">'.$pos.'/'.$total.' '.translate(pluralize('message',$total),$lang).'</div>';
}

?>