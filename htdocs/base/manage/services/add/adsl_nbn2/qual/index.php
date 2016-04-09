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

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/adsl_nbn2/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/adsl_nbn2/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/adsl_nbn2/index.html"));
  
}

if (!isset($_REQUEST['qual_id'])) {
	
	// NO qual id provided
	echo "No qualifcation id provided.";
	exit();
}

if (!isset($_SESSION['qual_' . $_REQUEST['qual_id']])) {
	
	// Invalid qual
	echo "No qualifcation id provided.";
	exit();
}

$qual = $_SESSION['qual_' . $_REQUEST['qual_id']];

// Setup connection to Frontier
$config = new config();
$client = new SoapClient($config->frontier_dir . "/wsdl/FrontierLink.wsdl", array('local_cert'     => $config->frontier_dir . "/cert/frontierlink-cert.xi.com.au.cer",'trace'=>1));

$params = array();
$params['qualifyNationalWholesaleBroadbandProductRequest'] = array();
$params['qualifyNationalWholesaleBroadbandProductRequest']['standAloneQualification'] = true;

if ($qual['type'] == 'location') {
  if ( $qual['provider'] == "Telstra" ||  $qual['provider'] == "AAPT" ) {
    $params['qualifyNationalWholesaleBroadbandProductRequest']['telstraLocationID'] =  $qual['location_id'];
  } else if (  $qual['provider'] == "NBN" ) {
    $params['qualifyNationalWholesaleBroadbandProductRequest']['nbnLocationID'] = $qual['location_id'];
  }
} else {
	
	// FNN
  $params['qualifyNationalWholesaleBroadbandProductRequest']['endCSN'] = $qual['fnn'];
}

try{
        $response = $client->QualifyProduct($params);
       //$response = $client->__soapCall("sendMessages", array($params));           
   }
catch (SoapFault $exception) {

}

print_r($response);
