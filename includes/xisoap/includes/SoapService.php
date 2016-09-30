<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/ISoapService.php";

abstract class SoapService implements ISoapService
{
    private $config;
    private $wsdl;

    protected function __construct(Array $config, $wsdl)
    {
        $this->config = $config;
        $this->wsdl = $wsdl;
    }

    public function getWSDL()
    {
        return $this->config["wsdl_path"] . "/" . $this->wsdl;
    }
}