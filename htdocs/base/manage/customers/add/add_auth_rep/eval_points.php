<?php

include_once "../../../../../setup.inc";

include "../../../../doauth.inc";
include_once "authorised_rep.class";

$secondary = "";
$ids = array();
$ids[] = $_POST['primary_id'];

foreach($_POST as $key => $value) {
  $pos = strpos($key , "ar_secondary_list_");
  if ($pos === 0){
    $ids[] = $_POST[$key];
  }
}

$total = 0;

for ($i=0; $i < count($ids); $i++) { 
	if ($ids[$i]!="0") {
		$doc_points = new authorised_rep();
		$var = $doc_points->get_requirements($ids[$i]);
		$total = $total + $var[0]["points"];
	}
}
echo $total;