<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/adsl_nbn/order/qualify/confirm/index.php - Create a service
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../../../../setup.inc";

include "../../../../../../../doauth.inc";

include_once("class.phpmailer.php");

include_once "services.class";
include_once "service_types.class";
include_once "service_attributes.class";
include_once "plans.class";
include_once "plan_attributes.class";
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
include_once "../../../../../add/getLosingServiceProvider.php";

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
  
  $pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/adsl_nbn/order/qualify/confirm/index.html"));

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
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/adsl_nbn/order/qualify/confirm/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/adsl_nbn/order/qualify/confirm/index.html"));
  
}

$pt->setFile(array("wholesaler_row" => "base/manage/services/edit/adsl_nbn/order/qualify/confirm/wholesaler_row.html","retailer_row" => "base/manage/services/edit/adsl_nbn/order/qualify/confirm/retailer_row.html","churn" => "base/manage/services/edit/adsl_nbn/order/qualify/confirm/churn.html", "add_contact" => "base/manage/services/add/qualify/creation/add_contact.html"));

$customer = new customers();
$customer->customer_id = $services->customer_id;
$customer->load();

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $customer->wholesaler_id;
$wholesaler->load();

$temp = explode(" - ", trim($_SESSION['order_service_available']));
$temp2 = explode(" ", $temp[0]);

$st = new service_types();
$st->description = $temp2[0];
$st->get_type_id();

$service_chosen = clean_array($_SESSION["service_qualify_array"][0],$temp);

if (empty($service_chosen["qualificationID"])) {
  $service_chosen = clean_array($_SESSION["service_qualify_array"][1],$temp);
}

$order = new orders();
$order->service_id = $services->service_id;
$order->get_latest_orders();

//set order_churn
if ( !empty($service_chosen["dslCodesOnLine"]) ) {
  if ( $temp2[0] != "NBN" ) {
    $_REQUEST["order_churn"] = "yes";
    $services->identifier = $_SESSION["order_service_number"];
  } else {
    $_REQUEST["order_churn"] = "no";
    $services->identifier = $service_chosen["nbnLocationID"];
  }
} else {
  $_REQUEST["order_churn"] = "no";
  if ( $temp2[0] != "NBN" ) {
    $services->identifier = $_SESSION["order_service_number"];
  } else {
    $services->identifier = $service_chosen["nbnLocationID"];
  }
}

if ( !isset($_REQUEST['order_contract_length']) ) {
  $_REQUEST['order_contract_length'] = 0;
}

