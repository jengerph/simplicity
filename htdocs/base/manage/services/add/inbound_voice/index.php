<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/inbound_voice/index.php - Inbound Voice
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../setup.inc";
include "../../../../doauth.inc";
include_once "customers.class";
include_once "service_temp.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/inbound_voice/index.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/inbound_voice/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/inbound_voice/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("existing_number" => "base/manage/services/add/inbound_voice/existing_number.html",
					"reserved_number" => "base/manage/services/add/inbound_voice/reserved_number.html",
					"reserved_yes" => "base/manage/services/add/inbound_voice/reserved_yes.html",
					"reserved_no" => "base/manage/services/add/inbound_voice/reserved_no.html"));

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
  exit();
}

$session_pointer = (isset($_REQUEST['sp']) && !empty($_REQUEST['sp'])?$_REQUEST['sp']:"");

$session_pointer1 = $_REQUEST["customer_id"] . "_" . $session_pointer;

$service_temp = new service_temp();
$service_temp->data_key = $session_pointer1;
$service_temp->load();

$service_data = unserialize($service_temp->data);

$customer = new customers();
$customer->customer_id = $_REQUEST["customer_id"];
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

if ( !isset($service_data["order_address"]) ) {
	// Done, goto list
	    $url = "";
	        
	    if ( isset($_SERVER["HTTPS"]) ) {
	        
	      $url = "https://";
	          
	    } else {
	        
	      $url = "http://";
	    }

	      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/inbound_voice/delivery_address/?customer_id=" . $customer->customer_id;

	    header("Location: $url");
	    exit();
}

if ( !isset($_REQUEST["existing_number"]) ) {
	$pt->setVar("NUMBER_YES", " checked");
	$pt->parse("FORM_PART2" , "existing_number", "true");
} else {
	$pt->setVar("NUMBER_" . strtoupper($_REQUEST["existing_number"]), " checked");
}

if ( isset($_REQUEST["existing_number"]) ) {
	if ( $_REQUEST["existing_number"] == "yes" ) {
		$pt->parse("FORM_PART2" , "existing_number", "true");
	} else {
		$pt->setVar("RESERVED_YES", " checked");
		$pt->parse("FORM_PART2" , "reserved_number", "true");
		if ( !isset($_REQUEST["reserved_number"]) ) {
			$pt->parse("FORM_PART3" , "reserved_yes", "true");
		}
	}
}

if ( isset($_REQUEST["reserved_number"]) ) {
	$pt->setVar("RESERVED_" . strtoupper($_REQUEST["reserved_number"]), " checked");
	if ( $_REQUEST["reserved_number"] == "yes" ) {
		$pt->parse("FORM_PART3" , "reserved_yes", "true");
	} else {
		$pt->parse("FORM_PART3" , "reserved_no", "true");
	}
}

if ( isset($_REQUEST["submit"]) ) {

	$error = 0;

	if ( isset($_REQUEST["existing_number"]) && $_REQUEST["existing_number"] == "yes" ) {
		if ( empty($_REQUEST["number"]) ) {
			$pt->setVar("ERROR_MSG","Error: Invalid Number.");
			$error = 1;
		} else if ( empty($_REQUEST["company_name"]) ) {
			$pt->setVar("ERROR_MSG","Error: Invalid Company Name.");
			$error = 1;
		} else if ( empty($_REQUEST["tel_account_num"]) ) {
			$pt->setVar("ERROR_MSG","Error: Invalid Telephone Account Number.");
			$error = 1;
		}

		$number_prefix = substr($_REQUEST["number"], 0, 4);

		if ( $number_prefix != 1300 && $number_prefix != 1800 ) {
			$number_prefix2 = substr($number_prefix, 0, 2);
			if ($number_prefix2 != 13) {
				$pt->setVar("ERROR_MSG","Error: Invalid Number.");
				$error = 1;
			}
		}

	} else if ( isset($_REQUEST["existing_number"]) && $_REQUEST["existing_number"] == "no" ) {
		if ( isset($_REQUEST["reserved_number"]) && $_REQUEST["reserved_number"] == "yes" ) {
			if ( empty($_REQUEST["number"]) ) {
				$pt->setVar("ERROR_MSG","Error: Invalid Number.");
				$error = 1;
			} else if ( empty($_REQUEST["company_name"]) ) {
				$pt->setVar("ERROR_MSG","Error: Invalid Company Name.");
				$error = 1;
			}
		} else if ( isset($_REQUEST["reserved_number"]) && $_REQUEST["reserved_number"] == "no" ) {
			if ( empty($_REQUEST["company_name"]) ) {
				$pt->setVar("ERROR_MSG","Error: Company Name Invalid.");
				$error = 1;
			}
		}
	}

	if ( $error == 0 ) {

		if ( !isset($_REQUEST["number"]) ) {
			$number = "";
		} else {
			$number = $_REQUEST["number"];
		}

		if ( !isset($_REQUEST["company_name"]) ) {
			$company_name = "";
		} else {
			$company_name = $_REQUEST["company_name"];
		}

		if ( !isset($_REQUEST["tel_account_num"]) ) {
			$tel_account_num = "";
		} else {
			$tel_account_num = $_REQUEST["tel_account_num"];
		}

		if ( !isset($_REQUEST["reserved_number"]) ) {
			$reserved_number = "";
		} else {
			$reserved_number = $_REQUEST["reserved_number"];
		}

		$service_data["inbound_voice"]["number"] = $number;
		$service_data["inbound_voice"]["company_name"] = $company_name;
		$service_data["inbound_voice"]["tel_account_num"] = $tel_account_num;
		$service_data["inbound_voice"]["existing_number"] = $_REQUEST["existing_number"];
		$service_data["inbound_voice"]["reserved_number"] = $reserved_number;

		$service_temp->data = serialize($service_data);
		$service_temp->save();

		//go to distribute
	    // Done, goto list
	    $url = "";
	        
	    if ( isset($_SERVER["HTTPS"]) ) {
	        
	      $url = "https://";
	          
	    } else {
	        
	      $url = "http://";
	    }

	      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/add/inbound_voice/distribute/?customer_id=" . $customer->customer_id . "&sp=" . $session_pointer;

	    header("Location: $url");
	    exit();

	}

}

if ( isset($_REQUEST["number"]) ) {
	$pt->setVar("NUMBER",$_REQUEST["number"]);
}
if ( isset($_REQUEST["company_name"]) ) {
	$pt->setVar("COMPANY_NAME",$_REQUEST["company_name"]);
}
if ( isset($_REQUEST["tel_account_num"]) ) {
	$pt->setVar("TEL_ACCOUNT_NUM",$_REQUEST["tel_account_num"]);
}

$pt->setVar("CUSTOMER_ID",$_REQUEST["customer_id"]);
$pt->setVar("SP",$session_pointer);

$pt->setVar("PAGE_TITLE", "Inbound Voice");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");