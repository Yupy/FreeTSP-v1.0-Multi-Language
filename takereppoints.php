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

//-- FreeTSP Reputation System v1.0 - Please Leave Credits In Place. --//
//-- Reputation Mod - - Subzero Thanks to google.com! for the reputation image! --//
//-- File Completed 02 July 2010 At 19:42 Secound Offical Submit --//

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');

db_connect();
logged_in();

$lang = array_merge(load_language('takereppoints'),
                    load_language('global'));

$id = intval(0 + $_GET['id']);

$res = $db->query("SELECT id
                  FROM users
                  WHERE id = " . sqlesc($id)) or die();

$row = $res->fetch_assoc() or error_message_center("error",
                                                       "{$lang['gbl_error']}",
                                                       "{$lang['err_no_user']}");

$userid = $row['id'];

if ($userid == user::$current['id'])
{
    error_message_center("warn",
                         "{$lang['gbl_sorry']}",
                         "{$lang['err_no_rep_youself']}");
}

site_header("", false);
{

    //-- Lets Update The Database With New Reputation Points Do Not Alter If You Do Not Know What You Are Doing - Subzero --//
    $db->query("UPDATE users
               SET reputation = reputation + 1
               WHERE id = $id") or sqlerr(__FILE__, __LINE__);

    //begin_frame("{$lang['title_add_rep']}");

    $returnto = security::html_safe($_SERVER['HTTP_REFERER']);

        if ($returnto)
        {
            display_message_center("success",
                                   "{$lang['gbl_success']}",
                                   "{$lang['text_rep_added']}<br /><br /><a class='btn' href='$returnto'>{$lang['text_click_here']}</a><br /><br />{$lang['text_return']}");
        }

    //header("Refresh: 3; url='userdetails.php?id=$id'");

    //end_frame();
}

site_footer();

?>