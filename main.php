<?php
header('Content-type: text/plain; charset=utf-8');

require_once('api.php');
require_once('class/AlgoPlaylistKonbini.class.php');
require_once ('credentials.php');

// error handling
function customError($error_level, $error_message) {
    echo "<b>Error:</b> [$error_level] $error_message";
}
set_error_handler("customError", E_USER_NOTICE);

// credentials JWPlatform

$playlistTag = 'playlist Konbini';
$channelKey = 'GOHRhAkf';
$daysInterval = 7;
$videosNb = 10;

$algo = new AlgoPlaylistKonbini($key, $secret, $videosNb, $daysInterval, $playlistTag, $channelKey);
$algo->mainLogic();
