<?php

class AlgoPlaylistKonbini
{
    protected $jwp_API;
    protected $playlistTag;
    protected $channelKey;
    protected $daysInterval;
    protected $videosNb;
    protected $logs = [];

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
            trigger_error("Fatal error, invalid channel key, no playlist found.", 256);
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
    }

    // --> Methods -->

    public function refreshPlaylist () {
        // logs for setting and the playlist state before any changes are made
        $this->printSettings();
        $this->logPlaylist("initial playlist");

        // interaction with the API to refresh playlist
        $this->emptyPlaylist();
        $this->fillPlaylist();
    }

    function emptyPlaylist () {
        $currentPlaylistVideos = $this->getPlaylist();

        foreach ($currentPlaylistVideos as $v) {
            $this->deleteTag($v["key"], $v["tags"]);
        }

        $this->logPlaylist('playlist AFTER ' . __FUNCTION__);
    }


    // return playlist from JWPlatform API, json
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
            "result_limit" => $limit,));
    }

    protected function selectVideos() {
        $remaining = $this->videosNb;
        $videos = [];

        $fast = $this->findSpecificVideo("fast", 150);

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

        $this->logPlaylist("selected videos AFTER " . __FUNCTION__);
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

    protected function printSettings() {
        $settings =  [
            "tag" => $this->playlistTag,
            "frequency of update" => "every " . $this->daysInterval,
            "playlist ID" => $this->channelKey,
            "number of videos in playlist" => $this->videosNb
        ];
        $this->printLog($settings, "Settings Used :");
    }

    function getLogs () {
        return print_r($this->logs);
    }
}