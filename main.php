<?php

require_once('class/api.class.php');
require_once('class/algoplaylistkonbini.class.php');
require_once('settings.php');
require_once('credentials.php');

$algo = new AlgoPlaylistKonbini();
$algo->refreshPlaylist();

