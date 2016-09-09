<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/xero/index.php - Link wholesaler to xero
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../setup.inc";

include_once "wholesalers.class";
require_once "xero.inc";

$wholesaler = new wholesalers();

if (isset($_REQUEST['oauth_token'])) {
	
	
	$wholesaler->xero_oauth_token = $_REQUEST['oauth_token'];
	
	 
	if (!$wholesaler->exist_xero_oauth_token()) {
	  // Error - invalid token
   	echo "Error: Token is not valid - " . $_REQUEST['oauth_token'] . "\n";
   	exit();
	}
	if (!$wholesaler->load_xero_oauth_token()) {
		echo "Error loading wholesaler details";
		exit();
	}
		
	$_REQUEST["wholesaler_id"]  = $wholesaler->wholesaler_id;
	
	
  $XeroOAuth->config['access_token']        = $wholesaler->xero_oauth_token;
  $XeroOAuth->config['access_token_secret'] = $wholesaler->xero_oauth_token_secret;
  
  $code = $XeroOAuth->request('GET', $XeroOAuth->url('AccessToken', ''), array(
      'oauth_verifier' => $_REQUEST['oauth_verifier'],
      'oauth_token' => $_REQUEST['oauth_token']
  ));
      
  if ($XeroOAuth->response['code'] == 200) {

		$response = $XeroOAuth->extract_params($XeroOAuth->response['response']);
    $wholesaler->xero_access_token       = $response['oauth_token'];
    $wholesaler->xero_access_token_secret = $response['oauth_token_secret'];
    $wholesaler->xero_session_handle = $response['oauth_session_handle'];
    if (!$wholesaler->save()) {
    	echo "Error updating wholesaler";
    	exit();
    }

  	$XeroOAuth->config['access_token']        = $wholesaler->xero_access_token;
  	$XeroOAuth->config['access_token_secret'] = $wholesaler->xero_access_token_secret;
  	$XeroOAuth->config['session_handle'] 			= $wholesaler->xero_session_handle;
        

    $response = $XeroOAuth->request('GET', $XeroOAuth->url('Organisation', 'core'), array('page' => 0));
    if ($XeroOAuth->response['code'] == 200) {
      $organisation = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);

      
      $wholesaler->xero_name = $organisation->Organisations[0]->Organisation->Name;
      $wholesaler->xero_apikey = $organisation->Organisations[0]->Organisation->APIKey;
      
      if (!$wholesaler->save()) {
      	echo "Error saving xero file details to wholesaler";
      	exit();
      }
      
      
    } else {
      xero_outputError($XeroOAuth); 
    }
          
  } else {
    xero_outputError($XeroOAuth); 
	}
  	    			 
	
}
include "../../../../doauth.inc";



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
	$pt->setFile(array("outside1" => "base/outside3.html","outside2" => "base/outside2.html", ));
} else if ( $user->class == 'admin' ) {
	$pt->setFile(array("outside1" => "base/outside1.html","outside2" => "base/outside2.html", ));
}

if ( !isset($_REQUEST["wholesaler_id"]) || $_REQUEST["wholesaler_id"] == "") {
	echo "Invalid Wholesaler ID.";
	exit(1);
}


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

$wholesaler->load();

if ($_REQUEST['connect']) {
	
	// Lets link up this wholesaler
 	
	$params = array(
    'oauth_callback' => XeroOAuth::php_self ()
  );


  $response = $XeroOAuth->request('GET', $XeroOAuth->url('RequestToken', ''), $params);
 	if ($XeroOAuth->response['code'] == 200) {
    //$scope = 'payroll.payrollcalendars,payroll.superfunds,payroll.payruns,payroll.payslip,payroll.employees,payroll.TaxDeclaration';
    if($_REQUEST['authenticate']>1) $scope = 'payroll.employees,payroll.payruns';
    
    $oauth = $XeroOAuth->extract_params($XeroOAuth->response['response']);
    //print_r( $oauth);

    
    $authurl = $XeroOAuth->url("Authorize", '') . "?oauth_token={$oauth['oauth_token']}&scope=" . $scope;
    
    
    
		$wholesaler->xero_oauth_token = $oauth['oauth_token'];
		$wholesaler->xero_oauth_token_secret = $oauth['oauth_token_secret'];
    $wholesaler->save();
        
		header("Location: $authurl");
		
		//echo '<p>To complete the OAuth flow follow this URL: <a href="' . $authurl . '">' . $authurl . '</a></p>';
		 
		exit();

	} else {
    xero_outputError($XeroOAuth);
	}    
}	

// Assign the templates to use
$pt->setFile(array("main" => "base/manage/wholesalers/edit/xero/index.html"));


$pt->setVar('WHOLESALER_ID', $wholesaler->wholesaler_id);
$pt->setVar('COMPANY_NAME', $wholesaler->company_name);
$pt->setVar('XERO_NAME', $wholesaler->xero_name);
$pt->setVar('XERO_APIKEY', $wholesaler->xero_apikey);
$pt->setVar('XERO_ACCESS_TOKEN', $wholesaler->xero_access_token);
$pt->setVar('XERO_ACCESS_TOKEN_SECRET', $wholesaler->xero_access_token_secret);


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
