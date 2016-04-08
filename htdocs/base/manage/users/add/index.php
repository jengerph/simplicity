<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/users/add/index.php - Add User
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

include_once("class.phpmailer.php");

include_once "wholesalers.class";
include_once "customers.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	$pt->setFile(array("outside" => "base/outside2.html", 
						"main" => "base/manage/users/add/index.html"));
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", 
						"main" => "base/manage/users/add/index.html"));
} else if ( $user->class == 'admin' ) {
	$pt->setFile(array("outside" => "base/outside1.html",
						"main" => "base/manage/users/add/index.html", 
						"class" => "base/manage/users/add/class_admin.html"));
}

$pt->setFile(array("wholesaler_row" => "base/manage/users/add/wholesaler_row.html",
					"customer_row" => "base/manage/users/add/customer_row.html"));


$user2 = new user();

if ( $user->class == 'admin' ) {
	$pt->parse("CLASS","class","true");
}

if ( isset($_REQUEST["submit2"]) ) {
	$user2->class = $_REQUEST["class"];
} else {

	if ( isset($_REQUEST["customer_id"]) ) {
		$access_id = $_REQUEST["customer_id"];
		$user2->class = "customer";
		$pt->setVar("SELF","?customer_id=".$access_id);
		$pt->clearVar("CLASS");
	} else if ( isset($_REQUEST["wholesaler_id"]) ) {
		$access_id = $_REQUEST["wholesaler_id"];
		$user2->class = "reseller";
		$pt->setVar("SELF","?wholesaler_id=".$access_id);
		$pt->clearVar("CLASS");
	} else if ( $user->class == 'admin' ) {
		$user2->class = "admin";
	} else {
		$user2->class = $user->class;
		$access_id = $user->access_id;
		$pt->clearVar("CLASS");
	}
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

if ( $user->class == "customer" && $user2->class == "customer" && $access_id != $user->access_id ) {
	$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this customer');
	$go = 0;
}

if ( $user->class == "reseller" && $user2->class == "reseller" && $access_id != $user->access_id ) {
	$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this wholesaler');
	$go = 0;
}

if ( $user->class == "reseller" && $user2->class == "customer") {
	//check customer if belong to wholesaler
	$customer = new customers();
	$customer->customer_id = $access_id;
	$customer->load();
	if ( $customer->wholesaler_id != $user->access_id ) {
		$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this customer');
		$go = 0;
	}
}

if (isset($_REQUEST['submit']) && $go = 1) {
	
	// Add new user
	$error_msg = '';

	$user2->username = $_REQUEST['username'];
	$user2->last_name = $_REQUEST['last_name'];
	$user2->first_name = $_REQUEST['first_name'];
	$user2->email = $_REQUEST['email'];
	$user2->email2 = $_REQUEST['email2'];

	if ( $user2->class != 'admin' ) {
		$user2->class = $user2->class;
		$user2->access_id = $access_id;
	} else {
		$user2->class = $_REQUEST['class'];
		if ( $user2->class == "customer" ) {
			$user2->access_id = $_REQUEST['customers_list'];
		} else if ( $user2->class == "reseller" ) {
			$user2->access_id = $_REQUEST['wholesalers_list'];
		}
	}
	
	$user2->active = $_REQUEST['active'];
	$user2->home_phone = $_REQUEST['home_phone'];
	$user2->work_phone = $_REQUEST['work_phone'];
	$user2->mobile = $_REQUEST['mobile'];
	$user2->password = $_REQUEST['password'];
	$user2->state = $_REQUEST['state'];
			
	$userpassword = $user2->password;

	$vc = $user2->validate();

	if ($user->class != 'admin') {
	
		if ($classes[$user2->class] > $classes[$user->class]) {
			
			$pt->setVar('ERROR_MSG', 'Error: User classes can only be less than your access class');
			$go = 0;
		}
	}

	if ( $user->class == "customer" && $user2->class == "customer" && $access_id != $user->access_id ) {
		$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this customer');
		$go = 0;
	}else if ( $user->class == "reseller" && $user2->class == "reseller" && $access_id != $user->access_id ) {
		$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this wholesaler');
		$go = 0;
	} else if ( $user->class == "reseller" && $user2->class == "customer") {
		//check customer if belong to wholesaler
		$customer = new customers();
		$customer->customer_id = $access_id;
		$customer->load();
		if ( $customer->wholesaler_id != $user->access_id ) {
			$pt->setVar('ERROR_MSG', 'Error: You don\'t have access to this customer');
			$go = 0;
		}
	}

	if ($user2->exist()) {
		
		$pt->setVar('ERROR_MSG','Error: selected username is unvailable.');
			
	} else if ($vc != 0) {
	
		$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

	} else if ( $go == 1 )  {
		
  		$user2->create();
  
  		// Send welcome note:
      	$mail = new PHPMailer();

		$mail->From     = "noreply@xi.com.au";
		$mail->FromName = "X Integration Pty Ltd";
		$mail->Subject = "Account created";
		$mail->Host     = "127.0.0.1";
		$mail->Mailer   = "smtp";

		// Plain text body (for mail clients that cannot read HTML)
		$text_body  = "Hello " . $user2->first_name . ",\r\n";
		$text_body .= "\r\n";
		$text_body .= "This is an automatically generated email, please do not reply.\r\n";
		$text_body .= "\r\n";
		$text_body .= "Your account has now been setup for X Integration.\r\n";
		$text_body .= "\r\n";
		$text_body .= "To begin, please goto http://simplicity.xi.com.au/ and login.\r\n";
		$text_body .= "Your username is " . $user2->username . "\r\n";
		$text_body .= "Your password is " . $userpassword . "\r\n";
		$text_body .= "\r\n";
		$text_body .= "Please remember to change your password when you login to something you will remember.\r\n";
		$text_body .= "\r\n";

		$mail->Body    = $text_body;
		$mail->AddAddress($user2->email);

		//if (!$mail->Send()) {
		//    echo "There has been a mail error sending to " . $user2->email . ", they will not have receievd their password!";
		//}
	  
      // Done, goto list
      $url = "";
          
      if (isset($_SERVER["HTTPS"])) {
          
        $url = "https://";
            
      } else {
          
        $url = "http://";
      }
  
  		if ( $user2->class == 'admin' || $user->class == 'admin' ) {
  			$url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/users/";
  		} else if ( $user2->class == 'reseller' ) {
  			$url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/users/?wholesaler_id=" . $user2->access_id;
  		} else if ( $user2->class == 'customer' ) {
  			$url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/users/?customer_id=" . $user2->access_id;
  		}
      
  
      header("Location: $url");
      exit();		
	}
}

if ($user2->state == '') {
	$user2->state = $user->state;
}

$pt->setVar('USERNAME', $user2->username);
$pt->setVar('LAST_NAME', $user2->last_name);
$pt->setVar('FIRST_NAME', $user2->first_name);
$pt->setVar('EMAIL', $user2->email);
$pt->setVar('EMAIL2', $user2->email2);
$pt->setVar('HOME_PHONE', $user2->home_phone);
$pt->setVar('WORK_PHONE', $user2->work_phone);
$pt->setVar('MOBILE', $user2->mobile);
$pt->setVar('ACTIVE_' . strtoupper($user2->active) . '_SELECT', ' checked');
$pt->setVar('CLASS_' . strtoupper($user2->class) . '_SELECT', ' selected');
$pt->setVar('STATE_' . strtoupper($user2->state) . '_SELECT', ' selected');

//Get a list of wholesalers
$wholesalers = new wholesalers();
$ws = $wholesalers->get_wholesalers();
$pt->setVar('WHOLESALER_LIST', $wholesalers->wholesalers_list( "wholesalers_list", $ws ));
$pt->setVar('WS_' . strtoupper($user2->access_id) . '_SELECT', ' selected');

//Get a list of customers
$customers = new customers();

if ( $user->class == 'reseller' || $user->class == 'customer') {
	$customers_list = $customers->get_customers($user->access_id);
} else {
	$customers_list = $customers->get_customers();
}

$available_customers = array();

for ($i=0; $i < count($customers_list); $i++) { 
	$available_customers[] = $customers_list[$i]["customer_id"];
}
$access_ids = $user->get_users();
$taken_customers = array();

for ($j=0; $j < count($access_ids); $j++) { 
	$taken_customers[] = $access_ids[$j]["access_id"];
}

$available_customers = array_diff($available_customers, $taken_customers);
$available_customers = array_values($available_customers);
$pt->setVar('CUSTOMER_LIST', $customers->customers_list( "customers_list", $available_customers ));
$pt->setVar('CS_' . strtoupper($user2->access_id) . '_SELECT', ' selected');



if ( (!isset($_REQUEST["wholesaler_id"]) && !isset($_REQUEST["customer_id"])) && $user->class == 'admin' ) {
	if ( $user2->class == 'reseller' ) {
		$pt->parse("WHOLESALER_ROW","wholesaler_row","true");
	} else if ( $user2->class == 'customer' ) {
		$pt->parse("CUSTOMER_ROW","customer_row","true");
	}
}

// Parse the main page
$pt->parse("MAIN", "main");

// Correct outside
$pt->parse("WEBPAGE", "outside");	

// Print out the page
$pt->p("WEBPAGE");

function generatePassword($length=9, $strength=0) {
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUY";
	}
	if ($strength & 4) {
		$consonants .= '23456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}
 
	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}


?>
