<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/link_credit_card/index.php - View Payment
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../setup.inc";

include "../../doauth.inc";
include_once "wholesalers.class";
include_once "customers.class";
include_once "tokens_wholesalers.class";
include_once "tokens_customers.class";
include_once "eway/lib/eWAY/RapidAPI.php";

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


$pt->setVar("PAGE_TITLE", "Link Credit Card");

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html", 
					"outside2" => "base/outside2.html", 
					"outside3" => "base/outside3.html", 
					"main" => "base/manage/link_credit_card/index.html",
					"wholesaler_link" => "base/manage/link_credit_card/wholesaler_link.html",
					"customer_link" => "base/manage/link_credit_card/customer_link.html",
					"back_link" => "base/manage/link_credit_card/back_link.html",
					"back_link2" => "base/manage/link_credit_card/back_link2.html",
					"customer_link2" => "base/manage/link_credit_card/customer_link2.html"));

if ( $user->class == 'reseller' && $_REQUEST['request'] == "wholesaler"){
	$_REQUEST["wholesaler_id"] = $user->access_id;
} else if ( $user->class == 'customer' && $_REQUEST['request'] == "customer") {
	$_REQUEST["customer_id"] = $user->access_id;
}

if ( isset($_REQUEST['wholesaler_id']) && isset($_REQUEST['customer_id']) ) {
	echo "URL invalid.";
	exit();
}

//check if mismatch
if ( $_REQUEST["request"] == "customer" && !isset($_REQUEST["customer_id"]) ) {
	echo "URL invalid.";
	exit();
} else if ( $_REQUEST["request"] == "wholesaler" && !isset($_REQUEST["wholesaler_id"]) ) {
	echo "URL invalid.";
	exit();
}

$wholesaler = new wholesalers();
if ( $user->class == 'reseller' || $user->class == 'admin' ) {
	$wholesaler->wholesaler_id = (isset($_REQUEST["wholesaler_id"])?$_REQUEST["wholesaler_id"]:"");
}
$wholesaler->load();

$customer = new customers();
if ( $user->class == 'customer' || $user->class == 'admin' ) {
	$customer->customer_id = (isset($_REQUEST["customer_id"])?$_REQUEST["customer_id"]:"");
}
$customer->load();

if ( $user->class == 'reseller' && ($user->access_id != $wholesaler->wholesaler_id) ) {

	$wholesaler->wholesaler_id = $user->access_id;
	$wholesaler->load();

} else if ( $user->class == 'customer' && ($user->access_id != $customer->customer_id) ) {

	$customer->customer_id = $user->access_id;
	$customer->load();

}

