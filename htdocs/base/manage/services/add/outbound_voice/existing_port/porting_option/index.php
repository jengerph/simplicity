<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/outbound_voice/existing_port/porting_option/index.php - Outbound Voice
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
include_once "customers.class";
include_once "service_attributes.class";
include_once "service_temp.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/outbound_voice/existing_port/porting_option/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/outbound_voice/existing_port/porting_option/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/outbound_voice/existing_port/porting_option/index.html"));
  
}

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
  exit();
}

if ( !isset($_REQUEST["outbound_kind"]) ) {
  echo "No Outbound Voice Kind selected.";
  exit();
}

if ( !isset($_REQUEST['sp']) || empty($_REQUEST['sp']) ) {
  echo "URL invalid";
  exit();
}

$session_pointer0 = $_REQUEST['sp'];
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

    $service_data["outbound_voice"]["kind"] = $_REQUEST["outbound_kind"];
    $service_data["outbound_voice"]["existing_port"] = "yes";
    $service_data["outbound_voice"]["service_number"] = $_REQUEST["outbound_service_number"];
    $service_data["outbound_voice"]["account_name"] = $_REQUEST["outbound_account_name"];
    $service_data["outbound_voice"]["account_number"] = $_REQUEST["outbound_account_number"];
    $service_data["outbound_voice"]["carrier"] = $_REQUEST["outbound_carrier"];
    // $_SESSION["outbound_voice"]["outbound_upload_bill"] = $_FILES["outbound_upload_bill"];

    //upload file
    $upload_bill = new service_attributes();
    $upload_bill->customer_id = $customer->customer_id;
    $upload_bill->file_name = $_FILES["outbound_upload_bill"]["name"];

    $fp      = fopen($_FILES["outbound_upload_bill"]['tmp_name'], 'r');
    $upload_bill->file = fread($fp, filesize($_FILES["outbound_upload_bill"]['tmp_name']));
    fclose($fp);
    
    $file_name = $upload_bill->file_upload();
    $service_data["outbound_voice"]["outbound_upload_bill"] = $file_name;

    $service_temp->data = serialize($service_data);
    $service_temp->save();

  // Done, goto list
    $url = "";
        
    if ( isset($_SERVER["HTTPS"]) ) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/outbound_voice/existing_port/simultaneous_calls/?customer_id=" . $customer->customer_id . "&sp=" . $session_pointer0;

    header("Location: $url");
    exit();
  }
}

$pt->setVar("OUTBOUND_KIND",$_REQUEST["outbound_kind"]);
$pt->setVar("CUSTOMER_ID",$_REQUEST["customer_id"]);
$pt->setVar("SP",$_REQUEST['sp']);

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