# refresh-jwplayer-playlist

A PHP class that refresh our jwplayer playlists via the JWPlatform API.
doc : https://developer.jwplayer.com/jw-platform/reference/v1/

Standalone API calls, doesn't interact with frond-end or DB.

Project composed of :

1) api.php (API kit written in php provided by JWPlatform.)

2) AlgoPlaylistKonbini.class.php
    Class which dictacte API calls in order to select and refresh a playlist.
    Use settings.php to set $videosNb, $daysInterval, $playlistTag, $channelKey.
    $daysInterval needs to be implemented with a cron in order to be effective.

3) main.php
    Creation of an AlgoPlaylistKonbini object, which update the playlist.
    Executing the script with "-v" to display more log in the console.
    Logs saved in log/log.txt, find update via timestamp.