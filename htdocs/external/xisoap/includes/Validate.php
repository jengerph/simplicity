<?php

namespace XiSoap;

class Validate
{

    public function validateString($string)
    {
        return ($this->sanitiseString($string) !== "") ? true : false;
    }

    public function sanitiseString($string)
    {
        return filter_var(trim($string), FILTER_SANITIZE_STRING);
    }

    public function validatePostcode($postcode)
    {
        return ($this->sanitisePostcode($postcode) !== "" && strlen($postcode) == 4) ? true : false;
    }

    public function sanitisePostcode($postcode)
    {
        return filter_var(trim($postcode), FILTER_SANITIZE_NUMBER_INT);
    }

    public function sanitiseData(Array $values)
    {
        foreach($values as &$value) {
            $this->sanitiseString($value);
        }

        return $values;
    }

}