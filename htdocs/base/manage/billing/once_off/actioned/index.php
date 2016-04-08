<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/billing/once_off/index.php - View Billing: Actioned
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
include_once "services.class";
include_once "service_billing_once_off.class";
include_once "service_types.class";
include_once "wholesalers.class";
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
  
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/billing/once_off/actioned/index.html"));
  
} else if ($user->class == 'admin') {
  
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/billing/once_off/actioned/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("back_link_service" => "base/manage/services/billing/back_link/back_link_service.html",
                    "back_link_add_once_off" => "base/manage/services/billing/back_link/back_link_add_once_off.html",
                    "row" => "base/manage/billing/once_off/row.html"));

if ( !isset($_REQUEST["page"]) ) {
  $current_page = 1;
} else {
  if ( is_numeric($_REQUEST["page"]) ) {
    $current_page = $_REQUEST["page"];
  } else {
    $current_page = 1;
  }
}

$service_billing_once_off = new service_billing_once_off();
$total_entries = $service_billing_once_off->get_num_rows_wua($user->class,$user->access_id);

$total_pages = ceil($total_entries/10);

if ( $total_pages <= 0 ) {
  $total_pages = 1;
}

if ( $current_page > $total_pages ) {
  $current_page = $total_pages;
}
if ( $current_page == $total_pages ) {
  $next_page = $current_page;
  $previous_page = $current_page - 1;
} else if ( $current_page < $total_pages ) {
  $next_page = $current_page + 1;
  $previous_page = $current_page - 1;
}
if ( $previous_page <= 0 ) {
  $previous_page = $current_page;
}

$start = ($current_page - 1) * 10;
$end = 10;

$once_off_arr = $service_billing_once_off->get_wua($user->class,$user->access_id,$start,$end);

for ($a=0; $a < count($once_off_arr); $a++) { 

  $date_nice = new misc();
  $service = new services();
  $service_type = new service_types();
  $customer = new customers();
  $wholesaler = new wholesalers();

  $service->service_id = $once_off_arr[$a]["service_id"];
  $service->load();

  $service_type->type_id = $service->type_id;
  $service_type->load();

  $customer->customer_id = $service->customer_id;
  $customer->load();

  $wholesaler->wholesaler_id = $customer->wholesaler_id;
  $wholesaler->load();

  if ( $user->class == "reseller" && $user->access_id == $wholesaler->wholesaler_id && $once_off_arr[$a]["wholesale_unit_amount"] != "0.00" ) {
    $pt->setVar("ITEM_ID",$once_off_arr[$a]["item_id"]);
    $pt->setVar("WHOLESALER",$wholesaler->company_name);
    $pt->setVar("WHOLESALER_ID",$wholesaler->wholesaler_id);
    $pt->setVar("CUSTOMER",$customer->first_name . " " . $customer->last_name);
    $pt->setVar("CUSTOMER_ID",$customer->customer_id);
    $pt->setVar("SERVICE",$service_type->description);
    $pt->setVar("SERVICE_ID",$service->service_id);
    $pt->setVar("DESCRIPTION",$once_off_arr[$a]["description"]);
    $pt->setVar("MAIN_UNIT_AMT","$".$once_off_arr[$a]["main_unit_amount"]);
    $pt->setVar("WHOLESALE_UNIT_AMT","$".$once_off_arr[$a]["wholesale_unit_amount"]);
    $pt->setVar("GST",$once_off_arr[$a]["gst"]);
    $pt->setVar("QTY",$once_off_arr[$a]["qty"]);
    $pt->setVar("DATE",$date_nice->date_nice($once_off_arr[$a]["ts"]));
    $pt->parse("ROWS","row","true");
  } else if ( $user->class == "admin" && $once_off_arr[$a]["wholesale_unit_amount"] != "0.00" ) {
    $pt->setVar("ITEM_ID",$once_off_arr[$a]["item_id"]);
    $pt->setVar("WHOLESALER",$wholesaler->company_name);
    $pt->setVar("WHOLESALER_ID",$wholesaler->wholesaler_id);
    $pt->setVar("CUSTOMER",$customer->first_name . " " . $customer->last_name);
    $pt->setVar("CUSTOMER_ID",$customer->customer_id);
    $pt->setVar("SERVICE",$service_type->description);
    $pt->setVar("SERVICE_ID",$service->service_id);
    $pt->setVar("DESCRIPTION",$once_off_arr[$a]["description"]);
    $pt->setVar("MAIN_UNIT_AMT","$".$once_off_arr[$a]["main_unit_amount"]);
    $pt->setVar("WHOLESALE_UNIT_AMT","$".$once_off_arr[$a]["wholesale_unit_amount"]);
    $pt->setVar("GST",$once_off_arr[$a]["gst"]);
    $pt->setVar("QTY",$once_off_arr[$a]["qty"]);
    $pt->setVar("DATE",$date_nice->date_nice($once_off_arr[$a]["ts"]));
    $pt->parse("ROWS","row","true");
  }
}

$pt->setVar("CURRENT_PAGE",$current_page);
$pt->setVar("PREVIOUS_PAGE",$previous_page);
$pt->setVar("NEXT_PAGE",$next_page);
$pt->setVar("TOTAL_PAGES",$total_pages);
$pt->setVar("ENTRIES",$total_entries);

$pt->setVar("PAGE_TITLE", "View Once-Off Billing Actioned");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");