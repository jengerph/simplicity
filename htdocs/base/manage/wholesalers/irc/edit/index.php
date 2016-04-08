<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/irc/edit/index.php - Edit International Rate Cards
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
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/wholesalers/irc/edit/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/wholesalers/irc/edit/index.html"));
  
}

$pt->setFile(array("base_row" => "base/manage/wholesalers/irc/add/base_row.html",
                    "master_row" => "base/manage/wholesalers/irc/edit/master_row.html"));

if ( !isset($_REQUEST["ir_id"]) ) {
	echo "International Rate Card is invalid";
	exit();
}

$irc = new calls();
$irc->ir_id = $_REQUEST["ir_id"];
$irc->load();

//check if user is allowed to see this page
if ( $user->access_id != '1' && $irc->wholesaler_id != $user->access_id ) {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
}

if ( isset($_REQUEST["submit2"]) ) {
  $irc->description = $_REQUEST["description"];
  $irc->active = $_REQUEST["active"];
}

if ( isset($_REQUEST["submit"]) ) {
  $irc->ir_id = $irc->ir_id;
  $irc->description = $_REQUEST["description"];
  $irc->active = $_REQUEST["active"];

  $vc = $irc->validate();

  if ( $vc != 0 ) {
    $pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);
  } else {
    $irc->save();
    $pt->setVar("SUCCESS_MSG","International Rate Card Saved.");
  }

}

if ( $irc->wholesaler_id != '1' ) {
  $pt->parse("MASTER_ROW","master_row","true");
}

$service_type = new service_types();
$service_type->type_id = $irc->type_id;
$service_type->load();

$pt->setVar("DESCRIPTION",$irc->description);
$pt->setVar("ACTIVE_".strtoupper($irc->active)."_SELECT", " checked");
$pt->setVar("SERVICE_TYPE",$service_type->description);
$pt->setVar("SUB_TYPE",strtoupper($irc->sub_type));

//set Base International Rate Card
$base_irc = new calls();
$base_irc->ir_id = $irc->master_international_list;
$base_irc->load();

$pt->setVar("MASTER_INTERNATIONAL_RATE_CARD",$base_irc->description);

$pt->setVar("IR_ID",$irc->ir_id);
$pt->setVar("WHOLESALER_ID",$irc->wholesaler_id);
$pt->setVar("PAGE_TITLE", "Edit International Rate Cards");
    
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");