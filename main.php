<?php
header('Content-type: text/plain; charset=utf-8');

require_once('class/api.class.php');
require_once('class/AlgoPlaylistKonbini.class.php');
require_once('settings.php');
require_once('credentials.php');

// logs to be filled by the AlgoPlaylistKonbini, output in console with -v parameter
$logs = [];

$algo = new AlgoPlaylistKonbini();
$algo->refreshPlaylist();

