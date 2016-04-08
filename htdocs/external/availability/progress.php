<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/export/availability/progress.php - Check on progress of a qualification
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
		
	} else {
		$return['progress'] = $wsq->status;
	}
		
}


echo json_encode($return);

