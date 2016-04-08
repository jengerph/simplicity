<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/customers/edit/index.php - Edit Customer
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

include_once "customers.class";
include_once "wholesalers.class";
include_once "authorised_rep.class";
include_once "services.class";


// $user = new user();
// $user->username = $_SESSION['username'];
// $user->load();

// if ($user->class == 'customer') {
// 	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
// 	// Parse the main page
// 	$pt->parse("MAIN", "main");
// 	$pt->parse("WEBPAGE", "outside");

// 	// Print out the page
// 	$pt->p("WEBPAGE");

// 	exit();
	
// }

$customers = new customers();

if (!isset($_REQUEST['customer_id'])) {
	
	echo "No Customer ID provided";
	exit();
	
}

$customers->customer_id = $_REQUEST['customer_id'];

if (!$customers->exist()) {
	
	echo "Customer does not exist";
	exit();
	
}

$customers->load();

$user = new user();
$user->username = $_SESSION['username'];
if (!$user->exist()) {
	
	echo "Username does not exist";
	exit(1);
	
}

$user->load();

// if ($user->class == 'customer') {
// 	echo "Access denied, this user has higher class than you.";
// 	exit();
// }
// Assign the templates to use
if ( $user->class == 'admin' ){
	$pt->setFile(array("outside1" => "base/outside1.html","outside2" => "base/outside2.html", "main" => "base/manage/customers/edit/index.html", "secondary_id_row" => "base/manage/customers/add/secondary_id_row.html"));
} else if ( $user->class == 'reseller' ){
	$pt->setFile(array("outside1" => "base/outside3.html","outside2" => "base/outside2.html", "main" => "base/manage/customers/edit/index.html", "secondary_id_row" => "base/manage/customers/add/secondary_id_row.html"));
} else if ( $user->class == 'customer' ){
	$pt->setFile(array("outside1" => "base/outside2.html","outside2" => "base/outside2.html", "main" => "base/manage/customers/edit/index.html", "secondary_id_row" => "base/manage/customers/add/secondary_id_row.html"));
}

$pt->setFile(array("abn_row" => "base/manage/customers/add/abn_row.html",
					"birthdate_row" => "base/manage/customers/add/birthdate_row.html",
					"kind_row_1" => "base/manage/customers/add/kind_row_1.html"));

$authorised_rep = new authorised_rep();

//check user if agent
$agent = new customers();
$agent->customer_id = $user->access_id;
$agent->load();

