<?php

/**
**************************
** FreeTSP Version: 1.0 **
**************************
** http://www.freetsp.info
** https://github.com/Krypto/FreeTSP
** Licence Info: GPL
** Copyright (C) 2010 FreeTSP v1.0
** A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
** Project Leaders: Krypto, Fireknight.
**/

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_torrenttable.php');

db_connect();
logged_in();

$lang = array_merge(load_language('takerate'));

if (!isset(user::$current))
{
    error_message_center("error",
                         "{$lang['err_rate_fail']}",
                         "{$lang['err_must_login']}");
}

if (!mkglobal("rating:id"))
{
    error_message_center("error",
                         "{$lang['err_rate_fail']}",
                         "{$lang['err_miss_data']}");
}

$id = intval(0 + $id);

if (!$id)
{
    error_message_center("error",
                         "{$lang['err_rate_fail']}",
                         "{$lang['err_inv_id']}");
}

$rating = 0 + $rating;

if ($rating <= 0 || $rating > 5)
{
    error_message_center("error",
                         "{$lang['err_rate_fail']}",
                         "{$lang['err_inv_rating']}");
}

$res = $db->query("SELECT owner
                  FROM torrents
                  WHERE id = " . $db->real_escape_string($id) . "");

$row = $res->fetch_assoc();

if (!$row)
{
    error_message_center("error",
                         "{$lang['err_rate_fail']}",
                         "{$lang['err_no_torrent']}");
}

$res = $db->query("INSERT INTO ratings (torrent, user, rating, added)
                  VALUES ($id, " . $db->real_escape_string(user::$current['id']) . ", $rating, NOW())");

if (!$res)
{
    if ($db->errno == 1062)
    {
        error_message_center("error",
                             "{$lang['err_rate_fail']}",
                             "{$lang['err_already_rated']}");
    }
    else
    {
        error_message_center("error",
                             "{$lang['err_rate_fail']}",
                             "{$lang['err_sql_err']}");
    }
}

$db->query("UPDATE torrents
           SET numratings = numratings + 1, ratingsum = ratingsum + $rating
           WHERE id = " . $db->real_escape_string($id) . "");

$Memcache->delete_value('torrent::details::rating::' . $id);

header("Refresh: 0; url=details.php?id=$id&rated=1");

?>