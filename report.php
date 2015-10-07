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
require_once(FUNC_DIR . 'function_bbcode.php');

db_connect();
logged_in();

$lang = array_merge(load_language('report'),
                    load_language('func_bbcode'),
                    load_language('global'));

//-- Now All Reports Just Use A Single Var $id And A Type --//
    $id   = ($_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id']);
    $type = (isset($_GET['type']) ? htmlsafechars($_GET['type']) : htmlsafechars($_POST['type']));

if (!is_valid_id($id))
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_bad_id']}");
}

    $typesallowed = array("User",
                          "Comment",
                          "Request_Comment",
                          "Offer_Comment",
                          "Request",
                          "Offer",
                          "Torrent",
                          "Hit_And_Run",
                          "Post");

if (!in_array($type, $typesallowed))
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_inv_report']}");
}

//-- Start Get Some Names And Limitations For The Array Types --//
if ($type == 'User')
{
    $query = "SELECT username, class
              FROM users
              WHERE id = $id";

    $sql   = $db->query($query);
    $row   = $sql->fetch_array(MYSQLI_BOTH);
    $name  = security::html_safe($row['username']);

    if ($row['class'] >= UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_report_staff']}");
    }
}

if ($type == 'Comment')
{
    $query  = "SELECT user, text
               FROM comments
               WHERE id = $id";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);
    $userid = (int)$row['user'];
    $name   = security::html_safe($row['text']);

    $query  = "SELECT class
               FROM users
               WHERE id = $userid";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);

    if ($row['class'] >= UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_report_staff_comm']}");
    }
}

if ($type == 'Request')
{
    $query  = "SELECT requested_by_user_id, request_name
               FROM requests
               WHERE id = $id";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);
    $userid = (int)$row['requested_by_user_id'];
    $name   = htmlsafechars($row['request_name']);

    $query  = "SELECT class
               FROM users
               WHERE id = $userid";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);

    if ($row['class'] >= UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_report_staff_req']}");
    }
}

if ($type == 'Request_Comment')
{
    $query  = "SELECT user, text
               FROM comments_request
               WHERE id = $id";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);
    $userid = (int)$row['user'];
    $name   = security::html_safe($row['text']);

    $query  = "SELECT class
               FROM users
               WHERE id = $userid";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);

    if ($row['class'] >= UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_report_staff_req_comm']}");
    }
}

if ($type == 'Offer')
{
    $query  = "SELECT offered_by_user_id, offer_name
               FROM offers
               WHERE id = $id";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);
    $userid = (int)$row['offered_by_user_id'];
    $name   = security::html_safe($row['offer_name']);

    $query  = "SELECT class
               FROM users
               WHERE id = $userid";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);

    if ($row['class'] >= UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_report_staff_offer']}");
    }
}

if ($type == 'Offer_Comment')
{
    $query  = "SELECT user, text
               FROM comments_offer
               WHERE id = $id";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);
    $userid = (int)$row['user'];
    $name   = security::html_safe($row['text']);

    $query  = "SELECT class
               FROM users
               WHERE id = $userid";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);

    if ($row['class'] >= UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_report_staff_offer_comm']}");
    }
}

if ($type == 'Torrent')
{
    $query  = "SELECT owner, name
               FROM torrents
               WHERE id = $id";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);
    $userid = (int)$row['owner'];
    $name   = security::html_safe($row['name']);

    $query = "SELECT class
              FROM users
              WHERE id = $userid";

    $sql   = $db->query($query);
    $row   = $sql->fetch_array(MYSQLI_BOTH);

    if ($row['class'] >= UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_report_staff_upload']}");
    }
}

if ($type == 'Post')
{
    $query  = "SELECT userid, body
               FROM posts
               WHERE id = $id";

    $sql    = $db->query($query);
    $row    = $sql->fetch_array(MYSQLI_BOTH);
    $userid = (int)$row['userid'];
    $name   = security::html_safe($row['body']);

    $query = "SELECT class
              FROM users
              WHERE id = $userid";

    $sql   = $db->query($query);
    $row   = $sql->fetch_array(MYSQLI_BOTH);

    if ($row['class'] >= UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_report_staff_post']}");
    }
}
//-- Finish Get Some Names And Limitations For The Array Types --//

