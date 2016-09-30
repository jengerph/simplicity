<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/SoapService.php";

class ConnectService extends SoapService
{
    const WDSL = "connect_service.wsdl";

    public function __construct(Array $config)
    {
        parent::__construct($config, self::WDSL);
    }

}