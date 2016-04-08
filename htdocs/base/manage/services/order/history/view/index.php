<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/order/history/view/index.php - Edit orders
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../../setup.inc";

include "../../../../../doauth.inc";

include_once "orders.class";
include_once "plans.class";
include_once "customers.class";
include_once "services.class";
include_once "service_types.class";
include_once "service_attributes.class";
include_once "order_attributes.class";
include_once "orders_states.class";
include_once "order_comments.class";
include_once "misc.class";
include_once "radius.class";
include_once "authorised_rep.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/order/history/view/index.html"));
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/order/history/view/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/order/history/view/index.html"));
	
}

$pt->setFile(array("retail_plan" => "base/manage/orders/edit/summary/summary_retail_plan.html", 
					"status_row" => "base/manage/orders/edit/summary/summary_status_row.html", 
					"status_option" => "base/manage/orders/edit/summary/summary_status_option.html", 
					"save_button" => "base/manage/orders/edit/summary/summary_save_button.html", 
					"withdraw_button" => "base/manage/orders/edit/summary/summary_withdraw_button.html", 
					"progressbar" => "base/manage/orders/edit/progress/progress_bar.html", 
					"progressbar2" => "base/manage/orders/edit/progress/progress_bar2.html", 
					"idlebar" => "base/manage/orders/edit/progress/progress_idle.html", 
					"inprogress" => "base/manage/orders/edit/progress/progress_inprogress.html", 
					"inprogress2" => "base/manage/orders/edit/progress/progress_inprogress2.html", 
					"comment_box" => "base/manage/orders/edit/comment/comment_box.html", 
					"onhold" => "base/manage/orders/edit/progress/progress_onhold.html", 
					"onhold2" => "base/manage/orders/edit/progress/progress_onhold2.html", 
					"progressbar3" => "base/manage/orders/edit/progress/progress_bar3.html", 
					"progress_withdrawn" => "base/manage/orders/edit/progress/progress_withdrawn.html", 
					"comment_form" => "base/manage/orders/edit/comment/comment_form.html", 
					"number_range_link" => "base/manage/orders/edit/links/links_number_range.html"));

if ( !isset($_REQUEST['order_id']) || $_REQUEST['order_id'] == "" ) {
	echo "Invlaid Order ID.";
	exit(1);
}

$order = new orders();
$order->order_id = $_REQUEST['order_id'];
$order->load();

$order_attributes = new order_attributes();
$order_attributes->order_id = $order->order_id;
$my_order_attributes = $order_attributes->get_order_attributes();

for ($i=0; $i < count($my_order_attributes); $i++) { 
	if($my_order_attributes[$i]['param']=="order_service_available"){
		$service_type = new service_types();
		$service_type->type_id = $my_order_attributes[$i]['value'];
		$service_type->load();
		$my_order_attributes[$i]['value'] = $service_type->description;	
	}
	$pt->setVar(strtoupper($my_order_attributes[$i]['param']),$my_order_attributes[$i]['value']);

	if ($my_order_attributes[$i]['param']=="order_contact") {

		$contact = new authorised_rep();
		$contact->id = $my_order_attributes[$i]['value'];
		$contact->get_rep_details();
		$pt->setVar("ORDER_CONTACT_FORMAT",$contact->first_name." ".$contact->surname);
		$pt->setVar("ORDER_CONTACT_NUMBER_FORMAT",$contact->contact_number);
	}
	if ( $my_order_attributes[$i]['param']=="edit_retail_plan"){
		$plan = new plans();
		$plan->plan_id = $my_order_attributes[$i]['value'];
		$plan->load();
		$pt->setVar("EDIT_RETAIL_PLAN",$plan->description);
		$pt->parse("CHANGE_RETAIL_PLAN","retail_plan","true");
	}
	if ( $my_order_attributes[$i]['param']=="order_number_range" && $user->class == "admin" ) {
		$pt->setVar("NUMBER_RANGE",$my_order_attributes[$i]['value']);
		$pt->parse("NUMBER_RANGE_LINK","number_range_link","true");
	}
}

