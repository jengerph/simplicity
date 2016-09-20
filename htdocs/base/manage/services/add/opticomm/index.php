<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/adsl_nbn2/index.php - Qualify Order
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
include_once "config.class";
include_once "customers.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/opticomm/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/opticomm/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/opticomm/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("fnn_section" => "base/manage/services/add/opticomm/fnn_yes.html"));

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
  exit(1);
}

$cust = new customers();
$cust->customer_id = $_REQUEST["customer_id"];

if (!$cust->exist()) {
	
	echo "Invalid Customer ID";
	exit();
}

$cust->load();

$pt->setVar('CUSTOMER_ID', $cust->customer_id);

if ( $user->class == 'customer' ) {
  if ( $cust->customer_id != $user->access_id ) {
    $pt->setFile(array("main" => "base/accessdenied.html"));
  }
} else if ( $user->class == 'reseller' ) {
  if ( $cust->wholesaler_id != $user->access_id ) {
    $pt->setFile(array("main" => "base/accessdenied.html"));
  }
}

if ( empty($cust->type) ) {
  echo "Customer ID does not exist.";
  exit(1);
}

if (!isset($_REQUEST['manual'])) {
	$_REQUEST['manual'] = 'no';
}

$pt->setVar('MANUAL_' . strtoupper($_REQUEST['manual']) . '_SELECT', ' checked');

if (isset($_REQUEST['fnn_service'])) {
	
	// Option selected
	if ( $_REQUEST["fnn_service"] == "yes" ) {
  	$pt->setFile(array("fnn_section" => "base/manage/services/add/opticomm/fnn_yes.html"));
  	$pt->setVar('SERVICE_NUMBER', $_REQUEST['service_number']);

  	$pt->setVar('FNN_SERVICE_YES', ' checked');

	} else if ( $_REQUEST["fnn_service"] == "nbn" ) {
  	$pt->setFile(array("fnn_section" => "base/manage/services/add/opticomm/nbn_yes.html"));
  	$pt->setVar('NBN_LOCATIONID', $_REQUEST['nbn_locationid']);

  	$pt->setVar('FNN_SERVICE_NBN', ' checked');
  	
  } else {
    $pt->setFile(array("fnn_section" => "base/manage/services/add/opticomm/fnn_no.html"));
  	$pt->setVar('FNN_SERVICE_NO', ' checked');

  	
  	$pt->setVar('ADDRESS_ENTRY', $_REQUEST['address_entry']);
  	$pt->setVar('UNIT_LEVEL', $_REQUEST['unit_level']);
  	$pt->setVar('STREET_NUMBER', $_REQUEST['street_number']);
  	$pt->setVar('STREET_NAME', $_REQUEST['street_name']);
  	$pt->setVar('SUBURB', $_REQUEST['suburb']);
  	$pt->setVar('STATE_', strtoupper($_REQUEST['state']) . "_SELECT", ' selected');
  	$pt->setVar('POST_CODE', $_REQUEST['post_code']);
  	$pt->setVar('COUNTRY', $_REQUEST['country']);

  }	
  
  $pt->parse('FNN_SECTION', 'fnn_section');
	
}

