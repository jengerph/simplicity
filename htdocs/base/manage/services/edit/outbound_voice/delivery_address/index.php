<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/outbound_voice/delivery_address/index.php - Edit Outbound Voice
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
include_once "customers.class";
include_once "services.class";
include_once "postcodes.class";
include_once "servicequalify.php";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/outbound_voice/delivery_address/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/outbound_voice/delivery_address/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/outbound_voice/delivery_address/index.html"));
  
}

$pt->setFile(array("delivery_address_form" => "base/manage/services/edit/adsl_nbn/order/fnn_no.html"));

if ( !isset($_REQUEST["service_id"]) ) {
  echo "Invalid Service ID.";
  exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

if ( isset($_REQUEST["submit"]) ) {
  if (!trim($_REQUEST["street_name"])) {
    $pt->setVar('ERROR_MSG','Error: Street Name invalid.');
  } else if (!trim($_REQUEST["suburb"])) {
    $pt->setVar('ERROR_MSG','Error: Suburb invalid.');
  } else if (!trim($_REQUEST["address_state"])) {
    $pt->setVar('ERROR_MSG','Error: State invalid.');
  } else if (!isset($_REQUEST["postcode"]) || empty($_REQUEST["postcode"])) {
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
      $address_verify = servicequal_address1($address);

      if ( isset($address_verify[0]["nbnLocationID"]) ) {
        $_SESSION["nbnLocationID"] = $address_verify[0]["nbnLocationID"];
      }

      if ( isset($address_verify[0]["telstraLocationID"]) ) {
        $_SESSION["telstraLocationID"] = $address_verify[0]["telstraLocationID"];
      }
      
      if ( !isset($address_verify[0]["siteAddress"]) ) {
        $pt->setVar("ERROR_MSG","ERROR: Address is invalid.");
      } else if ( isset($address_verify[0]["siteAddress"]) ) {
        $delivery_address = "";
        
        if ( isset($address["streetNumber"]) && !empty($address["streetNumber"]) ) {
          $delivery_address .= $address["streetNumber"];
        }
        if ( isset($address["streetNumberSuffix"]) && !empty($address["streetNumberSuffix"]) ) {
          $delivery_address .= " " . $address["streetNumberSuffix"];
        }
        if ( isset($address["streetName"]) && !empty($address["streetName"]) ) {
          $delivery_address .= " " . $address["streetName"];
        }
        if ( isset($address["streetType"]) && !empty($address["streetType"]) ) {
          $delivery_address .= " " . $address["streetType"];
        }
        if ( isset($address["streetTypeSufix"]) && !empty($address["streetTypeSufix"]) ) {
          $delivery_address .= " " . $address["streetTypeSufix"];
        }
        if ( isset($address["suburb"]) && !empty($address["suburb"]) ) {
          $delivery_address .= " " . $address["suburb"];
        }
        if ( isset($address["state"]) && !empty($address["state"]) ) {
          $delivery_address .= " " . $address["state"];
        }
        if ( isset($address["postcode"]) && !empty($address["postcode"]) ) {
          $delivery_address .= " " . $address["postcode"];
        }
        
        $_SESSION["outbound_voice"]["delivery_address"] = trim($delivery_address);
        $_SESSION["outbound_voice"]["delivery_address"] = strtoupper($delivery_address);

        // Done, goto list
        $url = "";
            
        if ( isset($_SERVER["HTTPS"]) ) {
            
          $url = "https://";
              
        } else {
            
          $url = "http://";
        }

          $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/outbound_voice/existing_port/?service_id=" . $service->service_id;

        header("Location: $url");
        exit();

      }
      exit();
    } else {
      $pt->setVar("ERROR_MSG","ERROR: Address Invalid.");
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

$pt->setVar("SERVICE_ID",$_REQUEST["service_id"]);
$pt->parse("DELIVERY_ADDRESS_FORM","delivery_address_form","true");

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