<?php

require_once dirname(__FILE__) . "/../includes/auth/WsseAuthHeader.php";
require_once dirname(__FILE__) . "/../includes/XiSoapClient.php";
require_once dirname(__FILE__) . "/../includes/FactoryXiSoap.php";

use PHPUnit\Framework\TestCase;

class FactoryXiSoapTest extends TestCase
{

    public function testCall()
    {
        $client = new \XiSoap\FactoryXiSoap("search.service");

        $lot_no = "";
        $unit_no = "";
        $house_no = "16";
        $street_type = "";
        $street_name = "Skimmer";
        $suburb = "Chisholm";
        $state_name = "nsw";
        $postcode = "2322";

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

        $this->assertNotFalse($client->hasResults("AddressSearch", $param));
    }

    public function testPropertyClass()
    {
        $client = new \XiSoap\FactoryXiSoap("search.service");

        $lot_no = "";
        $unit_no = "";
        $house_no = "16";
        $street_type = "";
        $street_name = "Skimmer";
        $suburb = "Chisholm";
        $state_name = "nsw";
        $postcode = "2322";

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

        $result = $client->getResults("AddressSearch", $param);
        var_dump($result[0]);
    }
}