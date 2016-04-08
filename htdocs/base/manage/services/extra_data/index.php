<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/extra_data/index.php - View Usage
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../setup.inc";

include "../../../doauth.inc";
include_once "services.class";
include_once "customers.class";
include_once "service_attributes.class";
include_once "service_billing_once_off.class";
include_once "plans.class";
include_once "plan_attributes.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

switch ($user->class) {
	case 'admin':
		$pt->setFile(array("outside" => "base/outside1.html"));		
		break;
	case 'reseller':
		$pt->setFile(array("outside" => "base/outside3.html"));	
		break;
	case 'customer':
		$pt->setFile(array("outside" => "base/outside2.html"));	
		break;
	
	default:
		# code...
		break;
}

if ( isset($_REQUEST["page"]) ) {
	$page = $_REQUEST["page"];
} else {
	$page = 1;
}

$pt->setVar("PAGE_TITLE", "View Summary");
// Assign the templates to use
$pt->setFile(array("main" => "base/manage/services/extra_data/index.html",
					"back_link_extra_data" => "base/manage/services/back_link/back_link_extra_data.html"));

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
	echo "Service ID invalid.";
	exit();
}

if ( !isset($_REQUEST["extra_data"]) ) {
	$_REQUEST["extra_data"] = 1;
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$customers = new customers();
$customers->customer_id = $service->customer_id;
$customers->load();

if ( $user->class == 'customer' ) {
	if ( $customers->customer_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
} else if ( $user->class == 'reseller' ) {
	if ( $customers->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

$main_plan = new plans();
$main_plan->plan_id = $service->retail_plan_id;
$main_plan->load();

$wholesale_plan = new plans();
$wholesale_plan->plan_id = $service->wholesale_plan_id;
$wholesale_plan->load();

$main_extra_data_cost = new plan_attributes();
$main_extra_data_cost->plan_id = $main_plan->plan_id;
$main_extra_data_cost->param = "extra_data_cost";
$main_extra_data_cost->get_latest();

$wholesale_extra_data_cost = new plan_attributes();
$wholesale_extra_data_cost->plan_id = $wholesale_plan->plan_id;
$wholesale_extra_data_cost->param = "extra_data_cost";
$wholesale_extra_data_cost->get_latest();

if ( isset($_REQUEST["submit"]) ) {
	if ( isset($_REQUEST["extra_data"]) && is_numeric($_REQUEST["extra_data"]) ) {

		$shape_status = new service_attributes();
		$shape_status->service_id = $service->service_id;
		$shape_status->param = "shape_status";
		$shape_status->value = "0";

		if ($shape_status->exist()) {
			$shape_status->save();
		} else {
			$shape_status->create();
		}

		$shape_extra_data = new service_attributes();
		$shape_extra_data->service_id = $service->service_id;
		$shape_extra_data->param = "shape_extra_data";
		$shape_extra_data->value = $_REQUEST["extra_data"];
		if ($shape_extra_data->exist()) {
			$shape_extra_data->save();
		} else {
			$shape_extra_data->create();
		}

		$shape_extra_data_dt = new service_attributes();
		$shape_extra_data_dt->service_id = $service->service_id;
		$shape_extra_data_dt->param = "shape_extra_data_dt";
		$dt = date('Y-m-d H:i:s');
		$shape_extra_data_dt->value = $dt;
		if ($shape_extra_data_dt->exist()) {
			$shape_extra_data_dt->save();
		} else {
			$shape_extra_data_dt->create();
		}

		$username = new service_attributes();
		$username->service_id = $service->service_id;
		$username->param = 'username';
		$username->get_attribute();

		$realms = new service_attributes();
		$realms->service_id = $service->service_id;
		$realms->param = 'realms';
		$realms->get_attribute();

		$query2 = "DELETE FROM radius.radreply WHERE username  = " . $username->db->quote($username->value . '@' . $realms->value) . " AND attribute='Cisco-Avpair'";
      		$result2 = $username->db->execute_query($query2);

		system("/var/www/simplicity/bin/unshape.pl " . $username->value . '@' . $realms->value. " > /dev/null");

		$service_billing_once_off = new service_billing_once_off();
		$service_billing_once_off->service_id = $service->service_id;
		$service_billing_once_off->description = $dt . " - Purchased " . $_REQUEST["extra_data"] . "GB extra data";
		$service_billing_once_off->main_unit_amount = $main_extra_data_cost->value;
		$service_billing_once_off->wholesale_unit_amount = $wholesale_extra_data_cost->value;
		$service_billing_once_off->gst = "yes";
		$service_billing_once_off->qty = $_REQUEST["extra_data"];
		$service_billing_once_off->item_type = "10";
		$service_billing_once_off->main_invoice_id = "1";
		$service_billing_once_off->main_item_id = "1";
		$service_billing_once_off->wholesale_invoice_id = "1";
		$service_billing_once_off->wholesale_item_id = "1";
		$service_billing_once_off->create();

		goto_billing();

	} else {
		$pt->setVar("ERROR_MSG","Error: Invalid amount of data.");
	}
}

$pt->setVar("SERVICE_ID",$service->service_id);
$pt->setVar("VALUE",$_REQUEST["extra_data"]);
$pt->parse("BACK_LINK","back_link_extra_data","true");

// Parse the main page
$pt->parse("MAIN", "main");
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function goto_billing(){
	// Done, goto list
	    $url = "";
	        
	    if ( isset($_SERVER["HTTPS"]) ) {
	        
	      $url = "https://";
	          
	    } else {
	        
	      $url = "http://";
	    }

	      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/billing/once_off/";

	    header("Location: $url");
	    exit();
}
