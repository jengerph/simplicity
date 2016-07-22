#!/usr/bin/php -q
<?php

require_once('nusoap/lib/nusoap.php');

$client = new nusoap_client('wsdl/AddressServiceV3.wsdl', true);

$client->setUseCurl(1);
$client->setCurlOption(CURLOPT_FOLLOWLOCATION, false);
$client->setCurlOption(CURLOPT_SSLCERT, '/var/www/simplicity/bin/telstra/lolig/Telstra XI LOLIG test cert.cer');


$params = array();
$params['Line1'] = 'UNIT 1';
$params['Line2'] = '525-535 COLLINS ST,';
$params['Line3'] = '';
$params['State'] = 'VIC';
$params['Locality'] = 'MELBOURNE';
$params['Postcode'] = '3000';


$result = $client->call('search', array('parameters' => $params), '', '', false, true);

// Check for a fault
if ($client->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result);
	echo '</pre>';
} else {
	// Check for errors
	$err = $client->getError();
	if ($err) {
		// Display the error
		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
		// Display the result
		echo '<h2>Result</h2><pre>';
		print_r($result);
		echo '</pre>';
	}
}
echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';


//print_r($client->response);
