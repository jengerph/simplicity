<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/export/availability/begin_qual.php - Begin a service qualification
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../setup.inc";
include_once "website_servicequal.class";
include_once dirname(__FILE__) . "/../../../includes/xisoap/includes/FactoryXiSoap.php";
include_once dirname(__FILE__) . "/../../../includes/xisoap/includes/Validate.php";

$return = array();

if ($_REQUEST['pass'] == '') {

    $return['qual_id'] = 0;
    $return['msg'] = 'Error: password for qualification missing.';

} else {

    //print_r($_GET);



    //echo '<br>';
    //print_r($params);


    //Check for Opticomm eligibility
    $validator = new \XiSoap\Validate();

    //Sanitise only, if values can be empty and not required by soap server
    $param = array(
        "lot_no"      => ($_GET["level"]) ? $validator->sanitiseString($_GET["level"]) : "",
        "unit_no"     => ($_GET["unit_no"]) ? $validator->sanitiseString($_GET["unit_no"]) : "",
        "house_no"    => ($_GET["street_number"] && $validator->validateString($_GET["street_number"])) ? $_GET["street_number"] : "",
        "street_type" => ($_GET["street_type"]) ? $validator->sanitiseString($_GET["street_type"]) : "",
        "street_name" => ($_GET["street_name"] && $validator->validateString($_GET["street_name"])) ? $_GET["street_name"] : "",
        "suburb"      => ($_GET["locality"] && $validator->validateString($_GET["locality"])) ? $_GET["locality"] : "",
        "state_name"  => ($_GET["state"]) ? $validator->sanitiseString($_GET["state"]) : "",
        "postcode"    => ($_GET["postcode"] && $validator->validatePostcode($_GET["postcode"])) ? $_GET["postcode"] : ""
    );

    //Opticomm street name can only contains the name and not the type (e.g. Cotters, instead of Cotters Road)
    $param["street_name"] = substr($param["street_name"], 0, strrpos($param["street_name"], " "));
    //Google Places API somtimes doesn't include street_number
    $param["house_no"] = $param["house_no"] ?: substr($_GET["autocomplete"], 0, strpos($_GET["autocomplete"], " "));

    $factorySoap = new \XiSoap\FactoryXiSoap("search.service");
    if($factorySoap->hasResults($param)) {
        $return["qual_id"] = true;
        echo json_encode($return);
        die();
    }

  
  $wsq = new website_servicequal();
  $wsq->level = $_REQUEST['level'];
  $wsq->street_number = $_REQUEST['street_number'];
  $wsq->street_name = $_REQUEST['street_name'];
  $wsq->locality = $_REQUEST['locality'];
  $wsq->state = $_REQUEST['state'];
  $wsq->postcode = $_REQUEST['postcode'];
  $wsq->pass = $_REQUEST['pass'];
  $wsq->create();

  //echo '<br>';
  //echo $wsq->qual_id;
  
  $return['qual_id'] =  $wsq->qual_id;
  
  $command = '/usr/bin/php -f ' . getcwd() . '/do_qual.php ' . $wsq->qual_id;
  
  //echo $command;

	exec( "$command > /dev/null &", $arrOutput);

}

echo json_encode($return);

