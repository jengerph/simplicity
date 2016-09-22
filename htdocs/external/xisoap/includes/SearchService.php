<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/ISoapService.php";

class SearchService implements ISoapService
{
    const WDSL = "service_qual.wsdl";
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