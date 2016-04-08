<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/outbound_voice/existing_port/index.php - Inbound Voice: Distribute
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
include_once "plan_attributes.class";
include_once("class.phpmailer.php");
include_once "wholesalers.class";
include_once "wholesaler_plan_groups.class";
include_once "service_temp.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/outbound_voice/existing_port/creation/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/outbound_voice/existing_port/creation/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/outbound_voice/existing_port/creation/index.html"));
  
}

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
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

$services = new services();
$services->type_id = 6;

$realms = new realms();
$realms->wholesaler_id = $customer->wholesaler_id;
$realms->type_id = $services->type_id;
$realms_array = $realms->get_my_realms();
$list_ready_realms = $realms->realm_lists('order_realms',$realms_array);

$contacts = new authorised_rep();
$contacts->customer_id = $customer->customer_id;
$contacts_arr = $contacts->get_contacts();

// print_r($_SESSION["outbound_voice"]);
//for local only
// $_SESSION["outbound_voice"]["kind"] = "SIP Trunk";
// $_SESSION["outbound_voice"]["existing_port"] = "yes";
// $_SESSION["outbound_voice"]["service_number"] = "123123123";
// $_SESSION["outbound_voice"]["account_name"] = "aaaaa";
// $_SESSION["outbound_voice"]["account_number"] = "123123123";
// $_SESSION["outbound_voice"]["carrier"] = "asdsadasd";
// $_SESSION["outbound_voice"]["upload_bill"] = "1";
// $_SESSION["outbound_voice"]["delivery_address"] = "2 WILD DUCK ROAD MIA MIA VIC 0000";
// $_SESSION["outbound_voice"]["simultaneous_calls"] = "5";
// $_SESSION["outbound_voice"]["number_range"] = "yes_20";
// $_SESSION["nbnLocationID"] = "LOC0000000000";
// $_SESSION["telstraLocationID"] = "1111111111111";
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
    
  if ( $parent_plan->parent_plan_id != 0 ) {
    $pp_id = $parent_plan->parent_plan_id;
  } else {
    $pp_id = $_REQUEST["retail_plan"];
  }

  $services->wholesale_plan_id = $pp_id;
  $services->state = "creation";
  $services->identifier = (isset($service_data["outbound_voice"]["service_number"]) ? $service_data["outbound_voice"]["service_number"] : (isset($service_data["outbound_voice"]["nbnLocationID"]) ? $service_data["outbound_voice"]["nbnLocationID"] : ""));
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
  $services->identifier = (isset($service_data["outbound_voice"]["service_number"]) ? $service_data["outbound_voice"]["service_number"] : (isset($service_data["outbound_voice"]["nbnLocationID"]) ? $service_data["outbound_voice"]["nbnLocationID"] : ""));
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
    $massive_array = array();
    $services->parent_service_id = 0;
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
    $order_keys = array("order_contract_length",
                        "order_contact");
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

//     $sess_keys = array("nbnLocationID",
//                         "telstraLocationID");
//     for ( $y = 0; $y < count($sess_keys); $y++ ) {
//       if ( isset($_SESSION[$sess_keys[$y]]) ) {
//         $order_attributes = new order_attributes();
//         $order_attributes->order_id = $orders->order_id;
//         $order_attributes->param ="order_" .  $sess_keys[$y];
//         $order_attributes->value = $_SESSION[$sess_keys[$y]];
//         if ( $order_attributes->value != "" ) {
//           // $order_attributes->create();
// $massive_array[get_class($order_attributes)][] = (array)$order_attributes;
//         }
//       }
//     }

//     $sess_keys = array("nbnLocationID",
//                         "telstraLocationID");
//     for ( $y = 0; $y < count($sess_keys); $y++ ) {
//       if ( isset($_SESSION[$sess_keys[$y]]) ) {
//         $service_attr = new service_attributes();
//         $service_attr->service_id = $services->service_id;
//         $service_attr->param = $sess_keys[$y];
//         $service_attr->value = $_SESSION[$sess_keys[$y]];
//         if ( $service_attr->value != "" ) {
//           // $service_attr->create();
// $massive_array[get_class($service_attr)][] = (array)$service_attr;
//         }
//       }
//     }

    //for order_attributes
    $service_attr_keys = array("service_number",
                                "account_name",
                                "account_number",
                                "carrier",
                                "existing_port",
                                "nbnLocationID",
                                "telstraLocationID");
    for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
      $order_attr = new order_attributes();
      $order_attr->order_id = $orders->order_id;
      $order_attr->param = "order_" . $service_attr_keys[$sk];
      if ( isset($service_data["outbound_voice"][$service_attr_keys[$sk]]) ) {
        $order_attr->value = $service_data["outbound_voice"][$service_attr_keys[$sk]];
        // $order_attr->create();
$massive_array[get_class($order_attr)][] = (array)$order_attr;
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
                                "nbnLocationID",
                                "telstraLocationID");
    for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
      $service_attr = new service_attributes();
      $service_attr->service_id = $services->service_id;
      $service_attr->param = $service_attr_keys[$sk];
      if ( isset($service_data["outbound_voice"][$service_attr_keys[$sk]]) ) {
        $service_attr->value = $service_data["outbound_voice"][$service_attr_keys[$sk]];
        // $service_attr->create();
$massive_array[get_class($service_attr)][] = (array)$service_attr;
      } else if (isset($_REQUEST["order_".$service_attr_keys[$sk]])) {
        $service_attr->value = $_REQUEST["order_".$service_attr_keys[$sk]];
        // $service_attr->create();
$massive_array[get_class($service_attr)][] = (array)$service_attr;
      }
    }

    if ( isset($service_data["outbound_voice"]["upload_bill"]) ) {

      copy($config->docs_dir . '/billing/temp/temp_' . $customer->customer_id, $config->docs_dir . '/billing/' . $services->service_id);
      unlink($config->docs_dir . '/billing/temp/temp_' . $customer->customer_id);

    }

    $service_attr = new service_attributes();
    $service_attr->service_id = $services->service_id;
    $service_attr->param = "address";
    $service_attr->value = $service_data["order_address"];