//check customer if allowed to link credit_card
if ( $customer->wholesaler_id ) {
	$check_credit_setting = new wholesalers();
	$check_credit_setting->wholesaler_id = $customer->wholesaler_id;
	$check_credit_setting->load();

	if ( $check_credit_setting->allow_credit_card == "no" ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

$tokens_wholesalers = new tokens_wholesalers();
$tokens_wholesalers->wholesaler_id = $wholesaler->wholesaler_id;
$tokens_wholesalers->load();

$tokens_customers = new tokens_customers();
$tokens_customers->customer_id = $customer->customer_id;
$tokens_customers->load();

$tokens_api = new tokens_wholesalers();
$tokens_api->wholesaler_id = (isset($customer->wholesaler_id) ? $customer->wholesaler_id : $wholesaler->wholesaler_id);
$tokens_api->load();

if ( $_REQUEST["request"] == "wholesaler" ) {
	$pt->parse("BACK_LINK","back_link","true");
	$pt->parse("WHOLESALER_LINK","wholesaler_link","true");
	$pt->setVar("CONFIGURATION_TITLE","Eway Configuration");
}

if ( $_REQUEST["request"] == "customer" ) {
	$pt->parse("BACK_LINK","back_link2","true");
	$pt->parse("WHOLESALER_LINK","customer_link","true");
	$pt->setVar("CONFIGURATION_TITLE","Direct Debit Configuration");
}
$pt->setVar("WHOLESALER_ID",$wholesaler->wholesaler_id);
$pt->setVar("CUSTOMER_ID",(isset($_REQUEST["customer_id"])?$_REQUEST["customer_id"]:""));

if ( isset($_REQUEST["wholesaler_submit"]) ) {
	$tokens_wholesalers->api_key = trim($_REQUEST["api_key"]);
	$tokens_wholesalers->password = $_REQUEST["api_password"];

	 if ( empty($tokens_wholesalers->api_key) ) {
		$pt->setVar("ERROR_MSG","Error: API Key must not be empty.");
	} else if ( empty($tokens_wholesalers->password) ) {
		$pt->setVar("ERROR_MSG","Error: Password must not be empty.");
	} else {
		$tokens_wholesalers->create();
		$pt->setVar("SUCCESS_MSG","Successfully saved.");
	}

} else if ( isset($_REQUEST["create_token"]) ) {

	if ( empty($_REQUEST['first_name']) || empty($_REQUEST['last_name']) ) {
		$pt->setVar("ERROR_MSG","Error: First Name and Last Name must not be empty.");
	} else if ( empty($tokens_api->api_key) || empty($tokens_api->password) ) {
		$pt->setVar("ERROR_MSG","Error: Wholesaler hasn't setup an eWay API.");
	} else {

		$request = new eWAY\CreateAccessCodeRequest();

		$request->Customer->Reference = '';
	    $request->Customer->Title = $_POST['title'];
	    $request->Customer->FirstName = $_POST['first_name'];
	    $request->Customer->LastName = $_POST['last_name'];
	    $request->Customer->Country = 'au';

	    $request->RedirectUrl = 'http://simplicity.xi.com.au/base/manage/link_credit_card/index.php?customer_id=174&request=customer';
	    $request->Method = 'CreateTokenCustomer';
	    $request->TransactionType = 'Purchase';

	    // Call RapidAPI
	    $eway_params = array();
	    // $eway_params['sandbox'] = true;//sandbox
	    $service = new eWAY\RapidAPI($tokens_api->api_key, $tokens_api->password, $eway_params);
	    $result = $service->CreateAccessCode($request);

		$pt->setVar("FORMACTIONURL",$result->FormActionURL);
		$pt->setVar("ACCESSCODE",$result->AccessCode);
		$pt->setVar("CARDNAME",$_REQUEST['first_name'] . " " . $_REQUEST['last_name']);
		$pt->clearVar("WHOLESALER_LINK");
		$pt->parse("WHOLESALER_LINK","customer_link2","true");

	}

} else if ( isset($_REQUEST["update_token"]) ) {

	if ( empty($_REQUEST['first_name']) || empty($_REQUEST['last_name']) ) {
		$pt->setVar("ERROR_MSG","Error: First Name and Last Name must not be empty.");
	} else if ( empty($tokens_api->api_key) || empty($tokens_api->password) ) {
		$pt->setVar("ERROR_MSG","Error: Wholesaler hasn't setup an eWay API.");
	} else {

		$request = new eWAY\CreateAccessCodeRequest();

		$request->Customer->Reference = '';
		$request->Customer->TokenCustomerID = $tokens_customers->token_id;
	    $request->Customer->Title = $_POST['title'];
	    $request->Customer->FirstName = $_POST['first_name'];
	    $request->Customer->LastName = $_POST['last_name'];
	    $request->Customer->Country = 'au';

	    $request->RedirectUrl = 'http://simplicity.xi.com.au/base/manage/link_credit_card/index.php?customer_id=174&request=customer';
	    $request->Method = 'UpdateTokenCustomer';
	    $request->TransactionType = 'Purchase';

	    // Call RapidAPI
	    $eway_params = array();
	    // $eway_params['sandbox'] = true; //sandbox
	    $service = new eWAY\RapidAPI($tokens_api->api_key, $tokens_api->password, $eway_params);
	    $result = $service->CreateAccessCode($request);

		$pt->setVar("FORMACTIONURL",$result->FormActionURL);
		$pt->setVar("ACCESSCODE",$result->AccessCode);
		$pt->setVar("CARDNAME",$_REQUEST['first_name'] . " " . $_REQUEST['last_name']);
		$pt->clearVar("WHOLESALER_LINK");
		$pt->parse("WHOLESALER_LINK","customer_link2","true");

	}

}

if ( isset($_REQUEST['AccessCode']) && !empty($_REQUEST["AccessCode"]) ) {

	// Call RapidAPI
    $eway_params = array();
    // $eway_params['sandbox'] = true; //sandbox
    $service = new eWAY\RapidAPI($tokens_api->api_key, $tokens_api->password, $eway_params);

    $request = new eWAY\GetAccessCodeResultRequest();
    $request->AccessCode = $_REQUEST['AccessCode'];
    $result = $service->GetAccessCodeResult($request);

	if ( $result->ResponseMessage == 'A2000' || $result->ResponseMessage == 'A2008' || $result->ResponseMessage == 'A2010' || $result->ResponseMessage == 'A2011' || $result->ResponseMessage == 'A2016' ) {
		$tokenID = $result->TokenCustomerID;

		if ( $tokenID ) {
			$tokens_customers->token_id = $tokenID;
			$tokens_customers->create();
			$pt->setVar("SUCCESS_MSG","Successfully saved Token ID.");
		} else {
			
		}
	} else {
		if ( $result->Message ) {
			$pt->setVar("ERROR_MSG","Error: " . $result->Message);
		} else {
			// Get Error Messages from Error Code.
		    $ErrorArray = explode(",", $result->ResponseMessage);
		    $lblError = "";
		    foreach ( $ErrorArray as $error ) {
		        $error = $service->getMessage($error);
		        $lblError .= $error . ". ";
		    }
		    $pt->setVar("ERROR_MSG","Error: " . $lblError);
		}
	}
}

$pt->setVar("API_KEY",$tokens_wholesalers->api_key);
$pt->setVar("API_PASSWORD",$tokens_wholesalers->password);
$pt->setVar("TOKEN_ID",$tokens_customers->token_id);
$pt->setVar("FIRST_NAME",(isset($_REQUEST["first_name"])?$_REQUEST["first_name"]:""));
$pt->setVar("LAST_NAME",(isset($_REQUEST["last_name"])?$_REQUEST["last_name"]:""));
$pt->setVar("AMOUNT",(isset($_REQUEST["amount"])?$_REQUEST["amount"]:""));
$pt->setVar("TITLE_".(isset($_REQUEST["title"])?str_replace(".", "", $_REQUEST["title"]):""), " selected");

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

if ($user->class == 'admin') {
	$pt->parse("WEBPAGE", "outside1");
} else if ($user->class == 'reseller') {
	$pt->parse("WEBPAGE", "outside3");
} else if ($user->class == 'customer') {
	$pt->parse("WEBPAGE","outside2");
}


// Print out the page
$pt->p("WEBPAGE");