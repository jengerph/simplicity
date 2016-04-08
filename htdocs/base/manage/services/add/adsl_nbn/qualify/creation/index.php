<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/adsl_nbn/qualify/creation/index.php - Create a service
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
include_once "../../../getLosingServiceProvider.php";
include_once "service_temp.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/adsl_nbn/qualify/creation/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/adsl_nbn/qualify/creation/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/adsl_nbn/qualify/creation/index.html"));
  
}

$pt->setFile(array("wholesaler_row" => "base/manage/services/add/adsl_nbn/qualify/creation/wholesaler_row.html", 
                    "add_contact" => "base/manage/services/add/adsl_nbn/qualify/creation/add_contact.html", 
                    "churn" => "base/manage/services/add/adsl_nbn/qualify/creation/churn.html",
                    "extra_staticip" => "base/manage/services/edit/extra_staticip.html",
                    "extra_ipblock4" => "base/manage/services/edit/extra_ipblock4.html",
                    "extra_ipblock8" => "base/manage/services/edit/extra_ipblock8.html",
                    "extra_ipblock16" => "base/manage/services/edit/extra_ipblock16.html"));

if ( !isset($_REQUEST['customer_id']) ) {
  echo "Customer ID invalid.";
  exit(1);
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

$customer = new customers();
$customer->customer_id = $_REQUEST['customer_id'];
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
// print_r($_SESSION);
// exit();
//for local testing
// $service_chosen = array(
//                           "subAddressType" => "UNIT",
//                           "subAddressNumber" => "1512",
//                           "streetNumber" => "10",
//                           "streetName" => "FIFTH",
//                           "streetType" => "AVENUE",
//                           "suburb" => "PALM BEACH",
//                           "state" => "QLD",
//                           "postcode" => "4221",
//                           "qualificationID" => "3896463",
//                           "nbnLocationID" => "LOC000097459296",
//                           "dslCodesOnLine" => "1",
//                           "serviceSpeed" => "Up to 20Mbps/1Mbps",
//                           "maximumDownBandwidth" => "20 mbps",
//                           "maximumUpBandwidth" => "1 kbps",
//                           "type" => "Telstra",
//                           "distanceToExchange" => "1911",
//                           "accessMethod" => "Telstra L2IG",
//                           "accessType" => "DSL-L2",
//                           "priceZone" => "Zone 1"
//                         );
// $_SESSION["order_service_number"] = "0755250602";
// $_SESSION['order_service_available'] = "ADSL Telstra L2IG - Zone 1 - Up to 20Mbps/1Mbps";
// $_SESSION['order_address'] = "UNIT 1512 10 FIFTH AVENUE, PALM BEACH QLD 4221";

$temp = explode(" - ", trim($service_data['order_service_available']));
$temp2 = explode(" ", $temp[0]);

$index = $service_data["order_service_available_index"];

$st = new service_types();
$st->description = $temp2[0];
$st->get_type_id();

$service_chosen = clean_array($service_data["service_qualify_array"][$index],$temp);

// if (empty($service_chosen["qualificationID"])) {
//   $service_chosen = clean_array($_SESSION["service_qualify_array"][1],$temp);
// }

$services->type_id = $st->type_id;

$address = "";

foreach ($service_data["service_qualify_array"][$index]["siteAddress"] as $key => $value) {
  $address .= $value . " ";
}

$address = trim($address);

if ( !isset($_REQUEST['order_contract_length']) ) {
  $_REQUEST['order_contract_length'] = 24;
}

//set order_churn
if ( !empty($service_chosen["dslCodesOnLine"]) ) {
  if ( $temp2[0] != "NBN" ) {
    $_REQUEST["order_churn"] = "yes";
    $services->identifier = $service_data["order_service_number"];
  } else {
    $_REQUEST["order_churn"] = "no";
    $services->identifier = $service_chosen["nbnLocationID"];
  }
} else {
  $_REQUEST["order_churn"] = "no";
  if ( $temp2[0] != "NBN" ) {
    $services->identifier = $service_data["order_service_number"];
  } else {
    $services->identifier = $service_chosen["nbnLocationID"];
  }
}

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
  $services->identifier = $services->identifier;
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
  $services->identifier = $services->identifier;
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

  if ( !isset($_REQUEST['order_username']) || $_REQUEST["order_username"] == "" ) {
    $error_order[] = "Invalid Username.";
  } 

  if ( !isset($_REQUEST['order_realms']) || $_REQUEST["order_realms"] == "0" ) {
    $error_order[] = "Invalid realm.";
  }

  if ( isset($_REQUEST['order_username']) && isset($_REQUEST['order_realms']) ) {
    $radcheck = new radius();
    $radcheck->username = $_REQUEST["order_username"] . "@" . $_REQUEST["order_realms"];
    $radcheck->user_exists();

    if ( isset($radcheck->id) ) {
      $error_order[] = "Username exists.";
    }

  }

  if ( !isset($_REQUEST['order_password']) || $validate->password($_REQUEST['order_password']) == 0 || $_REQUEST["order_password"] == "" ) {
    $error_order[] = "Invalid Password.";
  }

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
    
    $keys = array_keys($service_chosen);

    for ($x = 0; $x < count($service_chosen); $x++ ) {
      //create order_attributes
        create( $orders->order_id, $keys[$x], $service_chosen[$keys[$x]]);
    }

    //for service_attributes
    $service_attr_keys = array("username","realms","password","contract_length","contact","address","type","accessType","accessMethod","serviceSpeed","priceZone");
    for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
      $service_attr = new service_attributes();
      $service_attr->service_id = $services->service_id;
      $service_attr->param = $service_attr_keys[$sk];

      if ( $service_attr_keys[$sk] == "serviceSpeed" ) {
        $service_attr->param = "accessSpeed";
      }

      if ( $service_attr_keys[$sk] == 'address' ) {
        $service_attr->value = $address;
        // $service_attr->create();
      } else if ( isset($service_data["order_".$service_attr_keys[$sk]]) ) {
        $service_attr->value = $service_data["order_".$service_attr_keys[$sk]];
        // $service_attr->create();
      } else if ( isset($_REQUEST["order_".$service_attr_keys[$sk]]) ) {
        $service_attr->value = $_REQUEST["order_".$service_attr_keys[$sk]];
        // $service_attr->create();
      } else if ( isset($service_chosen[$service_attr_keys[$sk]]) ) {
        $service_attr->value = $service_chosen[$service_attr_keys[$sk]];
        // $service_attr->create();
      }
$massive_array[get_class($service_attr)][] = (array)$service_attr;
    }
    
    //set shape status
    if ( $services->type_id == 1 || $services->type_id == 2 ) {
      $status_shape = new service_attributes();
      $status_shape->service_id = $services->service_id;
      $status_shape->param = "shape_status";
      $status_shape->value = "0";
      // $status_shape->create();
$massive_array[get_class($status_shape)][] = (array)$status_shape;
    }

    //create entry to orders_states
    $orders_states = new orders_states();
    $orders_states->order_id = $orders->order_id;
    $orders_states->state_name = $orders->status;
    // $orders_states->create();