$massive_array[get_class($service_attr)][] = (array)$service_attr;

    $order_attr = new order_attributes();
    $order_attr->service_id = $services->service_id;
    $order_attr->param = "address";
    $order_attr->value = $service_data["order_address"];
$massive_array[get_class($order_attr)][] = (array)$order_attr;

    //create entry to orders_states
    $orders_states = new orders_states();
    $orders_states->order_id = $orders->order_id;
    $orders_states->state_name = $orders->status;
    // $orders_states->create();
$massive_array[get_class($orders_states)][] = (array)$orders_states;

    //number range
    if ($service_data["outbound_voice"]["existing_port"] == "no") {
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
        // $num_range_service->create();
$massive_array["number_range"][get_class($num_range_service)][] = (array)$num_range_service;

      //create entries order_attributes
        $num_range_order = new orders();
        $num_range_order->service_id = $num_range_service->service_id;
        $num_range_order->start = date("Y-m-d H:i:s");
        $num_range_order->request_type = "number range";
        $num_range_order->action = "new";
        $num_range_order->status = "pending";
        // $num_range_order->create();
$massive_array["number_range"][get_class($num_range_order)][] = (array)$num_range_order;

        //for order_attributes
      $order_attr = new order_attributes();
      $order_attr->order_id = $num_range_order->order_id;
      $order_attr->param = "parent_order";
      $order_attr->value = $orders->order_id;
      // $order_attr->create();
$massive_array["number_range"][get_class($order_attr)][] = (array)$order_attr;

        $service_attr_keys = array("order_address");
        for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
          $order_attr = new order_attributes();
          $order_attr->order_id = $num_range_order->order_id;
          $order_attr->param = "order_" . $service_attr_keys[$sk];
          if ( isset($service_data[$service_attr_keys[$sk]]) ) {
            if ( $service_attr_keys[$sk] == "order_address" ) {
              $order_attr->param = "order_address";
            }
            $order_attr->value = $service_data[$service_attr_keys[$sk]];
            // $order_attr->create();
$massive_array["number_range"][get_class($order_attr)][] = (array)$order_attr;
          }
        }

        $order_keys = array("order_contact");
        for ( $y = 0; $y < count($order_keys); $y++ ) {
          $order_attributes = new order_attributes();
          $order_attributes->order_id = $num_range_order->order_id;
          $order_attributes->param = $order_keys[$y];
          $order_attributes->value = $_REQUEST[$order_keys[$y]];
          if ( $order_attributes->value != "" ) {
            // $order_attributes->create();
$massive_array["number_range"][get_class($order_attributes)][] = (array)$order_attributes;
          }
        }

        //for order_attributes [number_range]
          $order_attr = new order_attributes();
          $order_attr->order_id = $orders->order_id;
          $order_attr->param = "order_number_range";
          $order_attr->value = $num_range_order->order_id;
          // $order_attr->create();
$massive_array["number_range"][get_class($order_attr)][] = (array)$order_attr;

        //create entry to orders_states
        $orders_states = new orders_states();
        $orders_states->order_id = $num_range_order->order_id;
        $orders_states->state_name = $num_range_order->status;
        // $orders_states->create();
$massive_array["number_range"][get_class($orders_states)][] = (array)$orders_states;
    }
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

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/summary/?customer_id=" . $customer->customer_id . "&sp=" . $session_pointer0;

    header("Location: $url");
    exit();   
  
  }
}

if ( $wholesaler->manage_own_plan == "no" ) {

  $plan_groups = new wholesaler_plan_groups();
  $plan_groups->wholesaler_id = $wholesaler->wholesaler_id;
  $plan_groups_list = $plan_groups->get_group_id();

  print_r($plan_groups_list);
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

$pt->setVar("SERVICE_TYPE","Outbound Voice");
$pt->setVar('REALM_OPTION', $list_ready_realms);
$pt->setVar('COMPANY_NAME', $customer->company_name);
$pt->setVar('IDENTIFIER', (isset($service_data["outbound_voice"]["service_number"]) ? $service_data["outbound_voice"]["service_number"] : (isset($service_data["outbound_voice"]["nbnLocationID"]) ? $service_data["outbound_voice"]["nbnLocationID"] : "-") ));
$pt->setVar('DELIVERY_ADDRESS', $service_data["order_address"]);
// $pt->setVar('ORDER_ADDRESS', $service_data["order_address"]);
$pt->setVar("CUSTOMER_ID",$_REQUEST["customer_id"]);
$pt->setVar('TAG', $services->tag);
$pt->setVar("SP",$_REQUEST['sp']);

$pt->setVar("PAGE_TITLE", "Outbound Voice - Service Creation");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");
