<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/inbound_voice/distribute/index.php - Inbound Voice: Distribute
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
include_once "service_temp.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/inbound_voice/distribute/creation/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/inbound_voice/distribute/creation/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/inbound_voice/distribute/creation/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("sod" => "base/manage/services/add/inbound_voice/distribute/sod.html",
					"cd" => "base/manage/services/add/inbound_voice/distribute/cd.html"));

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
  exit();
}

if ( !isset($_REQUEST['sp']) || empty($_REQUEST['sp']) ) {
  echo "URL invalid";
  exit();
}

$session_pointer = $_REQUEST['customer_id'] . "_" . $_REQUEST['sp'];

$service_temp = new service_temp();
$service_temp->data_key = $session_pointer;
$service_temp->load();

$service_data = unserialize($service_temp->data);

if ( !isset($_REQUEST["order_contract_length"]) ) {
  $_REQUEST["order_contract_length"] = "24";
	$pt->setVar("CONTRACT_LENGTH_24", " selected");
}

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

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $customer->wholesaler_id;
$wholesaler->load();

$contacts = new authorised_rep();
$contacts->customer_id = $customer->customer_id;
$contacts_arr = $contacts->get_contacts();

$services = new services();
$services->type_id = 5;

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
    
  if ( $parent_plan->parent_plan_id != 0 ) {
    $pp_id = $parent_plan->parent_plan_id;
  } else {
    $pp_id = $_REQUEST["retail_plan"];
  }

  $services->wholesale_plan_id = $pp_id;
  $services->state = "creation";
  $services->identifier = $service_data["inbound_voice"]["number"];
  $services->tag = $_REQUEST['tag'];
}

if ( isset($_REQUEST['submit2']) ) {
  
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
    
  if ( $parent_plan->parent_plan_id != 0 ) {
    $pp_id = $parent_plan->parent_plan_id;
  } else {
    $pp_id = $_REQUEST["retail_plan"];
  }

  $services->wholesale_plan_id = $pp_id;
  $services->state = "creation";
  $services->identifier = $service_data["inbound_voice"]["number"];
  $services->tag = $_REQUEST['tag'];

  $order_keys = array();
  $sess_keys = array();

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
    $massive_array = array();
    // $services->create();
$massive_array[get_class($services)][] = (array)$services;

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
    // $orders->create();
$massive_array[get_class($orders)][] = (array)$orders;

    //create entry to order_attributes
    for ( $y = 0; $y < count($order_keys); $y++ ) {
      $order_attributes = new order_attributes();
      $order_attributes->order_id = $orders->order_id;
      $order_attributes->param = $order_keys[$y];
      $order_attributes->value = $_REQUEST[$order_keys[$y]];
      if ( $order_attributes->value != "" ) {
        // $order_attributes->create();
$massive_array[get_class($order_attributes)][] = (array)$order_attributes;
      }
    }
    for ( $y = 0; $y < count($sess_keys); $y++ ) {
      $order_attributes = new order_attributes();
      $order_attributes->order_id = $orders->order_id;
      $order_attributes->param = $sess_keys[$y];
      $order_attributes->value = $service_data[$sess_keys[$y]];
      if ( $order_attributes->value != "" ) {
        // $order_attributes->create();
$massive_array[get_class($order_attributes)][] = (array)$order_attributes;
      }
    }

    $order_attributes = new order_attributes();
    $order_attributes->order_id = $orders->order_id;
    $order_attributes->param = "order_tel_account_num";
    $order_attributes->value = $service_data["inbound_voice"]["tel_account_num"];
    if ( $order_attributes->value != "" ) {
      // $order_attributes->create();
$massive_array[get_class($order_attributes)][] = (array)$order_attributes;
    }

    $order_attributes = new order_attributes();
    $order_attributes->order_id = $orders->order_id;
    $order_attributes->param = "order_sod";
    $order_attributes->value = $service_data["inbound_voice"]["sod"];
    if ( $order_attributes->value != "" ) {
      // $order_attributes->create();
$massive_array[get_class($order_attributes)][] = (array)$order_attributes;
    }

    $order_attributes = new order_attributes();
    $order_attributes->order_id = $orders->order_id;
    $order_attributes->param = "order_cd";
    $order_attributes->value = $service_data["inbound_voice"]["cd"];
    if ( $order_attributes->value != "" ) {
      // $order_attributes->create();
$massive_array[get_class($order_attributes)][] = (array)$order_attributes;
    }

    $order_attributes = new order_attributes();
    $order_attributes->order_id = $orders->order_id;
    $order_attributes->param = "order_address";
    $order_attributes->value = $service_data["inbound_voice"]["delivery_address"];
    if ( $order_attributes->value != "" ) {
      // $order_attributes->create();
$massive_array[get_class($order_attributes)][] = (array)$order_attributes;
    }

    //for service_attributes
    $service_attr_keys = array("tel_account_num","sod","cd","contact");
    for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
      $service_attr = new service_attributes();
      $service_attr->service_id = $services->service_id;
      $service_attr->param = $service_attr_keys[$sk];
      if ( isset($service_data["inbound_voice"][$service_attr_keys[$sk]]) ) {
        $service_attr->value = $service_data["inbound_voice"][$service_attr_keys[$sk]];
        // $service_attr->create();
$massive_array[get_class($service_attr)][] = (array)$service_attr;
      } else if (isset($_REQUEST["order_".$service_attr_keys[$sk]])) {
        $service_attr->value = $_REQUEST["order_".$service_attr_keys[$sk]];
        // $service_attr->create();
$massive_array[get_class($service_attr)][] = (array)$service_attr;
      }
    }

    //create entry to orders_states
    $orders_states = new orders_states();
    $orders_states->order_id = $orders->order_id;
    $orders_states->state_name = $orders->status;
    // $orders_states->create();
