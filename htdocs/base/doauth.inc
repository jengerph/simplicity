<?php
///////////////////////////////////////////////////////////////////////////////
//
// auth.inc - File containing the path to the include files / authentication
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
// Copyright (c) 2011 X Integration
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

include_once "auth.inc";
include_once "login_history.class";

$user = new user();

if ($_SESSION['username'] && $_SESSION['password']) {

  if (!verify_access($_SESSION['username'], $_SESSION['password'], array(0)) || ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR'])) {

    unset($_SESSION['password']);
  
    // failed
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/";

    header("Location: $url");
    exit();

  }
  
  // Check if IP Address has changed

} else {

  // failed
  $url = "";
        
  if (isset($_SERVER["HTTPS"])) {
        
    $url = "https://";
          
  } else {
        
    $url = "http://";
  }

  $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/";
	
	header("Location: $url");
  exit();

}

$user->username = $_SESSION['username'];
$user->load();

// Adjust language / template
if ($user->language != 'eng') {
	$pt = new Template_PHPLIB($config->template_root . '/' . strtolower($user->language));
}

// Poke history
$lh = new login_history();
$lh->sid = session_id();
$lh->username = $user->username;
$lh->poke();

?>
