<?php
header('Content-type: text/plain; charset=utf-8');

require_once('class/api.class.php');
require_once('class/AlgoPlaylistKonbini.class.php');
require_once('settings.php');
require_once('credentials.php');

$algo = new AlgoPlaylistKonbini();
$algo->refreshPlaylist();

