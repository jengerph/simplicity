<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/adsl_nbn/order/index.php - Qualify Order
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

include_once "postcodes.class";
include_once "../../../add/adsl_nbn/servicequalify.php";
include_once "customers.class";
include_once "services.class";
include_once "orders.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ( !isset($_REQUEST["service_id"]) ) {
  echo "Invalid Service ID.";
  exit(1);
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

if ($user->class == 'customer') {
  
  $pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/adsl_nbn/order/index.html"));
  
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
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/adsl_nbn/order/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/adsl_nbn/order/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("service_option" => "base/manage/wholesalers/service_option.html","fnn_section" => "base/manage/services/edit/adsl_nbn/order/fnn_yes.html"));

$customers = new customers();
$customers->customer_id = $service->customer_id;
$customers->load();

if ( $user->class == 'reseller' ) {
  if ( $customer->wholesaler_id != $user->access_id ) {
    $pt->setFile(array("main" => "base/accessdenied.html"));
  }
}

if ( empty($customers->type) ) {
  echo "Customer ID does not exist.";
  exit(1);
}

//get all services
$serv = new services();
$serv->customer_id = $customers->customer_id;
$serv_list = $serv->get_all();

if ( isset($_REQUEST['submit']) ) {
  if ( $_REQUEST["fnn_service"] ) {
      if ( $_REQUEST["fnn_service"] == "yes" ) {
        $pt->setFile(array("fnn_section" => "base/manage/services/edit/adsl_nbn/order/fnn_yes.html"));
      } else {
        $pt->setFile(array("fnn_section" => "base/manage/services/edit/adsl_nbn/order/fnn_no.html"));
      }
    }
}

if ( !isset($_REQUEST["fnn_service"]) || $_REQUEST["fnn_service"] == "") {
  $_REQUEST["fnn_service"] = 'yes';
  $pt->setFile(array("fnn_section" => "base/manage/services/edit/adsl_nbn/order/fnn_yes.html"));
}

