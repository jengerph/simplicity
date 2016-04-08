<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/outbound_voice/existing_port/number_range/index.php - Outbound Voice
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
include_once "service_temp.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/outbound_voice/existing_port/number_range/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/outbound_voice/existing_port/number_range/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/outbound_voice/existing_port/number_range/index.html"));
  
}

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
  exit();
}

if ( !isset($_REQUEST["outbound_kind"]) ) {
  echo "No Outbound Voice Kind selected.";
  exit();
}

if ( !isset($_REQUEST['sp']) || empty($_REQUEST['sp']) ) {
  echo "URL invalid";
  exit();
}

$session_pointer0 = $_REQUEST['sp'];
$session_pointer = $_REQUEST['customer_id'] . "_" . $_REQUEST['sp'];

$service_temp = new service_temp();
$service_temp->data_key = $session_pointer;
$service_temp->load();

$service_data = unserialize($service_temp->data);

$customer = new customers();
$customer->customer_id = $_REQUEST["customer_id"];
$customer->load();

if ( $user->class == 'customer' ) {
  if ( $customer->customer_id != $user->access_id ) {
    $pt->setFile(array("main" => "base/accessdenied.html"));
  }
} else if ( $user->class == 'reseller' ) {
  if ( $customer->wholesaler_id != $user->access_id ) {
    $pt->setFile(array("main" => "base/accessdenied.html"));
  }
}

if ( $_REQUEST["outbound_kind"] == "PSTN" ) {

  $service_data["outbound_voice"]["kind"] = $_REQUEST["outbound_kind"];
  $service_temp->data = serialize($service_data);
  $service_temp->save();

    // Done, goto list
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/outbound_voice/existing_port/creation/?customer_id=" . $customer->customer_id . "&sp=" . $session_pointer0;

    header("Location: $url");
    exit();

}

if ( isset($_REQUEST["submit"]) ) {
  $service_data["outbound_voice"]["kind"] = $_REQUEST["outbound_kind"];
  $service_data["outbound_voice"]["existing_port"] = "no";
  $service_data["outbound_voice"]["number_range"] = $_REQUEST["number_range"];

  $service_temp->data = serialize($service_data);
  $service_temp->save();

    // Done, goto list
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/outbound_voice/existing_port/simultaneous_calls/?customer_id=" . $customer->customer_id . "&sp=" . $session_pointer0;

    header("Location: $url");
    exit();
}

$pt->setVar("OUTBOUND_KIND",$_REQUEST["outbound_kind"]);
$pt->setVar("CUSTOMER_ID",$_REQUEST["customer_id"]);
$pt->setVar("SP",$_REQUEST['sp']);

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