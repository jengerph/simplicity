<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/summary/index.php - Order Summary
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

include_once("class.phpmailer.php");
include_once "customers.class";
include_once "service_types.class";
include_once "plans.class";
include_once "authorised_rep.class";
include_once "plan_attributes.class";
include_once "calls.class";
include_once "plan_extras.class";
include_once "services.class";
include_once "orders.class";
include_once "order_attributes.class";
include_once "service_attributes.class";
include_once "orders_states.class";
include_once "radius.class";
include_once "wholesalers.class";
include_once "order_comments.class";
include_once "service_temp.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/summary/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/summary/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/summary/index.html"));
  
}

$pt->setFile(array("adsl_nbn_row"=>"base/manage/services/add/summary/adsl_nbn_row.html",
					"inbound_voice_row"=>"base/manage/services/add/summary/inbound_voice_row.html",
					"irc_row"=>"base/manage/services/add/summary/irc_row.html",
					"outbound_voice_row"=>"base/manage/services/add/summary/outbound_voice_row.html",
					"government_tax_row"=>"base/manage/services/add/summary/components/government_tax_row.html",
					"radius_row"=>"base/manage/services/add/summary/components/radius_row.html",
					"losing_provider"=>"base/manage/services/add/summary/components/losing_provider.html",
					"extras_row"=>"base/manage/services/add/summary/extras_row.html"));

