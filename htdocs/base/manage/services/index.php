<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/index.php - View Services
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../setup.inc";

include "../../doauth.inc";

include_once "services.class";
include_once "service_attributes.class";
include_once "service_types.class";
include_once "plans.class";
include_once "orders.class";
include_once "customers.class";
include_once "radius.class";
include_once "aatp_ctop.class";
include_once "status.php";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html"));
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/index.html"));
	
}

$pt->setFile(array("adsl_nbn_extra" => "base/manage/services/adsl_nbn/adsl_nbn_extra.html",
					"adsl_nbn_status" => "base/manage/services/adsl_nbn/adsl_nbn_status.html",
					"adsl_nbn_status_online" => "base/manage/services/adsl_nbn/adsl_nbn_status_online.html",
					"adsl_nbn_status_offline" => "base/manage/services/adsl_nbn/adsl_nbn_status_offline.html",
					"adsl_nbn_status_shaped" => "base/manage/services/adsl_nbn/adsl_nbn_status_shaped.html",
					"adsl_nbn_status_normal" => "base/manage/services/adsl_nbn/adsl_nbn_status_normal.html",
					"adsl_nbn_radreply_row" => "base/manage/services/adsl_nbn/adsl_nbn_radreply_row.html",
					"back_link_adsl_nbn" => "base/manage/services/back_link/back_link_adsl_nbn.html",
					"back_link_admin_adsl_nbn" => "base/manage/services/back_link/back_link_admin_adsl_nbn.html",
					"back_link_inbound_voice" => "base/manage/services/back_link/back_link_inbound_voice.html",
					"back_link_service_stats" => "base/manage/services/back_link/back_link_service_stats.html",
					"back_link_outbound_voice" => "base/manage/services/back_link/back_link_outbound_voice.html",
					"back_link_num_range" => "base/manage/services/back_link/back_link_num_range.html",
					"back_link_once_off_billing" => "base/manage/services/back_link/back_link_once_off_billing.html",
					"back_link_invoice_link" => "base/manage/services/back_link/back_link_invoice_link.html",
					"back_link_order_history" => "base/manage/services/back_link/back_link_order_history.html",
					"order_status" => "base/manage/services/general/order_status.html",
					"inbound_voice_extra_cd" => "base/manage/services/inbound_voice/inbound_voice_extra_cd.html",
					"inbound_voice_extra_sod" => "base/manage/services/inbound_voice/inbound_voice_extra_sod.html",
					"inbound_voice_extra_tel_acc" => "base/manage/services/inbound_voice/inbound_voice_extra_tel_acc.html",
					"inbound_voice_service_call_history" => "base/manage/services/inbound_voice/inbound_voice_service_call_history.html",
					"inbound_voice_service_call_row" => "base/manage/services/inbound_voice/inbound_voice_service_call_row.html",
					"outbound_voice_extra" => "base/manage/services/outbound_voice/outbound_voice_extra.html",
					"outbound_voice_extra_number_range" => "base/manage/services/outbound_voice/outbound_voice_extra_number_range.html",
					"outbound_voice_extra_simul_calls" => "base/manage/services/outbound_voice/outbound_voice_extra_simul_calls.html",
					"outbound_voice_service_call_history" => "base/manage/services/outbound_voice/outbound_voice_service_call_history.html",
					"outbound_voice_service_call_row" => "base/manage/services/outbound_voice/outbound_voice_service_call_row.html",
					"row" => "base/manage/services/row2.html"));

