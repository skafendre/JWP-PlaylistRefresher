<?php

require_once('playlistrefresherviatags.php');
require_once('manual-playlist-refresh.class.php');
require_once('settings.php');
require_once('credentials.php');

//$algo = new PlaylistRefresherByTags();
//$algo->refreshPlaylist();

$manualAlgo = new ManualPlaylistRefresh();
$manualAlgo->refreshPlaylist();

