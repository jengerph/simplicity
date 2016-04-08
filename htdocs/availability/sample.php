<?php
include_once "../setup.inc";
include_once("servicequalify.php");

// $address["streetNumber"] = $_REQUEST["street_number"];
// $route = $_REQUEST["route"];
// $street_name = "";
// $route = explode(" ", $_REQUEST["route"]);
// $address["streetType"] = $route[count($route)-1];
// for ($a=0; $a < count($route)-1; $a++) { 
// $street_name .= $route[$a] . " ";
// }
// $address["streetName"] = trim($street_name);

// $address["suburb"] = $_REQUEST["locality"];
// $address["state"] = $_REQUEST["administrative_area_level_1"];
// $address["postcode"] = $_REQUEST["postal_code"];

$address["streetNumber"] = (isset($_REQUEST["street_number"]) ? $_REQUEST["street_number"] : "");
$address["streetNumberSuffix"] = (isset($_REQUEST["street_number_suffix"]) ? $_REQUEST["street_number_suffix"] : "");
$address["streetName"] = (isset($_REQUEST["street_name"]) ? $_REQUEST["street_name"] : "");
$address["streetType"] = (isset($_REQUEST["street_type"]) ? $_REQUEST["street_type"] : "");
$address["streetTypeSufix"] = (isset($_REQUEST["street_type_suffix"]) ? $_REQUEST["street_type_suffix"] : "");
$address["suburb"] = (isset($_REQUEST["suburb"]) ? $_REQUEST["suburb"] : "");
$address["state"] = (isset($_REQUEST["state"]) ? $_REQUEST["state"] : "");
$address["postcode"] = (isset($_REQUEST["postcode"]) ? $_REQUEST["postcode"] : "");

$result = servicequal_address1($address);
header('Content-Type: application/json');
// echo json_encode($result);
print_r(json_encode($result,JSON_PRETTY_PRINT));
// print_r($address);

// ?street_number=&street_name=&streetType=&suburb=&state=&postcode=