<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/reports/wholesalebill/index.php - Display report
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
require_once "customers.class";

$pt->setVar("PAGE_TITLE", "Wholesale Bill Report");


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
	$pt->setFile(array("outside1" => "base/outside1.html", "outside3" => "base/outside3.html", "main" => "base/manage/reports/wholesalebill/report.html", "row" => "base/manage/reports/wholesalebill/row.html"));


	if ($user->class == 'reseller') {
		$_REQUEST['wholesaler_id'] = $user->access_id;
	}

	$wholesaler = new wholesalers();
	$wholesaler->wholesaler_id = $_REQUEST["wholesaler_id"];
	$wholesaler->load();
	
	$pt->setVar('WHOLESALER', $wholesaler->company_name);
	
	// Fix start
	$bits = explode('/', $_REQUEST['start_date']);
	$start = $bits[1] . '-' . $bits[0];

	
	$pt->setVar('REPORT_START_DATE', $_REQUEST['start_date']);
	
	
	// Get a list of services
	$service = new services();
	
	$list = $service->get_service_bill($_REQUEST['wholesaler_id'], $start);
	
	$total = 0;
	while ($cel = each($list)) {
	
		$service->service_id = $cel['value'];
		$service->load();

		if ($service->identifier == '') {;
			$st = new service_types();
			$st->type_id = $service->type_id;
			$st->load();
		
			$service->identifier = $st->description;
		}
		
		$pt->setVar('SERVICE_ID', $service->service_id);
		$pt->setVar('CUSTOMER_ID', $service->customer_id);
		$pt->setVar('IDENTIFIER', $service->identifier);
		$pt->setVar('STATE', $service->state);
		
		$customer = new customers();
		$customer->customer_id = $service->customer_id;
		$customer->load();
		
		if ($customer->company_name == '') {
			$pt->setVar('CUSTOMER', $customer->first_name . ' ' . $customer->last_name);
		} else{
			$pt->setVar('CUSTOMER', $customer->company_name);
		}
		
		$bill = $service->bill_service_wholesale($service->service_id, $start);
		

		while ($cel2 = each($bill)) {

			$pt->setVar('DESCRIPTION', $cel2['value']['description']);
			$pt->setVar('AMOUNT', $cel2['value']['amount']);
			$pt->parse('ROWS','row',true);
			
			$total += $cel2['value']['amount'];
		}
		
			
	}

	$pt->setVar('TOTAL', $total);
	
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
$pt->setFile(array("outside1" => "base/outside1.html", "outside3" => "base/outside3.html", "main" => "base/manage/reports/wholesalebill/main.html"));


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
	
		
	$_REQUEST['start_date'] = $month . '/' . $year;
}


$pt->setVar('START_DATE', $_REQUEST['start_date']);


$pt->parse("MAIN", "main");

if ($user->class == 'admin') {
	$pt->parse("WEBPAGE", "outside1");
} else if ($user->class == 'reseller') {
	$pt->parse("WEBPAGE","outside3");
}


// Print out the page
$pt->p("WEBPAGE");
