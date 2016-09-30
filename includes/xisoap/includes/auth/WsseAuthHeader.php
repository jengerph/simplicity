<?php

namespace XiSoap\Auth;

class WsseAuthHeader extends \SoapHeader {

    private $wss_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    /**
     * WsseAuthHeader constructor.
     * @param $username
     * @param $password
     * @param null $ns
     */
    public function __construct($username, $password, $ns = null) {
        if ($ns) {
            $this->wss_ns = $ns;
        }

        $auth = new \stdClass();
        $auth->Username = new \SoapVar($username, XSD_STRING, NULL, $this->wss_ns, "Username", $this->wss_ns);
        $auth->Password = new \SoapVar($password, XSD_STRING, NULL, $this->wss_ns, "Password", $this->wss_ns);

        $username_token = new \stdClass();
        $username_token->UsernameToken = new \SoapVar($auth, SOAP_ENC_OBJECT, NULL, $this->wss_ns, "UsernameToken", $this->wss_ns);

        $security_sv = new \SoapVar(
            new \SoapVar($username_token, SOAP_ENC_OBJECT, NULL, $this->wss_ns, "UsernameToken", $this->wss_ns),
            SOAP_ENC_OBJECT,
            NULL,
            $this->wss_ns,
            "Security",
            $this->wss_ns
        );

        parent::SoapHeader($this->wss_ns, "Security", $security_sv, true);
    }

}
