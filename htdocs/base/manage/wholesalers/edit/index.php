<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/add/index.php - Edit wholesaler
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
	$pt->setFile(array("outside1" => "base/outside3.html",
						"outside2" => "base/outside2.html",
						"main" => "base/manage/wholesalers/edit/index2.html"));
} else if ( $user->class == 'admin' ) {
	$pt->setFile(array("outside1" => "base/outside1.html",
						"outside2" => "base/outside2.html", 
						"main" => "base/manage/wholesalers/edit/index.html"));
}


$wholesaler = new wholesalers();

if (!isset($_REQUEST['wholesaler_id'])) {
	if ( $user->class == 'reseller' ) {
		$_REQUEST["wholesaler_id"] = $user->access_id;
	} else {
		echo "No Wholesaler ID provided";
		exit();
	}
	
}

$wholesaler->wholesaler_id = $_REQUEST['wholesaler_id'];

if (!$wholesaler->exist()) {
	
	echo "Wholesaler does not exist";
	exit(1);
	
}

if ( $user->class == 'reseller' && ($user->access_id != $wholesaler->wholesaler_id) ) {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
}

// Assign the templates to use
$pt->setFile(array("service_option" => "base/manage/wholesalers/service_option.html",
					"eway_configuration" => "base/manage/wholesalers/edit/eway_configuration.html"));

$wholesaler->load();
$wst = new wholesaler_service_types();

if (isset($_REQUEST['submit'])) {
	
	// Edit wholesaler
	$error_msg = '';

	$wholesaler->wholesaler_id = isset($_REQUEST['wholesaler_id']) ? $_REQUEST['wholesaler_id'] : $wholesaler->wholesaler_id;
	$wholesaler->company_name = isset($_REQUEST['company_name']) ? $_REQUEST['company_name'] : $wholesaler->company_name;
	$wholesaler->email = isset($_REQUEST['email']) ? $_REQUEST['email'] : $wholesaler->email;
	$wholesaler->phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : $wholesaler->phone;
	$wholesaler->active = isset($_REQUEST['active']) ? $_REQUEST['active'] : $wholesaler->active;
	$wholesaler->address1 = isset($_REQUEST['address1']) ? $_REQUEST['address1'] : $wholesaler->address1;
	$wholesaler->address2 = isset($_REQUEST['address2']) ? $_REQUEST['address2'] : $wholesaler->address2;
	$wholesaler->city = isset($_REQUEST['city']) ? $_REQUEST['city'] : $wholesaler->city;
	$wholesaler->state = isset($_REQUEST['state']) ? $_REQUEST['state'] : $wholesaler->state;
	$wholesaler->postcode = isset($_REQUEST['postcode']) ? $_REQUEST['postcode'] : $wholesaler->postcode;
	$wholesaler->abn = isset($_REQUEST['abn']) ? $_REQUEST['abn'] : $wholesaler->abn;
	$wholesaler->require_ar_download = isset($_REQUEST['req_doc_upload']) ? $_REQUEST['req_doc_upload'] : "no";
	$wholesaler->require_ar_idcheck = isset($_REQUEST['req_id_check']) ? $_REQUEST['req_id_check'] : "no";
	$wholesaler->customer_billing = $_REQUEST["customer_billing"];
	$wholesaler->manage_own_plan = $_REQUEST["manage_own_plan"];
	$wholesaler->block_customer_order_notif = $_REQUEST["block_customer_order_notif"];
	$wholesaler->allow_credit_card = $_REQUEST["allow_credit_card"];
	$wholesaler->bpay = $_REQUEST["bpay"];
	
	$vc = $wholesaler->validate();

	if ($vc != 0) {
	
		$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

	} else {
		$go = 1;
		if ($user->class == 'customer') {
				
				$pt->setVar('ERROR_MSG', 'Error: User classes can only be less than your access class');
				$go = 0;
		}
		
		if ($go == 1) {
			$wholesaler->save();

			$checked = array();
			$prev_services = array();

			//Saving to wholesaler_service_type starts here
			$service_types = new service_types();
			$wstypes = new wholesaler_service_types();
			$st = $service_types->get_services();

			//check which values are checked
			for ( $x = 0; $x < count($st); $x++ ) {
				$keys = "service_type_" . $st[$x]["type_id"];
				if ( isset($_REQUEST[$keys]) ){
					$checked[] = $st[$x]["type_id"];
				}
			}

			//Gets previous list of wholesaler_service_types
			$wstypes->wholesaler_id = $wholesaler->wholesaler_id;
			$prev_list = $wstypes->get_wholesaler_services();

			for ( $y = 0; $y < count($prev_list); $y++ ) {
					$prev_services[$y] = $prev_list[$y]["type_id"];
			}

			//create wholesaler_service_type if checked
			if ( $checked ) {
				for (  $z = 0; $z < count($checked); $z++  ) {
					$create_wst = new wholesaler_service_types();
					$create_wst->type_id = $checked[$z];
					$create_wst->wholesaler_id = $wholesaler->wholesaler_id;
					if ( !$create_wst->exist() ) {
						$create_wst->create();
						$changes = new audit();
						$changes->wholesaler_id = $wholesaler->wholesaler_id;
						$changes->type_id = $checked[$z];
						$changes->store_changes_wst('Added');
					}
				}
			}

			//delete wholesaler_service_type if not checked
			if ( $prev_services ) {
				for (  $a = 0; $a < count($prev_services); $a++  ) {
						if ( !in_array($prev_services[$a], $checked) ) {
							$delete_wst = new wholesaler_service_types();
							$delete_wst->type_id = $prev_services[$a];
							$delete_wst->wholesaler_id = $wholesaler->wholesaler_id;
							$delete_wst->delete();
							$changes = new audit();
							$changes->wholesaler_id = $wholesaler->wholesaler_id;
							$changes->type_id = $prev_services[$a];
							$changes->store_changes_wst('Deleted');
						}
				}
			}
			// Done, goto list
		    $url = "";
		        
		    if (isset($_SERVER["HTTPS"])) {
		        
		      $url = "https://";
		          
		    } else {
		        
		      $url = "http://";
		    }

		    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/?wholesaler_id=".$wholesaler->wholesaler_id;

		    header("Location: $url");
		    exit();	
				}
  
	}
}

