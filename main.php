<?php

require_once('algoplaylistkonbini.class.php');
require_once('settings.php');
require_once('credentials.php');

$algo = new AlgoPlaylistKonbini();
$algo->refreshPlaylist();

//$testOutput = $test->videos->fetch(10);
//$testOutput = $test->channels->fetch("JKltxRtB");
//print_r($testOutput);

//print_r($test->videos->fetch("10"));

