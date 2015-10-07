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

$lang = array_merge(load_language('restoreclass'),
                    load_language('global'));

if (user::$current['override_class'] == 255)
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_denied']}");
}

$db->query("UPDATE users
           SET override_class = 255
           WHERE id = " . user::$current['id']);

header("Location: $site_url/index.php");

die();

?>