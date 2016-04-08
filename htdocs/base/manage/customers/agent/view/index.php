<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/customers/add/add_auth_rep/index.php - Add Authorised Representative
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

include_once "customers.class";
include_once "authorised_rep.class";
// include_once "secondary_ids.class";
include_once "requirement_documents.class";
include_once "wholesalers.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

//check if user(customer) is equal to user's access_id.
if ( !isset($_REQUEST['agent_id']) || empty($_REQUEST['agent_id']) ) {
	$_REQUEST['agent_id'] = $user->access_id;
}

//check if user is agent
$agent = new customers();
$agent->customer_id = $_REQUEST['agent_id'];
$agent->load();

if ( $agent->kind != 'agent' ) {
 echo "Agent ID is invalid";
 exit();
}

$pt->setFile(array("main" => "base/manage/customers/agent/view/index.html",
					"rows" => "base/manage/customers/agent/view/rows.html"));

//check if user has access to view agent record
if ( $user->class == 'reseller' ) {
	if ( $user->access_id != $agent->wholesaler_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
	$pt->setFile(array("outside" => "base/outside3.html"));
} else if ( $user->class == 'customer' ) {
	if ( $user->access_id != $agent->customer_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
	$pt->setFile(array("outside" => "base/outside2.html"));
} else {
	$pt->setFile(array("outside" => "base/outside1.html"));
}

$linked_customers = new customers();
$linked_customers->agent = $agent->customer_id;
$customer_list = $linked_customers->get_by_agent();

for ($i=0; $i < count($customer_list); $i++) { 
	$customer_name = "";
	if ( !empty($customer_list[$i]['company_name']) ) {
		$customer_name = $customer_list[$i]['company_name'];
	} else {
		$customer_name = $customer_list[$i]['first_name'] . " " . $customer_list[$i]['last_name'];
	}
	$pt->setVar("CUSTOMER_NAME",trim($customer_name));
	$pt->setVar("CREATOR",$customer_list[$i]['creator']);
	$pt->setVar("AGENT_CUSTOMER_ID",$customer_list[$i]['customer_id']);
	$pt->parse("ROWS","rows","true");
}

$pt->setVar("CUSTOMER_ID",$agent->customer_id);

$pt->setVar("PAGE_TITLE", "View Agent's Customers");		
// Parse the main page
$pt->parse("MAIN", "main");
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

