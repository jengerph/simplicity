<?php
///////////////////////////////////////////////////////////////////////////////
//
// htdocs/base/manage/services/add/index.php - Add Service
// $Id$
//
///////////////////////////////////////////////////////////////////////////////
//
// HISTORY:
// $Log$
///////////////////////////////////////////////////////////////////////////////

// Get the path of the include files
include_once "../../../../setup.inc";

include "../../../doauth.inc";

include_once "services.class";
include_once "service_types.class";
include_once "service_attributes.class";
include_once "plans.class";


$user = new user();
$user->username = $_SESSION['username'];
$user->load();

if ($user->class == 'customer') {
	
	$pt->setFile(array("outside" => "base/outside2.html", "main" => "base/manage/services/add/index2.html"));
	
} else if ($user->class == 'reseller') {
  $pt->setFile(array("outside" => "base/outside3.html", "main" => "base/manage/services/add/index.html"));
  
} else if ($user->class == 'admin') {
  $pt->setFile(array("outside" => "base/outside1.html", "main" => "base/manage/services/add/index.html"));
  
}

// Assign the templates to use
$pt->setFile(array("service_option" => "base/manage/wholesalers/service_option.html","fnn_section" => "base/manage/services/add/fnn_yes.html"));
// $pt->setFile(array("fnn_section" => "/base/manage/services/add/fnn_yes.html"));

$services = new services();

if ( !isset($_REQUEST["customer_id"]) ) {
  echo "Invalid Customer ID.";
  exit(1);
}

if ( isset($_REQUEST["fnn_service"]) ) {
  if ( $_REQUEST["fnn_service"] ) {
    if ( $_REQUEST["fnn_service"] == "yes" ) {
      $pt->setFile(array("fnn_section" => "base/manage/services/add/fnn_yes.html"));
    } else {
      $pt->setFile(array("fnn_section" => "base/manage/services/add/fnn_no.html"));
    }
  }
} else {
  $_REQUEST["fnn_service"] = 'yes';
  $pt->setFile(array("fnn_section" => "base/manage/services/add/fnn_yes.html"));
}

$pt->parse('FNN_SECTION','fnn_section','true');

if (isset($_REQUEST['submit'])) {
  
  // Add new service
  $error_msg = '';
  $services->customer_id = $_REQUEST['customer_id'];
  $services->type_id = $_REQUEST['service_type'];
  $services->start_date = $_REQUEST['start_date'];
  $services->contract_end = $_REQUEST['contract_end'];
  $services->wholesale_plan_id = $_REQUEST['wholesale_plan'];
  $services->retail_plan_id = $_REQUEST['retail_plan'];
  $services->state = $_REQUEST['state'];
  $services->identifier = $_REQUEST['identifier'];
  $services->tag = $_REQUEST['tag'];

  if ( $services->wholesale_plan_id == "" && $user->class == 'customer' ) {
    $services->wholesale_plan_id = "-";
  }
  $vc = $services->validate();

  if ($vc != 0) {
  
    $pt->setVar('ERROR_MSG','Error: ' . $config->error_message[$vc]);

  } else {
    
    $services->create();

    $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
   <soapenv:Body>
      <EnquireServiceResponse xmlns="http://www.aapt.com.au/FrontierLink/xsd">
         <accountNumber>2000000000</accountNumber>
         <serviceOrderDetails>
            <serviceOrderID>0299990000-0299990100</serviceOrderID>
            <serviceOrderType>Number Range</serviceOrderType>
            <serviceStatus>In Service</serviceStatus>
            <CtsProductOrderDetails>
               <cspCode>001</cspCode>
               <callTerminationServiceTrunkIds>
                  <serviceOrderIdTypes>
                     <serviceOrderID>100000</serviceOrderID>
                     <serviceOrderType>Call Termination Service</serviceOrderType>
                  </serviceOrderIdTypes>
               </callTerminationServiceTrunkIds>
               <callTerminationServiceTrunkIds>
                  <serviceOrderIdTypes>
                     <serviceOrderID>100000</serviceOrderID>
                     <serviceOrderType>Call Termination Service</serviceOrderType>
                  </serviceOrderIdTypes>
               </callTerminationServiceTrunkIds>
               <siteAddress>
                  <addressInformation>Carrier Rm MDF1011</addressInformation>
                  <streetNumber>100</streetNumber>
                  <streetName>TEST</streetName>
                  <streetType>CLOSE</streetType>
                  <suburb>ALISON</suburb>
                  <state>NSW</state>
                  <postcode>2259</postcode>
               </siteAddress>
            </CtsProductOrderDetails>
         </serviceOrderDetails>
      </EnquireServiceResponse>
   </soapenv:Body>
</soapenv:Envelope>';

    $string = preg_replace('/<soapenv:Envelope[^>]*?>/', "", $xml);
    $string = preg_replace('/<soapenv:Body[^>]*?>/', "", $string);
    $string = preg_replace('/<\/soapenv:Envelope[^>]*?>/', "", $string);
    $string = preg_replace('/<\/soapenv:Body[^>]*?>/', "", $string);

    $data_array = xml_to_array($string);
    $array = array();
    $test = array_flatten($data_array);
    $keys = array_keys($test);

    for ($x = 0; $x < count($test); $x++ ) {
      //create attributes
      if(! is_array($test[$keys[$x]])){

        create( $services->service_id, $keys[$x], $test[$keys[$x]]);

      } else {

        recursive( $test[$keys[$x]], $services->service_id, $keys[$x] );

      }
    }
    // Done, goto list
    $url = "";
        
    if (isset($_SERVER["HTTPS"])) {
        
      $url = "https://";
          
    } else {
        
      $url = "http://";
    }

    if ( $user->class != 'customer' ) {
      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/customers/?customer_id=" . $_REQUEST["customer_id"];
    } else if ( $user->class == 'customer' ) {
      $url .= $_SERVER["SERVER_NAME"] . ':' . $_SERVER['SERVER_PORT'] . "/base/manage/services/";
    }

    header("Location: $url");
    exit();   
  
  }
}