if ( isset($_REQUEST['submit']) ) {
  
  //set username, password, and realms
  $order_attributes = new order_attributes();
  $order_attributes->order_id = $order->order_id;
  $order_attributes->param = "order_username";
  $order_attributes->get_attribute();

  $_REQUEST["order_username"] = $order_attributes->value;

  $order_attributes = new order_attributes();
  $order_attributes->order_id = $order->order_id;
  $order_attributes->param = "order_realms";
  $order_attributes->get_attribute();

  $_REQUEST["order_realms"] = $order_attributes->value;

  $order_attributes = new order_attributes();
  $order_attributes->order_id = $order->order_id;
  $order_attributes->param = "order_password";
  $order_attributes->get_attribute();

  $_REQUEST["order_password"] = $order_attributes->value;

  $start_date = date("Y-m-d");
  $time = strtotime($start_date);
  $length = intval($_REQUEST['order_contract_length']);
  if ( $length == 0 ) { $length = $length + 1; }
  $final = date("Y-m-d", strtotime("+" . $length . " month -1 day", $time));


  $order_keys = array();
  $sess_keys = array();
  $sess_keys2 = array();

  foreach($_REQUEST as $key => $value) {
    $pos = strpos($key , "order_");
    if ($pos === 0){
      $order_keys[] = $key;
    }
  }

  foreach($_SESSION as $key => $value) {
    $pos = strpos($key , "order_");
    if ($pos === 0){
      $sess_keys[] = $key;
    }
  }

  foreach($_SESSION as $key => $value) {
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

  if ( $_REQUEST["order_churn"] == 'yes' ) {
    if ( !isset($_REQUEST["order_churn_provider"]) || $_REQUEST["order_churn_provider"] == "0" ) {
      $error_order[] = "Provider invalid.";
    }
  }

    if ( !isset($_REQUEST["order_contact"]) || $_REQUEST["order_contact"] == "" ) {
      $error_order[] = "Authorized Contact invalid.";
    }

  $vc = $services->validate();

  if ( count($error_order) > 0 ) {

    $pt->setVar('ERROR_MSG','Error: ' . $error_order[0]);

  } else if ($vc != 0) {
  
    $pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

  } else {
    
    // $services->save();

    create( $services->service_id, "edit_identifier", $services->identifier);

    $service_type = new service_types();
    $service_type->type_id = $services->type_id;
    $service_type->load();

    //create entry to orders
    $orders = new orders();
    $orders->service_id = $services->service_id;
    $orders->start = date("Y-m-d H:i:s");
    $orders->request_type = strtolower($service_type->description);
    $orders->action = $_SESSION["action_order"];
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
      $order_attributes->value = $_SESSION[$sess_keys[$y]];
      if ( $order_attributes->value != "" ) {
        $order_attributes->create();
      }
    }
    for ( $y = 0; $y < count($sess_keys2); $y++ ) {
      $order_attributes = new order_attributes();
      $order_attributes->order_id = $orders->order_id;
      $order_attributes->param = $sess_keys2[$y];
      $order_attributes->value = $_SESSION[$sess_keys2[$y]];
      if ( $order_attributes->value != "" ) {
        $order_attributes->create();
      }
    }

    $keys = array_keys($service_chosen);
    for ($x = 0; $x < count($service_chosen); $x++ ) {
      //create service_attributes
      if( isset($service_chosen[$keys[$x]]) ) {
        create( $orders->order_id, "edit_" . $keys[$x], $service_chosen[$keys[$x]]);
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
      $text_body .= "Username: " . $_REQUEST['order_username'] . "@" . $_REQUEST['order_realms'] . "\r\n\r\n";
      $text_body .= "Password: " . $_REQUEST['order_password'] . "\r\n\r\n";
      $text_body .= "Order Reference: " . $orders->order_id . "\r\n\r\n";
      $text_body .= "Below is a list of item(s) on this order. " . "\r\n\r\n";

      $plan_title = new plans();
      $plan_title->plan_id = $services->retail_plan_id;
      $plan_title->load();

      $text_body .= "Type of Order: " . ucwords($orders->action) . "- ADSL or NBN Service\r\n\r\n";
      $text_body .= "Service Components:\r\n\r\n";
      $text_body .= "Service ID: " . $services->service_id . "\r\n";
      $text_body .= "Transaction Type: " . ucwords($orders->action) . "\r\n";
      $text_body .= "Customer Account Number: " . ucwords($customer->customer_id) . "\r\n";

      $contract_length = new plan_attributes();
      $contract_length->plan_id = $plan_title->plan_id;
      $contract_length->param = "contract_length";
      $contract_length->get_latest();

      $text_body .= "Contract Term (Months): " . $contract_length->value . "\r\n\r\n";

      $text_body .= "Service Number: " . get_attribute( $orders->order_id, "order_service_number" ) . "\r\n";
      $text_body .= "Access Location: " . get_attribute( $orders->order_id, "order_address" ) . "\r\n";
      $text_body .= "Access Method: " . $plan_title->access_method . "\r\n";

      $service_types = new service_types();
      $service_types->type_id = $plan_title->type_id;
      $service_types->load();

      $text_body .= "Access Technology: " . $service_types->description . "\r\n";
      $text_body .= "Access Speed: Up to " . $plan_title->speed . "\r\n\r\n";
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

    foreach($_SESSION as $key => $value) {
      $pos = strpos($key , "action_order");
      if ($pos === 0){
        unset($_SESSION[$key]);
      }
    }

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

$pt->setVar("SERVICE_ID", $services->service_id);

$con_count = 0;

foreach($_REQUEST as $key => $value) {
  $pos = strpos($key , "order_churn_contact_");
  if ($pos === 0){
    $con_count = $con_count + 1;
  }
}

if ( $_REQUEST["order_churn"] == "yes" ) {
    $pt->parse("CHURN",'churn','true');
  }

//provide losing provider
$array = getProvider();

$losingProviderList = new misc();
// sort($array);
$losingProviderList_arr = $losingProviderList->make_dropdown("order_churn_provider",$array,"_PROVIDER","Provider");
$pt->setVar("PROVIDER_LIST",$losingProviderList_arr);
// print_r($losingProviderList_arr);
// exit();

// if ( isset($_REQUEST["order_churn_contact_0"]) ) {
//   $pt->setVar("ORDER_CHURN_CONTACT_0", $_REQUEST["order_churn_contact_0"]);
// }
// if ( isset($_REQUEST["order_churn_contact_num_0"]) ) {
//   $pt->setVar("ORDER_CHURN_CONTACT_NUM_0", $_REQUEST["order_churn_contact_num_0"]);
// }
if ( isset($_REQUEST["order_churn"]) ) {
  $pt->setVar('ORDER_CHURN_' . strtoupper($_REQUEST["order_churn"]), ' checked');
}

if ( isset($_REQUEST["order_churn_provider"]) ) {
  // print_r($_REQUEST["order_churn_provider"]);
  $_REQUEST["order_churn_provider"] = str_replace(" ", "_", $_REQUEST["order_churn_provider"]);
  $pt->setVar(strtoupper($_REQUEST["order_churn_provider"])."_PROVIDER_SELECT", ' selected');
}

$pt->setVar('TYPE', $services->type_id);
// $pt->setVar('WHOLESALE_PLAN', $services->wholesale_plan_id);
$pt->setVar('STATE', ucfirst($services->state));
$pt->setVar('IDENTIFIER', $services->identifier);
$pt->setVar('TAG', $services->tag);
$pt->setVar('ORDER_ADDRESS', $_SESSION["order_address"]);
// $pt->setVar('STATE_' . strtoupper($services->state) . '_SELECT', ' selected');
$con_end = strtotime($services->contract_end);
$new_con_end = date("d/m/Y",$con_end);
$pt->setVar("CONTRACT_END", $new_con_end);

$contacts = new authorised_rep();
$contacts->customer_id = $customer->customer_id;
$contacts_arr = $contacts->get_contacts();
$pt->setVar("ORDER_CONTACT_LIST",$contacts->contact_list("order_contact",$contacts_arr));

$pt->setVar("AR_CONTACT_".$_REQUEST["order_contact"]," selected");

if ( isset($_SESSION["edit_retail_plan"]) ) {
$new_retail_plan = new plans();
$new_retail_plan->plan_id = $_SESSION["edit_retail_plan"];
$new_retail_plan->load();
$pt->setVar('RETAIL_PLAN', $new_retail_plan->description);
$pt->parse('RETAILER_ROW','retailer_row','true');
}

if ( isset($_SESSION['edit_wholesale_plan']) ) {
  $new_wholesale_plan = new plans();
  $new_wholesale_plan->plan_id = $_SESSION["edit_wholesale_plan"];
  $new_wholesale_plan->load();
  $pt->setVar('WHOLESALE_PLAN', $new_wholesale_plan->description);
}

//Get a list of wholesalers
$services2 = new service_types();
$services2->type_id = $services->type_id;
$services2->load();

$pt->setVar('SERVICE_TYPE', $services2->description);

//Get a list of wholesaler_plans
$wholesale_plan = new plans();
$wholesale_plan_list = $wholesale_plan->order_get_all4($services2->type_id, '1');
$list_ready_w = $wholesale_plan->plans_list('wholesale_plan',$wholesale_plan_list);

$pt->setVar('WHOLESALE_PLAN_LIST', $list_ready_w);

$pt->setVar('P_' . strtoupper($services->wholesale_plan_id) . '_SELECT', ' selected');

if ( $user->class != 'customer' && isset($_SESSION["edit_wholesale_plan"])) {
  $pt->parse("WHOLESALER_ROW","wholesaler_row","true");
}

$realms = new realms();
$realms->wholesaler_id = $customer->wholesaler_id;
$realms->type_id = $services->type_id;
$realms_array = $realms->get_my_realms();
$list_ready_w = $realms->realm_lists('order_realms',$realms_array);

$pt->setVar('REALM_OPTION', $list_ready_w);

if ( isset($_REQUEST['order_realms']) ) {  
  $key = $_REQUEST['order_realms'];
  $key = str_replace('.', '', $key);
  $key = strtoupper($key);

  $pt->setVar('REALM_' . strtoupper($key) . '_SELECT', ' selected');
}

$order = new orders();
$order->service_id = $services->service_id;
$order->get_latest_orders();

$order_attributes = new order_attributes();
$order_attributes->order_id = $order->order_id;
$order_attributes->param = "order_username";
$order_attributes->get_attribute();

$username = $order_attributes->value;

$order_attributes = new order_attributes();
$order_attributes->order_id = $order->order_id;
$order_attributes->param = "order_realms";
$order_attributes->get_attribute();

$username .= "@" . $order_attributes->value;

$order_attributes = new order_attributes();
$order_attributes->order_id = $order->order_id;
$order_attributes->param = "order_password";
$order_attributes->get_attribute();

$pt->setVar("ORDER_USERNAME",$username);
$pt->setVar("ORDER_PASSWORD",$order_attributes->value);

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

function clean_array($array,$temp){

  $param = array("subAddressType",
                  "subAddressNumber",
                  "streetNumber",
                  "streetName",
                  "streetType",
                  "suburb",
                  "state",
                  "postcode",
                  "qualificationID",
                  "nbnLocationID",
                  "dslCodesOnLine",
                  "type",
                  "distanceToExchange",
                  "accessMethod",
                  "accessType",
                  "priceZone",
                  "serviceSpeed");
  $result = array();

  //get info for siteAddress
  for ($i=0; $i < count($array["siteAddress"]); $i++) { 
    if ( is_object($array["siteAddress"]) ) {
      for ($j=0; $j < count($param); $j++) { 
        if ( isset($array["siteAddress"]->{$param[$j]}) ) {
          $result[$param[$j]] = $array["siteAddress"]->{$param[$j]};
        }
      }
    }
  }

  //get info for qualificationID,nbnLocationID,dslCodesOnLine
  for ($k=0; $k < count($param); $k++) { 
    if ( isset($array[$param[$k]]) && !is_array($array[$param[$k]])){
      $result[$param[$k]] = $array[$param[$k]];
    }
  }

  //get info for results
  $array_keys = array_keys($array["results"]);
  $get_service_type = explode(" ", $temp[0]);
  $array1 = array($get_service_type[0]);
  $unset = array_diff($array_keys, $array1);
  foreach ($unset as $key => $value) {
    unset($array["results"][$value]);
  }

  $array2 = array_keys($array["results"][$get_service_type[0]]);

  //find serviceSpeed
  for ($a=0; $a < count($array2); $a++) { 
    if ( is_array($array["results"][$get_service_type[0]][$array2[$a]]) ) {
      if ( is_object($array["results"][$get_service_type[0]][$array2[$a]]) ) {
        $value = (array)$array["results"][$get_service_type[0]][$array2[$a]];
      } else{
        $value = $array["results"][$get_service_type[0]][$array2[$a]];
      }
      if ( is_array($array["results"][$get_service_type[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed) ) {
        for ($b=0; $b < count($array["results"][$get_service_type[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed); $b++) { 
          // print_r($array["results"][$temp[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed[$b]->serviceSpeed);
          $var =  $temp[count($temp)-1];
          // exit();
          // print_r(preg_match("#$var#",$array["results"][$temp[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed[$b]->serviceSpeed));
          if ( preg_match("#$var#",$array["results"][$get_service_type[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed[$b]->serviceSpeed) && ($array2[$a] == $temp[1]) ) {
            $result["serviceSpeed"] = $array["results"][$get_service_type[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed[$b]->serviceSpeed;
            $parent = $array2[$a];
          }
          // print_r($temp[count($temp)-1]);
        }
      } else {
        $var =  $temp[count($temp)-1];
        // print_r(preg_match("#$var#",$array["results"][$temp[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed->serviceSpeed));
        if ( preg_match("#$var#",$array["results"][$get_service_type[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed->serviceSpeed)) {
            $result["serviceSpeed"] = str_replace("Up to ", "", $array["results"][$get_service_type[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed->serviceSpeed);
            $parent = $array2[$a];
          }
      }
      // exit();
    }
  }

$result["maximumDownBandwidth"] = $array["results"][$get_service_type[0]][$parent]["maximumDownBandwidth"]->value  . " " . $array["results"][$get_service_type[0]][0]["maximumDownBandwidth"]->quantifier;
$result["maximumUpBandwidth"] = $array["results"][$get_service_type[0]][$parent]["maximumUpBandwidth"]->value  . " " . $array["results"][$get_service_type[0]][0]["maximumUpBandwidth"]->quantifier;
$result["type"] = $array["results"][$get_service_type[0]][$parent]["type"];
$result["distanceToExchange"] = $array["results"][$get_service_type[0]][$parent]["distanceToExchange"];
$result["accessMethod"] = $array["results"][$get_service_type[0]][$parent]["accessMethod"];
$result["accessType"] = $array["results"][$get_service_type[0]][$parent]["accessType"];
$result["priceZone"] = $array["results"][$get_service_type[0]][$parent]["priceZone"];

// print_r($array["results"][$temp[0]][$parent]);
// print_r($result);
// exit();
return $result;
}

function get_attribute($order_id,$param){

  $email_order_attr = new order_attributes();
  $email_order_attr->order_id = $order_id;
  $email_order_attr->param = $param;
  $email_order_attr->get_latest();

  return $email_order_attr->value;
}