<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/orders/edit/contact/index.php - Edit orders
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
include_once "order_attributes.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/orders/edit/contact/index.html"));
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/orders/edit/contact/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/orders/edit/contact/index.html"));
	
}

if ( !isset($_REQUEST["order_id"]) || $_REQUEST["order_id"] =="" ) {
	echo "Order ID invalid";
	exit(1);
}

$pt->setFile(array("contact_row" => "base/manage/orders/edit/contact/contact_row.html"));

$order = new orders();
$order->order_id = $_REQUEST["order_id"];
$order->load();

$order_attributes = new order_attributes();
$order_attributes->order_id = $_REQUEST["order_id"];
$attributes_array = $order_attributes->get_order_attributes();


$array_contacts = array();

for ($i=0; $i < count($attributes_array); $i++) { 
	if (count(preg_grep("/^order_churn_contact_*/", $attributes_array[$i])) != 0) {
		$array_contacts[] = $attributes_array[$i];
	}
}

for ($j=0; $j < count($array_contacts); $j = $j + 2) { 
	$pt->setVar("CONTACT_NAME",$array_contacts[$j]["value"]);
	$pt->setVar("CONTACT_NUMBER",$array_contacts[$j+1]["value"]);
	$pt->setVar("PRIM_NUM",$j);
	$pt->parse("CONTACT_ROW","contact_row","true");
	
}

if ( isset($_REQUEST["submit"]) && $_REQUEST["submit"]=="Save" ) {
	if ( isset($_REQUEST["submit"]) ) {
		if ( isset($_REQUEST["primary_contact"]) ) {

			$new_primary_arr = explode("_",$array_contacts[$_REQUEST["primary_contact"]]["param"]);
			$new_primary = $new_primary_arr[count($new_primary_arr)-1];

			$temp = new order_attributes();
			$temp->order_id = $order->order_id;
			$temp->param = $array_contacts[0]["param"];
			$temp->value = $array_contacts[$_REQUEST["primary_contact"]]["value"];
			$temp->save();

			$temp2 = new order_attributes();
			$temp2->order_id = $order->order_id;
			$temp2->param = $array_contacts[1]["param"];
			$temp2->value = $array_contacts[$_REQUEST["primary_contact"] +1 ]["value"];
			$temp2->save();

			$save_contact = new order_attributes();
			$save_contact->order_id = $array_contacts[$_REQUEST["primary_contact"]]["order_id"];
			$save_contact->param =  $array_contacts[$_REQUEST["primary_contact"]]["param"];
			$save_contact->value = $array_contacts[0]["value"];
			$save_contact->save();

			$save_contact_num = new order_attributes();
			$save_contact_num->order_id = $array_contacts[$_REQUEST["primary_contact"] +1 ]["order_id"];
			$save_contact_num->param = $array_contacts[$_REQUEST["primary_contact"] +1 ]["param"];
			$save_contact_num->value = $array_contacts[1]["value"];
			$save_contact_num->save();

			// Done, goto list
		    $url = "";
		        
		    if (isset($_SERVER["HTTPS"])) {
		        
		      $url = "https://";
		          
		    } else {
		        
		      $url = "http://";
		    }

		    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/orders/edit/index.php?order_id=" . $order->order_id;

		    header("Location: $url");
		    exit();
		}
	}
}
if ( isset($_REQUEST["submit"]) && $_REQUEST["submit"]=="Add Contact" ) {

	$err = array();

 	if ( !isset($_REQUEST["contact_name"]) || $_REQUEST["contact_name"] =="" ) {
 		$err[] = "Error: Invalid Contact Name.";
 	}
 	if ( !isset($_REQUEST["contact_num"]) || $_REQUEST["contact_num"] =="" ) {
 		$err[] = "Error: Invalid Contact Number.";
 	}
	if ( $err ) {
		$pt->setVar("ERROR_MSG", $err[0]);
	} else {

	$get_attributes = new order_attributes();
	$get_attributes->order_id = $order->order_id;
	$attr_arr = $get_attributes->get_order_attributes();

	$contacts = array();

	for ($i=0; $i < count($attr_arr); $i++) { 
		if ( preg_grep("/^order_churn_contact_*/", $attr_arr[$i]) ) {
			$temp = explode("_", $attr_arr[$i]["param"]);
			$contacts[] = $temp[count($temp)-1];
		}
	}

	$last_iterate = intval(max($contacts));
	$last_iterate = $last_iterate + 1;

	$new_attributes = new order_attributes();
	$new_attributes->order_id = $order->order_id;
	$new_attributes->param = "order_churn_contact_" . $last_iterate;
	$new_attributes->value = $_REQUEST["contact_name"];
	$new_attributes->create();

	$new_attributes2 = new order_attributes();
	$new_attributes2->order_id = $order->order_id;
	$new_attributes2->param = "order_churn_contact_num_" . $last_iterate;
	$new_attributes2->value = $_REQUEST["contact_num"];
	$new_attributes2->create();

	// Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/orders/edit/contact/index.php?order_id=" . $order->order_id;

    header("Location: $url");
    exit();
    }

}

$pt->setVar("ORDER_ID",$order->order_id);
$pt->setVar("PRIM_NUM_SELECT_0"," checked");

if ( isset($_REQUEST["contact_name"]) ) {
	$pt->setVar("ADD_CONTACT_NAME",$_REQUEST["contact_name"]);
}

if ( isset($_REQUEST["contact_num"]) ) {
	$pt->setVar("ADD_CONTACT_NUM",$_REQUEST["contact_num"]);
}

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");
	
// Print out the page
$pt->p("WEBPAGE");