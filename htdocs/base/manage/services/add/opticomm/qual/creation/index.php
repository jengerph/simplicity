<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/opticomm/qual/creation/index.php - Create order
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

if (!isset($_REQUEST['qual_id'])) {
	
	// NO qual id provided
	echo "No qualifcation id provided.";
	exit();
}

$pt->setVar('QUAL_ID', $_REQUEST['qual_id']);

if (!isset($_SESSION['qual_' . $_REQUEST['qual_id']])) {
	
	// Invalid qual
	echo "No qualifcation id provided.";
	exit();
}

$qual = $_SESSION['qual_' . $_REQUEST['qual_id']];

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html"));
  
}

$pt->setFile(array( "main" => "base/manage/services/add/opticomm/qual/creation/index.html", 
                    "wholesaler_row" => "base/manage/services/add/opticomm/qual/creation/wholesaler_row.html",
                    "add_contact" => "base/manage/services/add/opticomm/qual/creation/add_contact.html",
                    "churn" => "base/manage/services/add/opticomm/qual/creation/churn.html",
                    "fttn" => "base/manage/services/add/opticomm/qual/creation/fttn.html",
                    "extra_staticip" => "base/manage/services/edit/extra_staticip.html",
                    "extra_ipblock4" => "base/manage/services/edit/extra_ipblock4.html",
                    "extra_ipblock8" => "base/manage/services/edit/extra_ipblock8.html",
                    "extra_ipblock16" => "base/manage/services/edit/extra_ipblock16.html"));
                    
$customer = new customers();
$customer->customer_id = $qual['customer_id'];
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

$pt->setVar('ORDER_ADDRESS', $qual['address']);
$pt->setVar('IDENTIFER', $qual['location_id']);
$pt->setVar('MANUAL', $qual['manual']);


$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $customer->wholesaler_id;
$wholesaler->load();
$services = new services();
$services->type_id = 8; //Opticomm type id

// Save values
if (isset($_REQUEST['retail_plan'])) {
	$services->retail_plan_id = $_REQUEST['retail_plan'];
}
if (isset($_REQUEST['order_churn'])) {
	$services->retail_plan_id = $_REQUEST['retail_plan'];
	$pt->setVar('ORDER_CHURN_' . strtoupper($_REQUEST["order_churn"]), ' checked');
}

