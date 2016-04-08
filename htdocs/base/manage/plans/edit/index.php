<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/plans/edit/index.php - Edit Plans
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
include_once "plans.class";
include_once "plan_attributes.class";
include_once "plan_extras.class";
include_once "plan_groups.class";
include_once "service_types.class";
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
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/plans/edit/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/plans/edit/index.html"));
	
}

// Assign the templates to use
$pt->setFile(array( "customize_adsl_nbn_admins" => "base/manage/wholesalers/management/add/customize/customize_adsl_nbn_admins.html",
                    "customize_adsl_nbn_wholesalers" => "base/manage/wholesalers/management/add/customize/customize_adsl_nbn_wholesalers.html",
                    "customize_inbound_voice_admins" => "base/manage/wholesalers/management/add/customize/customize_inbound_voice_admins.html",
                    "customize_inbound_voice_wholesalers" => "base/manage/wholesalers/management/add/customize/customize_inbound_voice_wholesalers.html",
                    "customize_inbound_voice_13" => "base/manage/wholesalers/management/add/customize/customize_inbound_voice_13.html",
                    "customize_inbound_voice_13_wholesalers" => "base/manage/wholesalers/management/add/customize/customize_inbound_voice_13_wholesalers.html",
                    "customize_inbound_voice_1300" => "base/manage/wholesalers/management/add/customize/customize_inbound_voice_1300.html",
                    "customize_inbound_voice_1300_wholesalers" => "base/manage/wholesalers/management/add/customize/customize_inbound_voice_1300_wholesalers.html",
                    "customize_outbound_voice_admins" => "base/manage/wholesalers/management/add/customize/customize_outbound_voice_admins.html",
                    "customize_outbound_voice_wholesalers" => "base/manage/wholesalers/management/add/customize/customize_outbound_voice_wholesalers.html",
                    "customize_setup_fee_isdn_pri_aapt" => "base/manage/wholesalers/management/add/customize/customize_setup_fee_isdn_pri_aapt.html",
                    "customize_setup_fee_isdn_pri_telstra" => "base/manage/wholesalers/management/add/customize/customize_setup_fee_isdn_pri_telstra.html",
                    "customize_setup_fee_isdn2" => "base/manage/wholesalers/management/add/customize/customize_setup_fee_isdn2.html",
                    "customize_setup_fee_pstn" => "base/manage/wholesalers/management/add/customize/customize_setup_fee_pstn.html",
                    "customize_setup_fee_sip_trunk" => "base/manage/wholesalers/management/add/customize/customize_setup_fee_sip_trunk.html",
                    "customize_access_method_adsl" => "base/manage/wholesalers/management/add/customize/customize_access_method_adsl.html",
                    "customize_access_method_adsl_display" => "base/manage/wholesalers/management/add/customize/customize_access_method_adsl_display.html",
                    "customize_access_method_nbn" => "base/manage/wholesalers/management/add/customize/customize_access_method_nbn.html",
                    "customize_access_method_nbn_display" => "base/manage/wholesalers/management/add/customize/customize_access_method_nbn_display.html",
                    "customize_adsl_speed_aapt" => "base/manage/wholesalers/management/add/customize/customize_adsl_speed_aapt.html",
                    "customize_adsl_speed_telstra" => "base/manage/wholesalers/management/add/customize/customize_adsl_speed_telstra.html",
                    "extras_section" => "base/manage/wholesalers/management/add/extras/extras_section.html",
                    "extras_adsl_nbn_enable" => "base/manage/wholesalers/management/add/extras/extras_adsl_nbn_enable.html",
                    "extras_adsl_nbn_type" => "base/manage/wholesalers/management/add/extras/extras_adsl_nbn_type.html",
                    "extras_details" => "base/manage/wholesalers/management/add/extras/extras_details.html",
                    "wholesaler_section" => "base/manage/wholesalers/management/add/wholesaler/wholesaler_section.html",
                    "service_sub_type" => "base/manage/wholesalers/management/add/service_type/service_sub_type.html",
                    "outbound_voice_type" => "base/manage/wholesalers/management/add/service_type/outbound_voice_type.html",
                    "outbound_voice_type_display" => "base/manage/wholesalers/management/add/service_type/outbound_voice_type_display.html",
                    "inbound_voice_type" => "base/manage/wholesalers/management/add/service_type/inbound_voice_type.html",
                    "inbound_voice_type_display" => "base/manage/wholesalers/management/add/service_type/inbound_voice_type_display.html",
                    "parent_plan_section" => "base/manage/wholesalers/management/add/parent_plan/parent_plan_section2.html",
                    "plan_group" => "base/manage/wholesalers/management/add/parent_plan/plan_group.html",
                    "irc_row_admin" => "base/manage/wholesalers/management/add/customize/customize_irc_row_admin.html",
                    "irc_row_wholesaler" => "base/manage/wholesalers/management/add/customize/customize_irc_row_wholesaler.html",
                    "irc_row_wholesaler2" => "base/manage/wholesalers/management/add/customize/customize_irc_row_wholesaler2.html",
                    "parent_plan_access_method" => "base/manage/wholesalers/management/add/parent_plan/parent_plan_access_method.html"));

if ( !isset($_REQUEST["plan_id"]) || empty($_REQUEST["plan_id"]) ) {
  echo "Plan ID invalid.";
  exit();
}

$plan = new plans();
$plan->plan_id = $_REQUEST["plan_id"];
$plan->load();

$wholesaler = new wholesalers();
$wholesaler->wholesaler_id = $plan->wholesaler_id;
$wholesaler->load();

