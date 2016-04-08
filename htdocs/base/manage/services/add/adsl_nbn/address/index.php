<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/adsl_nbn/address/index.php - Qualify Results
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
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/adsl_nbn/address/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/adsl_nbn/address/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/adsl_nbn/address/index.html"));
  
}

$pt->setFile(array("address_option" => "base/manage/services/add/adsl_nbn/address/address_option.html"));

if ( !isset($_REQUEST['customer_id']) ) {
  echo "Customer ID invalid";
  exit(1);
}

$address = "";

if ( !isset($_REQUEST['sp']) || empty($_REQUEST['sp']) ) {
  echo "URL invalid";
  exit();
}

$session_pointer = $_REQUEST['customer_id'] . "_" . $_REQUEST['sp'];

$service_temp = new service_temp();
$service_temp->data_key = $session_pointer;
$service_temp->load();

$service_data = unserialize($service_temp->data);

$possible_addresses = check_addresses($service_data["service_qualify_array"]);

if ( isset($_REQUEST["submit"]) ) {

  if ( !isset($_REQUEST['address_available']) || $_REQUEST['address_available'] == "" ) {
    $pt->setVar('ERROR_MSG','Error: Select an address.');
  } else{

    $service_data["order_address"] = $_REQUEST["address_available"];

    $service_temp->data = serialize($service_data);
    $service_temp->save();

  // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/adsl_nbn/qualify/?customer_id=" . $_REQUEST["customer_id"] . "&sp=" . $_REQUEST['sp'];

    header("Location: $url");
    exit();
  }
}

$new_address_array = array();

for ($a=0; $a < count($possible_addresses); $a++) {
  $new_address_array[$a] = "";
  if ( isset($possible_addresses[$a]->subAddressType) ) {
    $new_address_array[$a] .= $possible_addresses[$a]->subAddressType. " ";
  }
  if ( isset($possible_addresses[$a]->subAddressNumber) ) {
    $new_address_array[$a] .= $possible_addresses[$a]->subAddressNumber. " ";
  }
  if ( isset($possible_addresses[$a]->streetNumber) ) {
    $new_address_array[$a] .= $possible_addresses[$a]->streetNumber. " ";
  }
  if ( isset($possible_addresses[$a]->streetNumberSuffix) ) {
    $new_address_array[$a] .= $possible_addresses[$a]->streetNumberSuffix. " ";
  }
  if ( isset($possible_addresses[$a]->streetName) ) {
    $new_address_array[$a] .= $possible_addresses[$a]->streetName. " ";
  }
  if ( isset($possible_addresses[$a]->streetType) ) {
    $new_address_array[$a] .= $possible_addresses[$a]->streetType. ", ";
  }
  if ( isset($possible_addresses[$a]->suburb) ) {
    $new_address_array[$a] .= $possible_addresses[$a]->suburb. ", ";
  }
  if ( isset($possible_addresses[$a]->state) ) {
    $new_address_array[$a] .= $possible_addresses[$a]->state. " ";
  }
  if ( isset($possible_addresses[$a]->postcode) ) {
    $new_address_array[$a] .= $possible_addresses[$a]->postcode;
  }
}

if ( count($possible_addresses) == 1 ) {
  $service_data["order_address"] = $new_address_array[0];
  $service_temp->data = serialize($service_data);
  $service_temp->save();

  // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/adsl_nbn/qualify/?customer_id=" . $_REQUEST["customer_id"] . "&sp=" . $_REQUEST['sp'];

    header("Location: $url");
    exit();
}

$inputted_address = "";
if ( isset($service_data['fnn']) && $service_data['fnn'] == 'no' ) {
  if ( isset($service_data["order_address_information"]) ) {
    $inputted_address .= $service_data["order_address_information"] . " ";
  }
  if ( isset($service_data["order_level"]) ) {
    $inputted_address .= $service_data["order_level"] . " ";
  }
  if ( isset($service_data["order_sub_address_type"]) ) {
    $inputted_address .= $service_data["order_sub_address_type"] . " ";
  }
  if ( isset($service_data["order_number"]) ) {
    $inputted_address .= $service_data["order_number"] . " ";
  }
  if ( isset($service_data["order_street_number"]) ) {
    $inputted_address .= $service_data["order_street_number"] . " ";
  }
  if ( isset($service_data["order_suffix"]) ) {
    $inputted_address .= $service_data["order_suffix"] . " ";
  }
  if ( isset($service_data["order_street_name"]) ) {
    $inputted_address .= $service_data["order_street_name"] . " ";
  }
  if ( isset($service_data["order_add_type"]) ) {
    $inputted_address .= $service_data["order_add_type"] . ", ";
  }
  if ( isset($service_data["order_suffix_type"]) ) {
    $inputted_address .= $service_data["order_suffix_type"] . " ";
  }
  if ( isset($service_data["order_suburb"]) ) {
    $inputted_address .= $service_data["order_suburb"] . " ";
  }
  if ( isset($service_data["order_address_state"]) ) {
    $inputted_address .= $service_data["order_address_state"] . " ";
  }
  if ( isset($service_data["order_postcode"]) ) {
    $inputted_address .= $service_data["order_postcode"] . " ";
  }
}
$inputted_address = trim($inputted_address);
$pt->setVar("CUSTOMER_ID", $_REQUEST['customer_id']);
$pt->setVar("SP",$_REQUEST['sp']);

$address_unique = array_unique($new_address_array);
$new_sa_set_keys = array_values($address_unique);

$similar = 0;
$similar_array = array();
for ($k=0; $k < count($new_sa_set_keys); $k++) {
  $similar = similar_text(strtoupper($inputted_address), $new_address_array[$k]);
  $similar_array[$k] = $similar;
  $pt->setVar("ADDRESS_VALUE",$new_sa_set_keys[$k]);
  $pt->setVar("AD_COUNT",$k);
  $pt->parse("ADDRESSES_AVAILABLE","address_option","true");
}

$max = array_keys($similar_array,max($similar_array));

$pt->setVar("ADDRESS_AVAILABLE".$max[0]," checked");

if ( count($new_address_array) == 0 ) {
  $pt->setVar("ERROR_MSG","Error: No Address Available");
}

// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function check_addresses($data){

  $address = array();
  for ($a=0; $a < count($data); $a++) { 
    if ( isset($data[$a]['displayAddress']) ) {
      $address[] = $data[$a]['siteAddress'];
    }
  }
  return $address;
}
