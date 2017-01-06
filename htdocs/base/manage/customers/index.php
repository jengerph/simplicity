<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/customers/index.php - View Customers
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

include_once "customers.class";
include_once "wholesalers.class";
include_once "services.class";
include_once "service_attributes.class";
include_once "plans.class";
include_once "service_billing_once_off.class";
include_once "radius.class";
include_once "status.php";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

// if ($user->class == 'customer') {
	
// 	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
// 	// Parse the main page
// 	$pt->parse("MAIN", "main");
// 	$pt->parse("WEBPAGE", "outside");

// 	// Print out the page
// 	$pt->p("WEBPAGE");

// 	exit();
	
// }

//check user if agent
$agent = new customers();
$agent->customer_id = $user->access_id;
$agent->load();

$check_customer = new customers();
$check_customer->customer_id = (!empty($_REQUEST["customer_id"]) ? $_REQUEST["customer_id"] : $user->access_id);
$check_customer->load();

if ( $user->class == 'customer' ) {
	if ( $check_customer->agent == $agent->customer_id ) {
		$_REQUEST["customer_id"] = $_REQUEST["customer_id"];
	} else {
		$_REQUEST["customer_id"] = $user->access_id;
	}
}

$pt->setVar("PAGE_TITLE", "Manage Customers");

if ( $user->class == 'admin' ) {
	$pt->setFile(array("outside1" => "base/outside1.html", "outside2" => "base/outside2.html", "main" => "base/manage/customers/index.html", "row" => "base/manage/customers/row.html", "customer_box" => "base/manage/customers/customer_box.html", "services_li" => "base/manage/customers/services_li.html" , "customer_table" => "base/manage/customers/customer_table.html", "back_link" => "base/manage/customers/back_link1.html", "services_table" => "base/manage/customers/services_table1.html", "row_services" => "base/manage/customers/row_services1.html"));
} else if ( $user->class == 'reseller' ) {
	$pt->setFile(array("outside1" => "base/outside3.html", "outside2" => "base/outside2.html", "main" => "base/manage/customers/index.html", "row" => "base/manage/customers/row.html", "customer_box" => "base/manage/customers/customer_box.html", "services_li" => "base/manage/customers/services_li.html" , "customer_table" => "base/manage/customers/customer_table.html", "back_link" => "base/manage/customers/back_link2.html", "services_table" => "base/manage/customers/services_table1.html", "row_services" => "base/manage/customers/row_services1.html"));
} else if ( $user->class == 'customer' ) {
	$pt->setFile(array("outside1" => "base/outside2.html", "outside2" => "base/outside2.html", "main" => "base/manage/customers/index.html", "row" => "base/manage/customers/row.html", "customer_box" => "base/manage/customers/customer_box.html", "services_li" => "base/manage/customers/services_li.html" , "customer_table" => "base/manage/customers/customer_table.html", "services_table" => "base/manage/customers/services_table2.html", "row_services" => "base/manage/customers/row_services2.html"));
}
$pt->setFile(array("welcome_banner" => "base/welcome_banner.html",
					"status_online" => "base/manage/customers/status_online.html",
					"status_offline" => "base/manage/customers/status_offline.html",
					"status_shaped" => "base/manage/customers/status_shaped.html",
					"status_normal" => "base/manage/customers/status_normal.html",
					"agent_links" => "base/manage/customers/agent_links.html",
					"agent_column" => "base/manage/customers/agent_column.html",
					"bpay_table" => "base/manage/customers/bpay_table.html",
					"credit_card_link" => "base/manage/customers/credit_card_link.html"));

if (!isset($_REQUEST['inactive'])) {
	$_REQUEST['inactive'] = 'yes';
}

$pt->setVar('INACTIVE_DISPLAY', $_REQUEST['inactive']);
if ($_REQUEST['inactive'] == 'yes') {
	$pt->setVar('INACTIVE_NEW', 'no');
} else {
	$pt->setVar('INACTIVE_NEW', 'yes');
}