if ( $user->class == 'reseller' ) {
  if ( $wholesaler->wholesaler_id != $user->access_id ) {
    $pt->setFile(array("main" => "base/accessdenied.html"));
  }
}

$plan_extras = new plan_extras();
$plan_extras->plan_id = $plan->plan_id;
$plan_extra_arr = $plan_extras->get_extra_types();

$attribute = new plan_attributes();
$attribute->plan_id = $plan->plan_id;

//display templates
if ( $wholesaler->wholesaler_id == 1 ) {
  $pt->parse("SERVICE_SUB_TYPE","service_sub_type","true");
  $pt->setVar('MANAGEMENT_FORM','3');
} else {
  $pt->parse('PARENT_PLAN_SECTION','parent_plan_section','true');
}

$call = new calls();

if (isset($_REQUEST['submit'])) {
  
  // Add new plan
  $error_msg = '';
  $plan->description = $_REQUEST['description'];
  $plan->type_id = $_REQUEST['service_type'];
  $plan->wholesaler_id = $wholesaler->wholesaler_id;

  if ( isset($_REQUEST['parent_plan_id']) ) {
    $plan->parent_plan_id = $_REQUEST['parent_plan_id'];
  }

  $plan->active = $_REQUEST['active'];

  if ( $wholesaler->wholesaler_id == 1 ) {
    if ( $plan->type_id != 5 && $plan->type_id != 6) {
      $plan->access_method = $_REQUEST["access_method"];
      $plan->speed = $_REQUEST["speed"];
    }
      
      if ( isset($_REQUEST["sub_type"]) ) {
        $plan->sub_type = $_REQUEST["sub_type"];
      }
  } else {
    $parent_plan = new plans();
    $parent_plan->plan_id = $plan->parent_plan_id;
    $parent_plan->load();
    $plan->access_method = $parent_plan->access_method;
    $plan->speed = $parent_plan->speed;
    $plan->sub_type = $parent_plan->sub_type;
  }
  $plan->group_id = $_REQUEST["group_id"];
  if ( isset($_REQUEST["ir_id"]) ) {
    $call->ir_id = $_REQUEST["ir_id"];
  }
}

