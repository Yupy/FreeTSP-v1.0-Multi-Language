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

$lang = array_merge(load_language('flushghosts'),
                    load_language('global'));

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$res = $db->query("SELECT id
                  FROM users
                  WHERE id = " . sqlesc($id)) or die();

$row = $res->fetch_assoc() or error_message("error",
                                                   "{$lang['gbl_error']}",
                                                   "{$lang['err_no_user']}");
$userid = (int)$row['id'];

if ($userid == user::$current['id'] || (get_user_class() >= UC_MODERATOR))
{
    $res  = $db->query("SELECT COUNT(id)
                        FROM peers
                        WHERE userid = " . $userid) or sqlerr();

    $ghost       = $res->fetch_row();
    $ghostnumber = (int)$ghost['0'];

    $db->query("DELETE
               FROM peers
               WHERE userid = " . sqlesc($id));

    site_header();

    begin_frame("{$lang['title_flushghosts']}");

    echo ('<meta http-equiv="refresh" content="1;url='. $_SERVER['HTTP_REFERER'] .'">');

    display_message("success",
                    "{$lang['gbl_success']}",
                    "{$lang['text_flushed']}$ghostnumber{$lang['text_flushed1']}");

    end_frame();

    site_footer();
}
else
{
    error_message("warn",
                  "{$lang['gbl_sorry']}",
                  "{$lang['err_inv_flush']}");

    end_frame();
}

    site_footer();

?>