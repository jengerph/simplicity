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


$s = new services();

$query = "select service_id, start_date, (select service_attributes.value from service_attributes WHERE service_attributes.service_id = services.service_id and service_attributes.param = 'username') as username, (select service_attributes.value from service_attributes WHERE service_attributes.service_id = services.service_id and service_attributes.param = 'realms') as realms, (select service_attributes.value from service_attributes WHERE service_attributes.service_id = services.service_id and service_attributes.param = 'shape_status') as shape_status  , (select plan_attributes.value from plan_attributes WHERE plan_attributes.plan_id = services.wholesale_plan_id and plan_attributes.param = 'monthly_data_allowance') as monthly_data_allowance , (select plan_attributes.value from plan_attributes WHERE plan_attributes.plan_id = services.wholesale_plan_id and plan_attributes.param = 'count_uploads') as count_uploads from services where type_id = 1 or type_id = 2 and services.state != 'inactive' having monthly_data_allowance is not null and monthly_data_allowance != 0";


$result = $s->db->execute_query($query);

while ( $row = $s->db->fetch_row_array($result)) {


	// Determine dates
	
	
	$datebits = $misc->date_bits($row['start_date']);
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
	

  $get_month = new accounting();
  $get_month->username = $row['username'] . '@' . $row['realms'];
  $get_month->start_date = $start_date;
  $get_month->end_date = $finish_date;
  $get_month_array = $get_month->get_user_month();
  
  $total_input = 0;
  $total_output = 0;
  for ($i=0; $i < count($get_month_array); $i++) {
          $total_input = $total_input + (int)$get_month_array[$i]["input"];
          $total_output = $total_output + (int)$get_month_array[$i]["output"];            
  }
  
  $usage = $total_output;
  
  if ($row['count_uploads'] == 1) {
  	$usage += $total_input;
  }
  
  $usage = number_format($usage / pow(1024,floor(2))/1024,2,'.','');
  

	$st = new service_attributes();
  $st->service_id = $row['service_id'];

 	if ($usage > $row['monthly_data_allowance']) {
 		
  	// Shapped
  	
  	if ($row['shape_status'] == 0 || strlen($row['shape_status']) == 0) {
  		
  		// Do they have extra data purchased?
  		$st->param = 'shape_extra_data';
  		$st->get_attribute();
  		
  		$shaped = 1;
  		
  		if ($st->value != 0 ) {
  			
  			$extra_data = $st->value;
  			
  			$st->param = 'shape_extra_data_dt';
  			$st->get_attribute();

  			
  			// They potentially could be okay
        $get_month->start_date = $st->value;
        $get_month->end_date = $finish_date;
        $get_month_array = $get_month->get_user_month();
        
        $total_input = 0;
        $total_output = 0;
        for ($i=0; $i < count($get_month_array); $i++) {
                $total_input = $total_input + (int)$get_month_array[$i]["input"];
                $total_output = $total_output + (int)$get_month_array[$i]["output"];            
        }
        
        $usage = $total_output;
        
        if ($row['count_uploads'] == 1) {
        	$usage += $total_input;
        }
        
        $usage = number_format($usage / pow(1024,floor(2))/1024,2,'.','');
  			
  			//echo "Matt: $usage < $extra_data\n";
  			//exit();
  			
  			if ($usage < $extra_data) {
  				
  				// Not reshaped yet
  				$shaped = 0;
  			}
  		}
  			
  		if ($shaped == 1) {
  			// Status change
      	$st->param = 'shape_status';
      	$st->value = 1;
      	if ($st->exist()) {
      		$st->save();
      	} else {
        	$st->create();
      	}

        $st->param = 'shape_extra_data';
        $st->value = 0;
        if ($st->exist()) {
        	$st->save();
        } else {
          $st->create();
        }
  
        $st->param = 'shape_extra_data_dt';
        $st->value = '';
        if ($st->exist()) {
        	$st->save();
        } else {
          $st->create();
        }
        
        $query2 = "INSERT INTO radius.radreply (username, attribute, op, value) values (" . $s->db->quote($row['username'] . '@' . $row['realms']) . ",'Cisco-Avpair',':=','ip:sub-qos-policy-out=shape-72k')";
        $result2 = $s->db->execute_query($query2);
        
        system("/var/www/simplicity/bin/shape.pl " . $row['username'] . '@' . $row['realms'] . " > /dev/null");

			}
  	}
  	
  	//echo $row['username'] . '@' . $row['realms'] . " - Shaped\n";
	} else {
		
		// Unshaped

  	if ($row['shape_status'] == 1 || strlen($row['shape_status']) == 0) {
  		
  		// Status change
      $st->param = 'shape_status';
      $st->value = 0;
      if ($st->exist()) {
      	$st->save();
      } else {
        $st->create();
      }

      $st->param = 'shape_extra_data';
      $st->value = 0;
      if ($st->exist()) {
      	$st->save();
      } else {
        $st->create();
      }

      $st->param = 'shape_extra_data_dt';
      $st->value = '';
      if ($st->exist()) {
      	$st->save();
      } else {
        $st->create();
      }

      $query2 = "DELETE FROM radius.radreply WHERE username  = " . $s->db->quote($row['username'] . '@' . $row['realms']) . " AND attribute='Cisco-Avpair'";
      $result2 = $s->db->execute_query($query2);

      system("/var/www/simplicity/bin/unshape.pl " . $row['username'] . '@' . $row['realms'] . " > /dev/null");
  		
  	}

  	//echo $row['username'] . '@' . $row['realms'] . " - Unshaped\n";
		
	} 
        	
}
