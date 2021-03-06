<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/SoapService.php";

class CancelService extends SoapService
{
    const WSDL = "cancel_service.wsdl";

    private $functionName;

    public function __construct(Array $config)
    {
        $this->functionName = "CancelService";
        parent::__construct($config, self::WSDL, $this->functionName);
    }

}