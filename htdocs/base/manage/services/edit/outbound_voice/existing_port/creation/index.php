<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/outbound_voice/existing_port/index.php - Edit Outbound Voice: Distribute
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
include_once "config.class";
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
include_once "plan_groups.class";
include_once "plan_attributes.class";
include_once("class.phpmailer.php");
include_once "wholesalers.class";
include_once "wholesaler_plan_groups.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/creation/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/creation/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/outbound_voice/existing_port/creation/index.html"));
  
}

if ( !isset($_REQUEST["service_id"]) ) {
  echo "Invalid Service ID.";
  exit();
}

if ( !isset($_REQUEST["order_contract_length"]) ) {
	$pt->setVar("CONTRACT_LENGTH_24", " selected");
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$customer = new customers();
$customer->customer_id = $service->customer_id;
$customer->load();

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $customer->wholesaler_id;
$wholesaler->load();

$contacts = new authorised_rep();
$contacts->customer_id = $service->customer_id;
$contacts_arr = $contacts->get_contacts();

$services = new services();
$services->type_id = 6;

//for local only
$_SESSION["outbound_voice"]["kind"] = "PSTN";
$_SESSION["outbound_voice"]["existing_port"] = "yes";
$_SESSION["outbound_voice"]["service_number"] = "123123123";
$_SESSION["outbound_voice"]["account_name"] = "aaaaa";
$_SESSION["outbound_voice"]["account_number"] = "123123123";
$_SESSION["outbound_voice"]["carrier"] = "asdsadasd";
$_SESSION["outbound_voice"]["upload_bill"] = "1";
$_SESSION["outbound_voice"]["delivery_address"] = "2 WILD DUCK ROAD MIA MIA VIC 0000";
$_SESSION["outbound_voice"]["simultaneous_calls"] = "5";
$_SESSION["outbound_voice"]["number_range"] = "yes_20";
$_SESSION["nbnLocationID"] = "LOC0000000000";
$_SESSION["telstraLocationID"] = "1111111111111";
// print_r($_SESSION["outbound_voice"]);
if ( isset($_REQUEST['submit']) ) {
  
  $start_date = date("Y-m-d");
  $time = strtotime($start_date);
  $length = intval($_REQUEST['order_contract_length']);
  if ( $length == 0 ) { $length = $length + 1; }
  $final = date("Y-m-d", strtotime("+" . $length . " month -1 day", $time));

  // edit service
  $error_msg = '';
  $services->customer_id = $customer->customer_id;
  $services->type_id = $services->type_id;
  $services->start_date = $start_date . " 00:00:00";
  $services->contract_end = $final . " 00:00:00";
  $services->retail_plan_id = $_REQUEST['retail_plan'];
  
  $parent_plan = new plans();
  $parent_plan->plan_id = $_REQUEST["retail_plan"];
  $parent_plan->load();
  
  $services->wholesale_plan_id = $parent_plan->parent_plan_id;
  $services->state = "creation";
  $services->identifier = $_SESSION["outbound_voice"]["service_number"];
  $services->tag = $_REQUEST['tag'];

  if ( $services->wholesale_plan_id == "" && $user->class == 'customer' ) {
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
    $services->parent_service_id = 0;
    $services->create();
    print_r($services);

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
    $order_keys = array("order_contract_length",
                        "order_contact");
    for ( $y = 0; $y < count($order_keys); $y++ ) {
      $order_attributes = new order_attributes();
      $order_attributes->order_id = $orders->order_id;
      $order_attributes->param = $order_keys[$y];
      $order_attributes->value = $_REQUEST[$order_keys[$y]];
      if ( $order_attributes->value != "" ) {
        $order_attributes->create();
      }
    }

    $sess_keys = array("nbnLocationID",
                        "telstraLocationID");
    for ( $y = 0; $y < count($sess_keys); $y++ ) {
      if ( isset($_SESSION[$sess_keys[$y]]) ) {
        $order_attributes = new order_attributes();
        $order_attributes->order_id = $orders->order_id;
        $order_attributes->param ="order_" .  $sess_keys[$y];
        $order_attributes->value = $_SESSION[$sess_keys[$y]];
        if ( $order_attributes->value != "" ) {
          $order_attributes->create();
        }
      }
    }

    $sess_keys = array("nbnLocationID",
                        "telstraLocationID");
    for ( $y = 0; $y < count($sess_keys); $y++ ) {
      if ( isset($_SESSION[$sess_keys[$y]]) ) {
        $service_attr = new service_attributes();
        $service_attr->service_id = $services->service_id;
        $service_attr->param = $sess_keys[$y];
        $service_attr->value = $_SESSION[$sess_keys[$y]];
        if ( $service_attr->value != "" ) {
          $service_attr->create();
        }
      }
    }

    //for order_attributes
    $service_attr_keys = array("service_number",
                                "account_name",
                                "account_number",
                                "carrier",
                                "delivery_address",
                                "existing_port");
    for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
      $order_attr = new order_attributes();
      $order_attr->order_id = $orders->order_id;
      $order_attr->param = "order_" . $service_attr_keys[$sk];
      if ( isset($_SESSION["outbound_voice"][$service_attr_keys[$sk]]) ) {
        if ( $service_attr_keys[$sk] == "delivery_address" ) {
          $order_attr->param = "order_address";
        }
        $order_attr->value = $_SESSION["outbound_voice"][$service_attr_keys[$sk]];
        $order_attr->create();
      }
    }

    //for service_attributes
    $service_attr_keys = array("kind",
                                "existing_port",
                                "service_number",
                                "account_name",
                                "account_number",
                                "carrier",
                                "number_range",
                                "simultaneous_calls",
                                "delivery_address");
    for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
      $service_attr = new service_attributes();
      $service_attr->service_id = $services->service_id;
      $service_attr->param = $service_attr_keys[$sk];
      if ( isset($_SESSION["outbound_voice"][$service_attr_keys[$sk]]) ) {
        $service_attr->value = $_SESSION["outbound_voice"][$service_attr_keys[$sk]];
        $service_attr->create();
      } else if (isset($_REQUEST["order_".$service_attr_keys[$sk]])) {
        $service_attr->value = $_REQUEST["order_".$service_attr_keys[$sk]];
        $service_attr->create();
      }
    }

    if ( isset($_SESSION["outbound_voice"]["upload_bill"]) ) {

      copy($config->docs_dir . '/billing/temp/temp_' . $customer->customer_id, $config->docs_dir . '/billing/' . $services->service_id);
      unlink($config->docs_dir . '/billing/temp/temp_' . $customer->customer_id);

    }

    //create entry to orders_states
    $orders_states = new orders_states();
    $orders_states->order_id = $orders->order_id;
    $orders_states->state_name = $orders->status;
    $orders_states->create();

    //number range
    if ($_SESSION["outbound_voice"]["existing_port"] == "no") {
        $num_range_service = new services();
        $num_range_service->customer_id = $customer->customer_id;
        $num_range_service->type_id = 7;
        $num_range_service->wholesale_plan_id = $services->wholesale_plan_id;
        $num_range_service->retail_plan_id = $services->retail_plan_id;
        $num_range_service->start_date = $services->start_date;
        $num_range_service->contract_end = $services->contract_end;
        $num_range_service->parent_service_id = $services->service_id;
        $num_range_service->state = "creation";
        $num_range_service->identifier = $services->identifier;
        $num_range_service->tag = $services->tag;
        $num_range_service->create();

        $num_range_order = new orders();
        $num_range_order->service_id = $num_range_service->service_id;
        $num_range_order->start = date("Y-m-d H:i:s");
        $num_range_order->request_type = "number range";
        $num_range_order->action = "new";
        $num_range_order->status = "pending";
        $num_range_order->create();

        //for order_attributes
        $service_attr_keys = array("delivery_address");
        for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
          $order_attr = new order_attributes();
          $order_attr->order_id = $num_range_order->order_id;
          $order_attr->param = "order_" . $service_attr_keys[$sk];
          if ( isset($_SESSION["outbound_voice"][$service_attr_keys[$sk]]) ) {
            if ( $service_attr_keys[$sk] == "delivery_address" ) {
              $order_attr->param = "order_address";
            }
            $order_attr->value = $_SESSION["outbound_voice"][$service_attr_keys[$sk]];
            $order_attr->create();
          }
        }

        $order_keys = array("order_contact");
        for ( $y = 0; $y < count($order_keys); $y++ ) {
          $order_attributes = new order_attributes();
          $order_attributes->order_id = $num_range_order->order_id;
          $order_attributes->param = $order_keys[$y];
          $order_attributes->value = $_REQUEST[$order_keys[$y]];
          if ( $order_attributes->value != "" ) {
            $order_attributes->create();
          }
        }

        //for order_attributes [number_range]
          $order_attr = new order_attributes();
          $order_attr->order_id = $orders->order_id;
          $order_attr->param = "order_number_range";
          $order_attr->value = $num_range_order->order_id;
          $order_attr->create();

        //create entry to orders_states
        $orders_states = new orders_states();
        $orders_states->order_id = $num_range_order->order_id;
        $orders_states->state_name = $num_range_order->status;
        $orders_states->create();
    }

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

      $text_body .= "Type of Order: " . ucwords($orders->action) . " - Outbound Voice\r\n\r\n";
      $text_body .= "Service Components:\r\n\r\n";
      $text_body .= "Service ID: " . $services->service_id . "\r\n";
      $text_body .= "Transaction Type: " . ucwords($orders->action) . "\r\n";
      $text_body .= "Customer Account Number: " . ucwords($customer->customer_id) . "\r\n";

      $contract_length = new plan_attributes();
      $contract_length->plan_id = $plan_title->plan_id;
      $contract_length->param = "contract_length";
      $contract_length->get_latest();

      $text_body .= "Contract Term (Months): " . $contract_length->value . "\r\n\r\n";

      if ( isset($service->identifier) ) {
        $text_body .= "Service Number: " . $service->identifier . "\r\n";
      }

      if ( !empty($_SESSION["outbound_voice"]["account_name"]) ) {
        $text_body .= "Account Name: " . $_SESSION["outbound_voice"]["account_name"] . "\r\n";
      }

      if ( !empty($_SESSION["outbound_voice"]["account_number"]) ) {
        $text_body .= "Account Number: " . $_SESSION["outbound_voice"]["account_number"] . "\r\n";
      }

      if ( !empty($_SESSION["outbound_voice"]["carrier"]) ) {
        $text_body .= "Carrier: " . $_SESSION["outbound_voice"]["carrier"] . "\r\n";
      }

      if ( !empty($_SESSION["outbound_voice"]["delivery_address"]) ) {
        $text_body .= "Delivery Address: " . $_SESSION["outbound_voice"]["delivery_address"] . "\r\n";
      }

      $service_types = new service_types();
      $service_types->type_id = $plan_title->type_id;
      $service_types->load();

      $text_body .= "Access Technology: " . $service_types->description . "\r\n";
      $text_body .= "Type: " . $_SESSION["outbound_voice"]["kind"] . "\r\n\r\n";
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

    foreach($_REQUEST as $key => $value) {
      $pos = strpos($key , "order_");
      if ($pos === 0){
        unset($_REQUEST[$key]);
      }
    }

    foreach($_SESSION as $key => $value) {
      $pos = strpos($key , "order_");
      if ($pos === 0){
        unset($_SESSION[$key]);
      }
    }

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

$pt->setVar("ORDER_CONTACT_LIST",$contacts->contact_list("order_contact",$contacts_arr));
$pt->setVar("SERVICE_TYPE","Outbound Voice");
$pt->setVar('COMPANY_NAME', $customer->company_name);
$pt->setVar('IDENTIFIER', $_SESSION["outbound_voice"]["service_number"]);
$pt->setVar('DELIVERY_ADDRESS', $_SESSION["outbound_voice"]["delivery_address"]);
$pt->setVar("SERVICE_ID",$service->service_id);

//current plan
$current_plan = new plans();
$current_plan->plan_id = $service->retail_plan_id;
$current_plan->load();

//contract_length
$plan_attributes = new plan_attributes();
$plan_attributes->plan_id = $current_plan->plan_id;
$plan_attributes->param = "contract_length";
$plan_attributes->get_latest();

$pt->setVar("CURRENT_PLAN",$current_plan->description);
$pt->setVar( "CONTRACT_LENGTH", $plan_attributes->value . " years");
// $pt->setVar("NEW_PLAN",$current_plan->description);

$pt->setVar("PAGE_TITLE", "Outbound Voice - Service Creation");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");