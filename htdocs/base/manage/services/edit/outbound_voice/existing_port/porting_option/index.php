<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/outbound_voice/existing_port/porting_option/index.php - Edit Outbound Voice
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../../../setup.inc";
include "../../../../../../doauth.inc";
include_once "services.class";
include_once "plans.class";
include_once "service_attributes.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/porting_option/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/porting_option/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/porting_option/index.html"));
  
}

if ( !isset($_REQUEST["service_id"]) ) {
  echo "Invalid Service ID.";
  exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

if ( isset($_REQUEST["submit"]) ) {

  $error = array();

  if ( !isset($_REQUEST["outbound_service_number"]) || empty($_REQUEST["outbound_service_number"]) ) {
    $error[] = "Error: Service Number invalid.";
  }

  if ( !isset($_REQUEST["outbound_account_name"]) || empty($_REQUEST["outbound_account_name"]) ) {
    $error[] = "Error: Account Name invalid.";
  }

  if ( !isset($_REQUEST["outbound_account_number"]) || empty($_REQUEST["outbound_account_number"]) ) {
    $error[] = "Error: Account Number invalid.";
  }

  if ( !isset($_REQUEST["outbound_carrier"]) || empty($_REQUEST["outbound_carrier"]) ) {
    $error[] = "Error: Carrier invalid.";
  }

  // if ( !isset($_REQUEST["outbound_upload_bill"]) || empty($_REQUEST["outbound_upload_bill"]) ) {
  //   $error[] = "Error: File invalid.";
  // }

  if ( count($error) != 0 ) {

    $pt->setVar("ERROR_MSG",$error[0]);

  } else {

    $plan = new plans();
    $plan->plan_id = $service->retail_plan_id;
    $plan->load();

    $_SESSION["outbound_voice"]["kind"] = $plan->sub_type;
    $_SESSION["outbound_voice"]["existing_port"] = "yes";
    $_SESSION["outbound_voice"]["service_number"] = $_REQUEST["outbound_service_number"];
    $_SESSION["outbound_voice"]["account_name"] = $_REQUEST["outbound_account_name"];
    $_SESSION["outbound_voice"]["account_number"] = $_REQUEST["outbound_account_number"];
    $_SESSION["outbound_voice"]["carrier"] = $_REQUEST["outbound_carrier"];
    // $_SESSION["outbound_voice"]["outbound_upload_bill"] = $_FILES["outbound_upload_bill"];

    //upload file
    $upload_bill = new service_attributes();
    $upload_bill->customer_id = $service->customer_id;
    $upload_bill->file_name = $_FILES["outbound_upload_bill"]["name"];

    $fp      = fopen($_FILES["outbound_upload_bill"]['tmp_name'], 'r');
    $upload_bill->file = fread($fp, filesize($_FILES["outbound_upload_bill"]['tmp_name']));
    fclose($fp);
    
    $file_name = $upload_bill->file_upload();
    $_SESSION["outbound_voice"]["outbound_upload_bill"] = $file_name;

  // Done, goto list
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/outbound_voice/existing_port/simultaneous_calls/?service_id=" . $service->service_id ;

    header("Location: $url");
    exit();
  }
}

$pt->setVar("SERVICE_ID",$_REQUEST["service_id"]);

if ( isset($_REQUEST["existing_port"]) ) {
  $pt->setVar(strtoupper($_REQUEST["existing_port"]), "checked");
} else {
  $pt->setVar("YES", "checked");
}

if ( isset($_REQUEST["outbound_service_number"]) ) {
  $pt->setVar("OUTBOUND_SERVICE_NUMBER",$_REQUEST["outbound_service_number"]);
}

if ( isset($_REQUEST["outbound_account_name"]) ) {
  $pt->setVar("OUTBOUND_ACCOUNT_NAME",$_REQUEST["outbound_account_name"]);
}

if ( isset($_REQUEST["outbound_account_number"]) ) {
  $pt->setVar("OUTBOUND_ACCOUNT_NUMBER",$_REQUEST["outbound_account_number"]);
}

if ( isset($_REQUEST["outbound_carrier"]) ) {
  $pt->setVar("OUTBOUND_CARRIER",$_REQUEST["outbound_carrier"]);
}

if ( isset($_REQUEST["outbound_upload_bill"]) ) {
  $pt->setVar("OUTBOUND_UPLOAD_BILL",$_REQUEST["outbound_upload_bill"]);
}

$pt->setVar("PAGE_TITLE", "Outbound Voice");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");