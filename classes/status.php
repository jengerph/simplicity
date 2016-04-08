<?php

function get_radius_status($username){
	$radius = new radius();
	$radius->username = $username;
	$radius->load();

	if ( $radius->check_online() != 0 ) {
		return 'status_online';
	} else {
		return 'status_offline';
	}
}

function get_shape_status($service_id){
	$service_attr = new service_attributes();
	$service_attr->service_id = $service_id;
	$service_attr->param = "shape_status";
	$service_attr->get_attribute();

	if ( $service_attr->value == "0" ) {
		return 'status_normal';
	} else if ( $service_attr->value == "1" ) {
		return 'status_shaped';
	} else {
		return 'status_normal';
	}
}

function get_username ($service_id) {
	$username = new service_attributes();
	$username->service_id = $service_id;
	$username->param = "username";
	$username->get_attribute();

	$realm = new service_attributes();
	$realm->service_id = $service_id;
	$realm->param = "realms";
	$realm->get_attribute();

	if ( !empty($username->value) && !empty($realm->value) ) {
		return $username->value . "@" . $realm->value;
	}
}