<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/customers/add/add_auth_rep/index.php - Add Authorised Representative
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
//	Date			Id			Description of change
//	--------	------	---------------------------------------------------------------------
//	20161214 	jenger 	adding first authorised representative details now defaults details 
//										from customer details
//
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../setup.inc";

include "../../../../doauth.inc";

include_once "customers.class";
include_once "authorised_rep.class";
// include_once "secondary_ids.class";
include_once "requirement_documents.class";
include_once "wholesalers.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

$customers = new customers();
$authorised_rep = new authorised_rep();
// $secondary_id = new secondary_ids();
$requirement_documents = new requirement_documents();

if ( isset($_REQUEST["customer_id"]) ) {
	$customers->customer_id = $_REQUEST["customer_id"];
}

$customers->load();

if ( $user->class == 'customer' ) {
	if ( $customers->customer_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

//check user if agent
$agent = new customers();
$agent->customer_id = $user->access_id;
$agent->load();

if ($user->class == 'customer' && $customers->agent != $user->access_id) {
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/accessdenied.html"));
	// Parse the main page
	$pt->parse("MAIN", "main");
	$pt->parse("WEBPAGE", "outside");

	// Print out the page
	$pt->p("WEBPAGE");

	exit();
	
}

// Assign the templates to use
if ( $user->class == "admin" ) {
	$pt->setFile(array("outside1" => "base/outside1.html",
						"outside2" => "base/outside2.html", 
						));
} else if ( $user->class == "reseller" ) {
	$pt->setFile(array("outside1" => "base/outside3.html",
						"outside2" => "base/outside2.html"));
} else if ( $user->class == "customer" ) {
	$pt->setFile(array("outside1" => "base/outside2.html"));
}

$pt->setFile(array("main" => "base/manage/customers/add/add_auth_rep/index.html", 
					"secondary_id_row" => "base/manage/customers/add/secondary_id_row.html",
					"primary_attachment" => "base/manage/customers/add/add_auth_rep/primary_attachment.html",
					"primary_id_check" => "base/manage/customers/add/add_auth_rep/primary_id_check.html",
					"secondary_attachment" => "base/manage/customers/add/add_auth_rep/secondary_attachment.html",
					"secondary_id_check" => "base/manage/customers/add/add_auth_rep/secondary_id_check.html",
					"primary_id_section" => "base/manage/customers/add/add_auth_rep/primary_id_section.html",
					"secondary_id_section" => "base/manage/customers/add/add_auth_rep/secondary_id_section.html"));

if ( $user->class == 'reseller' ) {
	if ( $customers->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}
$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $customers->wholesaler_id;
$wholesaler->load();

if ( isset($_REQUEST["ar_primary_id"]) ) {
	$authorised_rep->primary_id = $_REQUEST["ar_primary_id"];
}

// if ( isset($_REQUEST["add_secondary"]) ) {
// 	$authorised_rep->title = $_REQUEST['ar_title'];
// 	$authorised_rep->first_name = $_REQUEST['ar_first_name'];
// 	$authorised_rep->middle_name = $_REQUEST['ar_mid_name'];
// 	$authorised_rep->surname = $_REQUEST['ar_surname'];
// 	$authorised_rep->birthdate = date("Y-m-d", strtotime($_REQUEST['ar_birthdate']) );
// 	$authorised_rep->position = $_REQUEST['ar_position'];
// 	$authorised_rep->primary_id = $_REQUEST['ar_primary_id'];
// 	$authorised_rep->primary_id_number = $_REQUEST['ar_primary_id_number'];
// 	$authorised_rep->email = $_REQUEST['ar_email'];
// 	$authorised_rep->contact_number = $_REQUEST['ar_contact_number'];
// }

if (isset($_REQUEST['submit'])) {
	
	// Add new authorised representative
	$error_msg = '';

	$authorised_rep->title = $_REQUEST['ar_title'];
	$authorised_rep->first_name = $_REQUEST['ar_first_name'];
	$authorised_rep->middle_name = $_REQUEST['ar_mid_name'];
	$authorised_rep->surname = $_REQUEST['ar_surname'];
	// $authorised_rep->birthdate = date("Y-m-d", strtotime($_REQUEST['ar_birthdate']) );
	$birthdate = strtotime(str_replace('/', '.', $_REQUEST['ar_birthdate']));
	$authorised_rep->birthdate = date('Y-m-d',$birthdate);
	$authorised_rep->position = $_REQUEST['ar_position'];
	if ( $wholesaler->require_ar_idcheck == "yes" ) {
		$authorised_rep->primary_id = $_REQUEST['ar_primary_id'];
		$authorised_rep->primary_id_number = $_REQUEST['ar_primary_id_number'];
	}
	$authorised_rep->email = $_REQUEST['ar_email'];
	$authorised_rep->contact_number = $_REQUEST['ar_contact_number'];

	$secondary_count = 0;

	foreach($_REQUEST as $key => $value) {
	  $pos = strpos($key , "ar_secondary_list_");
	  if ($pos === 0){
	    $secondary_count = $secondary_count + 1;
	  }
	}

	$secondary_ids = array();
	$id_secondary = array();
	$error_id_secondary = 0;

	for ( $x = 0; $x < $secondary_count-1; $x++ ) {
		$secondary_ids[$x]["id"] = $_REQUEST["ar_secondary_list_".$x];
		$secondary_ids[$x]["number"] = $_REQUEST["ar_secondary_id_number_".$x];
		$secondary_ids[$x]["file"] = $_FILES["ar_s_attachment_".$x];
		if ($_REQUEST["ar_secondary_list_".$x] !=0 && $secondary_ids[$x]["file"]["tmp_name"]==""){
			$error_id_secondary = 1;
		}
	}
	$ar = $authorised_rep->validate();

	$req_doc =  new requirement_documents();
	$req_doc->customer_id = $customers->customer_id;
	$req_doc_all = $req_doc->get_all();
	$primary_id = array();
	$secondary_ids_list = array();
	for ($j=0; $j < count($req_doc_all); $j++) { 
		if ( $req_doc_all[$j]['category'] == 'primary' ) {
			$primary_id[] = $req_doc_all[$j];
		} else {
			$secondary_ids_list[] = $req_doc_all[$j];
		}
	}
	
	// $temp = new authorised_rep();
	// $temp->id = $authorised_rep->primary_id;
	// $primary_points = $temp->get_points();
	// $secondary_points = 0;
	// for ( $z = 0; $z < count($secondary_ids); $z++ ) {
	// 	$temp2 = new authorised_rep();
	// 	$temp2->id = $secondary_ids[$z]["id"];
	// 	$temp_points = $temp2->get_points();
	// 	$secondary_points = $secondary_points + $temp_points;
	// }

	// $total_points = $primary_points + $secondary_points;

	if ($ar != 0) {
	
		$pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$ar]);

	} /*else if ($total_points < 100) {
	
		$pt->setVar('ERROR_MSG','Error: Points must be 100 or more. Your points: ' . $total_points);

	} else if ($_FILES["ar_p_attachment"]['tmp_name']=="") {
	
		$pt->setVar('ERROR_MSG','Error: There must be a Primary ID File.');

	} else if ($error_id_secondary==1) {
	
		$pt->setVar('ERROR_MSG','Error: There must be a Secondary ID File.');

	}*/ else {
	
		$authorised_rep->customer_id = $customers->customer_id;
		$authorised_rep->create();

		$requirement_documents->doc_id = $_REQUEST['req_primary_id'];
		$requirement_documents->requirement_id = $authorised_rep->primary_id;
		$requirement_documents->requirement_number = $authorised_rep->primary_id_number;
		$requirement_documents->customer_id = $authorised_rep->customer_id;
		$requirement_documents->authorised_rep = $authorised_rep->id;
		$requirement_documents->file_name = $_FILES["ar_p_attachment"]["name"];
		$upload_exts = end(explode(".", $_FILES["ar_p_attachment"]["name"]));
		$requirement_documents->file_type = $upload_exts;
		$requirement_documents->category = "primary";

		$fp      = fopen($_FILES["ar_p_attachment"]['tmp_name'], 'r');
		$requirement_documents->file = fread($fp, filesize($_FILES['ar_p_attachment']['tmp_name']));
		fclose($fp);
		
		$requirement_documents->create();

		for ( $z = 0; $z < count($secondary_ids); $z++ ) {
			$sec_id = new requirement_documents();
			$sec_id->customer_id = $customers->customer_id;
			$sec_id->authorised_rep = $authorised_rep->id;
			$sec_id->category = "secondary";
			$sec_id->requirement_id = $secondary_ids[$z]["id"];
			$sec_id->requirement_number = $secondary_ids[$z]["number"];
			$sec_id->file_name = $secondary_ids[$z]["file"]["name"];
			$upload_exts = end(explode(".", $secondary_ids[$z]["file"]["name"]));
			$sec_id->file_type = $upload_exts;
			$fp      = fopen($secondary_ids[$z]["file"]['tmp_name'], 'r');

			$sec_id->file = fread($fp, filesize($secondary_ids[$z]["file"]['tmp_name']));
			fclose($fp);

			$sec_id->create();
		}
		
    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/customers/edit/auth_rep/?customer_id=".$customers->customer_id;

    header("Location: $url");
    exit();		
  
	}
}

if ( isset($_REQUEST["ar_birthdate"]) && !empty($_REQUEST["ar_birthdate"]) ) {
	$birthdate = date("d/m/Y", strtotime($_REQUEST["ar_birthdate"]));
} else {
	$birthdate = "";
}

// 20161214 begin
$authorised_rep->customer_id = $customers->customer_id;
if (!$authorised_rep->get_contacts()) {
	$authorised_rep->first_name = $customers->first_name;
	$authorised_rep->surname = $customers->last_name;
	$birthdate = date("d/m/Y", strtotime($customers->birthdate));
	$authorised_rep->email = $customers->email;
	$authorised_rep->contact_number = $customers->mobile;	
}
// 20161214 end

$pt->setVar('CUSTOMER_ID', $customers->customer_id);
$pt->setVar('AR_FIRST_NAME', $authorised_rep->first_name);
$pt->setVar('AR_MID_NAME', $authorised_rep->middle_name);
$pt->setVar('AR_SURNAME', $authorised_rep->surname);
$pt->setVar('AR_BIRTHDATE', $birthdate);
$pt->setVar('AR_POSITION', $authorised_rep->position);
$pt->setVar('AR_PRIMARY_ID_NUMBER', $authorised_rep->primary_id_number);
$pt->setVar('AR_EMAIL', $authorised_rep->email);
$pt->setVar('AR_CONTACT_NUMBER', $authorised_rep->contact_number);
$pt->setVar('AR_TITLE_' . strtoupper($authorised_rep->title), ' selected');

$secondary_count = 0;
$secondary_ids = array();
$id_secondary = array();

if ($_REQUEST){
	foreach($_REQUEST as $key => $value) {
	  $pos = strpos($key , "ar_secondary_list_");
	  if ($pos === 0){
	    $secondary_count = $secondary_count + 1;
		$id_secondary[] = end(split('_',$key));
	  }
	}
}

if ($customers->type) {
$idrequirements = new authorised_rep();
$idr = $idrequirements->get_authorised_rep();
$pt->setVar('AR_PRIMARY_ID_LIST', $idrequirements->idrequirements_list( "ar_primary_id", $idr, "primary", $customers->type ));

	if (isset($_REQUEST["add_secondary"]) || count($id_secondary) > 1) {

		if (isset($_REQUEST["add_secondary"])) {
			$_REQUEST["add_secondary"] = $_REQUEST["add_secondary"] + 1;
		} else {
			$_REQUEST["add_secondary"] = count($id_secondary);
		}
		for ( $x = 0; $x < $_REQUEST["add_secondary"]; $x++ ) {

			$idrequirements = new authorised_rep();
			$idr = $idrequirements->get_authorised_rep();
			$pt->setVar('AR_SECONDARY_ID_LIST', $idrequirements->secondary_list( "ar_secondary_list_" . $x, $idr, "secondary", $customers->type, $x ));
			$pt->setVar( 'SECONDARY_COUNT', $x);

			$pt->parse( 'SECONDARY_ID_ROW', 'secondary_id_row', 'true' );
		}
		$pt->setVar("ADD_VALUE", $_REQUEST["add_secondary"]);
	} else {
		$pt->setVar('AR_SECONDARY_ID_LIST', $idrequirements->secondary_list( "ar_secondary_list_0", $idr, "secondary", $customers->type, '0' ));
		$pt->setVar("ADD_VALUE", "1");
		$pt->setVar( 'SECONDARY_COUNT', '0');
		$pt->parse( 'SECONDARY_ID_ROW', 'secondary_id_row', 'true' );
	}

}

if ( $wholesaler->require_ar_download == "yes" ) {
	//Prompt attachments
	$pt->setVar("REQUIRE_DONWLOAD","yes");
	
	$pt->parse("PRIMARY_ATTACHMENT","primary_attachment","true");
	$pt->parse("SECONDARY_ATTACHMENT","secondary_attachment","true");
}

if ( $wholesaler->require_ar_idcheck == "yes" ) {
	//Prompt ID Types and number
	$pt->parse("PRIMARY_ID_CHECK","primary_id_check","true");
	$pt->parse("SECONDARY_ID_CHECK","secondary_id_check","true");
}

if ( $wholesaler->require_ar_download == "yes" || $wholesaler->require_ar_idcheck == "yes" ) {
	$pt->parse("PRIMARY_ID_SECTION","primary_id_section","true");
	$pt->parse("SECONDARY_ID_SECTION","secondary_id_section","true");
}

$pt->setVar('AR_ID_' . $authorised_rep->primary_id . '_SELECT', ' selected');

for ( $x = 0; $x < $secondary_count; $x++ ) {
	$secondary_ids[$x]["id"] = $id_secondary[$x];
	$pt->setVar('AR_ID_' . $_REQUEST["ar_secondary_list_".$id_secondary[$x]] . '_SELECT_' . $secondary_ids[$x]["id"], ' selected');
}

$pt->setVar("PAGE_TITLE", "New Authorised Representative");
		
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