if (isset($_REQUEST['submit2'])) {
  
  // Edit plans
  $error_msg = '';
  $plan->plan_id = $_REQUEST['plan_id'];
  $plan->description = $_REQUEST['description'];
  $plan->type_id = $plan->type_id;
  $plan->wholesaler_id = $wholesaler->wholesaler_id;
  
  if ( isset($_REQUEST["ir_id"]) ) {
    $call->ir_id = $_REQUEST["ir_id"];
  }

  if ( isset($_REQUEST['parent_plan_id']) ) {
    $plan->parent_plan_id = $_REQUEST['parent_plan_id'];
  }

  $plan->active = $_REQUEST['active'];
  if ( isset($_REQUEST["sub_type"]) ) {
    $plan->sub_type = $_REQUEST["sub_type"];
  }

  if ( isset($_REQUEST["access_method"]) ) {
    $plan->access_method = $_REQUEST["access_method"];
  }

  if ( isset($_REQUEST["speed"]) ) {
    $plan->speed = $_REQUEST["speed"];
  }

  if ( $wholesaler->wholesaler_id != 1 ) {
    $parent_plan = new plans();
    $parent_plan->plan_id = $plan->parent_plan_id;
    $parent_plan->load();
    $plan->access_method = $parent_plan->access_method;
    $plan->speed = $parent_plan->speed;
    $plan->sub_type = $parent_plan->sub_type;
  }

  if ( isset($_REQUEST["count_uploads"]) ) {
    if ( $_REQUEST["count_uploads"] == 'no' ) {
      $_REQUEST["count_uploads"] = 0;
    } else if ( $_REQUEST["count_uploads"] == 'yes' ) {
      $_REQUEST["count_uploads"] = 1;
    }
  }

  $plan->group_id = $_REQUEST["group_id"];

  $vc = $plan->validate();

  if ($vc != 0) {
  
    $pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

  } else {

    $plan->save();

    $delete_plan = new plan_attributes();
    $delete_plan->plan_id = $plan->plan_id;
    $delete_plan->delete();

        $c_length = new plan_attributes();
    if ( $wholesaler->wholesaler_id != 1 ) {
        $c_length->plan_id = $parent_plan->plan_id;
        $c_length->param = 'contract_length';
        $c_length->get_latest();
      } else {
        $c_length->value = $_REQUEST["contract_length"];
      }

        $c_data = new plan_attributes();
      if ( $wholesaler->wholesaler_id != 1 ) {
        $c_data->plan_id = $parent_plan->plan_id;
        $c_data->param = 'monthly_data_allowance';
        $c_data->get_latest();
      } else {
        if ( isset($_REQUEST["monthly_data_allowance"]) ) {
          $c_data->value = $_REQUEST["monthly_data_allowance"];
        }
      }

    switch ($plan->type_id) {
      case '5':
        $attributes = array(
          "contract_length" => $c_length->value,
          "setup_fee" => $_REQUEST["setup_fee"],
          "monthly_fee" => $_REQUEST["monthly_fee"],
          "standard_capf" => $_REQUEST["standard_capf"],
          "priority_capf" => $_REQUEST["priority_capf"],
          "local_to_fixed_line" => $_REQUEST["local_to_fixed_line"],
          "national_to_fixed_line" => $_REQUEST["national_to_fixed_line"],
          "mobile_to_fixed_line" => $_REQUEST["mobile_to_fixed_line"],
          "local_to_mobile" => $_REQUEST["local_to_mobile"],
          "national_to_mobile" => $_REQUEST["national_to_mobile"],
          "mobile_to_mobile" => $_REQUEST["mobile_to_mobile"]
        );
        if ( $plan->sub_type == "13" ) {
          $attributes["government_tax"] = $_REQUEST["government_tax"];
        }
        if ( isset($_REQUEST["ir_id"]) ) {
          $attributes["international_rate_card"] = $call->ir_id;
        }
        break;
      case '6':
        $attributes = array(
          "contract_length" => $c_length->value,
          "setup_fee" => $_REQUEST["setup_fee"],
          "monthly_fee" => $_REQUEST["monthly_fee"],
          "local_calls" => $_REQUEST["local_calls"],
          "national_calls" => $_REQUEST["national_calls"],
          "mobile_calls" => $_REQUEST["mobile_calls"],
          "usage_type_13_1300" => $_REQUEST["usage_type_13_1300"]
        );
        if ( isset($_REQUEST["ir_id"]) ) {
          $attributes["international_rate_card"] = $call->ir_id;
        }
        break;
      
      default:
        if ( $user->class == "admin" ) {
          $attributes = array(
            "monthly_cost" => $_REQUEST["monthly_cost"],
            "contract_length" => $c_length->value,
            "monthly_data_allowance" => $c_data->value,
            "early_termination_cost" => $_REQUEST["early_termination_cost"],
            "extra_data_cost" => $_REQUEST["extra_data_cost"],
            "count_uploads" => $_REQUEST["count_uploads"]
          );
        } else {
          $attributes = array(
            "monthly_cost" => $_REQUEST["monthly_cost"],
            "contract_length" => $c_length->value,
            "monthly_data_allowance" => $c_data->value,
            "early_termination_cost" => $_REQUEST["early_termination_cost"],
            "extra_data_cost" => $_REQUEST["extra_data_cost"]
          );
        }
        break;
    }

    //delete old plan_attributes
    $delete_plan_attr = new plan_attributes();
    $delete_plan_attr->plan_id = $plan->plan_id;
    $delete_plan_attr->delete();

    $keys = array_keys($attributes);

    for ($x = 0; $x < count($attributes); $x++ ) {
      //create new attribute
      create( $plan->plan_id, $keys[$x], $attributes[$keys[$x]]); 

    }

    //save extra plan
    for ($e=0; $e < count($plan_extra_arr); $e++) { 
      $extra_delete = new plan_extras();
      $extra_delete->extra_id = $plan_extra_arr[$e]['extra_id'];
      $extra_delete->delete();
    }

    $_REQUEST['extra_desc'] = array_values(array_filter($_REQUEST['extra_desc']));
    $_REQUEST['extra_month_cost'] = array_values(array_filter($_REQUEST['extra_month_cost']));
    $_REQUEST['extra_setup_cost'] = array_values(array_filter($_REQUEST['extra_setup_cost']));

    for ($r=0; $r < count($_REQUEST["extra_type"]); $r++) { 
      $plan_extras = new plan_extras();
      $plan_extras->plan_id = $plan->plan_id;
      $plan_extras->type = $_REQUEST['extra_type'][$r];
      $plan_extras->description = $_REQUEST['extra_desc'][$r];
      $plan_extras->month_cost = $_REQUEST['extra_month_cost'][$r];
      $plan_extras->setup_cost = $_REQUEST['extra_setup_cost'][$r];
      $plan_extras->create();
    }

    if ( isset($_REQUEST["extra_type_static_ip"]) ) {
        $plan_extras = new plan_extras();
        $plan_extras->plan_id = $plan->plan_id;
        $plan_extras->type = $_REQUEST['extra_type_static_ip'];
        $plan_extras->description = $_REQUEST['extra_desc_static_ip'];
        $plan_extras->month_cost = $_REQUEST['extra_month_cost_static_ip'];
        $plan_extras->setup_cost = $_REQUEST['extra_setup_cost_static_ip'];
        $plan_extras->create();
    }
    if ( isset($_REQUEST["extra_type_ip_4"]) ) {
          $plan_extras = new plan_extras();
          $plan_extras->plan_id = $plan->plan_id;
          $plan_extras->type = $_REQUEST['extra_type_ip_4'];
          $plan_extras->description = $_REQUEST['extra_desc_ip_4'];
          $plan_extras->month_cost = $_REQUEST['extra_month_cost_ip_4'];
          $plan_extras->setup_cost = $_REQUEST['extra_setup_cost_ip_4'];
          $plan_extras->create();
    }
    if ( isset($_REQUEST["extra_type_ip_8"]) ) {
          $plan_extras = new plan_extras();
          $plan_extras->plan_id = $plan->plan_id;
          $plan_extras->type = $_REQUEST['extra_type_ip_8'];
          $plan_extras->description = $_REQUEST['extra_desc_ip_8'];
          $plan_extras->month_cost = $_REQUEST['extra_month_cost_ip_8'];
          $plan_extras->setup_cost = $_REQUEST['extra_setup_cost_ip_8'];
          $plan_extras->create();
    }
    if ( isset($_REQUEST["extra_type_ip_16"]) ) {
          $plan_extras = new plan_extras();
          $plan_extras->plan_id = $plan->plan_id;
          $plan_extras->type = $_REQUEST['extra_type_ip_16'];
          $plan_extras->description = $_REQUEST['extra_desc_ip_16'];
          $plan_extras->month_cost = $_REQUEST['extra_month_cost_ip_16'];
          $plan_extras->setup_cost = $_REQUEST['extra_setup_cost_ip_16'];
          $plan_extras->create();
    }

    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/wholesalers/management/?wholesaler_id=".$wholesaler->wholesaler_id;

    header("Location: $url");
    exit();   
  
  }
}

