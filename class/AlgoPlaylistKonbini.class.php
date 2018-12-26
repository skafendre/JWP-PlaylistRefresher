<?php
/**
 * Created by PhpStorm.
 * User: Eliott Lambert
 * Date: 26/12/2018
 * Time: 15:49
 */

class AlgoPlaylistKonbini
{
    protected $jwpAPI;
    protected $playlistTag = 'algo test bot';

    function __construct($key, $secret)
    {
        $this->jwp_API = new JWPAPI($key, $secret);
    }

    // --> Methods -->
    function mainLogic () {
//        $this->addTagToVideo('iTRgaRKz', "placeholder");
    }

    function getVideoDetail ($video_key) {
        return $this->jwp_API->call("/videos/show", array ("video_key" => $video_key));
    }

    function addTagToVideo ($video_key, $old_tags) {
        // need to add old tags too
        // tags are display like (str:) tags => tag1, tag2, tag3
        $this->jwp_API->call("/videos/update", array("video_key" => $video_key, "tags" => $this->playlistTag));
    }

//    function InteractWithYoutube () {
//
//    }

    function refreshPlaylist () {

    }

    function blackList () {

    }

    function updatePlaylist () {

    }

    function repeatProcess () {

    }

}