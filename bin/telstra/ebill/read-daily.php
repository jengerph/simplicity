#!/usr/bin/php
<?php

// Read file from telstra

include "/var/www/simplicity/htdocs/setup.inc";
include_once("class.phpmailer.php");

$filename = $argv[1];
$sql = $argv[2];

if ($sql == '') {
	$sql = 0;
}

if ($sql == 1) {
	// Connect to database
  $config = new config();
  $db = new db($config->mysql_server_name, $config->mysql_database_name,  $config->mysql_user_name, $config->mysql_user_password);
    	
}
	


$fh = fopen($filename,'r');
$linecount = 0;
$counters = array();

$file_seq = '';
$file_date = '';

while ($line = fgets($fh)) {

	if (strlen($line) > 3) {
  	$rec = array();
  	
  	if (!isset($counters[substr($line, 0, 3)])) {
  		$counters[substr($line, 0, 3)] = 0;
  	}
  	
  	$counters[substr($line, 0, 3)]++;
  	
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
  		
  		$file_seq = $rec['File Registration Sequence Number'];
			$file_date = $rec['File event date'];
			
			if ($sql == 1) {
				
				// Need to insert
				
				$query = "INSERT INTO telstra_fdr values (" . $db->quote($rec['Interface file type code']) . "," . $db->quote($rec['File source code']) . "," . $db->quote($rec['File Registration Sequence Number']) . "," . $db->quote($rec['File event date']) . "," . $db->quote($rec['File create date']) . ")";
				$result = $db->execute_query($query);
				if ($result == 0) {
					
					echo "Failed to insert SQL entry $query\n";
					exit();
				}

			}

  		
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
      $rec['Quantity'] = substr($line,180,13)/100000;
      $rec['Call Duration (hrs, mins, secs)'] = substr($line,193,9);
      $rec['Call Type code'] = substr($line,202,3);
      $rec['Record Type'] = substr($line,205,1);
      $rec['Price (Excl GST)'] = substr($line,206,15)/10000000;
      $rec['Distance Range Code'] = substr($line,221,4);
      $rec['Closed User Group ID'] = substr($line,225,5);
      $rec['Reversal Charge Indicator'] = substr($line,230,1);
      $rec['1900 Call Description'] = substr($line,231,30);

			if ($sql == 1) {
				
				// Need to insert
				
				$query = "INSERT INTO telstra_uir values (" . $db->quote($file_seq) . "," . $db->quote($rec['Event File Instance Id']);
				$query .= "," . $db->quote($rec['Event Record Sequence Number']);
				$query .= "," . $db->quote($rec['Input LinxOnline eBill File Id']);
				$query .= "," . $db->quote($rec['Product Billing Identifier']);
				$query .= "," . $db->quote($rec['Billing Element Code']);
				$query .= "," . $db->quote($rec['Invoice Arrangement Id']);
				$query .= "," . $db->quote($rec['Service arrangement ID']);
				$query .= "," . $db->quote($rec['Full National Number']);
				$query .= "," . $db->quote($rec['Originating Number (A Party)']);
				$query .= "," . $db->quote($rec['Destination Number (B Party)']);
				$query .= "," . $db->quote(trim($rec['Originating Date']));
				$query .= "," . $db->quote($rec['Originating Time']);
				$query .= "," . $db->quote($rec['To area']);
				$query .= "," . $db->quote($rec['Unit of Measure Code']);
				$query .= "," . $db->quote($rec['Quantity']);
				$query .= "," . $db->quote($rec['Call Duration (hrs, mins, secs)']);
				$query .= "," . $db->quote($rec['Call Type code']);
				$query .= "," . $db->quote($rec['Record Type']);
				$query .= "," . $db->quote($rec['Price (Excl GST)']);
				$query .= "," . $db->quote(trim($rec['Distance Range Code']));
				$query .= "," . $db->quote($rec['Closed User Group ID']);
				$query .= "," . $db->quote($rec['Reversal Charge Indicator']);
				$query .= "," . $db->quote($rec['1900 Call Description']);
				
				
				$query .= ")";
				$result = $db->execute_query($query);
				if ($result == 0) {
					
					echo "Failed to insert SQL entry $query\n";
					exit();
				}

			}
  
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
      $rec['Full National Number'] = substr($line,60,29);
      $rec['Product Action Code'] = substr($line,89,1);
      $rec['Purchase Order Number'] = substr($line,90,16);
      $rec['Formatted Product Effective Date'] = substr($line,106,10);
      $rec['Unit Rate Excl GST (Gross Value)'] = substr($line,116,15)/10000000;
      $rec['Item Quantity'] = substr($line,131,5)/100;
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
      $rec['A end dlci'] = substr($line,455,4);
      $rec['A end cir'] = substr($line,459,7);
      $rec['A end vpi'] = substr($line,466,4);
      $rec['A end vci'] = substr($line,470,5);
      $rec['B end Service Nbr'] = substr($line,475,19);
      $rec['B end dlci'] = substr($line,494,4);
      $rec['B end cir'] = substr($line,498,7);
      $rec['B end vpi'] = substr($line,505,4);
      $rec['B end vci'] = substr($line,509,5);


			if ($sql == 1) {
				
				// Need to insert
				
				$query = "INSERT INTO telstra_ser values (" . $db->quote($file_seq);
				$query .= "," . $db->quote($rec['Customer Product Item ID']);
				$query .= "," . $db->quote($rec['Input LinxOnline eBill File ID']);
				$query .= "," . $db->quote($rec['Product Billing Identifier']);
				$query .= "," . $db->quote($rec['Billing Element Code']);
				$query .= "," . $db->quote($rec['Invoice Arrangement ID']);
				$query .= "," . $db->quote($rec['Service arrangement ID']);
				$query .= "," . $db->quote($rec['Full National Number']);
				$query .= "," . $db->quote($rec['Product Action Code']);
				$query .= "," . $db->quote($rec['Purchase Order Number']);
				$query .= "," . $db->quote(trim($rec['Formatted Product Effective Date']));
				$query .= "," . $db->quote($rec['Unit Rate Excl GST (Gross Value)']);
				$query .= "," . $db->quote($rec['Item Quantity']);
				$query .= "," . $db->quote($rec['Order Negotiated Rate Indicator']);
				$query .= "," . $db->quote($rec['Billing Transaction Description']);
				$query .= "," . $db->quote($rec['Product Description Text 1']);
				$query .= "," . $db->quote($rec['Product Description Text 2']);
				$query .= "," . $db->quote($rec['Product Description Text 3']);
				$query .= "," . $db->quote($rec['Data Service Type']);
				$query .= "," . $db->quote($rec['Bandwidth']);
				$query .= "," . $db->quote($rec['Charge Zone']);
				$query .= "," . $db->quote($rec['Service Location 1']);
				$query .= "," . $db->quote($rec['Service Location 2']);
				$query .= "," . $db->quote($rec['A end Service Nbr']);
				$query .= "," . $db->quote($rec['A end dlci']);
				$query .= "," . $db->quote($rec['A end cir']);
				$query .= "," . $db->quote($rec['A end vpi']);
				$query .= "," . $db->quote($rec['A end vci']);
				$query .= "," . $db->quote($rec['B end Service Nbr']);
				$query .= "," . $db->quote($rec['B end dlci']);
				$query .= "," . $db->quote($rec['B end cir']);
				$query .= "," . $db->quote($rec['B end vpi']);
				$query .= "," . $db->quote($rec['B end vci']);
				
				
				$query .= ")";
				$result = $db->execute_query($query);
				if ($result == 0) {
					
					echo "Failed to insert SQL entry $query\n";
					exit();
				}

			}  
  	} else if (substr($line, 0, 3) == 'SET') {
  	
  		// SET
  		$rec['record type description'] = 'Wholesale S&E Trailer Record ';
  		$rec['Service Provider Code '] = substr($line,3,3);
  		$rec['Service and Equipment Record Count'] = substr($line,6,9);
  
  	} else if (substr($line, 0, 3) == 'OCR') {
  	
  		// OCR
  		$rec['record type description'] = 'Wholesale OC&C Record';
      $rec['Service Provider Code'] = substr($line,3,3);
      $rec['Customer Product Item ID'] = substr($line,6,10);
      $rec['Input LinxOnline eBill File ID'] = substr($line,16,8);
      $rec['Product Billing Identifier'] = substr($line,24,8);
      $rec['Billing Element Code'] = substr($line,32,8);
      $rec['Invoice Arrangement ID'] = substr($line,40,10);
      $rec['Service arrangement ID'] = substr($line,50,10);
      $rec['Full National Number'] = substr($line,60,29);
      $rec['Purchase Order Number'] = substr($line,89,16);
      $rec['Formatted Product Effective Date'] = substr($line,105,10);
      $rec['Amount Sign Indicator'] = substr($line,115,1);
      $rec['Unit Rate Excl GST'] = substr($line,116,15)/10000000;
      $rec['Service Order Item Quantity'] = substr($line,131,5)/100;
      $rec['Order Negotiated Rate Indicator'] = substr($line,136,1);
      $rec['Total Instalment Quantity'] = substr($line,137,2);
      $rec['Billing Transaction Description'] = substr($line,139,50);
      $rec['Product Description Text 1'] = substr($line,189,30);
      $rec['Product Description Text 2'] = substr($line,219,30);
      $rec['Product Description Text 3'] = substr($line,249,30);
      $rec['Price (Excl GST)'] = substr($line,279,15)/10000000;

			if ($sql == 1) {
				
				// Need to insert
				
				$query = "INSERT INTO telstra_ocr values (" . $db->quote($file_seq);
				$query .= "," . $db->quote($rec['Customer Product Item ID']);
				$query .= "," . $db->quote($rec['Input LinxOnline eBill File ID']);
				$query .= "," . $db->quote($rec['Product Billing Identifier']);
				$query .= "," . $db->quote($rec['Billing Element Code']);
				$query .= "," . $db->quote($rec['Invoice Arrangement ID']);
				$query .= "," . $db->quote($rec['Service arrangement ID']);
				$query .= "," . $db->quote($rec['Full National Number']);
				$query .= "," . $db->quote($rec['Purchase Order Number']);
				$query .= "," . $db->quote(trim($rec['Formatted Product Effective Date']));
				$query .= "," . $db->quote($rec['Amount Sign Indicator']);
				$query .= "," . $db->quote($rec['Unit Rate Excl GST']);
				$query .= "," . $db->quote($rec['Service Order Item Quantity']);
				$query .= "," . $db->quote($rec['Order Negotiated Rate Indicator']);
				$query .= "," . $db->quote($rec['Total Instalment Quantity']);
				$query .= "," . $db->quote($rec['Billing Transaction Description']);
				$query .= "," . $db->quote($rec['Product Description Text 1']);
				$query .= "," . $db->quote($rec['Product Description Text 2']);
				$query .= "," . $db->quote($rec['Product Description Text 3']);
				$query .= "," . $db->quote($rec['Price (Excl GST)']);

				
				$query .= ")";
				$result = $db->execute_query($query);
				if ($result == 0) {
					
					echo "Failed to insert SQL entry $query\n";
					exit();
				}

			}   
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
      $rec['GST Percentage Rate'] = substr($line,50,7)/10000;
      $rec['Rate Structure Start Date'] = substr($line,57,10);
      $rec['Rate Structure End Date'] = substr($line,67,10);
      $rec['Amount Sign Indicator'] = substr($line,77,1);
      $rec['Unit Rate (excl. GST)'] = substr($line,78,15)/10000000;
      $rec['Billing Element Category Code'] = substr($line,93,1);
      $rec['Description'] = substr($line,94,50);
      $rec['Unit High Quantity'] = substr($line,144,8);
      $rec['Unit Low Quantity'] = substr($line,152,8);
      $rec['CAID'] = substr($line,160,10);
      $rec['Agreement Start Date'] = substr($line,170,10);
      $rec['Agreement End Date'] = substr($line,180,10);
      $rec['Tariff Change Indicator'] = substr($line,190,1);

			if ($sql == 1) {
				
				// Need to insert
				
				$query = "INSERT INTO telstra_ntr values (" . $db->quote($file_seq);
				$query .= "," . $db->quote($rec['Product Billing Identifier']);
				$query .= "," . $db->quote($rec['Billing Element Code']);
				$query .= "," . $db->quote($rec['Activity Completed Indicator']);
				$query .= "," . $db->quote($rec['Serv Charge Item Grp']);
				$query .= "," . $db->quote($rec['Unbilled Indicator']);
				$query .= "," . $db->quote($rec['GST Percentage Rate']);
				$query .= "," . $db->quote(trim($rec['Rate Structure Start Date']));
				$query .= "," . $db->quote(trim($rec['Rate Structure End Date']));
				$query .= "," . $db->quote($rec['Amount Sign Indicator']);
				$query .= "," . $db->quote($rec['Unit Rate (excl. GST)']);
				$query .= "," . $db->quote($rec['Billing Element Category Code']);
				$query .= "," . $db->quote($rec['Description']);
				$query .= "," . $db->quote($rec['Unit High Quantity']);
				$query .= "," . $db->quote($rec['Unit Low Quantity']);
				$query .= "," . $db->quote($rec['CAID']);
				$query .= "," . $db->quote(trim($rec['Agreement Start Date']));
				$query .= "," . $db->quote(trim($rec['Agreement End Date']));
				$query .= "," . $db->quote($rec['Tariff Change Indicator']);

				
				$query .= ")";

				$result = $db->execute_query($query);
				if ($result == 0) {
					
					echo "Failed to insert SQL entry $query\n";
					exit();
				}
				
			}   
  
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
      $rec['GST Percentage Rate'] = substr($line,50,7)/10000;
      $rec['Rate Structure Start Date'] = trim(substr($line,57,10));
      $rec['Rate Structure End Date'] = trim(substr($line,67,10));
      $rec['Description'] = substr($line,77,80);
      $rec['UOM Code'] = substr($line,157,5);
      $rec['Rate Period'] = substr($line,162,1);
      $rec['Rate Period Description'] = substr($line,163,10);
      $rec['Flagfall amount sign indicator'] = substr($line,173,1);
      $rec['Flagfall Amount'] = substr($line,174,15)/10000000;
      $rec['Unit Rate'] = substr($line,189,15)/10000000;
      $rec['Pulse Interval'] = substr($line,204,7)/1000;
      $rec['Day Of Week Start'] = substr($line,211,1);
      $rec['Day Of Week End'] = substr($line,212,1);
      $rec['Call Range Start Time'] = substr($line,213,8);
      $rec['Call Range End Time'] = substr($line,221,8);
      $rec['Distance Range Code'] = substr($line,229,4);
      $rec['Distance Range Description'] = substr($line,233,50);
      $rec['Unit rate Method'] = substr($line,283,1);
      $rec['Unit High QTY'] = substr($line,284,8);
      $rec['Unit Low QTY'] = substr($line,292,8);
      $rec['Max. Charge amount'] = substr($line,300,11)/10000;
      $rec['Min. Charge amount'] = substr($line,311,11)/10000;
      $rec['Max. Call Length'] = substr($line,322,11);
      $rec['Min. Call length'] = substr($line,333,11);
      $rec['Rate QTY depend. indicator'] = substr($line,344,1);
      $rec['Tariff Change Indicator'] = substr($line,345,1);


			if ($sql == 1) {
				
				// Need to insert
				
				$query = "INSERT INTO telstra_utr values (" . $db->quote($file_seq);
				$query .= "," . $db->quote($rec['Wholesale Redirection Group']);
				$query .= "," . $db->quote($rec['Product Billing Identifier']);
				$query .= "," . $db->quote($rec['Billing Element Code']);
				$query .= "," . $db->quote($rec['Activity Completed Indicator']);
				$query .= "," . $db->quote($rec['Serv Charge Item Grp']);
				$query .= "," . $db->quote($rec['Unbilled Indicator']);
				$query .= "," . $db->quote($rec['GST Percentage Rate']);
				$query .= "," . $db->quote(trim($rec['Rate Structure Start Date']));
				$query .= "," . $db->quote(trim($rec['Rate Structure End Date']));
				$query .= "," . $db->quote($rec['Description']);
				$query .= "," . $db->quote($rec['UOM Code']);
				$query .= "," . $db->quote($rec['Rate Period']);
				$query .= "," . $db->quote($rec['Rate Period Description']);
				$query .= "," . $db->quote($rec['Flagfall amount sign indicator']);
				$query .= "," . $db->quote($rec['Flagfall Amount']);
				$query .= "," . $db->quote($rec['Unit Rate']);
				$query .= "," . $db->quote($rec['Pulse Interval']);
				$query .= "," . $db->quote($rec['Day Of Week Start']);
				$query .= "," . $db->quote($rec['Day Of Week End']);
				$query .= "," . $db->quote($rec['Call Range Start Time']);
				$query .= "," . $db->quote($rec['Call Range End Time']);
				$query .= "," . $db->quote($rec['Distance Range Code']);
				$query .= "," . $db->quote($rec['Distance Range Description']);
				$query .= "," . $db->quote($rec['Unit rate Method']);
				$query .= "," . $db->quote($rec['Unit High QTY']);
				$query .= "," . $db->quote($rec['Unit Low QTY']);
				$query .= "," . $db->quote($rec['Max. Charge amount']);
				$query .= "," . $db->quote($rec['Min. Charge amount']);
				$query .= "," . $db->quote($rec['Max. Call Length']);
				$query .= "," . $db->quote($rec['Min. Call length']);
				$query .= "," . $db->quote($rec['Rate QTY depend. indicator']);
				$query .= "," . $db->quote($rec['Tariff Change Indicator']);

				
				$query .= ")";

				$result = $db->execute_query($query);
				if ($result == 0) {
					
					echo "Failed to insert SQL entry $query\n";
					exit();
				}
				
			}   
    
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

echo "Summary for " . $file_seq . ' - ' . $file_date . ": \n";
while ($cel = each($counters)) {
	
	echo $cel['key'] .  ' - ' . $cel['value'] . ' record(s)' . "\n";
}


if ($sql == 1) {
	
	// Send email
  $mail = new PHPMailer();

  $mail->From = "service.delivery@xi.com.au";
  $mail->FromName = "X Integration Pty Ltd";
  $mail->Subject = "Telstra EBill Daily Imported - $filename";
  $mail->Host = "127.0.0.1";
  $mail->Mailer = "smtp";
  
  $mail->Body = "Summary for " . $file_seq . ' - ' . $file_date . ": \r\n";
  
  reset($counters);
	while ($cel = each($counters)) {
	
		$mail->Body .= $cel['key'] .  ' - ' . $cel['value'] . ' record(s)' . "\r\n";
	}

  $mail->AddAddress('notifications@xi.com.au');

  $mail->Send();
	
}