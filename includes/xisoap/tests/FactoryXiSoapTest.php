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

        $this->assertNotFalse($client->hasResults($param));
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

        $result = $client->getResults($param);
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
            "Property_ID" => "600960",
            "Contact_Name" => "Ashleigh Green",
            "Contact_Phone" => "",
            "Contact_Mobile" => "0435579713",
            "FNN" => "",
            "SIP_Username" => "",
            "SIP_Password" => "",
            "CLID" => "",
            "Comment" => "",
            "Contact_Email" => "",
            "Provider_Ref" => "600960",
            "Product_Type" => "Broadband",
            "Product_Code" => "OptiHome-99",
            "POI" => "POI-01"
        ];

        $client = new \XiSoap\FactoryXiSoap("connect.service");
        //$result = $client->getResults($param);
        //var_dump($result->Service_ID);
        //$this->assertNotEmpty($result->Service_ID);
        //var_dump($client->getClient()->getClient()->__getLastRequest());
        //var_dump($client->getClient()->getClient()->__getLastResponse());

    }

    public function testCancelService()
    {
        date_default_timezone_set("Australia/Melbourne");
        $param = [
            "Service_ID" => "",
            "Cancel_Date" => date("Y-m-d"),
        ];

        $client = new \XiSoap\FactoryXiSoap("cancel.service");
        //$response = $client->getResults($param);
        //$this->assertNotEmpty($response->Service_ID);
    }
}