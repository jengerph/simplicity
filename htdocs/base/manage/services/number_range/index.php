<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/orders/edit/number_range/edit/number_range/index.php - Manage Number Range
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

include_once "orders.class";
include_once "services.class";
include_once "service_attributes.class";
include_once "order_attributes.class";
include_once "orders_states.class";
include_once "outbound_voice_fnn.class";

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
	
} else if ($user->class == 'reseller') {

	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/number_range/index.html"));
	
}

$pt->setFile(array("number_range_rows" => "base/manage/services/number_range/number_range_rows.html",
					"back_link_number_range" => "base/manage/services/number_range/back_link_number_range.html"));

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
	echo "Service ID invalid";
	exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$number_range = new outbound_voice_fnn();
$number_range->service_id = $service->service_id;
$number_range_list = $number_range->get_active();

$parent_service_attr = new service_attributes();
$parent_service_attr->service_id = $service->parent_service_id;
$parent_service_attr->param = "number_range";
$parent_service_attr->get_attribute();

//check if contain number range
if (strpos($parent_service_attr->value, 'yes_')!==FALSE) {
	$range = str_replace("yes_", "", $parent_service_attr->value);
} else {
	$range = 0;
}

$active_numbers = 0;

for ($a=0; $a < count($number_range_list); $a++) { 
	$active_numbers = $active_numbers + 1;
	$pt->setVar("FNN",$number_range_list[$a]["fnn"]);
	$pt->setVar("START",$number_range_list[$a]["start"]);
	$pt->setVar("STOP",$number_range_list[$a]["stop"]);
	$pt->parse("NUMBER_RANGE_ROWS","number_range_rows","true");
}

//check if user can order
$proceed = 1;

$check_orders = new orders();
$check_orders->service_id = $service->service_id;
$check_orders->get_latest_orders();

if ( $check_orders->status != "closed" && $check_orders->request_type == "number range" && $check_orders->action == "terminate" ) {
	$pt->setVar("ERROR_MSG","You can only process one cancel order at a time. Check current Cancel Order <a href='/base/manage/orders/edit/?order_id=".$check_orders->order_id."'>here</a>.");
    $proceed = 0;
}

if ( isset($_REQUEST["submit"]) ) {
	if ( $proceed == 1 ) {
		$order = new orders();
		$order->service_id = $service->service_id;
		$order->start = date("Y-m-d H:i:s");
		$order->request_type = "number range";
		$order->action = "terminate";
		$order->status = "pending";
		$order->create();

		$parent_service_attr = new service_attributes();
		$parent_service_attr->service_id = $service->service_id;
		$parent_service_attr->param = "start_number";
		$parent_service_attr->get_attribute();

		$order_attributes = new order_attributes();
		$order_attributes->order_id = $order->order_id;
		$order_attributes->param = "order_start";
		$order_attributes->value = $parent_service_attr->value;
		$order_attributes->datetime = date("Y-m-d H:i:s");
		$order_attributes->create();

		$parent_service_attr = new service_attributes();
		$parent_service_attr->service_id = $service->service_id;
		$parent_service_attr->param = "finish_number";
		$parent_service_attr->get_attribute();

		$order_attributes = new order_attributes();
		$order_attributes->order_id = $order->order_id;
		$order_attributes->param = "order_finish";
		$order_attributes->value = $parent_service_attr->value;
		$order_attributes->datetime = date("Y-m-d H:i:s");
		$order_attributes->create();

		$orders_states = new orders_states();
		$orders_states->order_id = $order->order_id;
		$orders_states->state_name = $order->status;
		$orders_states->create();

		$pt->setVar("SUCCESS_MSG","Successfully created a Termination of Number Range Order(<a href='/base/manage/orders/edit/?order_id=".$order->order_id."'>".$order->order_id."</a>).");
	}
}

if ( !isset($_SERVER['HTTP_REFERER']) ) {
	$_SERVER['HTTP_REFERER'] = "/base/manage/services/?service_id=" . $service->parent_service_id;
}

$pt->setVar("SERVICE_ID",$service->service_id);
$pt->setVar("TOTAL_NUMBER_RANGE",$range);
$pt->setVar("CURRENT_COUNT_RANGE",$active_numbers);
$pt->setVar("PREVIOUS_PAGE",$_SERVER['HTTP_REFERER']);
$pt->parse("BACK_LINK","back_link_number_range","true");
	
$pt->setVar("PAGE_TITLE", "Outbound Voice - Number Range");

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");
	
// Print out the page
$pt->p("WEBPAGE");

