<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/SoapService.php";

class ConnectService extends SoapService
{
    const WDSL = "connect_service.wsdl";

    private $functionName;

    public function __construct(Array $config)
    {
        $this->functionName = "ConnectService";
        parent::__construct($config, self::WDSL, $this->functionName);
    }
}