//-- Still Need A Second Value Passed For Stuff Like Hit And Run Where You Need Two ID Numbers --//
if ((isset($_GET['id_2'])) || (isset($_POST['id_2'])))
{
    $id_2 = ($_GET['id_2'] ? $_GET['id_2'] : $_POST['id_2']);

    if (!is_valid_id($id_2))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_missing']}");
    }

    $id_2b = "&amp;id_2=$id_2";
}

//-- -Start Updating The Report SQL --//
if ((isset($_GET['do_it'])) || (isset($_POST['do_it'])))
{
    $do_it = ($_GET['do_it'] ? $_GET['do_it'] : $_POST['do_it']);

    if (!is_valid_id($do_it))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_wrong']}");
    }

    //-- Make Sure The Reason Is Filled Out And Is Set --//
    $reason = sqlesc($_POST['reason']);

    if (!$reason)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_no_reason']}");
    }

    //-- Check If It Has Been Reported Already --//
    $res = $db->query("SELECT id
                      FROM reports
                      WHERE reported_by = " . user::$current['id'] . "
                      AND reporting_what = $id
                      AND reporting_type = '$type'") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows != 0)
    {
        error_message_center("error",
                             "{$lang['err_failure']}",
                             "{$lang['err_reported']}<strong>" . str_replace("_" , " ",$type) . "</strong>{$lang['err_id']}<strong>$id</strong>!");
    }

    //-- OK It Has Not Been Reported Yet, So Carry On --//
    $dt = sqlesc(get_date_time());

    $db->query("INSERT INTO reports (reported_by, reporting_what, reporting_type, reason, added, 2nd_value)
               VALUES (" . user::$current['id'] . ", '$id', '$type', $reason, $dt, '$id_2')") or sqlerr(__FILE__, __LINE__);
	
	$Memcache->delete_value('reports::count');

    site_header("{$lang['title_confirm']}");

    echo("<table width='80%'>
          <tr>
            <td class='colhead'><h1>{$lang['table_success']}</h1></td>
          </tr>
          <tr>
            <td class='rowhead' align='center'><strong>{$lang['table_success_report']}&nbsp;--&nbsp;" . str_replace("_" , " ",$type) . "&nbsp;--</strong>&nbsp;&nbsp;" . format_comment($name) . "!<br /><br /><strong>{$lang['table_reason']}</strong>&nbsp;$reason</td>
          </tr>
        </table>");

    site_footer();
    die();
}
//-- Finish Updating The Report SQL --//

//-- Starting Main Page For Reporting All... --//
site_header("{$lang['title_report']}");

    echo("<form method='post' action='report.php?type=$type$id_2b&amp;id=$id&amp;do_it=1'>
           <table width='80%'>");

    echo("<tr>
            <td class='colhead' colspan='2'><h1>{$lang['table_report']}:-&nbsp;&nbsp;" . format_comment($name) . "</h1></td>
        </tr>");

    echo("<tr>
            <td class='rowhead' align='center' colspan='2'>{$lang['table_sure_report']}" . str_replace("_" , " ",$type) . "<br />{$lang['table_violation']}<a class='altlink' href='rules.php' target='_blank'>{$lang['table_rules']}</a>?
            </td>
        </tr>");

    echo("<tr>
            <td class='rowhead' align='center' width='10%'><strong>{$lang['table_reason']}</strong><br /><br />( <strong>{$lang['table_required']}</strong> )</td>
            <td class='rowhead' align='center'>
                <textarea name='reason' cols='80' rows='5'></textarea><br />
            </td>
        </tr>");

    echo("<tr>
            <td class='rowhead' align='center' colspan='2'>
                <input type='submit' class='btn' value='{$lang['btn_confirm']}' />
            </td>
        </tr>");

    echo("</table></form>");

site_footer();
die;

?>