#!/usr/bin/php
<?php

// Read file from telstra

include "/var/www/simplicity/htdocs/setup.inc";

$filename = $argv[1];


$fh = fopen($filename,'r');
while ($line = fgets($fh)) {
	
	$rec = array();
	
	$rec['record type'] = substr($line, 0, 2);
	
		
	if ($rec['record type']== '01') {
		
		// Header record
		$rec['record type description'] = 'Header Record';
		$rec['file creation date'] = substr($line, 2, 8);
		$rec['file sequence number'] = substr($line, 10, 4);
		$rec['originating sp'] = substr($line, 14, 3);
		$rec['receiving sp'] = substr($line, 17, 3);
		$rec['file identifer'] = substr($line, 21, 1);
	} else if ($rec['record type']== '99') {

		// Footer Record
		$rec['record type description'] = 'Footer Record';
		$rec['record count'] = substr($line, 2, 7);

	} else if ($rec['record type']== '15') {

		// Gain Advice Record
		$rec['record type description'] = 'Gain Advice Record';
		$rec['sp record sequence'] = substr($line, 2, 11);
		$rec['service number'] = substr($line, 11, 17);
		$rec['wholesale redirection group code'] = substr($line, 28, 3);
		$rec['completion date'] = substr($line, 31, 8);
		$rec['class code'] = substr($line, 39, 2);
		$rec['bus / res indicator'] = substr($line, 41, 1);
		$rec['service name'] = substr($line, 42, 32);
		$rec['sub address type'] = substr($line, 74, 6);
		$rec['sub address number'] = substr($line, 80, 4);
		$rec['street number'] = substr($line, 84, 5);
		$rec['street number suffix'] = substr($line, 89, 3);
		$rec['street name'] = substr($line, 92, 24);
		$rec['street type'] = substr($line, 116, 8);
		$rec['street suffix'] = substr($line, 124, 6);
		$rec['locality'] = substr($line, 130, 25);
		$rec['state'] = substr($line, 155, 3);
		$rec['postcode'] = substr($line, 158, 4);
		$rec['old service number'] = substr($line, 162, 17);
		$rec['provisioning reference'] = substr($line, 179, 16);
		$rec['additional address information'] = substr($line, 195, 20);
		$rec['auxilary service numbers'] = substr($line, 225, 20);

	} else if ($rec['record type']== '20') {
		
		// Header record
		$rec['record type description'] = 'Rejection Advice Record';
		$rec['sp record sequemce'] = substr($line, 2, 9);
		$rec['service number'] = substr($line, 11, 17);
		$rec['wholesale redirection group code'] = substr($line, 28, 3);
		$rec['rejection date'] = substr($line, 31, 8);
		$rec['rejection code'] = substr($line, 39, 2);		
	} else {

		$rec['record type description'] = 'Unknown Record Type!';
	}

	echo "\n";
	
	while ($cel = each($rec)) {
		
		printf("%40s   %s\n", $cel['key'], $cel['value']);
	}
	
	echo "\n";
	echo "===========================================================\n";
	
		
		
}
fclose($fh);

