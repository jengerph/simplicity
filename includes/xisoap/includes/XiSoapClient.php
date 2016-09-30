<?php

namespace XiSoap;

class XiSoapClient 
{

    private $client;

    /**
     * XiSoapClient constructor.
     * @param ISoapService $service
     * @param $username
     * @param $password
     * @param int $soapVersion
     * @internal param $url
     */
    public function __construct(ISoapService $service, $username, $password, $soapVersion = SOAP_1_1)
    {
        try {
            $wsdl = $service->getWSDL();
            $options = array(
                "uri" => "http://schemas.xmlsoap.org/soap/envelope/",
                "style" => SOAP_RPC,
                "use" => SOAP_ENCODED,
                "soap_version" => $soapVersion,
                "cache_wsdl" => WSDL_CACHE_NONE,
                "connection_timeout" => 15,
                "trace" => true,
                "encoding" => "UTF-8",
                "exceptions" => false,
            );
            $this->client = new \SoapClient($wsdl, $options);
            $this->setClient($this->client);

        } catch (\SoapFault $exception) {
            trigger_error("SOAP Fault: (faultcode: {$exception->faultcode}, faultstring: {$exception->faultstring})", E_USER_ERROR);
        } catch(\Exception $e) {
            trigger_error($e->getMessage());
        }

    }

    /**
     * @param \SoapHeader $header
     */
    public function setHeaders(\SoapHeader $header)
    {
        $this->client->__setSoapHeaders($header);
    }

    /**
     * Call to soap method on non wsdl
     * @param $functionName
     * @param array $arguments
     * @param array|null $options
     * @param null $inputHeaders
     * @param null $outputHeaders
     * @return mixed|string
     */
    public function nonWsdlCall($functionName, Array $arguments, Array $options = null, $inputHeaders = null, $outputHeaders = null)
    {
        $result = "";
        try {
            $result = $this->client->__soapCall($functionName, $arguments, $options, $inputHeaders, $outputHeaders);
        } catch (\SoapFault $exception) {
            trigger_error("SOAP Fault: (faultcode: {$exception->faultcode}, faultstring: {$exception->faultstring})", E_USER_ERROR);
        } catch(\Exception $e) {
            trigger_error($e->getMessage());
        }

        return $result;
    }

    /**
     * Call to soap method on wsdl
     * @param $functionName
     * @param $arguments
     * @return string
     */
    public function wsdlCall($functionName, $arguments)
    {
        $result = "";
        try {
            $result = $this->client->$functionName($arguments);
        } catch (\SoapFault $exception) {
            trigger_error("SOAP Fault: (faultcode: {$exception->faultcode}, faultstring: {$exception->faultstring})", E_USER_ERROR);
        } catch(\Exception $e) {
            trigger_error($e->getMessage());
        }

        return $result;
    }

    /**
     * @param \SoapClient $client
     */
    public function setClient(\SoapClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return \SoapClient
     */
    public function getClient()
    {
        return $this->client;
    }


}
