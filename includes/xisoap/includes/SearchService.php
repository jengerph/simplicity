<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/SoapService.php";

class SearchService extends SoapService
{
    const WDSL = "service_qual.wsdl";

    private $functionName;

    public function __construct(Array $config)
    {
        $this->functionName = "AddressSearch";
        parent::__construct($config, self::WDSL, $this->functionName);
    }

}