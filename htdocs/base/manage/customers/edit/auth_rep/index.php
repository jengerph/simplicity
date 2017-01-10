<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/customers/edit/edit_auth_rep/index.php - Manage Authorised Representative
// $Id: 22edd549feffe7eb4cdc1332150bfd896d929a21 $
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


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

// if ($user->class == 'customer') {
// 	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
// 	// Parse the main page
// 	$pt->parse("MAIN", "main");
// 	$pt->parse("WEBPAGE", "outside");

// 	// Print out the page
// 	$pt->p("WEBPAGE");

// 	exit();
	
// }

if ( !isset($_REQUEST["customer_id"]) ) {
	echo "Customer ID invalid";
	exit();
}

$customer = new customers();
$customer->customer_id = $_REQUEST["customer_id"];
$customer->load();

// Assign the templates to use
if ( $user->class == 'admin' ) {
	$pt->setFile(array("outside1" => "base/outside1.html",
						"outside2" => "base/outside2.html"));
} else if ( $user->class == 'reseller' ) {
	$pt->setFile(array("outside1" => "base/outside3.html",
						"outside2" => "base/outside2.html"));
} else if ( $user->class == 'customer' ) {
	$pt->setFile(array("outside1" => "base/outside2.html",
						"outside2" => "base/outside2.html"));
}

$pt->setFile(array("main" => "base/manage/customers/edit/auth_rep/index.html", 
					"rows" => "base/manage/customers/edit/auth_rep/rows.html"));

if ( $user->class == 'customer' ) {

	//check user if agent
	$agent = new customers();
	$agent->customer_id = $user->access_id;
	$agent->load();

	if ( $customer->customer_id != $user->access_id && $customer->agent != $agent->customer_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
} else if ( $user->class == 'reseller' ) {
	if ( $customer->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

$authorised_reps = new authorised_rep();
$authorised_reps->customer_id = $customer->customer_id;
$auth_rep_arr = $authorised_reps->get_contacts();
$ar=0;
$dr=0;

for ($a=0; $a < count($auth_rep_arr); $a++) { 

	$auth_rep_name = ucfirst(strtolower($auth_rep_arr[$a]["title"])) . ". " . $auth_rep_arr[$a]["first_name"] . " " . $auth_rep_arr[$a]["surname"];

	$pt->setVar("AUTHORISED_REP_ID",$auth_rep_arr[$a]["id"]);
	$pt->setVar("AUTHORISED_REP",$auth_rep_name);

	if ($auth_rep_arr[$a]["auth_rep_active"] == 'yes') {
		$pt->setVar("ACTIVE_INACTIVE",'Deactivate');
		$pt->parse("ROWS_ACTIVATED","rows","true");
		$ar++;
	} else {
		$pt->setVar("ACTIVE_INACTIVE",'Activate'); 
		$pt->parse("ROWS_DEACTIVATED","rows","true");
		$dr++;
	}

}

if ($ar != 0) {
	$pt->setVar("ACTIVATED_REPRESENTATIVES_BANNER",'Authorised &nbspRepresentatives:');
} else {
	$pt->setVar("ACTIVATED_REPRESENTATIVES_BANNER",'Authorised &nbspRepresentatives:');
}	
if ($dr != 0) {
	$pt->setVar("DEACTIVATED_REPRESENTATIVES_BANNER",'Deactivated Representatives:');
} else {
	$pt->setVar("DEACTIVATED_REPRESENTATIVES_BANNER",'');
}	


$pt->setVar("CUSTOMER_ID",$customer->customer_id);

// Parse the main page
$pt->parse("MAIN", "main");

// Correct outside
if ($user->class != 'customer') {
	$pt->parse("WEBPAGE", "outside1");
} else {
	$pt->parse("WEBPAGE", "outside2");
}	

// Print out the page
$pt->p("WEBPAGE");

