<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/adsl_nbn2/qual/index.php - Preform qualification
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../../setup.inc";
include "../../../../../doauth.inc"; 
include_once "config.class";
include_once "customers.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if (!isset($_REQUEST['qual_id'])) {
	
	// NO qual id provided
	echo "No qualifcation id provided.";
	exit();
}

$pt->setVar('QUAL_ID', $_REQUEST['qual_id']);

if (!isset($_SESSION['qual_' . $_REQUEST['qual_id']])) {
	
	// Invalid qual
	echo "No qualifcation id provided.";
	exit();
}

$qual = $_SESSION['qual_' . $_REQUEST['qual_id']];

if (!isset($qual['result'])) {
	

  $_SESSION['qual_' . $_REQUEST['qual_id']]['quals'] = $quals;

  $qual = $_SESSION['qual_' . $_REQUEST['qual_id']];

}

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html"));
  
}


$pt->setFile(array("main" => "base/manage/services/add/opticomm/qual/index.html", "services_available" => "base/manage/services/add/opticomm/qual/services_available.html"));

$customer = new customers();
$customer->customer_id = $qual['customer_id'];
$customer->load();

$opticommPropertyClass[0] = "0 indicates the property class cannot be retrieved at this address";
$opticommPropertyClass[1] = '1 indicates this property will get service when it actually exists';
$opticommPropertyClass[2] = '2 indicates the property is serviceable but no ONT is installed yet';
$opticommPropertyClass[3] = '3 indicates that the service is active and has an ONT installed';

$pt->setVar('ORDER_ADDRESS', $qual['address']);
$pt->setVar('SERVICE_NUMBER', $qual['fnn']);
$pt->setVar('LOCATIONID', $qual['property_id']);
$pt->setVar('MANUAL', $qual['manual']);
$pt->setVar('NBNSERVICEABILITYCLASS', $qual['property_class']);
$pt->setVar('NBNSERVICEABILITYCLASSTEXT', $opticommPropertyClass[$qual['property_class']]);
/*
$pt->setVar('NBNNEWDEVELOPMENTSCHARGEAPPLIES',$nbnNewDevelopmentsChargeApplies);
*/

// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");
	
			
