<?php

require_once dirname(__FILE__) . "/../includes/auth/WsseAuthHeader.php";
require_once dirname(__FILE__) . "/../includes/XiSoapClient.php";
require_once dirname(__FILE__) . "/../includes/FactoryXiSoap.php";

use PHPUnit\Framework\TestCase;

class FactoryXiSoapTest extends TestCase
{

    public function testCall()
    {
        $client = new \XiSoap\FactoryXiSoap();

        $lot_no = "";
        $unit_no = "";
        $house_no = "13";
        $street_type = "Place";
        $street_name = "Joseba";
        $suburb = "Springfield Lakes";
        $state_name = "qld";
        $postcode = "4300";

        $param = array(
            "lot_no" => ($lot_no) ?: "",
            "unit_no" => ($unit_no) ?: "",
            "house_no" => ($house_no) ?: "",
            "street_type" => ($street_type) ?: "",
            "street_name" => ($street_name) ?: "",
            "suburb" => ($suburb) ?: "",
            "state_name" => ($state_name) ?: "",
            "postcode" => ($postcode) ?: ""
        );

        $this->assertNotFalse($client->hasResults("service_qual.wsdl", "AddressSearch", $param));
    }
}