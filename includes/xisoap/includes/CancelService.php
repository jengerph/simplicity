<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/SoapService.php";

class CancelService extends SoapService
{
    const WDSL = "cancel_service.wsdl";

    private $functionName;

    public function __construct(Array $config)
    {
        $this->functionName = "CancelService";
        parent::__construct($config, self::WDSL, $this->functionName);
    }

}