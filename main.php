<?php
header('Content-type: text/plain; charset=utf-8');

require_once('api.php');
require_once('class/AlgoPlaylistKonbini.class.php');
// credentials

//$jwp_api = new JWPAPI('hFhUg05J', '3RkF3cAHfiK1mB3b4jHMAc5h');

// Here's an example call that lists all videos.
//print_r($jwp_api->call("/videos/list"));

//print_r($jwp_api->call("/channels/list"));

// show video, take video_key
//print_r($jwp_api->call("/videos/show", array('video_key' => '2bmDj8VM')));

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
print_r($algo->getVideoDetail('XPii5F8f'));