// FORM PROCESS
if (isset($_REQUEST['submit2'])) {
	
	// Lookup Service Type
  $service_type = new service_types();
  $service_type->type_id = $services->type_id;
  $service_type->load();
  
  // Setup dates
  $start_date = date("Y-m-d");
  $time = strtotime($start_date);
  $length = intval($_REQUEST['order_contract_length']);
  if ( $length == 0 ) { $length = $length + 1; }
  $final = date("Y-m-d", strtotime("+" . $length . " month -1 day", $time));
  
  // Setup Service Object
  $error_msg = '';
  $services->customer_id = $customer->customer_id;
  $services->start_date = $start_date . " 00:00:00";
  $services->contract_end = $final . " 00:00:00";
  $services->retail_plan_id = $_REQUEST['retail_plan'];
  
  // Lookup Parent plan
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
  $services->identifier = $qual['location_id'];
  $services->tag = $_REQUEST['tag'];
  
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

  if ( !isset($_REQUEST["order_contact"]) || $_REQUEST["order_contact"] == "" ) {
  	$error_order[] = "Authorized Contact invalid.";
  }

  $vc = $services->validate();
  
  if ( count($error_order) > 0 ) {
  
  	$pt->setVar('ERROR_MSG','Error: ' . $error_order[0]);
  
  } else if ($vc != 0) {
  
  	$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);
  
  } else {
  	
  	// Create the service
  	if ($services->create()) {
  		
  		// Created
  		
  		// Lets set the service attributes	  	
  		$service_attr = new service_attributes();
  		$service_attr->service_id = $services->service_id;
  		
  		// Username
	  	$service_attr->param = "username";
  		$service_attr->value = $_REQUEST['order_username'];
  		$service_attr->create();

  		// Realms
	  	$service_attr->param = "realms";
  		$service_attr->value = $_REQUEST["order_realms"];
  		$service_attr->create();
  		
  		// Password
	  	$service_attr->param = "password";
  		$service_attr->value = $_REQUEST["order_password"];
  		$service_attr->create();
  		
  		// contract_length
	  	$service_attr->param = "contract_length";
  		$service_attr->value = $_REQUEST["order_contract_length"];
  		$service_attr->create();

  		// contact
	  	$service_attr->param = "contact";
  		$service_attr->value = $_REQUEST["order_contact"];
  		$service_attr->create();
  		
  		// address
	  	$service_attr->param = "address";
  		$service_attr->value = $address;
  		$service_attr->create();

  		// type
	  	$service_attr->param = "type";
  		$service_attr->value = $qual['quals'][$_REQUEST['result_id']]['type'];
  		$service_attr->create();

  		// accessType
	  	$service_attr->param = "accessType";
  		$service_attr->value = $qual['quals'][$_REQUEST['result_id']]['accessType'];
  		$service_attr->create();

  		// accessMethod
	  	$service_attr->param = "accessMethod";
  		$service_attr->value = $qual['quals'][$_REQUEST['result_id']]['accessMethod'];
  		$service_attr->create();

  		// serviceSpeed
	  	$service_attr->param = "serviceSpeed";
  		$service_attr->value = $_REQUEST['service_speed'];
  		$service_attr->create();

  		// priceZone
	  	$service_attr->param = "priceZone";
  		$service_attr->value = $qual['quals'][$_REQUEST['result_id']]['priceZone'];
  		$service_attr->create();

 			//set shape status
    	if ( $services->type_id == 1 || $services->type_id == 2 ) {
    		$service_attr->param = "shape_status";
    		$service_attr->value = "0";
	  		$service_attr->create();
    	}
  	  		
  	  // Insert into radius - not reqired as done in run_orders
  	  //$radcheck->create();
  	  
  		// Lets create the order for the service
    	$orders = new orders();
    	$orders->service_id = $services->service_id;
    	$orders->start = date("Y-m-d H:i:s");
    	$orders->request_type = strtolower($service_type->description);
    	$orders->action = "new";
    	
    	$order_comment_text = '';
    	if ($qual['manual'] === true) {
     		$orders->status = "on hold";  		
    	} else {
    		$orders->status = "pending";  		
    	}
    	
    	if (!$orders->create()) {
    		
    		$pt->setVar('ERROR_MSG', 'Error: Unable to create order.');
    		$services->delete();

    	} else {

    	
    		// Setup Order state
		  	$orders_states = new orders_states();
  			$orders_states->order_id = $orders->order_id;
  			$orders_states->state_name = $orders->status;
  			$orders_states->create();
  			
  	    		
	  		// Lets set the order attributes
	  		$order_attributes = new order_attributes();
  			$order_attributes->order_id = $orders->order_id;
  			
    		// Username
  	  	$order_attributes->param = "order_username";
    		$order_attributes->value = $_REQUEST['order_username'];
    		$order_attributes->create();
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
  
    		// Realms
  	  	$order_attributes->param = "order_realms";
    		$order_attributes->value = $_REQUEST["order_realms"];
    		$order_attributes->create();
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
    		
    		// Password
  	  	$order_attributes->param = "order_password";
    		$order_attributes->value = $_REQUEST["order_password"];
    		$order_attributes->create();
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
    		
    		// contract_length
  	  	$order_attributes->param = "order_contract_length";
    		$order_attributes->value = $_REQUEST["order_contract_length"];
    		$order_attributes->create();
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
  
    		// contact
  	  	$order_attributes->param = "order_contact";
    		$order_attributes->value = $_REQUEST["order_contact"];
    		$order_attributes->create();
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
    		
    		// address
  	  	$order_attributes->param = "order_address";
    		$order_attributes->value = $address;
    		$order_attributes->create();
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
  
    		// type
  	  	$order_attributes->param = "order_type";
    		$order_attributes->value = $qual['quals'][$_REQUEST['result_id']]['type'];
    		$order_attributes->create();
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
  
    		// accessType
  	  	$order_attributes->param = "order_accessType";
    		$order_attributes->value = $qual['quals'][$_REQUEST['result_id']]['accessType'];
    		$order_attributes->create();
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
  
    		// accessMethod
  	  	$order_attributes->param = "order_accessMethod";
    		$order_attributes->value = $qual['quals'][$_REQUEST['result_id']]['accessMethod'];
    		$order_attributes->create();
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
  
    		// serviceSpeed
  	  	$order_attributes->param = "order_serviceSpeed";
    		$order_attributes->value = $qual['quals'][$_REQUEST['result_id']]['speed'];
    		$order_attributes->create();
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
  
    		// priceZone
  	  	$order_attributes->param = "order_priceZone";
    		$order_attributes->value = $qual['quals'][$_REQUEST['result_id']]['priceZone'];
    		$order_attributes->create();  			
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";
    		
    		// nbnLocationID
  	  	$order_attributes->param = "order_opticommPropertyID";
    		$order_attributes->value = $qual["property_id"];
    		$order_attributes->create();  			
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";

    		// order_manual?
  	  	$order_attributes->param = "order_manual";
    		$order_attributes->value = $qual['manual'];
    		$order_attributes->create();  			
	    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";

            /*
				// DSL Churn
			  if ( $qual['result']['dslCodesOnLine'] == 'yes' ) {

	    		// order_service_number
  		  	$order_attributes->param = "order_churn";
    			$order_attributes->value = 'yes';
    			$order_attributes->create();  			
		    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";

	    		// order_churn_provider
  		  	$order_attributes->param = "order_churn_provider";
    			$order_attributes->value = $_REQUEST["order_churn_provider"];
    			$order_attributes->create();  
		    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";

    		}		
    		
    		// FTTN
    		if ($qual['quals'][$_REQUEST['result_id']]['accessType'] == 'NCAS') {
	    		// Pair
  		  	$order_attributes->param = "order_ncas_pair";
    			$order_attributes->value = $_REQUEST['pair'];
    			$order_attributes->create();  			
		    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";

	    		// voiceband_continuity
  		  	$order_attributes->param = "order_ncas_voiceband_continuity";
    			$order_attributes->value = $_REQUEST['voiceband_continuity'];
    			$order_attributes->create();  			
		    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";

	    		// voiceband_continuity
  		  	$order_attributes->param = "order_ncas_splitter_install";
    			$order_attributes->value = $_REQUEST['splitter_install'];
    			$order_attributes->create();  			
		    	$order_comment_text .= $order_attributes->param . ': ' . $order_attributes->value . "\r\n";

				}
				*/
  		
	  		// Do we need to create any additional sub orders (i.e. static IP)
		  	$extras = array("staticip","ipblock4","ipblock8","ipblock16");

	  	
	  		while ($cel = each($extras)) {
	  			
	  			if (isset($_REQUEST[$cel['value']])) {
	  				
	  				// We need to create this addon
        		$addon_order = new orders();
        		$addon_order->service_id = $services->service_id;
        		$addon_order->request_type = strtolower($service_type->description);
        		$addon_order->action = "addon create";
        		$addon_order->status = "pending";
        		$addon_order->start = date("Y-m-d H:i:s");	  				
        		
        		$addon_order->create();
        		
        		// Link to parent order
        		$order_attr = new order_attributes();
        		$order_attr->order_id = $addon_order->order_id;
        		$order_attr->param = "parent_order";
        		$order_attr->value = $orders->order_id;
        		$order_attr->create();
        		
        		// What are we ordering?
        		$order_attr = new order_attributes();
        		$order_attr->order_id = $addon_order->order_id;
        		$order_attr->param = "order_" . $cel['value'];
        		$order_attr->value = 'activated';
        		$order_attr->create();
        	}
        }
        
				$comment = new order_comments();
				$comment->order_id = $orders->order_id;
				$comment->username = 'system';
				$comment->comment_visibility = "internal";
				$comment->comment = $order_comment_text;
				$comment->create();        
      }
    }
        		
  	$url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/?service_id=" . $services->service_id;

    header("Location: $url");
    exit();
  
  }

	
}

