<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/outbound_voice/existing_port/simultaneous_calls/index.php - Outbound Voice
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
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/outbound_voice/existing_port/simultaneous_calls/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/outbound_voice/existing_port/simultaneous_calls/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/outbound_voice/existing_port/simultaneous_calls/index.html"));
  
}

//Assign templates to use
$pt->setFile(array("isdn_pri_option" => "base/manage/services/add/outbound_voice/existing_port/simultaneous_calls/isdn_pri_option.html",
                    "sip_trunk_option" => "base/manage/services/add/outbound_voice/existing_port/simultaneous_calls/sip_trunk_option.html"));

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
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

if ( isset($service_data["outbound_voice"]) ) {
  if ( $service_data["outbound_voice"]["kind"] != "ISDN_PRI" && $service_data["outbound_voice"]["kind"] != "SIP_TRUNK" ) {
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
  if ( $service_data["outbound_voice"]["kind"] == "ISDN_PRI" ) {
    $pt->parse("SIMUL_OPTION","isdn_pri_option","true");
  }
  if ( $service_data["outbound_voice"]["kind"] == "SIP_TRUNK" ) {
    $pt->parse("SIMUL_OPTION","sip_trunk_option","true");
  }
}

if ( isset($_REQUEST["submit"]) ) {

  if ( isset($_REQUEST["simultaneous_calls"]) ) {

    if ( $_REQUEST["simultaneous_calls"] < 5 ) {
      $pt->setVar("ERROR_MSG","Error: Simultaneous Calls should be greater than 5.");
    } else {
      // Done, goto list

      $service_data["outbound_voice"]["simultaneous_calls"] = $_REQUEST["simultaneous_calls"];
      $service_temp->data = serialize($service_data);
      
      $service_temp->save();

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
  }

}

if ( isset($_REQUEST["simultaneous_calls"]) ) {
  $pt->setVar("SIMUL_VALUE",$_REQUEST["simultaneous_calls"]);
}

$pt->setVar("CUSTOMER_ID",$_REQUEST["customer_id"]);
$pt->setVar("SP",$_REQUEST['sp']);

$pt->setVar("PAGE_TITLE", "Outbound Voice");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");