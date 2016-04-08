<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/inbound_voice/distribute/index.php - Inbound Voice: Distribute
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../../setup.inc";
include "../../../../../doauth.inc";
include_once "customers.class";
include_once "service_temp.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/inbound_voice/distribute/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/inbound_voice/distribute/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/inbound_voice/distribute/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("sod" => "base/manage/services/add/inbound_voice/distribute/sod.html",
					"cd" => "base/manage/services/add/inbound_voice/distribute/cd.html"));

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
  exit();
}

if ( !isset($_REQUEST['sp']) || empty($_REQUEST['sp']) ) {
  echo "URL invalid";
  exit();
}

$session_pointer = $_REQUEST['customer_id'] . "_" . $_REQUEST['sp'];

$service_temp = new service_temp();
$service_temp->data_key = $session_pointer;
$service_temp->load();

$service_data = unserialize($service_temp->data);

$customer = new customers();
$customer->customer_id = $_REQUEST["customer_id"];
$customer->load();

if ( $user->class == 'customer' ) {
	if ( $customer->customer_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
} else if ( $user->class == 'reseller' ) {
	if ( $customer->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

if ( !isset($_REQUEST["distribution"]) ) {
	$pt->setVar("DISTRIBUTION_SOD"," checked");
	$pt->parse("FORM_PART2","sod","true");
}

if ( isset($_REQUEST["distribution"]) ) {
	if ( $_REQUEST["distribution"] == "standard_one_destination" ) {
		$pt->setVar("DISTRIBUTION_SOD"," checked");
		$pt->parse("FORM_PART2","sod","true");
	} else if ( $_REQUEST["distribution"] == "complex_distribution" ) {
		$pt->setVar("DISTRIBUTION_CD"," checked");
		$pt->parse("FORM_PART2","cd","true");
	}
}

if ( isset($_REQUEST["submit"]) ) {
	$error = 0;
	if ( isset($_REQUEST["distribution"]) && $_REQUEST["distribution"] == "standard_one_destination" ) {
		if ( empty($_REQUEST["dist_number"]) ) {
			$pt->setVar("ERROR_MSG","Error: Invalid Number.");
			$error = 1;
		}
		$length = strlen($_REQUEST["dist_number"]);
		if ( $length != 10 ) {
			$pt->setVar("ERROR_MSG","Error: Invalid Number.");
			$error = 1;
		}
		if ( $_REQUEST["dist_number"][0] != 0 ) {
			$pt->setVar("ERROR_MSG","Error: Invalid Number.");
			$error = 1;
		}
	} else if ( isset($_REQUEST["distribution"]) && $_REQUEST["distribution"] == "complex_distribution" ) {
		if ( empty($_REQUEST["dist_complex"]) ) {
			$pt->setVar("ERROR_MSG","Error: Provide an answer to 'How do you want the order get created?'");
			$error = 1;
		}
	}

	if ( $error == 0 ) {
		if ( !isset($_REQUEST["dist_number"]) ) {
			$dist_number = "";
		} else {
			$dist_number = $_REQUEST["dist_number"];
		}
		if ( !isset($_REQUEST["dist_complex"]) ) {
			$dist_complex = "";
		} else {
			$dist_complex = $_REQUEST["dist_complex"];
		}

		$service_data["inbound_voice"]["distribution"] = $_REQUEST["distribution"];
		$service_data["inbound_voice"]["sod"] = $dist_number;
		$service_data["inbound_voice"]["cd"] = $dist_complex;

		$service_temp->data = serialize($service_data);
		$service_temp->save();

		// Done, goto list
	    $url = "";
	        
	    if ( isset($_SERVER["HTTPS"]) ) {
	        
	      $url = "https://";
	          
	    } else {
	        
	      $url = "http://";
	    }

	      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/inbound_voice/distribute/creation/?customer_id=" . $customer->customer_id . "&sp=" . $_REQUEST['sp'];

	    header("Location: $url");
	    exit();
	}
}

if ( isset($_REQUEST["dist_number"]) ) {
	$pt->setVar("DIST_NUMBER",$_REQUEST["dist_number"]);
}

if ( isset($_REQUEST["dist_complex"]) ) {
	$pt->setVar("DIST_COMPLEX",$_REQUEST["dist_complex"]);
}

$pt->setVar("CUSTOMER_ID",$_REQUEST["customer_id"]);
$pt->setVar("SP",$_REQUEST['sp']);

$pt->setVar("PAGE_TITLE", "Inbound Voice - Distribution");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");