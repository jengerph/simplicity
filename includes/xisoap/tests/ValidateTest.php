<?php

require_once dirname(__FILE__) . "/../includes/auth/WsseAuthHeader.php";
require_once dirname(__FILE__) . "/../includes/Validate.php";

use PHPUnit\Framework\TestCase;
use XiSoap\Validate;

class ValidateTest extends TestCase
{
    private $validation;

    public function __construct()
    {
        parent::__construct();
        $this->validation = new Validate();
    }

    public function testValidateString()
    {
        $string = "test test";
        $this->assertFalse(!$this->validation->validateString($string));
    }

    public function testValidatePostcode()
    {
        $postcode = "1234";
        $this->assertFalse(!$this->validation->validatePostcode($postcode));
    }

}