$pt->setVar('WHOLESALE_PLAN', $services->wholesale_plan_id);
$pt->setVar('RETAIL_PLAN', $services->retail_plan_id);
$pt->setVar('STATE', ucfirst($services->state));
$pt->setVar('IDENTIFIER', $services->identifier);
$pt->setVar('TAG', $services->tag);
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

// Does this wholesaler manage thier own plans?
if ( $wholesaler->manage_own_plan == "no" ) {

	// No they don't.

	// Fetch a list of plan groups this wholesaler has access to
  $plan_groups = new wholesaler_plan_groups();
  $plan_groups->wholesaler_id = $wholesaler->wholesaler_id;
  $plan_groups_list = $plan_groups->get_group_id();
  

	// Create a list of plans
  $retail_plan_list = array();

  $retail_plan = new plans();
  $retail_plan->type_id = $services2->type_id;
  $retail_plan->accessMethod = $qual['quals'][$_REQUEST['result_id']]["accessMethod"];
  $retail_plan->priceZone = $qual['quals'][$_REQUEST['result_id']]["priceZone"];
  $retail_plan->speed = $qual['quals'][$_REQUEST['result_id']]['speed'];

  // Loop through plan groups
  while ($plan_group = each($plan_groups_list)) {
    
    $array_plans = $retail_plan->get_wholesaler_plans_by_group($plan_group['value']["group_id"]);
    while ($plan = each($array_plans)) {
    	$retail_plan_list[]  = $plan['value'];
    }
  }

	// Clear out based on plan length  
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
  $retail_plan_list = $retail_plan->order_get_all2($services2->type_id, $customer->wholesaler_id, $qual['quals'][$_REQUEST['result_id']]['speed'],$qual['quals'][$_REQUEST['result_id']]["priceZone"]);

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

if (isset($_REQUEST['order_username'])) {
	$pt->setVar('ORDER_USERNAME', $_REQUEST['order_username']);
}

if (isset($_REQUEST['order_password'])) {
	$pt->setVar('ORDER_PASSWORD', $_REQUEST['order_password']);
}

if (isset($_REQUEST['tag'])) {
	$pt->setVar('TAG', $_REQUEST['tag']);
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

// Speed of service and type etc
while ($cel = each($qual['quals'][$_REQUEST['result_id']])) {
	
	$pt->setVar('RES_' . strtoupper($cel['key']), $cel['value']);
}

if(isset($_REQUEST["submit"])) {
    $selected_plan = $_REQUEST["retail_plan"];
    $plan = new plans();
    $plan->plan_id = $selected_plan;
    $plan->load();
    $pt->setVar('RES_SPEED', $plan->speed);
}


// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");