$massive_array[get_class($orders_states)][] = (array)$orders_states;

$service_data["service_order_summary"] = $massive_array;

$service_temp->data = serialize($service_data);
$service_temp->save();
    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    // if ( $user->class != 'customer' ) {
    //   $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/customers/?customer_id=" . $customer->customer_id;
    // } else if ( $user->class == 'customer' ) {
    //   $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/";
    // }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/summary/?customer_id=" . $customer->customer_id . "&sp=" . $_REQUEST['sp'];

    header("Location: $url");
    exit();   
  
  }
}

if ( $wholesaler->manage_own_plan == "no" ) {

  $plan_groups = new wholesaler_plan_groups();
  $plan_groups->wholesaler_id = $wholesaler->wholesaler_id;
  $plan_groups_list = $plan_groups->get_group_id();

  $retail_plan_list = array();
  for ($i=0; $i < count($plan_groups_list); $i++) { 
    $retail_plan = new plans();
    $retail_plan->type_id = $services->type_id;
    $retail_plan->accessMethod = "";
    $retail_plan->priceZone = "";
    $retail_plan->speed = "";
    $array_plans = $retail_plan->get_wholesaler_plans_by_group($plan_groups_list[$i]["group_id"]);
    for ($j=0; $j < count($array_plans); $j++) { 
      $retail_plan_list[] = $array_plans[$j];
    }
  }
  
  $rp_final = array();

  for ($i=0; $i < count($retail_plan_list); $i++) { 
    $plan_attributes = new plan_attributes();
    $plan_attributes->plan_id = $retail_plan_list[$i]["plan_id"];
    $plan_attributes->param = "contract_length";
    $plan_attributes->get_latest();
    
    if ( $plan_attributes->value == $_REQUEST["order_contract_length"] ) {
      $rp_final[] = $retail_plan_list[$i];
    }
  }

} else {

  $retail_plan = new plans();
  $retail_plan_list = $retail_plan->order_get_all2($services->type_id, $customer->wholesaler_id, "","");

  $rp_final = array();

  for ($i=0; $i < count($retail_plan_list); $i++) { 
    $plan_attributes = new plan_attributes();
    $plan_attributes->plan_id = $retail_plan_list[$i]["plan_id"];
    $plan_attributes->param = "contract_length";
    $plan_attributes->get_latest();
    
    if ( $plan_attributes->value == $_REQUEST["order_contract_length"] ) {
      $rp_final[] = $retail_plan_list[$i];
    }
  }
  
}

if ( count($rp_final) == 0 ) {
  $pt->setVar('RETAIL_PLAN_LIST', "There are no available plans for the contract length selected");
} else {
  $list_ready_w = $retail_plan->retail_plans_list('retail_plan',$rp_final);
  $pt->setVar('RETAIL_PLAN_LIST', $list_ready_w);
}


$pt->setVar('PR_' . strtoupper($services->retail_plan_id) . '_SELECT', ' selected');

if ( isset($_REQUEST["order_contract_length"]) ) {
  $pt->setVar( "CONTRACT_LENGTH_" . $_REQUEST["order_contract_length"], " selected");
}

$pt->setVar("ORDER_CONTACT_LIST",$contacts->contact_list("order_contact",$contacts_arr));

if ( isset($_REQUEST["order_contact"]) ) {
  $pt->setVar("AR_CONTACT_".$_REQUEST["order_contact"]," selected");
}

$pt->setVar("SERVICE_TYPE","Inbound Voice");
$pt->setVar("IDENTIFIER",$service_data["inbound_voice"]["number"]);
$pt->setVar("COMPANY_NAME",$service_data["inbound_voice"]["company_name"]);
$pt->setVar("TEL_ACCT_NUM",$service_data["inbound_voice"]["tel_account_num"]);
$pt->setVar("DIST_NUMBER",$service_data["inbound_voice"]["sod"]);
$pt->setVar("DIST_COMPLEX",$service_data["inbound_voice"]["cd"]);
$pt->setVar("DELIVERY_ADDRESS",$service_data["order_address"]);
$pt->setVar("CUSTOMER_ID",$_REQUEST["customer_id"]);
$pt->setVar('TAG', $services->tag);
$pt->setVar("SP",$_REQUEST['sp']);

$pt->setVar("PAGE_TITLE", "Inbound Voice - Service Creation");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");