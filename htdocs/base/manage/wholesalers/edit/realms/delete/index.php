<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/add/index.php - Edit wholesaler
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
include_once "service_types.class";
include_once "wholesaler_service_types.class";
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
	
} else if ( $user->class == 'reseller' ) {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
}


if ( !isset($_REQUEST["wholesaler_id"]) || $_REQUEST["wholesaler_id"] == "") {
	echo "Invalid Wholesaler ID.";
	exit(1);
}

if ( !isset($_REQUEST["realm"]) || $_REQUEST["realm"] == "") {
	echo "Invalid Realm.";
	exit(1);
}

if ( !isset($_REQUEST["type_id"]) || $_REQUEST["type_id"] == "") {
	echo "Invalid Type ID.";
	exit(1);
}

$realms = new realms();

$realms->wholesaler_id = $_REQUEST['wholesaler_id'];
$realms->realm = $_REQUEST['realm'];
$realms->type_id = $_REQUEST['type_id'];

if ( $user->class == 'reseller' || $user->class == 'customer' ) {
	if ( $realms->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
		// Parse the main page
		$pt->parse("MAIN", "main");
		$pt->parse("WEBPAGE", "outside");

		// Print out the page
		$pt->p("WEBPAGE");

		exit();
	}
}
		
$vc = $realms->validate();

if ($vc != 0) {

	$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

} else {

$realms->delete();

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