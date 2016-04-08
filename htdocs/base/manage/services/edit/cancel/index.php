<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/cancel/index.php - Cancel a service
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

include_once("class.phpmailer.php");

include_once "services.class";
include_once "service_types.class";
include_once "service_attributes.class";
include_once "plans.class";
include_once "plan_attributes.class";
include_once "plan_extras.class";
include_once "misc.class";
include_once "orders.class";
include_once "order_attributes.class";
include_once "orders_states.class";
include_once "order_comments.class";
include_once "validate.class";
include_once "realms.class";
include_once "customers.class";
include_once "radius.class";
include_once "authorised_rep.class";
include_once "wholesalers.class";
include_once "wholesaler_plan_groups.class";
include_once "service_temp.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ( !isset($_REQUEST['service_id']) ) {
  echo "Service ID invalid.";
  exit(1);
}

$services = new services();
$services->service_id = $_REQUEST['service_id'];
$services->load();

if ($user->class == 'customer') {
  
  $pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/cancel/index.html"));

  if ( $user->access_id != $service->customer_id ) {
    $pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
    // Parse the main page
    $pt->parse("MAIN", "main");
    $pt->parse("WEBPAGE", "outside");

    // Print out the page
    $pt->p("WEBPAGE");

    exit();
  }
  
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/cancel/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/cancel/index.html"));
  
}

$pt->setFile(array("wholesaler_row" => "base/manage/services/edit/confirm/wholesaler_row.html",
                    "retailer_row" => "base/manage/services/edit/confirm/retailer_row.html",
                    "username_password_row" => "base/manage/services/edit/confirm/username_password_row.html",
                    "extra_row" => "base/manage/services/edit/confirm/extra_row.html",
                    "churn" => "base/manage/services/edit/confirm/churn.html",
                    "extra_staticip" => "base/manage/services/edit/extra_staticip.html",
                    "extra_ipblock4" => "base/manage/services/edit/extra_ipblock4.html",
                    "extra_ipblock8" => "base/manage/services/edit/extra_ipblock8.html",
                    "extra_ipblock16" => "base/manage/services/edit/extra_ipblock16.html"));

$customer = new customers();
$customer->customer_id = $services->customer_id;
$customer->load();

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $customer->wholesaler_id;
$wholesaler->load();

$service_type = new service_types();
$service_type->type_id = $services->type_id;
$service_type->load();

$contacts = new authorised_rep();
$contacts->customer_id = $customer->customer_id;
$contacts_arr = $contacts->get_contacts();

$current_plan = new plans();
$current_plan->plan_id = $services->retail_plan_id;
$current_plan->load();

if ( !isset($_REQUEST["sp"]) ) {
  echo "Invalid URL";
  exit();
}

$session_pointer = $customer->customer_id . "_" . $_REQUEST["sp"];

$service_temp = new service_temp();
$service_temp->data_key = $session_pointer;
$service_temp->load();

$service_data = unserialize($service_temp->data);

$new_plan = new plans();
$new_plan->plan_id = $service_data["edit_retail_plan"];
$new_plan->load();

