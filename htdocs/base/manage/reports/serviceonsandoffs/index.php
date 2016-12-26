<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/reports/serviceonsandoffs/index.php - Display reports
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../setup.inc";

include "doauth.inc";
require_once "user.class";
require_once "wholesalers.class";
require_once "services.class";

$pt->setVar("PAGE_TITLE", "Service Ons and Offs Report");


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

if (isset($_REQUEST['submit'])) {

	// Report time

	// Assign the templates to use
	$pt->setFile(array("outside1" => "base/outside1.html", "outside3" => "base/outside3.html", "main" => "base/manage/reports/serviceonsandoffs/report.html", "row" => "base/manage/reports/serviceonsandoffs/row.html"));


	if ($user->class == 'reseller') {
		$_REQUEST['wholesaler_id'] = $user->access_id;
	}

	$wholesaler = new wholesalers();
	$wholesaler->wholesaler_id = $_REQUEST["wholesaler_id"];
	$wholesaler->load();
	
	$pt->setVar('WHOLESALER', $wholesaler->company_name);
	
	// Fix start & Finish
	$bits = explode('/', $_REQUEST['start_date']);
	$start = $bits[2] . '-' . $bits[1] . '-'.  $bits[0];

	$bits = explode('/', $_REQUEST['finish_date']);
	$finish = $bits[2] . '-' . $bits[1] . '-' . $bits[0];
	
	$pt->setVar('REPORT_START_DATE', $_REQUEST['start_date']);
	$pt->setVar('REPORT_FINISH_DATE', $_REQUEST['finish_date']);
	
	
	// Get a list of services
	$service = new services();
	
	$list = $service->get_service_ons_and_offs($_REQUEST['wholesaler_id'], $start, $finish);

	$ons = 0;
	$offs = 0;
	while ($cel = each($list)) {
		
		while ($cel2 = each($cel['value'])) {
			
			$pt->setVar(strtoupper($cel2['key']), $cel2['value']);
		}
		
		// Fix customer
		if ($cel['value']['company_name'] == '') {
			$pt->setVar('CUSTOMER', $cel['value']['first_name'] . ' ' . $cel['value']['last_name']);
		} else{
			$pt->setVar('CUSTOMER', $cel['value']['company_name']);
		}
		
		if ($cel['value']['identifier'] == '') {
			$pt->setVar('IDENTIFIER', $cel['value']['service_type']);
		}
		
		if ($cel['value']['state'] == 'active') {
			
			$ons++;
		} else {
			$offs++;
		}
		
		$pt->parse('ROWS','row',true);
	}

	$pt->setVar('ONS', $ons);
	$pt->setVar('OFFS', $offs);
	
  $pt->parse("MAIN", "main");
  
  if ($user->class == 'admin') {
  	$pt->parse("WEBPAGE", "outside1");
  } else if ($user->class == 'reseller') {
  	$pt->parse("WEBPAGE","outside3");
  }
  
  
  
  
  // Print out the page
  $pt->p("WEBPAGE");
  exit();		
}

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html", "outside3" => "base/outside3.html", "main" => "base/manage/reports/serviceonsandoffs/main.html"));


//Get a list of wholesalers
$wholesalers = new wholesalers();
$wholesalers_list = $wholesalers->get_wholesalers();
$list_ready_w = $wholesalers->wholesalers_list('wholesaler_id',$wholesalers_list);


$wholesaler = new wholesalers();

if ( isset($_REQUEST["wholesaler_id"]) ) {
$wholesaler->wholesaler_id = $_REQUEST["wholesaler_id"];
$wholesaler->load();
}

if ( $user->class == "admin" ) {
  $pt->setVar('WHOLESALER_LIST', $list_ready_w);
} else if ($user->class == "reseller") {
  $wholesaler = new wholesalers();
  $wholesaler->wholesaler_id = $user->access_id;
  $wholesaler->load();
  $pt->setVar('WHOLESALER_LIST', $wholesaler->company_name);
}

if (!isset($_REQUEST['start_date'])) {

	$year = date('Y');
	$month = date('m');
	$day = date('d');
	
	$month--;
	
	if ($month == 0) {
		$month = 12;
		$year--;
	}
	
		
	$_REQUEST['start_date'] = '01/' . $month . '/' . $year;
}
if (!isset($_REQUEST['finish_date'])) {
	
	$bits = explode('/', $_REQUEST['start_date']);
	
	$ts = $misc->date_ts($bits[2] . '-' . $bits[1] . '-' . $bits[0] . ' 06:00:00');
	
	$_REQUEST['finish_date'] = date('t/m/Y', $ts);
}

$pt->setVar('START_DATE', $_REQUEST['start_date']);
$pt->setVar('FINISH_DATE', $_REQUEST['finish_date']);

$pt->parse("MAIN", "main");

if ($user->class == 'admin') {
	$pt->parse("WEBPAGE", "outside1");
} else if ($user->class == 'reseller') {
	$pt->parse("WEBPAGE","outside3");
}


// Print out the page
$pt->p("WEBPAGE");