if ( isset($_REQUEST['withdraw']) ) {

	$os_previous = new orders_states();
	$os_previous->order_id = $order->order_id;
	$os_arr = $os_previous->get_by_order_id();

	$save_last = new orders_states();
	$save_last->state_id = $os_arr[count($os_arr)-1]["state_id"];
	$save_last->load();

	if ( $save_last->date_completed == "0000-00-00 00:00:00" ) {
		
		$save_last->date_completed = date("Y-m-d h:i:s");
		$save_last->save();

	}

	$order_state = new orders_states();
	$order_state->order_id = $order->order_id;
	$order_state->state_name = "withdrawn";
	$order_state->date_estimated = $os_arr[0]["date_estimated"];
	$order_state->date_completed = "0000-00-00 00:00:00";
	$order_state->create();

	if ( isset($_REQUEST["onhold_notes"]) && !empty($_REQUEST["onhold_notes"]) ) {
		$onhold_notes = $_REQUEST["onhold_notes"];
		create_comment($user->class,$order->order_id,$onhold_notes ,"internal");
	}

	$username = "";

	$service_attributes = new service_attributes();
	$service_attributes->service_id = $order->service_id;
	$service_attributes->param = "username";
	$service_attributes->get_attribute();

	$username = $service_attributes->value;

	$service_attributes = new service_attributes();
	$service_attributes->service_id = $order->service_id;
	$service_attributes->param = "realms";
	$service_attributes->get_attribute();

	$username .= "@" . $service_attributes->value;

	$service = new services();
	$service->service_id = $order->service_id;
	$service->load();

	$radius = new radius();
	$radius->username = $username;
	$radius->delete();

	$order->status = "withdrawn";
	$order->save();

	$check_other_orders = new orders();
	$check_other_orders->service_id = $service->service_id;
	$check_array = $check_other_orders->get_all_orders();

	$active = 0;

	for ( $x = 0; $x < count($check_array); $x++ ) {
		if ( $check_array[$x]['status'] != 'withdrawn' ) {
			$active = $active + 1;
		}
	}

	if ( $active == 0 && $service->state == "creation" ) { //mark service as inactive when order is withdrawn and it is still in creation
		$service->state = "inactive";
		$service->save();
	}
	
	// Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/orders/";

    header("Location: $url");
    exit();

} else if ( isset($_REQUEST['submit']) ) {

	if ( isset($_REQUEST["change_status"]) && $_REQUEST["change_status"] !="" ) {
		if ( $_REQUEST["change_status"] == "on hold" && empty($_REQUEST["onhold_notes"]) ) {
			$pt->setVar("ERROR_MSG","ERROR: Provide Notes when chaning status to On Hold.");
			$pt->setVar("ON_HOLD"," selected");
		} else {

			if ( $_REQUEST["change_status"] == "on hold" && $user->class != "admin" ) {
				$pt->setVar("ERROR_MSG","ERROR: You are not allowed to set this order to on hold.");
				$pt->setVar(strtoupper($order->status)," selected");
			} else {
				$order->status = $_REQUEST["change_status"];
				$order->save();

				$os_previous = new orders_states();
				$os_previous->order_id = $order->order_id;
				$os_arr = $os_previous->get_by_order_id();

				$save_last = new orders_states();
				$save_last->state_id = $os_arr[count($os_arr)-1]["state_id"];
				$save_last->load();

				if ( $save_last->date_completed == "0000-00-00 00:00:00" ) {
					
					$save_last->date_completed = date("Y-m-d h:i:s");
					$save_last->save();

				}

				$order_state = new orders_states();
				$order_state->order_id = $order->order_id;
				$order_state->state_name = $order->status;
				$order_state->date_estimated = $os_arr[0]["date_estimated"];
				$order_state->date_completed = "0000-00-00 00:00:00";
				$order_state->create();

				if ( isset($_REQUEST["onhold_notes"]) && !empty($_REQUEST["onhold_notes"]) ) {
					$onhold_notes = $_REQUEST["onhold_notes"];
					create_comment($user->class,$order->order_id,$onhold_notes ,"internal");
				}

				//mark service as active when order is closed and previous state is creation
				$set_service = new services();
				$set_service->service_id = $order->service_id;
				$set_service->load();
				
				if ( $order->status == "closed" && $set_service->state == "creation") {
					$set_service->state = "active";
					$set_service->save();
				}

				$pt->setVar('SUCCESS_MSG','Saved Order Status.');
			}
			}
	}

}

$orders_state = new orders_states();
$orders_state->order_id = $order->order_id;
$os_arr = $orders_state->get_by_order_id();

$previous_state = previous_state($os_arr);

