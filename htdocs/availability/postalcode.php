<?php
include_once "../setup.inc";
include_once "postcodes.class";

$suburb = $_REQUEST["suburb"];
$state = $_REQUEST["state"];

$postcodes = new postcodes();
$postcodes->locality = $suburb;
$postcodes->state = $state;
$postcode_arr = $postcodes->get_postcodes();
$postcode_list = $postcodes->postcode_list("postal_code",$postcode_arr);
print_r($postcode_list);