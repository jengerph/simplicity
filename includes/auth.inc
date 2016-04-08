<?php
///////////////////////////////////////////////////////////////////////////////
//
// includes/auth.inc - Authentication functions
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

include_once "user.class";


function verify_access($username, $password, $rights) { 

  // Verify the username and password combination
  $user = new user();
  $user->username = $username;

  if (!$user->exist()) {
  
    return 0;
    
  } else {
  
    $user->load();
    
    if ($user->password != $password) {

      return 0;    
      
    }
    
    if ($user->active == 'no') {
    	return 0;
    }
    
  }
  
  return 1;

}    

?>
