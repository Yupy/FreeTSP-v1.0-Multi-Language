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

$lang = array_merge(load_language('userhistory'),
                    load_language('func_vfunctions'),
                    load_language('func_bbcode'),
                    load_language('global'));

$userid = (int) $_GET['id'];

if (!is_valid_id($userid))
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_inv_id']}");
}

if (get_user_class() < UC_POWER_USER || (user::$current['id'] != $userid && get_user_class() < UC_MODERATOR))
{
    error_message_center("warn",
                         "{$lang['gbl_warning']}",
                         "{$lang['err_denied']}");
}

$page   = (isset($_GET['page']) ? (int)$_GET['page'] : ''); //-- Not Used? --//
$action = (isset($_GET['action']) ? security::html_safe($_GET['action']) : '');

//-- Global Variables --//
$perpage = 25;

//-- Action: View Posts --//
if ($action == "viewposts")
{
    $select_is = "COUNT(DISTINCT p.id)";
    $from_is   = "posts AS p
                  LEFT JOIN topics AS t ON p.topicid = t.id
                  LEFT JOIN forums AS f ON t.forumid = f.id";

    $where_is = "p.userid = $userid AND f.minclassread <= " . user::$current['class'];
    $order_is = "p.id DESC";

    $query = "SELECT $select_is
              FROM $from_is
              WHERE $where_is";

    $res = $db->query($query) or sqlerr(__FILE__, __LINE__);
    $arr = $res->fetch_row() or error_message_center("error",
                                                         "{$lang['gbl_error']}",
                                                         "{$lang['err_no_posts']}");

    $postcount = (int)$arr[0];

    //-- Make Page Menu --//
    list($pagertop, $pagerbottom, $limit) = pager($perpage, $postcount, security::esc_url($_SERVER['PHP_SELF']) . "?action=viewposts&amp;id=$userid&amp;");

    //-- Get User Data --//
    $res = $db->query("SELECT username, donor, warned, enabled
                      FROM users
                      WHERE id = $userid") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows == 1)
    {
        $arr = $res->fetch_assoc();

        $subject = "<a href='userdetails.php?id=$userid'><span style='font-weight : bold;'>" . security::html_safe($arr['username']) . "</span></a>" . get_user_icons($arr, true);
    }
    else
    {
        $subject = "unknown[$userid]";
    }

    //-- Get Posts --//
    $from_is = "posts AS p
                LEFT JOIN topics AS t ON p.topicid = t.id
                LEFT JOIN forums AS f ON t.forumid = f.id
                LEFT JOIN readposts AS r ON p.topicid = r.topicid and p.userid = r.userid";

    $select_is = "f.id AS f_id, f.name, t.id AS t_id, t.subject, t.lastpost, r.lastpostread, p.*";

    $query = "SELECT $select_is
              FROM $from_is
              WHERE $where_is
              ORDER BY $order_is
              $limit";

    $res = $db->query($query) or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows == 0)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_no_posts']}");
    }

    site_header("{$lang['title_post_hist']}");

    echo("<h1>{$lang['title_post_hist_for']}$subject</h1>\n");

    if ($postcount > $perpage)
    {
        echo $pagertop;
    }

    //-- Print Table --//
    begin_frame();

    while ($arr = $res->fetch_assoc())
    {
        $postid    = (int)$arr['id'];
        $posterid  = (int)$arr['userid'];
        $topicid   = (int)$arr['t_id'];
        $topicname = security::html_safe($arr['subject']);
        $forumid   = (int)$arr['f_id'];
        $forumname = security::html_safe($arr['name']);
        $dt        = (get_date_time(gmtime() - $posts_read_expiry));
        $newposts  = 0;

        if ($arr['added'] > $dt)
        {
            $newposts = ($arr['lastpostread'] < $arr['lastpost']) && user::$current['id'] == $userid;
        }

        $added = $arr['added'] . "{$lang['table_gmt']}(" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr['added']))) . " {$lang['table_ago']})";

        echo("<br />
                <table border='0' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td class='embedded'>
                            $added&nbsp;--&nbsp;
                            <span style='font-weight : bold;'>{$lang['table_forum']}:&nbsp;</span>
                            <a href='forums.php?action=viewforum&amp;forumid=$forumid'>$forumname</a>&nbsp;--&nbsp;
                            <span style='font-weight : bold;'>{$lang['table_topic']}:&nbsp;</span>
                            <a href='forums.php?action=viewtopic&amp;topicid=$topicid'>$topicname</a>&nbsp;--&nbsp;
                            <span style='font-weight : bold;'>{$lang['table_post']}:&nbsp;</span>
                            #<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=p$postid#$postid'>$postid</a>
                            ".($newposts ? " &nbsp;
                            <span class='userhistory_new'>{$lang['table_new']}</span>" : "") . "
                        </td>
                    </tr>
                </table>\n");

        begin_table(true);

        $body = format_comment($arr['body']);

        if (is_valid_id($arr['editedby']))
        {
            $subres = $db->query("SELECT username
                                 FROM users
                                 WHERE id = " . (int)$arr['editedby']);

            if ($subres->num_rows == 1)
            {
                $subrow = $subres->fetch_assoc();
                $body .= "<p><span style='font-size : xx-small;'>{$lang['table_last_edit']}<a href='userdetails.php?id={$arr['editedby']}'><span style='font-weight : bold;'>" . security::html_safe($subrow['username']) . "</span></a>{$lang['table_at']}{$arr['editedat']}{$lang['table_gmt']}</span></p>\n";
            }
        }

        echo("<tr valign='top'><td class='comment'>" . $body . "</td></tr>\n");

        end_table();
    }

    end_frame();

    if ($postcount > $perpage)
    {
        echo $pagerbottom;
    }

    site_footer();

    die;
}

