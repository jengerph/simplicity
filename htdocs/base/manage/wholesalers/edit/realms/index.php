<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/index.php - Delete Realm
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../setup.inc";

include "../../../../doauth.inc";

include_once "wholesalers.class";
include_once "service_types.class";
include_once "services.class";
include_once "wholesalers.class";
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
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside1" => "base/outside3.html","outside2" => "base/outside2.html", ));
} else if ( $user->class == 'admin' ) {
	$pt->setFile(array("outside1" => "base/outside1.html","outside2" => "base/outside2.html", ));
}

if ( !isset($_REQUEST["wholesaler_id"]) || $_REQUEST["wholesaler_id"] == "") {
	echo "Invalid Wholesaler ID.";
	exit(1);
}

// Assign the templates to use
$pt->setFile(array("main" => "base/manage/wholesalers/edit/realms/index.html", "row" => "base/manage/wholesalers/edit/realms/row.html"));

$realms = new realms();

$realms->wholesaler_id = $_REQUEST["wholesaler_id"];
$wholesaler_realms = $realms->get_my_realms2();

for ($i=0; $i < count($wholesaler_realms); $i++) { 
	$service = new service_types();
	$service->type_id = $wholesaler_realms[$i]["type_id"];
	$service->load();

	if ( $wholesaler_realms[$i]["type_id"] == 1 || $wholesaler_realms[$i]["type_id"] == 2 ) {
		$type = "ADSL/NBN";
	} else {
		$type = $service->description;
	}

	$pt->setVar('WHOLESALER_ID', $wholesaler_realms[$i]["wholesaler_id"]);
	$pt->setVar('REALM', $wholesaler_realms[$i]["realm"]);
	$pt->setVar('SERVICE_TYPE', $type);
	$pt->setVar('TYPE_ID', $wholesaler_realms[$i]["type_id"]);
	$pt->parse('ROWS','row','true');
}

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $_REQUEST["wholesaler_id"];
$wholesaler->load();

if ( $user->class == 'reseller' ) {
	if ( $wholesaler->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

$pt->setVar('WHOLESALER_ID', $wholesaler->wholesaler_id);


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
