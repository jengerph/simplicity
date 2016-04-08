<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/users/index.php - Edit Users
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

include_once "user.class";
include_once "customers.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", 
						"main" => "base/manage/users/index.html",
						"add_link" => "base/manage/users/add_link_user.html"));
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", 
						"main" => "base/manage/users/index.html",
						"add_link" => "base/manage/users/add_link_user.html"));
} else if ( $user->class == 'admin' ) {
	$pt->setFile(array("outside" => "base/outside1.html",
						"main" => "base/manage/users/index.html",
						"add_link" => "base/manage/users/add_link_admin.html"));
}


$pt->setVar("PAGE_TITLE", "Manage Users");

// Assign the templates to use
$pt->setFile(array("row" => "base/manage/users/row.html"));

if (!isset($_REQUEST['inactive'])) {
	$_REQUEST['inactive'] = 'yes';
}

$pt->setVar('INACTIVE_DISPLAY', $_REQUEST['inactive']);
if ($_REQUEST['inactive'] == 'yes') {
	$pt->setVar('INACTIVE_NEW', 'no');
} else {
	$pt->setVar('INACTIVE_NEW', 'yes');
}
$users = array();

if ( isset($_REQUEST["wholesaler_id"]) ) {
	$access_id = $_REQUEST["wholesaler_id"];
	$user_class = "reseller";
	$pt->setVar("USER_ID",$access_id);
	$pt->setVar("USER_CLASS","wholesaler_id");
	$pt->setFile(array("add_link" => "base/manage/users/add_link_user.html"));
} else if ( isset($_REQUEST["customer_id"]) ) {
	$access_id = $_REQUEST["customer_id"];
	$user_class = "customer";
	$pt->setVar("USER_ID",$access_id);
	$pt->setVar("USER_CLASS","customer_id");
	$pt->setFile(array("add_link" => "base/manage/users/add_link_user.html"));
} else {
	$access_id = $user->access_id;
	$user_class = $user->class;
}

//check if has access
$classes = array();
$classes['customer'] = 0;
$classes['reseller'] = 1;
$classes['admin'] = 2;

$go = 1;
if ($user->class != 'admin') {
	
	if ($classes[$user_class] > $classes[$user->class]) {
		
		$pt->setVar('ERROR_MSG', 'Error: User classes can only be less than or equal to your access class');
		$go = 0;
	}
}

if ( $user->class == "customer" && $user_class == "customer" && $access_id != $user->access_id ) {
	$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this customer');
	$go = 0;
}

if ( $user->class == "reseller" && $user_class == "reseller" && $access_id != $user->access_id ) {
	$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this wholesaler');
	$go = 0;
}

if ( $user->class == "reseller" && $user_class == "customer") {
	//check customer if belong to wholesaler
	$customer = new customers();
	$customer->customer_id = $access_id;
	$customer->load();
	if ( $customer->wholesaler_id != $user->access_id ) {
		$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this wholesaler');
		$go = 0;
	}
}

if ( $go == 1 ) {
	if ($user->class == 'admin' && $access_id == 0 ) {
		$users = $user->getAll($_REQUEST['inactive'],'',$user_class,$user->access_id);
	} else if ($user->class == 'admin' && $access_id != 0) {
		$users = $user->getAll($_REQUEST['inactive'],'',$user_class,$access_id);
	} else if (($user->class=='reseller' || $user->class=='customer') && $access_id !=0 ) {
		$users = $user->getAll($_REQUEST['inactive'],$user->state,$user_class,$access_id);
	}
}
while ($cel = each($users)) {
	
	$user->username = $cel['value'];
	$user->load();
	
	$pt->setVar('USERNAME', $user->username);
	$pt->setVar('LAST_NAME', $user->last_name);
	$pt->setVar('FIRST_NAME', $user->first_name);
	$pt->setVar('CLASS', $user->class);
	
	$pt->parse('ROWS','row','true');
	
}

$pt->parse("ADD_LINK","add_link","true");
// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");



?>
