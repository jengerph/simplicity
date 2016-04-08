<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/billing/once_off/index.php - View Billing
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

include_once "services.class";
include_once "service_billing_once_off.class";
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
  
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/billing/once_off/add/index.html"));
  
} else if ($user->class == 'admin') {
  
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/billing/once_off/add/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("back_link_once_off" => "base/manage/services/billing/back_link/back_link_once_off.html"));

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
  echo "Serivce ID Invalid.";
  exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$service_billing_once_off = new service_billing_once_off();

if ( !isset($service_billing_once_off->gst) ) {
  $service_billing_once_off->gst = 'yes';
}

if ( isset($_REQUEST["submit"]) ) {
  $service_billing_once_off->service_id = $service->service_id;
  $service_billing_once_off->description = $_REQUEST["description"];
  $service_billing_once_off->main_unit_amount = $_REQUEST["main_unit_amt"];
  $service_billing_once_off->wholesale_unit_amount = $_REQUEST["wholesale_unit_amt"];
  $service_billing_once_off->gst = $_REQUEST["gst"];
  $service_billing_once_off->qty = $_REQUEST["qty"];
  $service_billing_once_off->item_type = $_REQUEST["item_type"];
  $service_billing_once_off->main_invoice_id = '1';
  $service_billing_once_off->main_item_id = '1';
  $service_billing_once_off->wholesale_invoice_id = '1';
  $service_billing_once_off->wholesale_item_id = '1';

  $validate = $service_billing_once_off->validate();

  if ( $validate != 0 ) {
    $pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$validate]);
  } else {
    $service_billing_once_off->create();

    //email part here
    switch ($user->class) {
      case 'admin':
        # code...
        break;
      
      case 'reseller':
        # code...
        break;

      default:
        # code...
        break;
    }

    //go to
    $url = "";
            
        if (isset($_SERVER["HTTPS"])) {
            
          $url = "https://";
              
        } else {
            
          $url = "http://";
        }
    
        $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/billing/once_off/?service_id=".$service->service_id;
    
        header("Location: $url");
        exit();
  }

}

$item_types = new billing_invoice_item_types();
$item_types_arr = $item_types->get_all();

$item_type_list = $item_types->item_types_list("item_type",$item_types_arr);

$pt->setVar( "SERVICE_ID", $service->service_id );
$pt->setVar( "DESCRIPTION", $service_billing_once_off->description );
$pt->setVar( "MAIN_UNIT_AMT", $service_billing_once_off->main_unit_amount );
$pt->setVar( "WHOLESALE_UNIT_AMT", $service_billing_once_off->wholesale_unit_amount );
$pt->setVar( "GST_" . strtoupper($service_billing_once_off->gst), " checked" );
$pt->setVar( "QTY", $service_billing_once_off->qty );
$pt->setVar( "ITEM_TYPE", $item_type_list );
$pt->setVar( "IT_" . $service_billing_once_off->item_type . "_SELECT", " selected" );
$pt->parse( "BACK_LINK", "back_link_once_off", "true" );

$pt->setVar("PAGE_TITLE", "View Invoice Item Type");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");