if ( !isset($_REQUEST["customer_id"]) ) {
	echo "Customer ID invalid";
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

$username = "";
$realms = "";
$password = "";

$customer = new customers();
$customer->customer_id = $_REQUEST["customer_id"];
$customer->load();

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $customer->wholesaler_id;
$wholesaler->load();

if ( $user->class == "customer" && $customer->customer_id != $user->access_id ) {
	$pt->setFile(array("main" => "base/accessdenied.html"));
} else if ( $user->class == "reseller" && $customer->wholesaler_id != $user->access_id ) {
	$pt->setFile(array("main" => "base/accessdenied.html"));
}

$service_type = new service_types();
$service_type->type_id = $service_data["service_order_summary"]["services"][0]["type_id"];
$service_type->load();

$plan = new plans();
$plan->plan_id = $service_data["service_order_summary"]["services"][0]["retail_plan_id"];
$plan->load();
// print_r($_SESSION["service_order_summary"]);
// print_r($_SESSION["service_order_summary"]);
// exit();
for ($a=0; $a < count($service_data["service_order_summary"]["order_attributes"]); $a++) { 
	$param = strtoupper(str_replace("order_", "", $service_data["service_order_summary"]["order_attributes"][$a]["param"]));
	if ( $service_data["service_order_summary"]["order_attributes"][$a]["param"] == "order_contact" ) {
		$contact = new authorised_rep();
		$contact->id = $service_data["service_order_summary"]["order_attributes"][$a]["value"];
		$contact->load();
		$value = $contact->title . " " . $contact->first_name . " " . $contact->surname;
		$value = strtoupper($value);
		$pt->setVar("CONTACT_NUMBER",$contact->contact_number);
	} else if ( $service_data["service_order_summary"]["order_attributes"][$a]["param"] == "international_rate_card" ) {
		$irc = new calls();
		$irc->ir_id = $service_data["service_order_summary"]["order_attributes"][$a]["value"];
		$irc->load();
		$value = $irc->description;
	}  else {
		$value = $service_data["service_order_summary"]["order_attributes"][$a]["value"];
	}
	$pt->setVar($param,$value);
}

if ( isset($service_data["service_order_summary"]["addon"]) && count($service_data["service_order_summary"]["addon"]) > 0 ) {
	$extras_arr = $service_data["service_order_summary"]["addon"]["service_attributes"];

	for ($d=0; $d < count($extras_arr); $d++) { 
		$plan_extra = new plan_extras();
		$plan_extra->plan_id = $plan->plan_id;
		$plan_extra->type = $extras_arr[$d]["param"];
		$plan_extra->get_extra_types_id_type();
		$value = $plan_extra->description;
		$pt->setVar("EXTRA_KEY",strtoupper($plan_extra->type));
		$pt->setVar(strtoupper($extras_arr[$d]["param"])."_DESC",$plan_extra->description);
		$pt->setVar(strtoupper($plan_extra->type)."_MONTHLY_COST",$plan_extra->month_cost);
		$pt->setVar(strtoupper($plan_extra->type)."_SETUP_COST",$plan_extra->setup_cost);
		$pt->parse("EXTRAS_ROW","extras_row","true");
	}

}

$plan_attributes = new plan_attributes();
$plan_attributes->plan_id = $plan->plan_id;
$plan_attr_arr = $plan_attributes->get_plan_attributes();

for ($b=0; $b < count($plan_attr_arr); $b++) {
	if ( $plan_attr_arr[$b]["param"] == "government_tax" ) {
		$pt->parse("GOVERNMENT_TAX_ROW","government_tax_row","true");
	}
	if ( $plan_attr_arr[$b]["param"] == "international_rate_card" ) {
		$irc = new calls();
		$irc->ir_id = $plan_attr_arr[$b]["value"];
		$irc->load();
		$value = $irc->description;
		$pt->parse("IRC_ROW","irc_row","true");
	} else {
		$value = $plan_attr_arr[$b]["value"];
	}
	$pt->setVar(strtoupper($plan_attr_arr[$b]["param"]),$value);
}

if ( isset($_REQUEST["cancel"]) && $_REQUEST["cancel"] == 'yes' ) {
	//delete data
	$delete_service_temp = new service_temp();
	$delete_service_temp->data_key = $session_pointer;
	$delete_service_temp->delete();

    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/customers/?customer_id=" . $customer->customer_id;

    header("Location: $url");
    exit();

} else if ( isset($_REQUEST["submit"]) ) {
	$service_array_keys = array_keys($service_data["service_order_summary"]["services"][0]);

	$service = new services();

	for ($c=0; $c < count($service_array_keys); $c++) { 
		$service->{$service_array_keys[$c]} = $service_data["service_order_summary"]["services"][0][$service_array_keys[$c]];
	}

	$service->create();

	$main_service_id = $service->service_id;

	$order_array_keys = array_keys($service_data["service_order_summary"]["orders"][0]);

	$orders = new orders();

	for ($d=0; $d < count($order_array_keys); $d++) { 
		$orders->{$order_array_keys[$d]} = $service_data["service_order_summary"]["orders"][0][$order_array_keys[$d]];
	}

	$orders->service_id = $service->service_id;

	$orders->create();

	$main_order = $orders->order_id;

	for ($e=0; $e < count($service_data["service_order_summary"]["order_attributes"]); $e++) { 
		$order_attr_keys = array_keys($service_data["service_order_summary"]["order_attributes"][$e]);
		$order_attr = new order_attributes();
		for ($f=0; $f < count($order_attr_keys); $f++) { 
			$order_attr->{$order_attr_keys[$f]} = $service_data["service_order_summary"]["order_attributes"][$e][$order_attr_keys[$f]];
			if ( $service_data["service_order_summary"]["order_attributes"][$e]["param"] == "order_username" ) {
				$username = $service_data["service_order_summary"]["order_attributes"][$e]["value"];
			}
			if ( $service_data["service_order_summary"]["order_attributes"][$e]["param"] == "order_realms" ) {
				$realms = $service_data["service_order_summary"]["order_attributes"][$e]["value"];
			}
			if ( $service_data["service_order_summary"]["order_attributes"][$e]["param"] == "order_password" ) {
				$password = $service_data["service_order_summary"]["order_attributes"][$e]["value"];
			}
			if ( $service_data["service_order_summary"]["order_attributes"][$e]["param"] == "order_churn_provider" ) {
				$pt->parse("LOSING_PROVIDER","losing_provider","true");
			}
		}

		$order_attr->order_id = $orders->order_id;

		$order_attr->create();
	}

	for ($e=0; $e < count($service_data["service_order_summary"]["service_attributes"]); $e++) { 
		$service_attr_keys = array_keys($service_data["service_order_summary"]["service_attributes"][$e]);
		$service_attr = new service_attributes();
		for ($f=0; $f < count($service_attr_keys); $f++) { 
			$service_attr->{$service_attr_keys[$f]} = $service_data["service_order_summary"]["service_attributes"][$e][$service_attr_keys[$f]];
		}

		$service_attr->service_id = $service->service_id;

		$service_attr->create();
	}

	$orders_states_array_keys = array_keys($service_data["service_order_summary"]["orders_states"][0]);

	$orders_states = new orders_states();

	for ($d=0; $d < count($orders_states_array_keys); $d++) { 
		$orders_states->{$orders_states_array_keys[$d]} = $service_data["service_order_summary"]["orders_states"][0][$orders_states_array_keys[$d]];
	}

	$orders_states->order_id = $main_order;

	$orders_states->create();

	//for addon
	if ( isset($service_data["service_order_summary"]["addon"]) && count($service_data["service_order_summary"]["addon"]) > 0 ) {
		$order_array_keys = array_keys($service_data["service_order_summary"]["addon"]["orders"][0]);

		$orders = new orders();

		for ($d=0; $d < count($order_array_keys); $d++) { 
			$orders->{$order_array_keys[$d]} = $service_data["service_order_summary"]["addon"]["orders"][0][$order_array_keys[$d]];
		}

		$orders->service_id = $service->service_id;

		$orders->create();	

		$addon_order = $orders->order_id;

		for ($e=0; $e < count($service_data["service_order_summary"]["addon"]["order_attributes"]); $e++) { 
			$order_attr_keys = array_keys($service_data["service_order_summary"]["addon"]["order_attributes"][$e]);
			$order_attr = new order_attributes();
			for ($f=0; $f < count($order_attr_keys); $f++) { 
				$order_attr->{$order_attr_keys[$f]} = $service_data["service_order_summary"]["addon"]["order_attributes"][$e][$order_attr_keys[$f]];

			}
			
			if ( $order_attr->param == "parent_order" ) {
				$order_attr->value = $main_order;				
			}
			
			$order_attr->order_id = $orders->order_id;

			$order_attr->create();
		}

		for ($e=0; $e < count($service_data["service_order_summary"]["addon"]["service_attributes"]); $e++) { 
			$service_attr_keys = array_keys($service_data["service_order_summary"]["addon"]["service_attributes"][$e]);
			$service_attr = new service_attributes();
			for ($f=0; $f < count($service_attr_keys); $f++) { 
				$service_attr->{$service_attr_keys[$f]} = $service_data["service_order_summary"]["addon"]["service_attributes"][$e][$service_attr_keys[$f]];
			}

			$service_attr->service_id = $service->service_id;

			$service_attr->create();
		}

		$orders_states_array_keys = array_keys($service_data["service_order_summary"]["orders_states"][0]);

		$orders_states = new orders_states();

		for ($d=0; $d < count($orders_states_array_keys); $d++) { 
			$orders_states->{$orders_states_array_keys[$d]} = $service_data["service_order_summary"]["orders_states"][0][$orders_states_array_keys[$d]];
		}

		$orders_states->order_id = $addon_order;

		$orders_states->create();

	}

	//for number_range
	if ( isset($service_data["service_order_summary"]["number_range"]) && count($service_data["service_order_summary"]["number_range"]) > 0 ) {

		//number_range services
		$service_array_keys = array_keys($service_data["service_order_summary"]["number_range"]["services"][0]);

		$service = new services();

		for ($c=0; $c < count($service_array_keys); $c++) { 
			$service->{$service_array_keys[$c]} = $service_data["service_order_summary"]["number_range"]["services"][0][$service_array_keys[$c]];
		}

		$service->parent_service_id = $main_service_id;
		$service->create();

		//number_range orders
		$order_array_keys = array_keys($service_data["service_order_summary"]["number_range"]["orders"][0]);

		$orders = new orders();

		for ($d=0; $d < count($order_array_keys); $d++) { 
			$orders->{$order_array_keys[$d]} = $service_data["service_order_summary"]["number_range"]["orders"][0][$order_array_keys[$d]];
		}

		$orders->service_id = $service->service_id;

		$orders->create();

		//number_range order_attributes
		for ($e=0; $e < count($service_data["service_order_summary"]["number_range"]["order_attributes"]); $e++) { 
			$order_attr_keys = array_keys($service_data["service_order_summary"]["number_range"]["order_attributes"][$e]);
			$order_attr = new order_attributes();
			for ($f=0; $f < count($order_attr_keys); $f++) { 
				$order_attr->{$order_attr_keys[$f]} = $service_data["service_order_summary"]["number_range"]["order_attributes"][$e][$order_attr_keys[$f]];
			}

			$order_attr->order_id = $orders->order_id;
			if ( $service_data["service_order_summary"]["number_range"]["order_attributes"][$e]["param"] == "order_number_range" ) {
				$username = $service_data["service_order_summary"]["number_range"]["order_attributes"][$e]["value"];
				$order_attr->order_id = $main_order;
				$order_attr->value = $orders->order_id;
			}

			if ( $service_data["service_order_summary"]["number_range"]["order_attributes"][$e]["param"] == "parent_order" ) {
				$order_attr->order_id = $orders->order_id;
				$order_attr->value = $main_order;
			}

			$order_attr->create();
		}

		//number_range order_states
		$orders_states_array_keys = array_keys($service_data["service_order_summary"]["number_range"]["orders_states"][0]);

		$orders_states = new orders_states();

		for ($d=0; $d < count($orders_states_array_keys); $d++) { 
			$orders_states->{$orders_states_array_keys[$d]} = $service_data["service_order_summary"]["number_range"]["orders_states"][0][$orders_states_array_keys[$d]];
		}

		$orders_states->order_id = $main_order;

		$orders_states->create();


	}
	//service_chosen
	if ( isset($service_data["service_order_summary"]["service_chosen"]) && count($service_data["service_order_summary"]["service_chosen"]) > 0 ) {
		$order_attr_keys = array_keys($service_data["service_order_summary"]["service_chosen"]);

		for ($e=0; $e < count($service_data["service_order_summary"]["service_chosen"]); $e++) { 
			$order_attr = new order_attributes();
			$order_attr->param = "order_" . $order_attr_keys[$e];
			$order_attr->value = trim($service_data["service_order_summary"]["service_chosen"][$order_attr_keys[$e]]);

			$order_attr->order_id = $main_order;

			if ( !$order_attr->exist() && !empty($order_attr->value) ) {
				$order_attr->create();
			}
		}
	}

	// Send order receipt:
	$mail = new PHPMailer();

	$mail->From     = "service.delivery@xi.com.au";
	$mail->FromName = "X Integration Pty Ltd";
	$mail->Subject = "Order Receipt Notification";
	$mail->Host     = "127.0.0.1";
	$mail->Mailer   = "smtp";

	$text_body  = "Dear " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . ",\r\n";
	$text_body .= "\r\n";
	$text_body .= "Thank you for engaging X Integration as your preferred carrier. We are pleased to confirm your order for the service listed below. \r\n";
	$text_body .= "\r\n";
	$text_body .= "We have provided you with a unique X Integration Provisioning reference so that you can easily track the progress of this order. \r\n";
	$text_body .= "\r\n";
	$text_body .= "*Note: This email notification is confirmation of X Integration's receipt of your order only. X Integration has not yet accepted your order. \r\n\r\n";
	$text_body .= "Company Name: " . ucwords($customer->company_name) . "\r\n\r\n";
	$text_body .= "Customer Name: " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . "\r\n\r\n";
	if ( ($service->type_id == '1') || ($service->type_id == '2') ) {
		$text_body .= "Username: " . $username . "@" . $realms . "\r\n\r\n";
		$text_body .= "Password: " . $password . "\r\n\r\n";
	}
	$text_body .= "Order Reference: " . $orders->order_id . "\r\n\r\n";
	$text_body .= "Below is a list of item(s) on this order. " . "\r\n\r\n";

	$plan_title = new plans();
	$plan_title->plan_id = $service->retail_plan_id;
	$plan_title->load();

	$order_type = new service_types();
	$order_type->type_id = $service->type_id;
	$order_type->load();

	$text_body .= "Type of Order: " . ucwords($service_data["service_order_summary"]["orders"][0]["action"]) . " - " . $order_type->description . " Service\r\n\r\n";
	$text_body .= "Service Components:\r\n\r\n";
	$text_body .= "Service ID: " . $service->service_id . "\r\n";
	$text_body .= "Plan: " . $plan_title->description . "\r\n";
	$text_body .= "Transaction Type: " . ucwords($service_data["service_order_summary"]["orders"][0]["action"]) . "\r\n";
	$text_body .= "Customer Account Number: " . ucwords($customer->customer_id) . "\r\n";

	$contract_length = new plan_attributes();
	$contract_length->plan_id = $plan_title->plan_id;
	$contract_length->param = "contract_length";
	$contract_length->get_latest();

	$text_body .= "Contract Term (Months): " . $contract_length->value . "\r\n\r\n";

	// $text_body .= "Service Number: " . get_attribute( $orders->order_id, "order_service_number" ) . "\r\n";

	switch ($service->type_id) {
		case '1':
		case '2':
			$extras = array("staticip","ipblock4","ipblock8","ipblock16");

			$service_types = new service_types();
			$service_types->type_id = $plan_title->type_id;
			$service_types->load();

			$text_body .= "Access Technology: " . $service_types->description . "\r\n";
			$text_body .= "Access Speed: Up to " . $plan_title->speed . "\r\n\r\n";

			$text_body .= "Addons: \r\n";

			$extras_fmt = array("staticip" => "static ip",
			            "ipblock4" => "ip block 4",
			            "ipblock8" => "ip block 8",
			            "ipblock16" => "ip block 16");

			for ($j=0; $j < count($extras); $j++) { 
				$sa_extra = new service_attributes();
				$sa_extra->service_id = $service->service_id;
				$sa_extra->param = $extras[$j];
				$sa_extra->get_attribute();
				if ( isset($sa_extra->value) ) {
				  $text_body .= strtoupper($extras_fmt[$extras[$j]]) . ": ACTIVATE\r\n\r\n";
				}
			}

			break;
		case '5':
			if ( isset($service->identifier) ) {
				$text_body .= "Service Number: " . $service->identifier . "\r\n";
			}

			if ( !empty($service_data["inbound_voice"]["tel_account_num"]) ) {
				$text_body .= "Telephone Account Number: " . $service_data["inbound_voice"]["tel_account_num"] . "\r\n";
			}

			if ( !empty($service_data["inbound_voice"]["sod"]) ) {
				$text_body .= "Standard One Destination: " . $service_data["inbound_voice"]["sod"] . "\r\n";
			}

			if ( !empty($service_data["inbound_voice"]["cd"]) ) {
				$text_body .= "Customer Distribution: " . $service_data["inbound_voice"]["cd"] . "\r\n";
			}

			$service_types = new service_types();
			$service_types->type_id = $plan_title->type_id;
			$service_types->load();

			$text_body .= "Access Technology: " . $service_types->description . "\r\n\r\n";
			break;
		case '6':
			if ( isset($service->identifier) ) {
				$text_body .= "Service Number: " . $service->identifier . "\r\n";
			}

			if ( !empty($service_data["outbound_voice"]["account_name"]) ) {
				$text_body .= "Account Name: " . $service_data["outbound_voice"]["account_name"] . "\r\n";
			}

			if ( !empty($service_data["outbound_voice"]["account_number"]) ) {
				$text_body .= "Account Number: " . $service_data["outbound_voice"]["account_number"] . "\r\n";
			}

			if ( !empty($service_data["outbound_voice"]["carrier"]) ) {
				$text_body .= "Carrier: " . $service_data["outbound_voice"]["carrier"] . "\r\n";
			}

			if ( !empty($service_data["outbound_voice"]["delivery_address"]) ) {
				$text_body .= "Delivery Address: " . $service_data["outbound_voice"]["delivery_address"] . "\r\n";
			}

			$service_types = new service_types();
			$service_types->type_id = $plan_title->type_id;
			$service_types->load();

			$text_body .= "Access Technology: " . $service_types->description . "\r\n";
			$text_body .= "Type: " . $service_data["outbound_voice"]["kind"] . "\r\n\r\n";
			break;
		default:
			# code...
			break;
	}

	//adsl_nbn service details here
	//inbound_voice details here
	//outbound voice here
	$text_body .= "Provisioning Target Up to 21 days from X Integration's acceptance of your order (excluding customer delays).\r\n\r\n";
	$text_body .= "The provisioning target shown above is based on the access type for the service. You can only order one service at a time. This ensures that the provision of one service is not held up pending the provision of other services on the order.\r\n\r\n";
	$text_body .= "Throughout the provisioning process, we will provide you with updates on key milestones by sending the following notifications for each service on your order: \r\n\r\n";
	$text_body .= "Order Acceptance Notification\r\n";
	$text_body .= "If X Integration is able to accept your order you can expect to receive this in the next two business days. This notification will include the expected start date for this service.\r\n\r\n";
	$text_body .= "Service Completion Advice\r\n";
	$text_body .= "Confirmation that the provisioning of your service has been completed and billing has commenced. This will also provide you with the contact details for your Service and Provisioning Team.\r\n\r\n";
	$text_body .= "It is important to X Integration that you, our customer, are satisfied with the level of service provided.\r\n\r\n";
	$text_body .= "To view the latest update on your order please visit this link: https://simplicity.xi.com.au/base/manage/orders/edit/?order_id=" . $orders->order_id . ". or visit our online Simplicity portal by logging in via https://simplicity.xi.com.au and view all your current orders under the order tab.\r\n\r\n";
	$text_body .= "Alternatively you can contact X Integration Service and Provisioning Team using the contact details provided below.\r\n\r\n";
	$text_body .= "Kind Regards,\r\n";
	$text_body .= "X Integration Service and Provisioning Team\r\n\r\n";
	$text_body .= "service.delivery@xi.com.au";

	$mail->Body    = $text_body;

	if ( $wholesaler->block_customer_order_notif == "no" ) {
	$mail->AddAddress($customer->email);
	}

	$mail->AddBCC("alerts@xi.com.au");
	$mail->AddBCC($wholesaler->email);

	// $mail->AddAddress("renee@cloudemployee.co.uk");
	// $mail->AddBCC("campanilla_renee@yahoo.com");

	$comment = new order_comments();
	$comment->order_id = $main_order;
	$comment->username = $user->username;
	$comment->comment_visibility = "customer";
	$comment->comment = $text_body;
	$comment->create();

		if ( isset($service_data["service_order_summary"]["addon"]) >= 1 ) {
			$comment = new order_comments();
			$comment->order_id = $main_order;
			$comment->username = $user->username;
			$comment->comment_visibility = "customer";
			$comment->comment = "Addon Order: http://simplicity.xi.com.au/base/manage/orders/edit/?order_id=".$addon_order;
			$comment->create();

			$comment = new order_comments();
			$comment->order_id = $addon_order;
			$comment->username = $user->username;
			$comment->comment_visibility = "customer";
			$comment->comment = "Main Order: http://simplicity.xi.com.au/base/manage/orders/edit/?order_id=".$main_order;
			$comment->create();
		}

	$mail->Send();

	foreach($_REQUEST as $key => $value) {
      $pos = strpos($key , "order_");
      if ($pos === 0){
        unset($_REQUEST[$key]);
      }
    }

    foreach($service_temp as $key => $value) {
      $pos = strpos($key , "order_");
      if ($pos === 0){
        unset($service_data[$key]);
      }
    }

    foreach($service_temp as $key => $value) {
      $pos = strpos($key , "edit_");
      if ($pos === 0){
        unset($service_data[$key]);
      }
    }

    $service_temp->delete();

    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/?service_id=" . $main_service_id;

    header("Location: $url");
    exit();
}

switch ($service_type->type_id) {
	case '1':
	case '2':
		$pt->parse("ADSL_NBN_ROW","adsl_nbn_row","true");
		$pt->parse("RADIUS_ROW","radius_row","true");
		break;
	case '5':
		$pt->parse("INBOUND_VOICE_ROW","inbound_voice_row","true");
		break;
	case '6':
		$pt->parse("OUTBOUND_VOICE_ROW","outbound_voice_row","true");
		break;
	default:
		# code...
		break;
}

$pt->setVar("COMPANY_NAME",$customer->company_name);

$identifier = "";

if ( !empty($service_data["service_order_summary"]["services"][0]["identifier"]) ) {
	$identifier = $service_data["service_order_summary"]["services"][0]["identifier"];
} else if ( !empty($service_data["outbound_voice"]["nbnLocationID"]) ) {
	$identifier = $service_data["outbound_voice"]["nbnLocationID"];
} else if ( !empty($service_data["inbound_voice"]["nbnLocationID"]) ) {
	$identifier = $service_data["inbound_voice"]["nbnLocationID"];
}

$address = "";

if ( !empty($service_data["order_address"]) ) {
	$address = $service_data["order_address"];
} else if ( !empty($service_data["outbound_voice"]["delivery_address"]) ) {
	$address = $service_data["outbound_voice"]["delivery_address"];
} else if ( !empty($service_data["inbound_voice"]["delivery_address"]) ) {
	$address = $service_data["inbound_voice"]["delivery_address"];
}

$pt->setVar("IDENTIFIER",$identifier);
$pt->setVar("ADDRESS",$address);
$pt->setVar("TAG",$service_data["service_order_summary"]["services"][0]["tag"]);
$pt->setVar("CONTRACT_START",date('d/m/Y',strtotime($service_data["service_order_summary"]["services"][0]["start_date"])));
$pt->setVar("CONTRACT_END",date('d/m/Y',strtotime($service_data["service_order_summary"]["services"][0]["contract_end"])));
$pt->setVar("SERVICE_TYPE",$service_type->description);
$pt->setVar("PLAN",$plan->description);
$pt->setVar("ACCESS_METHOD",$plan->access_method);
$pt->setVar("SPEED",$plan->speed);
$pt->setVar("SUMMARY_USERNAME",(isset($service_data["service_order_summary"]["radius"][0]["username"]) ? $service_data["service_order_summary"]["radius"][0]["username"] : ""));
$pt->setVar("SUMMARY_PASSWORD",(isset($service_data["service_order_summary"]["radius"][0]["password"]) ? $service_data["service_order_summary"]["radius"][0]["password"] : ""));
$pt->setVar("CUSTOMER_ID",$customer->customer_id);
$pt->setVar("SP",$_REQUEST['sp']);

$pt->setVar("PAGE_TITLE", "ADSL/NBN - Order Summary");

// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function get_attribute($order_id,$param){

  $email_order_attr = new order_attributes();
  $email_order_attr->order_id = $order_id;
  $email_order_attr->param = $param;
  $email_order_attr->get_latest();

  return $email_order_attr->value;
}