if (isset($_REQUEST['submit2'])) {
	
	$qual_id=md5(time());
	if (isset($_REQUEST['location_id'])) {
		
		// Location has been selected
  	$_SESSION['qual_' . $qual_id] = array();
  	$_SESSION['qual_' . $qual_id]['customer_id']=$cust->customer_id;
   	$_SESSION['qual_' . $qual_id]['provider']='Telstra';
  	$_SESSION['qual_' . $qual_id]['type']='location';
  	$_SESSION['qual_' . $qual_id]['location_id']=$_REQUEST['location_id'];
  	$_SESSION['qual_' . $qual_id]['manual']=$_REQUEST['manual'];
  	
  	
  	// Single result, redirect to qual page
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/opticomm/qual/?qual_id=" . $qual_id;

    header("Location: $url");
	  exit();
		
	}
	
	// Begin Qualification

		
   if (isset($_REQUEST['nbn_locationid'])) {
  	
  	$_SESSION['qual_' . $qual_id] = array();
  	$_SESSION['qual_' . $qual_id]['customer_id']=$cust->customer_id;
   	$_SESSION['qual_' . $qual_id]['provider']='NBN';
  	$_SESSION['qual_' . $qual_id]['type']='location';
  	$_SESSION['qual_' . $qual_id]['location_id']= $_REQUEST['nbn_locationid'];
  	$_SESSION['qual_' . $qual_id]['manual']=$_REQUEST['manual'];
  	
  	
  	// Single result, redirect to qual page
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/opticomm/qual/?qual_id=" . $qual_id;

    header("Location: $url");
	  exit(); 
  
  } else if (isset($_REQUEST['service_number'])) {
  	
  	$_SESSION['qual_' . $qual_id] = array();
  	$_SESSION['qual_' . $qual_id]['customer_id']=$cust->customer_id;
  	$_SESSION['qual_' . $qual_id]['type']='fnn';
  	$_SESSION['qual_' . $qual_id]['fnn']= $_REQUEST['service_number'];
   	$_SESSION['qual_' . $qual_id]['manual']=$_REQUEST['manual'];
 	
  	
  	// Single result, redirect to qual page
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/opticomm/qual/?qual_id=" . $qual_id;

    header("Location: $url");
	  exit();
  	
  } else {

  	// Address lookup, more tricky

       //ini_set("display_errors", 1);

       require_once dirname(__FILE__) . "/../../../../../external/xisoap/includes/FactoryXiSoap.php";
       $client = new \XiSoap\FactoryXiSoap();

       $param = array(
           "lot_no" => ($_POST["lot_no"]) ?: "",
           "unit_no" => ($_POST["unit_no"]) ?: "",
           "house_no" => ($_POST["street_number"]) ?: "",
           "street_type" => ($_POST["street_type"]) ?: "",
           "street_name" => ($_POST["street_name"]) ?: "",
           "suburb" => ($_POST["suburb"]) ?: "",
           "state_name" => ($_POST["state"]) ?: "",
           "postcode" => ($_POST["post_code"]) ?: ""
       );

       $param["street_name"] = substr($param["street_name"], 0, strrpos($param["street_name"], " "));

       $results = $client->getResults(dirname(__FILE__) . "/../../../../../external/xisoap/service_qual.wsdl", "AddressSearch", $param);

       //TODO: check if soap returns only 1 address or multiple

    if (sizeof($result) == 1) {

    	$_SESSION['qual_' . $qual_id] = array();
    	$_SESSION['qual_' . $qual_id]['customer_id']=$cust->customer_id;
    	$_SESSION['qual_' . $qual_id]['type']='location';
    	$_SESSION['qual_' . $qual_id]['provider']='Telstra';
    	$_SESSION['qual_' . $qual_id]['location_id']=$response->serviceProviderLocationList->serviceProviderLocationList->locationList->addressInformation->locationId;
	  	$_SESSION['qual_' . $qual_id]['manual']=$_REQUEST['manual'];


    	// Single result, redirect to qual page
      $url = "";
          
      if ( isset($_SERVER["HTTPS"]) ) {
          
        $url = "https://";
            
      } else {
          
        $url = "http://";
      }

      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/adsl_nbn2/qual/?qual_id=" . $qual_id;

	    header("Location: $url");
  	  exit();


    } else {

			// We have a list - display it
			$pt->setFile(array("location_id" => "base/manage/services/add/adsl_nbn2/location_id.html", "location_id_row" => "base/manage/services/add/adsl_nbn2/location_id_row.html"));
			
			while ($cel = each($response->serviceProviderLocationList->serviceProviderLocationList->locationList->addressInformation)) {
				
				$pt->setVar('LOCATION_ID', $cel['value']->locationId);
				$pt->setVar('LOCATION_TEXT', $cel['value']->displayAddress);
				
				$pt->parse('LOCATION_ROWS', 'location_id_row', true);
			}
			
			$pt->parse('LOCATION_TABLE', 'location_id');			
 	
    }    
    
    //print_r($params2);
  }
  
  print_r($params);
}

$pt->setVar("PAGE_TITLE", "Qualify a Service");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function soapDebug($client){

    $requestHeaders = $client->__getLastRequestHeaders();
    $request = prettyXml($client->__getLastRequest());
    $responseHeaders = $client->__getLastResponseHeaders();
    $response = prettyXml($client->__getLastResponse());

    echo '<code>' . nl2br(htmlspecialchars($requestHeaders, true)) . '</code>';
    echo highlight_string($request, true) . "<br/>\n";

    echo '<code>' . nl2br(htmlspecialchars($responseHeaders, true)) . '</code>' . "<br/>\n";
    echo highlight_string($response, true) . "<br/>\n";
}

function prettyXML($xml, $debug=false) {
  // add marker linefeeds to aid the pretty-tokeniser
  // adds a linefeed between all tag-end boundaries
  $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

  // now pretty it up (indent the tags)
  $tok = strtok($xml, "\n");
  $formatted = ''; // holds pretty version as it is built
  $pad = 0; // initial indent
  $matches = array(); // returns from preg_matches()

  /* pre- and post- adjustments to the padding indent are made, so changes can be applied to
   * the current line or subsequent lines, or both
  */
  while($tok !== false) { // scan each line and adjust indent based on opening/closing tags

    // test for the various tag states
    if (preg_match('/.+<\/\w[^>]*>$/', $tok, $matches)) { // open and closing tags on same line
      if($debug) echo " =$tok= ";
      $indent=0; // no change
    }
    else if (preg_match('/^<\/\w/', $tok, $matches)) { // closing tag
      if($debug) echo " -$tok- ";
      $pad--; //  outdent now
    }
    else if (preg_match('/^<\w[^>]*[^\/]>.*$/', $tok, $matches)) { // opening tag
      if($debug) echo " +$tok+ ";
      $indent=1; // don't pad this one, only subsequent tags
    }
    else {
      if($debug) echo " !$tok! ";
      $indent = 0; // no indentation needed
    }

    // pad the line with the required number of leading spaces
    $prettyLine = str_pad($tok, strlen($tok)+$pad, ' ', STR_PAD_LEFT);
    $formatted .= $prettyLine . "\n"; // add to the cumulative result, with linefeed
    $tok = strtok("\n"); // get the next token
    $pad += $indent; // update the pad size for subsequent lines
  }
  return $formatted; // pretty format
}
