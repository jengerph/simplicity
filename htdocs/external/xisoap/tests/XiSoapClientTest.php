<?php

require_once dirname(__FILE__) . "/../includes/auth/WsseAuthHeader.php";
require_once dirname(__FILE__) . "/../includes/XiSoapClient.php";

use PHPUnit\Framework\TestCase;
use XiSoap\XiSoapClient;

class XiSoapClientTest extends TestCase
{
    public function testCall()
    {
        $client = new XiSoapClient("service_qual.wsdl", "menger", "isi0Lixe");

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

        $result = $client->wdslCall("AddressSearch", $param);

        $this->assertNotEmpty($result);
    }
}