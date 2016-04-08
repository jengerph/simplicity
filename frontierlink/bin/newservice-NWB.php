#!/usr/bin/php -q
<?php

$client = new SoapClient("../wsdl/FrontierLink.wsdl", array('local_cert'     => "../cert/frontierlink-cert.xi.com.au.cer",'trace'=>1));

$params = array();
$params['orderContact'] = array();
//$params['orderContact']['individual']['salutation'] = 'Mr';
//$params['orderContact']['individual']['firstName'] = 'Matthew';
//$params['orderContact']['individual']['lastName'] = 'Enger';
$params['orderContact']['name'] = 'Claringbold Financial Services';
$params['orderContact']['phone'] = '1300300844';
$params['orderContact']['mobile'] = '0406532792';
$params['orderContact']['email'] = 'm.enger@xi.com.au';
$params['installContact'] = array();
$params['installContact']['individual']['salutation'] = 'Mr';
$params['installContact']['individual']['firstName'] = 'Matthew';
$params['installContact']['individual']['lastName'] = 'Enger';
$params['installContact']['phone'] = '1300789299';
$params['installContact']['mobile'] = '0406532792';
$params['installContact']['email'] = 'm.enger@xi.com.au';
$params['customerReference'] = 'Claringbold-DSL';

$params['serviceDetailsList'] = array();
$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService'] = array();
$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['accountNumber'] = '2000027399';
$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['qualificationID'] = '3524746';
$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['endCSN'] = '0398375454';
$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['accessMethod'] = "On Net ADSL2+ Annex A (Type ii)";
$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['accessType'] = 'SSS';
$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['serviceSpeed'] = 'Up to 24Mbps/1Mbps (Annex A)';
$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['networkConnectionServiceId'] = '8104234';
$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['contractTerm'] = '24';
//$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['nbnConnectionType'] = '';
//$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['radiusEntry'] = array();
//$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['radiusEntry']['radiusUserName'] = 'fred';
//$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['radiusEntry']['radiusDomain'] = 'aapt.com.au';
//$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['radiusEntry']['password'] = 'spagetti';
//$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['dslTransfer'] = '';
$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['installDate'] = '30/08/2015';
//$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['qualificationAddressOverride'] = '';
//$params['serviceDetailsList'][0]['nationalWholesaleBroadbandService']['batteryBackupService'] = '';

//$params['qualifyNationalWholesaleBroadbandProductRequest']['telstraLocationID'] = '';
//$params['qualifyNationalWholesaleBroadbandProductRequest']['nbnLocationID'] = 'LOC000000000003';
//$params['qualifyNationalWholesaleBroadbandProductRequest']['returnExtendedNbnSqData'] = false;
//$params['qualifyNationalWholesaleBroadbandProductRequest']['standAloneQualification'] = false;


try{
				$response = $client->NewService($params);
       //$response = $client->__soapCall("sendMessages", array($params));           
   }
catch (SoapFault $exception) {

echo $exception;      
soapDebug($client);

}
//echo "REQUEST:\n" . $client->__getLastRequest() . "\n";
var_dump($response);

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