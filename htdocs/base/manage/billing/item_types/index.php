<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/billing/index.php - View Billing
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

include_once "billing_invoice_item_types.class";

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


$pt->setVar("PAGE_TITLE", "View Invoice Item Type");

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html", 
					"main" => "base/manage/billing/item_types/index.html",
					"rows" => "base/manage/billing/item_types/rows.html"));

$item_types = new billing_invoice_item_types();
$item_types_arr = $item_types->get_all();

for ($a=0; $a < count($item_types_arr); $a++) { 
	$pt->setVar("ITEM_TYPE",$item_types_arr[$a]["item_type"]);
	$pt->setVar("DESCRIPTION",$item_types_arr[$a]["description"]);
	$pt->parse("ROWS","rows","true");
}

if ( isset($_REQUEST["submit"]) ) {

	$item_type = new billing_invoice_item_types();
	$item_type->description = $_REQUEST["description"];

	$validate = $item_type->validate();

	if ( $validate != 0 ) {
		$pt->setVar("ERROR_MSG","ERROR: " . $config->error_message[$validate]);
	} else {
		$item_type->create();

		//go to
		$url = "";
	          
	      if (isset($_SERVER["HTTPS"])) {
	          
	        $url = "https://";
	            
	      } else {
	          
	        $url = "http://";
	      }
	  
	      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/billing/item_types/";
	  
	      header("Location: $url");
	      exit();
	}

} else if ( isset($_REQUEST["delete"]) ) {
	$item_type = new billing_invoice_item_types();
	$item_type->item_type = $_REQUEST["delete"];
	$item_type->delete();

	//go to
		$url = "";
	          
	      if (isset($_SERVER["HTTPS"])) {
	          
	        $url = "https://";
	            
	      } else {
	          
	        $url = "http://";
	      }
	  
	      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/billing/item_types/";
	  
	      header("Location: $url");
	      exit();
}
	
// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

if ($user->class != 'customer' || $user->class != 'reseller') {
	$pt->parse("WEBPAGE", "outside1");
} else {
	$pt->parse("WEBPAGE", "outside2");
}	
	
// Print out the page
$pt->p("WEBPAGE");

