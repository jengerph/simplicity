#!/usr/bin/php
<?php

// Read file from telstra

include "/var/www/simplicity/htdocs/setup.inc";

$filename = $argv[1];


$fh = fopen($filename,'r');
$linecount = 0;
while ($line = fgets($fh)) {

	if (strlen($line) > 3) {
  	$rec = array();
  	
  	if (substr($line, 0, 3) == 'FDR') {
  	
  		// Footer
  		$rec['record type description'] = 'File Designator Record';
  		$rec['Interface Record Type'] = substr($line, 0, 3);
  		$rec['Interface file type code'] = substr($line, 3, 3);
  		$rec['File source code'] = substr($line, 6, 5);
  		$rec['File Registration Sequence Number'] = substr($line, 11, 4);
  		$rec['File event date'] = substr($line, 15, 6);
  		$rec['Filler'] = substr($line, 21, 6);
  		$rec['File create date'] = substr($line, 27, 6);
  		$rec['Filler2'] = substr($line, 33, 481);
  		
  	} else if (substr($line, 0, 3) == 'UIR') {
  	
  		// UIR
  		$rec['record type description'] = 'Wholesale Usage Information Record ';
      $rec['Service Provider Code '] = substr($line,3,3);
      $rec['Event File Instance Id'] = substr($line,6,8);
      $rec['Event Record Sequence Number'] = substr($line,14,8);
      $rec['Input LinxOnline eBill File Id'] = substr($line,22,8);
      $rec['Product Billing Identifier'] = substr($line,30,8);
      $rec['Billing Element Code'] = substr($line,38,8);
      $rec['Invoice Arrangement Id'] = substr($line,46,10);
      $rec['Service arrangement ID'] = substr($line,56,10);
      $rec['Full National Number'] = substr($line,66,29);
      $rec['Originating Number (A Party)'] = substr($line,95,25);
      $rec['Destination Number (B Party)'] = substr($line,120,25);
      $rec['Originating Date'] = substr($line,145,10);
      $rec['Originating Time'] = substr($line,155,8);
      $rec['To area'] = substr($line,163,12);
      $rec['Unit of Measure Code'] = substr($line,175,5);
      $rec['Quantity'] = substr($line,180,13);
      $rec['Call Duration (hrs, mins, secs)'] = substr($line,193,9);
      $rec['Call Type code'] = substr($line,202,3);
      $rec['Record Type'] = substr($line,205,1);
      $rec['Price (Excl GST)'] = substr($line,206,15);
      $rec['Distance Range Code'] = substr($line,221,4);
      $rec['Closed User Group ID'] = substr($line,225,5);
      $rec['Reversal Charge Indicator'] = substr($line,230,1);
      $rec['1900 Call Description'] = substr($line,231,30);
  
  	} else if (substr($line, 0, 3) == 'UIT') {
  	
  		// UIT
  		$rec['record type description'] = 'Wholesale Usage Information Trailer Record';
      $rec['Service Provider Code '] = substr($line,3,3);
      $rec['Usage Record Count'] = substr($line,6,9);
      $rec['Usage Record Quantity'] = substr($line,15,18);
      $rec['Total Usage Amount'] = substr($line,33,15);
  
  	} else if (substr($line, 0, 3) == 'UAR') {
  	
  		// UAR
  		$rec['record type description'] = 'Wholesale Usage Information Adjustment Record ';
      $rec['Service Provider Code '] = substr($line,3,3);
      $rec['Event File Instance Id'] = substr($line,6,8);
      $rec['Event Record Sequence Number'] = substr($line,14,8);
      $rec['Input LinxOnline eBill File Id'] = substr($line,22,8);
      $rec['Product Billing Identifier'] = substr($line,30,8);
      $rec['Billing Element Code'] = substr($line,38,8);
      $rec['Invoice Arrangement Id'] = substr($line,46,10);
      $rec['Service arrangement ID'] = substr($line,56,10);
      $rec['Full National Number'] = substr($line,66,29);
      $rec['Originating Number (A Party)'] = substr($line,95,25);
      $rec['Destination Number (B Party)'] = substr($line,120,25);    
      $rec['Originating Date'] = substr($line,145,10);
      $rec['Originating Time'] = substr($line,155,8);
      $rec['To area'] = substr($line,163,12);
      $rec['Unit of Measure Code'] = substr($line,175,5);
      $rec['Quantity'] = substr($line,180,13);    
      $rec['Call Duration (hrs, mins, secs)'] = substr($line,193,9);
      $rec['Call Type code'] = substr($line,202,3);
      $rec['Record Type'] = substr($line,205,1);
      $rec['Price (Excl GST)'] = substr($line,206,15);    
      $rec['Usage Adjustment Reason Code'] = substr($line,221,3);
      $rec['Closed User Group ID'] = substr($line,224,5);    
      $rec['Reversal Charge Indicator'] = substr($line,229,1);    
      $rec['1900 Call Description'] = substr($line,230,30);			
  	
  	} else if (substr($line, 0, 3) == 'UAT') {
  	
  		// UAT
  		$rec['record type description'] = 'Wholesale Usage Information Adjustment Trailer Record';
      $rec['Service Provider Code '] = substr($line,3,3);
      $rec['Usage Adjustment Count'] = substr($line,6,9);
      $rec['Usage Adjustment Quantity'] = substr($line,15,18);
      $rec['Total Usage Adjustment Amount'] = substr($line,33,15);
  
  	} else if (substr($line, 0, 3) == 'SER') {
  	
  		// SER
  		$rec['record type description'] = 'Wholesale S&E Record';
      $rec['Service Provider Code '] = substr($line,3,3);
      $rec['Customer Product Item ID'] = substr($line,6,10);
      $rec['Input LinxOnline eBill File ID'] = substr($line,16,8);
      $rec['Product Billing Identifier'] = substr($line,24,8);
      $rec['Billing Element Code'] = substr($line,32,8);
      $rec['Invoice Arrangement ID'] = substr($line,40,10);
      $rec['Service arrangement ID'] = substr($line,50,10);
      $rec['Full National Number '] = substr($line,60,29);
      $rec['Product Action Code'] = substr($line,89,1);
      $rec['Purchase Order Number'] = substr($line,90,16);
      $rec['Formatted Product Effective Date'] = substr($line,106,10);
      $rec['Unit Rate Excl GST (Gross Value)'] = substr($line,116,15);
      $rec['Item Quantity'] = substr($line,131,5);
      $rec['Order Negotiated Rate Indicator'] = substr($line,136,1);
      $rec['Billing Transaction Description'] = substr($line,137,50);
      $rec['Product Description Text 1'] = substr($line,187,30);
      $rec['Product Description Text 2'] = substr($line,217,30);
      $rec['Product Description Text 3'] = substr($line,247,30);
      $rec['Data Service Type'] = substr($line,277,4);
      $rec['Bandwidth'] = substr($line,281,10);
      $rec['Charge Zone'] = substr($line,291,25);
      $rec['Service Location 1'] = substr($line,316,60);
      $rec['Service Location 2'] = substr($line,376,60);
      $rec['A end Service Nbr'] = substr($line,436,19);
      $rec['A end dlci '] = substr($line,455,4);
      $rec['A end cir '] = substr($line,459,7);
      $rec['A end vpi'] = substr($line,466,4);
      $rec['A end vci'] = substr($line,470,5);
      $rec['B end Service Nbr'] = substr($line,475,19);
      $rec['B end dlci'] = substr($line,494,4);
      $rec['B end cir '] = substr($line,498,7);
      $rec['B end vpi'] = substr($line,505,4);
      $rec['B end vci'] = substr($line,509,5);
  
  	} else if (substr($line, 0, 3) == 'SET') {
  	
  		// SET
  		$rec['record type description'] = 'Wholesale S&E Trailer Record ';
  		$rec['Service Provider Code '] = substr($line,3,3);
  		$rec['Service and Equipment Record Count'] = substr($line,6,9);
  
  	} else if (substr($line, 0, 3) == 'OCR') {
  	
  		// OCR
  		$rec['record type description'] = 'Wholesale OC&C Record';
      $rec['Service Provider Code '] = substr($line,3,3);
      $rec['Customer Product Item ID'] = substr($line,6,10);
      $rec['Input LinxOnline eBill File ID'] = substr($line,16,8);
      $rec['Product Billing Identifier'] = substr($line,24,8);
      $rec['Billing Element Code'] = substr($line,32,8);
      $rec['Invoice Arrangement ID'] = substr($line,40,10);
      $rec['Service arrangement ID'] = substr($line,50,10);
      $rec['Full National Number'] = substr($line,60,29);
      $rec['Purchase Order Number'] = substr($line,89,16);
      $rec['Formatted Product Effective Date'] = substr($line,105,10);
      $rec['Amount Sign Indicator '] = substr($line,115,1);
      $rec['Unit Rate Excl GST'] = substr($line,116,15);
      $rec['Service Order Item Quantity'] = substr($line,131,5);
      $rec['Order Negotiated Rate Indicator'] = substr($line,136,1);
      $rec['Total Instalment Quantity'] = substr($line,137,2);
      $rec['Billing Transaction Description'] = substr($line,139,50);
      $rec['Product Description Text 1'] = substr($line,189,30);
      $rec['Product Description Text 2'] = substr($line,219,30);
      $rec['Product Description Text 3'] = substr($line,249,30);
      $rec['Price (Excl GST)'] = substr($line,279,15);
  
  	} else if (substr($line, 0, 3) == 'OCT') {
  	
  		// OCT
  		$rec['record type description'] = 'Wholesale OC&C Trailer Record';
      $rec['Service Provider Code '] = substr($line,3,3);
      $rec['Record Count'] = substr($line,6,9);
      $rec['Amount Sign Indicator '] = substr($line,15,1);
      $rec['Total OC&C Amount '] = substr($line,16,15);
  
  	} else if (substr($line, 0, 3) == 'OAT') {
  	
  		// OAT
  		$rec['record type description'] = 'Wholesale OC&C Adjustment Trailer Record';
      $rec['Service Provider Code'] = substr($line,3,3);
      $rec['Record Count'] = substr($line,6,9);
      $rec['Amount Sign Indicator'] = substr($line,15,1);
      $rec['Total OC&C Amount'] = substr($line,16,15);
  
  	} else if (substr($line, 0, 3) == 'NTR') {
  	
  		// NTR
  		$rec['record type description'] = 'Wholesale Non-Usage Tariff Record';
      $rec['Service Provider Code'] = substr($line,3,3);
      $rec['Wholesale Redirection Group'] = substr($line,6,6);
      $rec['Product Billing Identifier'] = substr($line,12,8);
      $rec['Billing Element Code'] = substr($line,20,8);
      $rec['Activity Completed Indicator'] = substr($line,28,1);
      $rec['Serv Charge Item Grp'] = substr($line,29,20);
      $rec['Unbilled Indicator'] = substr($line,49,1);
      $rec['GST Percentage Rate'] = substr($line,50,7);
      $rec['Rate Structure Start Date'] = substr($line,57,10);
      $rec['Rate Structure End Date'] = substr($line,67,10);
      $rec['Amount Sign Indicator '] = substr($line,77,1);
      $rec['Unit Rate (excl. GST)'] = substr($line,78,15);
      $rec['Billing Element Category Code'] = substr($line,93,1);
      $rec['Description'] = substr($line,94,50);
      $rec['Unit High Quantity'] = substr($line,144,8);
      $rec['Unit Low Quantity'] = substr($line,152,8);
      $rec['CAID'] = substr($line,160,10);
      $rec['Agreement Start Date'] = substr($line,170,10);
      $rec['Agreement End Date'] = substr($line,180,10);
      $rec['Tariff Change Indicator'] = substr($line,190,1);
  
  	} else if (substr($line, 0, 3) == 'NTT') {
  	
  		// NTT
  		$rec['record type description'] = 'Wholesale Non-Usage Tariff Trailer Record';
  		$rec['Service Provider Code '] = substr($line,3,3);
  		$rec['Record Count'] = substr($line,6,9);
  
  	} else if (substr($line, 0, 3) == 'UTR') {
  	
  		// UTR
  		$rec['record type description'] = 'Wholesale Usage Tariff Record ';
      $rec['Service Provider Code '] = substr($line,3,3);
      $rec['Wholesale Redirection Group'] = substr($line,6,6);
      $rec['Product Billing Identifier'] = substr($line,12,8);
      $rec['Billing Element Code'] = substr($line,20,8);
      $rec['Activity Completed Indicator'] = substr($line,28,1);
      $rec['Serv Charge Item Grp'] = substr($line,29,20);
      $rec['Unbilled Indicator'] = substr($line,49,1);
      $rec['GST Percentage Rate'] = substr($line,50,7);
      $rec['Rate Structure Start Date'] = substr($line,57,10);
      $rec['Rate Structure End Date'] = substr($line,67,10);
      $rec['Description'] = substr($line,77,80);
      $rec['UOM Code'] = substr($line,157,5);
      $rec['Rate Period'] = substr($line,162,1);
      $rec['Rate Period Description'] = substr($line,163,10);
      $rec['Flagfall amount sign indicator'] = substr($line,173,1);
      $rec['Flagfall Amount'] = substr($line,174,15);
      $rec['Unit Rate'] = substr($line,189,15);
      $rec['Pulse Interval'] = substr($line,204,7);
      $rec['Day Of Week Start'] = substr($line,211,1);
      $rec['Day Of Week End'] = substr($line,212,1);
      $rec['Call Range Start Time'] = substr($line,213,8);
      $rec['Call Range End Time'] = substr($line,221,8);
      $rec['Distance Range Code'] = substr($line,229,4);
      $rec['Distance Range Description'] = substr($line,233,50);
      $rec['Unit rate Method'] = substr($line,283,1);
      $rec['Unit High QTY'] = substr($line,284,8);
      $rec['Unit Low QTY'] = substr($line,292,8);
      $rec['Max. Charge amount'] = substr($line,300,11);
      $rec['Min. Charge amount'] = substr($line,311,11);
      $rec['Max. Call Length'] = substr($line,322,11);
      $rec['Min. Call length'] = substr($line,333,11);
      $rec['Rate QTY depend. indicator'] = substr($line,344,1);
      $rec['Tariff Change Indicator'] = substr($line,345,1);
  
  	} else if (substr($line, 0, 3) == 'UTT') {
  	
  		// UTT
  		$rec['record type description'] = 'Wholesale Usage Tariff Trailer Record ';
      $rec['Service Provider Code '] = substr($line,3,3);
      $rec['Record Count'] = substr($line,6,9);
  
  	} else if (substr($line, 0, 3) == 'CTR') {
  	
  		// CTR
  		$rec['record type description'] = 'Wholesale CNR Usage Tariff Record ';
      $rec['Service Provider Code'] = substr($line,3,3);
      $rec['Wholesale Redirection Group'] = substr($line,6,6);
      $rec['Product Billing Identifier'] = substr($line,12,8);
      $rec['Billing Element Code'] = substr($line,20,8);
      $rec['Activity Completed Indicator'] = substr($line,28,1);
      $rec['Serv Charge Item Grp'] = substr($line,29,20);
      $rec['Unbilled Indicator'] = substr($line,49,1);
      $rec['GST Percentage Rate'] = substr($line,50,7);
      $rec['Rate Structure Start Date'] = substr($line,57,10);
      $rec['Rate Structure End Date'] = substr($line,67,10);
      $rec['Description'] = substr($line,77,80);
      $rec['UOM Code'] = substr($line,157,5);
      $rec['Rate Period'] = substr($line,162,1);
      $rec['Rate Period Description'] = substr($line,163,10);
      $rec['Flagfall amount sign indicator'] = substr($line,173,1);
      $rec['Flagfall Amount'] = substr($line,174,15);
      $rec['Unit Rate'] = substr($line,189,15);
      $rec['Pulse Interval'] = substr($line,204,7);
      $rec['Day Of Week Start'] = substr($line,211,1);
      $rec['Day Of Week End'] = substr($line,212,1);
      $rec['Call Range Start Time'] = substr($line,213,8);
      $rec['Call Range End Time'] = substr($line,221,8);
      $rec['Distance Range Code'] = substr($line,229,4);
      $rec['Distance Range Description'] = substr($line,233,50);
      $rec['Unit rate Method'] = substr($line,283,1);
      $rec['Unit High QTY'] = substr($line,284,8);
      $rec['Unit Low QTY'] = substr($line,292,8);
      $rec['Max. Charge amount'] = substr($line,300,11);
      $rec['Min. Charge amount'] = substr($line,311,11);
      $rec['Max. Call Length'] = substr($line,322,11);
      $rec['Min. Call length'] = substr($line,333,11);
      $rec['Rate QTY depend. indicator'] = substr($line,344,1);
      $rec['CAID'] = substr($line,345,10);
      $rec['Agreement Start Date'] = substr($line,355,10);
      $rec['Agreement End Date'] = substr($line,365,10);
      $rec['Tariff Change Indicator'] = substr($line,375,1);
  
  	} else if (substr($line, 0, 3) == 'CTT') {
  	
  		// CTT
  		$rec['record type description'] = 'Wholesale CNR Usage Tariff Trailer Record ';
  		$rec['Service Provider Code '] = substr($line,3,3);
  		$rec['Record Count'] = substr($line,6,9);
  	} else if (substr($line, 0, 3) == 'FTR') {
  	
  		// FTR
  		$rec['record type description'] = 'Processing File Trailer Record';
      $rec['Interface File Type Code'] = substr($line,3,3);
      $rec['File Source Code'] = substr($line,6,5);
      $rec['File sequence number'] = substr($line,11,4);
      $rec['FTR Detail record count'] = substr($line,15,9);
  
  	
  	} else {
  	
  		// Invalid
  		$rec['record type description'] = 'Unknown / invalid record type - ' . substr($line, 0, 3);
  
  	}
  		
  	
  	
  
  	echo "\n";
  	
  	while ($cel = each($rec)) {
  		
  		printf("%40s   %s\n", $cel['key'], $cel['value']);
  	}
  	
  	echo "\n";
  	echo "===========================================================\n";
  	
  	$linecount++;
	
	}
		
}
fclose($fh);