if ($user->class == 'admin' && isset($_REQUEST["customer_id"])) {

	if ( isset($_REQUEST["customer_id"]) && $_REQUEST["customer_id"] != "" ) {

		$customers = new customers();
		$customers->customer_id = $_REQUEST["customer_id"];
		$customers->load();

		if ( $customers->company_name ) {
			$customer_name =$customers->company_name;
		} else {
			$customer_name = $customers->first_name . " " . $customers->last_name;
		}

		$wholesaler = new wholesalers();
		$wholesaler->wholesaler_id = $customers->wholesaler_id;
		$wholesaler->load();

		if ( isset($wholesaler->allow_credit_card) && $wholesaler->allow_credit_card == "yes" ) {
			$pt->parse("CREDIT_CARD_LINK","credit_card_link","true");
		}

		$pt->setVar('WHOLESALER_ID', $customers->wholesaler_id);
		$pt->setVar('CUSTOMER_ID', $customers->customer_id);
		$pt->setVar('CUSTOMER_NAME', $customer_name);
		$pt->setVar('CS_WHOLESALER', $wholesaler->company_name);
		$pt->setVar('CS_HOME_PHONE', $customers->phone);
		$pt->setVar('CS_MOBILE_PHONE', $customers->mobile);
		$pt->setVar('CS_EMAIL', $customers->email);
		$pt->setVar('CS_TYPE', ucfirst($customers->type));
		$pt->setVar("CUSTOMER_KIND",ucfirst($customers->kind));
		$customer_agent = new customers();
		$customer_agent->customer_id = $customers->agent;
		$customer_agent->load();
		if ( !empty($customer_agent->company_name) ) {
			$agent_name = $customer_agent->company_name;
		} else {
			$agent_name = ucwords($customer_agent->first_name) . " " . ucwords($customer_agent->last_name);
		}
		$pt->setVar("AGENT",trim($agent_name));
		if ( $customers->kind == "customer" ) {
			$pt->parse("AGENT_COLUMN","agent_column","true");
		}
		$pt->setVar('CS_ACTIVE', ucfirst($customers->active));
		
		if ($wholesaler->bpay != '') {
			
			// Enable BPAY
			$pt->setVar('BPAY_BILLER_CODE', $wholesaler->bpay);
			$pt->setVar('BPAY_REFERENCE', $misc->generateBpayRef(100000000 + $customers->customer_id));
			
			$pt->parse('BPAY_TABLE','bpay_table');
		}
			

		$my_services = new services();
		$my_services->customer_id = $customers->customer_id;
		$my_services_array = $my_services->get_all();

		usort($my_services_array, function($a, $b) {return $a['type_id'] - $b['type_id'];});

		for ($x = 0; $x < count($my_services_array) ; $x++) {

			$service_billing_once_off = new service_billing_once_off();
			$service_billing_once_off->service_id = $my_services_array[$x]["service_id"];
			$service_billing_once_off->service_exist();
			
			if ( $my_services_array[$x]["state"] != "inactive" ) {
				if (isset($_REQUEST["filter"]) && $_REQUEST["filter"] ==  "no_wholesale_price" && !isset($service_billing_once_off->item_id)) {
					$service_type = new service_types();
					$service_type->type_id = $my_services_array[$x]["type_id"];
					$service_type->load();

					$wholesale_plan = new plans();
					$wholesale_plan->plan_id = $my_services_array[$x]["wholesale_plan_id"];
					$wholesale_plan->load();

					$retail_plan = new plans();
					$retail_plan->plan_id = $my_services_array[$x]["retail_plan_id"];
					$retail_plan->load();

					$pt->setVar('CUSTOMER_ID', $customers->customer_id);
					$pt->setVar('SERVICE_ID', $my_services_array[$x]["service_id"]);
					$pt->setVar('TYPE', $service_type->description);
					$con_start = strtotime($my_services_array[$x]["start_date"]);
					$new_con_start = date("d/m/Y",$con_start);
					$pt->setVar('START_DATE', $new_con_start);
					$con_end = strtotime($my_services_array[$x]["contract_end"]);
					$new_con_end = date("d/m/Y",$con_end);
					$pt->setVar('CONTRACT_END', $new_con_end);
					$pt->setVar('WHOLESALE_PLAN', $wholesale_plan->description . " " . $wholesale_plan->sub_type);
					$pt->setVar('RETAIL_PLAN', $retail_plan->description . " " . $retail_plan->sub_type);
					$pt->setVar('STATE', $my_services_array[$x]["state"]);
					$pt->setVar('IDENTIFIER', $my_services_array[$x]["identifier"]);
					$pt->setVar('TAG', $my_services_array[$x]["tag"]);

					$username = get_username($my_services_array[$x]["service_id"]);
					$pt->clearVar("STATUS_IMAGE");
					$pt->clearVar("SHAPED");
					$pt->parse("STATUS_IMAGE",get_radius_status($username),"true");
					$pt->parse("SHAPED",get_shape_status($my_services_array[$x]["service_id"]),"true");
					$pt->setVar("STATUS_TEXT",str_replace("status_", "", get_radius_status($username).get_shape_status($my_services_array[$x]["service_id"])));

					$pt->parse('ROW_SERVICES','row_services','true');
				} else if (isset($_REQUEST["filter"]) && $_REQUEST["filter"] ==  "with_wholesale_price" && isset($service_billing_once_off->item_id)) {
					$service_type = new service_types();
					$service_type->type_id = $my_services_array[$x]["type_id"];
					$service_type->load();

					$wholesale_plan = new plans();
					$wholesale_plan->plan_id = $my_services_array[$x]["wholesale_plan_id"];
					$wholesale_plan->load();

					$retail_plan = new plans();
					$retail_plan->plan_id = $my_services_array[$x]["retail_plan_id"];
					$retail_plan->load();

					$pt->setVar('CUSTOMER_ID', $customers->customer_id);
					$pt->setVar('SERVICE_ID', $my_services_array[$x]["service_id"]);
					$pt->setVar('TYPE', $service_type->description);
					$con_start = strtotime($my_services_array[$x]["start_date"]);
					$new_con_start = date("d/m/Y",$con_start);
					$pt->setVar('START_DATE', $new_con_start);
					$con_end = strtotime($my_services_array[$x]["contract_end"]);
					$new_con_end = date("d/m/Y",$con_end);
					$pt->setVar('CONTRACT_END', $new_con_end);
					$pt->setVar('WHOLESALE_PLAN', $wholesale_plan->description . " " . $wholesale_plan->sub_type);
					$pt->setVar('RETAIL_PLAN', $retail_plan->description . " " . $retail_plan->sub_type);
					$pt->setVar('STATE', $my_services_array[$x]["state"]);
					$pt->setVar('IDENTIFIER', $my_services_array[$x]["identifier"]);
					$pt->setVar('TAG', $my_services_array[$x]["tag"]);

					$username = get_username($my_services_array[$x]["service_id"]);
					$pt->clearVar("STATUS_IMAGE");
					$pt->clearVar("SHAPED");
					$pt->parse("STATUS_IMAGE",get_radius_status($username),"true");
					$pt->parse("SHAPED",get_shape_status($my_services_array[$x]["service_id"]),"true");
					$pt->setVar("STATUS_TEXT",str_replace("status_", "", get_radius_status($username).get_shape_status($my_services_array[$x]["service_id"])));

					$pt->parse('ROW_SERVICES','row_services','true');
				} else if ( !isset($_REQUEST["filter"]) ) {
					$service_type = new service_types();
					$service_type->type_id = $my_services_array[$x]["type_id"];
					$service_type->load();

					$wholesale_plan = new plans();
					$wholesale_plan->plan_id = $my_services_array[$x]["wholesale_plan_id"];
					$wholesale_plan->load();

					$retail_plan = new plans();
					$retail_plan->plan_id = $my_services_array[$x]["retail_plan_id"];
					$retail_plan->load();

					$pt->setVar('CUSTOMER_ID', $customers->customer_id);
					$pt->setVar('SERVICE_ID', $my_services_array[$x]["service_id"]);
					$pt->setVar('TYPE', $service_type->description);
					$con_start = strtotime($my_services_array[$x]["start_date"]);
					$new_con_start = date("d/m/Y",$con_start);
					$pt->setVar('START_DATE', $new_con_start);
					$con_end = strtotime($my_services_array[$x]["contract_end"]);
					$new_con_end = date("d/m/Y",$con_end);
					$pt->setVar('CONTRACT_END', $new_con_end);
					$pt->setVar('WHOLESALE_PLAN', $wholesale_plan->description . " " . $wholesale_plan->sub_type);
					$pt->setVar('RETAIL_PLAN', $retail_plan->description . " " . $retail_plan->sub_type);
					$pt->setVar('STATE', $my_services_array[$x]["state"]);
					$pt->setVar('IDENTIFIER', $my_services_array[$x]["identifier"]);
					$pt->setVar('TAG', $my_services_array[$x]["tag"]);

					$username = get_username($my_services_array[$x]["service_id"]);
					$pt->clearVar("STATUS_IMAGE");
					$pt->clearVar("SHAPED");
					$pt->parse("STATUS_IMAGE",get_radius_status($username),"true");
					$pt->parse("SHAPED",get_shape_status($my_services_array[$x]["service_id"]),"true");
					$pt->setVar("STATUS_TEXT",str_replace("status_", "", get_radius_status($username).get_shape_status($my_services_array[$x]["service_id"])));

					$pt->parse('ROW_SERVICES','row_services','true');
				}
			}

		}

		$pt->parse('CUSTOMER_BOX','customer_box','true');

	}
} else {

	if ( isset($_REQUEST["customer_id"]) && $_REQUEST["customer_id"] != "" ) {

		$customers = new customers();
		$customers->customer_id = $_REQUEST["customer_id"];
		$customers->load();

		if ( $user->class == 'customer' ) {
			if ( $customers->customer_id != $user->access_id && $customers->agent != $agent->customer_id ) {
				$pt->setFile(array("main" => "base/accessdenied.html"));
			}
		} else if ( $user->class == 'reseller' ) {
			if ( $customers->wholesaler_id != $user->access_id ) {
				$pt->setFile(array("main" => "base/accessdenied.html"));
			}
		}

		if ( $customers->company_name ) {
			$customer_name =$customers->company_name;
		} else {
			$customer_name = $customers->first_name . " " . $customers->last_name;
		}

		$wholesaler = new wholesalers();
		$wholesaler->wholesaler_id = $customers->wholesaler_id;
		$wholesaler->load();

		if ( isset($wholesaler->allow_credit_card) && $wholesaler->allow_credit_card == "yes" ) {
			$pt->parse("CREDIT_CARD_LINK","credit_card_link","true");
		}

		$pt->setVar('WHOLESALER_ID', $customers->wholesaler_id);
		$pt->setVar('CUSTOMER_ID', $customers->customer_id);
		$pt->setVar('CUSTOMER_NAME', $customer_name);
		$pt->setVar('CS_WHOLESALER', $wholesaler->company_name);
		$pt->setVar('CS_PHONE', $customers->phone);
		$pt->setVar('CS_TYPE', ucfirst($customers->type));
		$pt->setVar("CUSTOMER_KIND",ucfirst($customers->kind));
		$customer_agent = new customers();
		$customer_agent->customer_id = $customers->agent;
		$customer_agent->load();
		if ( !empty($customer_agent->company_name) ) {
			$agent_name = $customer_agent->company_name;
		} else {
			$agent_name = ucwords($customer_agent->first_name) . " " . ucwords($customer_agent->last_name);
		}
		$pt->setVar("AGENT",trim($agent_name));
		if ( $customers->kind == "customer" ) {
			$pt->parse("AGENT_COLUMN","agent_column","true");
		}
		$pt->setVar('CS_ACTIVE', ucfirst($customers->active));

		$pt->clearVar('SERVICES_LI');

		$my_services = new services();
		$my_services->customer_id = $customers->customer_id;
		$my_services_array = $my_services->get_all();
		usort($my_services_array, function($a, $b) {return $a['type_id'] - $b['type_id'];});

		for ($x = 0; $x < count($my_services_array) ; $x++) {

			$service_billing_once_off = new service_billing_once_off();
			$service_billing_once_off->service_id = $my_services_array[$x]["service_id"];
			$service_billing_once_off->service_exist();
			
			if ( $my_services_array[$x]["state"] != "inactive" ) {
				if (isset($_REQUEST["filter"]) && $_REQUEST["filter"] ==  "no_wholesale_price" && !isset($service_billing_once_off->item_id)) {
					$service_type = new service_types();
					$service_type->type_id = $my_services_array[$x]["type_id"];
					$service_type->load();

					$wholesale_plan = new plans();
					$wholesale_plan->plan_id = $my_services_array[$x]["wholesale_plan_id"];
					$wholesale_plan->load();

					$retail_plan = new plans();
					$retail_plan->plan_id = $my_services_array[$x]["retail_plan_id"];
					$retail_plan->load();

					$pt->setVar('CUSTOMER_ID', $customers->customer_id);
					$pt->setVar('SERVICE_ID', $my_services_array[$x]["service_id"]);
					$pt->setVar('TYPE', $service_type->description);
					$pt->setVar('START_DATE', date('d/m/Y',strtotime($my_services_array[$x]["start_date"])));
					$pt->setVar('CONTRACT_END', date('d/m/Y',strtotime($my_services_array[$x]["contract_end"])));
					$pt->setVar('WHOLESALE_PLAN', $wholesale_plan->description . " " . $wholesale_plan->sub_type);
					$pt->setVar('RETAIL_PLAN', $retail_plan->description . " " . $retail_plan->sub_type);
					$pt->setVar('STATE', $my_services_array[$x]["state"]);
					$pt->setVar('IDENTIFIER', $my_services_array[$x]["identifier"]);
					$pt->setVar('TAG', $my_services_array[$x]["tag"]);

					$username = get_username($my_services_array[$x]["service_id"]);
					$pt->clearVar("STATUS_IMAGE");
					$pt->clearVar("SHAPED");
					$pt->parse("STATUS_IMAGE",get_radius_status($username),"true");
					$pt->parse("SHAPED",get_shape_status($my_services_array[$x]["service_id"]),"true");
					$pt->setVar("STATUS_TEXT",str_replace("status_", "", get_radius_status($username).get_shape_status($my_services_array[$x]["service_id"])));

					$pt->parse('ROW_SERVICES','row_services','true');
				} else if (isset($_REQUEST["filter"]) && $_REQUEST["filter"] ==  "with_wholesale_price" && isset($service_billing_once_off->item_id)) {
					$service_type = new service_types();
					$service_type->type_id = $my_services_array[$x]["type_id"];
					$service_type->load();

					$wholesale_plan = new plans();
					$wholesale_plan->plan_id = $my_services_array[$x]["wholesale_plan_id"];
					$wholesale_plan->load();

					$retail_plan = new plans();
					$retail_plan->plan_id = $my_services_array[$x]["retail_plan_id"];
					$retail_plan->load();

					$pt->setVar('CUSTOMER_ID', $customers->customer_id);
					$pt->setVar('SERVICE_ID', $my_services_array[$x]["service_id"]);
					$pt->setVar('TYPE', $service_type->description);
					$pt->setVar('START_DATE', date('d/m/Y',strtotime($my_services_array[$x]["start_date"])));
					$pt->setVar('CONTRACT_END', date('d/m/Y',strtotime($my_services_array[$x]["contract_end"])));
					$pt->setVar('WHOLESALE_PLAN', $wholesale_plan->description . " " . $wholesale_plan->sub_type);
					$pt->setVar('RETAIL_PLAN', $retail_plan->description . " " . $retail_plan->sub_type);
					$pt->setVar('STATE', $my_services_array[$x]["state"]);
					$pt->setVar('IDENTIFIER', $my_services_array[$x]["identifier"]);
					$pt->setVar('TAG', $my_services_array[$x]["tag"]);

					$username = get_username($my_services_array[$x]["service_id"]);
					$pt->clearVar("STATUS_IMAGE");
					$pt->clearVar("SHAPED");
					$pt->parse("STATUS_IMAGE",get_radius_status($username),"true");
					$pt->parse("SHAPED",get_shape_status($my_services_array[$x]["service_id"]),"true");
					$pt->setVar("STATUS_TEXT",str_replace("status_", "", get_radius_status($username).get_shape_status($my_services_array[$x]["service_id"])));

					$pt->parse('ROW_SERVICES','row_services','true');
				} else if ( !isset($_REQUEST["filter"]) ) {
					$service_type = new service_types();
					$service_type->type_id = $my_services_array[$x]["type_id"];
					$service_type->load();

					$wholesale_plan = new plans();
					$wholesale_plan->plan_id = $my_services_array[$x]["wholesale_plan_id"];
					$wholesale_plan->load();

					$retail_plan = new plans();
					$retail_plan->plan_id = $my_services_array[$x]["retail_plan_id"];
					$retail_plan->load();

					$pt->setVar('CUSTOMER_ID', $customers->customer_id);
					$pt->setVar('SERVICE_ID', $my_services_array[$x]["service_id"]);
					$pt->setVar('TYPE', $service_type->description);
					$pt->setVar('START_DATE', date('d/m/Y',strtotime($my_services_array[$x]["start_date"])));
					$pt->setVar('CONTRACT_END', date('d/m/Y',strtotime($my_services_array[$x]["contract_end"])));
					$pt->setVar('WHOLESALE_PLAN', $wholesale_plan->description . " " . $wholesale_plan->sub_type);
					$pt->setVar('RETAIL_PLAN', $retail_plan->description . " " . $retail_plan->sub_type);
					$pt->setVar('STATE', $my_services_array[$x]["state"]);
					$pt->setVar('IDENTIFIER', $my_services_array[$x]["identifier"]);
					$pt->setVar('TAG', $my_services_array[$x]["tag"]);

					$username = get_username($my_services_array[$x]["service_id"]);
					$pt->clearVar("STATUS_IMAGE");
					$pt->clearVar("SHAPED");
					$pt->parse("STATUS_IMAGE",get_radius_status($username),"true");
					$pt->parse("SHAPED",get_shape_status($my_services_array[$x]["service_id"]),"true");
					$pt->setVar("STATUS_TEXT",str_replace("status_", "", get_radius_status($username).get_shape_status($my_services_array[$x]["service_id"])));

					$pt->parse('ROW_SERVICES','row_services','true');
				}
			}

		}

		$pt->parse('CUSTOMER_BOX','customer_box','true');

	} else {
		$my_customers = new customers();
		$my_customers_array = $my_customers->get_customers();
		for ( $i = 0; $i < count($my_customers_array); $i++ ) {

			if ( $user->class=='admin' || $user->access_id == $my_customers_array[$i]['wholesaler_id'] ) {

				if ( $_REQUEST["inactive"] == "yes") {
					$active = $my_customers_array[$i]["active"] == 'yes';
				} else {
					$active = $my_customers_array[$i]["active"];
				}

				if ( $active ) {
					if ( $my_customers_array[$i]['company_name'] != "" ) {
						$customer_name = $my_customers_array[$i]['company_name'];
					} else {
						$customer_name = $my_customers_array[$i]['first_name'] . " " . $my_customers_array[$i]['last_name'];
					}

					$pt->setVar('CUSTOMER_ID', $my_customers_array[$i]['customer_id']);
					$pt->setVar('CUSTOMER_NAME', $customer_name);
					$pt->setVar('ACTIVE', ucfirst($my_customers_array[$i]['active']));
					$pt->parse('ROWS','row','true');
				}
			}
		}
			$pt->parse('CUSTOMER_TABLE','customer_table','true');
	}
}

