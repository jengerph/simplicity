<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/reports/index.php - Display reports main page
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../setup.inc";

include "doauth.inc";
require_once "user.class";

$pt->setVar("PAGE_TITLE", "Reports Main Menu");

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html", "outside3" => "base/outside3.html", "main" => "base/manage/reports/main.html"));


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

$pt->parse("MAIN", "main");

if ($user->class == 'admin') {
	$pt->parse("WEBPAGE", "outside1");
} else if ($user->class == 'reseller') {
	$pt->parse("WEBPAGE","outside3");
}


// Print out the page
$pt->p("WEBPAGE");