if ( $wholesaler->allow_credit_card == "yes" ) {
	$pt->parse("EWAY_CONFIGURATION","eway_configuration","true");
}

$pt->setVar('WS_ID', $wholesaler->wholesaler_id);
$pt->setVar('COMPANY_NAME', $wholesaler->company_name);
$pt->setVar('EMAIL', $wholesaler->email);
$pt->setVar('PHONE', $wholesaler->phone);
$pt->setVar('ADDRESS1', $wholesaler->address1);
$pt->setVar('ADDRESS2', $wholesaler->address2);
$pt->setVar('CITY', $wholesaler->city);
$pt->setVar('POSTCODE', $wholesaler->postcode);
$pt->setVar('ABN', $wholesaler->abn);
$pt->setVar('BPAY', $wholesaler->bpay);
$pt->setVar('ACTIVE',ucfirst($wholesaler->active));
$pt->setVar('STATE',$wholesaler->state);
$pt->setVar('ACTIVE_' . strtoupper($wholesaler->active) . '_SELECT', ' checked');
$pt->setVar('STATE_' . strtoupper($wholesaler->state) . '_SELECT', ' selected');
$pt->setVar('REQ_DOC_UPLOAD_' . strtoupper($wholesaler->require_ar_download) . '_SELECT', ' checked');
$pt->setVar('REQ_ID_CHECK_' . strtoupper($wholesaler->require_ar_idcheck) . '_SELECT', ' checked');
$pt->setVar('CUSTOMER_BILLING_' . strtoupper($wholesaler->customer_billing) . '_SELECT', ' checked');
$pt->setVar('MANAGE_OWN_PLAN_' . strtoupper($wholesaler->manage_own_plan) . '_SELECT', ' checked');
$pt->setVar('BLOCK_CUSTOMER_ORDER_NOTIF_' . strtoupper($wholesaler->block_customer_order_notif) . '_SELECT', ' checked');
$pt->setVar('ALLOW_CREDIT_CARD_' . strtoupper($wholesaler->allow_credit_card) . '_SELECT', ' checked');

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

$wst->wholesaler_id = $wholesaler->wholesaler_id;
$services = $wst->get_wholesaler_services();

$services_str = "";

if ( $services ) {
	for ( $x = 0; $x < count($services); $x++ ) {
		$pt->setVar('TYPE_CHECK'.$services[$x]["type_id"], ' checked');
		$service_desc = new service_types();
		$service_desc->type_id = $services[$x]["type_id"];
		$service_desc->load();
		$services_str .= $service_desc->description . ", ";
	}
}

$pt->setVar("SERVICES",rtrim($services_str,', '));

$pt->setVar("PAGE_TITLE", "Edit Wholesaler");

		
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

