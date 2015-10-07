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
require_once(FUNC_DIR . 'function_bbcode.php');

db_connect(false);
logged_in();

$lang = array_merge(load_language('mytorrents'),
                    load_language('func_vfunctions'),
                    load_language('global'),
                    load_language('func_bbcode'));

site_header("" . security::html_safe(user::$current['username']) . "'{$lang['title_mytorrents']} ");

$where = "WHERE owner = " . user::$current['id'] . " AND banned != 'yes'";

if (($count = $Memcache->get_value('mytorrents::where::' . sha1($where))) === false) {
    $res = $db->query("SELECT COUNT(id)
                       FROM torrents $where");

    $row   = $res->fetch_array(MYSQLI_BOTH);
    $count = (int)$row[0];
    $Memcache->add_value('mytorrents::where::' . sha1($where), $count, 120);
}

if (!$count)
{
    display_message("info",
                    "{$lang['text_no_files']}",
                    "{$lang['text_no_uploads']}");
}
else
{
    list($pagertop, $pagerbottom, $limit) = pager(20, $count, "mytorrents.php?");

    $res = $db->query("SELECT torrents.type, torrents.comments, torrents.leechers, torrents.seeders, IF(torrents.numratings < $min_votes, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.id, categories.name AS cat_name, categories.image AS cat_pic, torrents.name, save_as, numfiles, added, size, views, visible, hits, times_completed, category
                      FROM torrents
                      LEFT JOIN categories ON torrents.category = categories.id $where
                      ORDER BY id DESC
                      $limit");

    print($pagertop);

    torrenttable($res, "mytorrents");

    print($pagerbottom);
}

site_footer();

?>