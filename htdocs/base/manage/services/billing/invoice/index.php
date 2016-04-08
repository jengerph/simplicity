<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/billing/invoice/index.php - View Invoice and can download pdf
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
include_once "service_attributes.class";
require 'pdfcrowd.php';

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
  
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/billing/invoice/index.html"));
  
} else if ($user->class == 'admin') {
  
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/billing/invoice/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("back_link_service" => "base/manage/services/billing/back_link/back_link_service.html",
                    "back_link_download_invoice" => "base/manage/services/billing/back_link/back_link_download_invoice.html",
                    "invoice_content" => "base/manage/services/billing/invoice/invoice_content.html"));

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
  echo "Serivce ID Invalid.";
  exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$customer = new customers();
$customer->customer_id = $service->customer_id;
$customer->load();

$address = new service_attributes();
$address->service_id = $service->service_id;
$address->param = "addressInformation";
$address->get_attribute();

if ( empty($address->value) && $address->param == "addressInformation" ) {
  $address->param = "address";
  $address->get_attribute();
}

if ( empty($address->value) && $address->param == "address" ) {
  $address->param = "delivery_address";
  $address->get_attribute();
}

$pt->setVar("SERVICE_ID", $service->service_id);
$pt->setVar("INVOICE_OWNER_NAME", $customer->first_name . " " . $customer->last_name);
$pt->setVar("INVOICE_ADDRESS", $address->value);
$pt->setVar("INVOICE_NUMBER", "0000");
$pt->setVar("BILLING_PERIOD", "11 Aug - 10 Sep 2015");
$pt->setVar("DATE_OF_ISSUE", "12 Sep 2015");
$pt->setVar("ITEM_TYPE", "Mobile Phone");
$pt->setVar("IT_NUMBER", "0406532793");
$pt->setVar("IT_AMOUNT", "34.99");
$pt->setVar("ACOUNT_CHARGES", "0.77");
$pt->setVar("PREVIOUS_BALANCE", "0.00");
$pt->setVar("TOTAL_BALANCE", "35.76");
$pt->parse( "DOWNLOAD_LINK_INVOICE", "back_link_download_invoice", "true" );
$pt->parse( "BACK_LINK", "back_link_service", "true" );
$pt->parse( "INVOICE_CONTENT", "invoice_content", "true" );

$temp_var = $pt->parse( "", "invoice_content", "true" );

// print_r($temp_var);
// exit();

if ( isset($_REQUEST["dl"]) && $_REQUEST["dl"] == "yes" ) {
  try
    {   
        // create an API client instance
        $client = new Pdfcrowd("reneecamp", "8afb4a6618366c20ebdb2ffdb624063c");

        //pageSetup
        // $client->setPageWidth('17in');
        // $client->setPageHeight('8in');

        // convert a web page and store the generated PDF into a $pdf variable
        $pdf = $client->convertHtml($temp_var);

        //date today
        $date = date("d-m-Y");

        // set HTTP response headers
        header("Content-Type: application/pdf");
        header("Cache-Control: max-age=0");
        header("Accept-Ranges: none");
        header("Content-Disposition: attachment; filename=\"billing_".$date.".pdf\"");

        // send the generated PDF 
        echo $pdf;
    }
    catch(PdfcrowdException $why)
    {
        echo "Pdfcrowd Error: " . $why;
    }
}

$pt->setVar("PAGE_TITLE", "View Invoice");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");