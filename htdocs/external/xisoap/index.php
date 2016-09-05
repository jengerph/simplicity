<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/includes/auth/WsseAuthHeader.php";
require_once dirname(__FILE__) . "/includes/XiSoapClient.php";
require_once dirname(__FILE__) . "/includes/Validate.php";

$config = require_once dirname(__FILE__) . "/config/config.php";

$validator = new Validate();

//Sanitise only if values can be empty and not required by soap server
$param = array(
    "lot_no"      => $validator->sanitiseString((($_GET["lot_no"]) ?: "")),
    "unit_no"     => $validator->sanitiseString((($_GET["unit_no"]) ?: "")),
    "house_no"    => $validator->validateString((($_GET["house_no"]) ?: "")),
    "street_type" => $validator->sanitiseString((($_GET["street_type"]) ?: "")),
    "street_name" => $validator->validateString((($_GET["street_name"]) ?: "")),
    "suburb"      => $validator->validateString((($_GET["suburb"]) ?: "")),
    "state_name"  => $validator->sanitiseString((($_GET["state_name"]) ?: "")),
    "postcode"    => $validator->validatePostcode((($_GET["postcode"]) ?: ""))
);

$client = new XiSoapClient("service_qual.wsdl", $config["username"], $config["password"]);
$result = $client->wdslCall("AddressSearch", $param);

if(is_array($result) && count($result) > 0) {
    return true;
}