$massive_array[get_class($orders_states)][] = (array)$orders_states;

    //create entries to radcheck and radusergroup
    $radius = new radius();
    $radius->service_id = $services->service_id;
    $radius->username = $_REQUEST["order_username"] . "@" . $_REQUEST["order_realms"];
    $radius->password = $_REQUEST["order_password"];
    // $radius->create();
$massive_array[get_class($radius)][] = (array)$radius;

    //create order adons
    $service_type = new service_types();
    $service_type->type_id = $services->type_id;
    $service_type->load();

    $extras = array("staticip","ipblock4","ipblock8","ipblock16");

    $create_for_addon = 0;

    for ($b=0; $b < count($extras); $b++) {

      if ( isset($_REQUEST[$extras[$b]]) ) {
        $create_for_addon = 1;
      }
    }

    if ( $create_for_addon == 1 ) {
      $addon_order = new orders();
      $addon_order->service_id = $services->service_id;
      $addon_order->request_type = strtolower($service_type->description);
      $addon_order->action = "addon create";
      $addon_order->status = "pending";
      $addon_order->start = date("Y-m-d H:i:s");
      // $addon_order->create();
$massive_array["addon"][get_class($addon_order)][] = (array)$addon_order;

      //create entries order_attributes
      $order_attr = new order_attributes();
      $order_attr->order_id = $addon_order->order_id;
      $order_attr->param = "parent_order";
      $order_attr->value = $orders->order_id;
      // $order_attr->create();
$massive_array["addon"][get_class($order_attr)][] = (array)$order_attr;

      $order_attr = new order_attributes();
      $order_attr->order_id = $addon_order->order_id;
      $order_attr->param = "order_address";
      if ( isset($address) ) {
        $order_attr->value = $address;
      }
      // $order_attr->create();
$massive_array["addon"][get_class($order_attr)][] = (array)$order_attr;

      $order_attr = new order_attributes();
      $order_attr->order_id = $addon_order->order_id;
      $order_attr->param = "order_contact";
      if ( isset($_REQUEST["order_contact"]) ) {
        $order_attr->value = $_REQUEST["order_contact"];
      }
      // $order_attr->create();
$massive_array["addon"][get_class($order_attr)][] = (array)$order_attr;

      for ($a=0; $a < count($extras); $a++) { 
        $order_attr = new order_attributes();
        $order_attr->order_id = $addon_order->order_id;
        $order_attr->param = "order_" . $extras[$a];

        $service_attr = new service_attributes();
        $service_attr->service_id = $services->service_id;
        $service_attr->param = $extras[$a];

        if ( isset($_REQUEST[$extras[$a]]) ) {
          $order_attr->value = "activated";
          // $order_attr->create();
$massive_array["addon"][get_class($order_attr)][] = (array)$order_attr;

          $service_attr->value = "activated";
          // $service_attr->create();
$massive_array["addon"][get_class($service_attr)][] = (array)$service_attr;
        }
      }

      $orders_states = new orders_states();
      $orders_states->order_id = $addon_order->order_id;
      $orders_states->state_name = $addon_order->status;
      // $orders_states->create();
$massive_array["addon"][get_class($orders_states)][] = (array)$orders_states;
    }

