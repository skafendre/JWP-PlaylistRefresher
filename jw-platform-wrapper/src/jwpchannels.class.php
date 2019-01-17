<?php
require_once 'jwpbase.php';

class JWPChannels extends JWPBase {
    function fetch ($limit = self::DEF_LIMIT, $orderBy = "title:asc") {
        return $this->jwp_API->call(
            "/channels/list", array(
            "order_by" => $orderBy,
            "result_limit" => $limit
        ));
    }

    function fetchById ($id) {
        return $this->jwp_API->call(
           "/channels/show", array(
               "channel_key" => $id
        ));
    }

    function setVideoMax ($id, $maxVideos) {
        $this->jwp_API->call(
            "/channels/update", array(
            "channel_key" => $id,
            "videos_max" => $maxVideos
        ));
    }

    function setDynamicTags ($id, $tags, $tags_mode = "any") {
        $this->jwp_API->call(
            "/channels/update", array(
            "channel_key" => $id,
            "tags" => $tags,
            "tags_mode" => $tags_mode
        ));
    }

    function setURL ($id, \http\Url $url) {
        $this->jwp_API->call(
            "/channels/update", array(
            "channel_key" => $id,
            "link" => $url
        ));
    }

    function fetchChannelVideos ($id, $includeStatus = false) {
        $response = $this->jwp_API->call("channels/videos/list", array(
            "channel_key" => $id
        ));
        if ($includeStatus === true) {
            return $response;
        } else {
            return $response["videos"];
        }
    }
}
