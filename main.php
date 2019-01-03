<?php
header('Content-type: text/plain; charset=utf-8');

require_once('api.php');
require_once('class/AlgoPlaylistKonbini.class.php');
require_once('credentials.php');


$logs = [];

$playlistTag = 'playlist Konbini';
$channelKey = 'GOHRhAkf';
$daysInterval = 7;
$videosNb = 10;

$algo = new AlgoPlaylistKonbini($key, $secret, $videosNb, $daysInterval, $playlistTag, $channelKey);
$algo->mainLogic();
