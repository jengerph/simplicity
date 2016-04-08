<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/inbound_voice/index.php - Edit Inbound Voice: Distribute
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
include_once "services.class";
include_once "service_attributes.class";
include_once "orders.class";
include_once "order_attributes.class";
include_once "postcodes.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/inbound_voice/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/inbound_voice/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/inbound_voice/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("sod" => "base/manage/services/edit/inbound_voice/sod.html",
					"cd" => "base/manage/services/edit/inbound_voice/cd.html"));

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
  echo "Invalid Service ID.";
  exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$order = new orders();
$order->service_id = $service->service_id;
$order->get_closed();

$order_attributes = new order_attributes();
$order_attributes->order_id = $order->order_id;
$order_attr_arr = $order_attributes->get_order_attributes();

if ( isset($_REQUEST["distribution_btn"]) || isset($_REQUEST["distribution"]) ) {

	if ( $_REQUEST["distribution"] == "standard_one_destination" ) {
		$pt->setVar("DISTRIBUTION_SOD" , " checked");
		$pt->parse("FORM_PART2","sod","true");
	} else if ( $_REQUEST["distribution"] == "complex_distribution" ) {
		$pt->setVar("DISTRIBUTION_CD" , " checked");
		$pt->parse("FORM_PART2","cd","true");
	}

	for ($a=0; $a < count($order_attr_arr); $a++) { 
		if ( $order_attr_arr[$a]["param"] == "order_sod" ) {
			if ( !empty($order_attr_arr[$a]["value"]) ) {
				$pt->setVar("DIST_NUMBER" , $order_attr_arr[$a]["value"]);
			}
		}
		if ( $order_attr_arr[$a]["param"] == "order_cd" ) {
			if ( !empty($order_attr_arr[$a]["value"]) ) {
				$pt->setVar("DIST_COMPLEX" , $order_attr_arr[$a]["value"]);
			}
		}
	}

} else {

	for ($a=0; $a < count($order_attr_arr); $a++) { 
		if ( $order_attr_arr[$a]["param"] == "order_sod" ) {
			if ( !empty($order_attr_arr[$a]["value"]) ) {
				$pt->setVar("DISTRIBUTION_SOD" , " checked");
				$pt->setVar("DIST_NUMBER" , $order_attr_arr[$a]["value"]);
				$pt->parse("FORM_PART2","sod","true");
			}
		}
		if ( $order_attr_arr[$a]["param"] == "order_cd" ) {
			if ( !empty($order_attr_arr[$a]["value"]) ) {
				$pt->setVar("DISTRIBUTION_CD" , " checked");
				$pt->setVar("DIST_COMPLEX" , $order_attr_arr[$a]["value"]);
				$pt->parse("FORM_PART2","cd","true");
			}
		}
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

		$_SESSION["inbound_voice"]["distribution"] = $_REQUEST["distribution"];
		$_SESSION["inbound_voice"]["sod"] = $dist_number;
		$_SESSION["inbound_voice"]["cd"] = $dist_complex;

		// Done, goto list
	    $url = "";
	        
	    if ( isset($_SERVER["HTTPS"]) ) {
	        
	      $url = "https://";
	          
	    } else {
	        
	      $url = "http://";
	    }

	      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/inbound_voice/creation/?service_id=" . $service->service_id;

	    header("Location: $url");
	    exit();
	}

}

$pt->setVar("SERVICE_ID",$_REQUEST["service_id"]);

$pt->setVar("PAGE_TITLE", "Edit - Call Distribution");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");