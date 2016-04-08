<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/usage/index.php - View Usage
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
include_once "accounting.class";
include_once "misc.class";
include_once "services.class";
include_once "customers.class";
include_once "service_attributes.class";
include_once "plans.class";
include_once "plan_attributes.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

switch ($user->class) {
	case 'admin':
		$pt->setFile(array("outside" => "base/outside1.html"));		
		break;
	case 'reseller':
		$pt->setFile(array("outside" => "base/outside3.html"));	
		break;
	case 'customer':
		$pt->setFile(array("outside" => "base/outside2.html"));	
		break;
	
	default:
		# code...
		break;
}

if ( isset($_REQUEST["page"]) ) {
	$page = $_REQUEST["page"];
} else {
	$page = 1;
}

$pt->setVar("PAGE_TITLE", "View Summary");
// Assign the templates to use
$pt->setFile(array("main" => "base/manage/services/usage/index.html", 
					"row" => "base/manage/services/usage/row.html", 
					"option" => "base/manage/services/usage/option.html", 
					"date_column" => "base/manage/services/usage/date_column.html", 
					"row2" => "base/manage/services/usage/row2.html", 
					"pages_header" => "base/manage/services/usage/pages_header.html", 
					"pages" => "base/manage/services/usage/pages.html", 
					"date_form" => "base/manage/services/usage/date_form.html", 
					"usage_per_month" => "base/manage/services/usage/usage_per_month.html", 
					"show_input" => "base/manage/services/usage/show_input.html", 
					"used_today" => "base/manage/services/usage/used_today.html",
					"service_stats_link" => "base/manage/services/back_link/back_link_service_stats.html"));


$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$customers = new customers();
$customers->customer_id = $service->customer_id;
$customers->load();

