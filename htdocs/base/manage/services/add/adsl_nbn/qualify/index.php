<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/adsl_nbn/qualify/index.php - Qualify Results
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
include_once "service_temp.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/adsl_nbn/qualify/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/adsl_nbn/qualify/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/adsl_nbn/qualify/index.html"));
  
}

$pt->setFile(array("adsl_option" => "base/manage/services/add/adsl_nbn/qualify/adsl_option.html", "services_available" => "base/manage/services/add/adsl_nbn/qualify/services_available.html"));

if ( !isset($_REQUEST['customer_id']) ) {
  echo "Customer ID invalid";
  exit(1);
}

if ( !isset($_REQUEST['sp']) || empty($_REQUEST['sp']) ) {
  echo "URL invalid";
  exit();
}

$session_pointer = $_REQUEST['customer_id'] . "_" . $_REQUEST['sp'];

$service_temp = new service_temp();
$service_temp->data_key = $session_pointer;
$service_temp->load();

$service_data = unserialize($service_temp->data);

$address = (isset($service_data['order_address'])?$service_data['order_address']:"");

$index = check_index($address,$service_data,$service_data["service_qualify_array"]);

$service_data["order_service_available_index"] = $index;

if ( isset($_REQUEST["submit"]) ) {

  if ( !isset($_REQUEST['service_available']) || $_REQUEST['service_available'] == "" ) {
    $pt->setVar('ERROR_MSG','Error: Service Type invalid.');
  } else{

    $service_data['order_service_available'] = $_REQUEST["service_available"];
    $service_temp = new service_temp();
    $service_temp->data_key = $session_pointer;
    $service_temp->data = serialize($service_data);
    $service_temp->save();

  // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/adsl_nbn/qualify/creation/?customer_id=" . $_REQUEST["customer_id"] . "&sp=" . $_REQUEST['sp'];

    header("Location: $url");
    exit();
  }
}

if ( isset($service_data["fnn"]) && $service_data["fnn"] != "" ) {
  if ( $service_data["fnn"] == "no" ) {
      $pt->setVar("SERVICE_NUMBER", "-");
  } else if ( $service_data["fnn"] == "yes" ) {
      $pt->setVar("SERVICE_NUMBER", $service_data["order_service_number"]);
      $pt->parse("ADSL_OPTION","adsl_option","true");
    }
}

foreach ($service_data["service_qualify_array"][$index]["siteAddress"] as $key => $value) {
  $address .= $value . " ";
}

$address = (isset($service_data["order_address"]) ? $service_data["order_address"] : trim($address));


$services_array = array();
$result_keys = array_keys($service_data["service_qualify_array"][$index]["results"]);

for ($i=0; $i < count($result_keys); $i++) { 
  if (isset($service_data["service_qualify_array"][$index]["results"][$result_keys[$i]])){
    $pre = $service_data["service_qualify_array"][$index]["results"][$result_keys[$i]];
    $array_keys = array_keys($pre);
    if (count($pre) != 0){
      for ($j=0; $j < count($pre); $j++) {
          $pt->setVar("MAXDOWNBAND",$pre[$array_keys[$j]]['maximumDownBandwidth']->value.$pre[$array_keys[$j]]['maximumDownBandwidth']->quantifier);
          $pt->setVar("MAXUPBAND",$pre[$array_keys[$j]]['maximumUpBandwidth']->value.$pre[$array_keys[$j]]['maximumUpBandwidth']->quantifier);
          $pt->setVar("TYPE",$pre[$array_keys[$j]]['type']);
          $pt->setVar("DISTANCETOEXCHANGE",$pre[$array_keys[$j]]['distanceToExchange']);
          $pt->setVar("ACCESSMETHOD",$pre[$array_keys[$j]]['accessMethod']);
          $pt->setVar("ACCESSTYPE",$pre[$array_keys[$j]]['accessType']);
          $pt->setVar("PRICEZONE",$pre[$array_keys[$j]]['priceZone']);
          $type = " " . $pre[$array_keys[$j]]["accessMethod"];
          if ( $pre[$array_keys[$j]]["accessMethod"] == $result_keys[$i] ) {
            $type = " " . $pre[$array_keys[$j]]["accessType"];
          }
        if ( is_array($pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed) ) {
          for ($k=0; $k < count($pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed); $k++) { 
            $services_array[] = $result_keys[$i] . $type . " - " . $pre[$array_keys[$j]]["priceZone"] . " - " . $pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed[$k]->serviceSpeed;
            // $pt->setVar("SERVICESPEED",$pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed[$k]->serviceSpeed);
          }
        } else {
            $services_array[] = $result_keys[$i] . $type . " - " . $pre[$array_keys[$j]]["priceZone"] . " - " . $pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed->serviceSpeed;
            // $pt->setVar("SERVICESPEED",$pre[$array_keys[$j]]["availableServiceSpeeds"]->serviceSpeed->serviceSpeed);
          }
      }
    }
  }
}
    
