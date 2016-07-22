#!/usr/bin/php
<?php

// Read file from telstra

include "/var/www/simplicity/htdocs/setup.inc";

$filename = $argv[1];


$fh = fopen($filename,'r');
$linecount = 0;
while ($line = fgets($fh)) {

	$rec = array();
	
	if ($linecount == 0) {
		
		// Header
		$rec['record type description'] = 'Header Record';
		$rec['Interface Record Type'] = substr($line, 0, 3);
		$rec['Service Provider code'] = substr($line, 3, 3);
		$rec['File source code'] = substr($line, 6, 5);
		$rec['File Sequence Number'] = substr($line, 11, 4);
		$rec['File event date'] = substr($line, 15, 6);
		$rec['Filler'] = substr($line, 21, 727);
		
	} else if (substr($line, 0, 3) == 'FTR') {
	
		// Footer
		$rec['record type description'] = 'Footer Record';
		$rec['Interface Record Type'] = substr($line, 0, 3);
		$rec['Service Provider code'] = substr($line, 3, 3);
		$rec['File source code'] = substr($line, 6, 5);
		$rec['File Sequence Number'] = substr($line, 11, 4);
		$rec['Detail Record Count'] = substr($line, 15, 9);
		$rec['Filler'] = substr($line, 24, 727);
			
	} else {
		
		// Middle records
		$rec['Invoice Arrangement Id'] = substr($line, 0, 10);
		$rec['Bill Issue Dt'] = substr($line, 10, 8);
		$rec['Doc Ref Nbr'] = substr($line, 18, 10);
		$rec['Invoice record sequence Nbr'] = substr($line, 28, 9);
		$rec['Section Type'] = substr($line, 37, 2);
		$rec['Line Type'] = substr($line, 39, 1);
		
		if ($rec['Line Type'] == 'D') {
			$rec['Line Type Text'] = 'Detail';
		} else if ($rec['Line Type'] == 'S') {
			$rec['Line Type Text'] = 'Sub total';
		} else if ($rec['Line Type'] == 'T') {
			$rec['Line Type Text'] = 'Total';
		}
		
		// Lets look at the sections
		
		if ($rec['Section Type'] == 'AD') {

			$rec['record type description'] = 'Adjustments Record';
      $rec['Bill Pull Date'] = substr($line, 40,8);
      $rec['Orig Doc Ref Nbr'] = substr($line, 48,10);
      $rec['Frmtd Service Nbr'] = substr($line, 58,18);
      $rec['Billing Trans Desc'] = substr($line, 76,50);
      $rec['Adjustment amount Incl GST'] = substr($line, 126,14);
      $rec['GST Adjustment Amount'] = substr($line, 140,14);
      $rec['Dispute Reference Number'] = substr($line, 154,10);
      $rec['Your Reference Number'] = substr($line, 164,10);
      $rec['Filler'] = substr($line, 174,584);
      
		} else if ($rec['Section Type'] == 'AS') {

			$rec['record type description'] = 'Account Summary Record';
      $rec['GIRN'] = substr($line, 40,7);
      $rec['Charge Desc'] = substr($line, 47,30);
      $rec['Gross value – GST exclusive'] = sprintf("%0.4f", substr($line, 77,14)/10000);
      $rec['Net value – GST exclusive'] = sprintf("%0.4f", substr($line, 91,14)/10000);
      $rec['GST amount on Net value '] = sprintf("%0.4f", substr($line, 105,14)/10000);
      $rec['Charge Amt (inclusive of GST)'] = sprintf("%0.4f", substr($line, 119,14)/10000);
      $rec['Filler'] = substr($line, 133,625);

		} else if ($rec['Section Type'] == 'DC') {

			$rec['record type description'] = 'Directory Charges Record';
      $rec['Directory Charges Record type'] = substr($line, 40,1);
      $rec['GIRN'] = substr($line, 41,7);
      $rec['Invc COSVC Desc'] = substr($line, 48,30);
      $rec['Full National Number '] = substr($line, 78,29);
      $rec['Service Nbr Label'] = substr($line, 107,30);
      $rec['Product Offering Name'] = substr($line, 137,40);
      $rec['Instalment Payment Number'] = substr($line, 177,10);
      $rec['Generic Quantity'] = substr($line, 187,10);
      $rec['Start Date'] = substr($line, 197,8);
      $rec['Unit Rate Incl GST'] = sprintf("%0.4f", substr($line, 205,14)/10000);
      $rec['Summary Gross Amount Excl GST '] = sprintf("%0.4f", substr($line, 219,14)/10000);
      $rec['Summary Net Amount Excl GST'] = sprintf("%0.4f", substr($line, 233,14)/10000);
      $rec['GST Amount'] = sprintf("%0.4f", substr($line, 247,14)/10000);
      $rec['Summary Price amount Incl GST'] = sprintf("%0.4f", substr($line, 261,14)/10000);
      $rec['Text One'] = substr($line, 275,30);
      $rec['Product Billing Identifier'] = substr($line, 305,8);
      $rec['Billing Element Code'] = substr($line, 313,8);
      $rec['Filler'] = substr($line, 321,437);
      
		} else if ($rec['Section Type'] == 'DI') {

			$rec['record type description'] = 'Discount Summary – Info Only Record';
      $rec['Discount Summary Record Type'] = substr($line, 40,1);
      $rec['GIRN'] = substr($line, 41,7);

      $rec['Plan ID'] = substr($line, 48,8);
      $rec['Pricing subplan number'] = substr($line, 56,3);
      $rec['Pricing Plan name'] = substr($line, 59,50);
      $rec['Pricing subplan name'] = substr($line, 109,30);
      $rec['Serv Charge Item Grp'] = substr($line, 139,20);
      $rec['Service type desc'] = substr($line, 159,30);
      $rec['Call type desc'] = substr($line, 189,45);
      $rec['Start Date'] = substr($line, 234,8);
      $rec['End Date'] = substr($line, 242,8);
      $rec['Cmn-to-nbr group'] = substr($line, 250,29);
      $rec['Eligible number ctc name'] = substr($line, 279,20);
      $rec['Discount percent off'] = substr($line, 299,14);

      $rec['Generic Qty'] = substr($line, 313,10);

      $rec['Total Eligible Charges excl GST'] = sprintf("%0.4f", substr($line, 323,14)/10000);

      $rec['Total Eligible Charges incl GST'] = sprintf("%0.4f", substr($line, 337,14)/10000);

      $rec['Earned Discount excl GST'] = sprintf("%0.4f", substr($line, 351,14)/10000);

      $rec['Earned Discount incl GST'] = sprintf("%0.4f", substr($line, 365,14)/10000);

      $rec['Extract To Date'] = substr($line, 379,8);
      $rec['Generic Duration A'] = substr($line, 387,11);
      $rec['Generic Duration B'] = substr($line, 398,11);
      $rec['Filler'] = substr($line, 409,349);
      
		} else if ($rec['Section Type'] == 'DF') {

			$rec['record type description'] = 'Daily File Inventory Details  Record';
      $rec['File Registration Sequence Number'] = substr($line, 40,4);
      $rec['File Transmission Date'] = substr($line, 44,8);
      $rec['High Event Date'] = substr($line, 52,8);
      $rec['Usage Record Count'] = substr($line, 60,9);
      $rec['Usage Record Quantity'] = substr($line, 69,18);

      $rec['Adjustment Record Count'] = substr($line, 87,9);
      $rec['Adjustment Record Quantity'] = substr($line, 96,18);

      $rec['OC&C Record Count'] = substr($line, 114,9);
      $rec['OC&C Adjustment Record Count'] = substr($line, 123,9);
      $rec['Filler'] = substr($line, 132,626);
      
		} else if ($rec['Section Type'] == 'GP') {

			$rec['record type description'] = 'Group Plan Details Record';
      $rec['Group Plan Record Type'] = substr($line, 40,1);
      $rec['Plan Id'] = substr($line, 41,8);
      $rec['Pricing Subplan Number'] = substr($line, 49,3);
      $rec['GIRN'] = substr($line, 52,7);
      $rec['Pricing Plan Name'] = substr($line, 59,50);
      $rec['Pricing Subplan Name'] = substr($line, 109,30);
      $rec['Serv Charge Item Grp'] = substr($line, 139,20);
      $rec['Service Type Desc'] = substr($line, 159,30);
      $rec['Call Type Desc'] = substr($line, 189,45);
      $rec['Doc Ref Nbr'] = substr($line, 234,10);
      $rec['Start Date'] = substr($line, 244,8);
      $rec['End Date'] = substr($line, 252,8);
      $rec['Total Eligible Charges Excl GST'] = sprintf("%0.4f", substr($line, 260,14)/10000);
      $rec['Discount Amount Excl GST '] = sprintf("%0.4f", substr($line, 274,14)/10000);
      $rec['Discount Amount Price Incl GST '] = sprintf("%0.4f", substr($line, 288,14)/10000);
      $rec['Filler'] = substr($line, 302,456);

		} else if ($rec['Section Type'] == 'HD') {

			$rec['record type description'] = 'Header Record';
      $rec['Header Record Type'] = substr($line, 40,1);
      $rec['Enq Tel Nbr'] = substr($line, 41,11);
      $rec['Total Last Bill'] = sprintf("%0.4f", substr($line, 52,14)/10000);
      $rec['Total Payment Received'] = sprintf("%0.4f", substr($line, 66,14)/10000);
      $rec['Total Adjustment'] = sprintf("%0.4f", substr($line, 80,14)/10000);
      $rec['Total GST Amt on Adjustment'] = sprintf("%0.4f", substr($line, 94,14)/10000);
      $rec['Total Amt this Bill'] = sprintf("%0.4f", substr($line, 108,13 +1)/10000);
      $rec['Total GST on Tot Amt this Bill'] = sprintf("%0.4f", substr($line, 122,14)/10000);
      $rec['Total Amt Payable'] = sprintf("%0.4f", substr($line, 136,13 +1)/10000);
      $rec['Total Amt Overdue'] = sprintf("%0.4f", substr($line, 150,13 +1)/10000);
      $rec['Payment Due Date'] = substr($line, 164,8);
      $rec['Payment Explanation'] = substr($line, 172,60);
      $rec['Customer Name'] = substr($line, 232,30);
      $rec['Cust Address Line one'] = substr($line, 262,30);
      $rec['Cust Address Line two'] = substr($line, 292,32);
      $rec['Cust Address Line three'] = substr($line, 324,32);
      $rec['Company Name'] = substr($line, 356,27);
      $rec['ABN Number'] = substr($line, 383,18);
      $rec['Tax Invoice literal'] = substr($line, 401,11);
      $rec['Filler'] = substr($line, 412,346);
      
		} else if ($rec['Section Type'] == 'MS') {

			$rec['record type description'] = 'Messages Record';
      $rec['Insert text'] = substr($line, 40,50);
      $rec['Filler'] = substr($line, 90,668);
      
		} else if ($rec['Section Type'] == 'OC') {

			$rec['record type description'] = 'Other Charges and Credits Record';
      $rec['GIRN'] = substr($line, 40,7);
      $rec['Invc COSVC Desc'] = substr($line, 47,30);
      $rec['Full National Number '] = substr($line, 77,29);
      $rec['Generic Qty'] = substr($line, 106,10);
      $rec['Billing Trans Desc'] = substr($line, 116,50);
      $rec['Tran Type Desc'] = substr($line, 166,25);
      $rec['Transaction Type Code'] = substr($line, 191,1);
      $rec['Product Start Date'] = substr($line, 192,8);
      $rec['Product End Date'] = substr($line, 200,8);
      $rec['Purchase Order Nbr '] = substr($line, 208,16);
      $rec['Unit Rate Incl GST'] = sprintf("%0.7f",substr($line, 224,16)/10000000);
      $rec['Summary Gross Amount Excl GST'] = sprintf("%0.4f",substr($line, 240,14)/10000);
      $rec['Summary Net Amount Excl GST'] = sprintf("%0.4f",substr($line, 254,14)/10000);
      $rec['GST Amount'] = sprintf("%0.4f",substr($line, 268,14)/10000);
      $rec['Summary Price amount Incl GST'] = sprintf("%0.4f",substr($line, 282,14)/10000);
      $rec['Service Nbr Label'] = substr($line, 296,30);
      $rec['FID Txt Desc'] = substr($line, 326,90);
      $rec['Instalment Payment Nbr'] = substr($line, 416,10);
      $rec['Instalment Payment Total'] = substr($line, 426,10);
      $rec['Product Billing Identifier'] = substr($line, 436,8);
      $rec['Billing Element Code'] = substr($line, 444,8);
      $rec['Filler'] = substr($line, 452,306);

		} else if ($rec['Section Type'] == 'OO') {

			$rec['record type description'] = 'Ons and Offs Record';
      $rec['Ons and Offs Record Type'] = substr($line, 40,1);
      $rec['GIRN'] = substr($line, 41,7);
      $rec['Transaction Date'] = substr($line, 48,8);
      $rec['Full National Number '] = substr($line, 56,29);
      $rec['Purchase Order Nbr'] = substr($line, 85,16);
      $rec['Invc COSVC Desc'] = substr($line, 101,30);
      $rec['Service Nbr Label'] = substr($line, 131,30);
      $rec['Service Locn 1'] = substr($line, 161,30);
      $rec['Service Locn 2'] = substr($line, 191,30);
      $rec['Service Locn 3'] = substr($line, 221,30);
      $rec['Service Locn 4'] = substr($line, 251,30);
      $rec['Filler'] = substr($line, 281,477);

		} else if ($rec['Section Type'] == 'PB') {

			$rec['record type description'] = 'Previous Bill Details';
      $rec['Orig Doc Ref Nbr'] = substr($line, 40,10);
      $rec['Bill Pull Date'] = substr($line, 50,8);
      $rec['Pay Date'] = substr($line, 58,8);
      $rec['Bilg Tran Desc'] = substr($line, 66,50);
      $rec['Transaction Amount Incl GST'] = substr($line, 116,14);
      $rec['Filler'] = substr($line, 130,628);
      
		} else if ($rec['Section Type'] == 'PY') {

			$rec['record type description'] = 'Payment Record';
      $rec['Transaction Date'] = substr($line, 40,8);
      $rec['Payment Desc'] = substr($line, 48,50);
      $rec['Orig Doc Ref Nbr'] = substr($line, 98,10);
      $rec['Payment Amount Incl GST'] = substr($line, 108,14);
      $rec['Filler'] = substr($line, 122,636);
      
		} else if ($rec['Section Type'] == 'RC') {

			$rec['record type description'] = 'Re rated Charges Record';
      $rec['Re rated Charges Record Type'] = substr($line, 40,1);
      $rec['GIRN'] = substr($line, 41,7);
      $rec['Plan ID'] = substr($line, 48,8);
      $rec['Pricing Subplan Number'] = substr($line, 56,3);
      $rec['Pricing Plan Name'] = substr($line, 59,50);
      $rec['Pricing Subplan Name'] = substr($line, 109,30);
      $rec['Start Date'] = substr($line, 139,8);
      $rec['End Date'] = substr($line, 147,8);
      $rec['Generic Duration'] = substr($line, 155,11);
      $rec['Unit Rate Incl GST'] = sprintf("%0.4f", substr($line, 166,14)/10000);
      $rec['Re rated Gross Amount Excl GST'] = sprintf("%0.4f", substr($line, 180,14)/10000);
      $rec['Re rated Net Amount Excl GST'] = sprintf("%0.4f", substr($line, 194,14)/10000);
      $rec['GST Amount '] = sprintf("%0.4f", substr($line, 208,14)/10000);
      $rec['Re rated Price amount Incl GST'] = sprintf("%0.4f", substr($line, 222,14)/10000);
      $rec['Transaction count'] = substr($line, 236,10);
      $rec['Filler'] = substr($line, 246,512);

		} else if ($rec['Section Type'] == 'RM') {

			$rec['record type description'] = 'Remit Record';
      $rec['Remit Record Type'] = substr($line, 40,1);
      $rec['Total Amt Payable'] = substr($line, 41,14);
      $rec['Payment Due Date'] = substr($line, 55,8);
      $rec['Payment explanation'] = substr($line, 63,60);
      $rec['Customer name'] = substr($line, 123,30);
      $rec['Cust Address Line one'] = substr($line, 153,30);
      $rec['Cust Address Line two'] = substr($line, 183,32);
      $rec['Cust Address Line three'] = substr($line, 215,32);
      $rec['Remit Address Line One'] = substr($line, 247,30);
      $rec['Remit Address Line Two'] = substr($line, 277,30);
      $rec['Remit City name'] = substr($line, 307,23);
      $rec['Remit Postal Area Cd'] = substr($line, 330,1);
      $rec['Remit Postal Area Div Cd'] = substr($line, 331,3);
      $rec['Filler'] = substr($line, 334,424);
           
		} else if ($rec['Section Type'] == 'SE') {

			$rec['record type description'] = 'Service & Equipment Record';
      $rec['GIRN'] = substr($line, 40,7);
      $rec['Invc COSVC Desc'] = substr($line, 47,30);
      $rec['Full National Number '] = substr($line, 77,29);
      $rec['Generic Qty'] = substr($line, 106,10);
      $rec['Billing Trans Desc'] = substr($line, 116,50);
      $rec['Trans Type Desc'] = substr($line, 166,25);
      $rec['Transaction Type Code'] = substr($line, 191,1);
      $rec['Start Date'] = substr($line, 192,8);
      $rec['End Date'] = substr($line, 200,8);
      $rec['Summary Gross Amount Excl GST'] = sprintf("%0.4f", substr($line, 208,14)/10000);
      $rec['Summary Net Amount Excl GST'] = sprintf("%0.4f", substr($line, 222,14)/10000);
      $rec['GST Amt'] = sprintf("%0.4f", substr($line, 236,14)/10000);
      $rec['Summary Price amount Incl GST'] = sprintf("%0.4f", substr($line, 250,14)/10000);
      $rec['Service Locn 1'] = substr($line, 264,30);
      $rec['Service Locn 2'] = substr($line, 294,30);
      $rec['Service Locn 3'] = substr($line, 324,30);
      $rec['Service Locn 4'] = substr($line, 354,30);
      $rec['Purchase Order Nbr'] = substr($line, 384,16);
      $rec['Unit Rate Incl GST'] = substr($line, 400,16);
      $rec['Product Billing ID'] = substr($line, 416,8);
      $rec['Billing Element Code'] = substr($line, 424,8);
      $rec['Service Type Desc'] = substr($line, 432,30);
      $rec['Service Nbr label'] = substr($line, 462,30);
      $rec['Data Service Type'] = substr($line, 492,4);
      $rec['Bandwidth'] = substr($line, 496,10);
      $rec['Charge Zone'] = substr($line, 506,25);
      $rec['A end Service Nbr'] = substr($line, 531,19);
      
      if ($rec['Data Service Type'] == 'XDSL') {
      	
      	$rec['A end dlci '] = substr($line, 550,4);
      	$rec['A end cir '] = substr($line, 554,7);
      } else if ($rec['Data Service Type'] == 'AATM') {
      	
      	$rec['A end vpi '] = substr($line, 550,4);
      	$rec['A end vci '] = substr($line, 554,5);

      } else if ($rec['Data Service Type'] == 'AATM') {
      	
      	$rec['A end vpi '] = substr($line, 550,4);
      	$rec['A end vci '] = substr($line, 554,5);
      	
      }

      $rec['B end Service Nbr'] = substr($line, 561,19);

      if ($rec['Data Service Type'] == 'XDSL' || $rec['Data Service Type'] == 'FRLY') {
      	
      	$rec['B end dlci '] = substr($line, 580,4);
      	$rec['B end cir '] = substr($line, 584,7);
      } else if ($rec['Data Service Type'] == 'AATM') {
      	
      	$rec['B end vpi '] = substr($line, 580,4);
      	$rec['B end vci '] = substr($line, 580,5);
      }

      $rec['Line Speed'] = substr($line, 591,10);
      $rec['Filler'] = substr($line, 601,157);
      
      			
		} else if ($rec['Section Type'] == 'SS') {

			// This is incomplete towards end due to multi use.
			
			$rec['record type description'] = 'Service Summary Details';
      $rec['Service Summary Record Type'] = substr($line, 40,1);
      $rec['GIRN'] = substr($line, 41,7);

      $rec['Invc COSVC Desc'] = substr($line, 48,30);
      $rec['Serv Charge Item Grp'] = substr($line, 78,20);
      $rec['Service Type Desc'] = substr($line, 98,30);
      $rec['Call Type Desc'] = substr($line, 128,45);
      $rec['Billing Service Id'] = substr($line, 173,29);
      $rec['Service Nbr Label '] = substr($line, 202,30);
      $rec['Start Date'] = substr($line, 232,8);
      $rec['End Date'] = substr($line, 240,8);
      $rec['Extract to Date'] = substr($line, 248,8);
      $rec['Generic Qty'] = substr($line, 256,10);

      $rec['Unit Desc'] = substr($line, 266,15);
      $rec['Unit Rate Incl GST'] = sprintf("%0.4f", substr($line, 281,14)/10000);

      $rec['Rate Period Desc'] = substr($line, 295,10);
      $rec['Generic Gross Amount Excl GST'] = sprintf("%0.4f", substr($line, 305,14)/10000);

      $rec['Generic Net Amount Excl GST '] = sprintf("%0.4f", substr($line, 319,14)/10000);

      $rec['GST Amount '] = sprintf("%0.4f", substr($line, 333,14)/10000);

      $rec['Generic Price amount Incl GST'] = sprintf("%0.4f", substr($line, 347,14)/10000);

      $rec['PON'] = substr($line, 361,16);
      $rec['Service Location 1'] = substr($line, 377,30);
      $rec['Service Location 2'] = substr($line, 407,30);
      $rec['Service Location 3'] = substr($line, 437,30);
      $rec['Service Location 4'] = substr($line, 467,30);
      $rec['Transaction Type Desc'] = substr($line, 497,30);
      $rec['FID Txt One'] = substr($line, 527,30);
      $rec['FID Txt Two'] = substr($line, 557,30);
      $rec['FID Txt Three'] = substr($line, 587,30);
      $rec['Service Nbr label'] = substr($line, 617,30);
      $rec['Data Service Type'] = substr($line, 647,4);
      $rec['Bandwidth'] = substr($line, 651,10);
      $rec['Charge Zone'] = substr($line, 661,25);
      $rec['A end Service Nbr'] = substr($line, 686,19);
      $rec['A end dlci & cir (redefines)'] = substr($line, 705,11);
      $rec['A end vpi & vci (redefines)'] = substr($line, 705,11);
      $rec['B end Service Nbr'] = substr($line, 716,19);
      $rec['B end dlci & cir (redefines)'] = substr($line, 735,11);
      $rec['B end vpi & vci (redefines)'] = substr($line, 735,11);
      $rec['Line Speed'] = substr($line, 746,10);
      $rec['Filler'] = substr($line, 756,2);
      
      	
		} else if ($rec['Section Type'] == 'UC') {

			$rec['record type description'] = 'Usage Charges';
      $rec['GIRN'] = substr($line, 40,7);
      $rec['Serv Charge Item Grp'] = substr($line, 47,20);
      $rec['Service Type Desc'] = substr($line, 67,30);
      $rec['Call Type Desc'] = substr($line, 97,45);
      $rec['Extract to Date'] = substr($line, 142,8);
      $rec['Transaction Count'] = substr($line, 150,10);
      $rec['Unit Description'] = substr($line, 160,15);
      $rec['Unit Rate Incl GST'] = sprintf("%0.4f", substr($line, 175,14)/10000);
      $rec['Rate Description'] = substr($line, 189,10);
      $rec['Summary Gross Amount Excl GST '] = sprintf("%0.4f", substr($line, 199,14)/10000);
      $rec['Summary Net Amount Excl GST '] = sprintf("%0.4f", substr($line, 213,14)/10000);
      $rec['GST Amt '] = sprintf("%0.4f", substr($line, 227,14)/10000);
      $rec['Summary Price amount Incl GST'] = sprintf("%0.4f", substr($line, 241,14)/10000);
      $rec['Filler'] = substr($line, 255,503);
      			
		} else {
			
			$rec['record type description'] = 'Unknown / invalid record type';
		}

		
	}
	

	echo "\n";
	
	while ($cel = each($rec)) {
		
		printf("%40s   %s\n", $cel['key'], $cel['value']);
	}
	
	echo "\n";
	echo "===========================================================\n";
	
	$linecount++;
	
		
		
}
fclose($fh);

