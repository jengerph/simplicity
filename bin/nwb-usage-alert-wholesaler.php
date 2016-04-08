#!/usr/bin/php
<?php

include "/var/www/simplicity/htdocs/setup.inc";
include_once "services.class";
include_once "service_attributes.class";
include_once "wholesalers.class";
include_once "customers.class";
include_once "plans.class";
include_once "plan_attributes.class";
include_once "accounting.class";
require_once 'lib/swift_required.php';

// Create the Transport
$transport = Swift_SmtpTransport::newInstance('smtp1.xi.com.au', 25);

$w = new wholesalers();
$wholesalers = $w->get_wholesalers();

while ($wholesaler = each($wholesalers)) {

	
	$w->wholesaler_id = $wholesaler['value']['wholesaler_id'];
	$w->load();

	echo $w->company_name . "\n";	
	
	$all_services = array();
	$warn_services = array();
	$shaped_services = array();
	
	$row = array();
	$row[] = 'Customer';
	$row[] = 'Service ID';
	$row[] = 'Identifier';
	$row[] = 'Tag';
	$row[] = 'Plan';
	$row[] = 'Usage Start Date';
	$row[] = 'Usage Finish Date';
	$row[] = 'Plan Allowance';
	$row[] = 'Usage';
	$row[] = 'Remaining';
	
	$all_services[] = $row;
	$warn_services[] = $row;
	$shaped_services[] = $row;
	
	
	
	
	$c = new customers();
	$customers = $c->get_customers($w->wholesaler_id);
	
	while ($customer = each($customers)) {
		
		$c->customer_id = $customer['value']['customer_id'];
		$c->load();
		
		echo ' - ' . $c->company_name . "\n";
		
		$s = new services();
		$s->customer_id = $c->customer_id;
		$services = $s->get_all();
		
		while ($service = each($services)) {
		
			$s->service_id = $service['value']['service_id'];
			$s->load();
			
			if (($s->type_id == 1 || $s->type_id == 2) && $s->state != 'inactive') {
				
					// DSL or NBN Circuit
					
					//echo "    - " . $s->tag . "\n";
					
					$plan = new plans();
          $plan->plan_id = $s->wholesale_plan_id;
          $plan->load();
          
          $plan_attr = new plan_attributes();
          $plan_attr->plan_id = $plan->plan_id;
          $plan_attr->param = "monthly_data_allowance";
          $plan_attr->get_latest();

          $count_uploads = new plan_attributes();
          $count_uploads->plan_id = $plan->plan_id;
          $count_uploads->param = "count_uploads";
          $count_uploads->get_latest();
          
        	$st = new service_attributes();
        	$st->service_id = $s->service_id;
        	$st->param = 'username';
        	$st->get_attribute();
        	
        	$username = $st->value;

        	$st = new service_attributes();
        	$st->service_id = $s->service_id;
        	$st->param = 'realms';
        	$st->get_attribute();
        	
        	$realms = $st->value;
        	

        	// Determine dates
        	
        	
        	$datebits = $misc->date_bits($s->start_date);
        	$start_date = '';
        	$finish_date = '';
        	
        	if (date('d') >= $datebits[2]) {
        		$start_date = date("Y-m-" . $datebits[2]);

        		$year = date('Y');
        		$month = date('m');
        		
        		$month++;
        		
        		if ($month == 13) {
        			$month = 1;
        			$year++;
        		}
        		
        		$day = $datebits[2];
        		$day--;
        		
        		if ($day == 0) {
        			
        			// Hit start of month
        			
        			$month--;

	        		if ($month ==0) {
  	      			$month = 12;
    	    			$year--;
      	  		}
      	  		
      	  		$day = date('t', $misc->date_ts($year . '-' . $month . '-01 00:00:00'));
      	  	}
        			
        		$finish_date = $year . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $day);
        		
        	} else {

        		$year = date('Y');
        		$month = date('m');
        		
        		$month--;
        		
        		if ($month == 0) {
        			$month = 12;
        			$year--;
        		}
        		$start_date = $year . '-' . sprintf("%02d", $month) . '-' . $datebits[2];
        		
        		$year = date('Y');
        		$month = date('m');
        		$day = $datebits[2];
        		        		
        		$day--;
        		
            if ($day == 0) {
        			
        			// Hit start of month
        			
        			$month--;

	        		if ($month ==0) {
  	      			$month = 12;
    	    			$year--;
      	  		}
      	  		
      	  		$day = date('t', $misc->date_ts($year . '-' . $month . '-01 00:00:00'));
      	  	}
      	  	
        		$finish_date = $year . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $day);
	
        		
        		
        	}
        	

					if ($plan_attr->value > 0) {
            $get_month = new accounting();
            $get_month->username = $username . '@' . $realms;
            $get_month->start_date = $start_date;
            $get_month->end_date = $finish_date;
            $get_month_array = $get_month->get_user_month();
            
            $total_input = 0;
            $total_output = 0;
            for ($i=0; $i < count($get_month_array); $i++) {
                    $total_input = $total_input + (int)$get_month_array[$i]["input"];
                    $total_output = $total_output + (int)$get_month_array[$i]["output"];
            
                    $temp_input = number_format($get_month_array[$i]["input"]/pow(1024,floor(3)), 2, '.', '') . " GB";
                    if ( $temp_input < 1  ) {
                            $temp_input = number_format($get_month_array[$i]["input"]/pow(1024,floor(2)), 2, '.', '') . " MB";
                    }
                    $temp_output = number_format($get_month_array[$i]["output"]/pow(1024,floor(3)), 2, '.', '') . " GB";
                    if ( $temp_output < 1  ) {
                            $temp_output = number_format($get_month_array[$i]["output"]/pow(1024,floor(2)), 2, '.', '') . " MB";
                    }
            
            }
            
            $usage = $total_output;
            
            if ($count_uploads->value == 1) {
            	$usage += $total_input;
            }
            
            $usage = number_format($usage / pow(1024,floor(2))/1024,2,'.','');
            
          	//echo "    - " . $s->service_id . ' - ' . $plan->description . ' - ' . $username . '@' . $realms . ' - ' . $start_date . ' - ' . $finish_date . '-' . $usage . "\n";
          	
        		$row = array();
          	$row[] = $c->company_name;
          	$row[] = $s->service_id;
          	$row[] = $s->identifier;
          	$row[] = $username . '@' . $realms;
          	$row[] = $plan->description;
          	$row[] = $start_date;
          	$row[] = $finish_date;
          	$row[] = $plan_attr->value;
          	$row[] = $usage;
          	$row[] = $plan_attr->value - $usage;
          	
          	$all_services[] = $row;
          	
          	if ($usage > ($plan_attr->value * 0.8)) {
          		
          		// Warning reached
          		
	          	if ($usage > $plan_attr->value) {
	          		
	          		// Shapped
	          		
	          		$shaped_services[] = $row;
	          	} else {
	          		$warn_services[] = $row;
	          	}
	          }
					}  
        	


			}	
		}
	}
	
	if (sizeof($all_services) > 1) {
  	// Send email
  	$mailer = Swift_Mailer::newInstance($transport);
      
    $message = Swift_Message::newInstance('DSL & NBN Usage Information for ' . $w->company_name);
    $message->setFrom(array('support@xi.com.au' => 'X Integration'));
    
    $message->setBody("Please find attached usage information for your DSL and NBN services.", 'text/plain');
    
    $output = fopen("/tmp/services.csv",'w') or die("Can't open /tmp/services.csv");
    while ($cel = each($all_services)) {
      fputcsv($output, $cel['value']);
  	}
  	fclose($output) or die("Can't close /tmp/services.csv");
  	
  	$message->attach(Swift_Attachment::fromPath('/tmp/services.csv'));	
  	
  
    $output = fopen("/tmp/shaped.csv",'w') or die("Can't open /tmp/shaped.csv");
    while ($cel = each($shaped_services)) {
      fputcsv($output, $cel['value']);
  	}
  	fclose($output) or die("Can't close /tmp/shaped.csv");
  	
  	$message->attach(Swift_Attachment::fromPath('/tmp/shaped.csv'));	
  	
  
    $output = fopen("/tmp/warn.csv",'w') or die("Can't open /tmp/warn.csv");
    while ($cel = each($warn_services)) {
      fputcsv($output, $cel['value']);
  	}
  	fclose($output) or die("Can't close /tmp/warn.csv");
  	
  	$message->attach(Swift_Attachment::fromPath('/tmp/warn.csv'));	
  	
  	
  	$message->setTo(array($w->email));
  	$message->setCC(array('alerts@xi.com.au' => 'X Integration Alerts'));
  
  	$result = $mailer->send($message);
  	unlink('/tmp/services.csv');
  	unlink('/tmp/warn.csv');
  	unlink('/tmp/shaped.csv');

	}
}
