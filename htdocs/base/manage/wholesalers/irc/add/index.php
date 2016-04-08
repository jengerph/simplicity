<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/irc/add/index.php - International Rate Cards
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
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/wholesalers/irc/add/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/wholesalers/irc/add/index.html"));
  
}

$pt->setFile(array("base_row" => "base/manage/wholesalers/irc/add/base_row.html"));

if ( !isset($_REQUEST["wholesaler_id"]) || empty($_REQUEST["wholesaler_id"]) ) {
  $_REQUEST["wholesaler_id"] = $user->access_id;
}

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $_REQUEST["wholesaler_id"];

//check if user is allowed to see this page
if ( $user->access_id != '1' && $wholesaler->wholesaler_id != $user->access_id ) {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
}


$irc = new calls();
$irc->wholesaler_id = $wholesaler->wholesaler_id;

if ( isset($_REQUEST["submit2"]) ) {
  $irc->description = $_REQUEST["description"];
  $irc->type_id = $_REQUEST["service_type"];
  $irc->sub_type = $_REQUEST["sub_type"];
  $irc->active = $_REQUEST["active"];
}

if ( isset($_REQUEST["submit"]) ) {
  $irc->description = $_REQUEST["description"];
  $irc->type_id = $_REQUEST["service_type"];
  $irc->sub_type = $_REQUEST["sub_type"];
  $irc->active = $_REQUEST["active"];

  if ( isset($_REQUEST["base_ir_id"]) ) {
    $irc->master_international_list = $_REQUEST["base_ir_id"];
  }

  $vc = $irc->validate();

  if ( $vc != 0 ) {
    $pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);
  } else if ( $wholesaler->wholesaler_id != '1' && $_REQUEST["base_ir_id"] == '0' ) {
    $pt->setVar('ERROR_MSG','Error: Select a Base International Rate Card.');
  } else {
    
    $irc->create();
    
    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/irc/?wholesaler_id=".$wholesaler->wholesaler_id;

    header("Location: $url");
    exit();
  }

}

//prepare service type options
$services2 = new service_types();
$services_list = $services2->get_services();
$list_ready = $services2->service_list('service_type',$services_list);

$pt->setVar('SERVICE_TYPE_LIST', $list_ready);
$pt->setVar("DESCRIPTION",$irc->description);
$pt->setVar("STATE_".str_replace(" ", "_", strtoupper($irc->sub_type))."_SELECT", " selected");
$pt->setVar("ST_".$irc->type_id."_SELECT", " selected");
$pt->setVar("ACTIVE_".strtoupper($irc->active)."_SELECT", " checked");
$pt->setVar("WHOLESALER_ID",$wholesaler->wholesaler_id);

//set Base International Rate Card
$base_irc = new calls();
$base_irc->type_id = $irc->type_id;
$base_irc->sub_type = $irc->sub_type;
$base_irc_arr = $base_irc->get_master_list();
$pt->setVar("BASE_IRC",$base_irc->calls_list("base_ir_id",$base_irc_arr));

if ( $wholesaler->wholesaler_id != '1' ) {
  $pt->parse("BASE_ROW","base_row","true");
}

$pt->setVar("PAGE_TITLE", "Manage International Rate Cards");
    
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");