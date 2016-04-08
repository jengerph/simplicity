<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/service_stats/rebuild/index.php - View Service Statistics
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

include_once "services.class";
include_once "customers.class";
include_once "plans.class";
include_once "service_attributes.class";
include_once "../NWB-DSL-rebuildPort.php";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/service_stats/rebuild/index.html"));
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/service_stats/rebuild/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/service_stats/rebuild/index.html"));
	
}

$pt->setFile(array("rebuild_success" => "base/manage/services/service_stats/rebuild/success.html",
					"rebuild_fail" => "base/manage/services/service_stats/rebuild/fail.html",
					"rebuild_form" => "base/manage/services/service_stats/rebuild/rebuild_form.html",
					"service_stats_link" => "base/manage/services/back_link/back_link_service_stats.html"));

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
	echo "Service ID invalid.";
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

$service_attribute = new service_attributes();
$service_attribute->service_id = $service->service_id;
$service_attribute->param = "aapt_service_id";
$service_attribute->get_attribute();

$plan = new plans();
$plan->plan_id = $service->retail_plan_id;
$plan->load();

if ( isset($_REQUEST["submit"]) ) {

	$result = dsl_rebuild_port($service_attribute->value);

	if ( is_numeric($result->referenceNumber) ) {

		$pt->parse("SUCCESS_NOTICE","rebuild_success","true");

		header( "refresh:60; url=/base/manage/services/service_stats/?service_id=".$service->service_id ); 

	} else {

		$pt->parse("SUCCESS_NOTICE","rebuild_fail","true");

	}

} else {
	$pt->parse("REBUILD_FORM","rebuild_form","true");
}

$pt->setVar("SERVICE_ID",$service->service_id);

if ( preg_match("/telstra/i", strtolower($retail_plan->access_method)) == 0 ) {
	$pt->parse("SERVICE_STATS_LINK","service_stats_link","true");
}

$pt->setVar("PAGE_TITLE", "Rebuild Port");

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");
	
// Print out the page
$pt->p("WEBPAGE");

