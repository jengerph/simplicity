<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/edit/index.php - Edit Services
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../setup.inc";

include "../../../doauth.inc";

include_once "services.class";
include_once "service_types.class";
include_once "service_attributes.class";
include_once "plans.class";
include_once "plan_attributes.class";
include_once "plan_extras.class";
include_once "customers.class";
include_once "orders.class";
include_once "radius.class";
include_once "wholesalers.class";
include_once "wholesaler_plan_groups.class";
include_once "service_temp.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

$services = new services();

if ( isset($_REQUEST["service_id"]) ) {
	$services->service_id = $_REQUEST["service_id"];
}

$services->load();

$customer = new customers();
$customer->customer_id = $services->customer_id;
$customer->load();

if ($user->class == 'customer') {
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/edit/index.html"));
	if ( $user->access_id != $services->customer_id ) {
		$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
		// Parse the main page
		$pt->parse("MAIN", "main");
		$pt->parse("WEBPAGE", "outside");

		// Print out the page
		$pt->p("WEBPAGE");

		exit();
	}
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/edit/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/edit/index.html"));
	
}

// Assign the templates to use
$pt->setFile(array("service_option" => "base/manage/wholesalers/service_option.html", 
					"wholesale_plan_row" => "base/manage/services/edit/wholesale_plan_row.html",
					"service_stats_link" => "base/manage/services/edit/service_stats_link.html",
					"move_service_link" => "base/manage/services/edit/move_service_link.html",
					"user_radius" => "base/manage/services/edit/user_radius.html",
					"service_links" => "base/manage/services/edit/service_links.html",
					"extra_table" => "base/manage/services/edit/extra_table.html",
					"extra_staticip" => "base/manage/services/edit/extra_staticip.html",
					"extra_ipblock4" => "base/manage/services/edit/extra_ipblock4.html",
					"extra_ipblock8" => "base/manage/services/edit/extra_ipblock8.html",
					"extra_ipblock16" => "base/manage/services/edit/extra_ipblock16.html",
					"extra_div_header" => "base/manage/services/edit/extra_div_header.html"));

if ( $user->class != 'customer' ) {
	$pt->parse('WHOLESALE_PLAN_ROW','wholesale_plan_row','true');
}

$temp_array = array();

