<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/adsl_service/index.php - View ADSL
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../setup.inc";

include "../../doauth.inc";

include_once "audit.class";

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
	
} elseif ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
}

$pt->setVar("PAGE_TITLE", "ADSL Service");

// Assign the templates to use
if ( $user->class == 'admin' ) {
	$pt->setFile(array("outside1" => "base/outside1.html", "outside2" => "base/outside2.html", "main" => "base/manage/adsl_service/index.html"));
}

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside1");

// Print out the page
$pt->p("WEBPAGE");

