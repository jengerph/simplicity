<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/agents/index.php - View Agents
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

include_once "wholesalers.class";
include_once "customers.class";
include_once "services.class";
include_once "service_types.class";

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


$pt->setVar("PAGE_TITLE", "Manage Agents");

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html", 
					"outside2" => "base/outside3.html", 
					"main" => "base/manage/wholesalers/agents/index.html",
					"rows" => "base/manage/wholesalers/agents/rows.html",
					"customers_list" => "base/manage/wholesalers/agents/customers_list.html",
					"customers_li" => "base/manage/wholesalers/agents/customers_li.html"));

if ( $user->class == 'reseller' ){
	$_REQUEST["wholesaler_id"] = $user->access_id;
}

if ( !isset($_REQUEST["wholesaler_id"]) || empty($_REQUEST["wholesaler_id"]) ) {
	$_REQUEST["wholesaler_id"] = $user->access_id;
}

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $_REQUEST["wholesaler_id"];

$customers = new customers();
$customers_list = $customers->get_customers($wholesaler->wholesaler_id);

for ($i=0; $i < count($customers_list); $i++) { 
	// $services_key = "";
	if ( $customers_list[$i]['kind'] == 'agent' ) {
			$pt->setVar("AGENT_NAME",ucwords($customers_list[$i]['company_name']));
		if ( empty($customers_list[$i]['company_name']) ) {
			$pt->setVar("AGENT_NAME",ucwords($customers_list[$i]['first_name']) . " " . ucwords($customers_list[$i]['last_name']));
		}

		//get agent's customers
		$agents_customers = new customers();
		$agents_customers->agent = $customers_list[$i]["customer_id"];
		$agents_customers_arr = $agents_customers->get_by_agent();

		$pt->clearVar("CUSTOMERS_LI");
		$pt->clearVar("CUSTOMERS_LIST");
		for ($j=0; $j < count($agents_customers_arr); $j++) { 
			if ( empty($agents_customers_arr[$j]['first_name']) && empty($agents_customers_arr[$j]['last_name']) ) {
				$agents_customer_name = ucwords($agents_customers_arr[$j]['first_name']) . " " . $agents_customers_arr[$j]['last_name'];
			} else {
				$agents_customer_name = ucwords($agents_customers_arr[$j]['company_name']);
			}
			$pt->setVar("CUSTOMER_NAME",$agents_customer_name);
			$pt->setVar("AGENTS_CUSTOMER_ID",$agents_customers_arr[$j]['customer_id']);
			$pt->parse("CUSTOMERS_LI","customers_li","true");
		}

		$pt->setVar("CUSTOMER_ID",$customers_list[$i]['customer_id']);
		$pt->setVar("CREATOR",$customers_list[$i]['creator']);
		$pt->parse("CUSTOMERS_LIST","customers_list","true");
		$pt->parse("ROWS","rows","true");
	}
}

$pt->setVar("WHOLESALER_ID",$wholesaler->wholesaler_id);
$pt->parse("MAIN", "main");

if ($user->class == 'admin') {
	$pt->parse("WEBPAGE", "outside1");
} else if ($user->class == 'customer') {
	$pt->parse("WEBPAGE", "outside");
} else if ($user->class == 'reseller') {
	$pt->parse("WEBPAGE","outside2");
}


// Print out the page
$pt->p("WEBPAGE");