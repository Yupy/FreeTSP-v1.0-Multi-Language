<?php

/**
**************************
** FreeTSP Version: 1.0 **
**************************
** http://www.freetsp.info
** https://github.com/Krypto/FreeTSP
** Licence Info: GPL
** Copyright © 2010 FreeTSP v1.0
** A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
** Project Leaders: Krypto, Fireknight.
** Original Code by Dodge
** Updated For FTSP by Fireknight
** Recoded for logging of staff actions By Rushed & Fireknight
**/

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_torrenttable.php');

db_connect(false);
logged_in();

$lang = array_merge(load_language('ratio'),
                    load_language('global'));

if (get_user_class() < UC_SYSOP)
{
    error_message("warn",
                  "{$lang['gbl_warning']}",
                  "{$lang['err_denied']}");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if ($_POST['username'] == '' || $_POST['uploaded'] == '' || $_POST['downloaded'] == '')
    {
        error_message("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_missing_data']}");
    }

    $username = sqlesc($_POST['username']);

    if ($_POST['bytes']=='1')
    {
        $uploaded   = (int)$_POST['uploaded'] * 1024 * 1024;
        $downloaded = (int)$_POST['downloaded'] * 1024 * 1024;
    }

    elseif ($_POST['bytes']=='2')
    {
        $uploaded   = (int)$_POST['uploaded'] * 1024 * 1024 * 1024;
        $downloaded = (int)$_POST['downloaded'] * 1024 * 1024 * 1024;
    }

    elseif ($_POST['bytes']=='3')
    {
        $uploaded   = (int)$_POST['uploaded'] * 1024 * 1024 * 1024 * 1024;
        $downloaded = (int)$_POST['downloaded'] * 1024 * 1024 * 1024 * 1024;
    }

    if ($_POST['action'] =='1')
    {
        $result = $db->query("SELECT id, modcomment, uploaded, downloaded
                             FROM users
                             WHERE username = $username") or sqlerr(__FILE__, __LINE__);

        $arr = $result->fetch_assoc();

        $id          = intval(0 + $arr['id']);
        $uploaded1   = 0 + $arr['uploaded'] + $uploaded;
        $downloaded1 = 0 + $arr['downloaded'] + $downloaded;
        $uploaded2   = misc::mksize($arr['uploaded']);
        $downloaded2 = misc::mksize($arr['downloaded']);
        $upped       = misc::mksize($uploaded);
        $downed      = misc::mksize($downloaded);

        $modcomment = security::html_safe($arr['modcomment']);

        $modcomment = gmdate('Y-m-d') ." {$lang['text_add_upload']} $upped {$lang['text_add_download']} $downed {$lang['text_by']} " . security::html_safe(user::$current['username']) . ". {$lang['text_orig_upload']} $uploaded2 {$lang['text_orig_download']} $downloaded2.\n\n" . $modcomment;

        $modcom = sqlesc($modcomment);

        write_stafflog("{$lang['stafflog_account']} <a href='userdetails.php?id=$id'><strong>$username</strong></a> {$lang['stafflog_man_credit']} $upped {$lang['stafflog_upload']} $downed {$lang['stafflog_download']} <a href='userdetails.php?id=" . user::$current['id'] . "'><strong>" . security::html_safe(user::$current['username']) . "</strong></a> ");

        $db->query("UPDATE users
                     SET uploaded = $uploaded1, downloaded = $downloaded1, modcomment = $modcom
                     WHERE username = $username") or sqlerr(__FILE__, __LINE__);
    }

    elseif($_POST['action'] =='2')
    {
        $result = $db->query("SELECT id, modcomment, uploaded, downloaded
                             FROM users
                             WHERE username = $username") or sqlerr(__FILE__, __LINE__);

        $arr = $result->fetch_assoc();

        $id          = intval(0 + $arr['id']);
        $uploaded1   = 0 + $arr['uploaded'] - $uploaded;
        $downloaded1 = 0 + $arr['downloaded'] - $downloaded;
        $uploaded2   = misc::mksize($arr['uploaded']);
        $downloaded2 = misc::mksize($arr['downloaded']);
        $upped       = misc::mksize($uploaded);
        $downed      = misc::mksize($downloaded);

        $modcomment = security::html_safe($arr['modcomment']);

        $modcomment = gmdate('Y-m-d') . " {$lang['text_up_credit']} $upped {$lang['text_down_credit']} $downed {$lang['text_removed_by']} " . security::html_safe(user::$current['username']) . ". {$lang['text_orig_upload']} $uploaded2 {$lang['text_orig_download']} $downloaded2.\n\n" . $modcomment;

        $modcom = sqlesc($modcomment);

        write_stafflog("{$lang['stafflog_account']} <a href='userdetails.php?id=$id'><strong>$username</strong></a> {$lang['stafflog_had']} $upped {$lang['stafflog_upload_removed']} $downed {$lang['stafflog_download_removed']} <a href='userdetails.php?id=" . user::$current['id'] . "'><strong>" . security::html_safe(user::$current['username']) . "</strong></a> ");

        $db->query("UPDATE users
                     SET uploaded = $uploaded1, downloaded = $downloaded1, modcomment = $modcom
                     WHERE username = $username") or sqlerr(__FILE__, __LINE__);
    }

    elseif($_POST['action'] =='3')
    {
        $result = $db->query("SELECT id, modcomment, uploaded
                             FROM users
                             WHERE username = $username") or sqlerr(__FILE__, __LINE__);

        $arr = $result->fetch_assoc();

        $id          = intval(0 + $arr['id']);
        $uploaded1   = misc::mksize($arr['uploaded']);
        $upped       = misc::mksize($uploaded);

        $modcomment = security::html_safe($arr['modcomment']);

        $modcomment = gmdate('Y-m-d') . " {$lang['text_orig_upload']} $uploaded1 {$lang['text_replaced']} $upped {$lang['text_upload_by']} " . security::html_safe(user::$current['username']) . ".\n\n" . $modcomment;

        $modcom = sqlesc($modcomment);

        write_stafflog("{$lang['stafflog_account']} <a href='userdetails.php?id=$id'><strong>$username</a></strong> {$lang['text_orig_upload_by']} $uploaded1 {$lang['text_replaced']} $upped {$lang['text_upload_by']}<a href='userdetails.php?id=" . user::$current['id'] . "'><strong>" . security::html_safe(user::$current['username']) . "</strong></a> ");

        $db->query("UPDATE users
                     SET uploaded = $uploaded, modcomment = $modcom
                     WHERE username = $username") or sqlerr(__FILE__, __LINE__);
    }

    elseif($_POST['action'] =='4')
    {
        $result = $db->query("SELECT id, modcomment, downloaded
                             FROM users
                             WHERE username = $username") or sqlerr(__FILE__, __LINE__);

        $arr = $result->fetch_assoc();

        $id          = intval(0 + $arr['id']);
        $downloaded1 = misc::mksize($arr['downloaded']);
        $downed      = misc::mksize($downloaded);

        $modcomment = security::html_safe($arr['modcomment']);

        $modcomment = gmdate('Y-m-d') . " {$lang['text_orig_down_of']} $downloaded1 {$lang['text_replaced']} $downed {$lang['text_down_by']} " . htmlspecialchars(user::$current['username']) . ".\n\n" . $modcomment;

        $modcom = sqlesc($modcomment);

        write_stafflog("{$lang['stafflog_account']} <a href='userdetails.php?id=$id'><strong>$username</a></strong> {$lang['stafflog_orig_down']} $downloaded1 {$lang['stafflog_replaced']} $downed {$lang['stafflog_download']} <a href='userdetails.php?id=" . user::$current['id'] . "'><strong>" . security::html_safe(user::$current['username']) . "</strong></a> ");

        $db->query("UPDATE users
                     SET downloaded = $downloaded, modcomment = $modcom
                     WHERE username = $username") or sqlerr(__FILE__, __LINE__);
    }

    $id = intval(0 + $arr['id']);

    header("Location: $site_url/userdetails.php?id=$id");

    die;
}

?>