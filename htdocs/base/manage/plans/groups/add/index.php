<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/plans/groups/add/index.php - Add Group Plan
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

include_once "plans.class";
include_once "wholesalers.class";
include_once "plan_groups.class";
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
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/plans/groups/add/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/plans/groups/add/index.html"));
  
}

$pt->setFile(array("back_manage_plans1" => "base/manage/plans/groups/add/back_manage_plans1.html",
                    "back_manage_plans2" => "base/manage/plans/groups/add/back_manage_plans2.html"));

$plan_groups = new plan_groups();
$wholesaler = new wholesalers();

if (isset($_REQUEST["wholesaler_id"]) ) {
  $wholesaler->wholesaler_id = $_REQUEST["wholesaler_id"];
  $pt->setVar("WHOLESALER_ID",$wholesaler->wholesaler_id);
  $pt->parse("BACK_LINK","back_manage_plans2","true");
} else {
  $pt->parse("BACK_LINK","back_manage_plans1","true");
}

if ( $user->class == "admin" ) {

  // if ( isset($_SERVER["HTTP_REFERER"]) ) {
  //   if ( strpos($_SERVER["HTTP_REFERER"], "base/manage/plans/groups/add/") !== false ) {
  //     $wholesalers = new wholesalers();
  //     $wholesalers_array = $wholesalers->get_wholesalers();
  //     $wholesalers_list = $wholesalers->wholesalers_list2("wholesaler_id",$wholesalers_array);
  //     $plan_groups->wholesaler_id = $wholesaler->wholesaler_id;
  //     $pt->setVar("WHOLESALER_LIST",$wholesalers_list);
  //     $pt->setVar("WS_" . $wholesaler->wholesaler_id . "_SELECT"," selected");
  //   } else if ( strpos($_SERVER["HTTP_REFERER"], "wholesaler_id=") !== false ) {
  //     $pt->setFile(array("main" => "base/manage/plans/groups/add/index2.html"));
  //     $wholesaler->wholesaler_id = $wholesaler->wholesaler_id;
  //     $wholesaler->load();
  //     $pt->setVar("WHOLESALER_LIST",$wholesaler->company_name);
  //   }
  // }

  //display wholesaler dropdown
  if ( isset($_REQUEST["wholesaler_id"]) ) {
    $pt->setFile(array("main" => "base/manage/plans/groups/add/index2.html"));
    $wholesaler->wholesaler_id = $wholesaler->wholesaler_id;
    $wholesaler->load();
    $pt->setVar("WHOLESALER_LIST",$wholesaler->company_name);
  } else {
    $wholesalers = new wholesalers();
    $wholesalers_array = $wholesalers->get_wholesalers();
    $wholesalers_list = $wholesalers->wholesalers_list("wholesaler_id",$wholesalers_array);
    $plan_groups->wholesaler_id = $wholesaler->wholesaler_id;
    $pt->setVar("WHOLESALER_LIST",$wholesalers_list);
    $pt->setVar("WS_" . $wholesaler->wholesaler_id . "_SELECT"," selected");
  }

} else if ( $user->class == "reseller" ) {
  $wholesaler->wholesaler_id = $user->access_id;
  $wholesaler->load();
  $pt->setVar("WHOLESALER_LIST",$wholesaler->company_name);
}

if ( isset($_REQUEST["real_submit"]) ) {
  $plan_groups->wholesaler_id = $wholesaler->wholesaler_id;
  $plan_groups->type_id = $_REQUEST["type_id"];
  $plan_groups->description = $_REQUEST["description"];
  $plan_groups->active = $_REQUEST["active"];

  $validate = $plan_groups->validate();

  if ( $validate != 0 ) {
    $pt->setVar("ERROR_MSG","ERROR: " . $config->error_message[$validate]);
  } else {
    $plan_groups->create();
    //go to
    $url = "";
            
        if (isset($_SERVER["HTTPS"])) {
            
          $url = "https://";
              
        } else {
            
          $url = "http://";
        }
    
        if ( isset($_REQUEST["wholesaler_id"]) ) {
          $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/plans/groups/?wholesaler_id=".$_REQUEST["wholesaler_id"];
        } else {
          $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/plans/groups/";
        }
    
        header("Location: $url");
        exit();
  }
}

$pt->setVar("ST_" . $plan_groups->type_id . "_SELECT", " selected");
$pt->setVar("DESCRIPTION",$plan_groups->description);
$pt->setVar("ACTIVE_" . strtoupper($plan_groups->active) . "_SELECT"," checked");

$pt->setVar("PAGE_TITLE", "Add Group Plans");
    
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