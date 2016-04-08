<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/order/qualify/index.php - confirm Results
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../../setup.inc";

include "../../../../../doauth.inc";
include_once "services.class";
include_once "service_types.class";
include_once "plans.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

$service = new services();
$service->service_id = $_REQUEST['service_id'];
$service->load();

if ($user->class == 'customer') {
  
  $pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/order/qualify/index.html"));

  if ( $user->access_id != $service->customer_id ) {
    $pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
    // Parse the main page
    $pt->parse("MAIN", "main");
    $pt->parse("WEBPAGE", "outside");

    // Print out the page
    $pt->p("WEBPAGE");

    exit();
  }
  
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/order/qualify/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/order/qualify/index.html"));
  
}

$pt->setFile(array("adsl_option" => "base/manage/services/add/qualify/adsl_option.html", "services_available" => "base/manage/services/add/qualify/services_available.html"));

if ( !isset($_REQUEST['service_id']) ) {
  echo "Service ID invalid";
  exit(1);
}

$address = "";

if ( isset($_REQUEST["submit"]) ) {
  if ( !isset($_REQUEST['service_available']) || $_REQUEST['service_available'] == "" ) {
    $pt->setVar('ERROR_MSG','Error: Service Type invalid.');
  } else{

    $_SESSION['order_service_available'] = $_REQUEST["service_available"];
  // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/order/qualify/confirm/?service_id=" . $_REQUEST["service_id"];

    header("Location: $url");
    exit();
  }
}

if ( isset($_SESSION["fnn"]) && $_SESSION["fnn"] != "" ) {
  if ( $_SESSION["fnn"] == "no" ) {

      $address = $_SESSION["service_qualify_array"][0]["siteAddress"]->streetNumber . " " . $_SESSION["service_qualify_array"][0]["siteAddress"]->streetName . ", " . $_SESSION["service_qualify_array"][0]["siteAddress"]->suburb . ", " . $_SESSION["service_qualify_array"][0]["siteAddress"]->state  . ", " . $_SESSION["service_qualify_array"][0]["siteAddress"]->postcode;
      $_SESSION["order_address"] = $address;

      $pt->setVar("SERVICE_NUMBER", "-");
  } else if ( $_SESSION["fnn"] == "yes" ) {
      $pt->setVar("SERVICE_NUMBER", $_SESSION["order_service_number"]);
      $pt->parse("ADSL_OPTION","adsl_option","true");
      $address = $_SESSION["service_qualify_array"][0]["siteAddress"]->streetNumber . " " . $_SESSION["service_qualify_array"][0]["siteAddress"]->streetName . ", " . $_SESSION["service_qualify_array"][0]["siteAddress"]->suburb . ", " . $_SESSION["service_qualify_array"][0]["siteAddress"]->state  . ", " . $_SESSION["service_qualify_array"][0]["siteAddress"]->postcode;
      $_SESSION["order_address"] = $address;
    }
}

$plan = new plans();
$plan->plan_id = $_SESSION["edit_retail_plan"];
$plan->load();

$service_type = new service_types();
$service_type->type_id = $service->type_id;
$service_type->load();

  $services_array = array();
for ($a=0; $a < count($_SESSION["service_qualify_array"]); $a++) { 
  $result_keys = array_keys($_SESSION["service_qualify_array"][$a]["results"]);

  for ($i=0; $i < count($result_keys); $i++) { 
    if (isset($_SESSION["service_qualify_array"][$a]["results"][$result_keys[$i]])){
      $pre = $_SESSION["service_qualify_array"][$a]["results"][$result_keys[$i]];
      $array_keys = array_keys($pre);
      if (count($pre) != 0){
        for ($j=0; $j < count($pre); $j++) { 
        if ($plan->access_method == $pre[$array_keys[$j]]["accessMethod"]){
            $type = " " . $pre[$array_keys[$j]]["accessMethod"];
            if ( $pre[$array_keys[$j]]["accessMethod"] == $result_keys[$i] ) {
              $type = " " . $pre[$array_keys[$j]]["accessType"];
            }
            // if ( isset($pre[$array_keys[$j]]["type"]) ) {
            //   $type = " " . $pre[$array_keys[$j]]["type"];
            // } else {
            //   $type = " " . $pre[$array_keys[$j]]["accessType"];
            // }
            // if ( preg_match('/On Net/',$pre[$array_keys[$j]]["accessMethod"]) ) {
            //   $type = " OnNet";
            // }
            if ( is_array($pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed) ) {
              for ($k=0; $k < count($pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed); $k++) { 
                // if (preg_match("#$plan->speed#", $pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed->serviceSpeed)){
                  $services_array[] = $result_keys[$i] . $type . " " . $pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed[$k]->serviceSpeed;
                // }
              }
            } else {
                // if (preg_match("#$plan->speed#", $pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed->serviceSpeed)){
                  $services_array[] = $result_keys[$i] . $type . " " . $pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed->serviceSpeed;
                // }
              }
            }
        }
      }
    }
  }
}


$pt->setVar("SERVICE_ID", $_REQUEST['service_id']);
$pt->setVar("ORDER_ADDRESS", $address);
$pt->setVar("SERVICE_TYPE",$service_type->description);

for ($k=0; $k < count($services_array); $k++) { 
  $pt->setVar("SERVICE_VALUE",preg_replace("/\([^)]+\)/","",$services_array[$k]));
  $pt->parse("SERVICES_AVAILABLE","services_available","true");
}
    
if ( count($services_array) == 0 ) {
  $pt->setVar("ERROR_MSG","Error: No Service Available");
}

// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function goto_next(){
  // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/customers/services/qualify/?customer_id=" . $_REQUEST["customer_id"];

    header("Location: $url");
    exit();
}