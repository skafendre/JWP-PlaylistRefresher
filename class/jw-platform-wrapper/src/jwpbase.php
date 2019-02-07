<?php
require_once 'api.class.php';

class JWPBase{
    protected $jwp_API;
    const DEF_LIMIT = 20;

    function __construct()
    {
        global $key;
        global $secret;

        $this->jwp_API = new JWPAPI($key, $secret);
    }
}