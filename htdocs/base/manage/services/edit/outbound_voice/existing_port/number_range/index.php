<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/outbound_voice/existing_port/number_range/index.php - Edit Outbound Voice
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../../../setup.inc";
include "../../../../../../doauth.inc";
include_once "customers.class";
include_once "services.class";
include_once "plans.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/number_range/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/number_range/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/number_range/index.html"));
  
}

if ( !isset($_REQUEST["service_id"]) ) {
  echo "Invalid Service ID.";
  exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$plan = new plans();
$plan->plan_id = $service->retail_plan_id;
$plan->load();

if ( $plan->sub_type == "PSTN" ) {

  $_SESSION["outbound_voice"]["kind"] = $_REQUEST["outbound_kind"];

    // Done, goto list
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/outbound_voice/existing_port/creation/?service_id=" . $service->service_id ;

    header("Location: $url");
    exit();

}

if ( isset($_REQUEST["submit"]) ) {

  $_SESSION["outbound_voice"]["kind"] = $plan->sub_type;
  $_SESSION["outbound_voice"]["existing_port"] = "no";
  $_SESSION["outbound_voice"]["number_range"] = $_REQUEST["number_range"];

    // Done, goto list
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/outbound_voice/existing_port/simultaneous_calls/?service_id=" . $service->service_id ;

    header("Location: $url");
    exit();
}

$pt->setVar("SERVICE_ID",$_REQUEST["service_id"]);

if ( isset($_REQUEST["existing_port"]) ) {
  $pt->setVar(strtoupper($_REQUEST["existing_port"]), "checked");
} else {
  $pt->setVar("YES", "checked");
}

$pt->setVar("PAGE_TITLE", "Outbound Voice");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");