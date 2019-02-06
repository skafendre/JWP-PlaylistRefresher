<?php

require_once "class/logger.class.php";
require_once "jw-platform-wrapper/jwpwrapper.class.php";

class ManualPlaylistRefresh {
    private $logger;
    private $playlistSettings;
    private $jwpWrapper;

    private $channelExist;

    public function setChannelExist($channelExist)
    {
        $this->channelExist = $channelExist;
    }

    function __construct()
    {
        global $playlistSettings;

        if ($playlistSettings->getCheckedSettings()["areValid"] === false) {
            $this->endScript("error");
        }
        $this->playlistSettings = $playlistSettings;

        $this->jwpWrapper = new JWPWrapper();
        $this->logger = new Logger();
        $this->verifyCredentials();
    }

    public function refreshPlaylist () {
        // log settings and playlist state before any changes are made
        $this->logger->logs["initial_playlist"] = $this->getParameterOutOfAPIResponse($this->jwpWrapper->channels->fetchChannelVideos($this->playlistSettings->channelKey), "title");
        $this->addRecentVideos();

        $this->endScript("success");
    }
    private function verifyCredentials () {
        // dummy call
        $response = $this->jwpWrapper->channels->fetchChannelVideos($this->playlistSettings->channelKey, true);
        // if the dummy return an error, end script
        if ($response["status"] === "error" ) {
            $this->logger->consoleLog($response, __FUNCTION__);
            // special case when the error concerns the channel_key
            if (strpos($response["message"], "channel_key")){
                $this->setChannelExist(false);
            }
            $this->logger->logs["errors"][$response["code"]] = $response["message"];
            $this->endScript("error");
        } else {
            $this->channelExist = true;
        }
    }

    private function addRecentVideos() {
        // channel type checking
        $channelType = $this->jwpWrapper->channels->fetchChannelType($this->playlistSettings->channelKey);
        if ($channelType !== "manual") {
            $this->logger->logs["errors"]["channel_type"] = "Channel is " . $channelType . ". Must be manual";
            $this->endScript("error");
        }

        // take yesterday videos,
        // takeout as much videos needed to make room for the new
        // push videos inside playlist,
        //
    }

    private function getParameterOutOfAPIResponse ($array, $apiParameter) {
        $result = [];
        foreach ($array as $value) {
            $result[] = $value[$apiParameter];
        }
        return $result;
    }

    private function endScript($status) {
        if ($this->channelExist === true) {
            $playlist = $this->getParameterOutOfAPIResponse($this->jwpWrapper->channels->fetchChannelVideos($this->playlistSettings->channelKey), "title");
        } else {
            $playlist = "playlist not found";
        }

        $endScriptLog = [
            "date" => date("F j, Y, g:i a"),
            "timestamp" => time(),
            "status" => $status,
            "settings" => $this->playlistSettings->getSettings(),
            "resulting_playlist" => $playlist,
        ];
        $this->logger->logs = array_merge($this->logger->logs, $endScriptLog);
        $this->logger->printLogInFile();

        $this->logger->displayLogInConsole();

        if ($status === "error") {
            die();
        }
    }
}
