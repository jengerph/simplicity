<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/delete/index.php - Delete Services
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
include_once "service_types.class";
include_once "service_attributes.class";
include_once "plans.class";
include_once "orders.class";
include_once "order_attributes.class";
include_once "radius.class";
include_once "customers.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/delete/index.html"));
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/delete/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/delete/index.html"));
	
}


if ( !isset($_REQUEST["service_id"]) || $_REQUEST["service_id"] == "") {
	echo "Service ID invalid.";
	exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$service_type = new service_types();
$service_type->type_id = $service->type_id;
$service_type->load();

$customer = new customers();
$customer->customer_id = $service->customer_id;
$customer->load();

$err = 0;

//check user if allowed to delete
if ( $user->class == 'customer' ) {
	if ( $user->access_id != $customer->customer_id ) {
		$pt->setVar("ERROR_MSG","Error: You cannot delete this service.");
		$err = $err + 1;
	}
} else if ( $user->class == 'reseller' ) {
	if ( $user->access_id != $customer->wholesaler_id ) {
		$pt->setVar("ERROR_MSG","Error: You cannot delete this service.");
		$err = $err + 1;
	}
}

if ( isset($_REQUEST["submit"]) ) {
	if ( $err == 0 ) {
		$service_attributes = new service_attributes();
		$service_attributes->service_id = $service->service_id;
		$service_attributes->delete();

		$orders = new orders();
		$orders->service_id = $service->service_id;
		$orders->open_load();

		$username = "";

		if (isset($orders->order_id)) {
		$order_attributes = new order_attributes();
		$order_attributes->order_id = $orders->order_id;

		$order_attributes->param = "order_username";
		$order_attributes->get_attribute();
		$username = $order_attributes->value;

		$order_attributes2 = new order_attributes();
		$order_attributes2->order_id = $orders->order_id;
		$order_attributes2->param = "order_realms";
		$order_attributes2->get_attribute();	
		
		$username .= "@" . $order_attributes2->value;

		$order_attributes->delete();
		}

		$orders->delete();

		$radcheck = new radius();
		$radcheck->username = $username;
		$radcheck->delete();

		$service->delete();


		// Done, goto list
		$url = "";
		    
		if (isset($_SERVER["HTTPS"])) {
		    
		  $url = "https://";
		      
		} else {
		    
		  $url = "http://";
		}

		  $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/customers/index.php?customer_id=" . $customer->customer_id;

		header("Location: $url");
		exit();
	}

} else if ( isset($_REQUEST["cancel"]) ) {
	// Done, goto list
	$url = "";
	    
	if (isset($_SERVER["HTTPS"])) {
	    
	  $url = "https://";
	      
	} else {
	    
	  $url = "http://";
	}

	  $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/?service_id=" . $service->service_id;

	header("Location: $url");
	exit();
}

$retail_plan = new plans();
$retail_plan->plan_id = $service->retail_plan_id;
$retail_plan->load();

$pt->setVar("SERVICE_ID",$service->service_id);
$pt->setVar("SERVICE_TYPE",$service_type->description);
$pt->setVar("RETAIL_PLAN",$retail_plan->description);
$con_start = strtotime($service->start_date);
$new_con_start = date("d/m/Y",$con_start);
$pt->setVar("START_DATE", $new_con_start);
$con_end = strtotime($service->contract_end);
$new_con_end = date("d/m/Y",$con_end);
$pt->setVar("CONTRACT_END", $new_con_end);
$pt->setVar("IDENTIFIER",$service->identifier);
$pt->setVar("TAG",$service->tag);

$pt->setVar("PAGE_TITLE", "Delete Service");

		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

