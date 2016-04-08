<?php
function insert_to_sess($session_pointer, $array){
	// if ( !isset($_SESSION['in_use']) ) {
	// 	$_SESSION['in_use'] = 'yes';
		$_SESSION[$session_pointer]['service_qualify_array'] = $array;
	// 	$_SESSION['in_use'] = 'no';
	// } else if ( $_SESSION['in_use'] == 'yes' ) {
	// 	return 0;
	// } else if ( $_SESSION['in_use'] == 'no' ) {
	// 	$_SESSION[$session_pointer]['service_qualify_array'] = $array;
	// 	$_SESSION['in_use'] = 'no';
	// }
	// array_push($_SESSION[$session_pointer]['service_qualify_array'],$array);
	return 0;
}