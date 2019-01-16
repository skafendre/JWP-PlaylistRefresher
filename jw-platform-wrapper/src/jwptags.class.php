<?php
require_once 'jwpbase.php';

class JWPTags extends JWPBase {
    function fetch ($limit = self::DEF_LIMIT) {
        return $this->jwp_API->call("accounts/tags/list", array(
            "result_limit" => $limit
        ));
    }

    function create ($name) {
        $this->jwp_API->call("accounts/tags/create", array(
            "name" => $name
        ));
    }

    function delete ($name) {
        $this->jwp_API->call("accounts/tags/delete", array(
            "name" => $name
        ));
    }

    function fetchTagUse ($name) {
        return $this->jwp_API->call("accounts/tags/show", array(
            "name" => $name
        ));
    }
}
