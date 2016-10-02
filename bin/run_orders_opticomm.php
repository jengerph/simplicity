#!/usr/bin/php
<?php

include "/var/www/simplicity/htdocs/setup.inc";
include_once("class.phpmailer.php");

include_once "services.class";
include_once "service_types.class";
include_once "service_attributes.class";
include_once "plans.class";
include_once "plan_attributes.class";
include_once "orders.class";
include_once "order_attributes.class";
include_once "orders_states.class";
include_once "order_comments.class";
include_once "validate.class";
include_once "realms.class";
include_once "customers.class";
include_once "radius.class";
include_once "radius_reply.class";
include_once "staticip.class";
include_once "authorised_rep.class";

// Do we process new orders?
$CONTROL_PROCESS_NEW = 1;

// Do we process cancellations?
$CONTROL_PROCESS_CANCEL = 1;


const OPTICOMM_SERVICE_ID = 8;

$orders = new orders();

$orders_list = $orders->all_open();

$config = new config();


while ($cel = each($orders_list)) {

    $orders->order_id = $cel['value']['order_id'];
    $orders->load();

    echo $orders->status . "\n";

    $services = new services();
    $services->service_id = $orders->service_id;
    $services->load();

    echo $services->identifier . "\n";

    if ($services->type_id == OPTICOMM_SERVICE_ID) {

        $customer = new customers();
        $customer->customer_id = $services->customer_id;
        $customer->load();

        $wholesaler = new wholesalers();
        $wholesaler->wholesaler_id = $customer->wholesaler_id;
        $wholesaler->load();


        // ADSL or NBN Order
        if ($orders->status == 'pending') {

            $ts = $misc->date_ts($orders->start);

            if ($ts < time() - 300) {

                if ($orders->action == 'new') {

                    // Been 5 minutes, accept order
                    $do_accept = 1;

                    $contract_length = new plan_attributes();
                    $contract_length->plan_id = $services->wholesale_plan_id;
                    $contract_length->param = "contract_length";
                    $contract_length->get_latest();

                    $property_id = get_attribute($orders->order_id, "order_opticommPropertyID");
                    $service_speed = get_attribute($orders->order_id, "order_serviceSpeed");
                    $service_number = get_attribute($orders->order_id, "order_service_number");
                    $contact = get_attribute($orders->order_id, "order_contact");
                    $address = get_attribute($orders->order_id, "order_address");
                    $username = get_attribute($orders->order_id, "order_username");
                    $realm = get_attribute($orders->order_id, "order_realms");
                    $password = get_attribute($orders->order_id, "order_password");

                    $response = "";

                    try {

                        require_once dirname(__FILE__) . "/../includes/xisoap/includes/FactoryXiSoap.php";
                        // Start connect service request to Opticomm via SOAP
                        $client = new \XiSoap\FactoryXiSoap("connect.service");

                        /*
                         * Product code
                         OptiHome-12 = 12M/1M
                         OptiHome-25 = 25M/5M
                         OptiHome-50 = 50M/20M
                         OptiHome-99 = 100M/40M
                        */

                        $product_code = "";
                        switch ($service_speed) {
                            case "12Mbps/1Mbps":
                                $product_code = "OptiHome-12";
                                break;
                            case "25Mbps/5Mbps":
                                $product_code = "OptiHome-25";
                                break;
                            case "50Mbps/20Mbps":
                                $product_code = "OptiHome-50";
                                break;
                            case "100Mbps/40Mbps":
                                $product_code = "OptiHome-99";
                                break;
                        }

                        $poi = "";

                        var_dump($orders->order_id);
                        var_dump($property_id);
                        var_dump($address);
                        var_dump($product_code);

                        $param = [
                            "Property_ID" => $property_id,
                            "Contact_Name" => "Matthew Enger",
                            "Contact_Phone" => "",
                            "Contact_Mobile" => "",
                            "Contact_Email" => "m.enger@xi.com.au",
                            "FNN" => "",
                            "SIP_Username" => "",
                            "SIP_Password" => "",
                            "CLID" => "",
                            "Comment" => "",
                            "Provider_Ref" => $property_id,
                            "Product_Type" => "Broadband",
                            "Product_Code" => $product_code,
                            "POI" => $poi,
                        ];

                        $client = new \XiSoap\FactoryXiSoap("connect.service");
                        //$response = $client->getResults("ConnectService", $param);

                        if (!is_array($response) || count($response) == 0) {
                            //die("An error occurred while sending the request to Opticomm. Please contact technical support");
                        }
                        // End connect service request to Opticomm


                    } catch (SoapFault $exception) {
                        $comment = new order_comments();
                        $comment->order_id = $orders->order_id;
                        $comment->username = 'system';
                        $comment->comment_visibility = "internal";
                        $comment->comment = $exception;
                        $comment->create();

                        $orders->status = 'on hold';
                        $orders->save();

                        soapDebug($client);

                        $orders_states = new orders_states();
                        $orders_states->order_id = $orders->order_id;
                        $orders_states->state_name = 'on hold';
                        $orders_states->create();

                        $do_accept = 0;
                    }

                    /*
                    if ($do_accept = 1) {

                        // DO we have order ids
                        $service_id = $response->Service_ID;
                        $text = "Opticomm Service ID:" . "\r\n";
                        $comment = new order_comments();
                        $comment->order_id = $orders->order_id;
                        $comment->username = 'system';
                        $comment->comment_visibility = "internal";
                        $comment->comment = $text;
                        $comment->create();

                        $order_attributes = new order_attributes();
                        $order_attributes->order_id = $orders->order_id;
                        $order_attributes->param = "opticomm_service_id";
                        $order_attributes->value = $service_id;
                        $order_attributes->create();

                        $orders_states = new orders_states();
                        $orders_states->order_id = $orders->order_id;
                        $orders_states->state_name = 'accepted';
                        $orders_states->create();

                        $orders->status = 'accepted';
                        $orders->save();

                        // //create entries to radcheck and radusergroup
                        $radius = new radius();
                        $radius->service_id = $services->service_id;
                        $radius->username = get_attribute($orders->order_id, "order_username") . "@" . get_attribute($orders->order_id, "order_realms");
                        $radius->password = get_attribute($orders->order_id, "order_password");
                        $radius->create();

                        // Send order receipt:
                        $mail = new PHPMailer();

                        $mail->From = "service.delivery@xi.com.au";
                        $mail->FromName = "X Integration Pty Ltd";
                        $mail->Subject = "Order Acceptance Notification";
                        $mail->Host = "127.0.0.1";
                        $mail->Mailer = "smtp";

                        $text_body = "Dear " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . ",\r\n";
                        $text_body .= "\r\n";
                        $text_body .= "This email is to confirm that X Integration has today accepted your recent request for the following order. \r\n";
                        $text_body .= "\r\n";
                        $text_body .= "The date we are intending to complete the modification to your service is " . date("d M Y ", time() + (86400 * 21)) . ".\r\n";
                        $text_body .= "\r\n";
                        $text_body .= "Company Name: " . ucwords($customer->company_name) . "\r\n\r\n";
                        $text_body .= "Customer Name: " . ucwords($customer->first_name) . " " . ucwords($customer->last_name) . "\r\n\r\n";
                        $text_body .= "Username: " . get_attribute($orders->order_id, "order_username") . "@" . get_attribute($orders->order_id, "order_realms") . "\r\n\r\n";
                        $text_body .= "Password: " . get_attribute($orders->order_id, "order_password") . "\r\n\r\n";
                        $text_body .= "Order Reference: " . $orders->order_id . "\r\n\r\n";
                        $text_body .= "Below is a list of item(s) on this order. " . "\r\n\r\n";

                        $plan_title = new plans();
                        $plan_title->plan_id = $services->retail_plan_id;
                        $plan_title->load();

                        $text_body .= "Type of Order: " . ucwords($orders->action) . " - ADSL or NBN Service\r\n\r\n";
                        $text_body .= "Service Components:\r\n\r\n";
                        $text_body .= "Service ID: " . $services->service_id . "\r\n";
                        $text_body .= "Transaction Type: " . ucwords($orders->action) . "\r\n";
                        $text_body .= "Customer Account Number: " . ucwords($customer->customer_id) . "\r\n";

                        $contract_length = new plan_attributes();
                        $contract_length->plan_id = $plan_title->plan_id;
                        $contract_length->param = "contract_length";
                        $contract_length->get_latest();

                        $text_body .= "Contract Term (Months): " . $contract_length->value . "\r\n\r\n";

                        $text_body .= "Service Number: " . get_attribute($orders->order_id, "order_service_number") . "\r\n";
                        $text_body .= "Access Location: " . get_attribute($orders->order_id, "order_address") . "\r\n";
                        $text_body .= "Access Method: " . $plan_title->access_method . "\r\n";

                        $service_types = new service_types();
                        $service_types->type_id = $plan_title->type_id;
                        $service_types->load();

                        $text_body .= "Access Technology: " . $service_types->description . "\r\n";
                        $text_body .= "Access Speed: Up to " . $plan_title->speed . "\r\n\r\n";

                        $text_body .= "Provisioning Target Up to 21 days from X Integration's acceptance of your order (excluding customer delays).\r\n\r\n";
                        $text_body .= "The provisioning target shown above is based on the access type for the service. You can only order one service at a time. This ensures that the provision of one service is not held up pending the provision of other services on the order.\r\n\r\n";
                        $text_body .= "Throughout the provisioning process, we will provide you with updates on key milestones by sending the following notifications for each service on your order: \r\n\r\n";
                        $text_body .= "Service Completion Advice\r\n";
                        $text_body .= "Confirmation that the provisioning of your service has been completed and billing has commenced. This will also provide you with the contact details for your Service and Provisioning Team.\r\n\r\n";
                        $text_body .= "It is important to X Integration that you, our customer, are satisfied with the level of service provided.\r\n\r\n";
                        $text_body .= "To view the latest update on your order please visit this link: https://simplicity.xi.com.au/base/manage/orders/edit/?order_id=" . $orders->order_id . ". or visit our online Simplicity portal by logging in via https://simplicity.xi.com.au and view all your current orders under the order tab.\r\n\r\n";
                        $text_body .= "Alternatively you can contact X Integration Service and Provisioning Team using the contact details provided below.\r\n\r\n";
                        $text_body .= "Kind Regards,\r\n";
                        $text_body .= "X Integration Service and Provisioning Team\r\n\r\n";

                        //$mail->AddAddress($customer->email);
                        $mail->AddCC($wholesaler->email);
                        $mail->AddBCC("notifications@xi.com.au");

                        $mail->Body = $text_body;

                        $comment = new order_comments();
                        $comment->order_id = $orders->order_id;
                        $comment->username = 'system';
                        $comment->comment_visibility = "customer";
                        $comment->comment = $text_body;
                        $comment->create();

                        $mail->Send();
                    }

                } else if ($orders->action == 'cancel' && $CONTROL_PROCESS_CANCEL == 1) {

                    if ($services->type_id == OPTICOMM_SERVICE_ID) {

                        try {

                            //TODO Opticomm cancel service

                        } catch (SoapFault $exception) {
                            //echo $exception;

                            $comment = new order_comments();
                            $comment->order_id = $orders->order_id;
                            $comment->username = 'system';
                            $comment->comment_visibility = "internal";
                            $comment->comment = $exception;
                            $comment->create();

                            $orders->status = 'on hold';
                            $orders->save();

                            soapDebug($client);

                            $orders_states = new orders_states();
                            $orders_states->order_id = $orders->order_id;
                            $orders_states->state_name = 'on hold';
                            $orders_states->create();

                            $do_accept = 0;

                        }

                    } else if ($orders->action == 'addon create') {

                        if ($services->type_id == 1 || $services->type_id == 2) {

                            $parent_order_id = get_attribute($orders->order_id, "parent_order");
                            $go = 0;

                            if ($parent_order_id != '')

                                // We need to check if the order has been accepted yet
                                $porders = new orders();
                            $porders->order_id = $parent_order_id;
                            $porders->load();

                            if ($porders->status != 'pending' && $porders->status != 'withdrawn') {

                                $go = 1;

                            }

                        } else {

                            // No parent order
                            $go = 1;
                        }

                        // DO we proceed
                        if ($go == 1) {

                            // Is the request for a static ip?
                            if (get_attribute($orders->order_id, "order_staticip") == 'activated') {

                                // Yes

                                // Lets find one to assign
                                $staticip = new staticip();

                                $ip = $staticip->get_next_free_ip();

                                if ($ip == '') {

                                    // No ip available, hold order
                                    $text = "No static ip addresses availble" . "\r\n";

                                    $comment = new order_comments();
                                    $comment->order_id = $orders->order_id;
                                    $comment->username = 'system';
                                    $comment->comment_visibility = "internal";
                                    $comment->comment = $text;
                                    $comment->create();

                                    $orders_states = new orders_states();
                                    $orders_states->order_id = $orders->order_id;
                                    $orders_states->state_name = 'on hold';
                                    $orders_states->create();

                                    $orders->status = 'on hold';
                                    $orders->save();
                                } else {


                                    // Determine username
                                    $so = new service_attributes();
                                    $so->service_id = $orders->service_id;
                                    $so->param = 'username';
                                    $so->get_attribute();

                                    //$username = $so->value;

                                    $so2 = new service_attributes();
                                    $so2->service_id = $orders->service_id;
                                    $so2->param = 'realms';
                                    $so2->get_attribute();

                                    $username = $so->value . '@' . $so2->value;


                                    // Update radius

                                    $radreply = new radius_reply();
                                    $radreply->username = $username;
                                    $radreply->attribute = 'Framed-IP-Address';
                                    $radreply->op = ':=';
                                    $radreply->value = $ip;

                                    if (!$radreply->create()) {

                                        // Did not create
                                        $text = "Unable to write static ip $ip to radius" . "\r\n";

                                        $comment = new order_comments();
                                        $comment->order_id = $orders->order_id;
                                        $comment->username = 'system';
                                        $comment->comment_visibility = "internal";
                                        $comment->comment = $text;
                                        $comment->create();

                                        $orders_states = new orders_states();
                                        $orders_states->order_id = $orders->order_id;
                                        $orders_states->state_name = 'on hold';
                                        $orders_states->create();

                                        $orders->status = 'on hold';
                                        $orders->save();
                                    } else {
                                        $text = "Static IP Assigned: $ip" . "\r\n";

                                        $comment = new order_comments();
                                        $comment->order_id = $orders->order_id;
                                        $comment->username = 'system';
                                        $comment->comment_visibility = "customer";
                                        $comment->comment = $text;
                                        $comment->create();

                                        $staticip->ip = $ip;
                                        $staticip->service_id = $orders->service_id;
                                        $staticip->save();

                                        $orders_states = new orders_states();
                                        $orders_states->order_id = $orders->order_id;
                                        $orders_states->state_name = 'closed';
                                        $orders_states->create();

                                        $orders->status = 'closed';
                                        $orders->save();
                                    }
                                }
                            } // End static ip
                        } // End go = 1
                    } // ENd addon creation task
                }
            }

        } else if ($orders->status == 'accepted') {

            if ($orders->action == 'new' || $orders->action == 'cancel') {

            } // End action = new || action = cancel
        } // End state = Accepted
                    */
                }}}
    } // ENd type 1 or 2 check
} // ENd while loop through orders

