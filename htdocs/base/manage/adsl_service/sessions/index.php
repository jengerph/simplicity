<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/adsl_service/sessions/index.php - View Session
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
include_once "radius.class";


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

$pt->setVar("PAGE_TITLE", "View Sessions");

// Assign the templates to use
if ( $user->class == 'admin' ){
	$pt->setFile(array("outside1" => "base/outside1.html",
						"outside2" => "base/outside2.html", 
						"main" => "base/manage/adsl_service/sessions/index.html", 
						"row" => "base/manage/adsl_service/sessions/row.html", 
						"option" => "base/manage/adsl_service/sessions/option.html", 
						"option_page_num" => "base/manage/services/sessions/option_page_num.html",
						"pages_header" => "base/manage/adsl_service/usage/pages_header.html", 
						"pages" => "base/manage/adsl_service/sessions/pages.html"));
}

$rad_session = new radius();
$all_rad_sess = $rad_session->get_all_usernames();
$users = array();
$users_sess = array();

for ( $x = 0; $x < count($all_rad_sess); $x++ ) {
	if ( !in_array($all_rad_sess[$x]["username"], $users) ) {
		$users[] = $all_rad_sess[$x]["username"];
	}
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

if ( isset($_REQUEST["select_username"]) && $_REQUEST["select_username"] != "0" ) {

	$username = $_REQUEST["select_username"];
	$user_radacct = new radius();
	$user_radacct->username = $username;
	
	$count_results = $user_radacct->get_user_record_count();

	$pages = ceil($count_results/50);

	$_REQUEST["page"] = ( isset($_REQUEST["page"]) && ($_REQUEST["page"] <= $pages) ? $_REQUEST["page"] : "1" );
	$user_radacct->start =  ((isset($_REQUEST["page"]) && $_REQUEST["page"] > 0 ? $_REQUEST["page"] : "1") - 1) * 50;
	$user_radacct->per_page = 50;

	for ($a=0; $a < $pages; $a++) { 
		$pt->setVar("PAGE_NUMBER",$a+1);
		$pt->parse("OPTION_PAGE_NUM","option_page_num","true");
	}

	$pt->setVar("SELECT_NUMBER_".(isset($_REQUEST["page"]) ? $_REQUEST["page"] : "1"), " selected");

	$user_array = $user_radacct->get_user_per_page();
	$temp_start = "";
	$temp_stop = "";
	$temp_servicetype = "";
	for ($c=0; $c < count($user_array); $c++) {
	$pt->setVar("ACCTSTARTTIME",date("d-m-Y H:i:s",strtotime($user_array[$c]["acctstarttime"])));
	$pt->setVar("ACCTSTOPTIME",( empty($user_array[$c]["acctstoptime"]) ? "" : date("d-m-Y H:i:s",strtotime($user_array[$c]["acctstoptime"])) ));
	$pt->setVar("ACCTINPUTOCTETS",formatBytes($user_array[$c]["acctinputoctets"]));
	$pt->setVar("ACCTOUTPUTOCTETS",formatBytes($user_array[$c]["acctoutputoctets"]));
	$pt->setVar("FRAMEDIPADDRESS",$user_array[$c]["framedipaddress"]);
	$pt->parse( 'ROWS', 'row', 'true' );
	}

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

function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('', 'k', 'M', 'G', 'T');   

    $value = round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];

    if ( $value=="NAN" ) {
    	return 0;
    } else {
    	return $value;
    }
}