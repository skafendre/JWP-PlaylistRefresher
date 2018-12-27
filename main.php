<?php
header('Content-type: text/plain; charset=utf-8');

require_once('api.php');
require_once('class/AlgoPlaylistKonbini.class.php');
// Thumbnail upload example; again replace zzzz with your video key.
/*
$response = $jwp_api->call("/videos/thumbnails/update", array('video_key' => 'zzzzzzzz'));
if ($response['status'] == "error") {
    print_r($response);
} else {
    $response = $jwp_api->upload($response['link'], "./thumbnail.jpg");
    print_r($response);
}
*/
$algo = new AlgoPlaylistKonbini('hFhUg05J', '3RkF3cAHfiK1mB3b4jHMAc5h');
$algo->mainLogic();