if ( $user->class == 'reseller' ) {
	if ( $customer->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

$order = new orders();
$order->service_id = $services->service_id;
$order->get_latest_orders();
if ($order->order_id != NULL) {
	$word = $order->status;
	if ( $word != 'closed' && $word !='withdrawn' ) {
		switch ($word) {
			case 'in progress':
			case 'on hold':
			case 'awaiting access install':
			case 'accepted':
				$word = "an";
				break;
			default:
				$word = "a";
				break;
		}
		$pt->setVar('ERROR_INPROGRESS','<font size="4">&#9888;</font> This service is currently being modified by ' . $word . ' ' . $order->status . ' order <a href="/base/manage/orders/edit/?order_id=' . $order->order_id . '">' . $order->order_id . '</a>');
	}
}

if ( isset($_REQUEST['submit']) ) {
	$services->retail_plan_id = $_REQUEST["retail_plan"];
}

if ( isset($_REQUEST['save_tag']) ) {
	$services->tag = $_REQUEST['tag'];
	$services->save();
	$services->load();

	$pt->setVar("SUCCESS_MSG","Tag saved successfully.");
}

if ( isset($_REQUEST["save_extra"]) ) {
	//evaluate extras
	$extra = array("staticip","ipblock4","ipblock8","ipblock16");
	$current_extra = array();
	$new_extra = array();
	$change_extra = 0;
	for ($i=0; $i < count($extra); $i++) { 
		$sa_extra = new service_attributes();
		$sa_extra->service_id = $services->service_id;
		$sa_extra->param = $extra[$i];
		$sa_extra->get_attribute();
		if ( isset($sa_extra->value) ) {
			$current_extra[$i]["param"] = $extra[$i];
			$current_extra[$i]["value"] = $sa_extra->value;
		} else {
			$current_extra[$i]["param"] = $extra[$i];
			$current_extra[$i]["value"] = "deactivated";
		}
	}
	for ($j=0; $j < count($extra); $j++) { 

		if ( isset($_REQUEST[$extra[$j]]) ) {
			$value = "activated";
		} else {
			$value = "deactivated";
		}

		$new_extra[$j]["param"] = $extra[$j];
		$new_extra[$j]["value"] = $value;
	}
	for ($k=0; $k < count($extra); $k++) { 
		if ( $current_extra[$k]["param"] == $new_extra[$k]["param"] ) {
			if ( $current_extra[$k]["value"] != $new_extra[$k]["value"] ) {
				$change_extra = 1;
				$extras = new service_attributes();
				$extras->service_id = $services->service_id;
				$extras->param = $current_extra[$k]["param"];
				$extras->value = $new_extra[$k]["value"];
				$extras->delete_attribute();
				$extras->create();
			}
		}
	}
	if ( $change_extra == 1 ) {
		for ($l=0; $l < count($extra); $l++) { 
			$_SESSION["order_".$new_extra[$l]["param"]] = $new_extra[$l]["value"];
		}

		$pt->setVar("SUCCESS_MSG","Extra saved successfully.");
	}
}

if (isset($_REQUEST['submit2']) && ($order->status == 'closed' || $order->status == 'withdrawn' || empty($order->status)) && ($services->retail_plan_id != $_REQUEST['retail_plan'])) {

	// edit new service
	$error_msg = '';


	if ( ($services->wholesale_plan_id == "" || $services->wholesale_plan_id == 0) && $user->class == 'customer' ) {
		$services->wholesale_plan_id = "-";
	}

	$vc = $services->validate();

	if ($vc != 0) {
	
		$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

	} else if (empty($_REQUEST['retail_plan']) || $_REQUEST['retail_plan'] == '0') {
	
		$pt->setVar('ERROR_MSG','Error: Please select a plan.');

	} else {

				$session_pointer = $session_pointer = md5(microtime());
				$key = $services->customer_id . "_" . $session_pointer;

			if ( $services->retail_plan_id != $_REQUEST['retail_plan'] ) {
				$temp_array['edit_retail_plan'] = $_REQUEST['retail_plan'];
			}

			$temp_array["action_order"] = "plan change";

			$service_temp = new service_temp();
			$service_temp->data_key = $key;
			$service_temp->data = serialize($temp_array);
			$service_temp->create();

			// Done, goto order
			$url = "";

			if (isset($_SERVER["HTTPS"])) {
				$url = "https://";
			} else {
				$url = "http://";
			}

			$url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/confirm/?service_id=" . $services->service_id . "&sp=" . $session_pointer;

			header("Location: $url");
			exit();
	}
} else if ( isset($_REQUEST["cancel"]) && ($order->status == 'closed' || $order->status == 'withdrawn' || empty($order->status)) ) {
	$temp_array["action_order"] = "cancel";
	$temp_array["edit_retail_plan"] = $services->retail_plan_id;

	$session_pointer = $session_pointer = md5(microtime());
	$key = $services->customer_id . "_" . $session_pointer;

	$service_temp = new service_temp();
	$service_temp->data_key = $key;
	$service_temp->data = serialize($temp_array);
	$service_temp->create();

	// Done, goto order
	$url = "";

	if (isset($_SERVER["HTTPS"])) {
		$url = "https://";
	} else {
		$url = "http://";
	}
		$url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/edit/cancel/?service_id=" . $services->service_id . "&sp=" . $session_pointer;

	header("Location: $url");
	exit();
}

if ( isset($_REQUEST["tag"]) ) {
	$services->tag = $_REQUEST["tag"];
}

//service_attribute static_ip and block
$extra = array();
$plan_extras = new plan_extras();
$plan_extras->plan_id = $services->retail_plan_id;
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

	if ( count($pe_arr) > 0 ) {
		$pt->parse("EXTRA_TABLE","extra_table","true");
		$pt->parse("EXTRA_DIV_HEADER","extra_div_header","true");
	}
}

$pt->setVar('SERVICE_ID', $services->service_id);
$pt->setVar('CUSTOMER_ID', $services->customer_id);
$pt->setVar('TYPE', $services->type_id);
$con_start = strtotime($services->start_date);
$new_con_start = date("d/m/Y",$con_start);
$pt->setVar("START_DATE", $new_con_start);
$con_end = strtotime($services->contract_end);
$new_con_end = date("d/m/Y",$con_end);
$pt->setVar("CONTRACT_END", $new_con_end);
$pt->setVar('WHOLESALE_PLAN', $services->wholesale_plan_id);
$pt->setVar('RETAIL_PLAN', $services->retail_plan_id);
$pt->setVar('STATE', ucfirst($services->state));
$pt->setVar('IDENTIFIER', $services->identifier);
$pt->setVar('TAG', $services->tag);
// $pt->setVar('STATE_' . strtoupper($services->state) . '_SELECT', ' selected');

//Get a list of service_type
$services2 = new service_types();
$services2->type_id = $services->type_id;
$services2->load();

$pt->setVar('SERVICE_TYPE', $services2->description);

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $customer->wholesaler_id;
$wholesaler->load();

//ready group_id
$get_group_id = new plans();
$get_group_id->plan_id = $services->retail_plan_id;
$get_group_id->load();

$retail_plan = new plans();
$retail_plan->plan_id = $services->retail_plan_id;
$retail_plan->load();

$plan_attribute = new plan_attributes();
$plan_attribute->plan_id = $retail_plan->plan_id;
$plan_attribute->param = "contract_length";
$plan_attribute->get_latest();

if ( $wholesaler->manage_own_plan == "no" ) {

  $plan_groups = new wholesaler_plan_groups();
  $plan_groups->wholesaler_id = $wholesaler->wholesaler_id;
  $plan_groups_list = $plan_groups->get_group_id();

  $retail_plan_list = array();
  for ($i=0; $i < count($plan_groups_list); $i++) { 
    // $retail_plan = new plans();
    // $retail_plan->type_id = $services2->type_id;
    $retail_plan->accessMethod = $retail_plan->access_method;
    $retail_plan->priceZone = $retail_plan->price_zone;
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
    
    if ( $plan_attributes->value == $plan_attribute->value ) {
      $rp_final[] = $retail_plan_list[$i];
    }
  }

} else {
  $retail_plan_list = $retail_plan->order_get_all2($services2->type_id, $customer->wholesaler_id, $retail_plan->speed,$retail_plan->price_zone);

  $rp_final = array();

  for ($i=0; $i < count($retail_plan_list); $i++) { 
    $plan_attributes = new plan_attributes();
    $plan_attributes->plan_id = $retail_plan_list[$i]["plan_id"];
    $plan_attributes->param = "contract_length";
    $plan_attributes->get_latest();
    
    if ( $plan_attributes->value == $plan_attribute->value ) {
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


	switch ($services->type_id) {
		case '1':
		case '2':
		case '3':
		case '4':
			$pt->parse("SERVICE_LINKS","service_links","true");
			if ( preg_match("/telstra/i", strtolower($retail_plan->access_method)) == 0 ) {
				$pt->parse("SERVICE_STATS_LINK","service_stats_link","true");
			}
			break;
		case '5':
		case '6':
			// $pt->parse("BACK_LINK","back_link_inbound_voice","true");
			break;
		default:
			# code...
			break;
	}

if ( $user->class == "admin" ) {
	$pt->parse("MOVE_SERVICE_LINK","move_service_link","true");
}

$pt->setVar("PAGE_TITLE", "Edit Service");

		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

