<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/outbound_voice/index.php - Outbound Voice
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../setup.inc";
include "../../../../doauth.inc";
include_once "services.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/outbound_voice/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/outbound_voice/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/outbound_voice/index.html"));
  
}

if ( !isset($_REQUEST["service_id"]) ) {
  echo "Invalid Service ID.";
  exit();
}

$services = new services();
$services->service_id = $_REQUEST["service_id"];
$services->load();

if ( isset($_REQUEST["submit"]) ) {
	// Done, goto list
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/outbound_voice/delivery_address/?service_id=" . $services->service_id . "&outbound_kind=" . $_REQUEST["outbound_kind"];

    header("Location: $url");
    exit();
}

$pt->setVar("SERVICE_ID",$_REQUEST["service_id"]);
if ( isset($_REQUEST["outbound_kind"]) ) {
	$pt->setVar($_REQUEST["outbound_kind"], "checked");
} else {
	$pt->setVar("PSTN", "checked");
}

$pt->setVar("PAGE_TITLE", "Outbound Voice");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");