$massive_array["service_chosen"] = $service_chosen;

$service_data["service_order_summary"] = $massive_array;
$service_data["order_address"] = $address;

$service_temp = new service_temp();
$service_temp->data_key = $session_pointer;
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

    // $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/?service_id=" . $services->service_id;
    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/summary/?customer_id=" . $customer->customer_id . "&sp=" . $_REQUEST['sp'];

    header("Location: $url");
    exit();   
  
  }
}

//provide losing provider
$array = getProvider();
// $array = array("Provider 1");

$losingProviderList = new misc();
// sort($array);
$losingProviderList_arr = $losingProviderList->make_dropdown("order_churn_provider",$array,"_PROVIDER","Provider");
$pt->setVar("PROVIDER_LIST",$losingProviderList_arr);
// print_r($losingProviderList_arr);
// exit();

$pt->setVar("CUSTOMER_ID", $customer->customer_id);
$pt->setVar("SP",$_REQUEST['sp']);

if ( isset($_REQUEST["order_username"]) ) {
  $pt->setVar("ORDER_USERNAME", $_REQUEST["order_username"]);
}
if ( isset($_REQUEST["order_password"]) ) {
  $pt->setVar("ORDER_PASSWORD", $_REQUEST["order_password"]);
}

if ( $_REQUEST["order_churn"] == "yes" ) {
    $pt->parse("CHURN",'churn','true');
  }

if ( isset($_REQUEST["order_churn"]) ) {
  $pt->setVar('ORDER_CHURN_' . strtoupper($_REQUEST["order_churn"]), ' checked');
}
if ( isset($_REQUEST["order_churn_provider"]) ) {
  // print_r($_REQUEST["order_churn_provider"]);
  $_REQUEST["order_churn_provider"] = str_replace(" ", "_", $_REQUEST["order_churn_provider"]);
  $pt->setVar(strtoupper($_REQUEST["order_churn_provider"])."_PROVIDER_SELECT", ' selected');
}

$pt->setVar('TYPE', $services->type_id);
$pt->setVar('WHOLESALE_PLAN', $services->wholesale_plan_id);
$pt->setVar('RETAIL_PLAN', $services->retail_plan_id);
$pt->setVar('STATE', ucfirst($services->state));
$pt->setVar('IDENTIFIER', $services->identifier);
$pt->setVar('TAG', $services->tag);
// $pt->setVar('ORDER_SERVICE_NUMBER', $_SESSION["order_service_number"]);

$pt->setVar('ORDER_ADDRESS', $address);
// $pt->setVar('STATE_' . strtoupper($services->state) . '_SELECT', ' selected');
$pt->setVar('CONTRACT_LENGTH_' . $_REQUEST['order_contract_length'], ' selected');


$contacts = new authorised_rep();
$contacts->customer_id = $customer->customer_id;
$contacts_arr = $contacts->get_contacts();
$pt->setVar("ORDER_CONTACT_LIST",$contacts->contact_list("order_contact",$contacts_arr));

if ( isset($_REQUEST["order_contact"]) ) {
  $pt->setVar("AR_CONTACT_".$_REQUEST["order_contact"]," selected");
}

//Get a list of services
$services2 = new service_types();
$services2->type_id = $services->type_id;
$services2->load();