if ( $user->class == 'customer' ) {
	if ( $customers->customer_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
} else if ( $user->class == 'reseller' ) {
	if ( $customers->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

$plan = new plans();
$plan->plan_id = $service->retail_plan_id;
$plan->load();

$plan_attr = new plan_attributes();
$plan_attr->plan_id = $service->retail_plan_id;
$plan_attr->param = "monthly_data_allowance";
$plan_attr->get_latest();

if ( empty($plan_attr->value) ) {
	$plan_attr->plan_id = $service->wholesale_plan_id;
	$plan_attr->get_latest();
	if ( empty($plan_attr->value) ) {
		$plan_attr->value = "Unlimited ";
	}
}

$count_uploads = new plan_attributes();
$count_uploads->plan_id = $plan_attr->plan_id;
$count_uploads->param = "count_uploads";
$count_uploads->get_latest();

//declare variables
$service_attr_keys = array("username",
							"realms");
$username = "";
$total_input = 0;
$total_output = 0;
$today_total_input = 0;
$today_total_output = 0;
$today_total_input_week = 0;
$today_total_output_week = 0;
$string_for_graph = "";
$usage_dates = "";
$usage_data = "";
$initial_input = 0;
$initial_output = 0;

for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
	$service_attr = new service_attributes();
	$service_attr->service_id = $service->service_id;
	$service_attr->param = $service_attr_keys[$sk];
	$service_attr->get_attribute();
	$pt->setVar( strtoupper($service_attr->param), $service_attr->value );
	if ( $service_attr_keys[$sk] == "username" ) {
		$username =  $service_attr->value;
	} else if ( $service_attr_keys[$sk] == "realms" && !empty($username) ) {
		$username .=  "@".$service_attr->value;
	}
}
if ( isset($_REQUEST["date"]) ) {
	$dates = prev_month($_REQUEST["date"]);
	if ( $dates[0] >= date('Y-m-d') ) {
		$dates = determine_date($misc->date_bits($service->start_date));
	}

} else {
	$dates = determine_date($misc->date_bits($service->start_date));
}

$get_month = new accounting();
$get_month->username = $username;
$get_month->start_date = $dates[0];
$get_month->end_date = $dates[1];
$get_month_array = $get_month->get_user_month();

for ($i=0; $i < count($get_month_array); $i++) {
	$total_input = $total_input + (int)$get_month_array[$i]["input"];
	$total_output = $total_output + (int)$get_month_array[$i]["output"];

    $temp_input = usage_GB($get_month_array[$i]["input"]);
	if ( $temp_input < 1  ) {
		$temp_input = usage_MB($get_month_array[$i]["input"]) . " MB";
	} else {
		$temp_input .= " GB";
	}
	$temp_output = usage_GB($get_month_array[$i]["output"]);
	if ( $temp_output < 1  ) {
		$temp_output = usage_MB($get_month_array[$i]["output"]) . " MB";
	} else {
		$temp_output .= " GB";
	}

	$pt->setVar( 'USERNAME' , $username);
	$pt->setVar( 'TOTALINPUT' , $temp_input);
	$pt->setVar( 'TOTALOUTPUT' , $temp_output);
	$pt->setVar( 'DATE' , date('d-m-Y',strtotime($get_month_array[$i]["date"])));
	$pt->parse( 'ROWS', 'row2', 'true' );      
}

$usage = $total_output;

if ($count_uploads->value == 1) {
	$usage += $total_input;
}

$usage = usage_GB($usage);

//get today's usage
$get_today = new accounting();
$get_today->username = $username;
$get_today->start_date = date('Y-m-d');
$get_today_array = $get_today->get_user_today();

for ($i=0; $i < count($get_today_array); $i++) { 
	$today_total_input = $today_total_input + (int)$get_today_array[$i]["input"];
	$today_total_output = $today_total_output + (int)$get_today_array[$i]["output"];
}

$usage_today = $today_total_output;

if ($count_uploads->value == 1) {
	$usage_today += $today_total_input;
}

$usage_today = usage_GB($usage_today);

$passed_days_start = strtotime($get_month->start_date);
$passed_days_end = strtotime($get_today->start_date);
$passed_days_diff = $passed_days_end - $passed_days_start;

$days_end = strtotime($get_month->end_date);
$total_days = $days_end - $passed_days_start;

if ( $passed_days_diff > $total_days ) {
	$passed_days_end = strtotime($get_month->end_date);
	$passed_days_diff = $passed_days_end - $passed_days_start;
}

//get month
$time_week = $get_month->start_date;

$get_past_week = new accounting();
$get_past_week->username = $username;
$get_past_week->start_date = $time_week;
$get_past_week->end_date = $get_month->end_date;
$get_past_week_array = $get_past_week->get_user_month_asc();

if ( date('Y-m-d') > $dates[0] && date('Y-m-d') < $dates[1] ) {
	$usage_for_the_past = "Usage for this Month";
} else {
	$usage_for_the_past = "Usage for the past Month";
}

$pt->setVar("TOTAL_USED","for " . $dates[0] . " to " . $dates[1]);

for ($i=0; $i < count($get_past_week_array); $i++) {
	$today_total_output_week = $initial_output + (int)$get_past_week_array[$i]["output"];

	if ($count_uploads->value == 1) {
		$today_total_output_week = $today_total_output_week + (int)$get_past_week_array[$i]["input"];
	}

		$initial_output = $today_total_output_week;
		$usage_dates .= "'".date("d/m",strtotime($get_past_week_array[$i]["date"]))."',";
		$usage_data .= "'".number_format($today_total_output_week/pow(1024,floor(3)), 2, '.', '')."',";
}

if ( empty($plan_attr->value) || $plan_attr->value == "Unlimited " ) {
	$monthly_data_allowance_for_graph = round(usage_GB($total_output),-1);
} else {
	$monthly_data_allowance_for_graph = $plan_attr->value;
}

$usage_remaining = (((int)$plan_attr->value)-$usage);

if ( $usage_remaining < 0 && !isset($plan_attr->value)) {
	$usage_remaining = "Unlimited ";
} else if ( $initial_output >= $plan_attr->value ) {
	$usage_remaining = (((int)$plan_attr->value)-$usage);
}

if ($usage_remaining <= 0) {
	$usage_remaining = ($usage_remaining)*(-1) . "GB Extra Data Used";
} else {
	$usage_remaining = $usage_remaining . "GB remaining";
}

//set variables
$pt->setVar("SERVICE_ID",$service->service_id);
$pt->setVar("MONTHLY_DATA_ALLOWANCE",$plan_attr->value);
$pt->setVar("MONTHLY_CONSUMED_OUTPUT",$usage);
$pt->setVar("USED_TODAY_OUTPUT",$usage_today . "GB");
$pt->setVar("MONTHLY_LEFT", $usage_remaining);
$pt->setVar("PASSED_DAYS",date('d',$passed_days_diff));
$pt->setVar("TOTAL_DAYS",date('d',$total_days));
$pt->setVar("DATE_START",date('d-m-Y',strtotime($get_month->start_date)));
$pt->setVar("DATE_END",date('d-m-Y',strtotime($get_month->end_date)));
$pt->setVar("PREV_MONTH", months($get_month->start_date," -1 month"));
$pt->setVar("NEXT_MONTH", months($get_month->start_date," +1 month"));
$pt->setVar("VALUES_FOR_GRAPH", $string_for_graph);
$pt->setVar("USAGE_FOR_THE_PAST",$usage_for_the_past);
$pt->setVar("MONTHLY_DATA_ALLOWANCE_FOR_GRAPH",$monthly_data_allowance_for_graph);
$pt->setVar("USAGE_DATES",$usage_dates);
$pt->setVar("USAGE_DATA",$usage_data);

//show service stats link
if ( $plan->type_id == 1 && preg_match("/telstra/i", strtolower($plan->access_method)) == 0 ) {
	$pt->parse("SERVICE_STATS_LINK","service_stats_link","true");
}

//show next month button
if ( months($get_month->start_date," +1 month") > date('Y-m-d') ) {
	$pt->setVar("NEXT_STATE", "hidden");
}

// Parse the main page
$pt->parse("MAIN", "main");
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function determine_date($start_date){

	$misc = new misc();

	$datebits = $start_date;
	$start_date = '';
	$finish_date = '';
	
	if (date('d') >= $datebits[2]) {
		$start_date = date("Y-m-" . $datebits[2]);

		$year = date('Y');
		$month = date('m');
		
		$month++;
		
		if ($month == 13) {
			$month = 1;
			$year++;
		}
		
		$day = $datebits[2];
		$day--;
		
		if ($day == 0) {
			
			// Hit start of month
			
			$month--;

  		if ($month ==0) {
  			$month = 12;
  			$year--;
  		}
  		
  		$day = date('t', $misc->date_ts($year . '-' . $month . '-01 00:00:00'));
  	}
			
		$finish_date = $year . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $day);
		
	} else {

		$year = date('Y');
		$month = date('m');
		
		$month--;
		
		if ($month == 0) {
			$month = 12;
			$year--;
		}
		$start_date = $year . '-' . sprintf("%02d", $month) . '-' . $datebits[2];
		
		$year = date('Y');
		$month = date('m');
		$day = $datebits[2];
		        		
		$day--;
		
    if ($day == 0) {
			
			// Hit start of month
			
			$month--;

  		if ($month ==0) {
  			$month = 12;
  			$year--;
  		}
  		
  		$day = date('t', $misc->date_ts($year . '-' . $month . '-01 00:00:00'));
  	}
  	
		$finish_date = $year . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $day);

		
		
	}
	return array($start_date,$finish_date);
}

function prev_month($date) {
	$finish_date = '';

	$finish_date = strtotime($date);
	$finish_date = date("Y-m-d", strtotime("+1 month -1 day", $finish_date));

	return array($date,$finish_date);
}

function usage_GB($usage) {
	return number_format($usage / pow(1024,floor(2))/1024,2,'.','');
}

function usage_MB($usage){
	return number_format($usage/pow(1024,floor(2)), 2, '.', '');
}

function months($date,$month){
	return date('Y-m-d', strtotime($date.$month));
}