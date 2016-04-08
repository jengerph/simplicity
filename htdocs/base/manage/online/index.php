<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/cohen/base/manage/online/index.php
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


if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
	
} else if ($user->class == 'reseller') {
	
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
	
}

  
$lh = new login_history();
$records = $lh->getOnline();
 
// Assign the templates to use
$pt->setFile(array(	"outside1" 	=> "base/outside1.html", 
  			"main" 			=> "base/manage/online/report-main.html",
  			"row"  => "base/manage/online/report-row.html"));
  
while ( $cel = each($records)) {
		
	$pt->setVar('USERNAME', $cel['value']['username']);
	$pt->setVar('HOSTNAME', $cel['value']['hostname']);
	$pt->setVar('IP', $cel['value']['ip']);
	$pt->setVar('LOGIN', date('d-m-Y H:i:s',strtotime($cel['value']['dt'])));
	$pt->setVar('LAST_ACTIVITY', date('d-m-Y H:i:s',strtotime($cel['value']['last_activity'])));

	$user = new user();
	$user->username = $cel['value']['username'];
	$user->load();

	$pt->setVar('FIRST_NAME', $user->first_name);
	$pt->setVar('LAST_NAME', $user->last_name);

	$pt->parse('ROWS','row','true');


} 
$pt->setVar("PAGE_TITLE", "Online Users Report");
  
// Parse the main page
$pt->parse("MAIN", "main");
  
$pt->parse("WEBPAGE", "outside1");

// Print out the page
$pt->p("WEBPAGE");


?>
