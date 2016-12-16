<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/adsl_nbn2/qual/index.php - Preform qualification
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../../../setup.inc";
include "../../../../../doauth.inc"; 
include_once "config.class";
include_once "customers.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if (!isset($_REQUEST['qual_id'])) {
	
	// NO qual id provided
	echo "No qualifcation id provided.";
	exit();
}

$pt->setVar('QUAL_ID', $_REQUEST['qual_id']);

if (!isset($_SESSION['qual_' . $_REQUEST['qual_id']])) {
	
	// Invalid qual
	echo "No qualifcation id provided.";
	exit();
}

$qual = $_SESSION['qual_' . $_REQUEST['qual_id']];

if (!isset($qual['result'])) {
	
  // Setup connection to Frontier
  $config = new config();
  $client = new SoapClient($config->frontier_dir . "/wsdl/FrontierLink.wsdl", array('local_cert'     => $config->frontier_dir . "/cert/frontierlink-cert.xi.com.au.cer",'trace'=>1));
  
  $params = array();
  $params['qualifyNationalWholesaleBroadbandProductRequest'] = array();
  $params['qualifyNationalWholesaleBroadbandProductRequest']['standAloneQualification'] = true;
  
  if ($qual['type'] == 'location') {
    if ( $qual['provider'] == "Telstra" ||  $qual['provider'] == "AAPT" ) {
      $params['qualifyNationalWholesaleBroadbandProductRequest']['telstraLocationID'] =  $qual['location_id'];
    } else if (  $qual['provider'] == "NBN" ) {
      $params['qualifyNationalWholesaleBroadbandProductRequest']['nbnLocationID'] = $qual['location_id'];
    }
  } else {
  	
  	// FNN
    $params['qualifyNationalWholesaleBroadbandProductRequest']['endCSN'] = $qual['fnn'];
  }
  
  try{
          $response = $client->QualifyProduct($params);
         //$response = $client->__soapCall("sendMessages", array($params));           
     }
  catch (SoapFault $exception) {
  
  }
  
  
	$nbnServiceabilityClass = array();
	
  $nbnServiceabilityClass[0] = '0 indicates that the location is not NBN serviceable';
  $nbnServiceabilityClass[1] = '1 indicates that the location is fibre serviceable, but a physical connection is not yet in place';
  $nbnServiceabilityClass[2] = '2 indicates that the location is fibre serviceable, but the NTD is not yet installed';
  $nbnServiceabilityClass[3] = '3 indicates that the location is fibre serviceable, and that the NTD has been installed';
  $nbnServiceabilityClass[4] = '4 indicates that the location is wireless serviceable, but a physical connection is not yet in place';
  $nbnServiceabilityClass[5] = '5 indicates that the location is wireless serviceable, but the NTD is not yet installed';
  $nbnServiceabilityClass[6] = '6 indicates that the location is wireless serviceable, and that the NTD has been installed';
  $nbnServiceabilityClass[10] = '10 indicates that the location is planned to be serviceable by copper';
  $nbnServiceabilityClass[11] = '11 indicates that the location is serviceable by copper, active node present';
  $nbnServiceabilityClass[12] = '12 indicates that the location is serviceable by copper, jumpering is required';
  $nbnServiceabilityClass[13] = '13 indicates that the location is serviceable by copper, infrastructure in place';
 
  $nbnServiceabilityClass[20] = '20 indicates that the location is planned to be serviced by HFC, outside plant does not exist';
  $nbnServiceabilityClass[21] = '21 indicates that the location is serviceable by HFC, the location has a street TAP but requires a lead-in, PCD, internal tie cable, and wall-plate/socket';
  $nbnServiceabilityClass[22] = '22 indicates that the location is serviceable by HFC, the location has a street TAP, lead-in and PCD in place, but no internal tie-cables with wall plates/sockets';
  $nbnServiceabilityClass[23] = '23 indicates that the location is serviceable by HFC, the location has a wall-plate/socket but no HFC NTD';
  $nbnServiceabilityClass[24] = '24 indicates that the location is serviceable by HFC, the location has a wall-plate/socket and HFC NTD and is Ready to Connect';
  
  $arr = array();
  $arr['displayAddress'] = $fnn;
  $arr['siteAddress'] = $response->siteAddress;
  $arr['qualificationID'] = $response->qualificationID;
  $arr['nbnLocationID'] = $response->siteDetails->nbnLocationID;
  $arr['dslCodesOnLine'] = $response->siteDetails->dslCodesOnLine;
  $arr['manual_order'] = $nbn_manual;
  $arr['nbnServiceabilityClass'] = $response->siteDetails->nbnServiceabilityClass;
  $arr['nbnServiceabilityClassText'] = $nbnServiceabilityClass[$response->siteDetails->nbnServiceabilityClass];
  
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
        $arr2['availableServiceSpeeds'] = $cel['value']->availableServiceSpeeds->serviceSpeed;
        
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
        $arr2['availableServiceSpeeds'] = $cel['value']->availableServiceSpeeds->serviceSpeed;
        
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
          $arr['results']['NBN'][$cel['value']->accessType]['availableServiceSpeeds'] = $cel['value']->availableServiceSpeeds->serviceSpeed;
          $arr['results']['NBN'][$cel['value']->accessType]['nbnNewDevelopmentsChargeApplies'] = $cel['value']->nbnNewDevelopmentsChargeApplies;
          
					if (isset($cel['value']->nbnCopperPairList)) {
          	
          	// Pairs...
          	$arr['results']['NBN'][$cel['value']->accessType]['pairs'] = $cel['value']->nbnCopperPairList;
          	
          }
        }
      }
    }
    
  }
  
  if ($qual['manual'] === true && sizeof($arr['results']['NBN']) == 0) {
  	
  	// NBN Manual order, lets return results
  	reset($response->accessQualificationList);
  	while ($cel = each($response->accessQualificationList)) {
  		
  		if ($cel['value']->accessMethod == 'NBN') {
  		
  			// We have NBN, does this result return more than one reason?
  			
  			if (sizeof($cel['value']->testOutcomes) > 1) {
  				
  				// This is the NBN Type
  	      $arr['results']['NBN'][$cel['value']->accessType]['accessMethod'] = $cel['value']->accessMethod;
          $arr['results']['NBN'][$cel['value']->accessType]['accessType'] = $cel['value']->accessType;
          $arr['results']['NBN'][$cel['value']->accessType]['priceZone'] = $cel['value']->priceZone;
          $arr['results']['NBN'][$cel['value']->accessType]['availableServiceSpeeds'] = $cel['value']->availableServiceSpeeds->serviceSpeed;
          $arr['results']['NBN'][$cel['value']->accessType]['nbnNewDevelopmentsChargeApplies'] = $cel['value']->nbnNewDevelopmentsChargeApplies;
          
					if (isset($cel['value']->nbnCopperPairList)) {
          	
          	// Pairs...
          	$arr['results']['NBN'][$cel['value']->accessType]['pairs'] = $cel['value']->nbnCopperPairList;
          	
          }
  			}
  		}
  	}
  }

  // Cable Pairs for FTTN
  
  // Unlisted / Telstra Pairs
  if (is_array($response->telstraCableDetails->cablePair)) {
  	
  	// Multiple pairs found
  	
  	while ($pair = each($response->telstraCableDetails->cablePair)) {
  		
  		$arr['pairs']['telstra'][] = $pair['value']->pairKey;
  	}
  } else if (isset($response->telstraCableDetails->cablePair->pairKey)) {
  	$arr['pairs']['telstra'][] = $response->telstraCableDetails->cablePair->pairKey;
  }  

  $_SESSION['qual_' . $_REQUEST['qual_id']]['result'] = $arr;
  
  // Loop through results and make qual list
  $quals = array();
  while ($method = each($arr['results'])) {

  
  	// Loop through their sub results
  	while ($type = each($method['value'])) {
  		
  		// Loop through speeds
  		if (is_array($type['value']['availableServiceSpeeds'])) {
  			
  			while ($speed = each($type['value']['availableServiceSpeeds'])) {
  
  				//echo $method['key'] . '-' . $type['value']['accessType'] . '-' . $type['value']['priceZone'] . '-' . $type['value']['accessMethod'] . '-' . $speed['value']->serviceSpeed . '<br>';
  				
  				$res = array();
  				$res['type'] = $method['key'] ;
  				$res['accessType'] = $type['value']['accessType'];
  				$res['priceZone'] = $type['value']['priceZone'];
  				$res['accessMethod'] = $type['value']['accessMethod'];
  				$res['speed'] = $speed['value']->serviceSpeed;
  
  				if (isset($type['value']['nbnNewDevelopmentsChargeApplies'])) {	
  					$res['nbnNewDevelopmentsChargeApplies'] = $type['value']['nbnNewDevelopmentsChargeApplies'];
  				}
  				
  				$quals[] = $res;
  				
  			}
  		} else {
  
  			//echo $method['key'] . '-' . $type['value']['accessType'] . '-' . $type['value']['priceZone'] . '-' . $type['value']['accessMethod'] . '-' . $type['value']['availableServiceSpeeds']->serviceSpeed . '<br>';
  
  			$res = array();
  			$res['type'] = $method['key'] ;
  			$res['accessType'] = $type['value']['accessType'];
  			$res['priceZone'] = $type['value']['priceZone'];
  			$res['accessMethod'] = $type['value']['accessMethod'];
  			$res['speed'] = $type['value']['availableServiceSpeeds']->serviceSpeed;
  			
  			if (isset($type['value']['nbnNewDevelopmentsChargeApplies'])) {	
  				$res['nbnNewDevelopmentsChargeApplies'] = $type['value']['nbnNewDevelopmentsChargeApplies'];
  			}
  			
  			$quals[] = $res;
  			
  		}
  	}
  }
  
  $_SESSION['qual_' . $_REQUEST['qual_id']]['quals'] = $quals;

  $qual = $_SESSION['qual_' . $_REQUEST['qual_id']];

}

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html"));
  
}


