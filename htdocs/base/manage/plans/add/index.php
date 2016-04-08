<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/plans/add/index.php - Add Plan
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
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/plans/add/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/plans/add/index.html"));
  
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
                    "customize_access_method_nbn" => "base/manage/wholesalers/management/add/customize/customize_access_method_nbn.html",
                    "customize_adsl_speed_aapt" => "base/manage/wholesalers/management/add/customize/customize_adsl_speed_aapt.html",
                    "customize_adsl_speed_telstra" => "base/manage/wholesalers/management/add/customize/customize_adsl_speed_telstra.html",
                    "extras_section" => "base/manage/wholesalers/management/add/extras/extras_section.html",
                    "extras_adsl_nbn_enable" => "base/manage/wholesalers/management/add/extras/extras_adsl_nbn_enable.html",
                    "extras_adsl_nbn_type" => "base/manage/wholesalers/management/add/extras/extras_adsl_nbn_type.html",
                    "extras_details" => "base/manage/wholesalers/management/add/extras/extras_details.html",
                    "wholesaler_section" => "base/manage/wholesalers/management/add/wholesaler/wholesaler_section.html",
                    "service_sub_type" => "base/manage/wholesalers/management/add/service_type/service_sub_type.html",
                    "outbound_voice_type" => "base/manage/wholesalers/management/add/service_type/outbound_voice_type.html",
                    "inbound_voice_type" => "base/manage/wholesalers/management/add/service_type/inbound_voice_type.html",
                    "parent_plan_section" => "base/manage/wholesalers/management/add/parent_plan/parent_plan_section.html",
                    "plan_group" => "base/manage/wholesalers/management/add/parent_plan/plan_group.html",
                    "irc_row_admin" => "base/manage/wholesalers/management/add/customize/customize_irc_row_admin.html",
                    "irc_row_wholesaler" => "base/manage/wholesalers/management/add/customize/customize_irc_row_wholesaler.html",
                    "irc_row_wholesaler2" => "base/manage/wholesalers/management/add/customize/customize_irc_row_wholesaler2.html"));

//Get a list of wholesalers
$wholesalers = new wholesalers();
$wholesalers_list = $wholesalers->get_wholesalers();
$list_ready_w = $wholesalers->wholesalers_list2('wholesaler_id',$wholesalers_list);


$wholesaler = new wholesalers();

if ( isset($_REQUEST["wholesaler_id"]) ) {
$wholesaler->wholesaler_id = $_REQUEST["wholesaler_id"];
$wholesaler->load();
}

if ( $user->class == "admin" ) {
  $pt->setVar('WHOLESALER_LIST', $list_ready_w);
  $pt->parse("WHOLESALER_SECTION","wholesaler_section","true");
} else if ($user->class == "reseller") {
  $wholesaler = new wholesalers();
  $wholesaler->wholesaler_id = $user->access_id;
  $wholesaler->load();
  $pt->setVar('WHOLESALER_LIST', $wholesaler->company_name);
}

$plan = new plans();
if ( !isset($_REQUEST["active"]) ) {
  $plan->active = "yes";
}

//prepare service type options
$services2 = new service_types();
$services_list = $services2->get_services();
$list_ready = $services2->service_list('service_type',$services_list);

//display service type list
$pt->setVar('SERVICE_TYPE_LIST', $list_ready);

//display templates
if ( $wholesaler->wholesaler_id == 1 ) {
  $pt->parse("SERVICE_SUB_TYPE","service_sub_type","true");
  $pt->setVar('MANAGEMENT_FORM','2');
} else {
  $pt->parse('PARENT_PLAN_SECTION','parent_plan_section','true');
}

$call = new calls();

if ( isset($_REQUEST["submit"]) ) {
  $plan->type_id = $_REQUEST['service_type'];
  $plan->wholesaler_id = $wholesaler->wholesaler_id;
  if ( isset($_REQUEST['parent_plan_id']) ) {
    $plan->parent_plan_id = $_REQUEST['parent_plan_id'];
  }
  $plan->active = $_REQUEST['active'];
  if ( isset($_REQUEST["sub_type"]) ) {
    $plan->sub_type = $_REQUEST["sub_type"];
  } else {
    if ( $plan->type_id == "5" ) {
      $plan->sub_type = "13";
    } else if ( $plan->type_id == "6" ) {
      $plan->sub_type = "PSTN";
    }
  }
  $plan->group_id = $_REQUEST["group_id"];
  if ( isset($_REQUEST["ir_id"]) ) {
    $call->ir_id = $_REQUEST["ir_id"];
  }
}

