<?php

class AlgoPlaylistKonbini
{
    protected $jwp_API;
    protected $playlistTag;
    protected $channelKey;
    protected $daysInterval;
    protected $videosNb;
    protected $channelExist;
    protected $logs = [];

    /**
     * @param mixed $channelExist
     */
    public function setChannelExist($channelExist)
    {
        $this->channelExist = $channelExist;
    }

    /**
     * @param string $playlistTag
     */
    public function setPlaylistTag($playlistTag)
    {
        if (empty($playlistTag) || !is_string($playlistTag)) {
            $playlistTag = "playlist Konbini (default)";
            $this->setterError();
        }

        $this->playlistTag = trim($playlistTag);
        $this->jwp_API->call("channels/update", array("channel_key" => $this->channelKey, "tags" => $this->playlistTag));
    }

    /**
     * @param string $channelKey
     */
    public function setChannelKey($channelKey)
    {
        if (empty($channelKey)) {
            $this->endScript("error");
        }

        $this->channelKey = $channelKey;
    }

    /**
     * @param int $daysInterval
     */
    public function setDaysInterval($daysInterval)
    {
        if ($daysInterval < 1) {
            $this->setterError();
            $daysInterval = 7;
        }
        $this->daysInterval = $daysInterval;
    }

    /**
     * @param int $videosNb
     */
    public function setVideosNb($videosNb)
    {
        if ($videosNb < 1) {
            $videosNb = 10;
            $this->log("tag invalid, default tag applied :" . $videosNb, __FUNCTION__);
        }

        $this->videosNb = $videosNb;
    }


    function __construct()
    {
        global $secret;
        global $key;
        global $videosNb;
        global $daysInterval;
        global $playlistTag;
        global $channelKey;

        $this->jwp_API = new JWPAPI($key, $secret);
        $this->setChannelKey($channelKey);
        $this->setVideosNb($videosNb);
        $this->setPlaylistTag($playlistTag);
        $this->setDaysInterval($daysInterval);
        $this->verifyCredentials();
    }

    // --> Methods -->

    public function refreshPlaylist () {
        // log settings and playlist state before any changes are made
        $this->logPlaylist("initial playlist");

        // interaction with the API to refresh playlist
        $this->emptyPlaylist();
        $this->fillPlaylist();

        $this->endScript("success");
    }

    protected function verifyCredentials () {
        // dummy call
        $call = $this->jwp_API->call("channels/show", array("channel_key" => $this->channelKey));

        // if the dummy return an error, end script
        if ($call["status"] === "error" ) {
            $this->log($call, __FUNCTION__);
            // special case when the error concerns the channel_key
            if (strpos($call["message"], "channel_key")){
                $this->setChannelExist(false);
            }
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

        $this->logPlaylist('playlist AFTER ' . __FUNCTION__);
    }


    // return playlist with the settings provided in settings.php
     function getPlaylist () {
         $currentPlaylist = $this->jwp_API->call("channels/videos/list", array(
             "channel_key" => $this->channelKey,
             "result_limit" => 50));

         return $currentPlaylist["videos"];
    }

    protected function getLastVideos($startDate) {
        $videos = $this->jwp_API->call("videos/list", array (
            "start_date" => $startDate,
            "statuses_filter" => "ready"));
        return $videos;
    }

    protected function addTagToVideo ($videoKey, $oldTag) {
        if (strpos($this->playlistTag, $oldTag)) {
            return ;
        }
        $this->jwp_API->call("/videos/update", array(
            "video_key" => $videoKey,
            "tags" => $oldTag . ', ' . $this->playlistTag));
    }

    protected function deleteTag ($videoKey, $oldTag) {
        // if oldTag doesn't contain the tag, abort
        if (strpos($this->playlistTag, $oldTag)) {
            return ;
        }

        // reconstruct clean tags
        $tags = explode(", ", $oldTag);
        unset($tags[array_search($this->playlistTag, $tags)]);
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
        $remaining = $this->videosNb;
        $videos = [];

        $fast = $this->findSpecificVideo("fast", 100);

        $recentsVideos = $this->jwp_API->call(
            "/videos/list", array(
                "start_date" => $this->getStartDate(),
                "statuses_filter" => "ready",
                "result_limit" => $remaining));
        $videos = array_merge($videos, $recentsVideos["videos"]);

        // if recentsVideos are not enought to fill the playlist, grap random fast & curious videos.
        if (count($videos) < $this->videosNb) {
            $remaining = $this->videosNb - count($videos);
            for ($i = 0; $i < $remaining; $i++){
                array_push($videos, $fast["videos"][rand(0, $fast["total"])]);
            }
        }

        $this->log($this->getParameterOutOfAPIResponse($videos, "title"), "selected videos AFTER " . __FUNCTION__);
        return $videos;
    }

    function fillPlaylist () {
        $videoSelection = $this->selectVideos();

        foreach ($videoSelection as $v) {
            $this->addTagToVideo($v["key"], $v["tags"]);
        }

        $this->logPlaylist("updated playlist AFTER " . __FUNCTION__);
    }

    // return timestamp of (TODAY - daysInterval)
    protected function getStartDate () {
        return strtotime( '-' . $this->daysInterval . ' day', time());
    }

    protected function getParameterOutOfAPIResponse ($array, $apiParameter) {
        $result = [];
        foreach ($array as $value) {
            $result[] = $value[$apiParameter];
        }

        return $result;
    }

    // -------- LOGS METHODS

    protected function setterError () {
        $this->log( debug_backtrace()[1]['function'] . " invalid, default will be applied.", "Setting error");
    }

    protected function logPlaylist ($message) {
        $this->log($this->getParameterOutOfAPIResponse($this->getPlaylist(), "title"), $message);
    }

    protected function log ($data, $name) {
        global $argv;
        if (in_array( "-v", $argv)) {
            $this->printLog($data, $name);
        }
        array_push($this->logs, [$data, $name]);

        $this->logs[] = $data;
    }

    protected function printLog($data, $name) {
        echo " --- " . $name . " ---" . PHP_EOL;
        print_r($data);
    }

    // compile settings, return an array
    protected function getSettings() {
        $settings =  [
            "tag" => $this->playlistTag,
            "frequency of update" => "every " . $this->daysInterval . " days",
            "playlist ID" => $this->channelKey,
            "number of videos in playlist" => $this->videosNb
        ];
        return $settings;
    }

    function getLogs () {
        return print_r($this->logs);
    }

    protected function endScript($status) {
        if ($this->channelExist === true) {
            $playlist = $this->getParameterOutOfAPIResponse($this->getPlaylist(), "title");
        } else {
            $playlist = "playlist not found, please double check \$channelKey in settings.php";
        }

        // basic response after the script run
        $response = [
            "date" => time(),
            "status" => $status,
            "settings" => $this->getSettings(),
            "resulting_playlist" => $playlist,
        ];

        $this->log($response, "result");

        // always print the basic response, despite a lack of verbose option.
        global $argv;
        if (!in_array( "-v", $argv)) {
            print_r($response);
        }

        if ($status === "error") {
            die();
        }
    }
}