if ( $os_arr[count($os_arr)-1]["state_name"] == "in progress" ) {
	if ( isset($previous_state) ) {
		switch ($previous_state) {
			case 'pending':
					$pt->parse("PROGRESSBAR_SUBMISSION","progressbar","true");
					$pt->parse("PROGRESSBAR_ACCEPTANCE","inprogress","true");
					$pt->parse("PROGRESSBAR_PROVISIONING","idlebar","true");
					$pt->parse("PROGRESSBAR_COMPLETION","idlebar","true");
				break;
			case 'accepted':
					$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
					$pt->parse("PROGRESSBAR_ACCEPTANCE","progressbar","true");
					$pt->parse("PROGRESSBAR_PROVISIONING","inprogress","true");
					$pt->parse("PROGRESSBAR_COMPLETION","idlebar","true");

					if ( isset($os_arr[0]["date_completed"]) && $os_arr[0]["date_completed"] != "0000-00-00 00:00:00" ) {
						$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($os_arr[0]["date_completed"]));
					}
				break;
			case 'awaiting access install':
					$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
					$pt->parse("PROGRESSBAR_ACCEPTANCE","progressbar2","true");
					$pt->parse("PROGRESSBAR_PROVISIONING","progressbar","true");
					$pt->parse("PROGRESSBAR_COMPLETION","inprogress2","true");

					if ( isset($os_arr[0]["date_completed"]) && $os_arr[0]["date_completed"] != "0000-00-00 00:00:00" ) {
						$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($os_arr[0]["date_completed"]));
					}
					if ( isset($os_arr[1]["date_completed"]) && $os_arr[1]["date_completed"] != "0000-00-00 00:00:00" ) {
						$pt->setVar("DATE_ACCEPTANCE", $misc->date_MDY($os_arr[1]["date_completed"]));
					}
				break;
			case 'closed':
					$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
					$pt->parse("PROGRESSBAR_ACCEPTANCE","progressbar2","true");
					$pt->parse("PROGRESSBAR_PROVISIONING","progressbar2","true");
					$pt->parse("PROGRESSBAR_COMPLETION","progressbar2","true");

					if ( isset($os_arr[0]["date_completed"]) && $os_arr[0]["date_completed"] != "0000-00-00 00:00:00" ) {
						$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($os_arr[0]["date_completed"]));
					}

					if ( isset($os_arr[1]["date_completed"]) && $os_arr[1]["date_completed"] != "0000-00-00 00:00:00" ) {
						$pt->setVar("DATE_ACCEPTANCE", $misc->date_MDY($os_arr[1]["date_completed"]));
					}

					if ( isset($os_arr[2]["date_completed"]) && $os_arr[2]["date_completed"] != "0000-00-00 00:00:00" ) {
						$pt->setVar("DATE_PROVISIONING", $misc->date_MDY($os_arr[2]["date_completed"]));
					}

					if ( isset($os_arr[3]["date_completed"]) && $os_arr[3]["date_completed"] != "0000-00-00 00:00:00" ) {
						$pt->setVar("DATE_ESTIMATED", $misc->date_MDY($os_arr[3]["date_completed"]));
					}
				break;
			
			default:
				# code...
				break;
		}
	}
} else if ( $os_arr[count($os_arr)-1]["state_name"] == "on hold" ) {
			if ( isset($previous_state) ) {
				switch ($previous_state) {
					case 'pending':
							$pt->parse("PROGRESSBAR_SUBMISSION","onhold","true");
							$pt->parse("PROGRESSBAR_ACCEPTANCE","idlebar","true");
							$pt->parse("PROGRESSBAR_PROVISIONING","idlebar","true");
							$pt->parse("PROGRESSBAR_COMPLETION","idlebar","true");
						break;
					case 'accepted':
							$pt->parse("PROGRESSBAR_SUBMISSION","progressbar3","true");
							$pt->parse("PROGRESSBAR_ACCEPTANCE","onhold","true");
							$pt->parse("PROGRESSBAR_PROVISIONING","idlebar","true");
							$pt->parse("PROGRESSBAR_COMPLETION","idlebar","true");

							if ( isset($os_arr[0]["date_completed"]) && $os_arr[0]["date_completed"] != "0000-00-00 00:00:00" ) {
								$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($os_arr[0]["date_completed"]));
							}
						break;
					case 'awaiting access install':
							$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
							$pt->parse("PROGRESSBAR_ACCEPTANCE","progressbar3","true");
							$pt->parse("PROGRESSBAR_PROVISIONING","onhold","true");
							$pt->parse("PROGRESSBAR_COMPLETION","idlebar","true");

							$date_submission = "0000-00-00 00:00:00";
							$date_acceptance = "0000-00-00 00:00:00";

							for ($c=0; $c < count($os_arr); $c++) { 
								if ( $os_arr[$c]["state_name"] == "pending" ) {
									$date_submission = $os_arr[$c]["date_completed"];
								}
								if ( $os_arr[$c]["state_name"] == "accepted" ) {
									$date_acceptance = $os_arr[$c]["date_completed"];
								}
							}
							if ( isset($date_submission) && $date_submission != "0000-00-00 00:00:00" ) {
								$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($date_submission));
							}
							if ( isset($date_acceptance) && $date_acceptance != "0000-00-00 00:00:00" ) {
								$pt->setVar("DATE_ACCEPTANCE", $misc->date_MDY($date_acceptance));
							}
						break;
					case 'closed':
							$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
							$pt->parse("PROGRESSBAR_ACCEPTANCE","progressbar2","true");
							$pt->parse("PROGRESSBAR_PROVISIONING","progressbar3","true");
							$pt->parse("PROGRESSBAR_COMPLETION","onhold2","true");

							if ( isset($os_arr[0]["date_completed"]) && $os_arr[0]["date_completed"] != "0000-00-00 00:00:00" ) {
								$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($os_arr[0]["date_completed"]));
							}

							if ( isset($os_arr[1]["date_completed"]) && $os_arr[1]["date_completed"] != "0000-00-00 00:00:00" ) {
								$pt->setVar("DATE_ACCEPTANCE", $misc->date_MDY($os_arr[1]["date_completed"]));
							}

							if ( isset($os_arr[2]["date_completed"]) && $os_arr[2]["date_completed"] != "0000-00-00 00:00:00" ) {
								$pt->setVar("DATE_PROVISIONING", $misc->date_MDY($os_arr[2]["date_completed"]));
							}

							if ( isset($os_arr[3]["date_completed"]) && $os_arr[3]["date_completed"] != "0000-00-00 00:00:00" ) {
								$pt->setVar("DATE_ESTIMATED", $misc->date_MDY($os_arr[3]["date_completed"]));
							}
						break;
					
					default:
						# code...
						break;
				}
	}

} else if ( $os_arr[count($os_arr)-1]["state_name"] == "withdrawn" ) {
	
	if ( isset($previous_state) ) {
	switch ($previous_state) {
		case 'pending':
			$pt->parse("PROGRESSBAR_SUBMISSION","progress_withdrawn","true");
			$pt->parse("PROGRESSBAR_ACCEPTANCE","idlebar","true");
			$pt->parse("PROGRESSBAR_PROVISIONING","idlebar","true");
			$pt->parse("PROGRESSBAR_COMPLETION","idlebar","true");
			break;
		case 'accepted':
				$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
				$pt->parse("PROGRESSBAR_ACCEPTANCE","progress_withdrawn","true");
				$pt->parse("PROGRESSBAR_PROVISIONING","idlebar","true");
				$pt->parse("PROGRESSBAR_COMPLETION","idlebar","true");

				if ( isset($os_arr[0]["date_completed"]) && $os_arr[0]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($os_arr[0]["date_completed"]));
				}
			break;
		case 'awaiting access install':
				$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
				$pt->parse("PROGRESSBAR_ACCEPTANCE","progressbar2","true");
				$pt->parse("PROGRESSBAR_PROVISIONING","progressbar2","true");
				$pt->parse("PROGRESSBAR_COMPLETION","progress_withdrawn","true");

				$date_submission = "0000-00-00 00:00:00";
				$date_acceptance = "0000-00-00 00:00:00";

				for ($c=0; $c < count($os_arr); $c++) { 
					if ( $os_arr[$c]["state_name"] == "pending" ) {
						$date_submission = $os_arr[$c]["date_completed"];
					}
					if ( $os_arr[$c]["state_name"] == "accepted" ) {
						$date_acceptance = $os_arr[$c]["date_completed"];
					}
				}

				if ( isset($date_submission) && $date_submission != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($date_submission));
				}
				if ( isset($date_acceptance) && $date_acceptance != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_ACCEPTANCE", $misc->date_MDY($date_acceptance));
				}
			break;
		case 'closed':
				$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
				$pt->parse("PROGRESSBAR_ACCEPTANCE","progressbar2","true");
				$pt->parse("PROGRESSBAR_PROVISIONING","progressbar2","true");
				$pt->parse("PROGRESSBAR_COMPLETION","progress_withdrawn","true");

				if ( isset($os_arr[0]["date_completed"]) && $os_arr[0]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($os_arr[0]["date_completed"]));
				}

				if ( isset($os_arr[1]["date_completed"]) && $os_arr[1]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_ACCEPTANCE", $misc->date_MDY($os_arr[1]["date_completed"]));
				}

				if ( isset($os_arr[2]["date_completed"]) && $os_arr[2]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_PROVISIONING", $misc->date_MDY($os_arr[2]["date_completed"]));
				}

				if ( isset($os_arr[3]["date_completed"]) && $os_arr[3]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_ESTIMATED", $misc->date_MDY($os_arr[3]["date_completed"]));
				}
			break;
		
		default:
			# code...
			break;
	}
}

} else {
	switch ($os_arr[count($os_arr)-1]["state_name"]) {
		case 'pending':
				$pt->parse("PROGRESSBAR_SUBMISSION","inprogress","true");
				$pt->parse("PROGRESSBAR_ACCEPTANCE","idlebar","true");
				$pt->parse("PROGRESSBAR_PROVISIONING","idlebar","true");
				$pt->parse("PROGRESSBAR_COMPLETION","idlebar","true");
			break;
		case 'accepted':
				$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
				$pt->parse("PROGRESSBAR_ACCEPTANCE","progressbar","true");
				$pt->parse("PROGRESSBAR_PROVISIONING","inprogress","true");
				$pt->parse("PROGRESSBAR_COMPLETION","idlebar","true");

				if ( isset($os_arr[0]["date_completed"]) && $os_arr[0]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($os_arr[0]["date_completed"]));
				}
			break;
		case 'awaiting access install':
				$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
				$pt->parse("PROGRESSBAR_ACCEPTANCE","progressbar2","true");
				$pt->parse("PROGRESSBAR_PROVISIONING","progressbar","true");
				$pt->parse("PROGRESSBAR_COMPLETION","inprogress2","true");

				if ( isset($os_arr[0]["date_completed"]) && $os_arr[0]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($os_arr[0]["date_completed"]));
				}
				if ( isset($os_arr[1]["date_completed"]) && $os_arr[1]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_ACCEPTANCE", $misc->date_MDY($os_arr[1]["date_completed"]));
				}
			break;
		case 'closed':
				$pt->parse("PROGRESSBAR_SUBMISSION","progressbar2","true");
				$pt->parse("PROGRESSBAR_ACCEPTANCE","progressbar2","true");
				$pt->parse("PROGRESSBAR_PROVISIONING","progressbar2","true");
				$pt->parse("PROGRESSBAR_COMPLETION","progressbar2","true");

				if ( isset($os_arr[0]["date_completed"]) && $os_arr[0]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_SUBMISSION", $misc->date_MDY($os_arr[0]["date_completed"]));
				}

				if ( isset($os_arr[1]["date_completed"]) && $os_arr[1]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_ACCEPTANCE", $misc->date_MDY($os_arr[1]["date_completed"]));
				}

				if ( isset($os_arr[2]["date_completed"]) && $os_arr[2]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_PROVISIONING", $misc->date_MDY($os_arr[2]["date_completed"]));
				}

				if ( isset($os_arr[3]["date_completed"]) && $os_arr[3]["date_completed"] != "0000-00-00 00:00:00" ) {
					$pt->setVar("DATE_ESTIMATED", $misc->date_MDY($os_arr[3]["date_completed"]));
				}
			break;
		
		default:
			# code...
			break;
	}
}

