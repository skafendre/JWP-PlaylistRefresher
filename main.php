<?php

require_once('playlistrefresherviatags.php');
require_once('settings.php');
require_once('credentials.php');

$algo = new PlaylistRefresherByTags();
$algo->refreshPlaylist();