//Access Method
if ( $plan->access_method ) {
  if ( strpos($plan->access_method, "ADSL") !== false ) {
  
    $pt->parse("ADSL_SPEED","customize_adsl_speed_aapt","true");

  } else if ( strpos($plan->access_method, "Telstra") !== false ) {

    $pt->parse("ADSL_SPEED","customize_adsl_speed_telstra","true");

  }
}

//prepare service type options
$services2 = new service_types();
$services2->type_id = $plan->type_id;
$services2->load();
// $services_list = $services2->get_services();
// $list_ready = $services2->service_list('service_type',$services_list);

//display service type list
$pt->setVar('SERVICE_TYPE_LIST', $services2->description);

//choose which template to use
switch ($plan->type_id) {
  case '1':
  case '2':
  case '3':
  case '4':
    if ($wholesaler->wholesaler_id == 1) {
      $pt->parse("CUSTOMIZE","customize_adsl_nbn_admins","true");
      $pt->parse("EXTRAS_SECTION","extras_section","true");
      $pt->parse("EXTRAS_INPUT","extras_adsl_nbn_enable","true");
    } else {
      $pt->parse("CUSTOMIZE","customize_adsl_nbn_wholesalers","true");
      $pt->parse("EXTRAS_SECTION","extras_section","true");
      $pt->parse("EXTRAS_INPUT","extras_adsl_nbn_type","true");
    }

    if ( $plan->type_id == 1 ) {
      $pt->parse("ACCESS_METHOD_OPTION","customize_access_method_adsl_display","true");
      $pt->parse("PARENT_PLAN_ACCESS_METHOD","parent_plan_access_method","true");
    } else if ( $plan->type_id == 2 ) {
      $pt->parse("ACCESS_METHOD_OPTION","customize_access_method_nbn_display","true");
      $pt->parse("PARENT_PLAN_ACCESS_METHOD","parent_plan_access_method","true");
    }

    break;
  case '5':
    if ($wholesaler->wholesaler_id == 1) {
      $pt->parse("CUSTOMIZE","customize_inbound_voice_admins","true");
    } else {
      $pt->parse("CUSTOMIZE","customize_inbound_voice_wholesalers","true");
    }
      // $pt->parse("SERVICE_SUB_TYPE","inbound_voice_type","true");
      $pt->parse("SERVICE_SUB_TYPE","inbound_voice_type_display","true");

    if ( isset($plan->sub_type) ) {
      if ( $plan->sub_type == "13" ) {
        if ( $wholesaler->wholesaler_id == 1 ) {
          $pt->parse("INBOUND_USAGE_TYPES","customize_inbound_voice_13","true");
        } else {
          $pt->parse("INBOUND_USAGE_TYPES","customize_inbound_voice_13_wholesalers","true");
        }
      } else {
        if ( $wholesaler->wholesaler_id == 1 ) {
          $pt->parse("INBOUND_USAGE_TYPES","customize_inbound_voice_1300","true");
        } else {
          $pt->parse("INBOUND_USAGE_TYPES","customize_inbound_voice_1300_wholesalers","true");
        }
      }
      $pt->setVar("PLAN_SUB_TYPE",$plan->sub_type);
    } else {
        if ( $wholesaler->wholesaler_id == 1 ) {
          $pt->parse("INBOUND_USAGE_TYPES","customize_inbound_voice_13","true");
        } else {
          $pt->parse("INBOUND_USAGE_TYPES","customize_inbound_voice_13_wholesalers","true");
        }
    }
    
    break;
  case '6':
    if ($wholesaler->wholesaler_id == 1) {
      $pt->parse("CUSTOMIZE","customize_outbound_voice_admins","true");
      $pt->clearVar("SERVICE_SUB_TYPE");
      // $pt->parse("SERVICE_SUB_TYPE","outbound_voice_type","true");
    } else {
      $pt->parse("CUSTOMIZE","customize_outbound_voice_wholesalers","true");
    }
    $pt->setVar("PLAN_SUB_TYPE",$plan->sub_type);
    $pt->parse("SERVICE_SUB_TYPE","outbound_voice_type_display","true");
    break;
  default:
    $pt->parse("CUSTOMIZE","customize_adsl_nbn_wholesalers","true");
    break;
}

//Access Method
if ( isset($_REQUEST["access_method"]) ) {
  if ( strpos($_REQUEST["access_method"], "ADSL") !== false ) {
  
    $pt->parse("ADSL_SPEED","customize_adsl_speed_aapt","true");

  } else if ( strpos($_REQUEST["access_method"], "Telstra") !== false ) {

    $pt->parse("ADSL_SPEED","customize_adsl_speed_telstra","true");

  }
}

$parent_plan = new plans();
$parent_plan->plan_id = $plan->parent_plan_id;
$parent_plan->load();

$pt->setVar('PARENT_PLAN_LIST', $parent_plan->description);

//prepare master plan information
$parent_plan_info = new plans();
$parent_plan_info->plan_id = $plan->parent_plan_id;
$parent_plan_info->load();

$parent_plan_info_attr = new plan_attributes();
$parent_plan_info_attr->plan_id = $plan->parent_plan_id;
$parent_plan_info_attr_list = $parent_plan_info_attr->get_plan_attributes();
// print_r($parent_plan_info_attr_list);
// exit();

