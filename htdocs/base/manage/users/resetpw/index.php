<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/users/resetpw/index.php - Reset password
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../setup.inc";

include "../../../doauth.inc";

include_once("class.phpmailer.php");


$user = new user();
$user->username = $_SESSION['username'];
$user->load();


if ($user->class == 'customer') {
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
	
} else if ($user->class == 'reseller') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
	
}

$user2 = new user();

if (!isset($_REQUEST['username'])) {
	
	echo "No Username provided";
	exit(1);
	
}

$user2->username = $_REQUEST['username'];

if (!$user2->exist()) {
	
	echo "Username does not exist";
	exit(1);
	
}
$user2->load();


if (isset($_REQUEST['yes'])) {
	
	// Edituser
	$error_msg = '';

	$user2->password = generatePassword(8,8);

	$userpassword = $user2->password;

	$vc = $user2->validate();

	if ($vc != 0) {
	
		$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

	} else {
		$user2->save();

		// Send welcome note:
    $mail = new PHPMailer();
    
    $mail->From     = "noreply@xi.com.au";
    $mail->FromName = "X Integration Pty Ltd";
    $mail->Subject  = "New Password";
    $mail->Host     = "127.0.0.1";
    $mail->Mailer   = "smtp";
    
    // Plain text body (for mail clients that cannot read HTML)
    $text_body  = "Hello " . $user2->first_name . ",\r\n";
    $text_body .= "\r\n";
    $text_body .= "This is an automatically generated email, please do not reply.\r\n";
    $text_body .= "\r\n";
    $text_body .= "Your account has had it's password reset. Your new password is below.\r\n";
    $text_body .= "\r\n";
    $text_body .= "To begin, please goto http://simplicity.xi.com.au/ and login.\r\n";
    $text_body .= "Your username is " . $user2->username . "\r\n";
    $text_body .= "Your password is " . $userpassword . "\r\n";
    $text_body .= "\r\n";
    $text_body .= "Please remember to change your password when you login to something you will remember.\r\n";
    $text_body .= "\r\n";
    
    $mail->Body    = $text_body;
    $mail->AddAddress($user2->email);
    
    if ($user2->email2 != '') {
	    $mail->AddAddress($user2->email2);
  	} 
  	 
    if (!$mail->Send()) {
        echo "There has been a mail error sending to " . $user2->email . ", they will not have receievd their password!";
    }
    
		$pt->setVar('ERROR_MSG','Password reset.' );
    
	}
} else if (isset($_REQUEST['no'])) {
	
	$url = "";
        
  if (isset($_SERVER["HTTPS"])) {
      
    $url = "https://";
        
  } else {
      
    $url = "http://";
  }

  $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/users/edit/?username=" . $user2->username;

  header("Location: $url");
  exit();		
    
}

$pt->setVar('USERNAME', $user2->username);
$pt->setVar('LAST_NAME', $user2->last_name);
$pt->setVar('FIRST_NAME', $user2->first_name);
$pt->setVar('EMAIL', $user2->email);
		
$pt->setVar("PAGE_TITLE", "Reset User Password");

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html", "main" => "base/manage/users/resetpw/index.html"));
		
// Parse the main page
$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside1");

// Print out the page
$pt->p("WEBPAGE");


function generatePassword($length=9, $strength=0) {
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUY";
	}
	if ($strength & 4) {
		$consonants .= '23456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}
 
	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}
?>
