<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/users/delete/index.php - Delete user
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
	
	$pt->setFile(array("outside" => "base/outside.html", "main" => "base/accessdenied.html"));
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
	
	// Delete
	$user2->delete();

}

if (isset($_REQUEST['no']) || isset($_REQUEST['yes'])) {
	
	$url = "";
        
  if (isset($_SERVER["HTTPS"])) {
      
    $url = "https://";
        
  } else {
      
    $url = "http://";
  }

  $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/users/";

  header("Location: $url");
  exit();		
    
}

$pt->setVar('USERNAME', $user2->username);
$pt->setVar('LAST_NAME', $user2->last_name);
$pt->setVar('FIRST_NAME', $user2->first_name);
$pt->setVar('EMAIL', $user2->email);
		
$pt->setVar("PAGE_TITLE", "Reset User Password");

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html","outside2" => "base/outside2.html", "main" => "base/manage/users/delete/index.html"));
		
// Parse the main page
$pt->parse("MAIN", "main");

// Correct outside
if ($user->class != 'customer' || $user->class != 'reseller') {
	$pt->parse("WEBPAGE", "outside1");
} else {
	$pt->parse("WEBPAGE", "outside2");
}	

// Print out the page
$pt->p("WEBPAGE");



?>
