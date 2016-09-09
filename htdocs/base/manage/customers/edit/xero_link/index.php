<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/customers/edit/xero_link/index.php - Link to xero
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
include_once "wholesalers.class";
require_once "xero.inc";

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

$pt->setFile(array("main" => "base/manage/customers/edit/xero_link/index.html"));

$wholesalers = new wholesalers();
$wholesalers->wholesaler_id = $customer->wholesaler_id;
$wholesalers->load();

if ($wholesalers->xero_name == '') {
	echo "Error: Xero is not linked for this wholesaler";
	exit();
}

$pt->setVar("CUSTOMER_ID",$customer->customer_id);
$pt->setVar("TYPE",$customer->type);
$pt->setVar("COMPANY_NAME",$customer->company_name);
$pt->setVar("FIRST_NAME",$customer->first_name);
$pt->setVar("LAST_NAME",$customer->last_name);

if ($_REQUEST['cmd'] == 'link') {
	
	if (isset($_REQUEST['submit'])) {
		
		if ($_REQUEST['xero_contact_id'] != '') {
			
			$customer->xero_contactid = $_REQUEST['xero_contact_id'];
			$customer->save();
			$pt->setVar("XERO_CONTACTID",$customer->xero_contactid);
			
		}
		
	} else {
	
		// Build list
		xero_setup($XeroOAuth, $customer->wholesaler_id);
		


		if ($customer->type == 'person') {
		 	$response = $XeroOAuth->request('GET', $XeroOAuth->url('Contacts', 'core'), array('page' => 0,'where' => 'LastName=="' . $customer->last_name . '"'));
  		
		} else {
		 	$response = $XeroOAuth->request('GET', $XeroOAuth->url('Contacts', 'core'), array('page' => 0,'where' => 'Name=="' . $customer->company_name . '"'));
  		
		}


    if ($XeroOAuth->response['code'] == 200) {
      $contacts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);		


			//echo count($contacts->Contacts->Contact);
			$i = 0;
			$list = '';
			while ($i < count($contacts->Contacts->Contact)) {
				
				$list .= '<option value="' . $contacts->Contacts->Contact[$i]->ContactID . '"';
				
				if ($contacts->Contacts->Contact[$i]->ContactID == $customer->xero_contactid) {
					$list .= ' selected';
				}
				
				$list .= '>' . $contacts->Contacts->Contact[$i]->Name . '</option>';
								
				$i++;
				
			}    
			
			$pt->setVar('XERO_CONTACT_LIST', $list);

      //print_r($contacts->Contacts);
      
      
     	//} else {
     		
      	//echo $contacts->Contacts->Contact->FirstName . '<br>';
      //}
    } else {
       xero_outputError($XeroOAuth); 
    }      

		$pt->setFile(array("main" => "base/manage/customers/edit/xero_link/index-link.html"));

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
      
		
	}
}



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

