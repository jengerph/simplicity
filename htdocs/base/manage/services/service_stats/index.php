<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/service_stats/index.php - View Service Statistics
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

include_once "services.class";
include_once "customers.class";
include_once "service_attributes.class";
include_once "plans.class";
include_once "NWB-DSL-lineStats.php";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/service_stats/index.html"));
	
} else if ($user->class == 'reseller') {
	$pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/service_stats/index.html"));
	
} else if ($user->class == 'admin') {
	$pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/service_stats/index.html"));
	
}

$pt->setFile(array("service_stats_link" => "base/manage/services/back_link/back_link_service_stats.html"));

if ( !isset($_REQUEST["service_id"]) || empty($_REQUEST["service_id"]) ) {
	echo "Service ID invalid.";
	exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();

$customers = new customers();
$customers->customer_id = $service->customer_id;
$customers->load();

if ( $user->class == 'customer' ) {
	if ( $customers->customer_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
} else if ( $user->class == 'reseller' ) {
	if ( $customers->wholesaler_id != $user->access_id ) {
		$pt->setFile(array("main" => "base/accessdenied.html"));
	}
}

$plan = new plans();
$plan->plan_id = $service->retail_plan_id;
$plan->load();

$service_attr_keys = array("username",
							"realms",
							"password");

$username = "";
$password = "";

for ($sk=0; $sk < count($service_attr_keys); $sk++) { 
	$service_attr = new service_attributes();
	$service_attr->service_id = $service->service_id;
	$service_attr->param = $service_attr_keys[$sk];
	$service_attr->get_attribute();
	$pt->setVar( strtoupper($service_attr->param), $service_attr->value );
	if ( $service_attr_keys[$sk] == "username" ) {
		$username =  $service_attr->value;
	} else if ( $service_attr_keys[$sk] == "realms" && !empty($username) ) {
		$username .=  "@".$service_attr->value;
	} else if ( $service_attr_keys[$sk] == "password" ) {
		$password = $service_attr->value;
	}
}

$service_attribute = new service_attributes();
$service_attribute->service_id = $service->service_id;
$service_attribute->param = "aapt_service_id";
$service_attribute->get_attribute();

$pt->setVar("SERVICE_ID",$service->service_id);
$pt->setVar("SPEED",$plan->speed);

$linestats = dsl_linestats($service_attribute->value);

$pt->setVar("ECHANGE_CODE",$linestats->exchangeCode);
$pt->setVar("USERNAME",$username);
$pt->setVar("PASSWORD",$password);
$pt->setVar("TRANSMISSION_MODE",$linestats->lineSummaryStatus->transmissionMode);
$pt->setVar("LBR_DOWN",$linestats->channelStatus->currentBitRate->down->value . " " . $linestats->channelStatus->currentBitRate->down->quantifier);
$pt->setVar("LBR_UP",$linestats->channelStatus->currentBitRate->up->value . " " . $linestats->channelStatus->currentBitRate->up->quantifier);
$pt->setVar("OP_DOWN",$linestats->lineDetailsStatus->outputPower->down . " dB");
$pt->setVar("OP_UP",$linestats->lineDetailsStatus->outputPower->up . " dB");
$pt->setVar("ATTENUATION_DOWN",$linestats->lineDetailsStatus->lineAttenuation->down . " dB");
$pt->setVar("ATTENUATION_UP",$linestats->lineDetailsStatus->lineAttenuation->up . " dB");
$pt->setVar("SNRM_DOWN",$linestats->lineDetailsStatus->lineNoiseMargin->down . " dB");
$pt->setVar("SNRM_UP",$linestats->lineDetailsStatus->lineNoiseMargin->up . " dB");
$pt->setVar("SP_UP",$linestats->lineDetailsStatus->lineProfile);

$_SESSION["lineProfile"] = $linestats->lineDetailsStatus->lineProfile;

if ( preg_match("/telstra/i", strtolower($retail_plan->access_method)) == 0 ) {
	$pt->parse("SERVICE_STATS_LINK","service_stats_link","true");
}

$pt->setVar("PAGE_TITLE", "Manage Service Stats");

// Parse the main page
$user->username = $_SESSION['username'];
$user->load();

$pt->parse("MAIN", "main");

$pt->parse("WEBPAGE", "outside");
	
// Print out the page
$pt->p("WEBPAGE");