$pt->setVar('SERVICE_TYPE_LIST', $services2->description);

//Get a list of retail_plans
if ( $service_chosen["priceZone"] == "Zone 3" || $service_chosen["priceZone"] == "Zone 2" ) {
  $service_chosen["priceZone"] = "Zone 2/3";
}
if ( $service_chosen["priceZone"] == "Metro" || $service_chosen["priceZone"] == "Regional" ) {
  $service_chosen["priceZone"] = "";
}

if ( $wholesaler->manage_own_plan == "no" ) {

  $plan_groups = new wholesaler_plan_groups();
  $plan_groups->wholesaler_id = $wholesaler->wholesaler_id;
  $plan_groups_list = $plan_groups->get_group_id();

  $retail_plan_list = array();
  for ($i=0; $i < count($plan_groups_list); $i++) { 
    $retail_plan = new plans();
    $retail_plan->type_id = $services2->type_id;
    $retail_plan->accessMethod = $service_chosen["accessMethod"];
    $retail_plan->priceZone = $service_chosen["priceZone"];
    $retail_plan->speed = $temp[count($temp)-1];
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
  $retail_plan_list = $retail_plan->order_get_all2($services2->type_id, $customer->wholesaler_id, $temp[count($temp)-1],$service_chosen["priceZone"]);

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

if ( $user->class != 'customer' ) {
  $pt->parse("WHOLESALER_ROW","wholesaler_row","true");
}

$realms = new realms();
$realms->wholesaler_id = $customer->wholesaler_id;
$realms->type_id = $services2->type_id;
$realms_array = $realms->get_my_realms();
$list_ready_w = $realms->realm_lists('order_realms',$realms_array);

$pt->setVar('REALM_OPTION', $list_ready_w);

if ( isset($_REQUEST['order_realms']) ) {  
  $key = $_REQUEST['order_realms'];
  $key = str_replace('.', '', $key);
  $key = strtoupper($key);

  $pt->setVar('REALM_' . strtoupper($key) . '_SELECT', ' selected');
}

//for addon
$extra = array();
$plan_extras = new plan_extras();
if ( isset($_REQUEST["retail_plan"]) ) {
  $plan_extras->plan_id = $_REQUEST["retail_plan"];
}
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

// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function create($id,$param,$value){

      $attributes = new order_attributes();
      $attributes->order_id = $id;
      $attributes->param = "order_".$param;
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
          $var =  $temp[count($temp)-1];
          $type = explode(" ", $temp[0]);
          if ( $type[0] == "NBN" ) {
            $type2 = $type[1];
          } else {
            $type2 = $temp[1];
          }
          if ( preg_match("#$var#",$array["results"][$get_service_type[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed[$b]->serviceSpeed) && ($array2[$a] == $type2) ) {
            $result["serviceSpeed"] = $array["results"][$get_service_type[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed[$b]->serviceSpeed;
            $parent = $array2[$a];
          }
        }
      } else {
        $var =  $temp[count($temp)-1];
        if ( preg_match("#$var#",$array["results"][$get_service_type[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed->serviceSpeed)) {
            $result["serviceSpeed"] = $array["results"][$get_service_type[0]][$array2[$a]]["availableServiceSpeeds"]->serviceSpeed->serviceSpeed;
            $parent = $array2[$a];
          }
      }
    }
  }

$result["maximumDownBandwidth"] = $array["results"][$get_service_type[0]][$parent]["maximumDownBandwidth"]->value  . " " . $array["results"][$get_service_type[0]][0]["maximumDownBandwidth"]->quantifier;
$result["maximumUpBandwidth"] = $array["results"][$get_service_type[0]][$parent]["maximumUpBandwidth"]->value  . " " . $array["results"][$get_service_type[0]][0]["maximumUpBandwidth"]->quantifier;
$result["type"] = $array["results"][$get_service_type[0]][$parent]["type"];
$result["distanceToExchange"] = $array["results"][$get_service_type[0]][$parent]["distanceToExchange"];
$result["accessMethod"] = $array["results"][$get_service_type[0]][$parent]["accessMethod"];
$result["accessType"] = $array["results"][$get_service_type[0]][$parent]["accessType"];
$result["priceZone"] = $array["results"][$get_service_type[0]][$parent]["priceZone"];

return $result;
}

function get_attribute($order_id,$param){

  $email_order_attr = new order_attributes();
  $email_order_attr->order_id = $order_id;
  $email_order_attr->param = $param;
  $email_order_attr->get_latest();

  return $email_order_attr->value;
}