<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/ISoapService.php";

class ConnectService implements ISoapService
{
    const WDSL = "connect_service.wsdl";
    private $config;

    public function __construct(Array $config)
    {
        $this->config = $config;
    }

    public function getWSDL()
    {
        return $this->config["wsdl_path"] . "/" . self::WDSL;
    }

}