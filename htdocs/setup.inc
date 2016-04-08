<?php
///////////////////////////////////////////////////////////////////////////////
//
// setup.inc - File containing the path to the include files
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// This file serves one purpose, to tell the programs the location
// of the include files. 
//
// Since the include files contain sensative information such as passwords,
// DB schemes and other details that we don't want web accessable, we place
// them in a location which you can not access from the outside through
// your web browser.
//
// In order for the scripts to know this location without having to edit each
// one individually we get them to include this file to get the location.
//
// Copyright (c) 2006 X Integration
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////


ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR  . '/var/www/simplicity/includes' . PATH_SEPARATOR  . '/var/www/simplicity/classes');

ini_set('magic_quotes_gpc','0');
//ini_set('display_errors','on');
//ini_set('error_reporting',E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR);

include_once "config.class";
include_once "misc.class";
//include_once "auth.inc";

$config = new config();
// Check if we need to force ssl
if ($config->force_ssl == 1) {

	/*
  if ($_SERVER["HTTPS"] != "on") {

    // Not in ssl mode
    $url = "https://" .  $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        
    header("Location: $url");
    exit();
  }*/
  if (isset($_SERVER["HTTPS"])) {
  	if ($_SERVER["HTTPS"] == "on") {

    	// Not in ssl mode
    	$url = "http://" .  $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        
    	header("Location: $url");
    	exit();
    }
  }
}

$config = new config();
$misc = new misc();

// New templating engine
include_once 'HTML/Template/PHPLIB.php';

$pt = new Template_PHPLIB($config->template_root . '/eng');

$pt->setVar("SELF", $_SERVER["PHP_SELF"]);
//ini_set('session.use_cookies', '0');
//ini_set('session.use_trans_sid', '1');
//ini_set('session.use_only_cookies', '0';
//ini_set('session.save_path', $config->session_dir);

// Include DB Session Handeler
include_once "databaseSessionHandler.inc.phps";

// Setup session
  session_name('sid');
  session_start();
// if (!isset($_SESSION)){
// }

$pt->setVar("SID", session_id());

if (isset($_SERVER["SERVER_NAME"])) {
	$bits = explode('.', $_SERVER["SERVER_NAME"]);

	$pt->setVar("HOSTNAME", $bits[0]);
}