<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/index.php - Select Kind of Service
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
include_once "postcodes.class"; 
include_once "config.class";
include_once "customers.class";
include_once "services.class";
include_once "service_types.class";
include_once "orders.class";
include_once "wholesaler_service_types.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("service_option" => "base/manage/wholesalers/service_option.html",
                    "option_adsl_nbn" => "base/manage/services/add/option_adsl_nbn.html",
                    "option_adsl_nbn2" => "base/manage/services/add/option_adsl_nbn2.html",
                    "option_inbound_voice" => "base/manage/services/add/option_inbound_voice.html",
                    "option_outbound_voice" => "base/manage/services/add/option_outbound_voice.html"));

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
  exit();
}

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

$services = new services();
$services->customer_id = $customer->customer_id;
$services_all = $services->get_all();

$service_nums = array();

for ($a=0; $a < count($services_all); $a++) { 
  $service_nums[] = $services_all[$a]["service_id"];
}

if ( !isset($_REQUEST["service_kind"]) ) {
  $pt->setVar("ADSL_NBN", " checked");
} else {
  $pt->setVar(strtoupper($_REQUEST["service_kind"]), " checked");
}

if ( isset($_REQUEST["submit"]) && isset($_REQUEST["service_kind"]) ) {

  // Done, goto list
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/" . $_REQUEST["service_kind"] . "/?customer_id=" . $customer->customer_id;

    header("Location: $url");
    exit();

}

$ws_service_types = new wholesaler_service_types();
$ws_service_types->wholesaler_id = $customer->wholesaler_id;
$services = $ws_service_types->get_wholesaler_services();

$services_str = "";

if ( $services ) {
  $adsl_nbn_count = 0;
  for ( $x = 0; $x < count($services); $x++ ) {

    switch ($services[$x]["type_id"]) {
      case '1':
      case '2':
        if ( $adsl_nbn_count == 0 ) {
          //$pt->parse("KINDS_SERVICES","option_adsl_nbn","true");
          
          $pt->parse("KINDS_SERVICES","option_adsl_nbn2","true");
          $adsl_nbn_count = 1;
        }
        break;
      case '5':
        $pt->parse("KINDS_SERVICES","option_inbound_voice","true");
        break;
      case '6':
        $pt->parse("KINDS_SERVICES","option_outbound_voice","true");
        break;
      default:
        # code...
        break;
    }

    $pt->setVar('TYPE_CHECK'.$services[$x]["type_id"], ' checked');
    $service_desc = new service_types();
    $service_desc->type_id = $services[$x]["type_id"];
    $service_desc->load();
    $services_str .= $service_desc->description . ", ";
  }
}

$pt->setVar("CUSTOMER_ID",$_REQUEST["customer_id"]);

$pt->setVar("PAGE_TITLE", "Kind of Service");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");