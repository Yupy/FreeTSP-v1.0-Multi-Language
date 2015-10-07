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
require_once(FUNC_DIR . 'function_commenttable.php');
require_once(FUNC_DIR . 'function_bbcode.php');

db_connect(false);
logged_in();

$lang = array_merge(load_language('comment'),
                    load_language('func_bbcode'),
                    load_language('global'));

$action = isset($_GET['action']) ? security::html_safe($_GET['action']) : '';

if ($action == "add")
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $torrentid = intval(0 + $_POST['tid']);

        if (!is_valid_id($torrentid))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_inv_id']}");
        }

        $res = $db->query("SELECT name
                           FROM torrents
                           WHERE id = $torrentid") or sqlerr(__FILE__, __LINE__);

        $arr = $res->fetch_array(MYSQLI_BOTH);

        if (!$arr)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_torr_id']}");
        }

        $text = trim($_POST['text']);

        if (!$text)
        {
            error_message_center("warn",
                                 "{$lang['gbl_warning']}",
                                 "{$lang['err_body']}");
        }

        $db->query("INSERT INTO comments (user, torrent, added, text, ori_text)
                   VALUES (" . user::$current['id'] . ", $torrentid, '" . get_date_time() . "', " . sqlesc($text) . "," . sqlesc($text) . ")");

        $newid = $db->insert_id;

        $db->query("UPDATE torrents
                   SET comments = comments + 1
                   WHERE id = $torrentid");

		$Memcache->delete_value('user::profile::comments::count::' . user::$current['id']);

        header("Refresh: 0; url=details.php?id=$torrentid&amp;viewcomm=$newid#comm$newid");
        die;
    }

    $torrentid = intval(0 + $_GET['tid']);

    if (!is_valid_id($torrentid))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    $res = $db->query("SELECT name
                      FROM torrents
                      WHERE id = $torrentid") or sqlerr(__FILE__, __LINE__);

    $arr = $res->fetch_assoc();

    if (!$arr)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_torr_id']}");
    }

    if (user::$current['torrcompos'] == 'no')
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_access']}");
    }

    site_header("{$lang['title_add']}" . security::html_safe($arr['name']));

    print("<h1>{$lang['title_add']}'" . security::html_safe($arr['name']) . "'</h1>\n");
    print("<form method='post' name='compose' enctype='multipart/form-data' action='comment.php?action=add'>\n");
    print("<input type='hidden' name='tid' value='$torrentid' />\n");
    print("" . textbbcode("compose", "text", "$text") . "");
    print("<p align='center'><input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' /></p>");
    print("</form>\n");

    $res = $db->query("SELECT comments.id, text, comments.added, username, users.id AS user, users.avatar
                     FROM comments LEFT JOIN users ON comments.user = users.id
                     WHERE torrent = $torrentid
                     ORDER BY comments.id DESC
                     LIMIT 5");

    $allrows = array();

    while ($row = $res->fetch_assoc())
    {
        $allrows[] = $row;
    }

    if (count($allrows))
    {
        display_message_center("info",
                               "{$lang['text_recent_comm']}",
                               "{$lang['text_reverse']}");

        commenttable($allrows);
    }

    site_footer();
    die;
}
elseif ($action == "edit")
{
    $commentid = intval(0 + $_GET['cid']);

    if (!is_valid_id($commentid))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    $res = $db->query("SELECT c.*, t.name
                      FROM comments AS c
                      LEFT JOIN torrents AS t ON c.torrent = t.id
                      WHERE c.id = $commentid") or sqlerr(__FILE__, __LINE__);

    $arr = $res->fetch_assoc();

    if (!$arr)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    if ($arr['user'] != user::$current['id'] && get_user_class() < UC_MODERATOR)
    {
        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "{$lang['err_denied']}");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $text     = $_POST['text'];
        $returnto = security::html_safe($_POST['returnto']);

        if ($text == "")
        {
            error_message_center("warn",
                                 "{$lang['gbl_warning']}",
                                 "{$lang['err_comm_body']}");
        }

        $text = sqlesc($text);

        $editedat = sqlesc(get_date_time());

        $db->query("UPDATE comments
                   SET text = $text, editedat = $editedat, editedby = " . user::$current['id'] . "
                   WHERE id = $commentid") or sqlerr(__FILE__, __LINE__);

        if ($returnto)
        {
            header("Location: $returnto");
        }
        else
        {
            header("Location: $site_url/");
        } //-- Change Later --//
        die;
    }

    if (user::$current['torrcompos'] == 'no')
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_access']}");
    }

    site_header("{$lang['title_edit']}'".security::html_safe($arr['name'])."'");

    print("<h1>{$lang['title_edit']}'".security::html_safe($arr['name'])."'</h1>\n");
    //-- Uncomment Below To Revert Back To Old Style & Comment Out The Line Below That--//
    //print("<form method='post' action='comment.php?action=edit&amp;cid=$commentid'>\n");
    print("<form method='post' name='compose' enctype='multipart/form-data' action='comment.php?action=edit&amp;cid=$commentid'>\n");
    print("<input type='hidden' name='returnto' value='{$_SERVER['HTTP_REFERER']}' />\n");
    print("<input type='hidden' name='cid' value='$commentid' />\n");
    //-- Uncomment Below To Revert Back To Old Style & Comment Out The Line Below That--//
    //print("<p align='center'><textarea name='text' cols='60' rows='10'>" . security::html_safe($arr['text']) . "</textarea></p>\n");
    print("" . textbbcode("compose", "text", "" . security::html_safe($arr['text']) . "") . "");
    print("<p align='center'><input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' /></p>\n");
    print("</form>\n");

    site_footer();
    die;
}
elseif ($action == "delete")
{
    if (get_user_class() < UC_MODERATOR)
    {
        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "{$lang['err_denied']}");
    }

    $commentid = intval(0 + $_GET['cid']);

    if (!is_valid_id($commentid))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : false;

    if (!$sure)
    {
        $referer = $_SERVER['HTTP_REFERER'];

        error_message_center("warn",
                             "{$lang['err_delete']}",
                             "" . "<a href='comment.php?action=delete&amp;cid=$commentid&amp;sure=1" . ($referer ? "&amp;returnto=" . urlencode($referer) : "") . "'>{$lang['text_del_sure']}</a>");
    }

    $res = $db->query("SELECT torrent
                      FROM comments
                      WHERE id = $commentid") or sqlerr(__FILE__, __LINE__);

    $arr = $res->fetch_assoc();

    if ($arr)
    {
        $torrentid = (int)$arr['torrent'];
    }

    $db->query("DELETE
               FROM comments
               WHERE id = $commentid") or sqlerr(__FILE__, __LINE__);

    if ($torrentid && $db->affected_rows > 0)
    {
        $db->query("UPDATE torrents
                   SET comments = comments - 1
                   WHERE id = $torrentid");

        $Memcache->delete_value('user::profile::comments::count::' . user::$current['id']);
    }

    $returnto = $_GET['returnto'];

    if ($returnto)
    {
        header("Location: $returnto");
    }
    else
    {
        header("Location: $site_url/");
    } //-- Change Later
    die;
}
elseif ($action == "vieworiginal")
{
    if (get_user_class() < UC_MODERATOR)
    {
        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "{$lang['err_denied']}");
    }

    $commentid = intval(0 + $_GET['cid']);

    if (!is_valid_id($commentid))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    $res = $db->query("SELECT c.*, t.name
                      FROM comments AS c
                      LEFT JOIN torrents AS t ON c.torrent = t.id
                      WHERE c.id = $commentid") or sqlerr(__FILE__, __LINE__);

    $arr = $res->fetch_assoc();

    if (!$arr)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']} $commentid.");
    }

    site_header("{$lang['title_original']}");

    print("<h1>{$lang['text_original']}#$commentid</h1>\n");
    print("<table border='1' width='100%' cellspacing='0' cellpadding='5'>");
    print("<tr>");
    print("<td class='comment'>\n");
    //-- Uncomment Below To Revert Back To Old Style & Comment Out The Line Below That--//
    //print security::html_safe($arr['ori_text']);
    print format_comment($arr['ori_text']);
    print("</td>");
    print("</tr>");
    print("</table><br />");

    $returnto = security::html_safe($_SERVER['HTTP_REFERER']);

    if ($returnto)
    {
        display_message_center("info",
                               "{$lang['gbl_info']}",
                               "{$lang['text_ret_comms']}<br /><br />
                               <a class='btn' href='$returnto'>{$lang['text_click_here']}</a>");
    }

    site_footer();
    die;
}
else
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_unknown']}");
}

die;

?>