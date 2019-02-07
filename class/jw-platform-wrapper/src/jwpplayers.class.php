<?php
require_once 'jwpbase.php';

class JWPPlayers extends JWPBase {

    function fetch ($limit = self::DEF_LIMIT) {
        return $this->jwp_API->call("players/list", array(
            "result_limit" => $limit,
        ));
    }

    function fetchById ($id) {
        return $this->jwp_API->call("players/show", array(
            "player_key" => $id
        ));
    }

    function delete ($id) {
        $this->jwp_API->call("players/delete", array(
            "player_key" => $id
        ));
    }

    function setTitle ($id, $title) {
        $this->jwp_API->call("players/update", array(
            "player_key" => $id,
            "name" => $title
        ));
    }

    function setDimensions ($id, $width, $height) {
        $this->jwp_API->call("players/update", array(
            "player_key" => $id,
            "width" => $width,
            "height" => $height
        ));
    }

    function setDisplayTitle ($id, boolean $displayTitle) {
        $this->jwp_API->call("players/update", array(
            "player_key" => $id,
            "displaytitle" => $displayTitle
        ));
    }

    function setDisplayDescription ($id, bool $displayDescription) {
        $this->jwp_API->call("players/update", array(
            "player_key" => $id,
            "displaydescription" => $displayDescription
        ));
    }

    function setAutoStart ($id, \parameters\player\autostart\PlayerAutoStart $autostart) {
        $this->jwp_API->call("players/update", array(
            "player_key" => $id,
            "autostart" => $autostart
        ));
    }

    function setMuteOnStart ($id, $isMute) {
        $this->jwp_API->call("players/update", array(
            "player_key" => $id,
            "mute" => $isMute
        ));
    }

    function setRelatedVideos ($id, $relatedVideos) {
        $this->jwp_API->call("players/update", array(
            "player_key" => $id,
            "related_videos" => $relatedVideos
        ));
    }

    function setCookies ($id, $cookies) {
        $this->jwp_API->call("players/update", array(
            "player_key" => $id,
            "cookies" => $cookies
        ));
    }

    function setSharing ($id, $sharing) {
        $this->jwp_API->call("players/update", array(
            "player_key" => $id,
            "sharing" => $sharing
        ));
    }
 }