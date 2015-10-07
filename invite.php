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

db_connect(true);
logged_in();

$lang = array_merge(load_language('invite'),
                    load_language('global'));

$do            = (isset($_GET['do']) ? $_GET['do'] : (isset($_POST['do']) ? $_POST['do'] : ''));
$valid_actions = array('create_invite', 'delete_invite', 'confirm_account', 'view_page');
$do            = (($do && in_array($do,$valid_actions,true)) ? $do : '') or header("Location: ?do=view_page");

//-- Show The Default Fist Page --//
if ($do == 'view_page')
{
    $query = $db->query("SELECT *
                        FROM users
                        WHERE invitedby = " . sqlesc(user::$current['id'])) or sqlerr(__FILE__, __LINE__);

    $rows = $query->num_rows;

    site_header($lang['title_invites']);

    echo("<table border='1' width='81%' cellspacing='0' cellpadding='5'>");
    echo("<tr><td class='colhead' align='center' colspan='7'><strong>{$lang['table_invited']}</strong></td></tr>");

    if (!$rows)
    {
        echo("<tr><td class='rowhead' align='center' colspan='7'>{$lang['table_no_invitees']}</td></tr>");
    }
    else
    {
        echo ("<tr>
                   <td class='rowhead' align='center'><strong>{$lang['table_username']}</strong></td>
                   <td class='rowhead' align='center'><strong>{$lang['table_uploaded']}</strong></td>
                   <td class='rowhead' align='center'><strong>{$lang['table_downloaded']}</strong></td>
                   <td class='rowhead' align='center'><strong>{$lang['table_ratio']}</strong></td>
                   <td class='rowhead' align='center'><strong>{$lang['table_status']}</strong></td>
                   <td class='rowhead' align='center'><strong>{$lang['table_confirm']}</strong></td>
               </tr>");

        for ($i = 0; $i < $rows; ++$i)
        {
            $arr = $query->fetch_assoc();

            if ($arr['status'] == 'pending')
            {
                $user = "" . security::html_safe($arr['username']) . "";
            }
            else
            {
                $user = "<a href='userdetails.php?id={$arr['id']}'><strong><font color='#" . get_user_class_color($arr['class']) . "'>" . security::html_safe($arr['username']) . "</font></strong></a>
                        " . ($arr['warned'] == 'yes' ? "&nbsp;<img src='{$image_dir}warned.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_warned']}' title='{$lang['gbl_img_alt_warned']}' />" : '') . "&nbsp;
                        " . ($arr['enabled'] == 'no' ? "&nbsp;<img src='{$image_dir}disabled.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_disabled']}' title='{$lang['gbl_img_alt_disabled']}' />" : '') . "&nbsp;
                        " . ($arr['donor'] == 'yes' ? "<img src='{$image_dir}star.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_donor']}' title='{$lang['gbl_img_alt_donor']}' />" : '') . "";
            }

            if ($arr['downloaded'] > 0)
            {
                $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
                $ratio = "<font color='" . get_ratio_color($ratio) . "'>" . $ratio . "</font>";
            }
            else
            {
                if ($arr['uploaded'] > 0)
                {
                    $ratio = 'Inf.';
                }
                else
                {
                    $ratio = '---';
                }
            }

            if ($arr['status'] == 'confirmed')
            {
                $status = "<font class='invite_confirmed'>{$lang['table_confirmed']}</font>";
            }
            else
            {
                $status = "<font class='invite_pending'>{$lang['table_pending']}</font>";
            }

            echo ("<tr>
                  <td class='rowhead'align='center'>$user</td>
                  <td class='rowhead'align='center'>" . misc::mksize($arr['uploaded']) . "</td>
                  <td class='rowhead'align='center'>" . misc::mksize($arr['downloaded']) . "</td>
                  <td class='rowhead'align='center'>$ratio</td>
                  <td class='rowhead'align='center'>$status</td>");

            if ($arr['status'] == 'pending')
            {
                echo("<td class='rowhead' align='center'><a href='?do=confirm_account&amp;userid={$arr['id']}&amp;sender=" . user::$current['id'] . "'><img src='{$image_dir}rep.png' width='24' height='25' border='0' alt='{$lang['img_alt_status']}' title='{$lang['img_alt_status']}' /></a></td>");
            }
            else
            {
                echo("<td class='rowhead' align='center'>---</td>");
            }
        }

        echo("</tr>");
        echo("</table><br />");
    }

    $select = $db->query("SELECT *
                         FROM invite_codes
                         WHERE sender = " . user::$current['id'] . "
                         AND status = 'Pending'") or sqlerr(__FILE__, __LINE__);

    $num_row = $select->num_rows;

    echo("<table border='1' width='81%' cellspacing='0' cellpadding='5'>");
    echo("<tr><td class='colhead' align='center' colspan='6'><strong>{$lang['table_created_code']}</strong></td></tr>");

    if (!$num_row)
    {
        echo("<tr><td class='rowhead' align='center' colspan='6'>{$lang['table_no_codes']}</td></tr>");
    }
    else
    {
        echo("<tr>
              <td class='rowhead' align='center'><strong>{$lang['table_invite_code']}</strong></td>
              <td class='rowhead' align='center'><strong>{$lang['table_date_created']}</strong></td>
              <td class='rowhead' align='center'><strong>{$lang['table_delete']}</strong></td>
              <td class='rowhead' align='center'><strong>{$lang['table_status']}</strong></td>
              </tr>");

        for ($i = 0; $i < $num_row; ++$i)
        {
            $fetch_assoc = $select->fetch_assoc();

            echo("<tr>
                  <td class='rowhead' align='center'>{$fetch_assoc['code']}</td>
                  <td class='rowhead' align='center'>" . get_elapsed_time(sql_timestamp_to_unix_timestamp($fetch_assoc['invite_added'])) . " ago</td>");

            echo("<td class='rowhead' align='center'><a href='?do=delete_invite&amp;id={$fetch_assoc['id']}&amp;sender=" . user::$current['id'] . "'><img src='{$image_dir}button_delete2.gif' width='12' height='14' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' /></a></td>
                  <td class='rowhead' align='center'>{$fetch_assoc['status']}</td>
                  </tr>");
        }
    }

    echo("<tr>
          <td class='rowhead' align='center' colspan='7'>
            <form method='post' action='?do=create_invite'><input type='submit' class='btn' value='{$lang['btn_create']}' style='height : 20px' /></form>
          </td>
        </tr>");

    echo '</table>';

    site_footer();

    die();
}

//-- Create The Invite --//
if ($do =='create_invite')
{
    if (user::$current['invites'] <= 0)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['text_no_invites']}");
    }

    if (user::$current['invite_rights'] == 'no')
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['text_invite_disabled']}");
    }

    $res = $db->query("SELECT COUNT(*)
                      FROM users") or sqlerr(__FILE__, __LINE__);

    $arr = $res->fetch_row();

    if ($arr[0] >= $invites)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['text_limit_reached']}");
    }

    $invite = md5(mksecret());

    $db->query("INSERT INTO invite_codes (sender, invite_added, code)
                VALUES (" . sqlesc(user::$current['id']) . ", " . sqlesc(get_date_time()) . ", " . sqlesc($invite) . ")") or sqlerr(__FILE__, __LINE__);

    $db->query("UPDATE users
               SET invites = invites - 1
               WHERE id = " . sqlesc(user::$current['id'])) or sqlerr(__FILE__, __LINE__);


    $update['invites'] = (user::$current['invites'] - 1);
    $Memcache->begin_transaction('statusbar::user::stats::' . user::$current['id']);
    $Memcache->update_row(false, array('invites' => $update['invites']));
    $Memcache->commit_transaction(1800);

    $Memcache->begin_transaction('user::profile::stats::' . user::$current['id']);
    $Memcache->update_row(false, array('invites' => $update['invites']));
    $Memcache->commit_transaction(1800);

    header("Location: ?do=view_page");
}

//-- Delete The Invite --//
if ($do =='delete_invite')
{
    $id = (isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : ''));

    $query = $db->query("SELECT *
                        FROM invite_codes
                        WHERE id = " . sqlesc($id) . "
                        AND sender = " . sqlesc(user::$current['id']) . "
                        AND status = 'Pending'") or sqlerr(__FILE__, __LINE__);

    $assoc = $query->fetch_assoc();

    if (!$assoc)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['text_inv_invite']}");
    }

    isset($_GET['sure']) && $sure = htmlentities($_GET['sure']);

    if (!$sure)
    {
        error_message_center("warn",
                             "{$lang['gbl_sanity']}",
                             "{$lang['text_del_sure']}<br /><br />
                             {$lang['text_click']}<a class='btn' href='" . security::esc_url($_SERVER['PHP_SELF']) . "?do=delete_invite&amp;id={$id}&amp;sender=" . user::$current['id'] . "&amp;sure=yes'>{$lang['text_here']}</a>
                             {$lang['text_del_click']}<br /><br />{$lang['text_click']}<a class='btn' href='?do=view_page'>{$lang['text_here']}</a>{$lang['text_go_back']}");
    }

    $db->query("DELETE FROM invite_codes
               WHERE id = " . sqlesc($id) . "
               AND sender = " . sqlesc(user::$current['id']) . "
               AND status = 'Pending'") or sqlerr(__FILE__, __LINE__);

    $db->query("UPDATE users
               SET invites = invites + 1
               WHERE id = " . sqlesc(user::$current['id'])) or sqlerr(__FILE__, __LINE__);

    $update['invites'] = (user::$current['invites'] + 1);
    $Memcache->begin_transaction('statusbar::user::stats::' . user::$current['id']);
    $Memcache->update_row(false, array('invites' => $update['invites']));
    $Memcache->commit_transaction(1800);

    $Memcache->begin_transaction('user::profile::stats::' . user::$current['id']);
    $Memcache->update_row(false, array('invites' => $update['invites']));
    $Memcache->commit_transaction(1800);

    header("Location: ?do=view_page");
}

//-- Confirm The Invite --//
if ($do ='confirm_account')
{
    $userid = (isset($_GET['userid']) ? (int)$_GET['userid'] : (isset($_POST['userid']) ? (int)$_POST['userid'] : ''));

    if (!is_valid_id($userid))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    $select = $db->query("SELECT id, username
                         FROM users
                         WHERE id = " . sqlesc($userid) . "
                         AND invitedby = " . sqlesc(user::$current['id'])) or sqlerr(__FILE__, __LINE__);

    $assoc = $select->fetch_assoc();

    if (!$assoc)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['text_no_userid']}");
    }

    isset($_GET['sure']) && $sure = htmlentities($_GET['sure']);

    if (!$sure)
    {
        error_message_center("info" ,
                             "{$lang['text_conf_account']}", "{$lang['text_conf_sure']}" . security::html_safe($assoc['username']) . " '{$lang['text_accounts']}<br /><br />
                             {$lang['text_click']}<a class='btn' href='?do=confirm_account&amp;userid={$userid}&amp;sender=" . user::$current['id'] . "&amp;sure=yes'>{$lang['text_here']}</a> {$lang['text_click_conf']}<br /><br />
                             {$lang['text_click']}<a class='btn' href='?do=view_page'>{$lang['text_here']}</a>{$lang['text_go_back']}");
    }

    $db->query("UPDATE users
               SET status = 'confirmed'
               WHERE id = " . sqlesc($userid) . "
               AND invitedby = " . sqlesc(user::$current['id']) . "
               AND status='pending'") or sqlerr(__FILE__, __LINE__);
	
	$Memcache->delete_value('invited::status::' . $userid);

    header("Location: ?do=view_page");
}

?>