<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/radius/index.php - View Radius Information
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
include_once "radius.class";
include_once "services.class";
include_once "customers.class";
include_once "service_attributes.class";
include_once "plans.class";
include_once "plan_attributes.class";
include_once "accounting.class";

$user = new user();
$user->username = $_SESSION['username'];
$user->load();

$pt->setVar("PAGE_TITLE", "View Radius Configuration");


switch ($user->class) {
	case 'admin':
		$pt->setFile(array("outside" => "base/outside1.html"));		
		break;
	case 'reseller':
		echo "Access Deined";
		exit();
		break;
	case 'customer':
		echo "Access Deined";
		exit();
		break;
	
	default:
		echo "Access Deined";
		exit();
		break;
}

// Assign the templates to use
$pt->setFile(array("main" => "base/manage/services/radius/index.html", 
					"radcheck_row" => "base/manage/services/radius/radcheck_row.html", 
					"radgroupcheck_row" => "base/manage/services/radius/radgroupcheck_row.html", 
					"radgroupreply_row" => "base/manage/services/radius/radgroupreply_row.html", 
					"radusergroup_row" => "base/manage/services/radius/radusergroup_row.html", 
					"radpostauth_row" => "base/manage/services/radius/radpostauth_row.html"));

if ( !isset($_REQUEST["service_id"]) || $_REQUEST["service_id"] == "" ) {
	echo "Service ID invalid.";
	exit();
}

$service = new services();
$service->service_id = $_REQUEST["service_id"];
$service->load();
$pt->setVar("SERVICE_ID",$service->service_id);

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


// Get username
$service_attr = new service_attributes();
$service_attr->service_id = $service->service_id;
$service_attr->param = 'username';
$service_attr->get_attribute();

$username =  $service_attr->value;

$service_attr = new service_attributes();
$service_attr->service_id = $service->service_id;
$service_attr->param = 'realms';
$service_attr->get_attribute();

$username .= '@' . $service_attr->value;
	
// Rad Check table
$radius = new radius();

$query = "SELECT * FROM radius.radcheck WHERE username = " . $radius->db->quote($username);
$result = $radius->db->execute_query($query);
while($row = $radius->db->fetch_row_array($result)) {

	while ($cel = each($row)) {
		
		$pt->setVar(strtoupper($cel['key']), $cel['value']);
	}
	
	$pt->parse('RADCHECK_ROWS', 'radcheck_row',true);
}

// Rad reply table
$query = "SELECT * FROM radius.radreply WHERE username = " . $radius->db->quote($username);
$result = $radius->db->execute_query($query);
while($row = $radius->db->fetch_row_array($result)) {

	while ($cel = each($row)) {
		
		$pt->setVar(strtoupper($cel['key']), $cel['value']);
	}
	
	$pt->parse('RADREPLY_ROWS', 'radreply_row',true);
}

// Rad usergroup
$query = "SELECT * FROM radius.radusergroup WHERE username = " . $radius->db->quote($username);
$result = $radius->db->execute_query($query);
while($row = $radius->db->fetch_row_array($result)) {

	while ($cel = each($row)) {
		
		$pt->setVar(strtoupper($cel['key']), $cel['value']);
	}
	
	$pt->parse('RADUSERGROUP_ROWS', 'radusergroup_row',true);
}

// Rad group reply
$query = "SELECT radius.radgroupreply.* FROM radius.radusergroup, radius.radgroupreply WHERE radius.radgroupreply.groupname = radius.radusergroup.groupname AND username = " . $radius->db->quote($username);
$result = $radius->db->execute_query($query);
while($row = $radius->db->fetch_row_array($result)) {

	while ($cel = each($row)) {
		
		$pt->setVar(strtoupper($cel['key']), $cel['value']);
	}
	
	$pt->parse('RADGROUPREPLY_ROWS', 'radgroupreply_row',true);
}

// Rad usergroup
$query = "SELECT * FROM radius.radpostauth WHERE username = " . $radius->db->quote($username) . " ORDER BY id DESC limit 10";
$result = $radius->db->execute_query($query);
while($row = $radius->db->fetch_row_array($result)) {

	while ($cel = each($row)) {
		
		$pt->setVar(strtoupper($cel['key']), $cel['value']);
	}
	
	$pt->parse('RADPOSTAUTH_ROWS', 'radpostauth_row',true);
}

// Parse the main page
$pt->parse("MAIN", "main");
$pt->parse("WEBPAGE", "outside");	

// Print out the page
$pt->p("WEBPAGE");
