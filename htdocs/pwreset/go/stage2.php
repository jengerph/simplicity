<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/reset/go/stage2.php - Reset a password - stage 2
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////


// Get the path of the include files
include_once "../../setup.inc";

include_once "user.class";
include_once "pwreset.class";

$pt->setVar("PAGE_TITLE", "Reset a lost password");


$pwr = new pwreset();

if (!isset($_REQUEST['reset_id'])) {
	echo "Invalid options.";
	exit();
}

$pwr->reset_id = $_REQUEST['reset_id'];

if (!$pwr->exist()) {
	echo "Invalid options.";
	exit();
}

$pwr->load();

if (!isset($_REQUEST['token'])) {
	echo "Invalid options.";
	exit();
}

if (md5($pwr->reset_id . '-' . $pwr->email . '-' . $pwr->dt) != $_REQUEST['token']) {
	echo "Invalid options.";
	exit();
}

if ($misc->date_ts($pwr->dt) < (time() - 172800)) {
	echo "Invalid options.";
	exit();
}
// User
if (!isset($_REQUEST['username'])) {
	echo "Invalid options.";
	exit();
}


$user = new user();
$user->username = $_REQUEST['username'];

if (!$user->exist()) {
	echo "Invalid options.";
	exit();
}

$user->load();

if (strtolower($user->email) != strtolower($pwr->email)) {
	// Attempt to hack
	echo "Invalid options.";
	exit();
}
			
if ($user->active == 'no') {
	// Attempt to hack - disabled account
	echo "Invalid options.";
	exit();
}

if (isset($_REQUEST['newpassword1'])) {
	
	// Password set
	if ($_REQUEST['newpassword1'] != $_REQUEST['newpassword2']) {
		
		$error_msg = 'New passwords must match.';
				
	} else if (strlen($_REQUEST['newpassword2']) < 4) {
		
		$error_msg = 'New password is too short.';
	
	} else {
		  
  	$user->password = $_REQUEST['newpassword1'];
  	$user->save();
  	
  	// Delete reset token
  	$pwr->delete();
  
    // Assign the templates to use
    $pt->setFile(array("main" => "pwreset/go/stage2-finish.html"));
    
    $pt->setVar('RESET_ID', $pwr->reset_id);
    $pt->setVar('TOKEN', $_REQUEST['token']);
    $pt->setVar('USERNAME', $_REQUEST['username']);
    
    	
    // Parse the main page
    $pt->parse("WEBPAGE", "main");
    
    
    // Print out the page
    $pt->p("WEBPAGE");
		exit();
		
  }
}
// Assign the templates to use
$pt->setFile(array("main" => "pwreset/go/stage2.html"));

$pt->setVar('ERROR_MSG', $error_msg);
$pt->setVar('RESET_ID', $pwr->reset_id);
$pt->setVar('TOKEN', $_REQUEST['token']);
$pt->setVar('USERNAME', $_REQUEST['username']);

$pt->setVar('FIRST_NAME', $user->first_name);
$pt->setVar('LAST_NAME', $user->last_name);
  		
// Parse the main page
$pt->parse("WEBPAGE", "main");


// Print out the page
$pt->p("WEBPAGE");


