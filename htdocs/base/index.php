<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/index.php - Display main page
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../setup.inc";

include "doauth.inc";
include_once("login_history.class");

$pt->setVar("PAGE_TITLE", "Main Menu");

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html", "outside2" => "base/outside2.html", "main1" => "base/main1.html", "main2" => "base/main2.html"));

// Get last login
$lh = new login_history();
$lh->username = $_SESSION['username'];
$history = $lh->getUser($_SESSION['username']);

if (isset($history[1])) {
  $lh->dt = $history[1];
  $lh->load();

  $pt->setVar('LOGIN_LAST', $misc->date_nice($lh->dt));
  $pt->setVar('LOGIN_FROM', $lh->hostname);

} else {
  $pt->setVar('LOGIN_LAST', 'never');
  $pt->setVar('LOGIN_FROM', 'nowhere');
}

if ($user->class == 'customer') {

  // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/customers/";

    header("Location: $url");
    exit();   

} else if ($user->class == 'admin'){

	// Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/";

    header("Location: $url");
    exit();  
} else if ($user->class == 'reseller'){

  // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/";

    header("Location: $url");
    exit();  
} 


// Print out the page
$pt->p("WEBPAGE");



?>
