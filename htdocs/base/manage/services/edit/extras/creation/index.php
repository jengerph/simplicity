<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/extras/index.php - Edit Service Extras
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
include_once "plan_extras.class";
include_once("class.phpmailer.php");


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/extras/creation/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/extras/creation/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/extras/creation/index.html"));
  
}

//assign template to use
$pt->setFile(array("rows" => "base/manage/services/edit/extras/creation/rows.html"));

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
  echo "Invalid Service ID.";
  exit();
}

// $_SESSION["extras"]["delivery_address"] = "2 WILD DUCK ROAD MIA MIA VIC 0000";

$services = new services();
$services->service_id = $_REQUEST["service_id"];
$services->load();

$customer = new customers();
$customer->customer_id = $services->service_id;
$customer->load();

$contacts = new authorised_rep();
$contacts->customer_id = $services->customer_id;
$contacts_arr = $contacts->get_contacts();

$extras = array();
$plan_extras = new plan_extras();
$plan_extras->plan_id = $services->retail_plan_id;
$pe_arr = $plan_extras->get_extra_types();

for ($h=0; $h < count($pe_arr); $h++) { 
  $extras[] = $pe_arr[$h]["type"];
}

$extras_fmt = array("staticip" => "static ip",
                    "ipblock4" => "ip block 4",
                    "ipblock8" => "ip block 8",
                    "ipblock16" => "ip block 16");

for ($i=0; $i < count($extras); $i++) { 
  $pt->setVar("EXTRAS_KEY",strtoupper($extras_fmt[$extras[$i]]));
  $pt->setVar("EXTRAS_ACTIVATE",strtoupper($_SESSION["order_".$extras[$i]]));
  $pt->parse("EXTRA_ROW","rows","true");
}

