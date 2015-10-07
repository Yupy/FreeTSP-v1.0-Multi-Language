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

db_connect();
logged_in();

$lang = array_merge(load_language('snatches'),
                    load_language('global'));

$id = intval(0 + $_GET['id']);

if (!is_valid_id($id))
{
    error_message("error",
                  "{$lang['gbl_error']}",
                  "{$lang['err_inv_id']}");
}

$res = $db->query("SELECT id, name
                  FROM torrents
                  WHERE id = $id") or sqlerr();

$arr = $res->fetch_assoc();

if (!$arr)
{
    error_message("error",
                  "{$lang['gbl_error']}",
                  "{$lang['err_inv_tor_id']}");
}

$res = $db->query("SELECT COUNT(*)
                  FROM snatched
                  WHERE torrentid = $id") or sqlerr();

$row = $res->fetch_row();

$count   = (int)$row[0];
$perpage = 100;

if (!$count)
{
    error_message("info",
                  "{$lang['err_no_snatch']}",
                  "{$lang['err_no_snatch_tor']}<a href=details.php?id={$arr['id']}>" . security::html_safe($arr['name']) . "</a>.");
}

list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "?id=$id&");

site_header("{$lang['title_snatch']}", false);

print("<h1>{$lang['text_snatch_for']}<a href='details.php?id={$arr['id']}'>" . security::html_safe($arr['name']) . "</a></h1>\n");
print("<h2>{$lang['text_current']}$row[0]{$lang['text_snatch']}" . ($row[0] == 1 ? "" : "{$lang['text_es']}") . "</h2>\n");

if ($count > $perpage)
{
    print("$pagertop");
}

print("<table border='0' cellspacing='0' cellpadding='5'>\n");
print("<tr>\n");
print("<td class='colhead' align='left'>{$lang['table_username']}</td>\n");
print("<td class='colhead' align='center'>{$lang['table_connect']}</td>\n");
print("<td class='colhead' align='right'>{$lang['table_upload']}</td>\n");
print("<td class='colhead' align='right'>{$lang['table_download']}</td>\n");
print("<td class='colhead' align='right'>{$lang['table_ratio']}</td>\n");
print("<td class='colhead' align='right'>{$lang['table_complete']}</td>\n");
print("<td class='colhead' align='center'>{$lang['table_seedtime']}</td>\n");
print("<td class='colhead' align='center'>{$lang['table_leechtime']}</td>\n");
print("<td class='colhead' align='center'>{$lang['table_lastaction']}</td>\n");
print("<td class='colhead' align='center'>{$lang['table_complete_at']}</td>\n");
print("<td class='colhead' align='center'>{$lang['table_client']}</td>\n");
print("<td class='colhead' align='center'>{$lang['table_port']}</td>\n");
print("<td class='colhead' align='center'>{$lang['table_seeding']}</td>\n");
print("</tr>\n");

$res = $db->query("SELECT s.*, size, username, warned, enabled, donor, class
                  FROM snatched AS s
                  INNER JOIN users ON s.userid = users.id
                  INNER JOIN torrents ON s.torrentid = torrents.id
                  WHERE torrentid = $id
                  ORDER BY complete_date DESC
                  $limit") or sqlerr();

while ($arr = $res->fetch_assoc())
{
    $upspeed = ($arr['upspeed'] > 0 ? misc::mksize($arr['upspeed']) : ($arr['seedtime'] > 0 ? misc::mksize($arr['uploaded'] / ($arr['seedtime'] + $arr['leechtime'])) : misc::mksize(0)));

    $downspeed = ($arr['downspeed'] > 0 ? misc::mksize($arr['downspeed']) : ($arr['leechtime'] > 0 ? misc::mksize($arr['downloaded'] / $arr['leechtime']) : misc::mksize(0)));

    $ratio = ($arr['downloaded'] > 0 ? number_format($arr['uploaded'] / $arr['downloaded'], 3) : ($arr['uploaded'] > 0 ? 'Inf.' : '---'));

    $completed = sprintf("%.2f%%", 100 * (1 - ($arr['to_go'] / $arr['size'])));

    $res1 = $db->query("SELECT seeder
                       FROM peers
                       WHERE torrent = " . (int)$_GET['id'] . "
                       AND userid = " . (int)$arr['userid']);

    $arr1 = $res1->fetch_assoc();

    print("<tr>\n");
    print("<td class='rowhead' align='left'>" . format_username($arr) . "</td>\n");
    print("<td class='rowhead' align='center'>" . ($arr['connectable'] == 'yes' ? "<span class='snatches_connect_yes'>{$lang['table_yes']}</span>" : "<span  class='snatches_connect_no'>{$lang['table_no']}</span>") . "</td>\n");
    print("<td class='rowhead' align='right'>" . misc::mksize($arr['uploaded']) . "</td>\n");
    print("<td class='rowhead' align='right'>" . misc::mksize($arr['downloaded']) . "</td>\n");
    print("<td class='rowhead' align='right'>$ratio</td>\n");
    print("<td class='rowhead' align='center'>$completed</td>\n");
    print("<td class='rowhead' align='right'>" . mkprettytime($arr['seedtime']) . "</td>\n");
    print("<td class='rowhead' align='right'>" . mkprettytime($arr['leechtime']) . "</td>\n");
    print("<td class='rowhead' align='center'>{$arr['last_action']}</td>\n");
    print("<td class='rowhead' align='center'>" . ($arr['complete_date'] == "0000-00-00 00:00:00" ? "{$lang['table_incomplete']}" : $arr['complete_date']) . "</td>\n");
    print("<td class='rowhead' align='center'>{$arr['agent']}</td>\n");
    print("<td class='rowhead' align='center'>{$arr['port']}</td>\n");
    print("<td class='rowhead' align='center'>" . ($arr1['seeder'] == "yes" ? "<span class='snatches_seeding_yes'>{$lang['table_yes']}</span>" : "<span class='snatches_seeding_no'>{$lang['table_no']}</span>") . "</td>\n");
    print("</tr>\n");
}
print("</table><br />\n");

if ($count > $perpage)

{
    print("$pagerbottom");
}

site_footer();

?>