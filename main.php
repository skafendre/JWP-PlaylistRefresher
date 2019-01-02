<?php
header('Content-type: text/plain; charset=utf-8');

require_once('api.php');
require_once('class/AlgoPlaylistKonbini.class.php');

// error handling
function customError($error_level, $error_message,
 $error_file, $error_line, $error_context) {
    echo "<b>Error:</b> [$error_level] $error_message";
}
set_error_handler("customError", E_USER_NOTICE);

// credentials JWPlatform
$secret = '3RkF3cAHfiK1mB3b4jHMAc5h';
$key = 'hFhUg05J';

$playlistTag = 'playlist Konbini';
$channelKey = 'GOHRhAkf';
$daysInterval = 7;
$videosNb = 10;

$algo = new AlgoPlaylistKonbini($key, $secret, $videosNb, $daysInterval, $playlistTag, $channelKey);
$algo->mainLogic();
