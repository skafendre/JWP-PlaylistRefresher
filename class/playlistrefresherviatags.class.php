<?php

require_once "logger.class.php";
require_once("jw-platform-wrapper/jwpwrapper.class.php");

// Refresh a JW playlist with the settings provided in settings.php
class PlaylistRefresherByTags
{
    private $logger;
    public $playlistSettings;
    private $jwpWrapper;

    private $channelExist;

    public function setChannelExist($channelExist)
    {
        $this->channelExist = $channelExist;
    }

    function __construct()
    {
        $this->jwpWrapper = new JWPWrapper();
        $this->logger = new Logger();
        $this->playlistSettings = new PlaylistSettings();

        if ($test = $this->playlistSettings->getInvalidsSettings()["areValid"] === 0) {

            $this->logger->logs["errors"] = $this->playlistSettings->getInvalidsSettings()["settings"];
        }

        $this->verifyCredentials();
    }

    // --> Methods -->

    public function refreshPlaylist () {
        // log settings and playlist state before any changes are made
        $this->logger->logs["initial_playlist"] = $this->getParameterOutOfAPIResponse($this->jwpWrapper->channels->fetchChannelVideos($this->playlistSettings->channelKey), "title");
        $this->emptyPlaylist();
        $this->fillPlaylist();
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

    private function emptyPlaylist () {
        $currentPlaylistVideos = $this->jwpWrapper->channels->fetchChannelVideos($this->playlistSettings->channelKey);
        foreach ($currentPlaylistVideos as $v) {
            // reconstruct clean tags
            $tags = explode(", ",  $v["tags"]);
            unset($tags[array_search($this->playlistSettings->playlistTag, $tags)]);
            $newTag = trim(implode(", ", $tags));

            $this->jwpWrapper->videos->setTags($v["key"], $newTag);
        }
    }

    private function selectVideos() {
        $remaining = $this->playlistSettings->videosNb;
        $videos = [];

        $fast = $this->jwpWrapper->videos->fetchVideoByKeyword("fast", 30);
        $recentsVideos = $this->jwpWrapper->videos->fetchByStartDate($this->getStartDate(), $remaining);

        $videos = array_merge($videos, $recentsVideos["videos"]);

        // if recentsVideos are not enought to fill the playlist, grap random fast & curious videos.
        if (count($videos) < $this->playlistSettings->videosNb) {
            $remaining = $this->playlistSettings->videosNb - count($videos);
            for ($i = 0; $i < $remaining; $i++){
                array_push($videos, $fast["videos"][rand(0, $fast["total"])]);
            }
        }

        $this->logger->consoleLog($this->getParameterOutOfAPIResponse($videos, "title"), "selected videos AFTER " . __FUNCTION__);
        return $videos;
    }

    function fillPlaylist () {
        $videoSelection = $this->selectVideos();

        foreach ($videoSelection as $v) {
            if (strpos($this->playlistSettings->playlistTag, $v["tags"])) {
                return ;
            }

            $newTag = $v["tags"] . ', ' . $this->playlistSettings->playlistTag;
            $this->jwpWrapper->videos->setTags($v["key"], $newTag);
        }
    }

    // return timestamp of (TODAY - daysInterval)
    private function getStartDate () {
        return strtotime( '-' . $this->playlistSettings->daysInterval . ' day', time());
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

        $this->logger->logs = array_merge($endScriptLog, $this->logger->logs);
        $this->logger->printLogInFile();
        
        $this->logger->displayLogInConsole();

        if ($status === "error") {
            die();
        }
    }
}