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
require_once(FUNC_DIR . 'function_page_verify.php');

db_connect();

$lang = array_merge(load_language('takelogin'),
                    load_language('func_vfunctions'),
                    load_language('global'));

if (!mkglobal("username:password:submitme"))
{
    die();
}

$sha = sha1(vars::$realip);

if (is_file('' . $dictbreaker . '/' . $sha) && filemtime('' . $dictbreaker . '/' . $sha) > (vars::$timestamp - 8))
{
    @fclose(@fopen('' . $dictbreaker . '/' . $sha, 'w'));

    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_wait_time']}");
}

$newpage = new page_verify();
$newpage->check('_login_');

failedloginscheck();

$res = $db->query("SELECT id, passhash, secret, enabled
                  FROM users
                  WHERE username = " . sqlesc($username) . "
                  AND status = 'confirmed'");

$row = $res->fetch_assoc();

if (!$row)
{
    $ip    = sqlesc(vars::$ip);
    $added = sqlesc(get_date_time());

    $a = @$db->query("SELECT COUNT(*)
                      FROM loginattempts
                      WHERE ip = $ip") or sqlerr(__FILE__, __LINE__);
	$a = @$a->fetch_row();

    if ($a[0] == 0)
    {
        $db->query("INSERT INTO loginattempts (ip, added, attempts)
                   VALUES ($ip, $added, 1)") or sqlerr(__FILE__, __LINE__);
    }
    else
    {
        $db->query("UPDATE loginattempts
                   SET attempts = attempts + 1
                   WHERE ip = $ip") or sqlerr(__FILE__, __LINE__);
    }

    @fclose(@fopen('' . $dictbreaker . '/' . sha1(vars::$realip), 'w'));

    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "<a href='/login.php'>{$lang['err_login_failed']}</a>");
}

if ($row['passhash'] != md5($row['secret'] . $password . $row['secret']))
{
    $ip    = sqlesc(vars::$ip);
    $added = sqlesc(get_date_time());

    $a = @$db->query("SELECT COUNT(*)
                     FROM loginattempts
                     WHERE ip = $ip") or sqlerr(__FILE__, __LINE__);
	$a = @$a->fetch_row();

    if ($a[0] == 0)
    {
        $db->query("INSERT INTO loginattempts (ip, added, attempts)
                   VALUES ($ip, $added, 1)") or sqlerr(__FILE__, __LINE__);
    }
    else
    {
        $db->query("UPDATE loginattempts
                   SET attempts = attempts + 1
                   WHERE ip = $ip") or sqlerr(__FILE__, __LINE__);
    }

    @fclose(@fopen('' . $dictbreaker . '/' . sha1(vars::$realip), 'w'));

    $to  = ((int)$row['id']);
    $sub = "{$lang['msg_alert']}";
    $msg = "[b][color=red]{$lang['msg_alert']}[/b][/color]\n\n{$lang['msg_account_id']}{$row['id']}{$lang['msg_somebody']}[b]$username![/b]){$lang['msg_login_fail']}"."\n\n{$lang['msg_their']}[b]{$lang['msg_ip_addy']}[/b]{$lang['msg_was']}: ([b]$ip " . @gethostbyaddr($ip) . "[/b])"."\n\n{$lang['msg_report']}\n\n - {$lang['msg_thanks']}\n";

    $sql = "INSERT INTO messages (subject, sender, receiver, msg, added)
            VALUES ('$sub', '$from', '$to', " . sqlesc($msg) . ", $added);";

    $res = $db->query($sql) or sqlerr(__FILE__, __LINE__);

    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "<a href='/login.php'>{$lang['err_login_failed']}</a>");
}

if ($row['enabled'] == "no")
{
    error_message_center("info",
                         "{$lang['gbl_info']}",
                         "{$lang['text_disabled']}");
}

if ($submitme != 'X')
{
    error_message_center("info",
                         "{$lang['gbl_info']}",
                         "{$lang['text_click_x']}");
}

logincookie($row['id'], $row['passhash']);

$ip = sqlesc(vars::$ip);

$db->query("DELETE
           FROM loginattempts
           WHERE ip = $ip");

$returnto = str_replace('&amp;', '&', security::html_safe($_POST['returnto']));

if (!empty($returnto))
{
    header("Location: " . $returnto);
}
else
{
    header("Location: index.php");
}

?>