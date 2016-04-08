<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/irc/index.php - International Rate Cards
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

include_once "calls.class";
include_once "wholesalers.class";
include_once "service_types.class";

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
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/wholesalers/irc/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/wholesalers/irc/index.html"));
  
}

$pt->setFile(array("rows" => "base/manage/wholesalers/irc/rows.html"));

if ( !isset($_REQUEST["wholesaler_id"]) || empty($_REQUEST["wholesaler_id"]) ) {
	$_REQUEST["wholesaler_id"] = $user->access_id;
}

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $_REQUEST["wholesaler_id"];

$cards = new calls();
$cards->wholesaler_id = $wholesaler->wholesaler_id;
$cards_arr = $cards->get_my_irc();

//check if user is allowed to see this page
if ( $user->access_id != '1' && $wholesaler->wholesaler_id != $user->access_id ) {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
}

for ($a=0; $a < count($cards_arr); $a++) { 
  $pt->setVar("IR_ID",$cards_arr[$a]["ir_id"]);
  $pt->setVar("DESCRIPTION",$cards_arr[$a]["description"]);

  $service_type = new service_types();
  $service_type->type_id = $cards_arr[$a]["type_id"];
  $service_type->load();

  $pt->setVar("TYPE",$service_type->description);
  $pt->setVar("SUB_TYPE",$cards_arr[$a]["sub_type"]);
  $pt->setVar("ACTIVE",$cards_arr["$a"]["active"]);
  $pt->parse("ROWS","rows","true");
}

$pt->setVar("WHOLESALER_ID",$wholesaler->wholesaler_id);
$pt->setVar("PAGE_TITLE", "Manage International Rate Cards");
    
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");
