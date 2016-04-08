<?php
$mail = new PHPMailer();

$mail->From     = "noreply@xi.com.au";
$mail->FromName = "X Integration Pty Ltd";
$mail->Subject = "Account created";
$mail->Host     = "127.0.0.1";
$mail->Mailer   = "smtp";

// Plain text body (for mail clients that cannot read HTML)
$text_body  = "Hello " . $user2->first_name . ",\r\n";
$text_body .= "\r\n";
$text_body .= "This is an automatically generated email, please do not reply.\r\n";
$text_body .= "\r\n";
$text_body .= "Your account has now been setup for X Integration.\r\n";
$text_body .= "\r\n";
$text_body .= "To begin, please goto http://simplicity.xi.com.au/ and login.\r\n";
$text_body .= "Your username is " . $user2->username . "\r\n";
$text_body .= "Your password is " . $userpassword . "\r\n";
$text_body .= "\r\n";
$text_body .= "Please remember to change your password when you login to something you will remember.\r\n";
$text_body .= "\r\n";

$mail->Body    = $text_body;
$mail->AddAddress($user2->email);

//if (!$mail->Send()) {
//    echo "There has been a mail error sending to " . $user2->email . ", they will not have receievd their password!";
//}