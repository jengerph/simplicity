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
        $this->assertNotEmpty($result[0]->property_class);
    }

    public function testConnectService()
    {
        /*
         * Product code
         OPHAEB-12 = 12M/1M
         OPHAEB-25 = 25M/5M
         OPHAEB-50 = 50M/20M
         OPHAEB-99 = 100M/40M
        */

        /* POI
        HAIS = ACT or NSW
        CREK = QLD
        KING = VIC
        */

        /*
        12M/1M	250GB	40.50
        25M/5M	250GB	47.10
        25M/10M	250GB	51.50
        50M/20M	250GB	58.10

        12M/1M	Unlimited	41.50
        25M/5M	Unlimited	50.10
        25M/510M	Unlimited	55.50
        25-50M/5-20M	Unlimited	64.10

        setup fee is 62.73 ex

        Early termination on 24 month is 200

        */


        $client = new \XiSoap\FactoryXiSoap("connect.service");

        $param = [
            "Property_ID" => "833111",
            "Contact_Name" => "Matthew Enger",
            "Contact_Phone" => "",
            "Contact_Mobile" => "",
            "FNN" => "",
            "SIP_Username" => "",
            "SIP_Password" => "",
            "CLID" => "",
            "Comment" => "",
            "Contact_Email" => "m.enger@xi.com.au",
            "Provider_Ref" => "833111/test",
            "Product_Type" => "Broadband",
            "Product_Code" => "OPHAEB-12",
            "POI" => "HAIS",
        ];

        $client = new \XiSoap\FactoryXiSoap("connect.service");
        $result = $client->getResults("ConnectService", $param);
        $this->assertNotEmpty($result[0]->service_id);

    }
}