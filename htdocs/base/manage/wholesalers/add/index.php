<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/add/index.php - Add Wholesaler
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
include_once "service_types.class";
include_once "wholesaler_service_types.class";


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
	
}

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html","outside2" => "base/outside2.html", "main" => "base/manage/wholesalers/add/index.html", "service_option" => "base/manage/wholesalers/service_option.html"));

$wholesalers = new wholesalers();
$wst = new wholesaler_service_types();


if (isset($_REQUEST['submit'])) {
	
	// Add new wholesalers
	$error_msg = '';

	$wholesalers->company_name = $_REQUEST['company_name'];
	$wholesalers->email = $_REQUEST['email'];
	$wholesalers->phone = $_REQUEST['phone'];
	$wholesalers->active = $_REQUEST['active'];
	$wholesalers->address1 = $_REQUEST['address1'];
	$wholesalers->address2 = $_REQUEST['address2'];
	$wholesalers->city = $_REQUEST['city'];
	$wholesalers->state = $_REQUEST['state'];
	$wholesalers->postcode = $_REQUEST['postcode'];
	$wholesalers->abn = $_REQUEST['abn'];
	$wholesalers->require_ar_download = isset($_REQUEST['req_doc_upload']) ? $_REQUEST['req_doc_upload'] : "no";
	$wholesalers->require_ar_idcheck = isset($_REQUEST['req_id_check']) ? $_REQUEST['req_id_check'] : "no";
	$wholesalers->customer_billing = $_REQUEST["customer_billing"];
	$wholesalers->manage_own_plan = $_REQUEST["manage_own_plan"];
	$wholesalers->block_customer_order_notif = $_REQUEST["block_customer_order_notif"];
	$wholesalers->allow_credit_card = $_REQUEST["allow_credit_card"];
			
	$vc = $wholesalers->validate();

	if ($vc != 0) {
	
		$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

	} else {

		
		$wholesalers->create();

		$service_types = new service_types();
		$st = $service_types->get_services();
		for ( $x = 0; $x < count($st); $x++ ) {
			$keys = "service_type_" . $st[$x]["type_id"];
			if ( isset($_REQUEST[$keys]) ) {
				$wst->type_id = $_REQUEST[$keys];
				$wholesalers->load();
				$wst->wholesaler_id = $wholesalers->wholesaler_id;
				$wst->create();
				$changes = new audit();
				$changes->wholesaler_id = $wholesalers->wholesaler_id;
				$changes->type_id = $_REQUEST[$keys];
				$changes->store_changes_wst('Added');
			}
		}

    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/";

    header("Location: $url");
    exit();		
  
	}
}

$pt->setVar('COMPANY_NAME', $wholesalers->company_name);
$pt->setVar('EMAIL', $wholesalers->email);
$pt->setVar('PHONE', $wholesalers->phone);
$pt->setVar('ADDRESS1', $wholesalers->address1);
$pt->setVar('ADDRESS2', $wholesalers->address2);
$pt->setVar('CITY', $wholesalers->city);
$pt->setVar('POSTCODE', $wholesalers->postcode);
$pt->setVar('ABN', $wholesalers->abn);
$pt->setVar('ACTIVE_' . strtoupper($wholesalers->active) . '_SELECT', ' checked');
$pt->setVar('STATE_' . strtoupper($wholesalers->state) . '_SELECT', ' selected');
$pt->setVar('REQ_DOC_UPLOAD_' . strtoupper($wholesalers->require_ar_download) . '_SELECT', ' checked');
$pt->setVar('REQ_ID_CHECK_' . strtoupper($wholesalers->require_ar_idcheck) . '_SELECT', ' checked');
$pt->setVar('CUSTOMER_BILLING_' . strtoupper($wholesalers->customer_billing) . '_SELECT', ' checked');
$pt->setVar('MANAGE_OWN_PLAN_' . strtoupper($wholesalers->manage_own_plan) . '_SELECT', ' checked');
$pt->setVar('BLOCK_CUSTOMER_ORDER_NOTIF_' . strtoupper($wholesalers->block_customer_order_notif) . '_SELECT', ' checked');
$pt->setVar('ALLOW_CREDIT_CARD_' . strtoupper($wholesalers->allow_credit_card) . '_SELECT', ' checked');

//Get a list of service_types
$service_types = new service_types();
$st = $service_types->get_services();
for ( $x = 0; $x < count($st); $x++ ) {
	if ( $st[$x]["active"] == "yes" ){
		$pt->setVar('TYPE_ID', $st[$x]["type_id"]);
		$pt->setVar('TYPE_DESCRIPTION', $st[$x]["description"]);
		$pt->parse('SERVICE_OPTION','service_option', true);
	}
}

$pt->setVar("PAGE_TITLE", "New Wholesaler");

		
// Parse the main page
$pt->parse("MAIN", "main");

// Correct outside
if ($user->class != 'customer' || $user->class != 'reseller') {
	$pt->parse("WEBPAGE", "outside1");
} else {
	$pt->parse("WEBPAGE", "outside2");
}	

// Print out the page
$pt->p("WEBPAGE");

