<?php

include_once "../../../../setup.inc";

include "../../../doauth.inc";

include_once "audit.class";

if ( isset($_REQUEST["select_username"]) ) {
	download_results($_REQUEST["select_username"]);
}

function download_results($username) {

	$audit = new audit();
	$audit_all = $audit->for_download($username,"ASC");

	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=audit-" . $username . ".csv");


	outputCSV($audit_all);
	
}

function outputCSV($data) {
    $output = fopen("php://output", "w");
    foreach ($data as $row) {
        fputcsv($output, $row); // here you can change delimiter/enclosure
    }
    fclose($output);
}