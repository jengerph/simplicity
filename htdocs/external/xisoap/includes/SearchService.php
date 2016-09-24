<?php

namespace XiSoap;

require_once dirname(__FILE__) . "/SoapService.php";

class SearchService extends SoapService
{
    const WDSL = "service_qual.wsdl";

    public function __construct(Array $config)
    {
        parent::__construct($config, self::WDSL);
    }


}