$pt->setFile(array("main" => "base/manage/services/add/adsl_nbn2/qual/index.html", "services_available" => "base/manage/services/add/adsl_nbn2/qual/services_available.html"));

$customer = new customers();
$customer->customer_id = $qual['customer_id'];
$customer->load();

$nbnNewDevelopmentsChargeApplies = '';
while ($cel = each($qual['quals'])) {
	$pt->setVar('RESULT_ID', $cel['key']);
	
	while ($cel2 = each($cel['value'])) {
		$pt->setVar('SERVICE_' . strtoupper($cel2['key']), $cel2['value']);
	}
	
	if ($cel['value']['nbnNewDevelopmentsChargeApplies'] != '') {
		$nbnNewDevelopmentsChargeApplies = $cel['value']['nbnNewDevelopmentsChargeApplies'];
	}
	
	$pt->parse('SERVICES_AVAILABLE', 'services_available', true);
}

foreach ($qual['result']['siteAddress'] as $key => $value) {
  $address .= $value . " ";
}

$pt->setVar('ORDER_ADDRESS', $address);
$pt->setVar('SERVICE_NUMBER', $qual['fnn']);
$pt->setVar('NBN_LOCATIONID', $qual['result']['nbnLocationID']);
$pt->setVar('MANUAL', $qual['manual']);
$pt->setVar('NBNSERVICEABILITYCLASS', $qual['result']['nbnServiceabilityClass']);
$pt->setVar('NBNSERVICEABILITYCLASSTEXT', $qual['result']['nbnServiceabilityClassText']);
$pt->setVar('NBNNEWDEVELOPMENTSCHARGEAPPLIES',$nbnNewDevelopmentsChargeApplies);


// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");
	
			
