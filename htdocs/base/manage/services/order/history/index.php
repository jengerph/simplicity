<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/orders/history/index.php - View order history
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../setup.inc";

include "../../../../doauth.inc";

include_once "orders.class";
include_once "services.class";
include_once "customers.class";
include_once "misc.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/order/history/index.html"));
	
} else if ($user->class == 'reseller') {

	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/order/history/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/order/history/index.html"));
	
}

$pt->setFile(array("rows" => "base/manage/services/order/history/row.html"));

if ( !isset($_REQUEST["service_id"]) ) {
	echo "Invalid Service ID.";
	exit();
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

$orders = new orders();
$orders->service_id = $service->service_id;
$orders_arr = $orders->get_all_closed();

for ($a=0; $a < count($orders_arr); $a++) { 

	$date = new misc();

	$pt->setVar("ORDER_ID",$orders_arr[$a]["order_id"]);
	$pt->setVar("STATUS",ucfirst($orders_arr[$a]["status"]));
	$pt->setVar("REQUEST_TYPE",strtoupper($orders_arr[$a]["request_type"]));
	$pt->setVar("ACTION",ucfirst($orders_arr[$a]["action"]));
	$pt->setVar("DATE_REQUESTED",$date->date_nice($orders_arr[$a]["start"]));
	$pt->parse("ROWS","rows","true");
}

$pt->setVar("SERVICE_ID",$service->service_id);
	
$pt->setVar("PAGE_TITLE", "Order History");

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");
	
// Print out the page
$pt->p("WEBPAGE");

