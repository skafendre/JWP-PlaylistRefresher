<?php
header('Content-type: text/plain; charset=utf-8');

require_once('api.php');
require_once('class/AlgoPlaylistKonbini.class.php');

// credentials JWPlatform
$secret = '3RkF3cAHfiK1mB3b4jHMAc5h';
$key = 'hFhUg05J';

$playlistTag = 'algo test bot';
$channelKey = 'JKltxRtB';
$daysInterval = 7;
$videosNb = 10;

$algo = new AlgoPlaylistKonbini($key, $secret, $videosNb, $daysInterval, $playlistTag, $channelKey);

$algo->mainLogic();
