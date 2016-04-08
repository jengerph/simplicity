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
include_once "../../../../../../setup.inc";
include "../../../../../doauth.inc";
include_once "customers.class";
include_once "services.class";
include_once "realms.class";
include_once "authorised_rep.class";
include_once "orders.class";
include_once "order_attributes.class";
include_once "orders_states.class";
include_once "order_comments.class";
include_once "services.class";
include_once "service_attributes.class";
include_once "radius.class";
include_once "plans.class";
include_once "plan_attributes.class";
include_once("class.phpmailer.php");
include_once "wholesalers.class";
include_once "wholesaler_plan_groups.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/inbound_voice/creation/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/inbound_voice/creation/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/inbound_voice/creation/index.html"));
  
}

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
  echo "Invalid Service ID.";
  exit();
}

if ( !isset($_REQUEST["order_contract_length"]) ) {
	$pt->setVar("CONTRACT_LENGTH_24", " selected");
}

$services = new services();
$services->service_id = $_REQUEST["service_id"];
$services->load();

$customer = new customers();
$customer->customer_id = $services->customer_id;
$customer->load();

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $customer->wholesaler_id;
$wholesaler->load();

$contacts = new authorised_rep();
$contacts->customer_id = $services->customer_id;
$contacts_arr = $contacts->get_contacts();

$plan = new plans();
$plan->plan_id = $_SESSION["edit_retail_plan"];
$plan->load();

if ( isset($_REQUEST['submit']) ) {

  // edit service
  $error_msg = '';
  $services->tag = $_REQUEST['tag'];

  $validate = new validate();

  $error_order = array();

    if ( !isset($_REQUEST["order_contact"]) || $_REQUEST["order_contact"] == "" ) {
      $error_order[] = "Authorized Contact invalid.";
    }

  $vc = $services->validate();

  if ( count($error_order) > 0 ) {

    $pt->setVar('ERROR_MSG','Error: ' . $error_order[0]);

  } else if ($vc != 0) {
  
    $pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

  } else {
    $services->save();

    $service_type = new service_types();
    $service_type->type_id = $services->type_id;
    $service_type->load();

    //create entry to orders
    $orders = new orders();
    $orders->service_id = $services->service_id;
    $orders->start = date("Y-m-d H:i:s");
    $orders->request_type = strtolower($service_type->description);
    $orders->action = "new";
    $orders->status = "pending";
    $orders->create();

    //create entry to order_attributes
    $order_attributes = new order_attributes();
    $order_attributes->order_id = $orders->order_id;
    $order_attributes->param = "edit_retail_plan";
    $order_attributes->value = $_SESSION["edit_retail_plan"];
    if ( $order_attributes->value != "" ) {
      $order_attributes->create();
    }

    $order_attributes = new order_attributes();
    $order_attributes->order_id = $orders->order_id;
    $order_attributes->param = "edit_contact";
    $order_attributes->value = $_REQUEST["order_contact"];
    if ( $order_attributes->value != "" ) {
      $order_attributes->create();
    }

    $order_attributes = new order_attributes();
    $order_attributes->order_id = $orders->order_id;
    $order_attributes->param = "edit_sod";
    $order_attributes->value = $_SESSION["inbound_voice"]["sod"];
    if ( $order_attributes->value != "" ) {
      $order_attributes->create();
    }

    $order_attributes = new order_attributes();
    $order_attributes->order_id = $orders->order_id;
    $order_attributes->param = "edit_cd";
    $order_attributes->value = $_SESSION["inbound_voice"]["cd"];
    if ( $order_attributes->value != "" ) {
      $order_attributes->create();
    }


    //create entry to orders_states
    $orders_states = new orders_states();
    $orders_states->order_id = $orders->order_id;
    $orders_states->state_name = $orders->status;
    $orders_states->create();

      // Send order receipt:
      $mail = new PHPMailer();
      
      $mail->From     = "service.delivery@xi.com.au";
      $mail->FromName = "X Integration Pty Ltd";
      $mail->Subject = "Order Receipt Notification";
      $mail->Host     = "127.0.0.1";
      $mail->Mailer   = "smtp";
    
      $text_body  = "Dear " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . ",\r\n";
      $text_body .= "\r\n";
      $text_body .= "Thank you for engaging X Integration as your preferred carrier. We are pleased to confirm your order for the service listed below. \r\n";
      $text_body .= "\r\n";
      $text_body .= "We have provided you with a unique X Integration Provisioning reference so that you can easily track the progress of this order. \r\n";
      $text_body .= "\r\n";
      $text_body .= "*Note: This email notification is confirmation of X Integration's receipt of your order only. X Integration has not yet accepted your order. \r\n\r\n";
      $text_body .= "Company Name: " . ucwords($customer->company_name) . "\r\n\r\n";
      $text_body .= "Customer Name: " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . "\r\n\r\n";
      $text_body .= "Order Reference: " . $orders->order_id . "\r\n\r\n";
      $text_body .= "Below is a list of item(s) on this order. " . "\r\n\r\n";

      $plan_title = new plans();
      $plan_title->plan_id = $services->retail_plan_id;
      $plan_title->load();

      $change_plan = new plans();
      $change_plan->plan_id = $_SESSION["edit_retail_plan"];
      $change_plan->load();

      $text_body .= "Type of Order: " . ucwords($orders->action) . "- Inbound Voice\r\n\r\n";
      $text_body .= "Service Components:\r\n\r\n";
      $text_body .= "Service ID: " . $services->service_id . "\r\n";
      $text_body .= "Current Plan: " . $plan_title->description . "\r\n";
      $text_body .= "Change Current Plan to: " . $change_plan->description . "\r\n";
      $text_body .= "Transaction Type: " . ucwords($orders->action) . "\r\n";
      $text_body .= "Customer Account Number: " . ucwords($customer->customer_id) . "\r\n";

      $contract_length = new plan_attributes();
      $contract_length->plan_id = $plan_title->plan_id;
      $contract_length->param = "contract_length";
      $contract_length->get_latest();

      $text_body .= "Contract Term (Months): " . $contract_length->value . "\r\n\r\n";

      if ( isset($services->identifier) ) {
        $text_body .= "Service Number: " . $service->identifier . "\r\n";
      }

      if ( !empty($_SESSION["inbound_voice"]["sod"]) ) {
        $text_body .= "Standard One Destination: " . $_SESSION["inbound_voice"]["sod"] . "\r\n";
      }

      if ( !empty($_SESSION["inbound_voice"]["cd"]) ) {
        $text_body .= "Customer Distribution: " . $_SESSION["inbound_voice"]["cd"] . "\r\n";
      }

      $service_types = new service_types();
      $service_types->type_id = $plan_title->type_id;
      $service_types->load();

      $text_body .= "Access Technology: " . $service_types->description . "\r\n\r\n";
      $text_body .= "Provisioning Target Up to 21 days from X Integration's acceptance of your order (excluding customer delays).\r\n\r\n";
      $text_body .= "The provisioning target shown above is based on the access type for the service. You can only order one service at a time. This ensures that the provision of one service is not held up pending the provision of other services on the order.\r\n\r\n";
      $text_body .= "Throughout the provisioning process, we will provide you with updates on key milestones by sending the following notifications for each service on your order: \r\n\r\n";
      $text_body .= "Order Acceptance Notification\r\n";
      $text_body .= "If X Integration is able to accept your order you can expect to receive this in the next two business days. This notification will include the expected start date for this service.\r\n\r\n";
      $text_body .= "Service Completion Advice\r\n";
      $text_body .= "Confirmation that the provisioning of your service has been completed and billing has commenced. This will also provide you with the contact details for your Service and Provisioning Team.\r\n\r\n";
      $text_body .= "It is important to X Integration that you, our customer, are satisfied with the level of service provided.\r\n\r\n";
      $text_body .= "To view the latest update on your order please visit this link: https://simplicity.xi.com.au/base/manage/orders/edit/?order_id=" . $orders->order_id . ". or visit our online Simplicity portal by logging in via https://simplicity.xi.com.au and view all your current orders under the order tab.\r\n\r\n";
      $text_body .= "Alternatively you can contact X Integration Service and Provisioning Team using the contact details provided below.\r\n\r\n";
      $text_body .= "Kind Regards,\r\n";
      $text_body .= "X Integration Service and Provisioning Team\r\n\r\n";
      $text_body .= "service.delivery@xi.com.au";
      
      $mail->Body    = $text_body;

      if ( $wholesaler->block_customer_order_notif == "no" ) {
        $mail->AddAddress($customer->email);
      }
      
      $mail->AddBCC("alerts@xi.com.au");
      $mail->AddBCC($wholesaler->email);

      // $mail->AddAddress("renee@cloudemployee.co.uk");
      // $mail->AddBCC("campanilla_renee@yahoo.com");

      $comment = new order_comments();
      $comment->order_id = $orders->order_id;
      $comment->username = $user->username;
      $comment->comment_visibility = "customer";
      $comment->comment = $text_body;
      $comment->create();

      $mail->Send();

    foreach($_SESSION as $key => $value) {
      $pos = strpos($key , "edit_");
      if ($pos === 0){
        unset($_SESSION[$key]);
      }
    }

    unset($_SESSION["service_qualify_array"]);
    unset($_SESSION["inbound_voice"]);

    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    if ( $user->class != 'customer' ) {
      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/customers/?customer_id=" . $customer->customer_id;
    } else if ( $user->class == 'customer' ) {
      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/";
    }

    header("Location: $url");
    exit();   
  
  }
}

