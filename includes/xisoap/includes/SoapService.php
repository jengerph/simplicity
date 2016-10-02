<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/ISoapService.php";

abstract class SoapService implements ISoapService
{
    private $config;
    private $wsdl;
    private $functionName;

    protected function __construct(Array $config, $wsdl, $functionName)
    {
        $this->config = $config;
        $this->wsdl = $wsdl;
        $this->functionName = $functionName;
    }

    public function getWSDL()
    {
        return $this->config["wsdl_path"] . "/" . $this->wsdl;
    }

    /**
     * Returns children function name to call
     * @return mixed
     */
    public function getFunctionName()
    {
        return $this->functionName;
    }
}