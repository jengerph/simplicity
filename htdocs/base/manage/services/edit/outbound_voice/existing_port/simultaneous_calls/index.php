<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/outbound_voice/existing_port/simultaneous_calls/index.php - Outbound Voice
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


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/simultaneous_calls/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/simultaneous_calls/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/simultaneous_calls/index.html"));
  
}

//Assign templates to use
$pt->setFile(array("isdn_pri_option" => "base/manage/services/edit/outbound_voice/existing_port/simultaneous_calls/isdn_pri_option.html",
                    "sip_trunk_option" => "base/manage/services/edit/outbound_voice/existing_port/simultaneous_calls/sip_trunk_option.html"));

if ( !isset($_REQUEST["service_id"]) ) {
  echo "Invalid Service ID.";
  exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

if ( isset($_SESSION["outbound_voice"]) ) {
  if ( $_SESSION["outbound_voice"]["kind"] != "ISDN_PRI" && $_SESSION["outbound_voice"]["kind"] != "SIP_TRUNK" ) {
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
  if ( $_SESSION["outbound_voice"]["kind"] == "ISDN_PRI" ) {
    $pt->parse("SIMUL_OPTION","isdn_pri_option","true");
  }
  if ( $_SESSION["outbound_voice"]["kind"] == "SIP_TRUNK" ) {
    $pt->parse("SIMUL_OPTION","sip_trunk_option","true");
  }
}

if ( isset($_REQUEST["submit"]) ) {

  if ( isset($_REQUEST["simultaneous_calls"]) ) {

    if ( $_REQUEST["simultaneous_calls"] < 5 ) {
      $pt->setVar("ERROR_MSG","Error: Simultaneous Calls should be greater than 5.");
    } else {
      // Done, goto list

      $_SESSION["outbound_voice"]["simultaneous_calls"] = $_REQUEST["simultaneous_calls"];

        $url = "";
            
        if ( isset($_SERVER["HTTPS"]) ) {
            
          $url = "https://";
              
        } else {
            
          $url = "http://";
        }

          $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/outbound_voice/existing_port/creation/?serivce_id=" . $service->service_id ;

        header("Location: $url");
        exit();
    }
  }

}

if ( isset($_REQUEST["simultaneous_calls"]) ) {
  $pt->setVar("SIMUL_VALUE",$_REQUEST["simultaneous_calls"]);
}

$pt->setVar("SERVICE_ID",$_REQUEST["service_id"]);

$pt->setVar("PAGE_TITLE", "Outbound Voice");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");