//-- Action: View Comments --//
if ($action == "viewcomments")
{
    $select_is = "COUNT(*)";

    //-- LEFT Due To Orphan Comments --//
    $from_is = "comments AS c
                LEFT JOIN torrents AS t ON c.torrent = t.id";

    $where_is = "c.user = $userid";
    $order_is = "c.id DESC";

    $query = "SELECT $select_is
              FROM $from_is
              WHERE $where_is
              ORDER BY $order_is";

    $res = $db->query($query) or sqlerr(__FILE__, __LINE__);

    $arr = $res->fetch_row() or error_message_center("error",
                                                         "{$lang['gbl_error']}",
                                                         "{$lang['err_no_comments']}");

    $commentcount = (int)$arr[0];

    //-- Make Page Menu --//
    list($pagertop, $pagerbottom, $limit) = pager($perpage, $commentcount, security::esc_url($_SERVER['PHP_SELF']) . "?action=viewcomments&id=$userid&");

    //-- Get User Data --//
    $res = $db->query("SELECT username, donor, warned, enabled
                      FROM users
                      WHERE id = $userid") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows == 1)
    {
        $arr     = $res->fetch_assoc();
        $subject = "<a href='userdetails.php?id=$userid'><span style='font-weight : bold;'>" . security::html_safe($arr['username']) . "</span></a>" . get_user_icons($arr, true);
    }
    else
    {
        $subject = "unknown[$userid]";
    }

    //-- Get Comments --//
    $select_is = "t.name, c.torrent AS t_id, c.id, c.added, c.text";

    $query = "SELECT $select_is
              FROM $from_is
              WHERE $where_is
              ORDER BY $order_is
              $limit";

    $res = $db->query($query) or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows == 0)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_no_comments']}");
    }

    site_header("{$lang['title_comment_hist']}");

    echo("<h1>{$lang['title_comment_hist_for']}$subject</h1>\n");

    if ($commentcount > $perpage)
    {
        echo $pagertop;
    }

    //-- Print Table --//
    begin_frame();

    while ($arr = $res->fetch_assoc())
    {
        $commentid = (int)$arr['id'];
        $torrent   = security::html_safe($arr['name']);

        //-- Make Sure The Line Doesn't Wrap --//
        if (strlen($torrent) > 55)
        {
            $torrent = substr($torrent, 0, 52) . "...";
        }

        $torrentid = (int)$arr['t_id'];

        //-- Find The Page; This Code Should Probably Be In details.php Instead --//
        $subres = $db->query("SELECT COUNT(id)
                             FROM comments
                             WHERE torrent = $torrentid
                             AND id < $commentid") or sqlerr(__FILE__, __LINE__);

        $subrow    = $subres->fetch_row();
        $count     = (int)$subrow[0];
        $comm_page = floor($count / 20);
        $page_url  = $comm_page ? "&page=$comm_page" : "";
        $added     = $arr['added'] . "{$lang['table_gmt']}(" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr['added']))) . "{$lang['table_ago']})";

        echo("<table border='0' cellspacing='0' cellpadding='0'>
                <tr>
                    <td class='embedded'>
                        " . "$added&nbsp;---&nbsp;
                        <span style='font-weight : bold;'>{$lang['table_torrent']}:&nbsp;</span>
                        " . ($torrent ? ("<a href='details.php?id=$torrentid&amp;tocomm=1'>$torrent</a>") : "{$lang['table_deleted']}") . "&nbsp;---&nbsp;
                        <span style='font-weight : bold;'>{$lang['table_comment']}:&nbsp;</span>
                        #<a href='details.php?id=$torrentid&amp;tocomm=1$page_url'>$commentid</a>
                    </td>
                </tr>
            </table>\n");

        begin_table(true);

        $body = format_comment($arr['text']);

        echo("<tr valign='top'><td class='comment'>" . $body . "</td></tr>\n");

        end_table();
    }

    end_frame();

    if ($commentcount > $perpage)
    {
        echo $pagerbottom;
    }

    site_footer();

    die;
}

//-- Handle Unknown Action --//
if ($action != "")
{
    error_message_center("error",
                         "{$lang['err_history_error']}",
                         "{$lang['err_unknown']}");
}

//-- Any Other Case --//
error_message_center("error",
                     "{$lang['err_history_error']}",
                     "{$lang['err_inv_query']}");

?>