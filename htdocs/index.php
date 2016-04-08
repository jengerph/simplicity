<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/index.php - Display main page
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////


// Get the path of the include files
include_once "setup.inc";
include_once "auth.inc";
include_once "login_history.class";

$pt->setVar("PAGE_TITLE", "Login");


if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {

  $_SESSION['username'] = $_REQUEST['username'];
  $_SESSION['password'] = md5($_REQUEST['password']);
  $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
  
}
  
if (isset($_REQUEST['forgot'])) {
    // Password recovery
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }
    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/pwreset/";
		 
    header("Location: $url");
    exit();

}
if (isset($_SESSION['username']) && isset($_SESSION['password'])) {

  if (verify_access($_SESSION['username'], $_SESSION['password'], array(0)) || ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR'])) {
  
    // Record login history
    $lh = new login_history();
    $lh->username = $_SESSION['username'];
    $lh->ip = $_SERVER["REMOTE_ADDR"];
    $lh->hostname = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
    $lh->sid = session_id();
    $lh->create();
  
    // Access approved, goto menu
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

		$user = new user();
		$user->username = $_SESSION['username'];
		$user->load();
    
    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/";
		 
    header("Location: $url");
    exit();

  } else {
    
    $pt->setVar("ERROR_MSG", "Access denied.");
    $pt->setVar("USERNAME", $_SESSION['username']);
    $_SESSION['username'] = $_REQUEST['username'];
    $_SESSION['password'] = "";
    
  }
  
}
  

// Assign the templates to use
$pt->setFile(array("main" => "index.html"));
		
// Parse the main page
$pt->parse("WEBPAGE", "main");


// Print out the page
$pt->p("WEBPAGE");



?>
