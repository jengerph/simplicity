<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/wholesalers/irc/card/index.php - International Rate Cards
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
include_once "calls_cost.class";
include_once 'excel_writer.php';
include_once 'PHPEXCEL/PHPExcel.php';
include_once 'PHPEXCEL/PHPExcel/IOFactory.php';

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
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/wholesalers/irc/card/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/wholesalers/irc/card/index.html"));
  
}

$pt->setFile(array("rows" => "base/manage/wholesalers/irc/card/rows.html",
                    "base_cost_section" => "base/manage/wholesalers/irc/card/base_cost_section.html"));

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

$master_cards = new calls();
$master_cards->ir_id = $cards->master_international_list;
$master_cards->load();

$countries_arr = $cards->get_countries();

$for_excel_band = array();

for ($a=0; $a < count($countries_arr); $a++) { 

  $cost_value = new calls_cost();
  $cost_value->ir_id = $cards->ir_id;
  $cost_value->wholesaler_id = $cards->wholesaler_id;
  $cost_value->band = $countries_arr[$a]["band"];
  $cost_value->load();

  $master_cost_value = new calls_cost();
  $master_cost_value->ir_id = $master_cards->master_international_list;
  $master_cost_value->wholesaler_id = $master_cards->wholesaler_id;
  $master_cost_value->band = $countries_arr[$a]["band"];
  $master_cost_value->load();

  if ( empty($cost_value->cost) ) {
    $cost_value->cost = $master_cost_value->cost;
    if ( empty($master_cost_value->cost) ) {
      $cost_value->cost = "0";
    }
  }

  if ( empty($master_cost_value->cost) ) {
    $master_cost_value->cost = '0';
  }

  $pt->setVar("BAND",$countries_arr[$a]["band"]);
  $pt->setVar("DESTINATION",$countries_arr[$a]["destination_name"]);
  $pt->setVar("BASE_COST",$master_cost_value->cost);
  $pt->setVar("COST",$cost_value->cost);
  $pt->setVar("COST_PER_MINUTE","$".$cost_value->cost);
  $pt->parse("ROWS","rows","true");

  $for_excel_band[$a]["band"] = $countries_arr[$a]["band"];
  $for_excel_band[$a]["destination"] = $countries_arr[$a]["destination_name"];
  $for_excel_band[$a]["cost"] = $cost_value->cost;
}

if ( isset($_REQUEST["submit"]) ) {
  $requested_band = $_REQUEST["band"];
  $requested_wholesaler_id = $_REQUEST["wholesaler_id"];
  $requested_cost = $_REQUEST["cost"];

  if ( ($requested_wholesaler_id == $cards->wholesaler_id) ) {
    $new_cost = new calls_cost();
    $new_cost->ir_id = $cards->ir_id;
    $new_cost->wholesaler_id = $requested_wholesaler_id;
    $new_cost->band = $requested_band;
    $new_cost->cost = str_replace("$", "", $requested_cost);
    $new_cost->delete();
    $new_cost->create();
  }

  //go to
    $url = "";
            
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/irc/card/index.php?ir_id=".$cards->ir_id;

    header("Location: $url");
    exit();

}

if ( $cards->wholesaler_id != '1' ) {
  $pt->parse("BASE_COST_SECTION","base_cost_section","true");
}

if ( isset($_REQUEST["dl_template"]) ) {
  if ( $_REQUEST["dl_template"] == 'yes' ) {
    php_excel($for_excel_band);
  }
}

if ( isset($_REQUEST["upload"]) ) {
$data = array();
if(isset($_FILES['spreadsheet'])){
    if($_FILES['spreadsheet']['tmp_name']){
    if(!$_FILES['spreadsheet']['error'])
    {

        if ( isset($_FILES['spreadsheet']) ) {
          $data = read_file($_FILES['spreadsheet']);
        } else{
            // echo "Please upload an XLSX or ODS file";
        }
    }
    else{
        // echo $_FILES['spreadsheet']['error'];
    }
    }
}

//set execution time
ini_set('max_execution_time', 300); //300 seconds = 5 minutes

//save data
for ($a=1; $a < count($data); $a++) { 
    $new_cost = new calls_cost();
    $new_cost->ir_id = $cards->ir_id;
    $new_cost->wholesaler_id = $cards->wholesaler_id;
    $new_cost->band = $data[$a][0][0];
    $new_cost->cost = str_replace("$", "", $data[$a][0][2]);
    $new_cost->delete();
    $new_cost->create();
}

//go to
  $url = "";
          
  if (isset($_SERVER["HTTPS"])) {
      
    $url = "https://";
        
  } else {
      
    $url = "http://";
  }

  $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/irc/card/index.php?ir_id=".$cards->ir_id;

  header("Location: $url");
  exit();

}

$pt->setVar("IR_ID",$cards->ir_id);
$pt->setVar("CARD_DESCRIPTION",$cards->description);
$pt->setVar("WHOLESALER_ID",$cards->wholesaler_id);
$pt->setVar("PAGE_TITLE", "View International Rate Card");
    
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");