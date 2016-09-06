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
include_once dirname(__FILE__) . "/../xisoap/includes/FactoryXiSoap.php";
include_once dirname(__FILE__) . "/../xisoap/includes/Validate.php";

$return = array();

if ($_REQUEST['pass'] == '') {

    $return['qual_id'] = 0;
    $return['msg'] = 'Error: password for qualification missing.';

} else {

    //print_r($_GET);



    //echo '<br>';
    //print_r($params);

    $street_type = substr(strrchr($_GET["street_name"], " "), 1);

    //Sanitise only if values can be empty and not required by soap server
    $param = array(
        "lot_no"      => $validator->sanitiseString((($_GET["level"]) ?: "")),
        "unit_no"     => $validator->sanitiseString((($_GET["unit_no"]) ?: "")),
        "house_no"    => $validator->validateString((($_GET["street_number"]) ?: "")),
        "street_type" => $validator->sanitiseString((($street_type) ?: "")),
        "street_name" => $validator->validateString((($_GET["street_name"]) ?: "")),
        "suburb"      => $validator->validateString((($_GET["locality"]) ?: "")),
        "state_name"  => $validator->sanitiseString((($_GET["state"]) ?: "")),
        "postcode"    => $validator->validatePostcode((($_GET["postcode"]) ?: ""))
    );

    $factorySoap = new \XiSoap\FactoryXiSoap();
    if($factorySoap->hasResults("service_qual.wsdl", "AddressSearch", $param)) {
        return true;
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

