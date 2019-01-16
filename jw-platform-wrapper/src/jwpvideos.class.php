<?php

require_once 'jwpbase.php';

class JWPVideos extends JWPBase {
    function fetch ($limit = self::DEF_LIMIT) {
        return $this->jwp_API->call(
            "/videos/list", array(
            "statuses_filter" => "ready",
            "result_limit" => $limit,
        ));
    }

    function fetchByStartDate ($startDate, $limit = self::DEF_LIMIT) {
        return $this->jwp_API->call(
            "/videos/list", array(
            "statuses_filter" => "ready",
            "result_limit" => $limit,
            "start_date" => $startDate
        ));
    }

    function fetchByEndDate ($endDate, $limit = self::DEF_LIMIT) {
        return $this->jwp_API->call(
            "/videos/list", array(
            "statuses_filter" => "ready",
            "result_limit" => $limit,
            "start_date" => $endDate
        ));
    }

    function fetchById ($id) {
        return $this->jwp_API->call(
            "/videos/show", array(
            "video_key" => $id
        ));
    }

    function fetchVideoByKeyword ($keyword, $limit = self::DEF_LIMIT, $orderBy = "views:asc") {
        return $this->jwp_API->call("/videos/list", array(
        "statuses_filter" => "ready",
        "search" => $keyword,
        "order_by" => $orderBy,
        "result_limit" => $limit,));
    }

    function setTags ($id, $tags) {
        $this->jwp_API->call(
            "/videos/show", array(
            "video_key" => $id,
            "tags" => $tags
        ));
    }

    function delete ($id) {
        $this->jwp_API->call(
            "/videos/delete", array(
            "video_key" => $id
        ));
    }

    function setURL ($id, $url) {
        $this->jwp_API->call(
            "/videos/update", array(
            "video_key" => $id,
            "link" => $url
        ));
    }

    function setTitle ($id, $title) {
        $this->jwp_API->call(
            "/videos/update", array(
            "video_key" => $id,
            "title" => $title
        ));
    }
}