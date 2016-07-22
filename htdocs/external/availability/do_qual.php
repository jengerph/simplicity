<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/export/availability/do_qual.php - Preform a qualification
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../setup.inc";
include_once "website_servicequal.class";

set_time_limit('90');

  
$wsq = new website_servicequal();
$wsq->qual_id = $argv[1];

if (!$wsq->exist()) {
	echo "Invalid qual.";
	exit();
} else {
	$wsq->load();
}

if ($wsq->status == 'pending') {
	
	// Change to processing
	$wsq->status = 'progressing';
	$wsq->save();
	
	// Format address
	 if (isset($wsq->level)) {
  	
  	// We have sub address information
  	$bits = explode(' ', $wsq->level);
  	$unitNumber = trim($bits[1]);
  	$unitType =trim( $bits[0]);
  }
  
  if ($unitType == 'L' || $unitType == 'Level') {
  	
  	// Level not unit
  	$levelNumber = $unitNumber;
  	$levelType = $unitType;
  	$unitNumber = '';
  	$unitType = '';
  	
  }
  
  $space = strrpos($wsq->street_name, ' ');
  $streetName = trim(substr($wsq->street_name, 0, $space));
  $streetType = trim(substr($wsq->street_name, $space+1));
  
  $dash = strrpos($wsq->street_number, '-');
  if ($dash !== false) {
  	$streetNumber = trim(substr($wsq->street_number, 0, $dash));
  } else {
  	$streetNumber  = $wsq->street_number;
  }
  
  $params = array();
  
  $params['ruralMailNumber'] = '';
  $params['ruralMailType'] = '';
  $params['ruralNumber'] = '';
  $params['levelNumber'] = $levelNumber;
  $params['levelType'] = $levelType;
  $params['unitNumber'] = $unitNumber;
  $params['unitType'] = $unitType;
  $params['planNumber'] = '';
  $params['lotNumber'] = '';
  $params['streetNumber'] = $streetNumber;
  $params['streetNumberSuffix'] ='';
  $params['siteName'] = '';
  $params['streetName'] = $streetName;
  $params['streetType'] = $streetType;
  $params['streetTypeSufix'] = '';
  $params['suburb'] = $wsq->locality;
  $params['state'] = $wsq->state;
  $params['postcode'] = $wsq->postcode;
  //print_r($params);
  
  // Determine Telstra and NBN Co ID Numbers
  
	$locationid = servicequal_address1($params);
	
	//echo "Location ID: $locationid" . "\n";
	
	// Lets do the servcie qual now to see what is available
	
	$qual = servicequal_address2('Telstra', $locationid);
	
	
	while ($method = each($qual['accessQualificationList'])) {
	
		if ($method['value']['accessMethod'] == 'AAPT ADSL2+' || $method['value']['accessMethod'] == 'On Net ADSL2+ Annex A (Type ii)') {
			if ($method['value']['qualificationResult'] == 'PASS') {
				$wsq->result_adsl_onnet = 'yes';
			}
		}
		if ($method['value']['accessMethod'] == 'Telstra L2IG') {
			if ($method['value']['qualificationResult'] == 'PASS') {
				$wsq->result_adsl_offnet = 'yes';
			}
		}
		if ($method['value']['accessMethod'] == 'NBN') {
			if ($method['value']['accessType'] == 'NFAS') {
				if ($method['value']['qualificationResult'] == 'PASS') {
					$wsq->result_nbn_fiber = 'yes';
				}
			}
			if ($method['value']['accessType'] == 'NWAS') {
				if ($method['value']['qualificationResult'] == 'PASS') {
					$wsq->result_nbn_wireless = 'yes';
				}
			}
			if ($method['value']['accessType'] == 'NCAS') {
				if ($method['value']['qualificationResult'] == 'PASS') {
					$wsq->result_nbn_fttn = 'yes';
				}
			}
		}
		
		
	}
	
	if ($wsq->result_nbn_fiber == 'yes' || $wsq->result_nbn_wireless == 'yes' || $wsq->result_nbn_fttn == 'yes') {
		$wsq->result_adsl_onnet = 'no';
		$wsq->result_adsl_offnet = 'no';
	}
	
	$wsq->status = 'complete';

	$wsq->save();
	
	//print_r($qual);
	
	//echo "Complete";
	exit();
	
} else {
	
	echo "Qual in incorrect state to complete";
	exit();
}