$misc = new misc();

$pt->setVar("DATE_ESTIMATED", $misc->date_MDY($os_arr[0]["date_estimated"]));

$pt->setVar("ORDER_ID",$order->order_id);
$pt->setVar("START",$misc->date_nice($order->start));
$pt->setVar("STATUS",ucfirst($order->status));
$pt->parse("STATUS_ROW","status_row","true");

if ( $user->class != 'customer' ) {
	$temp = str_replace(" ", "_", $order->status);
	$temp = strtoupper($temp);
	$pt->setVar($temp," selected");
	if ( $order->status != "closed" && $order->status != "withdrawn" ) {
		$pt->parse("STATUS_OPTION","status_option","true");
		$pt->parse("SAVE_BUTTON","save_button","true");
	}
}

if ( $order->status != "closed" && $order->status != "withdrawn" ) {
	$pt->parse("WITHDRAW_BUTTON","withdraw_button","true");
}

if ( isset($_REQUEST["order_churn_contact"]) ) {
	$pt->setVar("ORDER_CHURN_CONTACT",$_REQUEST["order_churn_contact"]);
}

if ( isset($_REQUEST["order_churn_contact_num"]) ) {
	$pt->setVar("ORDER_CHURN_CONTACT_NUM",$_REQUEST["order_churn_contact_num"]);
}