if ( isset($_REQUEST["submit"]) ) {
  $error_order = array();

  if ( !isset($_REQUEST["order_contact"]) || $_REQUEST["order_contact"] == "" ) {

    $pt->setVar('ERROR_MSG','Error: Authorized Contact invalid.');

  } else {

    $service_type = new service_types();
    $service_type->type_id = $services->type_id;
    $service_type->load();

    //create an entry for orders
    $order = new orders();
    $order->service_id = $services->service_id;
    $order->request_type = strtolower($service_type->description);
    $order->action = "addon update";
    $order->status = "pending";
    $order->start = date("Y-m-d H:i:s");
    $order->create();

    //create entries order_attributes
    $order_attr = new order_attributes();
    $order_attr->order_id = $order->order_id;
    $order_attr->param = "order_address";
    if ( isset($_SESSION["extras"]["delivery_address"]) ) {
      $order_attr->value = $_SESSION["extras"]["delivery_address"];
    }
    $order_attr->create();

    $order_attr = new order_attributes();
    $order_attr->order_id = $order->order_id;
    $order_attr->param = "order_contact";
    if ( isset($_REQUEST["order_contact"]) ) {
      $order_attr->value = $_REQUEST["order_contact"];
    }
    $order_attr->create();

    for ($a=0; $a < count($extras); $a++) { 
      $order_attr = new order_attributes();
      $order_attr->order_id = $order->order_id;
      $order_attr->param = "order_" . $extras[$a];
      if ( isset($_SESSION["order_".$extras[$a]]) ) {
        $order_attr->value = $_SESSION["order_".$extras[$a]];
        $order_attr->create();
      }
    }

    //create entry to orders_states
    $orders_states = new orders_states();
    $orders_states->order_id = $order->order_id;
    $orders_states->state_name = $order->status;
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
      $text_body .= "Order Reference: " . $order->order_id . "\r\n\r\n";
      $text_body .= "Below is a list of item(s) on this order. " . "\r\n\r\n";

      $plan_title = new plans();
      $plan_title->plan_id = $services->retail_plan_id;
      $plan_title->load();

      $text_body .= "Type of Order: " . ucwords($order->action) . "- " . strtoupper($service_type->description) . "\r\n\r\n";
      $text_body .= "Service Components:\r\n\r\n";
      $text_body .= "Service ID: " . $services->service_id . "\r\n";
      $text_body .= "Current Plan: " . $plan_title->description . "\r\n";
      $text_body .= "Transaction Type: " . ucwords($order->action) . "\r\n";
      $text_body .= "Customer Account Number: " . ucwords($customer->customer_id) . "\r\n";
      $text_body .= "Delivery Address: " . $_SESSION["extras"]["delivery_address"] . "\r\n";
      $text_body .= "Addons: \r\n";

      for ($j=0; $j < count($extras); $j++) { 
        $sa_extra = new service_attributes();
        $sa_extra->service_id = $services->service_id;
        $sa_extra->param = $extras[$j];
        $sa_extra->get_attribute();
        if ( isset($sa_extra->value) ) {
          $text_body .= strtoupper($extras_fmt[$extras[$j]]) . ": " . strtoupper($sa_extra->value) . " to " . strtoupper($_SESSION["order_".$extras[$j]]) . "\r\n";
        } else {
          $text_body .= strtoupper($extras_fmt[$extras[$j]]) . ": DEACTIVATED to " . strtoupper($_SESSION["order_".$extras[$j]]) . "\r\n";
        }
      }

      $text_body .= "\r\nProvisioning Target Up to 21 days from X Integration's acceptance of your order (excluding customer delays).\r\n\r\n";
      $text_body .= "The provisioning target shown above is based on the access type for the service. You can only order one service at a time. This ensures that the provision of one service is not held up pending the provision of other services on the order.\r\n\r\n";
      $text_body .= "Throughout the provisioning process, we will provide you with updates on key milestones by sending the following notifications for each service on your order: \r\n\r\n";
      $text_body .= "Order Acceptance Notification\r\n";
      $text_body .= "If X Integration is able to accept your order you can expect to receive this in the next two business days. This notification will include the expected start date for this service.\r\n\r\n";
      $text_body .= "Service Completion Advice\r\n";
      $text_body .= "Confirmation that the provisioning of your service has been completed and billing has commenced. This will also provide you with the contact details for your Service and Provisioning Team.\r\n\r\n";
      $text_body .= "It is important to X Integration that you, our customer, are satisfied with the level of service provided.\r\n\r\n";
      $text_body .= "To view the latest update on your order please visit this link: https://simplicity.xi.com.au/base/manage/orders/edit/?order_id=" . $order->order_id . ". or visit our online Simplicity portal by logging in via https://simplicity.xi.com.au and view all your current orders under the order tab.\r\n\r\n";
      $text_body .= "Alternatively you can contact X Integration Service and Provisioning Team using the contact details provided below.\r\n\r\n";
      $text_body .= "Kind Regards,\r\n";
      $text_body .= "X Integration Service and Provisioning Team\r\n\r\n";
      $text_body .= "service.delivery@xi.com.au";
      
      $mail->Body    = $text_body;

      $mail->AddAddress($customer->email);
      $mail->AddBCC("alerts@xi.com.au");

      // $mail->AddAddress("renee@cloudemployee.co.uk");
      // $mail->AddBCC("campanilla_renee@yahoo.com");

      $comment = new order_comments();
      $comment->order_id = $order->order_id;
      $comment->username = $user->username;
      $comment->comment_visibility = "customer";
      $comment->comment = $text_body;
      $comment->create();

      $mail->Send();

      // Done, goto list
      $url = "";
          
      if (isset($_SERVER["HTTPS"])) {
          
        $url = "https://";
            
      } else {
          
        $url = "http://";
      }

      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/orders/edit/?order_id=" . $order->order_id;

      header("Location: $url");
      exit();

  }
}

$current_plan = new plans();
$current_plan->plan_id = $services->retail_plan_id;
$current_plan->load();

$pt->setVar("ORDER_CONTACT_LIST",$contacts->contact_list("order_contact",$contacts_arr));
$pt->setVar("CURRENT_RETAIL_PLAN_LIST",$current_plan->description);
$pt->setVar("TAG",$services->tag);
$pt->setVar("SERVICE_ID",$services->service_id);
$pt->setVar("DELIVERY_ADDRESS",$_SESSION["extras"]["delivery_address"]);

$pt->setVar("PAGE_TITLE", "Inbound Voice - Service Creation");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");