$plan_attr = new plan_attributes();
$plan_attr->plan_id = $plan->plan_id;
$plan_attr->param = "contract_length";
$plan_attr->get_latest();

$current_plan = new plans();
$current_plan->plan_id = $services->retail_plan_id;
$current_plan->load();

$orders = new orders();
$orders->service_id = $services->service_id;
$orders->get_closed();

$order_attr = new order_attributes();
$order_attr->order_id = $orders->order_id;
$order_attr->param = "order_contact";
$order_attr->get_latest();

$order_attr_tan = new order_attributes();
$order_attr_tan->order_id = $orders->order_id;
$order_attr_tan->param= "order_tel_account_num";
$order_attr_tan->get_latest();

$pt->setVar("ORDER_CONTACT_LIST",$contacts->contact_list("order_contact",$contacts_arr));
$pt->setVar("SERVICE_TYPE","Inbound Voice");
$pt->setVar("IDENTIFIER",$services->identifier);
$pt->setVar("COMPANY_NAME",$customer->company_name);
$pt->setVar("TEL_ACCT_NUM",$order_attr_tan->value);
$pt->setVar("DIST_NUMBER",$_SESSION["inbound_voice"]["sod"]);
$pt->setVar("DIST_COMPLEX",$_SESSION["inbound_voice"]["cd"]);
$pt->setVar("CONTRACT_LENGTH",$plan_attr->value . " Months");
$pt->setVar("CURRENT_RETAIL_PLAN_LIST",$current_plan->description);
$pt->setVar("NEW_RETAIL_PLAN_LIST",$plan->description);
$pt->setVar("TAG",$services->tag);
$pt->setVar("SERVICE_ID",$services->service_id);

$pt->setVar("AR_CONTACT_".$order_attr->value," selected");

$pt->setVar("PAGE_TITLE", "Inbound Voice - Service Creation");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");