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

db_connect(false);
logged_in();

$lang = array_merge(load_language('stats'),
                    load_language('global'));

if (get_user_class() < UC_MODERATOR)
{
    error_message("warn",
                  "{$lang['gbl_warning']}",
                  "{$lang['err_denied']}");
}

site_header("{$lang['title_stats']}", false);

if (($n_tor = $Memcache->get_value('statsp::torrents::count')) === false) {
    $res = $db->query("SELECT COUNT(*)
                       FROM torrents") or sqlerr(__FILE__, __LINE__);

    $n     = $res->fetch_row();
    $n_tor = (int)$n[0];
    $Memcache->cache_value('statsp::torrents::count', $n_tor, 300);
}

if (($n_peers = $Memcache->get_value('statsp::peers::count')) === false) {
    $res = $db->query("SELECT COUNT(*)
                       FROM peers") or sqlerr(__FILE__, __LINE__);

    $n       = $res->fetch_row();
    $n_peers = (int)$n[0];
    $Memcache->cache_value('statsp::peers::count', $n_peers, 300);
}

$uporder  = isset($_GET['uporder']) ? $_GET['uporder'] : '';
$catorder = isset($_GET['catorder']) ? $_GET['catorder'] : '';

if ($uporder == "lastul")
{
    $orderby = "last DESC, name";
}

elseif ($uporder == "torrents")
{
    $orderby = "n_t DESC, name";
}

elseif ($uporder == "peers")
{
    $orderby = "n_p DESC, name";
}
else
{
    $orderby = "name";
}

$query = "SELECT u.id, u.username AS name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) AS n_p
          FROM users AS u
          LEFT JOIN torrents AS t ON u.id = t.owner
          LEFT JOIN peers AS p ON t.id = p.torrent
          WHERE u.class = " . UC_UPLOADER . "
          GROUP BY u.id
          UNION SELECT u.id, u.username AS name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) AS n_p
          FROM users AS u
          LEFT JOIN torrents AS t ON u.id = t.owner
          LEFT JOIN peers AS p ON t.id = p.torrent
          WHERE u.class > " . UC_UPLOADER . "
          GROUP BY u.id
          ORDER BY $orderby";

$res = $db->query($query) or sqlerr(__FILE__, __LINE__);

if ($res->num_rows == 0)
{
    error_message("info",
                  "{$lang['gbl_sorry']}",
                  "{$lang['err_no_uploads']}");
}
else
{
    begin_frame("{$lang['title_up_active']}", true);
    begin_table();

    echo("<tr>\n
    <td class='colhead'><a href='" . security::esc_url($_SERVER['PHP_SELF']) . "?uporder=uploader&amp;catorder=$catorder'>{$lang['table_uploader']}</a></td>\n
    <td class='colhead'><a href='" . security::esc_url($_SERVER['PHP_SELF']) . "?uporder=lastul&amp;catorder=$catorder'>{$lang['table_upload']}</a></td>\n
    <td class='colhead'><a href='" . security::esc_url($_SERVER['PHP_SELF']) . "?uporder=torrents&amp;catorder=$catorder'>{$lang['table_torr']}</a></td>\n
    <td class='colhead'>{$lang['table_perc']}</td>\n
    <td class='colhead'><a href='" . security::esc_url($_SERVER['PHP_SELF']) . "?uporder=peers&amp;catorder=$catorder'>{$lang['table_peers']}</a></td>\n
    <td class='colhead'>{$lang['table_perc']}</td>\n
    </tr>\n");

    while ($uper = $res->fetch_assoc())
    {
        echo("<tr><td class='std'><a href='userdetails.php?id={$uper['id']}'><span style='font-weight : bold;'>" . security::html_safe($uper['name']) . "</span></a></td>\n");
        echo("<td " . ($uper['last'] ? (">{$uper['last']} (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($uper['last'])) . "{$lang['table_ago']})") : " class='rowhead' align='center'>---") . "</td>\n");
        echo("<td class='rowhead' align='right'>{$uper['n_t']}</td>\n");
        echo("<td class='rowhead' align='right'>" . ($n_tor > 0 ? number_format(100 * $uper['n_t'] / $n_tor, 1) . "%" : "---") . "</td>\n");
        echo("<td class='rowhead' align='right'>{$uper['n_p']}</td>\n");
        echo("<td class='rowhead' align='right'>" . ($n_peers > 0 ? number_format(100 * $uper['n_p'] / $n_peers, 1) . "%" : "---") . "</td></tr>\n");
    }

    end_table();
    end_frame();
}

if ($n_tor == 0)
{
    error_message("info",
                  "{$lang['gbl_sorry']}",
                  "{$lang['err_no_cats']}");
}
else
{
    if ($catorder == "lastul")
    {
        $orderby = "last DESC, c.name";
    }

    elseif ($catorder == "torrents")
    {
        $orderby = "n_t DESC, c.name";
    }

    elseif ($catorder == "peers")
    {
        $orderby = "n_p DESC, name";
    }
    else
    {
        $orderby = "c.name";
    }

    $res = $db->query("SELECT c.name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) AS n_p
                      FROM categories AS c
                      LEFT JOIN torrents AS t ON t.category = c.id
                      LEFT JOIN peers AS p ON t.id = p.torrent
                      GROUP BY c.id
                      ORDER BY $orderby") or sqlerr(__FILE__, __LINE__);

    begin_frame("{$lang['title_cat_active']}", true);
    begin_table();

    echo("<tr><td class='colhead'><a href='" . security::esc_url($_SERVER['PHP_SELF']) . "?uporder=$uporder&amp;catorder=category'>{$lang['table_cat']}</a></td>
    <td class='colhead'><a href='" . security::esc_url($_SERVER['PHP_SELF']) . "?uporder=$uporder&amp;catorder=lastul'>{$lang['table_upload']}</a></td>
    <td class='colhead'><a href='" . security::esc_url($_SERVER['PHP_SELF']) . "?uporder=$uporder&amp;catorder=torrents'>{$lang['table_torr']}</a></td>
    <td class='colhead'>{$lang['table_perc']}</td>
    <td class='colhead'><a href='" . security::esc_url($_SERVER['PHP_SELF']) . "?uporder=$uporder&amp;catorder=peers'>{$lang['table_peers']}</a></td>
    <td class='colhead'>{$lang['table_perc']}</td></tr>\n");

    while ($cat = $res->fetch_assoc())
    {
        echo("<tr><td class='rowhead'>" . security::html_safe($cat['name']) . "</td>");
        echo("<td " . ($cat['last'] ? (">{$cat['last']} (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($cat['last'])) . "{$lang['table_ago']})") : "align = 'center'>---") . "</td>");
        echo("<td class='rowhead' align='right'>{$cat['n_t']}</td>");
        echo("<td class='rowhead' align='right'>" . number_format(100 * $cat['n_t'] / $n_tor, 1) . "%</td>");
        echo("<td class='rowhead' align='right'>{$cat['n_p']}</td>");
        echo("<td class='rowhead' align='right'>" . ($n_peers > 0 ? number_format(100 * $cat['n_p'] / $n_peers, 1) . "%" : "---") . "</td></tr>\n");
    }

    end_table();
    end_frame();
}

site_footer();

die;

?>