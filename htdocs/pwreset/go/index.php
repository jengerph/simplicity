<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/reset/go/index.php - Reset a password - stage 2
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////


// Get the path of the include files
include_once "../../setup.inc";

include_once "user.class";
include_once "pwreset.class";



$pt->setVar("PAGE_TITLE", "Reset a lost password");


$pwr = new pwreset();

if (!isset($_REQUEST['reset_id'])) {
	echo "Invalid options.";
	exit();
}

$pwr->reset_id = $_REQUEST['reset_id'];

if (!$pwr->exist()) {
	echo "Invalid options.";
	exit();
}

$pwr->load();

if (!isset($_REQUEST['token'])) {
	echo "Invalid options.";
	exit();
}

if (md5($pwr->reset_id . '-' . $pwr->email . '-' . $pwr->dt) != $_REQUEST['token']) {
	echo "Invalid options.";
	exit();
}

if ($misc->date_ts($pwr->dt) < (time() - 172800)) {
	echo "Invalid options.";
	exit();
}
	
// We are valid.

// How many users do we have?
$user = new user();
		
$list = $user->search('email',$pwr->email,'username','ASC');

if (sizeof($list) == 1) {
	
	// Only one, jump to next step
  $url = "";
        
  if (isset($_SERVER["HTTPS"])) {
        
    $url = "https://";
          
  } else {
        
    $url = "http://";
  }

  $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/pwreset/go/stage2.php?reset_id=" . $_REQUEST['reset_id'] . '&token=' . $_REQUEST['token'] .'&username=' . $list[0]['username'];

  header("Location: $url");
  exit();			
	
	
} 

// Assign the templates to use
$pt->setFile(array("main" => "pwreset/go/stage1.html", "row"=> "pwreset/go/stage1-row.html"));

$pt->setVar('RESET_ID', $pwr->reset_id);
$pt->setVar('TOKEN', $_REQUEST['token']);

while ($cel = each($list)) {
	$user->username = $cel['value']['username'];
	$user->load();
	
	if ($user->active == 'yes') {
  	  	
  	$pt->setVar('USERNAME', $user->username);
  	$pt->setVar('FIRST_NAME', $user->first_name);
  	$pt->setVar('LAST_NAME', $user->last_name);

  	
  	$pt->parse('ROWS','row',true);
	}
}

	
// Parse the main page
$pt->parse("WEBPAGE", "main");


// Print out the page
$pt->p("WEBPAGE");
