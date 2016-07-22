#!/usr/bin/php
<?php

include "/var/www/simplicity/htdocs/setup.inc";

$date = "20020111";
$file_seq = 5;

$filename = "662TELW" . sprintf("%04d", $file_seq) . $date;

$output = array();

$output[] = make_header($date, $file_seq);

$rec_count = 26;
$rec_count++;
$output[] = make_transfer($rec_count, "0386051140", '099', $date);
$rec_count++;
$output[] = make_long_distance_transfer($rec_count, "0386051111", $date);
$rec_count++;
$output[] = make_long_distance_transfer($rec_count, "0386051112", $date);
//$rec_count++;
//$output[] = make_reversal($rec_count, "00000000004", "0386051104");


$output[] = make_footer(sizeof($output)-1);

// Save file
$myfile = fopen($filename, "w") or die("Unable to open file!");
while ($cel = each($output)) {
	
	//print $cel['value'] . "\n";
	fwrite($myfile, $cel['value'] . "\n");
}
fclose($myfile);

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

function pad($str, $length=600) {
	
	while (strlen($str) < $length) {
		$str .= ' ';
	}
	
	return $str;
}