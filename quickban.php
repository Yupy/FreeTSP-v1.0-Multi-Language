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

$lang = array_merge(load_language('quickban'),
                    load_language('func_bbcode'),
                    load_language('global'));

if (get_user_class() < UC_SYSOP)
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_not_here']}");
}

$id = intval(0 + $_GET['id']);

$res = $db->query("SELECT id
                  FROM users
                  WHERE id = " . sqlesc($id)) or die();

$row = $res->fetch_assoc() or error_message_center("error",
                                                       "{$lang['gbl_error']}",
                                                       "{$lang['err_not_found']}");

$userid = (int)$row['id'];

//--Sysop Can Not Ban Thereselfs! --//
if ($userid == user::$current['id'])
{
    error_message_center("warn",
                         "{$lang['gbl_sorry']}",
                         "{$lang['err_ban_self']}");

    site_header();
}

//-- Start Error Meassage If Already Banned --//
$res = $db->query("SELECT enabled
                  FROM users
                  WHERE id = $userid");

$row    = $res->fetch_assoc();
$banned = $row['enabled'];

if ($banned == no)
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_disabled']}");
}
//--Finish Error Meassage If Already Banned --//

//--Start Error Message If Member Is A Sysop --//
$res = $db->query("SELECT class
                  FROM users
                  WHERE id = $userid");

$row   = $res->fetch_assoc();
$class = (int)$row['class'];

if ($class >= 6) //-- Change Class ID To Suit Your Coding --//
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_ban_sysop']}");
}
//--Finish Error Message If Member Is A Sysop --//

//--Run The Code --//
site_header();

$res = $db->query("SELECT *
                  FROM users
                  WHERE id = $userid") or sqlerr(__FILE__, __LINE__);

$row      = $res->fetch_assoc();
$ip       = unesc($row['ip']);
$username = security::html_safe($row['username']);
$longip   = ip2long($ip);
$comment  = $lang['text_ban_by'];
$added    = sqlesc(get_date_time());

$db->query("UPDATE users
           SET enabled = 'no'
           WHERE id = $userid") or sqlerr(__FILE__, __LINE__);

$db->query("INSERT INTO bans (added, addedby, first, last, comment)
           VALUES($added, " . user::$current['id'] . ", '$longip', '$longip', '$comment')") or sqlerr(__FILE__, __LINE__);

begin_frame("{$lang['title_ban']}");

display_message_center("success",
                       "{$lang['gbl_success']}",
                       "<strong>$username</strong>{$lang['text_banned']}<br />
                        <br />{$lang['text_ret_to']}<a href='userdetails.php?id=$id'><strong>$username{$lang['text_page1']}</strong>{$lang['text_page2']}</a>
                        <br />{$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");

end_frame();

site_footer();

?>