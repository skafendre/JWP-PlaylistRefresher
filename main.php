<?php

require_once('class/playlistrefresherviatags.class.php');
require_once('credentials.php');

$algo = new PlaylistRefresherByTags("dev"); // a config param here overwrite config argument sent in console
// to send config in the console -> -s dev
// to get verbose option -> -v
$algo->refreshPlaylist();


