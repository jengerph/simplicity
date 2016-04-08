<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/outbound_voice/delivery_address/index.php - Outbound Voice
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
include_once "servicequalify.php";
include_once "service_temp.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/outbound_voice/delivery_address/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/outbound_voice/delivery_address/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/outbound_voice/delivery_address/index.html"));
  
}

$pt->setFile(array("delivery_address_form" => "base/manage/services/edit/adsl_nbn/order/fnn_no.html"));

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
  exit();
}

if ( !isset($_REQUEST["outbound_kind"]) ) {
  echo "No Outbound Voice Kind selected.";
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

$sub_premises = "";
$autocomplete = "";
$street_number = "";
$route = "";
$locality = "";
$administrative_area_level_1 = "";
$postal_code = "";
$country = "";

if ( isset($_REQUEST["submit"]) ) {
  $sub_premises = $_REQUEST["sub_premises"];
  $autocomplete = $_REQUEST["autocomplete"];
  $street_number = $_REQUEST["street_number"];
  $route = $_REQUEST["route"];
  $locality = $_REQUEST["locality"];
  $administrative_area_level_1 = $_REQUEST["administrative_area_level_1"];
  $postal_code = $_REQUEST["postal_code"];
  $country = $_REQUEST["country"];

  $street_number = $_REQUEST["street_number"];
  $street_name = "";
  $add_type = "";
  $suburb = $_REQUEST["locality"];
  $state = $_REQUEST["administrative_area_level_1"];
  $postcode = $_REQUEST["postal_code"];

  $route = explode(" ", $_REQUEST["route"]);
  $add_type = $route[count($route)-1];
  for ($a=0; $a < count($route)-1; $a++) { 
    $street_name .= $route[$a] . " ";
  }
  $street_name = trim($street_name);

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

      //if ( empty($sub_premises) ) {
      //  $pt->setVar("ERROR_MSG","Error: Please provide Sub Premises");
      //} else 
      if ( empty($autocomplete) ) {
        $pt->setVar("ERROR_MSG","Error: Please provide Street Address");
      } else if ( empty($street_number) && empty($street_name) ) {
        $pt->setVar("ERROR_MSG","Error: Please provide Street Number");
      } else if ( empty($locality) ) {
        $pt->setVar("ERROR_MSG","Error: Please provide City");
      } else if ( empty($administrative_area_level_1) ) {
        $pt->setVar("ERROR_MSG","Error: Please provide State");
      } else if ( empty($postal_code) ) {
        $pt->setVar("ERROR_MSG","Error: Please provide Postcode");
      } else if ( empty($country) || strtoupper($country) != "AUSTRALIA" ) {
        $pt->setVar("ERROR_MSG","Error: Please provide a valid Country");
      } else {

        $session_pointer0 = md5(microtime());
        $service_data = array();
        $session_pointer = $customer->customer_id . "_" . $session_pointer0;

        $service_data["order_street_number"] = $street_number;
        $service_data["order_street_name"] = $street_name;
        $service_data["order_add_type"] = $add_type;
        $service_data["order_suburb"] = $suburb;
        $service_data["order_address_state"] = $state;
        $service_data["order_postcode"] = $postcode;

        $delivery_address = "";
            if ( isset($sub_premises) && !empty($sub_premises) ) {
              $delivery_address .= " " . $sub_premises . ",";
            }
            if ( isset($street_number) && !empty($street_number) ) {
              $delivery_address .= " " . $street_number;
            }
            if ( isset($street_name) && !empty($street_name) ) {
              $delivery_address .= " " . $street_name;
            }
            if ( isset($add_type) && !empty($add_type) ) {
              $delivery_address .= " " . $add_type;
            }
            if ( isset($locality) && !empty($locality) ) {
              $delivery_address .= ", " . $locality;
            }
            if ( isset($administrative_area_level_1) && !empty($administrative_area_level_1) ) {
              $delivery_address .= " " . $administrative_area_level_1;
            }
            if ( isset($postal_code) && !empty($postal_code) ) {
              $delivery_address .= ", " . $postal_code;
            }

            // $_SESSION["qualify_array"] = $qualify_array;
            $index = check_index($sub_premises,$qualify_array);

            // $_SESSION["order_service_available_index"] = $index;


            if ( isset($qualify_array[$index]["nbnLocationID"]) ) {
              $service_data["outbound_voice"]["nbnLocationID"] = $qualify_array[$index]["nbnLocationID"];
            }

            if ( isset($qualify_array[$index]["telstraLocationID"]) ) {
              $service_data["outbound_voice"]["telstraLocationID"] = $qualify_array[$index]["telstraLocationID"];
            }

            $delivery_address = trim($delivery_address);
            $service_data["order_address"] = strtoupper($delivery_address);
            
            $service_temp = new service_temp();
            $service_temp->data_key = $session_pointer;
            $service_temp->data = serialize($service_data);
            $service_temp->create();

            // Done, goto list
            $url = "";
                
            if ( isset($_SERVER["HTTPS"]) ) {
                
              $url = "https://";
                  
            } else {
                
              $url = "http://";
            }

              $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/outbound_voice/existing_port/?customer_id=" . $customer->customer_id . "&outbound_kind=" . $_REQUEST["outbound_kind"] . "&sp=" . $session_pointer0;

            header("Location: $url");
            exit();
      }
  }
}


$pt->setVar("SUB_PREMISES",$sub_premises);
$pt->setVar("AUTOCOMPLETE",$autocomplete);
$pt->setVar("STREET_NUMBER",$street_number);
$pt->setVar("ROUTE",$route);
$pt->setVar("LOCALITY",$locality);
$pt->setVar("ADMINISTRATIVE_AREA",$administrative_area_level_1);
$pt->setVar("POSTAL_CODE",$postal_code);
$pt->setVar("COUNTRY",$country);
$pt->setVar("OUTBOUND_KIND",$_REQUEST["outbound_kind"]);
$pt->setVar("CUSTOMER_ID",$_REQUEST["customer_id"]);
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

function check_index($sub_premises,$array){

  $index = array();

  for ($a=0; $a < count($array); $a++) { 
    if ( !empty($array[$a]["siteAddress"]) ) {
      $array_data = $array[$a]["siteAddress"]->subAddressType." ".$array[$a]["siteAddress"]->subAddressNumber;
      similar_text($sub_premises, $array_data, $percent);
      $index[$a] = $percent;
    }
  }

  $result = array_keys($index, max($index));

  return $result[0];
}
