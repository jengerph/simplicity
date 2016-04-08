<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/add/index.php - Add Wholesaler
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

include_once "wholesalers.class";
include_once "services.class";
include_once "service_types.class";
include_once "realms.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
	
}

if ( !isset($_REQUEST["wholesaler_id"]) || $_REQUEST["wholesaler_id"] == "") {
	echo "Invalid Wholesaler ID.";
	exit(1);
}
// Assign the templates to use
if ( $user->class == 'admin' ) {
	$pt->setFile(array("outside1" => "base/outside1.html","outside2" => "base/outside2.html", "main" => "base/manage/wholesalers/edit/realms/add/index.html", "service_option" => "base/manage/wholesalers/service_option.html"));
} else if ( $user->class == 'reseller' ) {
	$pt->setFile(array("outside1" => "base/outside3.html","outside2" => "base/outside2.html", "main" => "base/manage/wholesalers/edit/realms/add/index.html", "service_option" => "base/manage/wholesalers/service_option.html"));
}

if ( $user->class == 'reseller' ) {
	if ( $_REQUEST["wholesaler_id"] != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

$services = new services();

$realms = new realms();
$realms->wholesaler_id = $_REQUEST["wholesaler_id"];

if (isset($_REQUEST['submit'])) {
	
	// Add new wholesalers
	$error_msg = '';

	$realms->wholesaler_id = $_REQUEST['wholesaler_id'];
	$realms->realm = $_REQUEST['realm'];
	$realms->type_id = $_REQUEST['service_type'];
			
	$vc = $realms->validate();

	if ($vc != 0) {
	
		$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

	} else {

		
		$realms->create();

    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/edit/realms/?wholesaler_id=" . $realms->wholesaler_id;

    header("Location: $url");
    exit();		
  
	}
}

$pt->setVar('WHOLESALER_ID', $realms->wholesaler_id);
$pt->setVar('REALM', $realms->realm);
$pt->setVar('SERVICE_ID', $realms->type_id);

//Get a list of service_types
$service_types = new service_types();
$st = $service_types->get_services();
for ( $x = 0; $x < count($st); $x++ ) {
	if ( $st[$x]["active"] == "yes" ){
		$pt->setVar('TYPE_ID', $st[$x]["type_id"]);
		$pt->setVar('TYPE_DESCRIPTION', $st[$x]["description"]);
		$pt->parse('SERVICE_OPTION','service_option', true);
	}
}

//Get a list of wholesalers
$services2 = new service_types();
$services_list = $services2->get_services();
$list_ready = $services2->service_list_realms('service_type',$services_list);

$pt->setVar('SERVICE_TYPE_LIST', $list_ready);

$pt->setVar('ST_' . strtoupper($realms->type_id) . '_SELECT', ' selected');

$pt->setVar("PAGE_TITLE", "New Realm");

		
// Parse the main page
$pt->parse("MAIN", "main");

// Correct outside
if ($user->class != 'customer' || $user->class != 'reseller') {
	$pt->parse("WEBPAGE", "outside1");
} else {
	$pt->parse("WEBPAGE", "outside2");
}	

// Print out the page
$pt->p("WEBPAGE");