$parent_irc = new calls();

for ($i=0; $i < count($parent_plan_info_attr_list); $i++) { 
  if ( $parent_plan_info_attr_list[$i]['param'] == 'monthly_cost' ) {
    $pt->setVar("PARENT_PLAN_COST",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'monthly_data_allowance' ) {
    $pt->setVar("PARENT_PLAN_ALLOWANCE",$parent_plan_info_attr_list[$i]['value']);
    $pt->setVar('MONTHLY_DATA_ALLOWANCE', $parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'early_termination_cost' ) {
    $pt->setVar("PARENT_PLAN_EARLY_TERMINATION",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'extra_data_cost' ) {
    $pt->setVar("PARENT_PLAN_EXTRA_DATA",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'setup_fee' ) {
    $pt->setVar("PARENT_PLAN_SETUP_FEE",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'monthly_fee' ) {
    $pt->setVar("PARENT_PLAN_MONTHLY_FEE",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'standard_capf' ) {
    $pt->setVar("PARENT_PLAN_STANDARD_CAPF",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'priority_capf' ) {
    $pt->setVar("PARENT_PLAN_PRIORITY_CAPF",ucwords($parent_plan_info_attr_list[$i]['value']));
  } /*else if ( $parent_plan_info_attr_list[$i]['param'] == 'usage_types' ) {
    $pt->setVar("PARENT_PLAN_USAGE_TYPES",ucwords($parent_plan_info_attr_list[$i]['value']));
  }*/ else if ( $parent_plan_info_attr_list[$i]['param'] == 'contract_length' ) {
    $pt->setVar("PARENT_PLAN_CONTRACT_LENGTH",ucwords($parent_plan_info_attr_list[$i]['value'])." Months");
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'local_to_fixed_line' ) {
    $pt->setVar("PARENT_PLAN_LOCAL_TO_FIXED_LINE",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'national_to_fixed_line' ) {
    $pt->setVar("PARENT_PLAN_NATIONAL_TO_FIXED_LINE",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'mobile_to_fixed_line' ) {
    $pt->setVar("PARENT_PLAN_MOBILE_TO_FIXED_LINE",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'local_to_mobile' ) {
    $pt->setVar("PARENT_PLAN_LOCAL_TO_MOBILE",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'national_to_mobile' ) {
    $pt->setVar("PARENT_PLAN_NATIONAL_TO_MOBILE",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'mobile_to_mobile' ) {
    $pt->setVar("PARENT_PLAN_MOBILE_TO_MOBILE",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'government_tax' ) {
    $pt->setVar("PARENT_PLAN_GOVERNMENT_TAX",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'local_calls' ) {
    $pt->setVar("PARENT_PLAN_LOCAL_CALLS",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'national_calls' ) {
    $pt->setVar("PARENT_PLAN_NATIONAL_CALLS",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'mobile_calls' ) {
    $pt->setVar("PARENT_PLAN_MOBILE_CALLS",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'usage_type_13_1300' ) {
    $pt->setVar("PARENT_PLAN_USAGE_TYPE_13_1300",$parent_plan_info_attr_list[$i]['value']);
  } else if ( $parent_plan_info_attr_list[$i]['param'] == 'international_rate_card' ) {
    $parent_irc->ir_id = $parent_plan_info_attr_list[$i]['value'];
    $parent_irc->load();
    $pt->setVar("BASE_IRC",$parent_irc->description);
  }
}

$plan_attributes = new stdClass();

$attributes = array("monthly_cost",
                    "contract_length",
                    "monthly_data_allowance",
                    "early_termination_cost",
                    "extra_data_cost",
                    "setup_fee",
                    "monthly_fee",
                    "standard_capf",
                    "priority_capf",
                    // "usage_types",
                    "count_uploads",
                    "local_to_fixed_line",
                    "national_to_fixed_line",
                    "mobile_to_fixed_line",
                    "local_to_mobile",
                    "national_to_mobile",
                    "mobile_to_mobile",
                    "government_tax",
                    "local_calls",
                    "national_calls",
                    "mobile_calls",
                    "usage_type_13_1300",
                    "international_rate_card");

for ( $x = 0; $x < count($attributes); $x++ ) {
  $attribute->param = $attributes[$x];
  $attribute->get_latest();
  $plan_attributes->{$attributes[$x]} = $attribute->value;

  if ( isset($_REQUEST[$attributes[$x]]) ) {
    $plan_attributes->{$attributes[$x]} = $_REQUEST[$attributes[$x]];
  }
}

//prepare contract_length
$cl_select = "";

if ( isset($_REQUEST["contract_length"]) ) {
  switch ($_REQUEST["contract_length"]) {
    case '0':
      $cl_select = '0';
      break;
    case '12':
      $cl_select = '12';
      break;
    case '24':
      $cl_select = '24';
      break;
    default:
      break;
  }
} else {
  $cl_select = $plan_attributes->contract_length;
}

if ( isset($_REQUEST["extra_type"]) ) {
  for ($i=0; $i < count($_REQUEST["extra_type"]); $i++) { 
    $pt->setVar(strtoupper($_REQUEST["extra_type"][$i]), ' checked');
  }
}

$temp = preg_replace("/\([^)]+\)/","",$plan->access_method);
$temp = trim($temp);
$temp = str_replace(" ", "_", strtoupper($temp));
$temp = str_replace("+", "", $temp);
$temp = str_replace(".", "_", $temp);
$pt->setVar($temp," selected");


$temp = preg_replace("/\([^)]+\)/","",$plan->speed);
$temp = trim($temp);
$temp = str_replace(" ", "_", strtoupper($temp));
$temp = str_replace("+", "", $temp);
$temp = str_replace(".", "_", $temp);
$temp = str_replace("/", "_", $temp);
$pt->setVar($temp," selected");

$temp = $plan->price_zone;
$temp = str_replace("+", "", $temp);
$temp = preg_replace("/\([^)]+\)/","",$temp);
$temp = trim($temp);
$temp = str_replace(" ", "_", $temp);
$temp = str_replace("/", "_", $temp);
$temp = strtoupper($temp);
$pt->setVar("PRICE_".$temp, " selected");

if ( isset($_REQUEST["count_uploads"]) ) {
  $pt->setVar('COUNT_' . strtoupper($_REQUEST["count_uploads"]) . '_SELECT', ' checked');
} else {
  if ( $plan_attributes->count_uploads == 1) {
    $plan_attributes->count_uploads = "yes";
  } else if ( $plan_attributes->count_uploads == 0 ) {
    $plan_attributes->count_uploads = "no";
  }
  $pt->setVar('COUNT_'.strtoupper($plan_attributes->count_uploads).'_SELECT', ' checked');  
}

//Set Up Fee:
if ( isset($plan->sub_type) ) {
  switch ($plan->sub_type) {
    case 'PSTN':
      $pt->parse("SETUP_FEE_ROW","customize_setup_fee_pstn","true");
      break;
    case 'ISDN 2':
      $pt->parse("SETUP_FEE_ROW","customize_setup_fee_isdn2","true");
      break;
    case 'ISDN PRI - Telstra':
      $pt->parse("SETUP_FEE_ROW","customize_setup_fee_isdn_pri_telstra","true");
      break;
    case 'ISDN PRI - AAPT':
      $pt->parse("SETUP_FEE_ROW","customize_setup_fee_isdn_pri_aapt","true");
      break;
    case 'SIP Trunk':
      $pt->parse("SETUP_FEE_ROW","customize_setup_fee_sip_trunk","true");
      break;
    default:
      $pt->parse("SETUP_FEE_ROW","customize_setup_fee_pstn","true");
      break;
  }
} else {
  $pt->parse("SETUP_FEE_ROW","customize_setup_fee_sip_trunk","true");
}

$plan_extra_type = new plan_extras();
if ( $plan->parent_plan_id != 0 ) {
  $plan_extra_type->plan_id = $plan->plan_id;
} else {
  $plan_extra_type->plan_id = $plan->parent_plan_id;
}
  $extra_type_arr = $plan_extra_type->get_extra_types();

//master_plan
$parent_plan_extra_type = new plan_extras();
$parent_plan_extra_type->plan_id = $plan->parent_plan_id;
$parent_extra_type_arr = $parent_plan_extra_type->get_extra_types();

  //set initial
  $pt->setVar("SI_ENABLE", ' disabled');
  $pt->setVar("SI_HIDDEN", ' hidden');
  $pt->setVar("IP4_ENABLE", ' disabled');
  $pt->setVar("IP4_HIDDEN", ' hidden');
  $pt->setVar("IP8_ENABLE", ' disabled');
  $pt->setVar("IP8_HIDDEN", ' hidden');
  $pt->setVar("IP16_ENABLE", ' disabled');
  $pt->setVar("IP16_HIDDEN", ' hidden');

  for ($h=0; $h < count($parent_extra_type_arr); $h++) { 
    if ( $parent_extra_type_arr[$h]["type"] == "staticip" ) {
      $pt->clearVar("SI_ENABLE", ' disabled');
      $pt->clearVar("SI_HIDDEN", ' hidden');
      $pt->setVar("EXTRA_KEY", strtoupper($parent_extra_type_arr[$h]["type"]));
      $pt->parse("EXTRA_PLANS_STATIC", 'extras_details', 'true');
    }

    if ( $parent_extra_type_arr[$h]["type"] == "ipblock4" ) {
      $pt->clearVar("IP4_ENABLE", ' disabled');
      $pt->clearVar("IP4_HIDDEN", ' hidden');
      $pt->setVar("EXTRA_KEY", strtoupper($parent_extra_type_arr[$h]["type"]));
      $pt->parse("EXTRA_PLANS_4", 'extras_details', 'true');
    }

    if ( $parent_extra_type_arr[$h]["type"] == "ipblock8" ) {
      $pt->clearVar("IP8_ENABLE", ' disabled');
      $pt->clearVar("IP8_HIDDEN", ' hidden');
      $pt->setVar("EXTRA_KEY", strtoupper($parent_extra_type_arr[$h]["type"]));
      $pt->parse("EXTRA_PLANS_8", 'extras_details', 'true');
    }

    if ( $parent_extra_type_arr[$h]["type"] == "ipblock16" ) {
      $pt->clearVar("IP16_ENABLE", ' disabled');
      $pt->clearVar("IP16_HIDDEN", ' hidden');
      $pt->setVar("EXTRA_KEY", strtoupper($parent_extra_type_arr[$h]["type"]));
      $pt->parse("EXTRA_PLANS_16", 'extras_details', 'true');
    }
  }

  for ($i=0; $i < count($extra_type_arr); $i++) {

    if ( $extra_type_arr[$i]["type"] == "staticip" ) {
      // $pt->clearVar("SI_ENABLE", ' disabled');
      // $pt->clearVar("SI_HIDDEN", ' hidden');
      // $pt->setVar("EXTRA_KEY", strtoupper($extra_type_arr[$i]["type"]));
      $pt->setVar(strtoupper($extra_type_arr[$i]['type']), ' checked');
      $pt->setVar('EXTRA_DESC_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['description']);
      $pt->setVar('EXTRA_MONTH_COST_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['month_cost']);
      $pt->setVar('EXTRA_SETUP_COST_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['setup_cost']);
      // $pt->parse("EXTRA_PLANS_STATIC", 'extras_details', 'true');
    }

    if ( $extra_type_arr[$i]["type"] == "ipblock4" ) {
      // $pt->clearVar("IP4_ENABLE", ' disabled');
      // $pt->clearVar("IP4_HIDDEN", ' hidden');
      // $pt->setVar("EXTRA_KEY", strtoupper($extra_type_arr[$i]["type"]));
      $pt->setVar(strtoupper($extra_type_arr[$i]['type']), ' checked');
      $pt->setVar('EXTRA_DESC_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['description']);
      $pt->setVar('EXTRA_MONTH_COST_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['month_cost']);
      $pt->setVar('EXTRA_SETUP_COST_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['setup_cost']);
      // $pt->parse("EXTRA_PLANS_4", 'extras_details', 'true');
    }

    if ( $extra_type_arr[$i]["type"] == "ipblock8" ) {
      // $pt->clearVar("IP8_ENABLE", ' disabled');
      // $pt->clearVar("IP8_HIDDEN", ' hidden');
      // $pt->setVar("EXTRA_KEY", strtoupper($extra_type_arr[$i]["type"]));
      $pt->setVar(strtoupper($extra_type_arr[$i]['type']), ' checked');
      $pt->setVar('EXTRA_DESC_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['description']);
      $pt->setVar('EXTRA_MONTH_COST_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['month_cost']);
      $pt->setVar('EXTRA_SETUP_COST_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['setup_cost']);
      // $pt->parse("EXTRA_PLANS_8", 'extras_details', 'true');
    }

    if ( $extra_type_arr[$i]["type"] == "ipblock16" ) {
      // $pt->clearVar("IP16_ENABLE", ' disabled');
      // $pt->clearVar("IP16_HIDDEN", ' hidden');
      // $pt->setVar("EXTRA_KEY", strtoupper($extra_type_arr[$i]["type"]));
      $pt->setVar(strtoupper($extra_type_arr[$i]['type']), ' checked');
      $pt->setVar('EXTRA_DESC_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['description']);
      $pt->setVar('EXTRA_MONTH_COST_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['month_cost']);
      $pt->setVar('EXTRA_SETUP_COST_' . strtoupper($extra_type_arr[$i]["type"]), $extra_type_arr[$i]['setup_cost']);
      // $pt->parse("EXTRA_PLANS_16", 'extras_details', 'true');
    }

  }


$pt->setVar("PARENT_PLAN_DESC",$parent_plan_info->description);
$pt->setVar("PARENT_PLAN_ACTIVE",ucfirst($parent_plan_info->active));
$pt->setVar("PARENT_PLAN_TYPE",ucfirst($parent_plan_info->access_method));
$pt->setVar("PARENT_PLAN_SPEED",ucfirst($parent_plan_info->speed));

$pt->setVar("WHOLESALER_ID",$wholesaler->wholesaler_id);
$pt->setVar('ST_' . strtoupper($plan->type_id) . '_SELECT', ' selected');
$pt->setVar('WHOLESALER_NAME', $wholesaler->company_name);
$pt->setVar('DESCRIPTION', $plan->description);
$pt->setVar('SUB_TYPE', $plan->sub_type);

$temp_sub_type = $plan->sub_type;
$temp_sub_type = strtoupper($temp_sub_type);
$temp_sub_type = str_replace(" - ", "_", $temp_sub_type);
$temp_sub_type = str_replace(" ", "_", $temp_sub_type);

$pt->setVar('SELECT_SUB_TYPE_' . $temp_sub_type, ' selected');
$pt->setVar('PARENT_PLAN', $plan->parent_plan_id);
$pt->setVar('MONTHLY_COST', $plan_attributes->monthly_cost);
$pt->setVar('EARLY_TERMINATION_COST', $plan_attributes->early_termination_cost);
$pt->setVar('EXTRA_DATA_COST', $plan_attributes->extra_data_cost);
$pt->setVar('ACTIVE_' . strtoupper($plan->active) . '_SELECT', ' checked');
$pt->setVar('CL_SELECT_' . $cl_select, ' selected');

$pt->setVar('PLAN_ID', $plan->plan_id);
$pt->setVar('MONTHLY_DATA_ALLOWANCE', $plan_attributes->monthly_data_allowance);

$temp_setup_fee = $plan_attributes->setup_fee;
$temp_setup_fee = strtoupper($temp_setup_fee);
$temp_setup_fee = str_replace(" - ", "_", $temp_setup_fee);
$temp_setup_fee = str_replace(" ", "_", $temp_setup_fee);
$temp_setup_fee = str_replace("$", "", $temp_setup_fee);
$temp_setup_fee = str_replace(".", "", $temp_setup_fee);
$temp_setup_fee = str_replace(":", "", $temp_setup_fee);

$pt->setVar('SF_'.$temp_setup_fee," selected");
$pt->setVar("SETUP_FEE",$temp_setup_fee);

// $temp_usage_types = $plan_attributes->usage_types;
// $temp_usage_types = strtoupper($temp_usage_types);
// $temp_usage_types = str_replace(" ", "_", $temp_usage_types);

// $pt->setVar('USAGE_TYPES_'.$temp_usage_types," selected");

$pt->setVar('MONTHLY_FEE', $plan_attributes->monthly_fee);
$pt->setVar('STANDARD_CAPF', $plan_attributes->standard_capf);
$pt->setVar('PRIORITY_CAPF', $plan_attributes->priority_capf);
$pt->setVar('LOCAL_TO_FIXED_LINE', $plan_attributes->local_to_fixed_line);
$pt->setVar('NATIONAL_TO_FIXED_LINE', $plan_attributes->national_to_fixed_line);
$pt->setVar('MOBILE_TO_FIXED_LINE', $plan_attributes->mobile_to_fixed_line);
$pt->setVar('LOCAL_TO_MOBILE', $plan_attributes->local_to_mobile);
$pt->setVar('NATIONAL_TO_MOBILE', $plan_attributes->national_to_mobile);
$pt->setVar('MOBILE_TO_MOBILE', $plan_attributes->mobile_to_mobile);
$pt->setVar('GOVERNMENT_TAX', $plan_attributes->government_tax);
$pt->setVar('LOCAL_CALLS', $plan_attributes->local_calls);
$pt->setVar('NATIONAL_CALLS', $plan_attributes->national_calls);
$pt->setVar('MOBILE_CALLS', $plan_attributes->mobile_calls);
$pt->setVar('USAGE_TYPE_13_1300', $plan_attributes->usage_type_13_1300);
$pt->setVar('TYPE_ID', $plan->type_id);
$pt->setVar('ACCESS_METHOD_DESC', $plan->access_method);
$pt->setVar('ADSL_SPEED_DESC', $plan->speed);

for ($r=0; $r < count($plan_extra_arr); $r++) { 
      $pt->setVar(strtoupper($plan_extra_arr[$r]['type']), ' checked');
      $pt->setVar('EXTRA_DESC_' . strtoupper($plan_extra_arr[$r]['type']), $plan_extra_arr[$r]['description']);
      $pt->setVar('EXTRA_MONTH_COST_' . strtoupper($plan_extra_arr[$r]['type']), $plan_extra_arr[$r]['month_cost']);
      $pt->setVar('EXTRA_SETUP_COST_' . strtoupper($plan_extra_arr[$r]['type']), $plan_extra_arr[$r]['setup_cost']);
}

if ( isset($plan->access_method) ) {
  $temp = $plan->access_method;
  $temp = str_replace("+", "", $temp);
  $temp = preg_replace("/\([^)]+\)/","",$temp);
  $temp = trim($temp);
  $temp = str_replace(" ", "_", $temp);
  $temp = strtoupper($temp);
  
  $pt->setVar($temp, " selected");

}

if ( isset($plan->speed) ) {
  $temp = $plan->speed;
  $temp = str_replace("+", "", $temp);
  $temp = preg_replace("/\([^)]+\)/","",$temp);
  $temp = trim($temp);
  $temp = str_replace(" ", "_", $temp);
  $temp = str_replace("/", "_", $temp);
  $temp = strtoupper($temp);
  $pt->setVar($temp, " selected");

}

//plan groups
$plan_groups = new plan_groups();
$plan_groups->wholesaler_id = $wholesaler->wholesaler_id;
$plan_groups->type_id = $plan->type_id;
$plan_groups_arr = $plan_groups->wholesaler_plan_groups();

$plan_groups_list = $plan_groups->plan_groups_list("group_id",$plan_groups_arr);
$pt->setVar("PLAN_GROUP_LIST",$plan_groups_list);
$pt->setVar("PG_".$plan->group_id."_SELECT"," selected");
$pt->parse("PLAN_GROUP","plan_group","true");

//international rate cards section
if ( ($plan->type_id == '5' || $plan->type_id == '6') && $plan->sub_type != "PSTN" ) {
  $call->type_id = $plan->type_id;
  $call->sub_type = strtolower($plan->sub_type);
  $call->wholesaler_id = $plan->wholesaler_id;
  if ( $plan->wholesaler_id == '1' ) {
    $call->master_international_list = '0';
  } else {
    $call->master_international_list = $parent_irc->ir_id;
  }
  $call_arr = $call->get_cards();
  $call_list = $call->calls_list("ir_id",$call_arr);
  $pt->setVar("IRC_LIST",$call_list);
  if ($wholesaler->wholesaler_id == 1) {
    $pt->parse("IRC_ROW","irc_row_admin","true");
  } else {
    if ( $plan->type_id == '5' ) {
      $pt->parse("IRC_ROW","irc_row_wholesaler2","true");
    } else {
      $pt->parse("IRC_ROW","irc_row_wholesaler","true");
    }
  }
}
if ( !empty($call->ir_id) ) {
  $ir_id = $call->ir_id;
} else {
  $ir_id = $plan_attributes->international_rate_card;
}
$pt->setVar("CARD_".$ir_id."_SELECT", " selected");

if ( isset($_SERVER["HTTP_REFERER"]) ) {
  if ( strpos($_SERVER["HTTP_REFERER"], "base/manage/plans/add/") !== false ) {
    
  } else {
    $pt->setVar("FOR_CURRENT_JQUERY_STEP","$.removeCookie('jQu3ry_5teps_St@te_example-vertical');");
  }
} else {
  $pt->setVar("FOR_CURRENT_JQUERY_STEP","$.removeCookie('jQu3ry_5teps_St@te_example-vertical');");
}

$pt->setVar("PAGE_TITLE", "Edit Plan");
		
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
      $exist = $attributes->exist();
      if ( !$exist ) {
      	$attributes->create();
      }

}