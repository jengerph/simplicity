<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/plans/groups/index.php - Group Plan
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

include_once "plans.class";
include_once "plan_groups.class";
include_once "wholesalers.class";
include_once "wholesaler_plan_groups.class";

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
    $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/accessdenied.html"));
    // Parse the main page
    $pt->parse("MAIN", "main");
    $pt->parse("WEBPAGE", "outside");

    // Print out the page
    $pt->p("WEBPAGE");

    exit();
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/wholesalers/group/index.html"));
  
}

$pt->setFile(array("plan_group_table" => "base/manage/wholesalers/group/plan_group_table.html",
                    "group_form" => "base/manage/wholesalers/group/group_form.html"));

$groups = new plan_groups();
$groups_arr = $groups->get_plan_groups();

for ( $a = 0; $a < count($groups_arr); $a++ ) {

  $wholesaler = new wholesalers();
  $wholesaler->wholesaler_id = $groups_arr[$a]["wholesaler_id"];
  $wholesaler->load();

  $wholesaler_arr = $wholesaler->get_wholesalers();

  $wholesaler_plan_groups = new wholesaler_plan_groups();
  $wholesaler_plan_groups->group_id = $groups_arr[$a]["group_id"];
  $wpg_array = $wholesaler_plan_groups->get_wholesalers_in_group();
  
  $new_wholesaler_list = array();

  for ($b=0; $b < count($wholesaler_arr); $b++) { 
    for ($c=0; $c < count($wpg_array); $c++) { 
      if ( ($wholesaler_arr[$b]["wholesaler_id"] == $wpg_array[$c]["wholesaler_id"]) ) {
        $new_wholesaler_list[] = $wholesaler_arr[$b];
      }
    }
  }

  if ( count($wpg_array) <= 0 ) {
    $right_values = $groups->plan_groups_list_multi("right_values",$wpg_array,$groups_arr[$a]["group_id"],$groups_arr[$a]["wholesaler_id"]);
    $left_values = $groups->plan_groups_list_multi("left_values",$wholesaler_arr,$groups_arr[$a]["group_id"],$groups_arr[$a]["wholesaler_id"]);
  } else {

      $new_wholesaler_not_in_group = array();
      $exist = 0;
      for ($d=0; $d < count($wholesaler_arr); $d++) { 
        for ($e=0; $e < count($new_wholesaler_list); $e++) { 
          if ( ($wholesaler_arr[$d]["wholesaler_id"] == $new_wholesaler_list[$e]["wholesaler_id"]) ) {
              $exist = 1;
          }
        }
        if ($exist == 0) {
            $new_wholesaler_not_in_group[] = $wholesaler_arr[$d];
        }
        $exist = 0;
      }

    $right_values = $groups->plan_groups_list_multi("right_values",$new_wholesaler_list,$groups_arr[$a]["group_id"],$groups_arr[$a]["wholesaler_id"]);
    $left_values = $groups->plan_groups_list_multi("left_values",$new_wholesaler_not_in_group,$groups_arr[$a]["group_id"],$groups_arr[$a]["wholesaler_id"]);
  }

  $pt->setVar("WHOLESALER_NOT_IN_GROUP",$left_values);
  $pt->setVar("WHOLESALER_IN_GROUP",$right_values);
  $pt->setVar("GROUP_DESCRIPTION", $groups_arr[$a]["description"]);
  $pt->setVar("WHOLESALER", " - ".$wholesaler->company_name );
  $pt->setVar("GROUP_ID", $groups_arr[$a]["group_id"]);

  $pt->parse("PLAN_GROUP_TABLE","plan_group_table","true");
  // $pt->parse("WHOLESALER_DIV","group_form","true");
}

if ( isset($_REQUEST["submit"]) && isset($_REQUEST["group_id"]) ) {

  if ( isset($_REQUEST["txtRight_".$_REQUEST["group_id"]]) ) {
    $items = $_REQUEST["txtRight_".$_REQUEST["group_id"]];
    $items_arr = explode(",", $items);

    $delete_group_record = new wholesaler_plan_groups();
    $delete_group_record->group_id = $_REQUEST["group_id"];
    $delete_group_record->delete();

    for ($a=0; $a < count($items_arr); $a++) { 
      $wpg = new wholesaler_plan_groups();
      $wpg->wholesaler_id = $items_arr[$a];
      $wpg->group_id = $_REQUEST["group_id"];
      $wpg->create();
    }

    //go to
    $url = "";
            
      if (isset($_SERVER["HTTPS"])) {
          
        $url = "https://";
            
      } else {
          
        $url = "http://";
      }
  
      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/group/";
  
      header("Location: $url");
      exit();
  }
}

$pt->setVar("PAGE_TITLE", "Manage Plans");
    
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function create($id,$param,$value){

      $attributes = new plan_attributes();
      $attributes->plan_id = $id;
      $attributes->param = $param;
      $attributes->value = $value;
      $attributes->create();

}