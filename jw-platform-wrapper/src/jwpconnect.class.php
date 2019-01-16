<?php
require_once 'jwpbase.php';

class JWPConnect extends JWPBase {
    function fetchAPIStatus () {
        return $this->jwp_API->call('/status');
    }
}