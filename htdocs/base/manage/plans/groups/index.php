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
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/plans/groups/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/plans/groups/index.html"));
  
}

$pt->setFile(array("plan_group_table" => "base/manage/plans/groups/plan_group_table.html",
                    "back_plans1" => "base/manage/plans/groups/back_plans1.html",
                    "back_plans2" => "base/manage/plans/groups/back_plans2.html",
                    "rows" => "base/manage/plans/groups/rows.html"));

$plan_groups = new plan_groups();

if ( isset($_REQUEST["wholesaler_id"]) ) {
  $wholesaler_id = $_REQUEST["wholesaler_id"];
  $pt->setVar("WHOLESALER_ID",$wholesaler_id);
  $pt->parse("BACK_LINK","back_plans2","true");
} else {
  $pt->parse("BACK_LINK","back_plans1","true");
}

if ( $user->class == 'admin' ) {
  if ( isset($wholesaler_id) ) {
    $plan_groups->wholesaler_id = $wholesaler_id;
    $plan_groups_arr = $plan_groups->wholesaler_plan_groups();
  } else {
    $plan_groups_arr = $plan_groups->get_plan_groups();
  }
} else if ( $user->class == 'reseller' || isset($wholesaler_id) ) {
  $plan_groups->wholesaler_id = $user->access_id;
  $plan_groups_arr = $plan_groups->wholesaler_plan_groups();
}

for ($a=0; $a < count($plan_groups_arr); $a++) { 

  $wholesaler = new wholesalers();
  $wholesaler->wholesaler_id = $plan_groups_arr[$a]["wholesaler_id"];
  $wholesaler->load();

  if ( $user->class == 'admin' ) {
  $pt->setVar("WHOLESALER"," - ".$wholesaler->company_name);
  }

  $plans = new plans();
  $plans->group_id = $plan_groups_arr[$a]["group_id"];
  $plans->wholesaler_id = $plan_groups_arr[$a]["wholesaler_id"];
  $plans_arr = $plans->get_wholesaler_group();

  $pt->clearVar("ROWS");
  
  if (count($plans_arr) > 0) {
    for ($b=0; $b < count($plans_arr); $b++) { 

      $plan = new plans();
      $plan->plan_id = $plans_arr[$b]["plan_id"];
      $plan->load();

      $pt->setVar("PLAN_ID",$plans_arr[$b]["plan_id"]);
      $pt->setVar("PLAN_DESCRIPTION",$plan->description);
      $pt->parse("ROWS","rows","true");
    }
  }

  $pt->setVar("GROUP_ID",$plan_groups_arr[$a]["group_id"]);
  $pt->setVar("GROUP_DESCRIPTION",$plan_groups_arr[$a]["description"]);
  $pt->setVar("ACTIVE",$plan_groups_arr[$a]["active"]);
  $pt->parse("PLAN_GROUP_TABLE","plan_group_table","true");
}

$pt->setVar("PAGE_TITLE", "Group Plans");
    
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