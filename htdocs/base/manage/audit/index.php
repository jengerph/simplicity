<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/audit/index.php - View Audit
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

include_once "audit.class";
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
	
} else if ($user->class == 'reseller') {
	
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
	
}

if ( !isset($_REQUEST["page"]) ) {
  $current_page = 1;
} else {
  if ( is_numeric($_REQUEST["page"]) ) {
    $current_page = $_REQUEST["page"];
  } else {
    $current_page = 1;
  }
}

$pt->setVar("PAGE_TITLE", "View Audit");

// Assign the templates to use
$pt->setFile(array("outside1" => "base/outside1.html", "outside2" => "base/outside2.html", "main" => "base/manage/audit/index.html", "row" => "base/manage/audit/row.html", "option" => "base/manage/audit/option.html"));
$misc = new misc();

if ( isset($_REQUEST["sortby"]) ) {
	$sort = $_REQUEST["sortby"];
} else {
	$sort = "ASC";
}
	
$audit = new audit();
$audit_list = $audit->get_all_users();

for ($x = 0; $x < count($audit_list); $x++) {
	$usernames[$x] = $audit_list[$x]["username"];
}

if ( isset($_REQUEST["select_username"]) ) {
	$username = $_REQUEST["select_username"];
} else {
	$username = "view_all";
}

$audit = new audit();
$username = $username;
$audit->username = $username;

$total_entries = $audit->get_num_audit($username);
$total_pages = ceil($total_entries/10);

if ( $current_page > $total_pages ) {
  $current_page = $total_pages;
}
if ( $current_page == $total_pages ) {
  $next_page = $current_page;
  $previous_page = $current_page - 1;
} else if ( $current_page < $total_pages ) {
  $next_page = $current_page + 1;
  $previous_page = $current_page - 1;
}
if ( $previous_page <= 0 ) {
  $previous_page = $current_page;
}

$start = ($current_page - 1) * 10;
$end = 10;

$audit_list = $audit->get_all($start,$end,$username,$sort);

$pt->clearVar("ROWS");

for ($x = 0; $x < count($audit_list); $x++) {
	$pt->setVar( 'USERNAME' , $audit_list[$x]["username"]);
	$pt->setVar( 'FIELD' , $audit_list[$x]["field"]);
	$pt->setVar( 'ID' , $audit_list[$x]["id"]);
	$pt->setVar( 'ACTIVITY' , $audit_list[$x]["activity"]);
	$pt->setVar( 'DATE' , $misc->date_nice($audit_list[$x]["dt"]));
	$pt->parse( 'ROWS', 'row', 'true' );
}
$pt->setVar('SORT_ASC_CHECK', ' checked');

$result = array_unique($usernames);
$result = array_values(array_filter($result));
for ($y=0; $y < count($result); $y++) { 
	$pt->setVar( 'USERNAME' , $result[$y]);
	if(isset($_REQUEST["select_username"])){
		if($_REQUEST["select_username"] == $result[$y]){
			$pt->setVar('USERNAME_SELECT', ' selected');
		}else{
			$pt->setVar('USERNAME_SELECT', '');
		}
	}
	$pt->parse( 'OPTION', 'option', 'true' );
}

$pt->setVar('SORT_' . $sort . '_CHECK', ' checked');
$pt->setVar("CURRENT_PAGE",$current_page);
$pt->setVar("PREVIOUS_PAGE",$previous_page);
$pt->setVar("NEXT_PAGE",$next_page);
$pt->setVar("TOTAL_PAGES",$total_pages);
$pt->setVar("ENTRIES",$total_entries);
$pt->setVar("SELECT_USERNAME",$username);
$pt->setVar("SORT",$sort);

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

if ($user->class != 'customer' || $user->class != 'reseller') {
	$pt->parse("WEBPAGE", "outside1");
} else {
	$pt->parse("WEBPAGE", "outside2");
}	
	
// Print out the page
$pt->p("WEBPAGE");