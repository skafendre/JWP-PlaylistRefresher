<?php

require_once('playlistrefresherviatags.php');
require_once('settings.php');
require_once('credentials.php');

$algo = new PlaylistRefresherByTags();
$algo->refreshPlaylist();

//$testOutput = $test->videos->fetch(10);
//$testOutput = $test->channels->fetch("JKltxRtB");
//print_r($testOutput);

//print_r($test->videos->fetch("10"));

