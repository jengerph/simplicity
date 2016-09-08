<?php

namespace XiSoap;

use XiSoap\Auth\WsseAuthHeader;

require_once dirname(__FILE__) . "/auth/WsseAuthHeader.php";
require_once dirname(__FILE__) . "/XiSoapClient.php";

class FactoryXiSoap
{

    private $config;

    public function __construct()
    {
        $this->config = require_once dirname(__FILE__) . "/../config/config.php";
    }

    public function hasResults($url, $functionName, Array $values)
    {
        $header = new WsseAuthHeader($this->config["username"], $this->config["username"]);
        $soapClient = new XiSoapClient($url, $this->config["username"], $this->config["username"]);
        $soapClient->setHeaders($header);
        $result = $soapClient->wdslCall($functionName, $values);

        if(is_array($result) && $result[0]->property_id != "") {
            return true;
        }

        return false;
    }

}