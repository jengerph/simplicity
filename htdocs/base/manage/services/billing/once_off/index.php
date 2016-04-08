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
include_once "../../../../../setup.inc";
include "../../../../doauth.inc";

include_once "services.class";
include_once "service_billing_once_off.class";
include_once "billing_invoice_item_types.class";
include_once "misc.class";

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
  
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/billing/once_off/index.html"));
  
} else if ($user->class == 'admin') {
  
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/billing/once_off/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("back_link_service" => "base/manage/services/billing/back_link/back_link_service.html",
                    "back_link_add_once_off" => "base/manage/services/billing/back_link/back_link_add_once_off.html",
                    "row" => "base/manage/services/billing/once_off/row.html"));

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
  echo "Serivce ID Invalid.";
  exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$service_billing_once_off = new service_billing_once_off();
$service_billing_once_off->service_id = $service->service_id;
$once_off_arr = $service_billing_once_off->get_all();

for ($a=0; $a < count($once_off_arr); $a++) { 

  $date_nice = new misc();

  $pt->setVar("ITEM_ID",$once_off_arr[$a]["item_id"]);
  $pt->setVar("DESCRIPTION",$once_off_arr[$a]["description"]);
  $pt->setVar("MAIN_UNIT_AMT","$".$once_off_arr[$a]["main_unit_amount"]);
  $pt->setVar("WHOLESALE_UNIT_AMT","$".$once_off_arr[$a]["wholesale_unit_amount"]);
  $pt->setVar("GST",$once_off_arr[$a]["gst"]);
  $pt->setVar("QTY",$once_off_arr[$a]["qty"]);
  $pt->setVar("DATE",$date_nice->date_nice($once_off_arr[$a]["ts"]));
  $pt->parse("ROWS","row","true");
}

$pt->setVar( "SERVICE_ID", $service->service_id );
$pt->parse( "BACK_LINK", "back_link_service", "true" );
$pt->parse( "ONCE_OFF_LINK", "back_link_add_once_off", "true" );

$pt->setVar("PAGE_TITLE", "View Invoice Item Type");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");