if ( isset($_REQUEST["service_id"]) ) {

	$date_today = date("d/m/Y");

	if ( !isset($_REQUEST["date_end"]) && empty($_REQUEST["date_end"]) ) {
		$date_end = $date_today;
	} else {
		$date_end = $_REQUEST["date_end"];
	}
	if ( !isset($_REQUEST["date_start"]) && empty($_REQUEST["date_start"]) ) {
		$date_start = date('d/m/Y', time() - 2592000);
	} else {
		$date_start = $_REQUEST["date_start"];
	}

	$pt->setFile(array("main" => "base/manage/services/index.html"));

	$service = new services();
	$service->service_id = $_REQUEST["service_id"];
	$service->load();

	$type_desc = new service_types();
	$type_desc->type_id = $service->type_id;
	$type_desc->load();

	$retail_plan = new plans();
	$retail_plan->plan_id = $service->retail_plan_id;
	$retail_plan->load();

	$customer = new customers();
	$customer->customer_id = $service->customer_id;
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

	$order = new orders();
	$order->service_id = $service->service_id;
	$order->get_latest_main_orders();
	if ($order->order_id != NULL) {
		$word = $order->status;
		if ( $word != 'closed' && $word !='withdrawn' ) {
			$pt->setVar("ORDER_ID",$order->order_id);
			$pt->setVar("ORDER_STATUS",ucwords($order->status));
			$pt->parse("ORDER","order_status","true");
		}
	}

	switch ($service->type_id) {
		case '1':
		
			if ($user->class == 'admin') {
				$pt->parse("ADMIN_LINKS","back_link_admin_adsl_nbn","true");
			} 
			if ( preg_match("/telstra/i", strtolower($retail_plan->access_method)) == 0 ) {
				$pt->parse("SERVICE_STATS","back_link_service_stats","true");
			}
		case '2':
			if ($user->class == 'admin') {
				$pt->parse("ADMIN_LINKS","back_link_admin_adsl_nbn","true");
			} 
		case '3':
		case '4':
			if ( $service->type_id == 1 || $service->type_id == 2 ) {
				$pt->parse("STATUS_ROW","adsl_nbn_status","true");
			}
			
			$pt->parse("BACK_LINK","back_link_adsl_nbn","true");
			$pt->parse("EXTRAS","adsl_nbn_extra","true");
			break;
		case '5':
			$pt->parse("BACK_LINK","back_link_inbound_voice","true");
			$pt->parse("SERVICE_CALL_HISTORY","inbound_voice_service_call_history","true");

			$service_calls = new aatp_ctop();
			$service_calls->id_value = $service->identifier;
			$service_calls_list = $service_calls->get_history($date_start,$date_end);

			for ($a=0; $a < count($service_calls_list); $a++) { 

				$usage_type = new aatp_ctop();
				$usage_type->usage_type = $service_calls_list[$a]["UsageType"];
				$usage_type->get_usage_type();

				$pt->setVar("CALL_DATE",$service_calls_list[$a]["TransDateTime"]);
				$pt->setVar("CALL_ORIGIN",$service_calls_list[$a]["Origin"]);
				$pt->setVar("CALL_USAGE_TYPE",$usage_type->description);
				$pt->setVar("CALL_DURATION",$service_calls_list[$a]["RawUnits"]);
				$pt->parse("CALL_HISTORY_ROWS","inbound_voice_service_call_row","true");
			}

			break;
		case '6':

			$num_range = new service_attributes();
			$num_range->service_id = $service->service_id;
			$num_range->param = "number_range";
			$num_range->get_attribute();
			
			if ( isset($num_range->value) ) {
				$child_service = new services();
				$child_service->parent_service_id = $service->service_id;
				$child_service->child_service();

				$pt->setVar("NUM_RANGE_SERVICE_ID",$child_service->service_id);
				$pt->parse("NUM_RANGE_LINK","back_link_num_range","true");
			}

			$pt->parse("BACK_LINK","back_link_outbound_voice","true");
			$pt->parse("EXTRAS","outbound_voice_extra","true");

			$pt->parse("SERVICE_CALL_HISTORY","outbound_voice_service_call_history","true");

			$service_calls = new aatp_ctop();
			$service_calls->id_value = $service->identifier;
			$service_calls_list = $service_calls->get_history($date_start,$date_end);

			for ($a=0; $a < count($service_calls_list); $a++) { 

				$usage_type = new aatp_ctop();
				$usage_type->usage_type = $service_calls_list[$a]["UsageType"];
				$usage_type->get_usage_type();

				$pt->setVar("CALL_DATE",$service_calls_list[$a]["TransDateTime"]);
				$pt->setVar("CALL_TARGET",$service_calls_list[$a]["Target"]);
				$pt->setVar("CALL_USAGE_TYPE",$usage_type->description);
				$pt->setVar("CALL_DURATION",$service_calls_list[$a]["RawUnits"]);
				$pt->parse("CALL_HISTORY_ROWS","outbound_voice_service_call_row","true");
			}

			break;
		default:
			# code...
			break;
	}

	$service_attr_keys = array("username",
								"realms",
								"password",
								"address",
								"type",
								"accessType",
								"accessMethod",
								"tel_account_num",
								"sod",
								"cd",
								"delivery_address",
								"number_range",
								"simultaneous_calls",
								"accessSpeed",
								"shape_status");

	$username = "";

	for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
		$service_attr = new service_attributes();
		$service_attr->service_id = $service->service_id;
		$service_attr->param = $service_attr_keys[$sk];
		$service_attr->get_attribute();
		if ( $service_attr_keys[$sk] == "username" ) {
			$username =  $service_attr->value;
		} else if ( $service_attr_keys[$sk] == "realms" && !empty($username) ) {
			$username .=  "@".$service_attr->value;
		}
		if ( ($service_attr_keys[$sk] == "number_range") && isset($service_attr->value) ) { 
			$temp = explode("_", $service_attr->value);
			$service_attr->value = (isset($temp[1]) ? $temp[1] : "None");
			$pt->parse("NUMBER_RANGE_ROW","outbound_voice_extra_number_range","true");
		}
		if ( ($service_attr_keys[$sk] == "simultaneous_calls") && isset($service_attr->value) ) { 
			$pt->parse("SIMUL_CALL_ROW","outbound_voice_extra_simul_calls","true");
		}
		if ( ($service_attr_keys[$sk] == "tel_account_num") && isset($service_attr->value) ) { 
			$pt->parse("EXTRAS","inbound_voice_extra_tel_acc","true");
		}
		if ( ($service_attr_keys[$sk] == "sod") && isset($service_attr->value) ) { 
			$pt->parse("EXTRAS","inbound_voice_extra_sod","true");
		}
		if ( ($service_attr_keys[$sk] == "cd") && isset($service_attr->value) ) { 
			$pt->parse("EXTRAS","inbound_voice_extra_cd","true");
		}
		if ( ($service_attr_keys[$sk] == "accessSpeed") && isset($service_attr->value) ) { 
			$pt->setVar("SPEED",$service_attr->value);
		}
		$pt->setVar( strtoupper($service_attr->param), $service_attr->value );
	}

	$pt->setVar("KIND",$retail_plan->sub_type);

	$pt->parse("STATUS_IMAGE","adsl_nbn_".get_radius_status($username),"true");
	$pt->parse("SHAPED","adsl_nbn_".get_shape_status($service->service_id),"true");

	if ( $user->class != "customer" ) {
		$pt->parse("ONCE_OFF_BILLING","back_link_once_off_billing","true");
	}

	if ( isset($_REQUEST["radius_password"]) ) {
		$radius = new radius();
		$radius->username = $username;
		$radius->value = $_REQUEST["radius_password"];
		$radius->save();

		$service_attr = new service_attributes();
		$service_attr->service_id = $service->service_id;
		$service_attr->param = "password";
		$service_attr->value = $_REQUEST["radius_password"];
		$service_attr->create();

		// Done, goto list
	    $url = "";
	        
	    if ( isset($_SERVER["HTTPS"]) ) {
	        
	      $url = "https://";
	          
	    } else {
	        
	      $url = "http://";
	    }

	      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/index.php?service_id=" . $service->service_id;

	    header("Location: $url");
	    exit();

	}

	$radreply = new radius();
	$radreply->username = $username;
	$radreply_array = $radreply->radreply_load();
	
	if ( count($radreply_array) != 0 ) {
		for ($a=0; $a < count($radreply_array); $a++) { 
			if ( $radreply_array[$a]["attribute"] == "Framed-IP-Address" ) {
				$radreply_array[$a]["attribute"] = "Static IP";
			} else if ( $radreply_array[$a]["attribute"] == "Framed-Route" ) {
				$radreply_array[$a]["attribute"] = "Subnet";
			}
			if ( $radreply_array[$a]["attribute"] == "Static IP" || $radreply_array[$a]["attribute"] == "Subnet" ) {
				$pt->setVar("RADREPLY_ATTRIBUTE",$radreply_array[$a]["attribute"]);
				$pt->setVar("RADREPLY_VALUE",$radreply_array[$a]["value"]);
				$pt->parse("RADREPLY_ROW","adsl_nbn_radreply_row","true");
			}
		}
	}

	$pt->setVar("WHOLESALER_ID",$customer->wholesaler_id);
	$pt->setVar("CUSTOMER_ID",$service->customer_id);
	$pt->setVar("SERVICE_ID",$service->service_id);
	$pt->setVar("SERVICE_TYPE",$type_desc->description);
	$con_start = strtotime($service->start_date);
	$new_con_start = date("d/m/Y",$con_start);
	$pt->setVar("START_DATE", $new_con_start);
	$pt->setVar("SERVICE_START_DATE",$new_con_start);
	$con_end = strtotime($service->contract_end);
	$new_con_end = date("d/m/Y",$con_end);
	$pt->setVar("SERVICE_CONTRACT_END",$new_con_end);
	$pt->setVar("SERVICE_RETAIL_PLAN",$retail_plan->description);
	$pt->setVar("SERVICE_STATE",$service->state);
	$pt->setVar("SERVICE_IDENTIFIER",$service->identifier);
	$pt->setVar("SERVICE_TAG",$service->tag);
	$pt->setVar("DATE_START",$date_start);
	$pt->setVar("DATE_END",$date_end);
	// $pt->parse("INVOICE_LINK","back_link_invoice_link","true"); //uncomment if invoice is ready
	$pt->parse("ORDER_HISTORY","back_link_order_history","true");

} else {

	$pt->setFile(array("main" => "base/manage/services/index2.html"));

	$services = new services();
	$services->customer_id = $user->access_id;
	$all_services = $services->get_all();

	for ($i=0; $i < count($all_services); $i++) { 

		$type = new service_types();
		$type->type_id = $all_services[$i]["type_id"];
		$type->load();

		$retail_plan = new plans();
		$retail_plan->plan_id = $all_services[$i]["retail_plan_id"];
		$retail_plan->load();

		$pt->setVar("SERVICE_ID",$all_services[$i]["service_id"]);
		$pt->setVar("TYPE",$type->description);
		$pt->setVar("START_DATE",$all_services[$i]["start_date"]);
		$pt->setVar("CONTRACT_END",$all_services[$i]["contract_end"]);
		$pt->setVar("RETAIL_PLAN",$retail_plan->description);
		$pt->setVar("STATE",$all_services[$i]["state"]);
		$pt->setVar("IDENTIFIER",$all_services[$i]["identifier"]);
		$pt->setVar("TAG",$all_services[$i]["tag"]);
		$pt->setVar("CUSTOMER_ID",$user->access_id);

		$pt->parse("ROWS","row","true");
	}
}

$pt->setVar("PAGE_TITLE", "Manage Services");

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");
	
// Print out the page
$pt->p("WEBPAGE");

