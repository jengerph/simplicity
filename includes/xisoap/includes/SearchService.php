<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/SoapService.php";

class SearchService extends SoapService
{
    const WSDL = "service_qual.wsdl";

    private $functionName;

    public function __construct(Array $config)
    {
        $this->functionName = "AddressSearch";
        parent::__construct($config, self::WSDL, $this->functionName);
    }

}