<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/index.php - View Wholesalers
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

include_once "wholesalers.class";
include_once "services.class";
include_once "service_types.class";
include_once "wholesaler_service_types.class";
include_once "customers.class";

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


$pt->setVar("PAGE_TITLE", "Manage Wholesalers");

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html", 
					"outside2" => "base/outside2.html", 
					"main" => "base/manage/wholesalers/index.html", 
					"row" => "base/manage/wholesalers/row.html", 
					"wholesaler_box" => "base/manage/wholesalers/wholesaler_box.html", 
					"customer_li" => "base/manage/wholesalers/customer_li.html", 
					"wholesaler_table" => "base/manage/wholesalers/wholesaler_table.html", 
					"outside3" => "base/outside3.html", 
					"welcome_banner" => "base/welcome_banner.html", 
					"back_link" => "base/manage/wholesalers/back_link.html"));

if ( $user->class == 'reseller' ){
	$_REQUEST["wholesaler_id"] = $user->access_id;
}

if (!isset($_REQUEST['inactive'])) {
	$_REQUEST['inactive'] = 'yes';
}

$pt->setVar('INACTIVE_DISPLAY', $_REQUEST['inactive']);

if ($_REQUEST['inactive'] == 'yes') {
	$pt->setVar('INACTIVE_NEW', 'no');
} else {
	$pt->setVar('INACTIVE_NEW', 'yes');
}

if ( isset($_REQUEST["wholesaler_id"]) && $_REQUEST["wholesaler_id"] !="" ) {

$wholesalers = new wholesalers();
$wholesalers->wholesaler_id = $_REQUEST["wholesaler_id"];
$wholesalers->load();

$customers = new customers();
$customers_list = $customers->get_customers();

$services = new wholesaler_service_types();

	$services->wholesaler_id = $wholesalers->wholesaler_id;
	$services_list = $services->get_wholesaler_services();
	$ws_services = "";

	for ( $y = 0; $y < count($services_list); $y++ ) {
		$type = new service_types();
		$type->type_id = $services_list[$y]["type_id"];
		$type->load();
		if ( $type->active == 'yes' ) {
			$ws_services .= $type->description . ", ";
		}
	}

			$pt->setVar('WHOLESALER_ID', $wholesalers->wholesaler_id);
			$pt->setVar('WHOLESALER_NAME', $wholesalers->company_name);
			$pt->setVar('WS_EMAIL', $wholesalers->email);
			$pt->setVar('WS_ACTIVE', $wholesalers->active);
			$pt->setVar('WS_PHONE', $wholesalers->phone);
			$pt->setVar('WS_ABN', $wholesalers->abn);
			
			$pt->clearVar('CUSTOMER_LI');

			for ($i=0; $i < count($customers_list); $i++) { 
				if ( $customers_list[$i]["wholesaler_id"] == $wholesalers->wholesaler_id ) {
					$pt->setVar('CUSTOMER_ID', $customers_list[$i]["customer_id"]);
					if ( $customers_list[$i]["company_name"] ) {
						$customer_name = $customers_list[$i]["company_name"];
					} else {
						$customer_name = $customers_list[$i]["first_name"] . " " . $customers_list[$i]["last_name"];
					}
					if ( empty($customers_list[$i]["phone"]) ) {
						$customers_list[$i]["phone"] = $customers_list[$i]["mobile"];
					}

					$pt->setVar('CUSTOMER_NAME', $customer_name);
					$pt->setVar('CUSTOMER_PHONE', $customers_list[$i]["phone"]);
					$pt->setVar('ADSL_COUNT', services_count($customers_list[$i]["customer_id"],'1'));
					$pt->setVar('NBN_COUNT', services_count($customers_list[$i]["customer_id"],'2'));
					$pt->setVar('OPTICOMM_COUNT', services_count($customers_list[$i]["customer_id"],'8'));
					$pt->setVar('OUTBOUND_VOICE_COUNT', services_count($customers_list[$i]["customer_id"],'6'));
					$pt->setVar('INBOUND_VOICE_COUNT', services_count($customers_list[$i]["customer_id"],'5'));
					$pt->setVar('WEB_HOSTING_COUNT', services_count($customers_list[$i]["customer_id"],'3'));
					$pt->parse('CUSTOMER_LI','customer_li','true');
				}
			}

			$pt->parse('WHOLESALER_BOX','wholesaler_box','true');
	$pt->setVar('SERVICES_'.$wholesalers->wholesaler_id, rtrim($ws_services,', '));
} else {
	$wholesalers = new wholesalers();

	$wholesalers = $wholesalers->get_wholesalers();

	$services = new wholesaler_service_types();

	for ($x = 0; $x < count($wholesalers) ; $x++) {
		$services->wholesaler_id = $wholesalers[$x]["wholesaler_id"];
		$services_list = $services->get_wholesaler_services();
		$ws_services = "";

		for ( $y = 0; $y < count($services_list); $y++ ) {
			$type = new service_types();
			$type->type_id = $services_list[$y]["type_id"];
			$type->load();
			if ( $type->active == 'yes' ) {
				$ws_services .= $type->description . ", ";
			}
		}
		if($_REQUEST['inactive']=='yes'){
			if($wholesalers[$x]["active"]=='yes'){

				$pt->setVar('WHOLESALER_ID', $wholesalers[$x]["wholesaler_id"]);
				$pt->setVar('COMPANY_NAME', $wholesalers[$x]["company_name"]);
				$pt->setVar('PHONE', $wholesalers[$x]["phone"]);
				$pt->setVar('EMAIL', $wholesalers[$x]["email"]);
				$pt->setVar('ACTIVE', $wholesalers[$x]["active"]);
				$pt->parse('ROWS','row','true');
			}
		}else{
				$pt->setVar('WHOLESALER_ID', $wholesalers[$x]["wholesaler_id"]);
				$pt->setVar('COMPANY_NAME', $wholesalers[$x]["company_name"]);
				$pt->setVar('PHONE', $wholesalers[$x]["phone"]);
				$pt->setVar('EMAIL', $wholesalers[$x]["email"]);
				$pt->setVar('ACTIVE', $wholesalers[$x]["active"]);
				$pt->parse('ROWS','row','true');
		}
		$pt->setVar('SERVICES_'.$wholesalers[$x]["wholesaler_id"], rtrim($ws_services,', '));
	}
	$pt->parse('WHOLESALER_TABLE','wholesaler_table','true');
}

if ( $user->class == "admin" ) {
	$pt->parse("BACK_LINK","back_link","true");
}
	
// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

if ( isset($_SERVER['HTTP_REFERER']) ) {
	// if ($_SERVER['HTTP_REFERER'] == "http://localhost/") {;
	if ($_SERVER['HTTP_REFERER'] == "http://simplicity.xi.com.au/") {;
		$pt->parse("WELCOME_BANNER","welcome_banner","true");
	}
}

if ($user->class == 'admin') {
	$pt->parse("WEBPAGE", "outside1");
} else if ($user->class == 'customer') {
	$pt->parse("WEBPAGE", "outside2");
} else if ($user->class == 'reseller') {
	$pt->parse("WEBPAGE","outside3");
}


// Print out the page
$pt->p("WEBPAGE");

function services_count($customer_id,$type_id){

	$count = new services();
	$count->customer_id = $customer_id;
	$count->type_id = $type_id;
	
	return count($count->get_service_count());
}