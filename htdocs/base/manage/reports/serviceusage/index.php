<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/reports/serviceusage/index.php - Display reports
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
require_once "service_types.class";
require_once "plans.class";
require_once "service_attributes.class";
require_once "accounting.class";

$pt->setVar("PAGE_TITLE", "Service Usage Report");


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
	$pt->setFile(array("outside1" => "base/outside1.html", "outside3" => "base/outside3.html", "main" => "base/manage/reports/serviceusage/report.html", "row" => "base/manage/reports/serviceusage/row.html"));


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

	$total_input = 0;
	$total_output = 0;
	$total_services = 0;
	// Get a list of services for a wholesaler
	$customer = new customers();
	$customers = $customer->get_customers($wholesaler->wholesaler_id);
	
	while ($cel = each($customers)) {

		// Output customr record details
		while ($cel2 = each($cel['value'])) {
			
			$pt->setVar(strtoupper($cel2['key']), $cel2['value']);
		}
				
		// Fix customer name
		if ($cel['value']['company_name'] == '') {
			$pt->setVar('CUSTOMER', $cel['value']['first_name'] . ' ' . $cel['value']['last_name']);
		} else{
			$pt->setVar('CUSTOMER', $cel['value']['company_name']);
		}
				
		// Get that customers services
		
		$service = new services();
		$service->customer_id = $cel['value']['customer_id'];
		$services = $service->get_all();
		
		while ($cel2 = each($services)) {


			// Is this a type 1, 2 or 8?
			if ($cel2['value']['state'] != 'inactive' && ($cel2['value']['type_id'] == 1 || $cel2['value']['type_id'] == 2 || $cel2['value']['type_id'] == 8)) {

    		// Output service record details
    		while ($cel3 = each($cel2['value'])) {
    			
    			$pt->setVar(strtoupper($cel3['key']), $cel3['value']);
    		}
  
  			if ($cel2['value']['identifier'] == '') {
  				$st = new service_types();
  				$st->type_id = $cel2['value']['type_id'];
  				$st->load();
  				
  				$pt->setVar('IDENTIFIER', $st->description);
  			}
  			
  			$plans = new plans();
  			$plans->plan_id = $cel2['value']['wholesale_plan_id'];
  			$plans->load();
  			
  			$pt->setVar('WHOLESALE_PLAN', $plans->description);

  			$plans = new plans();
  			$plans->plan_id = $cel2['value']['retail_plan_id'];
  			$plans->load();
  			
  			$pt->setVar('RETAIL_PLAN', $plans->description);
  			

				// Fetch username
				$sa = new service_attributes();
				$sa->service_id = $cel2['value']['service_id'];
				$sa->param = 'username';
				$sa->get_attribute();
				
				$username = $sa->value;

				$sa = new service_attributes();
				$sa->service_id = $cel2['value']['service_id'];
				$sa->param = 'realms';
				$sa->get_attribute();
				
				$username .= '@' . $sa->value;
				
				$pt->setVar('USERNAME', $username);
				
				$accounting = new accounting();
				$usage = $accounting->get_user_period($username, $start, $finish);
				
				$pt->setVar('UPLOAD', number_format($usage['input'] / pow(1024,floor(2))/1024,2,'.',''));
				$pt->setVar('DOWNLOAD', number_format($usage['output'] / pow(1024,floor(2))/1024,2,'.',''));
				
				$total_input += $usage['input'];
				$total_output += $usage['output'];
				$total_services++;
				
		
				$pt->parse('ROWS','row',true);  						

				
			}
		}
		


	}	

	$pt->setVar('TOTAL_UPLOAD', number_format($total_input / pow(1024,floor(2))/1024,2,'.',''));
	$pt->setVar('TOTAL_DOWNLOAD', number_format($total_output / pow(1024,floor(2))/1024,2,'.',''));
	$pt->setVar('TOTAL_SERVICES', $total_services);
	$pt->setVar('AVERAGE_UPLOAD', number_format(($total_input / pow(1024,floor(2))/1024)/$total_services,2,'.',''));
	$pt->setVar('AVERAGE_DOWNLOAD', number_format(($total_output / pow(1024,floor(2))/1024)/$total_services,2,'.',''));

	
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
$pt->setFile(array("outside1" => "base/outside1.html", "outside3" => "base/outside3.html", "main" => "base/manage/reports/serviceusage/main.html"));


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

	
		
	$_REQUEST['start_date'] = date('d/m/Y');
}
if (!isset($_REQUEST['finish_date'])) {
	
	$bits = explode('/', $_REQUEST['start_date']);
	
	$ts = $misc->date_ts($bits[2] . '-' . $bits[1] . '-' . $bits[0] . ' 06:00:00');
	
	$_REQUEST['finish_date'] = date('d/m/Y', $ts);
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
