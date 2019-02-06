<?php
require_once 'src/jwpvideos.class.php';
require_once 'src/jwpchannels.class.php';
require_once 'src/jwpplayers.class.php';
require_once 'src/jwptags.class.php';
require_once 'src/jwpconnect.class.php';
require_once 'src/jwpmanualchannels.class.php';

class JWPWrapper {
    public $videos;
    public $channels;
    public $players;
    public $tags;
    public $connect;
    public $manualchannels;

    function __construct()
    {
        $this->videos = new JWPVideos();
        $this->channels = new JWPChannels();
        $this->players = new JWPPlayers();
        $this->tags = new JWPTags();
        $this->connect = new JWPConnect();
        $this->manualchannels = new JWPManualChannels();
    }
}