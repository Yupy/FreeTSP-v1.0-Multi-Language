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

$lang = array_merge(load_language('recover'),
                    load_language('global'));

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $email = trim($_POST['email']);

    if (!security::valid_email($email))
    {
        error_message("error",
                      "{$lang[gbl_error]}",
                      "{$lang['err_need_email']}");
    }

    $res = $db->query("SELECT *
                      FROM users
                      WHERE email = " . sqlesc($email) . "
                      LIMIT 1") or sqlerr();

    $arr = $res->fetch_assoc() or error_message("error",
                                                    "{$lang[gbl_error]}",
                                                    "{$lang['err_no_email_db']}");
    $sec = mksecret();

    $db->query("UPDATE users
               SET editsecret = " . sqlesc($sec) . "
               WHERE id = " . (int)$arr['id']) or sqlerr();

    if (!$db->affected_rows)
    {
        error_message("error",
                      "{$lang['err_db_error']}",
                      "{$lang['err_contact_admin']}");
    }

    //$hash = md5($sec . $email . $arr['passhash'] . $sec);
    $hash = md5($sec . $arr['email'] . $arr['passhash'] . $sec);
	$var_ip = vars::$realip;

$body = <<<EOD
{$lang['text_email1']}($email){$lang['text_email2']}

{$lang['text_email3']}{$var_ip}.

{$lang['text_email4']}


{$lang['text_email5']}

$site_url/recover.php?id={$arr['id']}&secret=$hash


{$lang['text_email6']}

--
$site_name
EOD;

    @mail($arr['email'], "$site_name{$lang['text_reset_conf']}", $body, "{$lang['text_from']}$site_email", "-f$site_email")
        or
        error_message("error",
                      "{$lang[gbl_error]}",
                      "{$lang['err_send_email']}");

        error_message("success",
                      "{$lang['gbl_success']}",
                      "{$lang['text_conf_email']}<br /><br />{$lang['text_allow_time']}");
}
elseif ($_GET)
{
    $id  = intval(0 + $_GET['id']);
    $md5 = $_GET['secret'];

    if (!$id)
    {
        httperr();
    }

    $res = $db->query("SELECT username, email, passhash, editsecret
                      FROM users
                      WHERE id = $id");

    $arr   = $res->fetch_assoc() or httperr();
    $email = unesc($arr['email']);
    $sec   = hash_pad($arr['editsecret']);

    if (preg_match('/^ *$/s', $sec))
    {
        httperr();
    }

    if ($md5 != md5($sec . $email . $arr['passhash'] . $sec))
    {
        httperr();
    }

    //-- Generate New Password --//
    $chars       = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $newpassword = "";

    for ($i = 0; $i < 10; $i++)
    {
        $newpassword .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    $sec         = mksecret();
    $newpasshash = md5($sec . $newpassword . $sec);

    $db->query("UPDATE users
               SET secret = " . sqlesc($sec) . ", editsecret='', passhash = " . sqlesc($newpasshash) . "
               WHERE id = $id
               AND editsecret = " . sqlesc($arr['editsecret']));

    if (!$db->affected_rows)
    {
        error_message("error",
                      "{$lang[gbl_error]}",
                      "{$lang['err_update_db']}");
    }

$body = <<<EOD
{$lang['text_email1_pt2']}

{$lang['text_email2_pt2']}

{$lang['text_email3_pt2']}{$arr['username']}
{$lang['text_email1_pt4']}$newpassword

{$lang['text_email5_pt2']}$site_url/login.php

--
$site_name
EOD;

    @mail($email, "$site_name{$lang['text_email_details']}", $body, "{$lang['text_email_from']}$site_email", "-f$site_email")
        or
        error_message("error",
                      "{$lang[gbl_error]}",
                      "{$lang['err_send_email']}");

        error_message("success",
                      "{$lang['gbl_success']}",
                      "{$lang['text_email_to']}<span style='font-weight : bold;'>$email</span>.<br /><br />{$lang['text_allow_time']}");
}

?>
