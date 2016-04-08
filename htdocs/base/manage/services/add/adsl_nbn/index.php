<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/adsl_nbn/index.php - Qualify Order
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
include_once "postcodes.class"; 
include_once "config.class";
include_once "customers.class";
include_once "servicequalify.php";
include_once "services.class";
include_once "orders.class";
include_once "../insert_to_session.php";
include_once "service_temp.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/adsl_nbn/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/adsl_nbn/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/adsl_nbn/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("service_option" => "base/manage/wholesalers/service_option.html","fnn_section" => "base/manage/services/add/adsl_nbn/fnn_yes.html"));

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
  exit(1);
}

$customers = new customers();
$customers->customer_id = $_REQUEST["customer_id"];
$customers->load();

if ( $user->class == 'customer' ) {
  if ( $customers->customer_id != $user->access_id ) {
    $pt->setFile(array("main" => "base/accessdenied.html"));
  }
} else if ( $user->class == 'reseller' ) {
  if ( $customers->wholesaler_id != $user->access_id ) {
    $pt->setFile(array("main" => "base/accessdenied.html"));
  }
}

if ( empty($customers->type) ) {
  echo "Customer ID does not exist.";
  exit(1);
}

//get all services
$services = new services();
$services->customer_id = $customers->customer_id;
$services_list = $services->get_all();

if ( isset($_REQUEST['submit']) ) {
  if ( $_REQUEST["fnn_service"] ) {
      if ( $_REQUEST["fnn_service"] == "yes" ) {
        $pt->setFile(array("fnn_section" => "base/manage/services/add/adsl_nbn/fnn_yes.html"));
      } else {
        $pt->setFile(array("fnn_section" => "base/manage/services/add/adsl_nbn/fnn_no.html"));
      }
    }
}

if ( !isset($_REQUEST["fnn_service"]) || $_REQUEST["fnn_service"] == "") {
  $_REQUEST["fnn_service"] = 'yes';
  $pt->setFile(array("fnn_section" => "base/manage/services/add/adsl_nbn/fnn_yes.html"));
}

$address = "";

