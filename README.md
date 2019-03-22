# refresh-jwplayer-playlist

A PHP class that refresh jwplayer playlists via the JWPlatform API.
doc : https://developer.jwplayer.com/jw-platform/reference/v1/

Standalone API calls, doesn't interact with frond-end or DB.
Tags and dynamics channels implementation.  

Project composed of :

1) api.php (API kit written in php provided by JWPlatform.)

2) playlistrefresherviatags.class.php
    Class which dictacte API calls in order to select and refresh a playlist.

3) main.php
    Executing the script with "-v" to get verbose log.
    Execute the script with "-s" followed by a settings objects defined in settings.json.
    Logs saved in log/log.txt.
    
4) credentials.php & settings.json
    User settings.
    