if ( isset($_REQUEST['submit']) ) {
  
  //set username, password, and realms
  $_REQUEST["order_username"] = get_service_attribute($services->service_id,"username");
  $_REQUEST["order_realms"] = get_service_attribute($services->service_id,"realms");
  $_REQUEST["order_password"] = get_service_attribute($services->service_id,"password");
  $_REQUEST["order_address"] = get_service_attribute($services->service_id,"address");
  if ( empty($_REQUEST["order_address"]) ) {
    $_REQUEST["order_address"] = get_service_attribute($services->service_id,"delivery_address");
  }
  $_REQUEST["order_service_number"] = $services->identifier;
  $_REQUEST["order_contract_length"] = get_service_attribute($services->service_id,"contract_length");
  $_REQUEST["order_type"] = get_service_attribute($services->service_id,"contract_length");

  $order_keys = array();
  $sess_keys = array();
  $sess_keys2 = array();

  foreach($_REQUEST as $key => $value) {
    $pos = strpos($key , "order_");
    if ($pos === 0){
      $order_keys[] = $key;
    }
  }

  foreach($service_data as $key => $value) {
    $pos = strpos($key , "order_");
    if ($pos === 0){
      $sess_keys[] = $key;
    }
  }

  foreach($service_data as $key => $value) {
    $pos = strpos($key , "edit_");
    if ($pos === 0){
      $sess_keys2[] = $key;
    }
  }

  if ( $services->wholesale_plan_id == 0 && $user->class == 'customer' ) {
    $services->wholesale_plan_id = "-";
  }

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

    $service_type = new service_types();
    $service_type->type_id = $services->type_id;
    $service_type->load();

    //create entry to orders
    $orders = new orders();
    $orders->service_id = $services->service_id;
    $orders->start = date("Y-m-d H:i:s");
    $orders->request_type = strtolower($service_type->description);
    $orders->action = $service_data["action_order"];
    $orders->status = "pending";
    $orders->create();

    //create entry to order_attributes
    for ( $y = 0; $y < count($order_keys); $y++ ) {
      $order_attributes = new order_attributes();
      $order_attributes->order_id = $orders->order_id;
      $order_attributes->param = $order_keys[$y];
      $order_attributes->value = $_REQUEST[$order_keys[$y]];
      if ( $order_attributes->value != "" ) {
        $order_attributes->create();
      }
    }
    for ( $y = 0; $y < count($sess_keys); $y++ ) {
      $order_attributes = new order_attributes();
      $order_attributes->order_id = $orders->order_id;
      $order_attributes->param = $sess_keys[$y];
      $order_attributes->value = $service_data[$sess_keys[$y]];
      if ( $order_attributes->value != "" ) {
        $order_attributes->create();
      }
    }
    for ( $y = 0; $y < count($sess_keys2); $y++ ) {
      $order_attributes = new order_attributes();
      $order_attributes->order_id = $orders->order_id;
      $order_attributes->param = $sess_keys2[$y];
      $order_attributes->value = $service_data[$sess_keys2[$y]];
      if ( $order_attributes->value != "" ) {
        $order_attributes->create();
      }
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
      if ( $services->type_id == '1' ||  $services->type_id == '2' ) {
        $text_body .= "Username: " . get_service_attribute($services->service_id,"username") . "@" . get_service_attribute($services->service_id,"realms") . "\r\n\r\n";
        $text_body .= "Password: " . get_service_attribute($services->service_id,"password") . "\r\n\r\n";
      }
      $text_body .= "Order Reference: " . $orders->order_id . "\r\n\r\n";
      $text_body .= "Below is a list of item(s) on this order. " . "\r\n\r\n";

      $plan_title = new plans();
      $plan_title->plan_id = $services->retail_plan_id;
      $plan_title->load();

      $text_body .= "Type of Order: " . ucwords($orders->action) . " - " . $service_type->description . " Service\r\n\r\n";
      $text_body .= "Service Components:\r\n\r\n";
      $text_body .= "Service ID: " . $services->service_id . "\r\n";
      $text_body .= "Transaction Type: " . ucwords($orders->action) . "\r\n";
      $text_body .= "Customer Account Number: " . ucwords($customer->customer_id) . "\r\n\r\n";

      $contract_length = new plan_attributes();
      $contract_length->plan_id = $plan_title->plan_id;
      $contract_length->param = "contract_length";
      $contract_length->get_latest();

      $text_body .= "Current Plan: " . $current_plan->description . "\r\n";
      
      $text_body .= "\r\nProvisioning Target Up to 21 days from X Integration's acceptance of your order (excluding customer delays).\r\n\r\n";
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

    $service_temp->delete();

    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }
      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/orders/edit/?order_id=".$orders->order_id;

    header("Location: $url");
    exit();   
  
  }
}

//for addon
$extra = array();
$plan_extras = new plan_extras();
$plan_extras->plan_id = $service_data["edit_retail_plan"];
$pe_arr = $plan_extras->get_extra_types();

for ($h=0; $h < count($pe_arr); $h++) { 
  $extra[] = $pe_arr[$h]["type"];
}

// $extra = array("static_ip","ip_block4","ip_block8","ip_block16");

if ( isset($extra[0]) ) {
  for ($i=0; $i < count($extra); $i++) {
    $sa_extra = new service_attributes();
    $sa_extra->service_id = $services->service_id;
    $sa_extra->param = $extra[$i];
    $sa_extra->get_attribute();
    if ( $sa_extra->value == "activated" ) {
      $pt->setVar("ACTIVATE_" . strtoupper($sa_extra->param), " checked" );
    }
      $pt->parse("EXTRA_OPTION","extra_".$extra[$i],"true");
  }
}

if ( $services->type_id == '1' || $services->type_id == '2' ) {
  $pt->parse("USERNAME_PASSWORD_ROW","username_password_row","true");
  $pt->parse("EXTRA_ROW","extra_row","true");
}

$pt->setVar("SERVICE_ID", $services->service_id);
$pt->setVar("IDENTIFIER", $services->identifier);
$address = get_service_attribute($services->service_id,"address");
if ( empty($address) ) {
    $address = get_service_attribute($services->service_id,"delivery_address");
}
$pt->setVar("ORDER_ADDRESS", $address);
$pt->setVar("ORDER_USERNAME", get_service_attribute($services->service_id,"username") . "@" . get_service_attribute($services->service_id,"realms"));
$pt->setVar("ORDER_PASSWORD", get_service_attribute($services->service_id,"password"));
$pt->setVar("CONTRACT_END", date('d/m/Y',strtotime($services->contract_end)));
$pt->setVar("SERVICE_TYPE", $service_type->description);
$pt->setVar("ORDER_CONTACT_LIST",$contacts->contact_list("order_contact",$contacts_arr));
$pt->setVar("CURRENT_PLAN",$current_plan->description);
$pt->setVar("SP",$_REQUEST["sp"]);

$pt->setVar("PAGE_TITLE", "Cancel Service");

// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function create($id,$param,$value){

      $attributes = new order_attributes();
      $attributes->order_id = $id;
      $attributes->param = $param;
      $attributes->value = $value;
      if ( $attributes->value !="" ) {
        $attributes->create();
      }

}

function get_attribute($order_id,$param){

  $email_order_attr = new order_attributes();
  $email_order_attr->order_id = $order_id;
  $email_order_attr->param = $param;
  $email_order_attr->get_latest();

  return $email_order_attr->value;
}

function get_service_attribute($service_id,$param){

  $attribute = new service_attributes();
  $attribute->service_id = $service_id;
  $attribute->param = $param;
  $attribute->get_attribute();

  return $attribute->value;
}