if (isset($_REQUEST['submit2'])) {

  // Add new plan
  $error_msg = '';
  $plan->description = $_REQUEST['description'];
  $plan->type_id = $_REQUEST['service_type'];
  $plan->wholesaler_id = $wholesaler->wholesaler_id;

  if ( isset($_REQUEST["ir_id"]) ) {
    $call->ir_id = $_REQUEST["ir_id"];
  }

  if ( isset($_REQUEST['parent_plan_id']) ) {
    $plan->parent_plan_id = $_REQUEST['parent_plan_id'];
  }

  $plan->active = $_REQUEST['active'];

  if ( $wholesaler->wholesaler_id == 1 ) {
      if ( isset($_REQUEST["speed"]) ) {
        $plan->speed = $_REQUEST["speed"];
      }
      if ( isset($_REQUEST["sub_type"]) ) {
        $plan->sub_type = $_REQUEST["sub_type"];
      }
      if ( isset($_REQUEST["access_method"]) ) {
        $plan->access_method = $_REQUEST["access_method"];
      }
  } else {
    $parent_plan = new plans();
    $parent_plan->plan_id = $plan->parent_plan_id;
    $parent_plan->load();
    $plan->access_method = $parent_plan->access_method;
    $plan->speed = $parent_plan->speed;
    $plan->sub_type = $parent_plan->sub_type;
    $plan->price_zone = $parent_plan->price_zone;
  }

  if ( $plan->access_method == "Telstra L2IG" && isset($_REQUEST["price_zone"]) ) {
    $plan->price_zone = $_REQUEST["price_zone"];
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
    
    $plan->create();
    $plan->load();

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

    if ( $wholesaler->wholesaler_id != 1 ) {
      $c_data = new plan_attributes();
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

    $keys = array_keys($attributes);

    for ($x = 0; $x < count($attributes); $x++ ) {
      //create attributes

        if ( $attributes[$keys[$x]] == "" ) {
          $attributes[$keys[$x]] = "0";
        }
        
        create( $plan->plan_id, $keys[$x], $attributes[$keys[$x]]);
    }

    //create extra plan

    if ( $plan->type_id != 5) {

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
      $pt->parse("ACCESS_METHOD_OPTION","customize_access_method_adsl","true");
    } else if ( $plan->type_id == 2 ) {
      $pt->parse("ACCESS_METHOD_OPTION","customize_access_method_nbn","true");
    }

    break;
  case '5':
    if ($wholesaler->wholesaler_id == 1) {
      $pt->parse("CUSTOMIZE","customize_inbound_voice_admins","true");
    } else {
      $pt->parse("CUSTOMIZE","customize_inbound_voice_wholesalers","true");
    }
      $pt->parse("SERVICE_SUB_TYPE","inbound_voice_type","true");

    if ( isset($_REQUEST["sub_type"]) ) {
      if ( $_REQUEST["sub_type"] == "13" ) {
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
    } else {
      $pt->parse("CUSTOMIZE","customize_outbound_voice_wholesalers","true");
    }
      $pt->parse("SERVICE_SUB_TYPE","outbound_voice_type","true");
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

//ready which plan groups
$get_pg = new plan_groups();
$get_pg->wholesaler_id = $wholesaler->wholesaler_id;
$get_pg->type_id = $plan->type_id;
$get_pg_arr = $get_pg->get_group();

//Get a list of parent plans
$parent_plan = new plans();

if ( $wholesaler->wholesaler_id != 1 && isset($plan->type_id)) {
  $parent_plan_list = $parent_plan->order_get_all($plan->type_id,$get_pg_arr);
  
  if ( count($parent_plan_list) != 0 ) {
    $list_ready_p = $parent_plan->plans_list2('parent_plan_id',$parent_plan_list,$plan->type_id,$plan->sub_type);
  } else {
    $list_ready_p = '<select name="parent_plan_id" id="parent_plan_id" onchange="form.submit.click();"><option value="0">Select Parent Plan</option></select>';
  }
    $pt->setVar('PARENT_PLAN_LIST', $list_ready_p);
} 

$pt->setVar('P_' . strtoupper($plan->parent_plan_id) . '_SELECT', ' selected');

//prepare master plan information
$parent_plan_info = new plans();
$parent_plan_info->plan_id = $plan->parent_plan_id;
$parent_plan_info->load();

$parent_plan_info_attr = new plan_attributes();
$parent_plan_info_attr->plan_id = $plan->parent_plan_id;
$parent_plan_info_attr_list = $parent_plan_info_attr->get_plan_attributes();

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
    $pt->setVar("PARENT_PLAN_CONTRACT_LENGTH",ucwords($parent_plan_info_attr_list[$i]['value']) . " Months");
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

if ( isset($_REQUEST["monthly_cost"]) ) {
  $plan_attributes->monthly_cost = $_REQUEST["monthly_cost"];
} else {
  $plan_attributes->monthly_cost = "";
}

if ( isset($_REQUEST["contract_length"]) ) {
  $plan_attributes->contract_length = $_REQUEST["contract_length"];
} else {
  $plan_attributes->contract_length = "";
}

if ( isset($_REQUEST["early_termination_cost"]) ) {
  $plan_attributes->early_termination_cost = $_REQUEST["early_termination_cost"];
} else {
  $plan_attributes->early_termination_cost = "";
}

if ( isset($_REQUEST["extra_data_cost"]) ) {
  $plan_attributes->extra_data_cost = $_REQUEST["extra_data_cost"];
} else {
  $plan_attributes->extra_data_cost = "";
}

// if ( isset($_REQUEST["usage_types"]) ) {
//   $plan_attributes->usage_types = $_REQUEST["usage_types"];
// } else {
//   $plan_attributes->usage_types = "";
// }

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

if ( isset($_REQUEST["count_uploads"]) ) {
  $pt->setVar('COUNT_' . strtoupper($_REQUEST["count_uploads"]) . '_SELECT', ' checked');
} else {
  $pt->setVar('COUNT_NO_SELECT', ' checked');  
}
//Set Up Fee:

if ( isset($plan->sub_type) || isset($parent_plan_info->sub_type ) ) {
  if ( $wholesaler->wholesaler_id == 1 ) {
    $key = $plan->sub_type;
  } else {
    $key = $parent_plan_info->sub_type;
  }
  switch ($key) {
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
}

if ( isset($_REQUEST["parent_plan_id"]) ) {

  $plan_extra_type = new plan_extras();
  $plan_extra_type->plan_id = $_REQUEST["parent_plan_id"];
  $plan_extra_type->load();

  $plan_ex = explode(",", $plan_extra_type->type);
  $array2 = array("staticip","ipblock4","ipblock8","ipblock16");
  $array3 = array_diff($array2,$plan_ex);
  $array3 = array_values($array3);

  for ($i=0; $i < count($array3); $i++) {

    if ( $array3[$i] == "staticip" ) {
      $pt->setVar("SI_ENABLE", ' disabled');
      $pt->setVar("SI_HIDDEN", ' hidden');
    }

    if ( $array3[$i] == "ipblock4" ) {
      $pt->setVar("IP4_ENABLE", ' disabled');
      $pt->setVar("IP4_HIDDEN", ' hidden');
    }

    if ( $array3[$i] == "ipblock8" ) {
      $pt->setVar("IP8_ENABLE", ' disabled');
      $pt->setVar("IP8_HIDDEN", ' hidden');
    }

    if ( $array3[$i] == "ipblock16" ) {
      $pt->setVar("IP16_ENABLE", ' disabled');
      $pt->setVar("IP16_HIDDEN", ' hidden');
    }

  }

  for ($r=0; $r < count($plan_ex); $r++) { 
    if ( $plan_ex[$r] == "staticip" ) {
      $pt->setVar("EXTRA_KEY", strtoupper($plan_ex[$r]));
      $pt->parse("EXTRA_PLANS_STATIC", 'extras_details', 'true');
    }

    if ( $plan_ex[$r] == "ipblock4" ) {
      $pt->setVar("EXTRA_KEY", strtoupper($plan_ex[$r]));
      $pt->parse("EXTRA_PLANS_4", 'extras_details', 'true');
    }

    if ( $plan_ex[$r] == "ipblock8" ) {
      $pt->setVar("EXTRA_KEY", strtoupper($plan_ex[$r]));
      $pt->parse("EXTRA_PLANS_8", 'extras_details', 'true');
    }

    if ( $plan_ex[$r] == "ipblock16" ) {
      $pt->setVar("EXTRA_KEY", strtoupper($plan_ex[$r]));
      $pt->parse("EXTRA_PLANS_16", 'extras_details', 'true');
    }
  }

} else {
  $pt->setVar("SI_ENABLE", ' disabled');
  $pt->setVar("IP4_ENABLE", ' disabled');
  $pt->setVar("IP8_ENABLE", ' disabled');
  $pt->setVar("IP16_ENABLE", ' disabled');
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
$pt->setVar('TYPE_ID', $plan->type_id);

$pt->setVar('WS_' . strtoupper($wholesaler->wholesaler_id) . '_SELECT', ' selected');

if ( isset($_REQUEST["access_method"]) ) {
  $temp = $_REQUEST["access_method"];
  $temp = str_replace("+", "", $temp);
  $temp = preg_replace("/\([^)]+\)/","",$temp);
  $temp = trim($temp);
  $temp = str_replace(" ", "_", $temp);
  $temp = strtoupper($temp);
  
  $pt->setVar($temp, " selected");

}

if ( isset($_REQUEST["price_zone"]) ) {
  $temp = $_REQUEST["price_zone"];
  $temp = str_replace("+", "", $temp);
  $temp = preg_replace("/\([^)]+\)/","",$temp);
  $temp = trim($temp);
  $temp = str_replace(" ", "_", $temp);
  $temp = str_replace("/", "_", $temp);
  $temp = strtoupper($temp);
  print_r($temp);
  $pt->setVar("PRICE_".$temp, " selected");

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
  $call->master_international_list = $parent_irc->ir_id;
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

$pt->setVar("CARD_".$call->ir_id."_SELECT", " selected");

if ( isset($_SERVER["HTTP_REFERER"]) ) {
  if ( strpos($_SERVER["HTTP_REFERER"], "base/manage/plans/add/") !== false ) {
    
  } else {
    $pt->setVar("FOR_CURRENT_JQUERY_STEP","$.removeCookie('jQu3ry_5teps_St@te_example-vertical');");
  }
} else {
  $pt->setVar("FOR_CURRENT_JQUERY_STEP","$.removeCookie('jQu3ry_5teps_St@te_example-vertical');");
}

$pt->setVar("PAGE_TITLE", "Add Plan");
    
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