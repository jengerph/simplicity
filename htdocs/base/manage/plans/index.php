<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/plans/index.php - View plans
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../setup.inc";

include "../../doauth.inc";

include_once "plans.class";
include_once "plan_groups.class";
include_once "plan_attributes.class";
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
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/plans/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/plans/index.html"));
	
}

$pt->setFile(array("row" => "base/manage/plans/row.html"));

$pt->setVar("PAGE_TITLE", "Manage Plans");

if (!isset($_REQUEST['inactive'])) {
	$_REQUEST['inactive'] = 'yes';
}

$pt->setVar('INACTIVE_DISPLAY', $_REQUEST['inactive']);

if ($_REQUEST['inactive'] == 'yes') {
	$pt->setVar('INACTIVE_NEW', 'no');
} else {
	$pt->setVar('INACTIVE_NEW', 'yes');
}

$plans = new plans();

if ( isset($_REQUEST["submit"]) ) {

	$filter_type = $_REQUEST["filter_type"];
	$filter_subtype = $_REQUEST["filter_subtype"];

	$filter_plans = new plans();

	if ( $user->class == "admin" ) {
		$plans_list = $filter_plans->filter_plans2($filter_type,strtolower($filter_subtype));
	} else {
		$filter_plans->wholesaler_id = $user->access_id;
		$plans_list = $filter_plans->filter_plans3($filter_type,strtolower($filter_subtype));
	}

	$pt->setVar("FILTER_SELECT_".$filter_type," selected");
	$pt->setVar("FILTER_SUBTYPE_VALUE",$filter_subtype);

} else {
	if ( $user->class == "admin" ) {
		$plans_list = $plans->get_all();
	} else {
		$plans->wholesaler_id = $user->access_id;
		$plans_list = $plans->get_wholesaler_plans();
	}
}

for ($x = 0; $x < count($plans_list) ; $x++) {
	
	$service_type = new service_types();
	$service_type->type_id = $plans_list[$x]["type_id"];
	$service_type->load();

	$wholesaler = new wholesalers();
	$wholesaler->wholesaler_id = $plans_list[$x]["wholesaler_id"];
	$wholesaler->load();

	$attribute = new plan_attributes();
	$attribute->plan_id = $plans_list[$x]["plan_id"];
	$attribute->param = "monthly_cost";
	$attribute->get_latest();

	$attribute2 = new plan_attributes();
	$attribute2->plan_id = $plans_list[$x]["plan_id"];
	$attribute2->param = "contract_length";
	$attribute2->get_latest();

	if($_REQUEST['inactive']=='yes'){
		if ( $plans_list[$x]["active"] == 'yes' ) {

			$plan_group = new plan_groups();
			$plan_group->group_id = $plans_list[$x]["group_id"];
			$plan_group->load();

			$pt->setVar('PLAN_ID', $plans_list[$x]["plan_id"]);
			$pt->setVar('DESCRIPTION', $plans_list[$x]["description"]);
			$pt->setVar('TYPE', $service_type->description);
			$pt->setVar('ACCESS_METHOD', $plans_list[$x]["access_method"]);
			$pt->setVar('SPEED', $plans_list[$x]["speed"]);
			$pt->setVar('WHOLESALER', $wholesaler->company_name);
			$pt->setVar('MONTHLY_COST', $attribute->value);
			$pt->setVar('CONTRACT_LENGTH', $attribute2->value);
			$pt->setVar('ACTIVE', $plans_list[$x]["active"]);
			$pt->setVar('GROUP', $plan_group->description);
			$pt->parse('ROWS','row','true');
		}
	}else{

			$plan_group = new plan_groups();
			$plan_group->group_id = $plans_list[$x]["group_id"];
			$plan_group->load();

			$pt->setVar('PLAN_ID', $plans_list[$x]["plan_id"]);
			$pt->setVar('DESCRIPTION', $plans_list[$x]["description"]);
			$pt->setVar('TYPE', $service_type->description);
			$pt->setVar('ACCESS_METHOD', $plans_list[$x]["access_method"]);
			$pt->setVar('SPEED', $plans_list[$x]["speed"]);
			$pt->setVar('WHOLESALER', $wholesaler->company_name);
			$pt->setVar('MONTHLY_COST', $attribute->value);
			$pt->setVar('CONTRACT_LENGTH', $attribute2->value);
			$pt->setVar('ACTIVE', $plans_list[$x]["active"]);
			$pt->setVar('GROUP', $plan_group->description);
			$pt->parse('ROWS','row','true');
	}

}

//Get a list of parents
$parent_plan = new plans();
$parent_plan_list = $parent_plan->get_all();
$list_ready_p = $parent_plan->plans_list('parent_plan_id',$parent_plan_list);

$pt->setVar('PARENT_PLAN_LIST', $list_ready_p);
	
// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");
	
// Print out the page
$pt->p("WEBPAGE");

