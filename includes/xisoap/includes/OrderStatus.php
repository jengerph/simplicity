<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/SoapService.php";

class OrderStatus extends SoapService
{
    const WSDL = "order_status.wsdl";

    private $functionName;

    public function __construct(Array $config)
    {
        $this->functionName = "OrderStatus";
        parent::__construct($config, self::WSDL, $this->functionName);
    }

}