<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/availability/index.php - Display Service Availability
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../setup.inc";

// include "doauth.inc";
include_once("servicequalify.php");

$pt->setVar("PAGE_TITLE", "Service Availability");

// Assign the templates to use
$pt->setFile(array("outside"=>"availability/outside.html",
                    "main" => "availability/index.html",
                    "table_row" => "availability/table_row.html",
                    "specific_address_table" => "availability/specific_address_table.html",
                    "specific_address_div" => "availability/specific_address_div.html",
                    "service_speed_li" => "availability/service_speed_li.html",
                    "available_service_speeds" => "availability/available_service_speeds.html",
                    "maximum_bandwidth" => "availability/maximum_bandwidth.html",
                    "location_id_header" => "availability/location_id_header.html",
                    "location_id_row" => "availability/location_id_row.html",
                    "location_id_div" => "availability/location_id_div.html"));

//declare variables
$autocomplete = "";
$address_information = "";
$level = "";
$sub_address_type = "";
$number = "";
$street_number = "";
$suffix = "";
$route = "";
$street_type = "";
$suffix_type = "";
$locality = "";
$administrative_area_level_1 = "";
$postal_code = "";
$true_address = "";

if ( isset($_REQUEST["specific_btn"]) ) {
  $specific_add = explode("_", $_REQUEST["specific_add"]);

  for ($a=0; $a < count($specific_add); $a++) { 
    if ( isset($specific_add[$a]) && !empty($specific_add[$a]) ) {
      $temp = explode("=", $specific_add[$a]);
      ${$temp[0]} = $temp[1];
      $address[$temp[0]] = $temp[1];
      $true_address .= $temp[1] . " ";
    }
  }

  $true_address = trim($true_address);
  $true_address = preg_replace('/\s+/', ' ',$true_address);
  $autocomplete = $true_address;

$level = "";
$sub_address_type = "";
$number = $levelNumber;
$street_number = $streetNumber;
$suffix = $streetNumberSuffix;
$route = $streetName;
$street_type = $streetType;
$locality = $suburb;
$administrative_area_level_1 = $state;
$postal_code = $postcode;

$result = servicequal_address1($address);

// $result = $_SESSION["result"];
// $_SESSION["result"] = $result;

// print_r($_SESSION["result"]);

$service_count = 0;

  for ($b=0; $b < count($result[0]["accessQualificationList"]); $b++) { 
    if ( $result[0]["accessQualificationList"][$b]["qualificationResult"] != "FAIL" ) {
      $pt->setVar("ACCESS_METHOD",$result[0]["accessQualificationList"][$b]["accessMethod"]);
      $pt->setVar("ACCESS_TYPE",$result[0]["accessQualificationList"][$b]["accessType"]);
      $pt->setVar("PRICE_ZONE",$result[0]["accessQualificationList"][$b]["priceZone"]);
      $pt->setVar("MDB",$result[0]["accessQualificationList"][$b]["maximumDownBandwidth"]->value . $result[0]["accessQualificationList"][$b]["maximumDownBandwidth"]->quantifier);
      $pt->setVar("MUB",$result[0]["accessQualificationList"][$b]["maximumUpBandwidth"]->value . $result[0]["accessQualificationList"][$b]["maximumUpBandwidth"]->quantifier);
      if ( $result[0]["accessQualificationList"][$b]["maximumDownBandwidth"]->value > 0 || $result[0]["accessQualificationList"][$b]["maximumUpBandwidth"]->value > 0 ) {
        $pt->parse("MAXIMUM_BANDWIDTH","maximum_bandwidth","true");
      }
      for ($c=0; $c < count($result[0]["accessQualificationList"][$b]["availableServiceSpeeds"]->serviceSpeed); $c++) { 
        if ( $result[0]["accessQualificationList"][$b]["availableServiceSpeeds"]->serviceSpeed[$c]->status == "PASS" ) {
          $pt->setVar("SERVICE_SPEED",$result[0]["accessQualificationList"][$b]["availableServiceSpeeds"]->serviceSpeed[$c]->serviceSpeed);
          $pt->parse("SERVICE_SPEED_LI","service_speed_li","true");
        }
      }
        $pt->parse("AVAILABLE_SERVICE_SPEEDS","available_service_speeds","true");

        if ( $result[0]["accessQualificationList"][$b]["accessMethod"] == "NBN" ) {
          $pt->parse("LOCATION_ID_DIV","location_id_div","true");
        }
    } else {
      //count fail and array length
      $service_count++;
    }
  }
  if ( $service_count == count($result[0]["accessQualificationList"]) ) {
    $pt->setVar("AVAILABLE_SERVICE_SPEEDS","*There are no available services for this address.*");
  }

  $pt->setVar("AUTOCOMPLETE",$true_address);
  $pt->setVar("LOCATION_ID",$result[0]["siteDetails"]->nbnLocationID);
  $pt->parse("SPECIFIC_ADDRESS_TABLE","specific_address_div","true");

} else if ( isset($_REQUEST["submit"]) ) {

$autocomplete = $_REQUEST["autocomplete"];
$address_information = $_REQUEST["address_information"];
$level = $_REQUEST["level"];
$sub_address_type = $_REQUEST["sub_address_type"];
$number = $_REQUEST["number"];
$street_number = $_REQUEST["street_number"];
$suffix = $_REQUEST["suffix"];
$route = $_REQUEST["route"];
$street_type = $_REQUEST["street_type"];
$suffix_type = $_REQUEST["suffix_type"];
$locality = $_REQUEST["locality"];
$administrative_area_level_1 = $_REQUEST["administrative_area_level_1"];
$postal_code = $_REQUEST["postal_code"];

$address["subAddressNumber"] = (isset($number) ? $number : "");
$address["subAddressType"] = (isset($sub_address_type) ? $sub_address_type : "");
$address["streetNumber"] = (isset($street_number) ? $street_number : "");
$address["streetNumberSuffix"] = (isset($suffix) ? $suffix : "");
$address["streetName"] = (isset($route) ? $route : "");
$address["streetType"] = (isset($street_type) ? $street_type : "");
$address["streetTypeSufix"] = (isset($suffix_type) ? $suffix_type : "");
$address["suburb"] = (isset($locality) ? $locality : "");
$address["state"] = (isset($administrative_area_level_1) ? $administrative_area_level_1 : "");
$address["postcode"] = (isset($postal_code) ? $postal_code : "");

if ( $route == "" || $locality == "" || $administrative_area_level_1 == "" || $postal_code == "" || $postal_code == "0" ) {

  $pt->setVar("ERROR_MSG","Error: Please fill the fields that are marked with an asterisk (*)");

} else {

  $result = servicequal_address1($address);

  for ($a=0; $a < count($result); $a++) { 
    $loc_address = "";
    $specific_add = "";

      $pt->clearVar("ADDRESS");

      foreach ($result[$a]["siteAddress"] as $key => $value) {
        $loc_address .= $result[$a]["siteAddress"]->{$key} . " ";
        $specific_add .= $key . "=" . $result[$a]["siteAddress"]->{$key} . "_";
      }
      $pt->setVar("ADDRESS",$loc_address);
      $pt->setVar("SPECIFIC_ADD",$specific_add);
      $pt->setVar("LOCATION_ID",$result[$a]["siteDetails"]->nbnLocationID);
      $pt->parse("ROWS","table_row","true");
  }

  $pt->parse("SPECIFIC_ADDRESS_TABLE","specific_address_table","true");

}

} else if ( isset($_REQUEST["street_name"]) && isset($_REQUEST["suburb"]) && !isset($_REQUEST["submit"]) ) {
  $autocomplete = $_REQUEST["autocomplete"];
  $address_information = $_REQUEST["address_information"];
  $level = $_REQUEST["level"];
  $sub_address_type = $_REQUEST["sub_address_type"];
  $number = $_REQUEST["number"];
  $street_number = $_REQUEST["street_number"];
  $suffix = $_REQUEST["street_number_suffix"];
  $route = $_REQUEST["street_name"];
  $street_type = $_REQUEST["street_type"];
  $suffix_type = $_REQUEST["suffix_type"];
  $locality = $_REQUEST["suburb"];
  $administrative_area_level_1 = $_REQUEST["state"];
  $postal_code = $_REQUEST["postal_code"];

  $address["subAddressNumber"] = (isset($number) ? $number : "");
  $address["subAddressType"] = (isset($sub_address_type) ? $sub_address_type : "");
  $address["streetNumber"] = (isset($street_number) ? $street_number : "");
  $address["streetNumberSuffix"] = (isset($suffix) ? $suffix : "");
  $address["streetName"] = (isset($route) ? $route : "");
  $address["streetType"] = (isset($street_type) ? $street_type : "");
  $address["streetTypeSufix"] = (isset($suffix_type) ? $suffix_type : "");
  $address["suburb"] = (isset($locality) ? $locality : "");
  $address["state"] = (isset($administrative_area_level_1) ? $administrative_area_level_1 : "");
  $address["postcode"] = (isset($postal_code) ? $postal_code : "");

  if ( $route == "" || $locality == "" || $administrative_area_level_1 == "" || $postal_code == "" ) {
    echo "Unable to retrieve. Please supply values for street name, suburb, state, and postal code.";
  } else {

    $result = servicequal_address1($address);
    header('Content-Type: application/json');
    // echo json_encode($result);
    print_r(json_encode($result,JSON_PRETTY_PRINT));
  }
  exit();
}

$pt->setVar("AUTOCOMPLETE",$autocomplete);
$pt->setVar("ADDRESS_INFORMATION",$address_information);
$pt->setVar("LEVEL",$level);
$pt->setVar("SUB_ADDRESS_TYPE",$sub_address_type);
$pt->setVar("NUMBER",$number);
$pt->setVar("STREET_NUMBER",$street_number);
$pt->setVar("SUFFIX",$suffix);
$pt->setVar("STREET_NAME",$route);
$pt->setVar("TYPE_".$street_type,"selected");
$pt->setVar("SUFFIX_TYPE_".$suffix_type, " selected");
$pt->setVar("SUBURB",$locality);
$pt->setVar("ADDRESS_STATE_".$administrative_area_level_1," selected");
$pt->setVar("POSTCODE",$postal_code);

$pt->parse("MAIN", "main");
$pt->parse("WEBPAGE", "outside");
// Print out the page
$pt->p("WEBPAGE");
?>












