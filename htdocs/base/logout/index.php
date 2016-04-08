<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/cohen/base/logout/index.php - Logout
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

$_SESSION['password'] = '';

$pt->setVar("USERNAME", $_SESSION['username']);

// Poke history
$lh = new login_history();
$lh->sid = session_id();
$lh->username = $user->username;
$lh->logout();


// Destroy session
session_destroy();

// Done, goto game list
$url = "http://" . $_SERVER["SERVER_NAME"];

header("Location: $url");
exit();		
      
$pt->setVar("PAGE_TITLE", "Logout");

// Assign the templates to use
$pt->setFile(array("main" => "base/logout/index.html"));
		
// Parse the main page
$pt->parse("WEBPAGE", "main");

// Print out the page
$pt->p("WEBPAGE");



?>
