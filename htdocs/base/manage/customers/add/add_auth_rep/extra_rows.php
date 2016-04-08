<?php
include_once "../../../../../setup.inc";
include "../../../../doauth.inc";
include_once "authorised_rep.class";

$pt->setFile(array("secondary_id_row" => "base/manage/customers/add/secondary_id_row.html"));

$add_secondary = $_POST['add_secondary'];

$idrequirements = new authorised_rep();
$idr = $idrequirements->get_authorised_rep();
$list = '<tr id="secondary_ids" bgcolor="#FFFF99"><td><div align="right"><font color="#000000"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Secondary ID:</font></b></font></div></td><td>';
$list .= '<select name="ar_secondary_list_'.$add_secondary.'" id="ar_secondary_list_'.$add_secondary.'" onchange="evaluate_points()">'; 
$list .= '<option value="0">Select ID</option>';
for ( $x = 0; $x < count($idr); $x++ ) {
if( $idr[$x]["id_type"] == "secondary" && $idr[$x]["account"] == "person" ){
  $list .= "<option value='" . $idr[$x]["id"] . "' {AR_ID_". $idr[$x]["id"] ."_SELECT_". $add_secondary ."}>" . $idr[$x]["description"] . "</option>";
}
}
$list .= '</select>';
$list .= '</td></tr>';
$list .= '<tr id="secondary_ids" bgcolor="#FFFF99"><td><div align="right"><font color="#000000"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Secondary ID Number:</font></b></font></div></td><td><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><input name="ar_secondary_id_number_'.$add_secondary.'" type="text" id="ar_secondary_id_number_'.$add_secondary.'" value=""></font></td></tr>';
$list .= '<tr id="secondary_ids" bgcolor="#FFFF99"><td><div align="right"><font color="#000000"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Upload Secondary ID:</font></b></font></div></td><td><input name="ar_s_attachment_'.$add_secondary.'" type="file" id="ar_s_attachment_'.$add_secondary.'"></td></tr>';
print_r($list);