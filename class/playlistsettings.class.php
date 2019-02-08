<?php

class PlaylistSettings {

    function __construct($config)
    {
        $this->settingsConfig = $config;
        $this->useJSONSettings();
    }

    public $settingsConfig;
    public $playlistTag;
    public $channelKey;
    public $daysInterval;
    public $videosNb;
    public $invalidSettings = [];

    function useJSONSettings () {
        $json = json_decode(file_get_contents("settings.json"), true);

        if (is_null($this->settingsConfig)) {
            // choose settings to use with -s in console
            global $argv;

            // if there is no -s argument
            $index = array_search("-s", $argv);
            if (!$index) {
                echo "ERROR! Please specify the settings you want to use preceded by '-s'in console, or by setting the settingsConfig proprety in the PlaylistSettings class \n Possible settingsConfigs in settings.json : ";
                foreach ($json as $key => $value) {
                    echo $key . " | ";
                }
                die();
            }
            if (array_key_exists($index + 1, $argv)) {
                $this->settingsConfig = $argv[$index + 1];
            } else {
                $this->settingsConfig = null;
            }
        }

            if (array_key_exists($this->settingsConfig, $json)) {
                $this->playlistTag = $json[$this->settingsConfig]["playlist_tag"];
                $this->channelKey = $json[$this->settingsConfig]["channel_key"];
                $this->daysInterval = intval($json[$this->settingsConfig]["days_interval"]);
                $this->videosNb = intval($json[$this->settingsConfig]["videos_number"]);
            } else {
                $this->invalidSettings["json_settings"] = "JSON config " . $this->settingsConfig . " doesn't exist.";
            }
    }

    function getSettings () {
        $settings = [
            "playlist_tag" => $this->playlistTag,
            "playlist_id" => $this->channelKey,
            "videos_number" => $this->videosNb,
            "update_frequency" => $this->daysInterval
        ];
        return $settings;
    }

    public function getInvalidsSettings () {
        $this->useJSONSettings();

        if (empty($this->playlistTag)) {
            $this->invalidSettings["playlist_tag"] = "Invalid playlist tag";
        }

        if (empty($this->channelKey || is_null($this->channelKey)) ) {
            $this->invalidSettings["playlist_id"] = "Invalid playlist ID";
        }

        if (empty($this->videosNb) || !is_int($this->videosNb) || $this->videosNb <= 0) {
            $this->invalidSettings["video_number"] = "Invalid videos number";
        }

        if (empty($this->daysInterval) || !is_int($this->daysInterval) ||$this->daysInterval <= 0) {
            $this->invalidSettings["update_frequency"] = "Invalid update frequency";
        }

        // "areValid" is checked by CheckSettings in AlgoPlaylistKonbini
        if (count($this->invalidSettings) >= 1) {
            return array ("areValid" => 0, "settings" => $this->invalidSettings);
        } else {
            return array ("areValid" => 1, "settings" => $this->invalidSettings);
        }
    }
}