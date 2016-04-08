#!/usr/bin/php
<?php

include "/var/www/simplicity/htdocs/setup.inc";
include_once "services.class";
include_once "service_attributes.class";

$query = "SELECT * FROM services WHERE (type_id = 1 OR type_id = 2) and customer_id = 310";

$service = new services();

$result = $service->db->execute_query($query);

while ( $row = $service->db->fetch_row_array($result)) {
      
	$service->service_id = $row['service_id'];
	$service->load();

	$st = new service_attributes();
	$st->service_id = $service->service_id;
	$st->param = 'aapt_service_id';

	$st->get_attribute();

	echo $service->service_id . '-' . $service->identifier . '-' . $st->value . "\n";


	$client = new SoapClient("../wsdl/FrontierLink.wsdl", array('local_cert'     => "../cert/frontierlink-cert.xi.com.au.cer",'trace'=>1));

	$params = array();
	$params['productType']= 'National Wholesale Broadband';
	$params['serviceOrderID']= $st->value;

	try{
				$response = $client->enquireService($params);
       			//$response = $client->__soapCall("sendMessages", array($params));           
   	}
	catch (SoapFault $exception) {

		echo $exception;      

	}
	//var_dump($response);

	if ($response->serviceDetails->nationalWholesaleBroadbandService->accessMethod == 'NBN') {

		$query2 = "UPDATE services set identifier = " . $service->db->quote($response->serviceDetails->nationalWholesaleBroadbandService->nbnLocationID) . " WHERE service_id = " . $service->service_id;
		$result2 = $service->db->execute_query($query2);

		echo $response->serviceDetails->nationalWholesaleBroadbandService->nbnLocationID . "\n";

	} else {

		// ADSL - need price zone
		$arr = servicequal($service->identifier);

		$st->param = 'priceZone';
		$st->value = $arr['results']['ADSL'][0]['priceZone'];
		if ($st->exist()) {
			$st->save();
		} else {
			$st->create();
		}

	}	

	$st->param = 'accessMethod';
	$st->value = $response->serviceDetails->nationalWholesaleBroadbandService->accessMethod;
	if ($st->exist()) {
		$st->save();
	} else {
		$st->create();
	}
	$st->param = 'accessSpeed';
	$st->value = $response->serviceDetails->nationalWholesaleBroadbandService->serviceSpeed;
	if ($st->exist()) {
		$st->save();
	} else {
		$st->create();
	}
	$st->param = 'accessType';
	$st->value = $response->serviceDetails->nationalWholesaleBroadbandService->accessType;
	if ($st->exist()) {
		$st->save();
	} else {
		$st->create();
	}

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





function servicequal($fnn) {

  $client = new SoapClient("../wsdl/FrontierLink.wsdl", array('local_cert'     => "../cert/frontierlink-cert.xi.com.au.cer",'trace'=>1));
  
  $params = array();
  $params['qualifyNationalWholesaleBroadbandProductRequest'] = array();
  
  $params['qualifyNationalWholesaleBroadbandProductRequest']['endCSN'] = $fnn;
  //$params['qualifyNationalWholesaleBroadbandProductRequest']['telstraLocationID'] = '';
  //$params['qualifyNationalWholesaleBroadbandProductRequest']['nbnLocationID'] = 'LOC000070716306';
  //$params['qualifyNationalWholesaleBroadbandProductRequest']['returnExtendedNbnSqData'] = false;
  //$params['qualifyNationalWholesaleBroadbandProductRequest']['standAloneQualification'] = false;
  
  
  try{
  				$response = $client->QualifyProduct($params);
         //$response = $client->__soapCall("sendMessages", array($params));           
     }
  catch (SoapFault $exception) {
  
  	echo $exception;      
  	soapDebug($client);
  
  }
  //echo "REQUEST:\n" . $client->__getLastRequest() . "\n";
  //print_r($response);	
  
  
  $arr = array();
  $arr['siteAddress'] = $response->siteAddress;
  $arr['qualificationID'] = $response->qualificationID;
  $arr['nbnLocationID'] = $response->siteDetails->nbnLocationID;
  $arr['dslCodesOnLine'] = $response->siteDetails->dslCodesOnLine;
  
  $arr['results'] = array();
  $arr['results']['ADSL'] = array();
  $arr['results']['NBN'] = array();
  
  while ($cel = each($response->accessQualificationList)) {
  	
  	if ($cel['value']->qualificationResult == 'PASS') {
    	
    	if ($cel['value']->accessMethod == 'On Net ADSL2+ Annex A (Type ii)' || $cel['value']->accessMethod == 'AAPT ADSL2+') {
    		
    		$arr2 = array();
    		$arr2['type'] = 'AAPT';
    		$arr2['distanceToExchange'] = $response->siteDetails->distanceToExchange;
    		
    		$arr2['accessMethod'] = $cel['value']->accessMethod;
    		$arr2['maximumDownBandwidth'] = $cel['value']->maximumDownBandwidth;
    		$arr2['maximumUpBandwidth'] = $cel['value']->maximumUpBandwidth;
    		$arr2['accessType'] = $cel['value']->accessType;
    		$arr2['priceZone'] = $cel['value']->priceZone;
    		$arr2['availableServiceSpeeds'] = $cel['value']->availableServiceSpeeds;
    		
    		$arr['results']['ADSL'][] = $arr2;
    	} else if ($cel['value']->accessMethod == 'Telstra L2IG') {
    		
    		$arr2 = array();
    		$arr2['type'] = 'Telstra';
    		$arr2['distanceToExchange'] = $response->siteDetails->distanceToExchange;
    		
    		$arr2['accessMethod'] = $cel['value']->accessMethod;
    		$arr2['maximumDownBandwidth'] = $cel['value']->maximumDownBandwidth;
    		$arr2['maximumUpBandwidth'] = $cel['value']->maximumUpBandwidth;
    		$arr2['accessType'] = $cel['value']->accessType;
    		$arr2['priceZone'] = $cel['value']->priceZone;
    		$arr2['availableServiceSpeeds'] = $cel['value']->availableServiceSpeeds;
    		
    		$arr['results']['ADSL'][] = $arr2;
    		
    	} else if ($cel['value']->accessMethod == 'NBN') {
    		
    		$pass = 1;
    		
    		if (is_array($cel['value']->testOutcomes)) {
    			
      		while ($cel2 = each($cel['value']->testOutcomes)) {
      			
      			if ($cel2['value']->testResult != 'PASS') {
      				
      				$pass = 0;
      			}
      		}
      	} else {
    			if ($cel['value']->testOutcomes->testResult != 'PASS') {
    				
    				$pass = 0;
    			}
  			}    		
    		
    		if ($pass == 1) {
  	
    			$arr['results']['NBN'][$cel['value']->accessType]['accessMethod'] = $cel['value']->accessMethod;
    			$arr['results']['NBN'][$cel['value']->accessType]['accessType'] = $cel['value']->accessType;
    			$arr['results']['NBN'][$cel['value']->accessType]['priceZone'] = $cel['value']->priceZone;
    			$arr['results']['NBN'][$cel['value']->accessType]['availableServiceSpeeds'] = $cel['value']->availableServiceSpeeds;
    		}
    		
    	}
    }
  }
  
  return $arr;

}
