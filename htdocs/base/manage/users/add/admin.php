<?php

	$user2 = new user();

	if (isset($_REQUEST['submit'])) {
	
	// Add new user
	$error_msg = '';

	$user2->username = $_REQUEST['username'];
	$user2->last_name = $_REQUEST['last_name'];
	$user2->first_name = $_REQUEST['first_name'];
	$user2->email = $_REQUEST['email'];
	$user2->email2 = $_REQUEST['email2'];
	$user2->class = $_REQUEST['class'];
	$user2->active = $_REQUEST['active'];
	$user2->home_phone = $_REQUEST['home_phone'];
	$user2->work_phone = $_REQUEST['work_phone'];
	$user2->mobile = $_REQUEST['mobile'];
	$user2->password = $_REQUEST['password'];
	$user2->state = $_REQUEST['state'];
	$user2->wholesaler_id = $_REQUEST['wholesalers_list'];
	$user2->customer_id = $_REQUEST['customers_list'];

	if ( $user2->class == "customer" ) {
		$user2->access_id = $_REQUEST['customers_list'];
	} else if ( $user2->class == "reseller" ) {
		$user2->access_id = $_REQUEST['wholesalers_list'];
	}
			
	$userpassword = $user2->password;

	$vc = $user2->validate();

	if ($user2->exist()) {
		
		$pt->setVar('ERROR_MSG','Error: selected username is unvailable.');
			
	}
	 else if ($vc != 0) {
	
		$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

	} else {

		$classes = array();
		$classes['customer'] = 0;
		$classes['reseller'] = 1;
		$classes['admin'] = 2;
		
		$go = 1;
		if ($user->class != 'admin') {
			
			if ($classes[$user2->class] >= $classes[$user->class]) {
				
				$pt->setVar('ERROR_MSG', 'Error: User classes can only be less than your access class');
				$go = 0;
			}
		}
		
		if ($go == 1) {
  		$user2->create();
  
  		// Send welcome note:
      	require "welcome_note.php";
  
      // Done, goto list
      $url = "";
          
      if (isset($_SERVER["HTTPS"])) {
          
        $url = "https://";
            
      } else {
          
        $url = "http://";
      }
  
      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/users/";
  
      header("Location: $url");
      exit();		
    }
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
$pt->setVar('WS_' . strtoupper($user2->wholesaler_id) . '_SELECT', ' selected');

//Get a list of customers
$customers = new customers();
$customers_list = $customers->get_customers();
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
$pt->setVar('CS_' . strtoupper($user2->customer_id) . '_SELECT', ' selected');