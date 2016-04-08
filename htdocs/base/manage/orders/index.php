<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/orders/index.php - View orders
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

include_once "orders.class";
include_once "plans.class";
include_once "customers.class";
include_once "services.class";
include_once "order_attributes.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/orders/index.html"));
	
} else if ($user->class == 'reseller') {

	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/orders/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/orders/index.html"));
	
}

$pt->setFile(array("orders_table" => "base/manage/orders/orders_table.html", "rows" => "base/manage/orders/row.html"));

	$customer = new customers();
	$customer_array = $customer->get_customers();
	$my_customer_array = array();
	$all_services = array();

	$orders = new orders();	
	$orders_array = $orders->all_open();

	if ( $user->class == 'admin' ) {

		for ( $x = 0; $x < count($customer_array); $x++ ) {
			$my_customer_array[] = $customer_array[$x];
		}

	} else if ( $user->class == 'reseller' ) {

		for ( $x = 0; $x < count($customer_array); $x++ ) {
			if ( $user->access_id == $customer_array[$x]['wholesaler_id'] ) {
				$my_customer_array[] = $customer_array[$x];
			}
		}

	} else if ( $user->class == 'customer' ) {

		for ( $x = 0; $x < count($customer_array); $x++ ) {
			if ( $user->access_id == $customer_array[$x]['customer_id'] ) {
				$my_customer_array[] = $customer_array[$x];
			}
		}

	}

	if ( isset($_REQUEST['wholesaler_id']) && $_REQUEST['wholesaler_id'] != "" ) {
		$my_customer_array = array();
		for ( $x = 0; $x < count($customer_array); $x++ ) {
			if ( $_REQUEST['wholesaler_id'] == $customer_array[$x]['wholesaler_id'] ) {
				$my_customer_array[] = $customer_array[$x];
			}
		}

	} else if ( isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] != "" ) {

		$my_customer_array = array();
		for ( $x = 0; $x < count($customer_array); $x++ ) {
			if ( $_REQUEST['customer_id'] == $customer_array[$x]['customer_id'] ) {
				$my_customer_array[] = $customer_array[$x];
			}
		}

	}

for ($y=0; $y < count($my_customer_array); $y++) { 
	$services = new services();
	$services->customer_id = $my_customer_array[$y]["customer_id"];
	$temp = $services->get_all();

	if ( count($temp) != 0 ) {
		for ($z=0; $z < count($temp); $z++) { 
			$all_services[] = $temp[$z]["service_id"];
		}
	}
}

for ($a=0; $a < count($orders_array); $a++) { 
	for ($b=0; $b < count($all_services); $b++) { 
		if ( ($orders_array[$a]["service_id"] == $all_services[$b]) ) {
			$service = new services();
			$service->service_id = $all_services[$b];
			$service->load();

			$order = new orders();
			$order->order_id =  $orders_array[$a]["order_id"];
			$order->load();
			
			$plan = new plans();
			$plan->plan_id =  $service->retail_plan_id;
			$plan->load();

			$customer = new customers();
			$customer->customer_id = $service->customer_id;
			$customer->load();

			switch ($orders_array[$a]["request_type"]) {
				case 'adsl':
				case 'nbn':
				case 'efm':
				case 'inbound voice':
				case 'outbound voice':

					if ( $order->order_id ) {
						$pt->setVar('ORDER_ID',$order->order_id);
						$pt->setVar('CUSTOMER_NAME',$customer->company_name);
						$pt->setVar('ACTION',ucfirst($order->action));
						$pt->setVar('STATUS', ucfirst($order->status));
						$pt->setVar('START_DATE',date('d-m-Y H:i:s',strtotime($order->start)));
						$pt->parse('ROWS','rows','true');
					}
					break;
				case 'number range':
					if ( $orders_array[$a]["action"] != 'new' ) {
						if ( $order->order_id ) {
							$pt->setVar('ORDER_ID',$order->order_id);
							$pt->setVar('CUSTOMER_NAME',$customer->company_name);
							$pt->setVar('ACTION',ucfirst($order->action));
							$pt->setVar('STATUS', ucfirst($order->status));
							$pt->setVar('START_DATE',date('d-m-Y H:i:s',strtotime($order->start)));
							$pt->parse('ROWS','rows','true');
						}
					}
					break;
				
				default:
					# code...
					break;
			}
		}
	}
}
	
$pt->parse('ORDERS_TABLE','orders_table','true');
	
// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");
	
// Print out the page
$pt->p("WEBPAGE");