if ( $user->class != 'customer' ) {
	$pt->parse('BACK_LINK','back_link','true');
}

if ( ($user->class != "customer" && $customers->kind == "agent") || ($user->class == "customer" && $agent->kind == "agent" && $customers->kind == "agent") ) {
	$pt->parse("AGENT_LINKS","agent_links","true");
}

if ( $user->class == 'reseller' && isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"] != "http://localhost/base/manage/customers/" ) {
	$pt->clearVar("BACK_LINK");
	$pt->setFile(array("back_link2" => "base/manage/customers/back_link3.html"));
	$pt->parse("BACK_LINK","back_link2","true");
}


$_SESSION["order_service_number"] = "";
$_SESSION["order_address_information"] = "";
$_SESSION["order_level"] = "";
$_SESSION["order_sub_address_type"] = "";
$_SESSION["order_number"] = "";
$_SESSION["order_street_number"] = "";
$_SESSION["order_suffix"] = "";
$_SESSION["order_street_name"] = "";
$_SESSION["order_add_type"] = "";
$_SESSION["order_suffix_type"] = "";
$_SESSION["order_suburb"] = "";
$_SESSION["order_address_state"] = "";
$_SESSION["order_postcode"] = "";
$_SESSION["order_address"] = "";

$pt->parse('SERVICES_TABLE','services_table','true');

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

if ( isset($_SERVER['HTTP_REFERER']) ) {
	// if ($_SERVER['HTTP_REFERER'] == "http://localhost/") {;
	if ($_SERVER['HTTP_REFERER'] == "http://simplicity.xi.com.au/") {;
		$pt->parse("WELCOME_BANNER","welcome_banner","true");
	}
}

if ($user->class != 'customer') {
	$pt->parse("WEBPAGE", "outside1");
} else if ( $user->class == 'customer' ) {
	$pt->parse("WEBPAGE", "outside2");
}	
	
// Print out the page
$pt->p("WEBPAGE");

