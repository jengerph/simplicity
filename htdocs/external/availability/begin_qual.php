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

$return = array();

if ($_REQUEST['pass'] == '') {

	$return['qual_id'] = 0;
	$return['msg'] = 'Error: password for qualification missing.';
	
} else {

  //print_r($_GET);
  

  
  //echo '<br>';
  //print_r($params);
  
  
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

