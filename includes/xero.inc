<?php

require_once 'xero/XeroOAuth.php';

$config = new config();

$signatures = array (
		'consumer_key' => $config->xero_consumer_key,
		'shared_secret' => $config->xero_consumer_secret,
		// API versions
		'core_version' => '2.0',
		'payroll_version' => '1.0',
		'file_version' => '1.0' 
);

$XeroOAuth = new XeroOAuth ( array_merge ( array (
		'application_type' => "Public",
		'oauth_callback' => 'http://simplicity.xi.com.au/base/manage/wholesalers/xero/index.php',
		'user_agent' => "Xero-OAuth-PHP Public" 
), $signatures ) );

$xero_initialCheck = $XeroOAuth->diagnostics ();

$checkErrors = count ( $xero_initialCheck );
if ($checkErrors > 0) {
	// you could handle any config errors here, or keep on truckin if you like to live dangerously
	foreach ( $xero_initialCheck as $check ) {
		echo 'Error: ' . $check . PHP_EOL;
	}
	exit();
} 


function xero_outputError($XeroOAuth)
{
    echo 'Error: ' . $XeroOAuth->response['response'] . PHP_EOL;
    xero_pr($XeroOAuth);
    exit();
}

function xero_pr($obj)
{

    if (!is_cli())
        echo '<pre style="word-wrap: break-word">';
    if (is_object($obj))
        print_r($obj);
    elseif (is_array($obj))
        print_r($obj);
    else
        echo $obj;
    if (!is_cli())
        echo '</pre>';
}

function is_cli()
{
    return (PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR']));
}
