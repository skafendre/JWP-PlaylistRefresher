<?php
require_once 'src/jwpvideos.class.php';
require_once 'src/jwpchannels.class.php';
require_once 'src/jwpplayers.class.php';
require_once 'src/jwptags.class.php';
require_once 'src/jwpconnect.class.php';

class JWPWrapper {
    public $videos;
    public $channels;
    public $players;
    public $tags;
    public $connect;

    function __construct()
    {
        $this->videos = new JWPVideos();
        $this->channels = new JWPChannels();
        $this->players = new JWPPlayers();
        $this->tags = new JWPTags();
        $this->connect = new JWPConnect();
    }
}