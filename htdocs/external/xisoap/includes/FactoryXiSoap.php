<?php

namespace XiSoap;

use XiSoap\Auth\WsseAuthHeader;

require_once dirname(__FILE__) . "/auth/WsseAuthHeader.php";
require_once dirname(__FILE__) . "/XiSoapClient.php";
require_once dirname(__FILE__) . "/Validate.php";
require_once dirname(__FILE__) . "/ISoapService.php";
require_once dirname(__FILE__) . "/SearchService.php";
require_once dirname(__FILE__) . "/ConnectService.php";

class FactoryXiSoap
{

    private $config;
    private $validate;
    private $service;

    /**
     * FactoryXiSoap constructor.
     * @param $serviceType
     */
    public function __construct($serviceType)
    {
        try {

            $this->config = require_once dirname(__FILE__) . "/../config/config.php";
            $this->validate = new Validate();

            if(!$this->validate->validateString($serviceType)) {
                throw new \InvalidArgumentException("Service type must be a string. Refer to the documentation for more information");
            }

            if ($serviceType === "search.service") {
                $this->service = new SearchService($this->config);
            }
            if ($serviceType === "connect.service") {
                $this->service = new ConnectService($this->config);
            }

        } catch (\Exception $e) {
            trigger_error("An error occurred, please contact technical support");
        }

    }

    /**
     * @param $functionName
     * @param array $values
     * @return bool
     */
    public function hasResults($functionName, Array $values)
    {
        $result = $this->getResults($functionName, $values);

        if(is_array($result) && $result[0]->property_id != "") {
            return true;
        }

        return false;
    }

    /**
     * @param $functionName
     * @param array $values
     * @return string
     */
    public function getResults($functionName, Array $values)
    {
        $header = new WsseAuthHeader($this->config["username"], $this->config["username"]);
        $soapClient = new XiSoapClient($this->service, $this->config["username"], $this->config["username"]);
        $soapClient->setHeaders($header);
        $result = $soapClient->wsdlCall($functionName, $this->validate->sanitiseData($values));

        return $result;
    }

}