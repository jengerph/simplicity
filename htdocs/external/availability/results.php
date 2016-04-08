<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/export/availability/results.php - Return results
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../setup.inc";
include_once "website_servicequal.class";

$wsq = new website_servicequal();
$wsq->qual_id = $_REQUEST['qual_id'];

$return = array();

if (!$wsq->exist()) {
	$return['progress'] = 'failed';
	$return['msg'] = 'Error: invalid qualification id';
	
} else {
	$wsq->load();
	
	if ($wsq->pass != $_REQUEST['pass']) {
		$return['progress'] = 'failed';
		$return['msg'] = 'Error: pass mismatch';
		
	} else if ($wsq->status != 'complete') {
		$return['progress'] = 'failed';
		$return['msg'] = 'Error: qual is not yet complete!';
	} else {
				
		$return['adsl_onnet'] = $wsq->result_adsl_onnet;
		$return['adsl_offnet'] = $wsq->result_adsl_offnet;
		$return['nbn_wireless'] = $wsq->result_nbn_wireless;
		$return['nbn_fiber'] = $wsq->result_nbn_fiber;
	}
		
}


echo json_encode($return);

