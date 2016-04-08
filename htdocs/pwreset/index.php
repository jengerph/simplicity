<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/reset/index.php - Reset a password
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////


// Get the path of the include files
include_once "../setup.inc";

include_once "user.class";
include_once "pwreset.class";


$pt->setVar("PAGE_TITLE", "Reset a lost password");


$pwr = new pwreset();


if (isset($_REQUEST['submit'])) {

	$pwr->email = $_REQUEST['email'];
  
	$vc = $pwr->validate();

	if ($vc != 0) {
	
		$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

	} else {
		
		// Do we have any users with that email address?
		$user = new user();
		
		$list = $user->search('email',$pwr->email,'username','ASC');
		
		if (sizeof($list) == 0) {

      $mail = new PHPMailer();
      
      $mail->From     = "noreply@xi.com.au";
      $mail->FromName = "X Integration Pty Ltd";
      $mail->Subject  = "Password reset request";
      $mail->Host     = "127.0.0.1";
      $mail->Mailer   = "smtp";
      
      // Plain text body (for mail clients that cannot read HTML)
      $text_body  = "Hello,\r\n";
      $text_body .= "\r\n";
      $text_body .= "This is an automaticly generated message, please do not reply.\r\n";
      $text_body .= "\r\n";
      $text_body .= "Someone reqested a password reset for an account associated with your email address at X Integration.\r\n";
      $text_body .= "\r\n";
      $text_body .= "After checking our database, we were unable to find any accounts registered to the email address " . $pwr->email . "\r\n";
      $text_body .= "\r\n";
      $text_body .= "We are unable to process this request any further.\r\n";
      
      $mail->Body    = $text_body;
      $mail->AddAddress($pwr->email);
      
      if (!$mail->Send()) {
        echo "There has been a mail error sending to " . $pwr->email;
      }
        
		
		} else {

			$pwr->create();

      $mail = new PHPMailer();
      
      $mail->From     = "noreply@xi.com.au";
      $mail->FromName = "X Integration Pty Ltd";
      $mail->Subject  = "Password reset request";
      $mail->Host     = "127.0.0.1";
      $mail->Mailer   = "smtp";
      
      // Plain text body (for mail clients that cannot read HTML)
      $text_body  = "Hello,\r\n";
      $text_body .= "\r\n";
      $text_body .= "This is an automaticly generated message, please do not reply.\r\n";
      $text_body .= "\r\n";
      $text_body .= "Someone reqested a password reset for an account associated with your email address at X Integration.\r\n";
      $text_body .= "\r\n";
      $text_body .= "If this was you, and you wish to recover access to your account, please goto: http://simplicity.xi.com.au/pwreset/go/?reset_id=" . $pwr->reset_id . '&token=' . md5($pwr->reset_id . '-' . $pwr->email . '-' . $pwr->dt) . "\r\n";
      $text_body .= "\r\n";
      $text_body .= "If this was not you, please discard this email and no further action will be taken.\r\n";
      
      $mail->Body    = $text_body;
      $mail->AddAddress($pwr->email);
      
      if (!$mail->Send()) {
        echo "There has been a mail error sending to " . $pwr->email;
      }
			
		}

		$pt->setFile(array("main" => "pwreset/finish.html"));

		// Parse the main page
		$pt->parse("WEBPAGE", "main");

		// Print out the page
		$pt->p("WEBPAGE");
		
		exit();
	}

}
// Assign the templates to use
$pt->setFile(array("main" => "pwreset/index.html"));

$pt->setVar('EMAIL', $pwr->email);

// Parse the main page
$pt->parse("WEBPAGE", "main");


// Print out the page
$pt->p("WEBPAGE");
