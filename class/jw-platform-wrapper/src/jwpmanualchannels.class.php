<?php

class JWPManualChannels extends JWPBase{
    function addVideo ($playlistId, $videoId, $position = 0) {
        $this->jwp_API->call(
            "/channels/videos/create", array(
            "channel_key" => $playlistId,
            "video_key" => $videoId,
            "position" => $position,
        ));
    }

    function deleteVideo ($playlistId, $videoId) {
        $this->jwp_API->call(
            "/channels/videos/create", array(
            "channel_key" => $playlistId,
            "video_key" => $videoId,
        ));
    }

    function changeVideoPosition ($channelId, $oldPos, $newPos) {
        $this->jwp_API->call(
           "/channels/videos/update", array (
               "channel_key" => $channelId,
               "position_from" => $oldPos,
               "position_to" => $newPos
        ));
    }
}