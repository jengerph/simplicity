<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/SoapService.php";

class CancelService extends SoapService
{
    const WDSL = "cancel_service.wsdl";

    public function __construct(Array $config)
    {
        parent::__construct($config, self::WDSL);
    }

}