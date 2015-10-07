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

db_connect(true);
logged_in();

$lang = array_merge(load_language('delete_member'),
                    load_language('func_bbcode'),
                    load_language('global'));

site_header("{$lang['title_del_acc']}", false);

if (get_user_class() < UC_MODERATOR)
{
    error_message_center("error",
                         "{$lang['err_denied']}",
                         "{$lang['err_not_permitted']}");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $action = isset($_GET['action']) ? security::html_safe($_GET['action']) : '';

    if ($action == 'deluser')
    {
        $username = trim($_POST['username']);

        display_message_center("warn",
                               "{$lang['gbl_sanity']}",
                               "{$lang['text_del']}<strong>$username</strong><br />
                               <form method='post' action='delete_member.php&amp;action=sure'>
                                   <input type='hidden' name='username' size='20' value='$username' />
                                   <input type='submit' class='btn' value='{$lang['btn_delete']}$username' />
                               </form>");
    }

    if ($action == 'sure')
    {
        $username = trim($_POST['username']);

        display_message_center("warn",
                               "{$lang['err_final_check']}",
                               "{$lang['text_are_you']}<strong>{$lang['text_really']}</strong>{$lang['text_del_sure']}<strong>$username</strong><br />
                               <form method='post' action='delete_member.php&amp;action=delete'>
                                   <input type='hidden' name='username' size='20' value='$username' />
                                   <input type='submit' class='btn' value='{$lang['btn_delete']}$username' />
                               </form>");
    }

    if ($action == 'delete')
    {
        $username = trim($_POST['username']);

        $res = $db->query("SELECT *
                          FROM users
                          WHERE username = " . sqlesc($username)) or sqlerr(__FILE__, __LINE__);

        $arr = $res->fetch_assoc();

        $id = (int)$arr['id'];

        $res = $db->query("DELETE
                          FROM users
                          WHERE id = $id") or sqlerr(__FILE__, __LINE__);

        write_stafflog("{$lang['stafflog_member']}<strong>$username</strong> -- {$lang['stafflog_del_by']}" . user::$current['username']);

        display_message_center("success",
                               "{$lang['text_deleted']}",
                               "{$lang['text_member']}<strong>$username</strong>{$lang['text_was_deleted']}");
    }
}

site_footer();

?>