function get_attribute($order_id, $param)
{

    $email_order_attr = new order_attributes();
    $email_order_attr->order_id = $order_id;
    $email_order_attr->param = $param;
    $email_order_attr->get_latest();

    return $email_order_attr->value;
}

function prettyXML($xml, $debug = false)
{
    // add marker linefeeds to aid the pretty-tokeniser
    // adds a linefeed between all tag-end boundaries
    $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

    // now pretty it up (indent the tags)
    $tok = strtok($xml, "\n");
    $formatted = ''; // holds pretty version as it is built
    $pad = 0; // initial indent
    $matches = array(); // returns from preg_matches()

    /* pre- and post- adjustments to the padding indent are made, so changes can be applied to
    * the current line or subsequent lines, or both
    */
    while ($tok !== false) { // scan each line and adjust indent based on opening/closing tags

        // test for the various tag states
        if (preg_match('/.+<\/\w[^>]*>$/', $tok, $matches)) { // open and closing tags on same line
            if ($debug) echo " =$tok= ";
            $indent = 0; // no change
        } else if (preg_match('/^<\/\w/', $tok, $matches)) { // closing tag
            if ($debug) echo " -$tok- ";
            $pad--; //  outdent now
        } else if (preg_match('/^<\w[^>]*[^\/]>.*$/', $tok, $matches)) { // opening tag
            if ($debug) echo " +$tok+ ";
            $indent = 1; // don't pad this one, only subsequent tags
        } else {
            if ($debug) echo " !$tok! ";
            $indent = 0; // no indentation needed
        }

        // pad the line with the required number of leading spaces
        $prettyLine = str_pad($tok, strlen($tok) + $pad, ' ', STR_PAD_LEFT);
        $formatted .= $prettyLine . "\n"; // add to the cumulative result, with linefeed
        $tok = strtok("\n"); // get the next token
        $pad += $indent; // update the pad size for subsequent lines
    }
    return $formatted; // pretty format
}


function soapDebug($client)
{

    $requestHeaders = $client->__getLastRequestHeaders();
    $request = prettyXml($client->__getLastRequest());
    $responseHeaders = $client->__getLastResponseHeaders();
    $response = prettyXml($client->__getLastResponse());

    echo '<code>' . nl2br(htmlspecialchars($requestHeaders, true)) . '</code>';
    echo highlight_string($request, true) . "<br/>\n";

    echo '<code>' . nl2br(htmlspecialchars($responseHeaders, true)) . '</code>' . "<br/>\n";
    echo highlight_string($response, true) . "<br/>\n";
}