if ( isset($_REQUEST["submit2"]) ) {

  $session_pointer = md5(microtime());

  $temp = array();
  $key = $customers->customer_id."_".$session_pointer;

  if ( $_REQUEST["fnn_service"] ) {
      if ( $_REQUEST["fnn_service"] == "yes" ) {
        $pt->setFile(array("fnn_section" => "base/manage/services/add/adsl_nbn/fnn_yes.html"));
        if (!trim($_REQUEST["service_number"]) || !is_numeric($_REQUEST["service_number"]) || strlen($_REQUEST["service_number"]) > 10 ) {
          $pt->setVar('ERROR_MSG','Error: Service Number invalid.');
        } else {

          $qualify_array = qualify($_REQUEST["service_number"]);

          $qualify_json = json_encode($qualify_array);
          
          if ( isset($qualify_array[0]["qualificationID"]) ) {

            $temp[$key]["fnn"] = "yes";
            $temp[$key]["order_service_number"] = $_REQUEST["service_number"];
            $temp[$key]['service_qualify_array'] = $qualify_array;

            $service_temp = new service_temp();
            $service_temp->data_key = $key;
            $service_temp->data = serialize($temp[$key]);
            $service_temp->create();

          //All Okay
          goto_next($customers->customer_id,$session_pointer);
          } else {
            // var_dump("Service Number Invalid");
            $pt->setVar("ERROR_MSG","ERROR: Service Number invalid.");
          }
        }
      } else {
        $address = $_REQUEST["autocomplete"];
        $street_number = $_REQUEST["street_number"];
        $street_name = "";
        $add_type = "";
        $suburb = $_REQUEST["locality"];
        $state = $_REQUEST["administrative_area_level_1"];
        $postcode = $_REQUEST["postal_code"];
        $country = $_REQUEST["country"];

        $address_route = explode(" ", $_REQUEST["route"]);
        $add_type = $address_route[count($address_route)-1];
        for ($a=0; $a < count($address_route)-1; $a++) { 
          $street_name .= $address_route[$a] . " ";
        }
        $street_name = trim($street_name);
        
        $pt->setFile(array("fnn_section" => "base/manage/services/add/adsl_nbn/fnn_no.html"));
        if (empty($street_name)) {
          $pt->setVar('ERROR_MSG','Error: Street Name invalid.');
        } else if (empty($suburb)) {
          $pt->setVar('ERROR_MSG','Error: Suburb invalid.');
        } else if (empty($state)) {
          $pt->setVar('ERROR_MSG','Error: State invalid.');
        } else if (empty($postcode) || $postcode=='0') {
          $pt->setVar('ERROR_MSG','Error: Postcode invalid.');
        } else if (empty($country)||strtoupper($country)!="AUSTRALIA") {
          $pt->setVar('ERROR_MSG','Error: Country invalid.');
        } else {

          $temp[$key]["fnn"] = "no";
          // $temp[$key]["order_address_information"] = $_REQUEST["address_information"];
          // $temp[$key]["order_level"] = $_REQUEST["level"];
          // $temp[$key]["order_sub_address_type"] = $_REQUEST["sub_address_type"];
          // $temp[$key]["order_number"] = $_REQUEST["number"];
          $temp[$key]["order_street_number"] = $street_number;
          $temp[$key]["order_street_name"] = $street_name;
          $temp[$key]["order_add_type"] = $add_type;
          $temp[$key]["order_suburb"] = $suburb;
          $temp[$key]["order_address_state"] = $state;
          $temp[$key]["order_postcode"] = $postcode;

          $address = array();
          $address['streetNumber'] = $street_number;
          $address['streetName'] = $street_name;
          $address['streetType'] = $add_type;
          $address['suburb'] = $suburb;
          $address['state'] = $state;
          $address['postcode'] = $postcode;


          //qualify number
          $qualify_array = servicequal_address1($address);


          if ( is_array($qualify_array) ) {
            // var_dump("Service Number Valid");
            $temp[$key]["service_qualify_array"] = $qualify_array;
            //$temp[$key]["service_qualify_array"] = servicequal_address1($address);

            $service_temp = new service_temp();
            $service_temp->data_key = $key;
            $service_temp->data = serialize($temp[$key]);
            $service_temp->create();

            //All Okay
            goto_next($customers->customer_id,$session_pointer);
          } else {
            $pt->setVar("ERROR_MSG","ERROR: Address Invalid.");
          }

        }

      }
    }
}

  if ( isset($_REQUEST["street_number"]) ) {
    $pt->setVar('STREET_NUMBER', $_REQUEST["street_number"]);
  }
  if ( isset($_REQUEST["route"]) ) {
    $pt->setVar('ROUTE', $_REQUEST["route"]);
  }
  if ( isset($_REQUEST["locality"]) ) {
    $pt->setVar('LOCALITY', $_REQUEST["locality"]);
  }
  if ( isset($_REQUEST["administrative_area_level_1"]) ) {
    $pt->setVar('ADMINISTRATIVE_AREA', $_REQUEST["administrative_area_level_1"]);
  }
  if ( isset($_REQUEST["postal_code"]) ) {
    $pt->setVar('POSTAL_CODE', $_REQUEST["postal_code"]);
  }
  if ( isset($_REQUEST["country"]) ) {
    $pt->setVar('COUNTRY', $_REQUEST["country"]);
  }
  if ( isset($_REQUEST["autocomplete"]) ) {
    $pt->setVar('AUTOCOMPLETE', $_REQUEST["autocomplete"]);
  }

  $pt->setVar('FNN_SERVICE_' . strtoupper($_REQUEST["fnn_service"]), ' checked');
  $pt->setVar('CUSTOMER_ID', $_REQUEST["customer_id"]);
  $pt->parse('FNN_SECTION','fnn_section','true');
  if (isset($_REQUEST["service_number"])){
    $pt->setVar('SERVICE_NUMBER', $_REQUEST["service_number"]);
  }

$pt->setVar("ADDRESS",$address);

$pt->setVar("PAGE_TITLE", "Qualify a Service");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function goto_next($customer_id,$session_pointer){
  // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/adsl_nbn/address/index.php?customer_id=" . $customer_id . "&sp=" . $session_pointer;
    // $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/adsl_nbn/qualify/index.php?customer_id=" . $_REQUEST["customer_id"];

    header("Location: $url");
    exit();
}

// function add_type_map($data) {  
//   switch ($data) {
//     case 'value':
//       # code...
//       break;
    
//     default:
//       # code...
//       break;
//   }
// }
