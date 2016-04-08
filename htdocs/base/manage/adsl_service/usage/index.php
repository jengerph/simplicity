<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/adsl_service/usage/index.php - View Usage
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
	
} elseif ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
}

if ( isset($_REQUEST["page"]) ) {
	$page = $_REQUEST["page"];
} else {
	$page = 1;
}

$pt->setVar("PAGE_TITLE", "View Summary");
// Assign the templates to use
if ( $user->class == 'admin' ){
	$pt->setFile(array("outside1" => "base/outside1.html","outside2" => "base/outside2.html", "main" => "base/manage/adsl_service/usage/index.html", "row" => "base/manage/adsl_service/usage/row.html", "option" => "base/manage/adsl_service/usage/option.html", "date_column" => "base/manage/adsl_service/usage/date_column.html", "row2" => "base/manage/adsl_service/usage/row2.html", "pages_header" => "base/manage/adsl_service/usage/pages_header.html", "pages" => "base/manage/adsl_service/usage/pages.html", "date_form" => "base/manage/adsl_service/usage/date_form.html", "usage_per_month" => "base/manage/adsl_service/usage/usage_per_month.html", "view_all" => "base/manage/adsl_service/usage/view_all.html"));
}
$accounting = new accounting();
$all_accounting = $accounting->get_all();

$users = array();
$users_usage = array();


for ( $x = 0; $x < count($all_accounting); $x++ ) {
	if ( !in_array($all_accounting[$x]["username"], $users) ) {
		$users[] = $all_accounting[$x]["username"];
	}
}

$array_divide = array_chunk($users, 20);

