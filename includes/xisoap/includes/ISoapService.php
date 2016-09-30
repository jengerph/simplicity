<?php

namespace XiSoap;

interface ISoapService
{
    /**
     * Provides the right type of wdsl to load
     * @return mixed
     */
    public function getWSDL();

}