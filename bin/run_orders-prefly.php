#!/usr/bin/php
<?php

include "/var/www/simplicity/htdocs/setup.inc";
include_once("class.phpmailer.php");
  
include_once "services.class";
include_once "service_types.class";
include_once "service_attributes.class";
include_once "plans.class";
include_once "plan_attributes.class";
include_once "orders.class";
include_once "order_attributes.class";
include_once "orders_states.class";
include_once "order_comments.class";
include_once "validate.class";
include_once "realms.class";
include_once "customers.class";
include_once "radius.class";
include_once "authorised_rep.class";

// Do we process new orders?
$CONTROL_PROCESS_NEW = 0;

$orders = new orders();

$orders_list = $orders->all_open();

$config = new config();

$client = new SoapClient($config->frontier_dir . "/wsdl/FrontierLink.wsdl", array('local_cert'     => $config->frontier_dir . "/cert/frontierlink-cert.xi.com.au.cer",'trace'=>1));


while ($cel = each($orders_list)) {
	
	$orders->order_id = $cel['value']['order_id'];
	$orders->load();
	
	echo $orders->status . "\n";
	
	$services = new services();
	$services->service_id = $orders->service_id;
	$services->load();
	
	echo $services->identifier . "\n";
	
	if ($services->type_id == 1 || $services->type_id == 2) {

  	$customer = new customers();
  	$customer->customer_id = $services->customer_id;
  	$customer->load();
  	
  	$wholesaler = new wholesalers();
  	$wholesaler->wholesaler_id = $customer->wholesaler_id;
  	$wholesaler->load();
  	
  	
		
		// ADSL or NBN Order
  	if ($orders->status == 'pending') {
  		
  		$ts = $misc->date_ts($orders->start);
  		
  		if ($ts < time() - 300) {
  			
  			if ($orders->action == 'new' && $CONTROL_PROCESS_NEW == 1) { 
  			
    			// Been 5 minutes, accept order
    
					$do_accept = 1;
        	
      		if ($services->type_id == 1 || $services->type_id == 2 ) {
      		
        		// DSL & NBN
        		
            $contract_length = new plan_attributes();
            $contract_length->plan_id = $services->wholesale_plan_id;
            $contract_length->param = "contract_length";
            $contract_length->get_latest();
        		
        		// Need to lodge with frontier
            $params = array();
            $params['orderContact'] = array();
            //$params['orderContact']['individual']['salutation'] = 'Mr';
            //$params['orderContact']['individual']['firstName'] = 'Matthew';
            //$params['orderContact']['individual']['lastName'] = 'Enger';
            $params['orderContact']['name'] = 'XIntegration Provisioning';
            $params['orderContact']['phone'] = '1300789299';
            $params['orderContact']['mobile'] = '0406532792';
            $params['orderContact']['email'] = 'alerts@xi.com.au';
            $params['installationContact'] = array();
            $params['installationContact']['individual']['salutation'] = 'Mr';
            $params['installationContact']['individual']['firstName'] = $customer->first_name;
            $params['installationContact']['individual']['lastName'] = $customer->last_name;
            $params['installationContact']['phone'] = $customer->phone;
            //$params['installContact']['mobile'] = $customer->mobile;
            $params['installationContact']['email'] = $customer->email;
            $params['customerReference'] = $services->service_id;
                  		
            $params['serviceDetailsList'] = array();
            $params['serviceDetailsList'][0]['nationalWholesaleBroadbandService'] = array();
            $params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['accountNumber'] = '2000027399';
            $params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['qualificationID'] = get_attribute( $orders->order_id, "order_qualificationID" );
            
            if ($services->type_id == 1) {
            	$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['endCSN'] = get_attribute( $orders->order_id, "order_service_number" );
            } else {
            	$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['nbnLocationID'] = get_attribute( $orders->order_id, "order_nbnLocationID" );
            	$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['nbnConnectionType'] = 2;
            	$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['batteryBackupService'] = "FALSE";
            }
            $params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['accessMethod'] = get_attribute( $orders->order_id, "order_accessMethod" );
            $params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['accessType'] = get_attribute( $orders->order_id, "order_accessType" );
            $params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['serviceSpeed'] = get_attribute( $orders->order_id, "order_serviceSpeed" );
            $params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['networkConnectionServiceId'] = '8104234';
            $params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['contractTerm'] = $contract_length->value;
            //$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['nbnConnectionType'] = '';
            
            if ( get_attribute( $orders->order_id, "order_churn" ) == 'yes') {	
            	$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['dslTransfer'] = array();
            	$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['dslTransfer']['losingServiceProvider'] = get_attribute( $orders->order_id, "order_churn_provider" );
            	
            	$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['dslTransfer']['dslTransferAuthorityDate'] = date('Y-m-d', $misc->date_ts($orders->start . ' 06:00:00'));
            	
            }
            $params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['installDate'] = date('d/m/Y', time() + 86400*5);
            //$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['qualificationAddressOverride'] = '';
            //$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['batteryBackupService'] = '';
            
            print_r($params);
            
						try{
              $response = $client->NewService($params);
					  }
					  catch (SoapFault $exception) {

							//echo $exception;

			        $comment = new order_comments();
      			  $comment->order_id = $orders->order_id;
        			$comment->username = 'system';
        			$comment->comment_visibility = "internal";
        			$comment->comment = $exception;
        			$comment->create();
        			
        			$orders->status = 'on hold';
        			$orders->save();

        			soapDebug($client);

            	$orders_states = new orders_states();
            	$orders_states->order_id = $orders->order_id;
            	$orders_states->state_name = 'on hold';
            	$orders_states->create();
        			
        			$do_accept = 0;
  
        		} 
        		
        		if ($do_accept == 1) {

  
  					  //var_dump($response);
  					  
  					  // DO we have order ids
  					  $sales_order_id = $response->salesOrder->salesOrderID;
  					  $product_order_id = $response->salesOrder->productOrders->productOrderID;
  					  $standard_access_service_id = 0;
  					  $nwb_link_service_id = 0;
  					  while ($cel99 = each($response->salesOrder->productOrders->serviceOrders)) {
  					  	if ($cel99['value']->serviceOrderType == 'Standard Access') {
  					  		$standard_access_service_id = $cel99['value']->serviceOrderID;
  					  	} else if ($cel99['value']->serviceOrderType == 'NWB Link') {
  					  		$nwb_link_service_id = $cel99['value']->serviceOrderID;
  					  	}
  					  }
  					  
  					  $text = "BSB Order placed to frontier:" . "\r\n";
  					  $text .= "Sales Order ID:" . $sales_order_id . "\r\n";
  					  $text .= "Product Order ID:" . $product_order_id . "\r\n";
  					  $text .= "Standard Access Service ID:" . $standard_access_service_id . "\r\n";
  					  $text .= "NWB Link Service ID:" . $nwb_link_service_id . "\r\n";
  
  		        $comment = new order_comments();
      			  $comment->order_id = $orders->order_id;
        			$comment->username = 'system';
        			$comment->comment_visibility = "internal";
        			$comment->comment = $text;
        			$comment->create();

              $order_attributes = new order_attributes();
              $order_attributes->order_id = $orders->order_id;
              $order_attributes->param = "aapt_sales_order_id";
              $order_attributes->value = $sales_order_id;
              $order_attributes->create();

              $order_attributes = new order_attributes();
              $order_attributes->order_id = $orders->order_id;
              $order_attributes->param = "aapt_product_order_id";
              $order_attributes->value = $product_order_id;
              $order_attributes->create();

              $order_attributes = new order_attributes();
              $order_attributes->order_id = $orders->order_id;
              $order_attributes->param = "aapt_standard_access_service_id";
              $order_attributes->value = $standard_access_service_id;
              $order_attributes->create();

              $order_attributes = new order_attributes();
              $order_attributes->order_id = $orders->order_id;
              $order_attributes->param = "aapt_nwb_link_service_id";
              $order_attributes->value = $nwb_link_service_id;
              $order_attributes->create();

					    $service_attr = new service_attributes();
              $service_attr->service_id = $services->service_id;
              $service_attr->param = 'aapt_service_id';
              $service_attr->value = $standard_access_service_id;
              $service_attr->create();

        			
        			//echo $text;
					  }
					}
					
					if ($do_accept == 1) {
          	$orders_states = new orders_states();
          	$orders_states->order_id = $orders->order_id;
          	$orders_states->state_name = 'accepted';
          	$orders_states->create();

      			$orders->status = 'accepted';
      			$orders->save();
          	
          	
      
            // Send order receipt:
            $mail = new PHPMailer();
      
            $mail->From     = "service.delivery@xi.com.au";
            $mail->FromName = "X Integration Pty Ltd";
            $mail->Subject  = "Order Acceptance Notification";
            $mail->Host     = "127.0.0.1";
            $mail->Mailer   = "smtp";
      
            $text_body  = "Dear " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . ",\r\n";
            $text_body .= "\r\n";
            $text_body .= "This email is to confirm that X Integration has today accepted your recent request for the following order. \r\n";
            $text_body .= "\r\n";
            $text_body .= "The date we are intending to complete the modification to your service is " . date("d M Y ", time() + (86400*21)) . ".\r\n";
            $text_body .= "\r\n";
            $text_body .= "Company Name: " . ucwords($customer->company_name) . "\r\n\r\n";
            $text_body .= "Customer Name: " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . "\r\n\r\n";
            $text_body .= "Username: " . get_attribute( $orders->order_id, "order_username" ) . "@" . get_attribute( $orders->order_id, "order_realms" ) . "\r\n\r\n";
            $text_body .= "Password: " . get_attribute( $orders->order_id, "order_password" ) . "\r\n\r\n";
            $text_body .= "Order Reference: " . $orders->order_id . "\r\n\r\n";
            $text_body .= "Below is a list of item(s) on this order. " . "\r\n\r\n";
      
            $plan_title = new plans();
            $plan_title->plan_id = $services->retail_plan_id;
            $plan_title->load();
      
            $text_body .= "Type of Order: " . ucwords($orders->action) . " - ADSL or NBN Service\r\n\r\n";
            $text_body .= "Service Components:\r\n\r\n";
            $text_body .= "Service ID: " . $services->service_id . "\r\n";
            $text_body .= "Transaction Type: " . ucwords($orders->action) . "\r\n";
            $text_body .= "Customer Account Number: " . ucwords($customer->customer_id) . "\r\n";
      
            $contract_length = new plan_attributes();
            $contract_length->plan_id = $plan_title->plan_id;
            $contract_length->param = "contract_length";
            $contract_length->get_latest();
      
            $text_body .= "Contract Term (Months): " . $contract_length->value . "\r\n\r\n";
      
            $text_body .= "Service Number: " . get_attribute( $orders->order_id, "order_service_number" ) . "\r\n";
            $text_body .= "Access Location: " . get_attribute( $orders->order_id, "order_address" ) . "\r\n";
            $text_body .= "Access Method: " . $plan_title->access_method . "\r\n";
         	
            $service_types = new service_types();
            $service_types->type_id = $plan_title->type_id;
            $service_types->load();
      
            $text_body .= "Access Technology: " . $service_types->description . "\r\n";
            $text_body .= "Access Speed: Up to " . $plan_title->speed . "\r\n\r\n";
            
            $text_body .= "Provisioning Target Up to 21 days from X Integration's acceptance of your order (excluding customer delays).\r\n\r\n";
            $text_body .= "The provisioning target shown above is based on the access type for the service. You can only order one service at a time. This ensures that the provision of one service is not held up pending the provision of other services on the order.\r\n\r\n";
            $text_body .= "Throughout the provisioning process, we will provide you with updates on key milestones by sending the following notifications for each service on your order: \r\n\r\n";
            $text_body .= "Service Completion Advice\r\n";
            $text_body .= "Confirmation that the provisioning of your service has been completed and billing has commenced. This will also provide you with the contact details for your Service and Provisioning Team.\r\n\r\n";
            $text_body .= "It is important to X Integration that you, our customer, are satisfied with the level of service provided.\r\n\r\n";
            $text_body .= "To view the latest update on your order please visit this link: https://simplicity.xi.com.au/base/manage/orders/edit/?order_id=" . $orders->order_id . ". or visit our online Simplicity portal by logging in via https://simplicity.xi.com.au and view all your current orders under the order tab.\r\n\r\n";
            $text_body .= "Alternatively you can contact X Integration Service and Provisioning Team using the contact details provided below.\r\n\r\n";
            $text_body .= "Kind Regards,\r\n";
            $text_body .= "X Integration Service and Provisioning Team\r\n\r\n";
    
           	//$mail->AddAddress($customer->email);
           	$mail->AddCC($wholesaler->email);
            $mail->AddBCC("alerts@xi.com.au");
      
    				$mail->Body    = $text_body;
    
            $comment = new order_comments();
            $comment->order_id = $orders->order_id;
            $comment->username = 'system';
            $comment->comment_visibility = "customer";
            $comment->comment = $text_body;
            $comment->create();
      
            $mail->Send();						
					}
      	}
  		}
  	} else if ($orders->status == 'accepted') {
  		
  		// Lets pull the order status from AAPT
  		$params = array();
			$params['productType']= 'National Wholesale Broadband';
			$params['productOrderID']= get_attribute( $orders->order_id, "aapt_product_order_id" );

			$do_check = 1;
			try{
        $response = $client->enquireService($params);
		  }
		  catch (SoapFault $exception) {

				//echo $exception;

        $comment = new order_comments();
			  $comment->order_id = $orders->order_id;
  			$comment->username = 'system';
  			$comment->comment_visibility = "internal";
  			$comment->comment = $exception;
  			$comment->create();
  			
  			$orders->status = 'on hold';
  			$orders->save();

  			soapDebug($client);

      	$orders_states = new orders_states();
      	$orders_states->order_id = $orders->order_id;
      	$orders_states->state_name = 'on hold';
      	$orders_states->create();
  			
  			$do_check = 0;

  		}
  		
  		if ($do_check == 1) {
    		// Determine the current service status
    		while ($cel99 = each($response->serviceOrderDetails)) {
    		
    			if ($cel99['value']->serviceOrderType == 'Standard Access') {
    			
    				// We are looking at the actual link provision as the NWB component is pointless to monitor
    				
    				// Loop through the activities
    				$current= '';
    				$current_end = '';
    				$current_start = '';
    				$prev='';
    				$prev_end = '';
    				$prev_start = '';
    				$first = 1;
    				
    				//print_r($cel99['value']);
    				while ($cel88 = each($cel99['value']->ProductOrderDetails->productOrderActivities)) {
    					
    					if (isset($cel88['value']->actualEndDate)) {
    						
    						// Defined
    						$prev = $current;
    						$prev_end = $current_end;
    						$prev_start = $current_start;
    						
    						$current = $cel88['value']->activityType;
    						$current_start = $cel88['value']->actualStartDate;
    						$current_end = $cel88['value']->actualEndDate;
    					} else {
    						
    						if ($first == 1) {
    							
    							$current = $cel88['value']->activityType;
    							$current_start = $cel88['value']->actualStartDate;
    							$current_end = '';
    							
    							$first = 0;
    						}
    					}
    					
    				}
  
  					if ($current == 'Order Provisioning') {
  						
  						// Awaiting provisioning
  						
  						$install_date = '';
  						// Determnine if we have an install date
  						while ($cel88 = each($cel99['value']->ProductOrderDetails->serviceOrderActivities)) {
  							
  							if ($cel88['value']->activityType == 'Access Service Delivery') {
  							
  								$install_date = $cel88['value']->scheduledStartDate;
  								
  							}
  						}
  						
  						if ($install_date != '') {
  							
  							if ($install_date != get_attribute( $orders->order_id, "aapt_install_date" )) {
  								
  								// We have a date of install

                  $order_attributes = new order_attributes();
                  $order_attributes->order_id = $orders->order_id;
                  $order_attributes->param = "aapt_install_date";
                  $order_attributes->value = $install_date;
                  if ($order_attributes->exist()) {
                  	$order_attributes->save();
                  } else {
                  	$order_attributes->create();
                  }
  								
          
                  // Send order receipt:
                  $mail = new PHPMailer();
            
                  $mail->From     = "service.delivery@xi.com.au";
                  $mail->FromName = "X Integration Pty Ltd";
                  $mail->Subject  = "Access Install Notification";
                  $mail->Host     = "127.0.0.1";
                  $mail->Mailer   = "smtp";
            
                  $text_body  = "Dear " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . ",\r\n";
                  $text_body .= "\r\n";
                  $text_body .= "Please be advised your order: " . $orders->order_id . " has the following targeted cutover date: " . $install_date . "\r\n";
                  $text_body .= "\r\n";
                  $text_body .= "For your reference, this email contains information about your service, plus service numbers and attributes for each service on the order.\r\n";
                  $text_body .= "\r\n";
                  $text_body .= "Company Name: " . ucwords($customer->company_name) . "\r\n\r\n";
                  $text_body .= "Customer Name: " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . "\r\n\r\n";
                  $text_body .= "Username: " . get_attribute( $orders->order_id, "order_username" ) . "@" . get_attribute( $orders->order_id, "order_realms" ) . "\r\n\r\n";
                  $text_body .= "Password: " . get_attribute( $orders->order_id, "order_password" ) . "\r\n\r\n";
                  $text_body .= "Order Reference: " . $orders->order_id . "\r\n\r\n";
                  $text_body .= "Below is a list of item(s) on this order. " . "\r\n\r\n";
            
                  $plan_title = new plans();
                  $plan_title->plan_id = $services->retail_plan_id;
                  $plan_title->load();
            
                  $text_body .= "Type of Order: " . ucwords($orders->action) . " - ADSL or NBN Service\r\n\r\n";
                  $text_body .= "Service Components:\r\n\r\n";
                  $text_body .= "Service ID: " . $services->service_id . "\r\n";
                  $text_body .= "Transaction Type: " . ucwords($orders->action) . "\r\n";
                  $text_body .= "Customer Account Number: " . ucwords($customer->customer_id) . "\r\n";
            
                  $contract_length = new plan_attributes();
                  $contract_length->plan_id = $plan_title->plan_id;
                  $contract_length->param = "contract_length";
                  $contract_length->get_latest();
            
                  $text_body .= "Contract Term (Months): " . $contract_length->value . "\r\n\r\n";
            
                  $text_body .= "Service Number: " . get_attribute( $orders->order_id, "order_service_number" ) . "\r\n";
                  $text_body .= "Access Location: " . get_attribute( $orders->order_id, "order_address" ) . "\r\n";
                  $text_body .= "Access Method: " . $plan_title->access_method . "\r\n";
               	
                  $service_types = new service_types();
                  $service_types->type_id = $plan_title->type_id;
                  $service_types->load();
            
                  $text_body .= "Access Technology: " . $service_types->description . "\r\n";
                  $text_body .= "Access Speed: Up to " . $plan_title->speed . "\r\n\r\n";
                  
                  $text_body .= "If you have any further queries, please don't hesitate to contact X Integration and quote the X Integration Provisioning Reference provided above.\r\n\r\n";
                  $text_body .= "Kind Regards,\r\n";
                  $text_body .= "X Integration Service and Provisioning Team\r\n\r\n";
          
           				//$mail->AddAddress($customer->email);
           				$mail->AddCC($wholesaler->email);
            			$mail->AddBCC("alerts@xi.com.au");

            
          				$mail->Body    = $text_body;
          
                  $comment = new order_comments();
                  $comment->order_id = $orders->order_id;
                  $comment->username = 'system';
                  $comment->comment_visibility = "customer";
                  $comment->comment = $text_body;
                  $comment->create();
            
                  $mail->Send();	
                }
                
            	}  							
  						
  					} else if ($current == 'Order Completion') {
  					
  						// Competed!
  						echo "COMPLETED!";

            	$orders_states = new orders_states();
            	$orders_states->order_id = $orders->order_id;
            	$orders_states->state_name = 'closed';
            	$orders_states->create();
  
        			$orders->status = 'closed';
        			$orders->save();
            	
            	
        
              // Send order receipt:
              $mail = new PHPMailer();
        
              $mail->From     = "service.delivery@xi.com.au";
              $mail->FromName = "X Integration Pty Ltd";
              $mail->Subject  = "Service Completion Advice";
              $mail->Host     = "127.0.0.1";
              $mail->Mailer   = "smtp";
        
              $text_body  = "Dear " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . ",\r\n";
              $text_body .= "\r\n";
              $text_body .= "X Integration is pleased to advise that successful commissioning of your service has been completed, this service is ready for use and billing has commenced. " . "\r\n";
              $text_body .= "\r\n";
              $text_body .= "For your reference, this email contains information about your service, plus service numbers and attributes for each service on the order.\r\n";
              $text_body .= "\r\n";
              $text_body .= "Company Name: " . ucwords($customer->company_name) . "\r\n\r\n";
              $text_body .= "Customer Name: " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . "\r\n\r\n";
              $text_body .= "Username: " . get_attribute( $orders->order_id, "order_username" ) . "@" . get_attribute( $orders->order_id, "order_realms" ) . "\r\n\r\n";
              $text_body .= "Password: " . get_attribute( $orders->order_id, "order_password" ) . "\r\n\r\n";
              $text_body .= "Order Reference: " . $orders->order_id . "\r\n\r\n";
              $text_body .= "Below is a list of item(s) on this order. " . "\r\n\r\n";
        
              $plan_title = new plans();
              $plan_title->plan_id = $services->retail_plan_id;
              $plan_title->load();
        
              $text_body .= "Type of Order: " . ucwords($orders->action) . " - ADSL or NBN Service\r\n\r\n";
              $text_body .= "Service Components:\r\n\r\n";
              $text_body .= "Service ID: " . $services->service_id . "\r\n";
              $text_body .= "Transaction Type: " . ucwords($orders->action) . "\r\n";
              $text_body .= "Customer Account Number: " . ucwords($customer->customer_id) . "\r\n";
        
              $contract_length = new plan_attributes();
              $contract_length->plan_id = $plan_title->plan_id;
              $contract_length->param = "contract_length";
              $contract_length->get_latest();
        
              $text_body .= "Contract Term (Months): " . $contract_length->value . "\r\n\r\n";
        
              $text_body .= "Service Number: " . get_attribute( $orders->order_id, "order_service_number" ) . "\r\n";
              $text_body .= "Access Location: " . get_attribute( $orders->order_id, "order_address" ) . "\r\n";
              $text_body .= "Access Method: " . $plan_title->access_method . "\r\n";
           	
              $service_types = new service_types();
              $service_types->type_id = $plan_title->type_id;
              $service_types->load();
        
              $text_body .= "Access Technology: " . $service_types->description . "\r\n";
              $text_body .= "Access Speed: Up to " . $plan_title->speed . "\r\n\r\n";
              
              $text_body .= "If you have any further queries, please don't hesitate to contact X Integration and quote the X Integration Provisioning Reference provided above.\r\n\r\n";
              $text_body .= "Service Difficulties:\r\n";
              $text_body .= "To report service difficulties or faults please phone the X Integration Help Desk at 1300 789 299 and quote the X Integration Provisioning Reference provided above.\r\n\r\n";
              $text_body .= "Kind Regards,\r\n";
              $text_body .= "X Integration Service and Provisioning Team\r\n\r\n";
      
    	       	//$mail->AddAddress($customer->email);
     	      	$mail->AddCC($wholesaler->email);
      	      $mail->AddBCC("alerts@xi.com.au");

        
      				$mail->Body    = $text_body;
      
              $comment = new order_comments();
              $comment->order_id = $orders->order_id;
              $comment->username = 'system';
              $comment->comment_visibility = "customer";
              $comment->comment = $text_body;
              $comment->create();
        
              $mail->Send();	
              
              $services->state = 'active';
              $services->start_date = $current_end;
              $services->save();
              
  					}  				
    				
    				echo "Current state with AAPT: $current\n";	
    				echo "Install Date AAPT: $install_date\n";	
    					
    			}
  			}
  		}

  	}
  }
}


