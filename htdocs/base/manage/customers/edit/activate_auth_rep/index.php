<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/customers/edit/edit_auth_rep/index.php - Activate/De-Activate Authorised Representative
// $Id: 3aca96488ea3a3c3a5220e181bbd4c6e3264a308 $
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
include_once "authorised_rep.class";
include_once "requirement_documents.class";
include_once "wholesalers.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

$authorised_rep = new authorised_rep();

if ( isset($_REQUEST["id"]) ) {
	$authorised_rep->id = $_REQUEST["id"];
}

$authorised_rep->load();
if ($authorised_rep->auth_rep_active == 'yes') {
	$authorised_rep->auth_rep_active = 'no';
} else {
	$authorised_rep->auth_rep_active = 'yes';
}
$authorised_rep->save();
 
// Done, goto list
$url = "";
    
if (isset($_SERVER["HTTPS"])) {
    
  $url = "https://";
      
} else {
    
  $url = "http://";
}

$url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/customers/edit/auth_rep/?customer_id=" . $authorised_rep->customer_id;

header("Location: $url");
exit();		
