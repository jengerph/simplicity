<?php

$in = "100000014";

echo $in . "\n";
echo generateBpayRef($in) . "\n";

function generateBpayRef($number) {

    $number = preg_replace("/\D/", "", $number);

    // The seed number needs to be numeric
    if(!is_numeric($number)) return false;

    // Must be a positive number
    if($number <= 0) return false;

    // Get the length of the seed number
    $length = strlen($number);

    $total = 0;

    // For each character in seed number, sum the character multiplied by its one based array position (instead of normal PHP zero based numbering)
    for($i = 0; $i < $length; $i++) $total += $number{$i} * ($i + 1);

    // The check digit is the result of the sum total from above mod 10
    $checkdigit = fmod($total, 10);

    // Return the original seed plus the check digit
    return $number . $checkdigit;

}