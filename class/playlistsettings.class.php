<?php
class PlaylistSettings {
    public $playlistTag;
    public $channelKey;
    public $daysInterval;
    public $videosNb;

    function getSettings () {
        return $settings = [
            "playlist_tag" => $this->playlistTag,
            "playlist_id" => $this->channelKey,
            "videos_number" => $this->videosNb,
            "update_frequency" => $this->daysInterval
        ];
    }

    public function getCheckedSettings () {
        $invalidSettings = [];

        if (empty($this->playlistTag)) {
            $invalidSettings["playlist_tag"] = "Invalid playlist tag";
        }

        if (empty($this->channelKey) ) {
            $invalidSettings["playlist_id"] = "Invalid playlist ID";
        }

        if (empty($this->videosNb) || !is_int($this->videosNb) || $this->videosNb <= 0) {
            $invalidSettings["video_number"] = "Invalid videos number";
        }

        if (empty($this->daysInterval) || !is_int($this->daysInterval) ||$this->daysInterval <= 0) {
            $invalidSettings["update_frequency"] = "Invalid update frequency";
        }

        // "areValid" is checked by CheckSettings in AlgoPlaylistKonbini
        if (count($invalidSettings) >= 1) {
            return array ("areValid" => false, $invalidSettings);
        } else {
            return array ("areValid" => true, $this->getSettings());
        }
    }
}