if ( $user->class == 'customer' ) {
	if ( $customers->customer_id != $user->access_id && $customers->agent != $agent->customer_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
} else if ( $user->class == 'reseller' ) {
	if ( $customers->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

if ( $customers->type == "company" ) {
	$pt->parse("ABN_ROW","abn_row","true");
} else if ( $customers->type == "person" ) {
	$pt->parse("BIRTHDATE_ROW","birthdate_row","true");
}

if (isset($_REQUEST['submit'])) {
	
	// Edit customers
	$error_msg = '';

	$customers->type = $_REQUEST['type'];
	$customers->company_name = $_REQUEST['company_name'];
	$customers->first_name = $_REQUEST['first_name'];
	$customers->last_name = $_REQUEST['last_name'];
	$customers->email = $_REQUEST['email'];
	$customers->mobile = $_REQUEST['mobile'];
	$customers->phone = $_REQUEST['phone'];
	$customers->fax = $_REQUEST['fax'];
	$customers->address1 = $_REQUEST['address1'];
	$customers->address2 = $_REQUEST['address2'];
	$customers->city = $_REQUEST['city'];
	$customers->postal_same = $_REQUEST['postal_same'];
	$customers->postcode = $_REQUEST['postcode'];
	$customers->postal_address1 = $_REQUEST['postal_address1'];
	$customers->postal_address2 = $_REQUEST['postal_address2'];
	$customers->postal_city = $_REQUEST['postal_city'];
	$customers->postal_postcode = $_REQUEST['postal_postcode'];
	$customers->type = $_REQUEST['type'];
	$customers->active = $_REQUEST['active'];
	$customers->state = $_REQUEST['state'];
	$customers->postal_state = $_REQUEST['postal_state'];
	$customers->wholesaler_id = $customers->wholesaler_id;
	$customers->kind = $_REQUEST["kind"];
	$customers->agent = (isset($_REQUEST["agent_id"])?$_REQUEST["agent_id"]:$customers->agent);

	if ( isset($_REQUEST['abn']) ) {
		$customers->abn = $_REQUEST['abn'];		
	}

	if ( isset($_REQUEST['birthdate']) ) {
		$birthdate = strtotime(str_replace('/', '.', $_REQUEST['birthdate']));
		$customers->birthdate = date('Y-m-d',$birthdate);
	}

	$vc = $customers->validate();

	if ($vc != 0) {
	
		$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

	} else {

		
		$customers->save();
		$customers->load();

    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/customers/?wholesaler_id=".$customer->wholesaler_id."&customer_id=".$customers->customer_id;

    header("Location: $url");
    exit();		
  
	}
}

$pt->setVar('CUSTOMER_ID', $customers->customer_id);
$pt->setVar('COMPANY_NAME', $customers->company_name);
$pt->setVar('FIRST_NAME', $customers->first_name);
$pt->setVar('LAST_NAME', $customers->last_name);
$pt->setVar('BIRTHDATE', date('d/m/Y',strtotime($customers->birthdate)));
$pt->setVar('ABN', $customers->abn);
$pt->setVar('EMAIL', $customers->email);
$pt->setVar('MOBILE', $customers->mobile);
$pt->setVar('PHONE', $customers->phone);
$pt->setVar('FAX', $customers->fax);
$pt->setVar('ADDRESS1', $customers->address1);
$pt->setVar('ADDRESS2', $customers->address2);
$pt->setVar('CITY', $customers->city);
$pt->setVar('POSTCODE', $customers->postcode);
$pt->setVar('POSTAL_ADDRESS1', $customers->postal_address1);
$pt->setVar('POSTAL_ADDRESS2', $customers->postal_address2);
$pt->setVar('POSTAL_CITY', $customers->postal_city);
$pt->setVar('POSTAL_POSTCODE', $customers->postal_postcode);
$pt->setVar('TYPE_' . strtoupper($customers->type) . '_SELECT', ' selected');
$pt->setVar('ACTIVE_' . strtoupper($customers->active) . '_SELECT', ' checked');
$pt->setVar('POSTAL_' . strtoupper($customers->postal_same) . '_SELECT', ' checked');
$pt->setVar('STATE_' . strtoupper($customers->state) . '_SELECT', ' selected');
$pt->setVar('POSTAL_STATE_' . strtoupper($customers->postal_state) . '_SELECT', ' selected');
$pt->setVar('KIND_' . strtoupper($customers->kind) . '_SELECT', ' selected');

$agent = new customers();
$agent->customer_id = $customers->agent;
$agent->load();

if ( empty($agent->first_name) && empty($agent->last_name) ) {
	$agent_name = $agent->company_name;
} else {
	$agent_name = ucwords($agent->first_name) . " " . ucwords($agent->last_name);
}

if ( $user->class == "admin" ) {
	$customer_agents = new customers();
	$customer_agents->wholesaler_id = $customers->wholesaler_id;
	$agent_arr = $customer_agents->get_agents_by_wholesalers();
	$agent_list = $customer_agents->agent_list("agent_id",$agent_arr);
	$agent_name = $agent_list;
}

$pt->setVar("AGENT",$agent_name);
$pt->setVar("AGENT_".$customers->agent."_SELECT", " selected");

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $customers->wholesaler_id;
$wholesaler->load();
$pt->setVar("WHOLESALER_COMPANY",$wholesaler->company_name);

if ( $user->class != "customer" ) {
	$pt->parse("KIND_ROW_1","kind_row_1","true");
}

$pt->setVar("PAGE_TITLE", "Edit Customer");
		
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