if ( isset($_REQUEST["submit2"]) ) {
  if ( $_REQUEST["fnn_service"] ) {
      if ( $_REQUEST["fnn_service"] == "yes" ) {
        $pt->setFile(array("fnn_section" => "base/manage/services/edit/adsl_nbn/order/fnn_yes.html"));
        if (!trim($_REQUEST["service_number"]) || !is_numeric($_REQUEST["service_number"]) || strlen($_REQUEST["service_number"]) > 10 ) {
          $pt->setVar('ERROR_MSG','Error: Service Number invalid.');
        } else {

          $_SESSION["fnn"] = "yes";
          $_SESSION["order_service_number"] = $_REQUEST["service_number"];

          //qualify number
          $qualify_array = qualify($_REQUEST["service_number"]);

          if ( isset($qualify_array[0]["qualificationID"]) ) {
          $_SESSION["service_qualify_array"] = $qualify_array;
          // var_dump("Service Number Valid");
          //All Okay
          goto_next();
          } else {
            // var_dump("Service Number Invalid");
            $pt->setVar("ERROR_MSG","ERROR: Service Number invalid.");
          }
        }
      } else {
        $pt->setFile(array("fnn_section" => "base/manage/services/edit/adsl_nbn/order/fnn_no.html"));

        if (!trim($_REQUEST["street_name"])) {
          $pt->setVar('ERROR_MSG','Error: Street Name invalid.');
        } else if (!trim($_REQUEST["suburb"])) {
          $pt->setVar('ERROR_MSG','Error: Suburb invalid.');
        } else if (!trim($_REQUEST["address_state"])) {
          $pt->setVar('ERROR_MSG','Error: State invalid.');
        } else if (!trim($_REQUEST["postcode"])) {
          $pt->setVar('ERROR_MSG','Error: postcode invalid.');
        } else {

          $_SESSION["fnn"] = "no";
          $_SESSION["order_address_information"] = $_REQUEST["address_information"];
          $_SESSION["order_level"] = $_REQUEST["level"];
          $_SESSION["order_sub_address_type"] = $_REQUEST["sub_address_type"];
          $_SESSION["order_number"] = $_REQUEST["number"];
          $_SESSION["order_street_number"] = $_REQUEST["street_number"];
          $_SESSION["order_suffix"] = $_REQUEST["suffix"];
          $_SESSION["order_street_name"] = $_REQUEST["street_name"];
          $_SESSION["order_add_type"] = $_REQUEST["add_type"];
          $_SESSION["order_suffix_type"] = $_REQUEST["suffix_type"];
          $_SESSION["order_suburb"] = $_REQUEST["suburb"];
          $_SESSION["order_address_state"] = $_REQUEST["address_state"];
          $_SESSION["order_postcode"] = $_REQUEST["postcode"];

          $address = array();
          $address['streetNumber'] = $_REQUEST["street_number"];
          $address['streetNumberSuffix'] = $_REQUEST["suffix"];
          $address['streetName'] = $_REQUEST["street_name"];
          $address['streetType'] = $_REQUEST["add_type"];
          $address['streetTypeSufix'] = $_REQUEST["suffix_type"];
          $address['suburb'] = $_REQUEST["suburb"];
          $address['state'] = $_REQUEST["address_state"];
          $address['postcode'] = $_REQUEST["postcode"];


          //qualify number
          $qualify_array = servicequal_address1($address);

          if ( is_array($qualify_array) ) {
            // var_dump("Service Number Valid");
            $_SESSION["service_qualify_array"] = servicequal_address1($address);
            //All Okay
            goto_next();
          } else {
            $pt->setVar("ERROR_MSG","ERROR: Address Invalid.");
          }
          
        }

      }
    }
}

    if ( isset($_REQUEST["address_information"]) ) {
      $pt->setVar('ADDRESS_INFORMATION', $_REQUEST["address_information"]);
    }
    if ( isset($_REQUEST["level"]) ) {
      $pt->setVar('LEVEL', $_REQUEST["level"]);
    }
    if ( isset($_REQUEST["sub_address_type"]) ) {
      $pt->setVar('SUB_ADDRESS_TYPE', $_REQUEST["sub_address_type"]);
    }
    if ( isset($_REQUEST["number"]) ) {
      $pt->setVar('NUMBER', $_REQUEST["number"]);
    }
    if ( isset($_REQUEST["street_number"]) ) {
      $pt->setVar('STREET_NUMBER', $_REQUEST["street_number"]);
    }
    if ( isset($_REQUEST["suffix"]) ) {
      $pt->setVar('SUFFIX', $_REQUEST["suffix"]);
    }
    if ( isset($_REQUEST["street_name"]) ) {
      $pt->setVar('STREET_NAME', $_REQUEST["street_name"]);
    }
    if ( isset($_REQUEST["add_type"]) ) {
      $pt->setVar('TYPE_' . str_replace("-", "_", str_replace(" ", "_", $_REQUEST["add_type"])), "selected");
    }
    if ( isset($_REQUEST["suffix_type"]) ) {
      $pt->setVar('SUFFIX_TYPE_' . str_replace(" ", "_", $_REQUEST["suffix_type"]), "selected");
    }
    if ( isset($_REQUEST["suburb"]) ) {
      $pt->setVar('SUBURB', $_REQUEST["suburb"]);
    }
    if ( isset($_REQUEST["address_state"]) ) {
      $pt->setVar('ADDRESS_STATE_' . $_REQUEST["address_state"], "selected");
    }
    if ( isset($_REQUEST["suburb"]) && isset($_REQUEST["address_state"]) ) {
      $postcodes = new postcodes();
      $postcodes->locality = strtoupper($_REQUEST["suburb"]);
      $postcodes->state = strtoupper($_REQUEST["address_state"]);
      $postcodes_array = $postcodes->get_postcodes();
      $postcodes_list = $postcodes->postcode_list("postcode",$postcodes_array);
      $pt->setVar('POSTCODE_OPTION', $postcodes_list);
      if( count($postcodes_array) == 1 ){
        $pt->setVar('POSTCODE_'.$postcodes_array[0]['pcode'].'_SELECT', "selected");
      } else if ( count($postcodes_array) == 0 ) {
        $pt->setVar('POSTCODE_OPTION', $_REQUEST["suburb"] . ' is not found in ' . $_REQUEST["address_state"]);  
      }
    } else {
      $pt->setVar('POSTCODE_OPTION', 'Select Suburb & State');
    }

$pt->setVar('FNN_SERVICE_' . strtoupper($_REQUEST["fnn_service"]), ' checked');
$pt->parse('FNN_SECTION','fnn_section','true');
$pt->setVar("PAGE_TITLE", "Qualify a Service");
$pt->setVar("SERVICE_ID", $_REQUEST['service_id']);
		
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

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/adsl_nbn/order/qualify/index.php?service_id=" . $_REQUEST["service_id"];

    header("Location: $url");
    exit();
}

function load_value ( $value ) {

  if ( isset($value) && $value !="" ) {
  
    return $value;

  }

}