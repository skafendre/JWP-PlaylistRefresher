<?php
class AlgoPlaylistKonbini
{
    protected $jwp_API;
    protected $playlistTag;
    protected $channelKey;
    protected $daysInterval;
    protected $videosNb;
    protected $logs;

    /**
     * @param string $playlistTag
     */
    public function setPlaylistTag($playlistTag)
    {
        $this->playlistTag = trim($playlistTag);
        $this->jwp_API->call("channels/update", array("channel_key" => $this->channelKey, "tags" => $this->playlistTag));
    }

    /**
     * @param string $channelKey
     */
    public function setChannelKey($channelKey)
    {
        $this->channelKey = $channelKey;
    }

    /**
     * @param int $daysInterval
     */
    public function setDaysInterval($daysInterval)
    {
        $this->daysInterval = $daysInterval;
    }

    /**
     * @param int $videosNb
     */
    public function setVideosNb($videosNb)
    {
        $this->videosNb = $videosNb;
    }


    function __construct($key, $secret, $videosNb, $daysInterval, $playlistTag, $channelKey)
    {
        $this->jwp_API = new JWPAPI($key, $secret);
        $this->setChannelKey($channelKey);
        $this->setVideosNb($videosNb);
        $this->setPlaylistTag($playlistTag);
        $this->setDaysInterval($daysInterval);
    }

    // --> Methods -->
    public function mainLogic () {
        $this->log($this->getParameterOutOfAPIResponse($this->getPlaylist(), "title"), 'oldPLaylist'); // playlist before change
        $this->emptyPlaylist();
        $this->fillPlaylist();

//        echo "AFTER";
//        print_r($videoSelection);
//        $this->getLogs();
    }

    function emptyPlaylist () {
        $currentPlaylistVideos = $this->getPlaylist();

        foreach ($currentPlaylistVideos as $v) {
            $this->deleteTag($v["key"], $v["tags"]);
        }

        $this->log($this->getParameterOutOfAPIResponse($this->getPlaylist(), "title"), 'emptyPlaylist');
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

        // at least 2 F&C in the playlist
        $fast = $this->findSpecificVideo("fast", 150);
        for ($i = 0; $i < 2; $i++){
            array_push($videos, $fast["videos"][rand(0, $fast["total"])]);
            $remaining--;
            // stop if there is no remaining video slot to be filled
            if ($remaining <= 0) {
                $i = 2;
            }
        }

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

        $this->log($this->getParameterOutOfAPIResponse($videos, "title"), "selectVideos");
        return $videos;
    }

    function fillPlaylist () {
        $videoSelection = $this->selectVideos();

        foreach ($videoSelection as $v) {
            $this->addTagToVideo($v["key"], $v["tags"]);
        }
        $this->log($this->getParameterOutOfAPIResponse($this->getPlaylist(), "title"), "fillPlaylist");
    }

    // return timestamp of (TODAY - daysInterval)
    protected function getStartDate () {
        $startDate = strtotime( '-' . $this->daysInterval . ' day', time());
        return $startDate;
    }

    protected function getParameterOutOfAPIResponse ($array, $apiParameter) {
        $result = [];
        foreach ($array as $value) {
            $result[] = $value[$apiParameter];
        }
        return $result;
    }

    protected function log ($data, $name) {
//        array_push($this->logs, [$data, $name]);
        echo "-------------------- FUNCTION " . $name . " -------------------------";
        print_r($data);
        $this->logs[] = $data;

    }

    function getLogs () {
        return print_r($this->logs);
    }
}