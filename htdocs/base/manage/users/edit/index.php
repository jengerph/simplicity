<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/users/edit/index.php - Edit User
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

include_once "wholesalers.class";
include_once "customers.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if (isset($_REQUEST['get_photo'])) {
	header('Content-Type: image/jpeg');
	echo $user->photo;
	exit();
	
}
if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", 
						"main" => "base/manage/users/edit/index.html", 
						"class" => "base/manage/users/add/class_customer.html"));
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", 
						"main" => "base/manage/users/edit/index.html", 
						"class" => "base/manage/users/add/class_wholesaler.html"));
} else if ( $user->class == 'admin' ) {
	$pt->setFile(array("outside" => "base/outside1.html",
						"main" => "base/manage/users/edit/index.html", 
						"class" => "base/manage/users/add/class_admin.html"));
}

$pt->setFile(array("wholesaler_row" => "base/manage/users/add/wholesaler_row.html",
					"customer_row" => "base/manage/users/add/customer_row.html"));

$user2 = new user();

if (!isset($_REQUEST['username'])) {
	
	echo "No Username provided";
	exit(1);
	
}

$user2->username = $_REQUEST['username'];
$user2->load();
if (!$user2->exist()) {
	
	echo "Username does not exist";
	exit(1);
	
}

//check if has access
$classes = array();
$classes['customer'] = 0;
$classes['reseller'] = 1;
$classes['admin'] = 2;

$go = 1;
if ($user->class != 'admin') {
	
	if ($classes[$user2->class] > $classes[$user->class]) {
		
		$pt->setVar('ERROR_MSG', 'Error: User classes can only be less than your access class');
		$go = 0;
	}
}

if ( $user->class == "customer" && $user2->class == "customer" && $user2->access_id != $user->access_id ) {
	$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this customer');
	$go = 0;
}

if ( $user->class == "reseller" && $user2->class == "reseller" && $user2->access_id != $user->access_id ) {
	$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this wholesaler');
	$go = 0;
}

if ( $user->class == "reseller" && $user2->class == "customer") {
	//check customer if belong to wholesaler
	$customer = new customers();
	$customer->customer_id = $user2->access_id;
	$customer->load();
	if ( $customer->wholesaler_id != $user->access_id ) {
		$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this customer');
		$go = 0;
	}
}

if ( $go == 1 ) {
	if (isset($_REQUEST['submit'])) {
		
		// Edituser
		$error_msg = '';

		//$user2->username = $_REQUEST['username'];
		$user2->last_name = $_REQUEST['last_name'];
		$user2->first_name = $_REQUEST['first_name'];
		$user2->email = $_REQUEST['email'];
		$user2->email2 = $_REQUEST['email2'];
		$user2->active = $_REQUEST['active'];
		$user2->home_phone = $_REQUEST['home_phone'];
		$user2->work_phone = $_REQUEST['work_phone'];
		$user2->mobile = $_REQUEST['mobile'];
		$user2->state = $_REQUEST['state'];

		if ($_REQUEST['password'] != '') {
			$user2->password = $_REQUEST['password'];
		}

		$vc = $user2->validate();

		if ($vc != 0) {
		
			$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

		} else {
				$user2->save();

				$pt->setVar('ERROR_MSG','User saved');
		}
	}

	$pt->setVar('CLIENT_ID', $user2->client_id);
	$pt->setVar('LAST_NAME', $user2->last_name);
	$pt->setVar('FIRST_NAME', $user2->first_name);
	$pt->setVar('EMAIL', $user2->email);
	$pt->setVar('EMAIL2', $user2->email2);
	$pt->setVar('HOME_PHONE', $user2->home_phone);
	$pt->setVar('WORK_PHONE', $user2->work_phone);
	$pt->setVar('MOBILE', $user2->mobile);
	$pt->setVar('ACTIVE_' . strtoupper($user2->active) . '_SELECT', ' checked');
	$pt->setVar('STATE_' . strtoupper($user2->state) . '_SELECT', ' selected');
}
	$pt->setVar('USERNAME', $user2->username);

$pt->setVar("PAGE_TITLE", "Edit User");
		
// Parse the main page
$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");



?>
