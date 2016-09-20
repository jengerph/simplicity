<?php

namespace XiSoap;

use XiSoap\Auth\WsseAuthHeader;

require_once dirname(__FILE__) . "/auth/WsseAuthHeader.php";
require_once dirname(__FILE__) . "/XiSoapClient.php";
require_once dirname(__FILE__) . "/Validate.php";

class FactoryXiSoap
{

    private $config;
    private $validate;

    public function __construct()
    {
        $this->config = require_once dirname(__FILE__) . "/../config/config.php";
        $this->validate = new Validate();
    }

    public function hasResults($url, $functionName, Array $values)
    {
        $result = $this->getResults($url, $functionName, $values);

        if(is_array($result) && $result[0]->property_id != "") {
            return true;
        }

        return false;
    }

    public function getResults($url, $functionName, Array $values)
    {
        $header = new WsseAuthHeader($this->config["username"], $this->config["username"]);
        $soapClient = new XiSoapClient($url, $this->config["username"], $this->config["username"]);
        $soapClient->setHeaders($header);
        $result = $soapClient->wdslCall($functionName, $this->validate->sanitiseData($values));

        return $result;
    }

}