if ( isset($_REQUEST["select_username"]) && $_REQUEST["select_username"] != "view_all" ) {
	$username = $_REQUEST["select_username"];
	$user_summary = new accounting();
	$user_summary->username = $username;
	$user_array = $user_summary->get_user();
	$total_input = 0;
	$total_output = 0;

	if ( isset($_REQUEST["contract_start"]) && $_REQUEST["contract_start"] != "" ) {
		$contract_start = $_REQUEST["contract_start"];
	} else {
		$contract_start = "9/23/2014";
	}

	if ( isset($_REQUEST["contract_end"]) && $_REQUEST["contract_start"] != "" ) {
		$contract_end = $_REQUEST["contract_end"];
	} else {
		$contract_end = "9/22/2016";
	}

	$date_range = $user_summary->date_range($contract_start,$contract_end);
	$pt->setVar( 'DATE_RANGE' , $date_range);

	if (isset($_REQUEST["date_range"])) {
		$date_start = $_REQUEST["date_range"];
		$date_start = str_replace("_", "-", $date_start);

		$time_end = strtotime($date_start);
		$time_end = date("Y-m-d", strtotime("+1 month -1 day", $time_end));

		$get_month = new accounting();
		$get_month->username = $username;
		$get_month->start_date = $date_start;
		$get_month->end_date = $time_end;
		$get_month_array = $get_month->get_user_month();

		for ($i=0; $i < count($get_month_array); $i++) { 
			$total_input = $total_input + (int)$get_month_array[$i]["input"];
			$total_output = $total_output + (int)$get_month_array[$i]["output"];

			$pt->setVar( 'USERNAME' , $username);
			$pt->setVar( 'TOTALINPUT' , $get_month_array[$i]["input"]);
			$pt->setVar( 'TOTALOUTPUT' , $get_month_array[$i]["output"]);
			$pt->setVar( 'DATE' , $get_month_array[$i]["date"]);
			$pt->parse( 'ROWS', 'row2', 'true' );
		}

		$total_input = number_format($total_input/pow(1024,floor(3)), 2, '.', '');
		$total_output = number_format($total_output/pow(1024,floor(3)), 2, '.', '');
		$pt->setVar( 'START_DATE' , $date_start);
		$pt->setVar( 'END_DATE' , $time_end);
		$pt->setVar( 'TOTAL_INPUT' , $total_input . " GB");
		$pt->setVar( 'TOTAL_OUTPUT' , $total_output . " GB");

		$pt->setVar('DR_'.$_REQUEST["date_range"].'_SELECT', ' selected');
	}

		$pt->setVar( 'CONTRACT_START' , $contract_start);
		$pt->setVar( 'CONTRACT_END' , $contract_end);
	$pt->parse( 'USAGE_PER_MONTH', 'usage_per_month', 'true' );

} else {

	for ($z=0; $z < count($array_divide[$page-1]); $z++) { 
				$temp_in = 0;
				$temp_out = 0;
		for ($a=0; $a < count($all_accounting); $a++) { 
			if ( $array_divide[$page-1][$z] == $all_accounting[$a]["username"] ) {
				$temp_in = $temp_in + (int)$all_accounting[$a]["input"];
				$temp_out = $temp_out + (int)$all_accounting[$a]["output"];
			}
		}
		$users_usage[$z]["input"] = $temp_in;
		$users_usage[$z]["output"] = $temp_out;
	}

		for ($b = 0; $b < count($array_divide[$page-1]); $b++) {
			$pt->setVar( 'USERNAME' , $array_divide[$page-1][$b]);
			$pt->setVar( 'TOTALINPUT' , number_format($users_usage[$b]["input"]/pow(1024,floor(3)), 2, '.', ''));
			$pt->setVar( 'TOTALOUTPUT' , number_format($users_usage[$b]["output"]/pow(1024,floor(3)), 2, '.', ''));
			$pt->parse( 'ROWS', 'row', 'true' );
		}
			$pt->setVar( 'TOTAL_HEADER' , "Total ");
	$total_pages = count($users);
	$total_pages = (int)$total_pages / 20;
	$modulo = (int)$total_pages % 20;
	$total_pages = (int)$total_pages;
	if ($modulo > 0) {
		$total_pages = $total_pages + 1;
	}
	if ( $page > 5 ) {
		$pagination_s = $page - 5;
	} else {
		$pagination_s = 0;
	}
	if ( $page <= $total_pages - 4 ) {
		$pagination_e = $page + 4;
	} else {
		$pagination_e = $total_pages;
	}
	if ($page != $pagination_s && $page > 5) {
		$pt->setVar( 'PAGE_NUMBER1' , $page-5);
		$pt->setVar( 'PAGE_NUMBER2' , "...");
		$pt->parse( 'PAGINATION_BAR', 'pages', 'true' );
	}
	for ( $c = $pagination_s; $c < $pagination_e; $c++ ) {
		$pt->setVar( 'PAGE_NUMBER1' , $c + 1);
		$pt->setVar( 'PAGE_NUMBER2' , $c + 1);
		$pt->parse( 'PAGINATION_BAR', 'pages', 'true' );
	}
	if ($pagination_e != $total_pages) {
		$pt->setVar( 'PAGE_NUMBER1' , $c + 1);
		$pt->setVar( 'PAGE_NUMBER2' , "...");
		$pt->parse( 'PAGINATION_BAR', 'pages', 'true' );
	}
	$pt->parse( 'PAGES_HEADER', 'pages_header', 'true' );

	$pt->parse( 'VIEW_ALL', 'view_all', 'true' );
}
for ($b = 0; $b < count($users); $b++) {
	$pt->setVar( 'USERNAME' , $users[$b]);

	if(isset($_REQUEST["select_username"])){
		if($_REQUEST["select_username"] == $users[$b]){
			$pt->setVar('USERNAME_SELECT', ' selected');
		}else{
			$pt->setVar('USERNAME_SELECT', '');
		}
	}
	$pt->parse( 'OPTION', 'option', 'true' );
}

// Parse the main page
$pt->parse("MAIN", "main");

// Correct outside
if ($user->class != 'customer' || $user->class != 'reseller') {
	$pt->parse("WEBPAGE", "outside1");
} else {
	$pt->parse("WEBPAGE", "outside2");
}	

// Print out the page
$pt->p("WEBPAGE");

