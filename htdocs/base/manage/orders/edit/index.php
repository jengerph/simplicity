<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/orders/edit/index.php - Edit orders
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
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/orders/edit/index.html"));
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/orders/edit/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/orders/edit/index.html"));
	
}

$pt->setFile(array("retail_plan" => "base/manage/orders/edit/summary/summary_retail_plan.html", 
					"status_row" => "base/manage/orders/edit/summary/summary_status_row.html", 
					"status_option" => "base/manage/orders/edit/summary/summary_status_option.html", 
					"save_button" => "base/manage/orders/edit/summary/summary_save_button.html", 
					"withdraw_button" => "base/manage/orders/edit/summary/summary_withdraw_button.html", 
					"address_row" => "base/manage/orders/edit/summary/summary_address.html", 
					"contact_row" => "base/manage/orders/edit/summary/summary_contact_row.html", 
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

$services = new services();
$services->service_id = $order->service_id;
$services->load();

$customer = new customers();
$customer->customer_id = $services->customer_id;
$customer->load();

if ( $user->class == 'customer' ) {
	if ( $services->customer_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
} else if ( $user->class == 'reseller' ) {
	if ( $customer->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

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
	if ( $my_order_attributes[$i]['param']=="order_address" && isset($my_order_attributes[$i]['value']) ) {
		$pt->clearVar("ADDRESS_ROW");
		$pt->parse("ADDRESS_ROW","address_row","true");
	}
	if ( $my_order_attributes[$i]['param']=="order_contact" && isset($my_order_attributes[$i]['value']) ) {
		$pt->clearVar("CONTACT_ROW");
		$pt->parse("CONTACT_ROW","contact_row","true");
	}
}

if ( isset($_REQUEST['withdraw']) || (isset($_REQUEST['submit']) && $_REQUEST['change_status']=="withdrawn") ) {
	//check if there's an existing withdraw order
	$check_withdraw_order = new order_attributes();
	$check_withdraw_order->order_id = $order->order_id;
	$check_withdraw_order->param = "withdraw_order";
	$check_withdraw_order->get_latest();

	$check_withdraw_order2 = new order_attributes();
	$check_withdraw_order2->order_id = $check_withdraw_order->value;
	$check_withdraw_order2->param = "order_id";
	$check_withdraw_order2->get_attribute();

	$check_order = new orders();
	$check_order->order_id = $check_withdraw_order->value;
	$check_order->load();

	if ( $order->action != "withdraw" ) {

		 if ( ($check_order->status == "withdrawn") || ($check_order->status == "closed") || !isset($check_order->status) ) {
			//create an order when withdrawn
			$withdraw_order = new orders();
			$withdraw_order->service_id = $order->service_id;
			$withdraw_order->start = date("Y-m-d H:i:s");
			$withdraw_order->description = "withdraw order = " . $order->order_id;
			$withdraw_order->request_type = $order->request_type;
			$withdraw_order->action = "withdraw";
			$withdraw_order->status = "pending";
			$withdraw_order->create();

			$withdraw_order_attributes = new order_attributes();
			$withdraw_order_attributes->order_id = $order->order_id;
			$withdraw_order_attributes->param = "withdraw_order";
			$withdraw_order_attributes->value = $withdraw_order->order_id;
			$withdraw_order_attributes->create();

			$withdraw_order_attributes = new order_attributes();
			$withdraw_order_attributes->order_id = $withdraw_order->order_id;
			$withdraw_order_attributes->param = "order_id";
			$withdraw_order_attributes->value = $order->order_id;
			$withdraw_order_attributes->create();

			$withdraw_order_state = new orders_states();
			$withdraw_order_state->order_id = $withdraw_order->order_id;
			$withdraw_order_state->state_name = "pending";
			$withdraw_order_state->create();

			$comment = new order_comments();
		    $comment->order_id = $order->order_id;
		    $comment->username = $user->username;
		    $comment->comment_visibility = "customer";
		    $comment->comment = "Withdraw Order: http://simplicity.xi.com.au/base/manage/orders/edit/?order_id=".$withdraw_order->order_id;
		    $comment->create();

		    $comment = new order_comments();
		    $comment->order_id = $withdraw_order->order_id;
		    $comment->username = $user->username;
		    $comment->comment_visibility = "customer";
		    $comment->comment = "Main Order: http://simplicity.xi.com.au/base/manage/orders/edit/?order_id=".$order->order_id;
		    $comment->create();

		    $pt->setVar("SUCCESS_MSG","Successfully created a Withdraw Order(<a href='/base/manage/orders/edit/?order_id=".$withdraw_order->order_id."'>".$withdraw_order->order_id."</a>)");
		} else {
			if ( (!empty($check_withdraw_order->value) && !empty($check_withdraw_order2->value) && $check_withdraw_order->value == $check_withdraw_order2->order_id && $order->action != "withdraw") ) {
				$pt->setVar("ERROR_MSG","Error: There's an existing Withdraw Order(<a href='/base/manage/orders/edit/?order_id=".$check_withdraw_order2->order_id."'>".$check_withdraw_order2->order_id."</a>)");
			}
		}
	} else {
		$withdraw_order = new orders();
		$withdraw_order->order_id = $order->order_id;
		$withdraw_order->load();
		$withdraw_order->status = "withdrawn";
		$withdraw_order->save();

		$withdraw_order_state = new orders_states();
		$withdraw_order_state->order_id = $withdraw_order->order_id;
		$os_arr = $withdraw_order_state->get_by_order_id();

		$save_last = new orders_states();
		$save_last->state_id = $os_arr[count($os_arr)-1]["state_id"];
		$save_last->load();

		if ( $save_last->date_completed == "0000-00-00 00:00:00" ) {
			
			$save_last->date_completed = date("Y-m-d h:i:s");
			$save_last->save();

		}

		$order_state = new orders_states();
		$order_state->order_id = $withdraw_order->order_id;
		$order_state->state_name = "withdrawn";
		$order_state->date_estimated = $os_arr[0]["date_estimated"];
		$order_state->date_completed = date("Y-m-d H:i:s");
		$order_state->create();

		$withdraw_orders = new order_attributes();
		$withdraw_orders->order_id = $order->order_id;
		$withdraw_orders->param = "order_id";
		$withdraw_orders->get_attribute();

		$comment = new order_comments();
	    $comment->order_id = $withdraw_orders->value;
	    $comment->username = $user->username;
	    $comment->comment_visibility = "customer";
	    $comment->comment = "Withdraw Order: http://simplicity.xi.com.au/base/manage/orders/edit/?order_id=".$withdraw_order->order_id." was withdrawn.";
	    $comment->create();

	    $comment = new order_comments();
	    $comment->order_id = $withdraw_order->order_id;
	    $comment->username = $user->username;
	    $comment->comment_visibility = "customer";
	    $comment->comment = "This order was withdrawn.";
	    $comment->create();

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

				if ( $_REQUEST["change_status"] == "closed" ) {
					$order->finish = date("Y-m-d h:i:s");
				}

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
				if ( $_REQUEST["change_status"] == "closed" ) {
					$order_state->date_completed = date("Y-m-d H:i:s");
				} else {
					$order_state->date_completed = "0000-00-00 00:00:00";
				}
				$order_state->create();

				if ( isset($_REQUEST["onhold_notes"]) && !empty($_REQUEST["onhold_notes"]) ) {
					$onhold_notes = $_REQUEST["onhold_notes"];
					create_comment($user->class,$order->order_id,$onhold_notes ,"internal");
				}

				//mark service as active when order is closed and previous state is creation
				$set_service = new services();
				$set_service->service_id = $order->service_id;
				$set_service->load();
				
				if ( $order->status == "closed" && $set_service->state == "creation" && $order->action != "withdraw") {
					$set_service->state = "active";
					$set_service->save();
				}

				//change wholesale_plan_id and retail_plan_id
				$new_plan = new order_attributes();
				$new_plan->order_id = $order->order_id;
				$new_plan->param = "edit_retail_plan";
				$new_plan->get_attribute();

				$wholesaler_plan_id = new plans();
				$wholesaler_plan_id->plan_id = $new_plan->value;
				$wholesaler_plan_id->load();

				if ( $order->status == "closed" && $order->action == "plan change" ) {
					$modify_service = new services();
					$modify_service->service_id = $order->service_id;
					$modify_service->load();
					$modify_service->retail_plan_id = $new_plan->value;
					if ( $wholesaler_plan_id->parent_plan_id == '0' ) {
						$wholesaler_plan_id->parent_plan_id = $wholesaler_plan_id->plan_id;
					}
					$modify_service->wholesale_plan_id = $wholesaler_plan_id->parent_plan_id;
					$modify_service->save();

					$extra = array("static_ip","ip_block4","ip_block8","ip_block16");
						for ($i=0; $i < count($extra); $i++) {
							$sa_extra = new service_attributes();
							$sa_extra->service_id = $order->service_id;
							$sa_extra->param = $extra[$i];
							$sa_extra->get_attribute();

							$order_extra = new order_attributes();
							$order_extra->order_id = $order->order_id;
							$order_extra->param = "order_" . $extra[$i];
							$order_extra->get_attribute();

							if ( isset($order_extra->value) ) {
								$sa_extra->value = $order_extra->value;
								$sa_extra->save();
							}
						}
						//get add on order and set to closed
						$addon = new order_attributes();
						$addon->param = "parent_order";
						$addon->value = $order->order_id;
						$addon->get_order_id();

						$addon_order = new orders();
						$addon_order->order_id = $addon->order_id;
						$addon_order->load();
						$addon_order->status = "closed";
						$addon_order->save();

						$addon_previous = new orders_states();
						$addon_previous->order_id = $addon_order->order_id;
						$addon_arr = $addon_previous->get_by_order_id();

						$addon_save = new orders_states();
						$addon_save->state_id = $addon_arr[count($addon_arr)-1]["state_id"];
						$addon_save->load();

						if ( $addon_save->date_completed == "0000-00-00 00:00:00" ) {
							
							$addon_save->date_completed = date("Y-m-d h:i:s");
							$addon_save->save();

						}

						$addon_create = new orders_states();
						$addon_create->order_id = $addon->order_id;
						$addon_create->state_name = "closed";
						$addon_create->date_estimated = $addon_save->date_estimated;
						$addon_create->date_completed = date("Y-m-d H:i:s");
						$addon_create->create();

				} else if ( $order->status == "closed" && $order->action == "new" ) {
						//get add on order and set to closed
						$addon = new order_attributes();
						$addon->param = "parent_order";
						$addon->value = $order->order_id;
						$addon->get_order_id();

						$addon_order = new orders();
						$addon_order->order_id = $addon->order_id;
						$addon_order->load();
						$addon_order->status = "closed";
						$addon_order->save();

						$addon_previous = new orders_states();
						$addon_previous->order_id = $addon_order->order_id;
						$addon_arr = $addon_previous->get_by_order_id();

						$addon_save = new orders_states();
						$addon_save->state_id = $addon_arr[count($addon_arr)-1]["state_id"];
						$addon_save->load();

						if ( $addon_save->date_completed == "0000-00-00 00:00:00" ) {
							
							$addon_save->date_completed = date("Y-m-d h:i:s");
							$addon_save->save();

						}

						$addon_create = new orders_states();
						$addon_create->order_id = $addon->order_id;
						$addon_create->state_name = "closed";
						$addon_create->date_estimated = $addon_save->date_estimated;
						$addon_create->date_completed = date("Y-m-d H:i:s");
						$addon_create->create();

				} else if ( $order->status == "closed" && $order->action == "withdraw" ) { 
					$withdraw_orders = new order_attributes();
					$withdraw_orders->order_id = $order->order_id;
					$withdraw_orders->param = "order_id";
					$withdraw_orders->get_attribute();

					$main_order = new orders();
					$main_order->order_id = $withdraw_orders->value;
					$main_order->load();

					$os_previous = new orders_states();
					$os_previous->order_id = $main_order->order_id;
					$os_arr = $os_previous->get_by_order_id();

					$save_last = new orders_states();
					$save_last->state_id = $os_arr[count($os_arr)-1]["state_id"];
					$save_last->load();

					if ( $save_last->date_completed == "0000-00-00 00:00:00" ) {
						
						$save_last->date_completed = date("Y-m-d h:i:s");
						$save_last->save();

					}

					$order_state = new orders_states();
					$order_state->order_id = $main_order->order_id;
					$order_state->state_name = "withdrawn";
					$order_state->date_estimated = $os_arr[0]["date_estimated"];
					$order_state->date_completed = date("Y-m-d H:i:s");
					$order_state->create();
					if ( isset($_REQUEST["onhold_notes"]) && !empty($_REQUEST["onhold_notes"]) ) {
						$onhold_notes = $_REQUEST["onhold_notes"];
						create_comment($user->class,$main_order->order_id,$onhold_notes ,"internal");
					}

					$username = "";

					$service_attributes = new service_attributes();
					$service_attributes->service_id = $main_order->service_id;
					$service_attributes->param = "username";
					$service_attributes->get_attribute();

					$username = $service_attributes->value;

					$service_attributes = new service_attributes();
					$service_attributes->service_id = $main_order->service_id;
					$service_attributes->param = "realms";
					$service_attributes->get_attribute();

					$username .= "@" . $service_attributes->value;

					$service = new services();
					$service->service_id = $main_order->service_id;
					$service->load();

					$radius = new radius();
					$radius->username = $username;
					$radius->delete();

					$main_order->status = "withdrawn";
					$main_order->save();

					$check_other_orders = new orders();
					$check_other_orders->service_id = $service->service_id;
					$check_array = $check_other_orders->get_all_orders();

					$active = 0;

					for ( $x = 0; $x < count($check_array); $x++ ) {
						if ( $check_array[$x]['status'] != 'withdrawn' && $check_array[$x]['action'] == 'new' ) {
							$active = $active + 1;
						}
					}

					if ( $active == 0 && $service->state == "creation" ) { //mark service as inactive when order is withdrawn and it is still in creation
						$service->state = "inactive";
						$service->save();
					}

					//get add on order and set to closed
					$addon = new order_attributes();
					$addon->param = "parent_order";
					$addon->value = $main_order->order_id;
					$addon->get_order_id();

					if ( isset($addon->order_id) && !empty($addon->order_id) ) {
						$addon_order = new orders();
						$addon_order->order_id = $addon->order_id;
						$addon_order->load();
						$addon_order->status = "withdrawn";
						$addon_order->save();

						$addon_previous = new orders_states();
						$addon_previous->order_id = $addon_order->order_id;
						$addon_arr = $addon_previous->get_by_order_id();

						$addon_save = new orders_states();
						$addon_save->state_id = $addon_arr[count($addon_arr)-1]["state_id"];
						$addon_save->load();

						if ( $addon_save->date_completed == "0000-00-00 00:00:00" ) {
							
							$addon_save->date_completed = date("Y-m-d h:i:s");
							$addon_save->save();

						}

						$addon_create = new orders_states();
						$addon_create->order_id = $addon->order_id;
						$addon_create->state_name = "withdrawn";
						$addon_create->date_estimated = $addon_save->date_estimated;
						$addon_create->date_completed = date("Y-m-d H:i:s");
						$addon_create->create();

						//addon service
						$child_service = new services();
						$child_service->service_id = $addon_order->service_id;
						$child_service->load();

						if ( $service->state == "inactive" ) {
							$child_service->state = "inactive";
							$child_service->save();
						}
					}


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
				} else if ( $order->status == "closed" && $order->action == "cancel" ) { 

					//set service to inactive
					$set_service->state = "inactive";
					$set_service->save();

					//set child service
					$child_service = new services();
					$child_service->parent_service_id = $set_service->service_id;
					$child_service->child_service();
					$child_service->state= "inactive";
					$child_service->save();

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

//current
// print_r($os_arr[count($os_arr)-1]);
// print_r("<br/>");
//previous
// if ( isset($os_arr[count($os_arr)-2]) ) {
// print_r($os_arr[count($os_arr)-2]);
// }


// for ($b=0; $b < count($os_arr); $b++) {
// 	print_r($os_arr[$b]);
// 	print_r("<br/>");
// }
// exit();

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

//set Service Type
$st_order = new service_types();
$st_order->type_id = $services->type_id;
$st_order->load();

$pt->setVar("SERVICE_TYPE",$st_order->description);
$pt->setVar("ACTION",ucwords($order->action));
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