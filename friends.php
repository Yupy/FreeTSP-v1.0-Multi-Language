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

$lang = array_merge(load_language('friends'),
                    load_language('global'));

$userid = isset($_GET['id']) ? (int) $_GET['id'] : user::$current['id'];
$action = isset($_GET['action']) ? security::html_safe($_GET['action']) : '';

if (!is_valid_id($userid))
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_inv_id']}");
}

if ($userid != user::$current['id'])
{
    error_message_center("warn",
                         "{$lang['gbl_warning']}",
                         "{$lang['err_denied']}");
}

$res = $db->query("SELECT username, donor, warned 
                   FROM users
                   WHERE id = " . $userid . "
                   AND enabled = 'yes'") or sqlerr(__FILE__, __LINE__);

$user = $res->fetch_assoc() or sqlerr(__FILE__, __LINE__);

$username = security::html_safe($user['username']);

//-- Action: Add --//
if ($action == 'add')
{
    $targetid = intval(0 + $_GET['targetid']);
    $type     = security::html_safe($_GET['type']);

    if (!is_valid_id($targetid))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    if ($type == 'friend')
    {
        $table_is = $frag = 'friends';
        $field_is = 'friendid';
    }
    elseif ($type == 'block')
    {
        $table_is = $frag = 'blocks';
        $field_is = 'blockid';
    }
    else
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_unknown']}");
    }

    $r = $db->query("SELECT id
                    FROM $table_is
                    WHERE userid = $userid
                    AND $field_is = $targetid") or sqlerr(__FILE__, __LINE__);

    if ($r->num_rows == 1)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_dupe_id']}" . htmlentities($table_is) . "{$lang['err_list']}");
    }

    $db->query("INSERT INTO $table_is
               VALUES (0,$userid, $targetid)") or sqlerr(__FILE__, __LINE__);

    header("Location: $site_url/friends.php?id=$userid#$frag");
    die;
}

//-- Action: Delete --//
if ($action == 'delete')
{
    $targetid = (int) $_GET['targetid'];
    $sure     = isset($_GET['sure']) ? htmlentities($_GET['sure']) : false;
    $type     = isset($_GET['type']) ? ($_GET['type'] == 'friend' ? 'friend' : 'block') : error_message_center("error",
                                                                                                               "{$lang['gbl_error']}",
                                                                                                               "{$lang['err_lol']}");

    if (!is_valid_id($targetid))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    if (!$sure)
    {
        error_message_center("warn",
                             "{$lang['err_delete']}$type",
                             "{$lang['text_del_sure']}$type{$lang['text_click']}\n" . "<a class='btn' href='?id=$userid&amp;action=delete&amp;type=$type&amp;targetid=$targetid&amp;sure=1'>{$lang['btn_here']}</a>{$lang['text_sure']}");
    }

    if ($type == 'friend')
    {
        $db->query("DELETE
                   FROM friends
                   WHERE userid = $userid
                   AND friendid = $targetid") or sqlerr(__FILE__, __LINE__);

        if ($db->affected_rows == 0)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_no_id']}");
        }

        $frag = 'friends';
    }
    elseif ($type == 'block')
    {
        $db->query("DELETE
                   FROM blocks
                   WHERE userid = $userid
                   AND blockid = $targetid") or sqlerr(__FILE__, __LINE__);

        if ($db->affected_rows == 0)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_no_blocked_id']}");
        }

        $frag = 'blocks';
    }
    else
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_unknown']}");
    }

    header("Location: $site_url/friends.php?id=$userid#$frag");
    die;
}

//-- Main Body --//
site_header("{$lang['title_friend_list']}" . security::html_safe($user['username']));

if ($user['donor'] == 'yes')
{
    $donor = "<img src='{$image_dir}star.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_donor']}' title='{$lang['gbl_img_alt_donor']}r' style='margin-left : 4pt' />";
}

if ($user['warned'] == 'yes')
{
    $warned = "<img src='{$image_dir}warned.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_warned']}' title='{$lang['gbl_img_alt_warned']}' style='margin-left : 4pt' />";
}

