<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/orders/edit/number_range/edit/number_range/index.php - Manage Number Range
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../setup.inc";

include "../../../../doauth.inc";

include_once "orders.class";
include_once "services.class";
include_once "service_attributes.class";
include_once "order_attributes.class";
include_once "orders_states.class";
include_once "outbound_voice_fnn.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
	
} else if ($user->class == 'reseller') {

	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/orders/edit/number_range/index.html"));
	
}

$pt->setFile(array("number_range_rows" => "base/manage/orders/edit/number_range/number_range_rows.html",
					"number_range_form" => "base/manage/orders/edit/number_range/number_range_form.html",
					"back_link_number_range" => "base/manage/orders/edit/number_range/back_link_number_range.html"));

if ( !isset($_REQUEST["order_id"]) || empty($_REQUEST["order_id"]) ) {
	echo "Order ID invalid";
	exit();
}

$order = new orders();
$order->order_id = $_REQUEST["order_id"];
$order->load();

$services = new services();
$services->service_id = $order->service_id;
$services->load();

$order_attributes = new order_attributes();
$order_attributes->order_id = $order->order_id;
$my_order_attributes = $order_attributes->get_order_attributes();

for ($i=0; $i < count($my_order_attributes); $i++) { 
	if($my_order_attributes[$i]['param']=="order_address"){
		$pt->setVar("ORDER_ADDRESS",$my_order_attributes[$i]['value']);
	}
}

$number_range = new outbound_voice_fnn();
$number_range->service_id = $order->service_id;
$number_range_list = $number_range->get_number_range();

for ($a=0; $a < count($number_range_list); $a++) { 
	$pt->setVar("FNN",$number_range_list[$a]["fnn"]);
	$pt->setVar("START",$number_range_list[$a]["start"]);
	$pt->setVar("STOP",$number_range_list[$a]["stop"]);
	$pt->parse("NUMBER_RANGE_ROWS","number_range_rows","true");
}

$service_attr_range = new service_attributes();
$service_attr_range->service_id = $services->parent_service_id;
$service_attr_range->param = "number_range";
$service_attr_range->get_attribute();

//check if contain number range
if (strpos($service_attr_range->value, 'yes_')!==FALSE) {
	$range = str_replace("yes_", "", $service_attr_range->value);
} else {
	$range = 0;
}

if ( isset($_REQUEST["submit"]) ) {
	if ( isset($_REQUEST["fnn_start"]) && isset($_REQUEST["fnn_finish"]) && !empty($_REQUEST["fnn_start"]) && !empty($_REQUEST["fnn_finish"]) ){
		$start = $_REQUEST["fnn_start"];
		$finish = $_REQUEST["fnn_finish"];
		if ( $start > $finish ) {
			$pt->setVar('ERROR_MSG','Error: Start Number should not be greater than the Finish Number.');
		} else if ( (($finish - $start) + 1) != $range ) {
			$pt->setVar('ERROR_MSG','Error: Range of numbers should be equal to the ordered block of numbers which is ' . $range);
		} else {
			$temp_finish = $start + ($range - 1);
			if ( $finish == $temp_finish ) {
				$numbers = array();
				for ($b=0; $b < $range; $b++) { 
					$numbers[] = $start + $b;
				}
				for ($c=0; $c < count($numbers); $c++) { 
						$create_number = new outbound_voice_fnn();
						$create_number->service_id = $services->service_id;
						$create_number->fnn = $numbers[$c];
						$create_number->start = date("Y-m-d H:i:s");
						$create_number->stop = "0000-00-00 00:00:00";
						$create_number->create();
				}

				$num_range_complete = new orders();
				$num_range_complete->order_id = $order->order_id;
				$num_range_complete->load();
				$num_range_complete->action = $order->action;
				$num_range_complete->status = "closed";
				$num_range_complete->save();
				
				$order_states = new orders_states();
				$order_states->order_id = $order->order_id;
				$os = $order_states->get_by_order_id();

				$num_range_os = new orders_states();
				$num_range_os->state_id = $os[0]["state_id"];
				$num_range_os->date_completed = date("Y-m-d H:i:s");
				$num_range_os->save();

				$num_range_os = new orders_states();
				$num_range_os->order_id = $order->order_id;
				$num_range_os->state_name = "closed";
				$num_range_os->date_estimated = $os[0]["date_estimated"];
				$num_range_os->date_completed = date("Y-m-d H:i:s");
				$num_range_os->create();

				$sa_fnn = new service_attributes();
				$sa_fnn->service_id = $services->service_id;
				$sa_fnn->param = "start_number";
				$sa_fnn->value = $start;
				$sa_fnn->create();

				$sa_fnn = new service_attributes();
				$sa_fnn->service_id = $services->service_id;
				$sa_fnn->param = "finish_number";
				$sa_fnn->value = $finish;
				$sa_fnn->create();

				// Done, goto list
			    $url = "";
			        
			    if (isset($_SERVER["HTTPS"])) {
			        
			      $url = "https://";
			          
			    } else {
			        
			      $url = "http://";
			    }

			    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/orders/edit/number_range/?order_id=".$order->order_id;

			    header("Location: $url");
			    exit(); 
			}
		}
	} else {
		$pt->setVar('ERROR_MSG','Error: Please provide a Start Number and a Finish Number.');
	}

}

if ( isset($_REQUEST["fnn_start"]) ) {
	$fnn_start = $_REQUEST["fnn_start"];
} else {
	$fnn_start = "";
}

if ( isset($_REQUEST["fnn_finish"]) ) {
	$fnn_finish = $_REQUEST["fnn_finish"];
} else {
	$fnn_finish = "";
}

if ( count($number_range_list) < $range ) {
	$pt->parse("NUMBER_RANGE_FORM","number_range_form","true");
}

$parent_order = new orders();
$parent_order->service_id = $services->parent_service_id;
$parent_order->get_latest_orders();

$pt->setVar("ORDER_ID",$order->order_id);
$pt->setVar("FNN_START",$fnn_start);
$pt->setVar("FNN_FINISH",$fnn_finish);
$pt->setVar("TOTAL_NUMBER_RANGE",$range);
$pt->setVar("PARENT_ORDER_ID",$parent_order->order_id);
$pt->setVar("CURRENT_COUNT_RANGE",count($number_range_list));
$pt->parse("BACK_LINK","back_link_number_range","true");

$pt->setVar("PAGE_TITLE", "Outbound Voice - Number Range");
	
// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");
	
// Print out the page
$pt->p("WEBPAGE");