function servicequal_address1($address){

  $config = new config();

  $client = new SoapClient($config->frontier_dir . "/wsdl/FrontierLink.wsdl", array('local_cert'     => $config->frontier_dir . "/cert/frontierlink-cert.xi.com.au.cer",'trace'=>1));

  $params = array();
  $params['address'] = $address;
  $params['serviceProvider'] = array();
  $params['serviceProvider'][0] = 'Telstra';
	//echo "Matt:\n";
  //print_r($params); 
  try{
          $response = $client->findServiceProviderLocationId($params);         
     }
  catch (SoapFault $exception) {

  }

	print_r($response);
	//echo "Matt";
	//exit();
  
  if (sizeof($response->serviceProviderLocationList->serviceProviderLocationList->locationList->addressInformation) == 1) {
  	
  	return $response->serviceProviderLocationList->serviceProviderLocationList->locationList->addressInformation->locationId;
  } else {
  	
  	// Return last response
  	return $response->serviceProviderLocationList->serviceProviderLocationList->locationList->addressInformation[sizeof($response->serviceProviderLocationList->serviceProviderLocationList->locationList->addressInformation)-1]->locationId;
  }
  
  
  
}

function servicequal_address2($serviceProvider,$id){

  $config = new config();

  $client = new SoapClient($config->frontier_dir . "/wsdl/FrontierLink.wsdl", array('local_cert'     => $config->frontier_dir . "/cert/frontierlink-cert.xi.com.au.cer",'trace'=>1));

  $params = array();
  $params['qualifyNationalWholesaleBroadbandProductRequest'] = array();

  if ( $serviceProvider == "Telstra" || $serviceProvider == "AAPT" ) {
    $params['qualifyNationalWholesaleBroadbandProductRequest']['telstraLocationID'] = $id;
  } else if ( $serviceProvider == "NBN" ) {
    $params['qualifyNationalWholesaleBroadbandProductRequest']['nbnLocationID'] = $id;
  }

  //$params['qualifyNationalWholesaleBroadbandProductRequest']['endCSN'] = '0398415846';
  // $params['qualifyNationalWholesaleBroadbandProductRequest']['telstraLocationID'] = '426889031';
  // $params['qualifyNationalWholesaleBroadbandProductRequest']['nbnLocationID'] = $locationID;
  // $params['qualifyNationalWholesaleBroadbandProductRequest']['nbnLocationID'] = 'LOC000032090909';
  //$params['qualifyNationalWholesaleBroadbandProductRequest']['returnExtendedNbnSqData'] = false;
  $params['qualifyNationalWholesaleBroadbandProductRequest']['standAloneQualification'] = true;


  try{
          $response = $client->QualifyProduct($params);
         //$response = $client->__soapCall("sendMessages", array($params));           
     }
  catch (SoapFault $exception) {

  }
  
  print_r($response);
  //echo "REQUEST:\n" . $client->__getLastRequest() . "\n";

  $arr = array();

  if ( is_object($response->siteAddress) ) {
    $arr['qualificationID'] = $response->qualificationID;
    $arr['siteDetails'] = $response->siteDetails;
    $arr['siteAddress'] = $response->siteAddress;

    $accessQualificationList_arr = array();

    $index=0;

      while ($cel = each($response->accessQualificationList)) {
        if ($cel['value']->qualificationResult == 'PASS') {
          // $accessQualificationList_arr[$index]=$cel['value'];
          $accessQualificationList_arr[$index]["id"]=$cel['value']->id;
          $accessQualificationList_arr[$index]["qualificationResult"]=$cel['value']->qualificationResult;
          $accessQualificationList_arr[$index]["accessMethod"]=$cel['value']->accessMethod;
          $accessQualificationList_arr[$index]["accessType"]=$cel['value']->accessType;
          $accessQualificationList_arr[$index]["priceZone"]=$cel['value']->priceZone;
          $accessQualificationList_arr[$index]["maximumDownBandwidth"]=$cel['value']->maximumDownBandwidth;
          $accessQualificationList_arr[$index]["maximumUpBandwidth"]=$cel['value']->maximumUpBandwidth;
          $accessQualificationList_arr[$index]["availableServiceSpeeds"]=$cel['value']->availableServiceSpeeds;
          // unset($accessQualificationList_arr[$index]["testOutcomes"]);
        }else{
          $accessQualificationList_arr[$index]["id"]=$cel['value']->id;
          $accessQualificationList_arr[$index]["qualificationResult"]=$cel['value']->qualificationResult;
          $accessQualificationList_arr[$index]["accessMethod"]=$cel['value']->accessMethod;
          $accessQualificationList_arr[$index]["accessType"]=$cel['value']->accessType;
        }
        $index++;
      }
    $arr['accessQualificationList'] = $accessQualificationList_arr;
  }


	print_r($arr);
  if ( count($arr) > 0 ) {
    return $arr;
  } else {
    return;
  }
}

