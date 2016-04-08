<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/move/index.php - Move Services
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
include_once "wholesalers.class";


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
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/move/index.html"));
	
}

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
	echo "Service ID Invalid.";
	exit();
}

// Assign the templates to use
$pt->setFile(array("service_stats_link" => "base/manage/services/edit/service_stats_link.html",
					"move_service_link" => "base/manage/services/edit/move_service_link.html"));


$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$pt->setVar("SERVICE_ID",$service->service_id);

$customer = new customers();
$customer->customer_id = $service->customer_id;
$customer->load();

if ( $user->class == "reseller" ) {
	//check if customer belongs to wholesaler
	if ( $customer->wholesaler_id != $user->access_id ) {
		//access denied for wholesalers that doesn't link to this service
		$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
		// Parse the main page
		$pt->parse("MAIN", "main");
		$pt->parse("WEBPAGE", "outside");

		// Print out the page
		$pt->p("WEBPAGE");

		exit();
	}
}

$plan = new plans();
$plan->plan_id = $service->retail_plan_id;
$plan->load();

if ( preg_match("/telstra/i", strtolower($plan->access_method)) == 0 ) {
	$pt->parse("SERVICE_STATS_LINK","service_stats_link","true");
}

if ( isset($_REQUEST["customer"]) ) {
	if ( isset($_REQUEST["new_service_owner"]) && $_REQUEST["new_service_owner"] != 0 ) {
		$service->customer_id = $_REQUEST["new_service_owner"];
		$vc = $service->validate();

		if ($vc != 0) {
		
			$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

		} else {
			$service->save();

			$pt->setVar("SUCCESS_MSG","New Service Owner Saved.");
			// Done, goto order
			// $url = "";

			// if (isset($_SERVER["HTTPS"])) {
			// 	$url = "https://";
			// } else {
			// 	$url = "http://";
			// }
			// 	$url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/?service_id=" . $services->service_id;

			// header("Location: $url");
			// exit();
		}
	} else {
		$pt->setVar("ERROR_MSG","Error: Invalid Customer.");
	}
}

$customer_arr = $customer->get_customers();
$customer_list = $customer->customers_list2("new_service_owner",$customer_arr);

$plan = new plans();
$plan->plan_id = $service->retail_plan_id;
$plan->load();

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $plan->wholesaler_id;
$wholesaler->load();

if ( isset($_REQUEST["submit"]) ) {
	if ( isset($_REQUEST["new_wholesaler"]) && $_REQUEST["new_wholesaler"] != 0 ) {

		$plan_list = new plans();
		$plan_list->wholesaler_id = $_REQUEST["new_wholesaler"];
		$plan_arr = $plan_list->get_wholesaler_plans();

		$plans = $plan_list->plans_list("new_plan",$plan_arr);

		$pt->setVar("PLAN_LIST",$plans);

			$pt->setVar("SUCCESS_MSG","Select a new plan to continue with the moving of service to the new Wholesaler.");
			// Done, goto order
			// $url = "";

			// if (isset($_SERVER["HTTPS"])) {
			// 	$url = "https://";
			// } else {
			// 	$url = "http://";
			// }
			// 	$url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/?service_id=" . $services->service_id;

			// header("Location: $url");
			// exit();
	} else {
		$pt->setVar("ERROR_MSG","Error: Invalid Wholesaler.");
	}
}

if ( isset($_REQUEST["plan"]) ) {
	if ( isset($_REQUEST["new_plan"]) && $_REQUEST["new_plan"] != 0 ) {

		$new_plan = new plans();
		$new_plan->plan_id = $_REQUEST["new_plan"];
		$new_plan->load();

		$service->wholesale_plan_id = $new_plan->parent_plan_id;
		$service->retail_plan_id = $new_plan->plan_id;

		$vc = $service->validate();

		if ($vc != 0) {
		
			$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

		} else {
			$service->save();

			$customer->wholesaler_id = $new_plan->wholesaler_id;
			$customer->save();

			$pt->setVar("SUCCESS_MSG","New Service Owner Saved.");
			// Done, goto order
			// $url = "";

			// if (isset($_SERVER["HTTPS"])) {
			// 	$url = "https://";
			// } else {
			// 	$url = "http://";
			// }
			// 	$url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/?service_id=" . $services->service_id;

			// header("Location: $url");
			// exit();
		}
	} else {
		$pt->setVar("ERROR_MSG","Error: Invalid Wholesaler.");
	}
}

$wholesaler_arr = $wholesaler->get_wholesalers();
$wholesaler_list = $wholesaler->wholesalers_list2("new_wholesaler",$wholesaler_arr);

$pt->setVar("CURRENT_WHOLESALER", $wholesaler->company_name );
$pt->setVar("CURRENT_OWNER", ucwords($customer->company_name) . " - " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) );
$pt->setVar("CUSTOMER_LIST", $customer_list);
$pt->setVar("WHOLESALER_LIST", $wholesaler_list);

if ( isset($_REQUEST["new_service_owner"]) ) {
	$pt->setVar("CS_" . $_REQUEST["new_service_owner"] . "_SELECT", " selected");
}

if ( isset($_REQUEST["new_wholesaler"]) ) {
	$pt->setVar("WS_" . $_REQUEST["new_wholesaler"] . "_SELECT", " selected");
}

$pt->setVar("PAGE_TITLE", "Move Service");

		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