$pt->setVar('CUSTOMER_ID', $_REQUEST["customer_id"]);
$pt->setVar('TYPE', $services->type_id);
$pt->setVar('START_DATE', $services->start_date);
$pt->setVar('CONTRACT_END', $services->contract_end);
$pt->setVar('WHOLESALE_PLAN', $services->wholesale_plan_id);
$pt->setVar('RETAIL_PLAN', $services->retail_plan_id);
$pt->setVar('STATE', $services->state);
$pt->setVar('IDENTIFIER', $services->identifier);
$pt->setVar('TAG', $services->tag);
$pt->setVar('FNN_SERVICE_' . strtoupper($_REQUEST["fnn_service"]), ' checked');
$pt->setVar('STATE_' . strtoupper($services->state) . '_SELECT', ' selected');

//Get a list of wholesalers
$services2 = new service_types();
$services_list = $services2->get_services();
$list_ready = $services2->service_list('service_type',$services_list);

$pt->setVar('SERVICE_TYPE_LIST', $list_ready);

$pt->setVar('ST_' . strtoupper($services->type_id) . '_SELECT', ' selected');

//Get a list of wholesaler_plans
$wholesale_plan = new plans();
$wholesale_plan_list = $wholesale_plan->get_all();
$list_ready_w = $wholesale_plan->plans_list('wholesale_plan',$wholesale_plan_list);

$pt->setVar('WHOLESALE_PLAN_LIST', $list_ready_w);

$pt->setVar('P_' . strtoupper($services->wholesale_plan_id) . '_SELECT', ' selected');

//Get a list of retail_plans
$retail_plan = new plans();
$retail_plan_list = $retail_plan->get_all();
$list_ready_w = $retail_plan->retail_plans_list('retail_plan',$retail_plan_list);

$pt->setVar('RETAIL_PLAN_LIST', $list_ready_w);

$pt->setVar('PR_' . strtoupper($services->retail_plan_id) . '_SELECT', ' selected');


$pt->setVar("PAGE_TITLE", "New Service");
		
// Parse the main page
$pt->parse("MAIN", "main");
// Parse the outside page
$pt->parse("WEBPAGE", "outside");

// Print out the page
$pt->p("WEBPAGE");

function xml_to_array($xml,$main_heading = '') {
    $deXml = simplexml_load_string($xml);
    $deJson = json_encode($deXml);
    $xml_array = json_decode($deJson,TRUE);
    if (! empty($main_heading)) {
        $returned = $xml_array[$main_heading];
        return $returned;
    } else {
        return $xml_array;
    }
}

function array_flatten($array) { 
  if (!is_array($array)) { 
    return FALSE; 
  } 
  $result = array();
  foreach ($array as $key => $value) { 
    if (is_array($value)) { 
      $result = array_merge_recursive($result, array_flatten($value)); 
    } 
    else { 
      $result[$key]= $value; 
    }
  } 

  return $result; 
}

function create($id,$param,$value){

      $attributes = new service_attributes();
      $attributes->service_id = $id;
      $attributes->param = $param;
      $attributes->value = $value;
      $attributes->create();

}

function recursive($array, $id, $key){

  if ( is_array($array) ) {
          
          foreach ($array as &$value) {

            if ( !is_array($value)) {

            create( $id, $key, $value);

            } else if ( is_array($value) ) {

              recursive($value, $id, $key);

            }
          }
        }

}