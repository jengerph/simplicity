<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/changepw/index.php - Change password
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../setup.inc";

include "../doauth.inc";

if (isset($_REQUEST['submit'])) {
	
	// CHange password
	$error_msg = '';
	if ($_REQUEST['newpassword1'] != $_REQUEST['newpassword2']) {
		
		$error_msg = 'New passwords must match.';
		
	} else if (md5($_REQUEST['password']) != $_SESSION['password']) {
		
		$error_msg = 'Current password is incorrect.';
		
	} else if (strlen($_REQUEST['newpassword2']) < 4) {
		
		$error_msg = 'New password is too short.';
	
	} else {
		
		$user = new user();
  	$user->username = $_SESSION['username'];
  	$user->load();
  
  	$user->password = $_REQUEST['newpassword1'];
  	$user->save();
  
  	$_SESSION['password'] = $user->password;
  	
  	$error_msg = 'Password changed';
  	
  }

	$pt->setVar("ERROR_MSG", $error_msg);
  
}

  
		
$pt->setVar("PAGE_TITLE", "Change Password");

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html","outside2" => "base/outside2.html","outside3" => "base/outside3.html", "main" => "base/changepw/index.html"));
// Parse the main page
$pt->parse("MAIN", "main");
if ($user->class == 'admin' ) {
	$pt->parse("WEBPAGE", "outside1");
} else if ($user->class == 'customer') {
	$pt->parse("WEBPAGE", "outside2");
} else if ( $user->class == 'reseller' ) {
	$pt->parse("WEBPAGE", "outside3");
}	

// Print out the page
$pt->p("WEBPAGE");



?>