function get_attribute($order_id,$param){

  $email_order_attr = new order_attributes();
  $email_order_attr->order_id = $order_id;
  $email_order_attr->param = $param;
  $email_order_attr->get_latest();

  return $email_order_attr->value;
}

function prettyXML($xml, $debug=false) {
  // add marker linefeeds to aid the pretty-tokeniser
  // adds a linefeed between all tag-end boundaries
  $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

  // now pretty it up (indent the tags)
  $tok = strtok($xml, "\n");
  $formatted = ''; // holds pretty version as it is built
  $pad = 0; // initial indent
  $matches = array(); // returns from preg_matches()

  /* pre- and post- adjustments to the padding indent are made, so changes can be applied to
   * the current line or subsequent lines, or both
  */
  while($tok !== false) { // scan each line and adjust indent based on opening/closing tags

    // test for the various tag states
    if (preg_match('/.+<\/\w[^>]*>$/', $tok, $matches)) { // open and closing tags on same line
      if($debug) echo " =$tok= ";
      $indent=0; // no change
    }
    else if (preg_match('/^<\/\w/', $tok, $matches)) { // closing tag
      if($debug) echo " -$tok- ";
      $pad--; //  outdent now
    }
    else if (preg_match('/^<\w[^>]*[^\/]>.*$/', $tok, $matches)) { // opening tag
      if($debug) echo " +$tok+ ";
      $indent=1; // don't pad this one, only subsequent tags
    }
    else {
      if($debug) echo " !$tok! ";
      $indent = 0; // no indentation needed
    }

    // pad the line with the required number of leading spaces
    $prettyLine = str_pad($tok, strlen($tok)+$pad, ' ', STR_PAD_LEFT);
    $formatted .= $prettyLine . "\n"; // add to the cumulative result, with linefeed
    $tok = strtok("\n"); // get the next token
    $pad += $indent; // update the pad size for subsequent lines
  }
  return $formatted; // pretty format
}


function soapDebug($client){

    $requestHeaders = $client->__getLastRequestHeaders();
    $request = prettyXml($client->__getLastRequest());
    $responseHeaders = $client->__getLastResponseHeaders();
    $response = prettyXml($client->__getLastResponse());

    echo '<code>' . nl2br(htmlspecialchars($requestHeaders, true)) . '</code>';
    echo highlight_string($request, true) . "<br/>\n";

    echo '<code>' . nl2br(htmlspecialchars($responseHeaders, true)) . '</code>' . "<br/>\n";
    echo highlight_string($response, true) . "<br/>\n";
}
