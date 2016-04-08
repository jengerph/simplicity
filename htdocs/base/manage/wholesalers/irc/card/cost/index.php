<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/irc/card/cost/index.php - Change Cost Per Minute
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../../setup.inc";

include "../../../../../doauth.inc";

include_once "calls.class";
include_once "calls_cost.class";
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
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/wholesalers/irc/card/cost/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/wholesalers/irc/card/cost/index.html"));
  
}

$pt->setFile(array("table_head_admin" => "base/manage/wholesalers/irc/card/cost/table_head_admin.html",
                    "table_head_wholesaler" => "base/manage/wholesalers/irc/card/cost/table_head_wholesaler.html",
                    "rows_admin" => "base/manage/wholesalers/irc/card/cost/rows_admin.html",
                    "rows_wholesaler" => "base/manage/wholesalers/irc/card/cost/rows_wholesaler.html",
                    "base_row" => "base/manage/wholesalers/irc/card/cost/base_row.html"));

if ( !isset($_REQUEST["ir_id"]) ) {
	echo "Card ID is invalid";
	exit();
}

$cards = new calls();
$cards->ir_id = $_REQUEST["ir_id"];
$cards->load();

//check if user is allowed to see this page
if ( $user->access_id != '1' && $cards->wholesaler_id != $user->access_id ) {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
}

$countries_arr = $cards->get_countries();

if ( $cards->wholesaler_id != '1' ) {
  $pt->parse("TABLE_HEAD","table_head_wholesaler","true");
  $template_row_key = "rows_wholesaler";
} else {
  $pt->parse("TABLE_HEAD","table_head_admin","true");
  $template_row_key = "rows_admin";
}

for ($a=0; $a < count($countries_arr); $a++) { 

  $cost_value = new calls_cost();
  $cost_value->ir_id = $cards->ir_id;
  $cost_value->wholesaler_id = $cards->wholesaler_id;
  $cost_value->band = $countries_arr[$a]["band"];
  $cost_value->load();

  if ( empty($cost_value->cost) ) {
    $cost_value->cost = "0";
  }

  $base_cost = new calls_cost();
  $base_cost->ir_id = $cards->master_international_list;
  $base_cost->wholesaler_id = '1';
  $base_cost->band = $countries_arr[$a]["band"];
  $base_cost->load();

  if ( empty($base_cost->cost) ) {
    $base_cost->cost = "0";
  }

  $pt->setVar("DESTINATION",$countries_arr[$a]["destination_name"]);
  $pt->setVar("BASE_COST_PER_MINUTE","$".$base_cost->cost);
  $pt->setVar("COST_PER_MINUTE","$".$cost_value->cost);
  $pt->setVar("BAND",$countries_arr[$a]["band"]);
  $pt->parse("ROWS",$template_row_key,"true");
}

if ( isset($_REQUEST["submit"]) ) {
    $cost = array();
    $new_cost_arr = $_REQUEST["band"];

    $keys = array_keys($new_cost_arr);
    for ($b=0; $b < count($keys); $b++) { 
      //compare previous values
      $previous = new calls_cost();
      $previous->ir_id = $cards->ir_id;
      $previous->wholesaler_id = $cards->wholesaler_id;
      $previous->band = $keys[$b];
      $previous->load();
      
      $new_cost = new calls_cost();
      $new_cost->ir_id = $cards->ir_id;
      $new_cost->wholesaler_id = $cards->wholesaler_id;
      $new_cost->band = $keys[$b];
      $new_cost->cost = str_replace("$", "", $_REQUEST["band"][$keys[$b]]);

      if ( floatval(str_replace("$", "", $previous->cost)) != floatval(str_replace("$", "", $new_cost->cost)) ) {
        $previous->delete();
        $new_cost->create();
      }
    }
    //go to
    $url = "";
            
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/irc/card/cost/index.php?ir_id=".$cards->ir_id;

    header("Location: $url");
    exit();

}

if ( $cards->wholesaler_id != '1' ) {
  $pt->parse("BASE_ROW","base_row","true");
}

$service_type = new service_types();
$service_type->type_id = $cards->type_id;
$service_type->load();

$base_irc = new calls();
$base_irc->ir_id = $cards->master_international_list;
$base_irc->load();

$pt->setVar("IR_ID",$cards->ir_id);
$pt->setVar("CARD_DESCRIPTION",$cards->description);
$pt->setVar("SERVICE_TYPE",$service_type->description);
$pt->setVar("SUB_TYPE",strtoupper($cards->sub_type));
$pt->setVar("WHOLESALER_ID",$cards->wholesaler_id);
$pt->setVar("BASE_IRC",$base_irc->description);

$pt->setVar("PAGE_TITLE", "Change Cost Per Minute");
    
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");