if ( isset($_REQUEST["submit_comment"]) ) {
	if ( isset($_REQUEST["comment"]) && !empty($_REQUEST["comment"]) && !empty($_REQUEST["comment_visibility"]) ) {
		create_comment($user->class,$order->order_id,$_REQUEST["comment"],$_REQUEST["comment_visibility"]);
	}
}

$comments = new order_comments();
$comments->order_id = $order->order_id;
$comments_arr = $comments->get_comments();

$services = new services();
$services->service_id = $order->service_id;
$services->load();

$customer = new customers();
$customer->customer_id = $services->customer_id;
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

for ($i=0; $i < count($comments_arr); $i++) { 
	$pt->clearVar("COMMENT_DATE");
	$pt->clearVar("USERNAME");
	$pt->clearVar("COMMENT");
	$pt->clearVar("BG_COLOR");
	$pt->setVar("COMMENT_DATE",$comments_arr[$i]["td"]);
	if ( $user->class != "customer" ) {
		$pt->setVar("USERNAME",$comments_arr[$i]["username"]);
	}
	if ( $comments_arr[$i]["comment_visibility"] == "wholesaler" ) {
		$pt->setVar("BG_COLOR","#eff4ff");
	} else if ( $comments_arr[$i]["comment_visibility"] == "internal" ) {
		$pt->setVar("BG_COLOR","#F5D593");
	} else {
		$pt->setVar("BG_COLOR", "white");
	}

	if ( ($user->class == "customer" && $comments_arr[$i]["comment_visibility"] == "customer") ||
		($user->class == "reseller" && $comments_arr[$i]["comment_visibility"] == "customer") ||
		($user->class == "reseller" && $comments_arr[$i]["comment_visibility"] == "wholesaler" && $user->access_id == $customer->wholesaler_id) ||
		($user->class == "admin" && $comments_arr[$i]["comment_visibility"] == "customer" && $user->access_id == 1) ||
		($user->class == "admin" && $comments_arr[$i]["comment_visibility"] == "wholesaler" && $user->access_id == 1) ||
		($user->class == "admin" && $comments_arr[$i]["comment_visibility"] == "internal" && $user->access_id == 1)
	 ) {
		$pt->setVar("COMMENT",$comments_arr[$i]["comment"]);
		$pt->parse("COMMENT_BOX","comment_box","true");
	}
}

