<?php

/*
 * Error functions in php ?
 * ||--->
 * Serializer like separate class for API calls
 * ||--->
 * PHP default variables in functions
 * ||--->
 * Logs functions in separate file
 * ||---> partially done
 * Settings verifications in a separate file and/or in a separate function? -> in a class, which verify?
 * ||--->
 * Verify protected/private/public
 * ||--->
 * MVC oriented code logic
 * ||--->
 */
require_once "logger.class.php";
require_once  "jwplatformwrapper.class.php";

class AlgoPlaylistKonbini
{
    protected $logger;
    protected $playlistSettings;
    protected $jwp_API;

    protected $channelExist;

    /**
     * @param mixed $channelExist
     */
    public function setChannelExist($channelExist)
    {
        $this->channelExist = $channelExist;
    }

    function __construct()
    {
        global $secret;
        global $key;
        global $playlistSettings;

        $this->playlistSettings = $playlistSettings;
        $this->logger = new Logger();

        $this->jwp_API = new JWPAPI($key, $secret);
        $this->processCheckedSettings($this->playlistSettings->checkAndGetSettings());
        $this->verifyCredentials();
    }

    // --> Methods -->

    public function processCheckedSettings ($checkedSettings) {
        if ($checkedSettings["isValid"] === false) {
            $this->endScript("error");
        }
    }

    public function refreshPlaylist () {
        // log settings and playlist state before any changes are made
        $this->logger->logs["initial_playlist"] = $this->getParameterOutOfAPIResponse($this->getPlaylist(), "title");

        // interaction with the API to refresh playlist
        $this->emptyPlaylist();
        $this->fillPlaylist();

        $this->endScript("success");
    }

    protected function verifyCredentials () {
        // dummy call
        $call = $this->jwp_API->call("channels/show", array("channel_key" => $this->playlistSettings->channelKey));

        // if the dummy return an error, end script
        if ($call["status"] === "error" ) {
            $this->logger->consoleLog($call, __FUNCTION__);
            // special case when the error concerns the channel_key
            if (strpos($call["message"], "channel_key")){
                $this->setChannelExist(false);
            }
            $this->logger->logs["errors"][$call["code"]] = $call["message"];
            $this->endScript("error");
        } else {
            $this->channelExist = true;
        }
    }

    protected function emptyPlaylist () {
        $currentPlaylistVideos = $this->getPlaylist();

        foreach ($currentPlaylistVideos as $v) {
            $this->deleteTag($v["key"], $v["tags"]);
        }

    }

    // return playlist with the settings provided in settings.php
     function getPlaylist () {
         $currentPlaylist = $this->jwp_API->call("channels/videos/list", array(
             "channel_key" => $this->playlistSettings->channelKey,
             "result_limit" => 50));

         return $currentPlaylist["videos"];
    }

    protected function getLastVideos($startDate) {
        $videos = $this->jwp_API->call("videos/list", array (
            "start_date" => $startDate,
            "statuses_filter" => "ready",
            "order_by" => "views:asc",
        ));
        return $videos;
    }

    protected function addTagToVideo ($videoKey, $oldTag) {
        if (strpos($this->playlistSettings->playlistTag, $oldTag)) {
            return ;
        }

        $this->jwp_API->call("/videos/update", array(
            "video_key" => $videoKey,
            "tags" => $oldTag . ', ' . $this->playlistSettings->playlistTag));
    }

    protected function deleteTag ($videoKey, $oldTag) {
        // reconstruct clean tags
        $tags = explode(", ", $oldTag);
        unset($tags[array_search($this->playlistSettings->playlistTag, $tags)]);
        $newTag = trim(implode(", ", $tags));

        $this->jwp_API->call("/videos/update", array(
            "video_key" => $videoKey,
            "tags" => $newTag));
    }

    protected function findVideos ($limit, $startDate) {
       return $this->jwp_API->call("/videos/list", array(
           "start_date" => $startDate,
           "statuses_filter" => "ready",
           "result_limit" => $limit));
    }

    protected function findSpecificVideo ($category, $limit) {
        return $this->jwp_API->call("/videos/list", array(
            "statuses_filter" => "ready",
            "search" => $category,
            "order_by" => "views:asc",
            "result_limit" => $limit,));
    }

    protected function selectVideos() {
        $remaining = $this->playlistSettings->videosNb;
        $videos = [];

        $fast = $this->findSpecificVideo("fast", 100);

        $recentsVideos = $this->jwp_API->call(
            "/videos/list", array(
                "start_date" => $this->getStartDate(),
                "statuses_filter" => "ready",
                "result_limit" => $remaining));
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
            $this->addTagToVideo($v["key"], $v["tags"]);
        }
    }

    // return timestamp of (TODAY - daysInterval)
    protected function getStartDate () {
        return strtotime( '-' . $this->playlistSettings->daysInterval . ' day', time());
    }

    protected function getParameterOutOfAPIResponse ($array, $apiParameter) {
        $result = [];
        foreach ($array as $value) {
            $result[] = $value[$apiParameter];
        }

        return $result;
    }

    // -------- LOGS METHODS

    // log error into the response array
    protected function setterError ($errorOrigin) {
        $this->logger->logs["errors"][$errorOrigin] = debug_backtrace()[1]['function'] . " provided with invalid parameter, default [" . $errorOrigin . "] used.";
    }
    // compile settings, return an array
    protected function getSettings() {
        $settings =  [
            "tag" => $this->playlistSettings->playlistTag,
            "frequency of update" => "every " . $this->playlistSettings->daysInterval . " days",
            "playlist ID" => $this->playlistSettings->channelKey,
            "number of videos in playlist" => $this->playlistSettings->videosNb
        ];
        return $settings;
    }

    protected function endScript($status) {
        if ($this->channelExist === true) {
            $playlist = $this->getParameterOutOfAPIResponse($this->getPlaylist(), "title");
        } else {
            $playlist = "playlist not found, please double check \$channelKey in settings.php";
        }

        // basic response displayed after the script end
        $completeResponse = [
            "date" => date("F j, Y, g:i a"),
            "timestamp" => time(),
            "status" => $status,
            "settings" => $this->getSettings(),
            "resulting_playlist" => $playlist,
        ];
        $this->logger->logs = array_merge($this->logger->logs, $completeResponse);

        $this->logger->printLogInFile();

        // console response
        global $argv;
        if (!in_array( "-v", $argv)) {
            // non verbose
            if (array_key_exists("errors", $this->logger->logs)) {
                foreach($this->logger->logs["errors"] as $key=>$value) {
                    echo "[" . $key . "] error : " . $value, " \n";
                }
            }
            echo "Refresh status : " . $this->logger->logs["status"] . ", timestamp : " . $this->logger->logs["timestamp"];
        } else {
            // verbose
            echo $this->logger->formatLogs();
        }

        // stop script if it ended with an error
        if ($status === "error") {
            die();
        }
    }
}