$pt->setVar("CUSTOMER_ID", $_REQUEST['customer_id']);
$pt->setVar("SP",$_REQUEST['sp']);
$pt->setVar("ORDER_ADDRESS", $address);
if ( isset($_REQUEST['service_available']) ) {
  $pt->setVar("SERVICE_AVAILABLE_" . $_REQUEST['service_available'], " checked");
}

// $new_services_array = array_unique($services_array);
$new_sa_set_keys = array_values($services_array);

for ($k=0; $k < count($new_sa_set_keys); $k++) { 
  $pt->setVar("SERVICE_VALUE",preg_replace("/\([^)]+\)/","",$new_sa_set_keys[$k]));
  $pt->setVar("SERVICESPEED",explode(" - ", $new_sa_set_keys[$k])[2]);
  $pt->parse("SERVICES_AVAILABLE","services_available","true");
}

if ( count($services_array) == 0 ) {
  $pt->setVar("ERROR_MSG","Error: No Service Available");
}

$pt->setVar("SUBADDRESSTYPE",$service_data["service_qualify_array"][$index]['siteAddress']->subAddressType);
$pt->setVar("SUBADDRESSNUMBER",$service_data["service_qualify_array"][$index]['siteAddress']->subAddressNumber);
$pt->setVar("STREETNUMBER",$service_data["service_qualify_array"][$index]['siteAddress']->streetNumber);
$pt->setVar("STREETNAME",$service_data["service_qualify_array"][$index]['siteAddress']->streetName);
$pt->setVar("STREETTYPE",$service_data["service_qualify_array"][$index]['siteAddress']->streetType);
$pt->setVar("SUBURB",$service_data["service_qualify_array"][$index]['siteAddress']->suburb);
$pt->setVar("STATE",$service_data["service_qualify_array"][$index]['siteAddress']->state);
$pt->setVar("POSTCODE",$service_data["service_qualify_array"][$index]['siteAddress']->postcode);
$pt->setVar("QUALIFICATIONID",$service_data["service_qualify_array"][$index]['qualificationID']);
$pt->setVar("NBNLOCATIONID",$service_data["service_qualify_array"][$index]['nbnLocationID']);
$pt->setVar("DSLCODESONLINE",$service_data["service_qualify_array"][$index]['dslCodesOnLine']);
$pt->setVar("ORDERADDRESS",$service_data['order_address']);
$pt->setVar("FNN",$service_data['fnn']);
$pt->setVar("ORDER_SERVICE_NUMBER",$service_data['order_service_number']);
$test = $service_data;

$pt->setVar("SERVICE_QUAL_DATA",$test);
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function check_index($data,$session,$array){

  $data = str_replace(",", "", $data);

  for ($i=0; $i < count($array); $i++) { 
    $address="";
    foreach ($array[$i]['siteAddress'] as $key => $value) {
      $address .= $value . " ";
    }
    $address = trim($address);
    if ( $address == $data ) {
      return $i;
    }
  }

  return 0;
}