if ( $user->class =="admin" && $user->access_id == 1 ) {
	$pt->parse("COMMENT_FORM","comment_form");
}

$pt->setVar("SERVICE_ID",$order->service_id);

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");
	
// Print out the page
$pt->p("WEBPAGE");

function create_comment($user,$order_id,$comments,$comment_visibility){
	$comment = new order_comments();
	$comment->order_id = $order_id;
	$comment->username = $_SESSION["username"];
	$comment->comment_visibility = $comment_visibility;
	$comment->comment = $comments;
	$comment->create();
}

function previous_state($array){

	$states = array(1 => "pending",
					// 2 => "in progress", 
					2 => "accepted", 
					3 => "awaiting access install",
					4 => "closed");

	for ($i=(count($array)-1); $i >= 0; $i--) { 
		$index_above = array_search($array[$i]["state_name"], $states); 
		// if ( $array[count($array)-1]["state_name"] == "on hold" && $array[$i]["state_name"] == "in progress" ) {
		// 	if ($key == 3) {
		// 		return $states[4];
		// 	} else {
		// 		return $states[3];	
		// 	}
		// }

		if (($array[count($array)-1]["state_name"] != $array[$i]["state_name"])) {
			if ( $array[$i]["state_name"] != "in progress" && $array[$i]["state_name"] != "on hold" ) {
				if ( $array[count($array)-1]["state_name"] == "on hold" ) {
					$key = array_search($array[$i]["state_name"], $states);
					// print_r($key);
					if ( $index_above == 1 && $array[$i+1]["state_name"] != "in progress" ) { 
						return $states[$key];
					} else {
						return $states[$key+1];	
					}
				} else {
					return $array[$i]["state_name"];
				}
			}
		}
	}
	return 0;
}