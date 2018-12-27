<?php
/**
 * Created by PhpStorm.
 * User: Eliott Lambert
 * Date: 26/12/2018
 * Time: 15:49
 */

class AlgoPlaylistKonbini
{
    protected $jwp_API;
    protected $playlistTag = 'algo test bot';
    protected $channelKey = 'JKltxRtB';
    protected $daysInterval = 7;
    protected $videosNb = 10;

    function __construct($key, $secret)
    {
        $this->jwp_API = new JWPAPI($key, $secret);
    }

    // --> Methods -->
    function mainLogic () {

        $videosToAdd = $this->selectVideos();

        // make choosing video to add

        // refresh videos.
//        foreach ($videosToAdd as $v) {
//            $this->addTagToVideo($v["key"], $v["tags"]);
//        }

         $currentPlaylist = $this->getPlaylist();
//        foreach ($currentPlaylist as $v) {
//            $this->deleteTag($v["key"], $v["tags"]);
//        }
    }

    // return playlist from JWPlatform API, json
    function getPlaylist () {
        return $this->jwp_API->call("channels/videos/list", array("channel_key" => $this->channelKey, "result_limit" => 50));
    }

    function getLastVideos($startDate) {
        $videos = $this->jwp_API->call("videos/list", array ("start_date" => $startDate));
        return $videos;
    }

    function addTagToVideo ($videoKey, $oldTag) {
        if (!strpos($this->playlistTag, $oldTag)) {
//            echo "tag is already present - ";
            return ;
        }
        $this->jwp_API->call("/videos/update", array("video_key" => $videoKey, "tags" => $oldTag . ', ' . $this->playlistTag));
    }

    function deleteTag ($videoKey, $oldTag) {
        if (strpos($this->playlistTag, $oldTag)) {
            return ;
        }

        // reconstruct clean tags
        $tags = explode(", ", $oldTag);
        unset($tags[array_search($this->playlistTag, $tags)]);
        $newTag = trim(implode(", ", $tags));

        $this->jwp_API->call("/videos/update", array("video_key" => $videoKey, "tags" => $newTag));
    }

    function findVideos ($limit) {
       return $this->jwp_API->call("/videos/list", array("start_date" => $this->getStartDate(), "statuses_filter" => "ready", "result_limit" => $limit));
    }

    function findSpecificVideo ($category) {
        return $this->jwp_API->call("/videos/list", array("statuses_filter" => "ready", "search" => $category, "result_limit" => 1000));
    }

    function selectVideos() {
        $remaining = $this->videosNb;
        $videos = [];

        $fast = $this->findSpecificVideo("fast");
        for ($i = 0; $i < 2; $i++){
            array_push($videos, $fast["videos"][rand(0, $fast["total"])]);
            $remaining--;
        }

        $recentsVideos = $this->jwp_API->call(
            "/videos/list", array(
                "start_date" => $this->getStartDate(),
                "statuses_filter" => "ready",
                "result_limit" => $remaining));

        $videos = array_merge($videos, $recentsVideos);

        // if recentsVideos are not enought to fill the playlist, grap randome fast & curious videos.
        if (count($videos) < $this->videosNb) {
            $remaining = $this->videosNb - count($videos);
            for ($i = 0; $i < $remaining; $i++){
                array_push($videos, $fast["videos"][rand(0, $fast["total"])]);
                $remaining--;
            }
        }
        return $videos;
    }

    // return timestamp of (TODAY - daysInterval)
    function getStartDate () {
        $startDate = strtotime( '-' . $this->daysInterval . ' day', time());
        return $startDate;
    }
}