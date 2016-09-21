#!/usr/bin/php
<?php

include "/var/www/simplicity/htdocs/setup.inc";

require_once "telstra_infotrans_file.class";
require_once "telstra_infotrans_record.class";

$tif = new telstra_infotrans_file();

$prev_file =  $tif->get_last_file_seq();
$tif->file_seq = $prev_file+1;
$tif->creation = date('Y-m-d');

$date = date('Ymd');

if ($tif->create()) {
	
	// We have a new file
	
	$filename = "/home/telstra-out/OUTBOX/662TELW" . sprintf("%04d", $tif->file_seq) . $date;

	$output = array();
	$output[] = make_header($date, $tif->file_seq);	
	
	
	// Now to work on any records that need uploading
	$tir = new telstra_infotrans_record();
	$pending = $tir->get_pending();
	
	// Get the next record sequence
	$seq = $tir->get_last_seq();
	$seq++;

	// Loop through records	
	while ($record = each($pending)) {
		
		$tir->id = $record['value'];
		$tir->load();
		
		if ($tir->type == 10) {
			
			// Transfer record
			
			$tir->seq = $seq;
			$tir->file_seq = $tif->file_seq;
			$tir->save();
			
			$output[] = make_transfer($seq, $tir->param1, $tir->param2, $tir->param3);
			$seq++;
			
			
		}
		
	}
	
	// Write out to file
  $output[] = make_footer(sizeof($output)-1);
  
  // Save file
  $myfile = fopen($filename, "w") or die("Unable to open file!");
  while ($cel = each($output)) {
  	
  	//print $cel['value'] . "\n";
  	fwrite($myfile, $cel['value'] . "\n");
  }
  fclose($myfile);	
	
}



function make_header($creation_date, $seq, $origin = '662', $dest = 'TEL', $file_id = 'W') {
	
	$str = sprintf("%02d%8d%04d%3s%3s%s", '01', $creation_date, $seq, $origin, $dest, $file_id);
	
	return pad($str);

}

function make_footer($rec_count) {
	$str = sprintf("%02d%07d", '99', $rec_count);
	return pad($str);
}

function make_transfer($seq, $service_number, $wholesale_redirection_group_code, $ca_date) {
	
	$str = sprintf("%02d%09d%-17s%03d%8d", '10', $seq, $service_number, $wholesale_redirection_group_code, $ca_date);

	return pad($str);
	
}	 

function make_long_distance_transfer($seq, $service_number, $ca_date) {
	
	$str = sprintf("%02d%09d%010d%8d", '13', $seq, $service_number, $ca_date);

	return pad($str);
	
}	 

function make_reversal($seq, $original_seq, $service_number) {
	
	$str = sprintf("%02d%09d%09d%-17s", '50', $seq, $original_seq, $service_number);

	return pad($str);
	
}	 

function make_long_distance_package_reversal($seq, $original_seq, $service_number) {
	
	$str = sprintf("%02d%09d%09d%010d", '53', $seq, $original_seq, $service_number);

	return pad($str);
	
}	 

function make_status_request($seq, $original_sp, $service_number, $wno_file_sent_date, $wno_seq, $status_req_code) {
	
	$str = sprintf("%02d%09d%09d%-17s%8d%04d%02d", '80', $seq, $original_sp, $service_number, $wno_file_sent_date, $wno_seq, $status_req_code);

	return pad($str);
	
}	 

function pad($str, $length=600) {
	
	while (strlen($str) < $length) {
		$str .= ' ';
	}
	
	return $str;
}


