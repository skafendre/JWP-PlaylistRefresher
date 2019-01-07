<?php

class AlgoPlaylistKonbini
{
    protected $jwp_API;
    protected $playlistTag;
    protected $channelKey;
    protected $daysInterval;
    protected $videosNb;

    protected $channelExist;
    protected $response = [];

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
            $this->setterError("tag");
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
            $this->setterError("days_interval");
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
            $this->setterError("videos_number");
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
        $this->response["initial_playlist"] = $this->getParameterOutOfAPIResponse($this->getPlaylist(), "title");
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
            $this->consoleLog($call, __FUNCTION__);
            // special case when the error concerns the channel_key
            if (strpos($call["message"], "channel_key")){
                $this->setChannelExist(false);
            }
            $this->endScript("error");
            $this->setterError($call["message"]);
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
            "statuses_filter" => "ready",
            "order_by" => "views:asc",
        ));
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

        $this->consoleLog($this->getParameterOutOfAPIResponse($videos, "title"), "selected videos AFTER " . __FUNCTION__);
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

    // log error into the response array
    protected function setterError ($errorOrigin) {
        $this->response["errors"][$errorOrigin] = debug_backtrace()[1]['function'] . " provided with invalid parameter, default [" . $errorOrigin . "] used.";
    }

    protected function logPlaylist ($message) {
        $this->consoleLog($this->getParameterOutOfAPIResponse($this->getPlaylist(), "title"), $message);
    }

    protected function consoleLog ($data, $name) {
        global $argv;
        if (in_array( "-v", $argv)) {
            $this->printConsoleLog($data, $name);
        }
    }

    protected function printConsoleLog($data, $name) {
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
        $this->response = array_merge($this->response, $completeResponse);

        $this->printLogInFile();

        // always print a basic response, despite a lack of verbose option.
        global $argv;
        if (!in_array( "-v", $argv)) {
            if (array_key_exists("errors", $this->response)) {
                foreach($this->response["errors"] as $key=>$value) {
                    echo "[" . $key . "] error : " . $value, " \n";
                }
            }
            echo "Refresh status : " . $this->response["status"] . ", timestamp : " . $this->response["timestamp"];
        } else {
            echo $this->formatLogs();
        }

        if ($status === "error") {
            die();
        }
    }

    protected function formatLogs () {
        $formatedContents = $this->response["date"] . ", " . $this->response["timestamp"] . " \n";
        foreach ($this->response as $key => $value) {
            if (is_array($value)) {
                $formatedContents .= trim($key). "=> array : \n";
                foreach ($value as $key1 => $value1) {
                    $formatedContents .= "\t " . trim($key1) . " => " . trim($value1) . " \n";
                }
            } else {
                $formatedContents .= trim($key) . " => " . trim($value) . " \n ";
            }
        }
        return $formatedContents;
    }

    // create log.txt and the folder if they don't exist, then print log to the file.
    protected function printLogInFile () {
        if (!file_exists('log/')) {
            mkdir('log/', 0777, true);
        }
        file_put_contents("log/log.txt", $this->formatLogs() ."\n \n", FILE_APPEND);
    }
}