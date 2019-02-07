<?php
require_once "class/playlistsettings.class.php";
$playlistSettings = new PlaylistSettings();

/*
// SETTINGS TEMPLATE
$playlistSettings->playlistTag = "PLAYLIST TAG (string)";
$playlistSettings->channelKey = "PLAYLIST ID HERE (string)";
$playlistSettings->daysInterval = "FREQUENCY OF UPLOAD (int)";
$playlistSettings->videosNb = "NUMBER OF VIDEOS (int);
*/

//// prod settings
//$playlistSettings->playlistTag = "playlist Konbini";
//$playlistSettings->channelKey = 'GOHRhAkf';
//$playlistSettings->daysInterval = 7;
//$playlistSettings->videosNb = 10;

//// dev settings
//$playlistSettings->playlistTag = "algo test bot";
//$playlistSettings->channelKey = 'JKltxRtB';
//$playlistSettings->daysInterval = 7;
//$playlistSettings->videosNb = 10;

// wrong settings
//$playlistSettings->playlistTag = "test";
//$playlistSettings->channelKey = 'zds';
//$playlistSettings->daysInterval = -1;
//$playlistSettings->videosNb = 10;


