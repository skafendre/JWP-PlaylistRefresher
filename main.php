<?php

require_once('class/playlistrefresherviatags.class.php');
require_once('settings.php');
require_once('credentials.php');

$algo = new PlaylistRefresherByTags();
$algo->refreshPlaylist();