print("<table class='main' border='0' cellspacing='0' cellpadding='0'>
        <tr>
            <td class='embedded'>
                <h1 style='margin:0px'>{$lang['title_friend_list']}" . security::html_safe($user['username']) . "" . $donor . "" . $warned . "" . $country . "</h1>
            </td>
        </tr>
    </table>\n");

print("<table class='main' border='0' width='100%' cellspacing='0' cellpadding='0'>
        <tr>
            <td class='embedded'>");

print("<br />");
print("<h2 align='left'>{$lang['title_friend']}</h2>\n");

echo("<table border='1' width='100%' cellspacing='0' cellpadding='5'>
        <tr>
            <td>");

$i = 0;

$res = $db->query("SELECT f.friendid AS id, u.username AS name, u.class, u.avatar, u.title, u.donor, u.warned, u.enabled, u.country, u.last_access
                  FROM friends AS f
                  LEFT JOIN users AS u ON f.friendid = u.id
                  WHERE userid = $userid
                  ORDER BY name") or sqlerr(__FILE__, __LINE__);

if ($res->num_rows == 0)
{
    $friends = "<span style='font-style : italic;'>{$lang['title_list_empty']}</span>";
}
else
{
    while ($friend = $res->fetch_assoc())
    {
        $title = $friend['title'];

        if (!$title)
        {
            $title = get_user_class_name($friend['class']);
        }

        $body1 = "<a href='userdetails.php?id={$friend['id']}'><span style='font-weight : bold;'>" . htmlentities($friend['name'], ENT_QUOTES) . "</span></a>" . get_user_icons($friend) . " ($title)<br /><br />{$lang['table_last_seen']}{$friend['last_access']}<br />(" . get_elapsed_time(sql_timestamp_to_unix_timestamp($friend['last_access'])) . "{$lang['table_ago']})";

        $body2 = "<br /><a href='friends.php?id=$userid&amp;action=delete&amp;type=friend&amp;targetid={$friend['id']}'>
                        <input type='submit' class='btn' value='{$lang['btn_remove']}' /></a><br /><br />
                        <a href='sendmessage.php?receiver={$friend['id']}'>
                        <input type='submit' class='btn' value='{$lang['btn_pm']}' /></a>";

        $avatar = ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? security::html_safe($friend['avatar']) : '');

        if (!$avatar)
        {
            $avatar = "{$image_dir}default_avatar.gif";
        }

        if ($i % 2 == 0)
        {
            print("<table width='100%' style='padding: 0px'>
                    <tr>
                        <td class='bottom' align='center' width='50%' style='padding : 5px'>");
        }
        else
        {
            print("<td class='bottom' align='center' width='50%' style='padding : 5px'>");
        }
        print("<table class='main' style='width : 100%; height : 75'>");
        print("<tr valign='top'>
                <td align='center' width='75' style='padding : 0px'>" . ($avatar ? "<div style='width : 75; height : 75; overflow :  hidden'><img src='$avatar' width='125' height='125' border='0' alt='' title='' /></div>" : '') . "</td><td>\n");

        print("<table class='main'>");
        print("<tr>
                <td class='embedded' width='80%' style='padding : 5px'>$body1</td>\n");
        print("<td class='embedded' width='20%' style='padding : 5px'>$body2</td></tr>\n");
        print("</table>");

        echo("</td></tr></table>\n");

        if ($i % 2 == 1)
        {
            print("</td></tr></table>\n");
        }
        else
        {
            print("</td>\n");
        }
        $i++;
    }
}

if ($i % 2 == 1)
{
    print("<td class='bottom' width='50%'>&nbsp;</td></tr></table>\n");
}
print($friends);
print("</td></tr></table>\n");

$res = $db->query("SELECT b.blockid AS id, u.username AS name, u.donor, u.warned, u.username, u.enabled, u.last_access
                  FROM blocks AS b
                  LEFT JOIN users AS u ON b.blockid = u.id
                  WHERE userid = $userid ORDER BY name") or sqlerr(__FILE__, __LINE__);

$blocks = '';

if ($res->num_rows == 0)
{
    $blocks = "<span style='font-style : italic;'>{$lang['text_block_list']}</span>";
}
else
{
    while ($block = $res->fetch_assoc())
    {
        $blocks .= "<div style='border : 1px solid black; padding : 5px;'>";
        $blocks .= "<span class='btn' style='float : right; '><a href='friends.php?id=$userid&amp;action=delete&amp;type=block&amp;targetid={$block['id']}'>{$lang['btn_delete']}</a></span><br />";
        $blocks .= "<p><a href='userdetails.php?id={$block['id']}'>";
        $blocks .= "<span style='font-weight : bold;'>" . htmlentities($block['name'], ENT_QUOTES) . "</span></a>" . get_user_icons($block) . "</p></div><br />";
    }
}

print("<br /><br />");
print("<table class='main' border='0' width='100%' cellspacing='0' cellpadding='10'>
        <tr>
            <td class='embedded'>");
print("<h2 align='left'>{$lang['title_block_list']}</h2></td></tr>");
print("<tr><td class='friends'>");
print("$blocks\n");
print("</td></tr></table>\n");
print("</td></tr></table>\n");
print("<p><a href='users.php'><span style='font-weight : bold;'>{$lang['text_browse_list']}</span></a></p>");

site_footer();

?>