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

$newpage = new page_verify();
$newpage->check('_login_');

$lang = array_merge(load_language('takesignup'));

$res = $db->query("SELECT COUNT(*)
                  FROM users") or sqlerr(__FILE__, __LINE__);

$arr = $res->fetch_row();

if ($arr[0] >= $max_users)
{
    error_message_center("info",
                         "{$lang['err_sorry']}",
                         "{$lang['err_limit']}");
}

if (!mkglobal("wantusername:wantpassword:passagain:email"))
{
    die();
}

function validusername($username)
{
    if ($username == "")
    {
        return false;
    }

    //-- The Following Characters Are Allowed In User Names --//
    $allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    for ($i = 0;
        $i < strlen($username);
        ++$i)
    {
        if (strpos($allowedchars, $username[$i]) === false)
        {
            return false;
        }
    }
    return true;
}

/*
function isportopen($port)
{
    $sd = @fsockopen($_SERVER['REMOTE_ADDR'], $port, $errno, $errstr, 1);

    if ($sd)
    {
        fclose($sd);
        return true;
    }
    else
    {
        return false;
    }
}
*/

if (empty($wantusername) || empty($wantpassword) || empty($email))
{
    error_message_center("error",
                         "{$lang['err_signup_fail']}",
                         "{$lang['err_blank_fields']}");
}

if (strlen($wantusername) > 12)
{
    error_message_center("error",
                         "{$lang['err_signup_fail']}",
                         "{$lang['err_name_long']}");
}

if ($wantpassword != $passagain)
{
    error_message_center("error",
                         "{$lang['err_signup_fail']}",
                         "{$lang['err_pass_mismatch']}");
}

if (strlen($wantpassword) < 6)
{
    error_message_center("error",
                         "{$lang['err_signup_fail']}",
                         "{$lang['err_pass_short']}");
}

if (strlen($wantpassword) > 40)
{
    error_message_center("error",
                         "{$lang['err_signup_fail']}",
                         "{$lang['err_pass_long']}");
}

if ($wantpassword == $wantusername)
{
    error_message_center("error",
                         "{$lang['err_signup_fail']}",
                         "{$lang['err_pass_user']}");
}

if (!security::valid_email($email))
{
    error_message_center("error",
                         "{$lang['err_signup_fail']}",
                         "{$lang['err_inv_email']}");
}

if (!validusername($wantusername))
{
    error_message_center("error",
                         "{$lang['err_signup_fail']}",
                         "{$lang['err_inv_user']}");
}

//-- Make Sure User Agrees To Everything... --//
if ($_GET['rulesverify'] != "yes" || $_GET['faqverify'] != "yes" || $_GET['ageverify'] != "yes")
{
    error_message_center("info",
                         "{$lang['err_signup_fail']}",
                         "{$lang['err_unqualified']}");
}

//-- Check If Email Addy Is Already In Use --//
$a = @$db->query("SELECT COUNT(*)
                  FROM users
                  WHERE email = '$email'") or die($db->error);
$a = @$a->fetch_row();

if ($a[0] != 0)
{
    error_message_center("info",
                         "{$lang['err_signup_fail']}",
                         "{$lang['err_email_addy']}<b>" . security::html_safe($email) . "</b>{$lang['err_email_used']}");
}

if (!$email_confirm)
{
    $secret       = mksecret();
    $wantpasshash = md5($secret . $wantpassword . $secret);
    $editsecret   = (!$arr[0] ? "" : mksecret());

    $ret = $db->query("INSERT INTO users (username, passhash, secret, editsecret, email, status, " . (!$arr[0] ? "class, " : "") . "added)
                     VALUES (" . implode(", ", array_map("sqlesc", array($wantusername, $wantpasshash, $secret, $editsecret, $email, (!$arr[0] ? 'confirmed' : 'confirmed')))) . ", " . (!$arr[0] ? UC_MANAGER . ", " : "") . "'" . get_date_time() . "')");

    if (!$ret)
     {
        if ($db->errno == 1062)
        {
            error_message_center("info",
                                 "{$lang['err_signup_fail']}",
                                 "{$lang['err_user_taken']}");
        }
        error_message_center("info",
                             "{$lang['err_signup_fail']}",
                             "{$lang['err_signup_fail']}");
    }

    $id = $db->insert_id;

    write_log("{$lang['writelog_user_acc']}" . security::html_safe($wantusername) . "{$lang['writelog_created']}");

    $psecret = md5($editsecret);

    logincookie($id, $wantpasshash);

    header("Refresh: 0; url=$site_url/confirm.php?id=$id&secret=$psecret");
}
else
{
    $secret       = mksecret();
    $wantpasshash = md5($secret . $wantpassword . $secret);
    $editsecret   = (!$arr[0] ? "" : mksecret());

    $ret = $db->query("INSERT INTO users (username, passhash, secret, editsecret, email, status, " . (!$arr[0] ? "class, " : "") . "added)
                      VALUES (" . implode(", ", array_map("sqlesc", array($wantusername, $wantpasshash, $secret, $editsecret, $email, (!$arr[0] ? 'confirmed' : 'pending')))) . ", " . (!$arr[0] ? UC_MANAGER . ", " : "") . "'" . get_date_time() . "')");

    if (!$ret)
    {
        if ($db->errno == 1062)
        {
            error_message_center("info",
                                 "{$lang['err_signup_fail']}",
                                 "{$lang['err_user_taken']}");
        }
        error_message_center("info",
                             "{$lang['err_signup_fail']}",
                             "{$lang['err_signup_fail']}");
    }

    $id = $db->insert_id;

    write_log("{$lang['writelog_user_acc']}" . security::html_safe($wantusername) . "{$lang['writelog_created']}");

    $psecret = md5($editsecret);

//-- Start Email Confirmation --//

$body = <<<EOD
{$lang['msg_email']}$site_name{$lang['msg_email2']}$email{$lang['msg_email3']}

{$lang['msg_email4']}{$_SERVER['REMOTE_ADDR']}{$lang['msg_email5']}

{$lang['msg_email6']}

$site_url/confirm.php?id=$id&secret=$psecret

{$lang['msg_email7']}$site_name.
EOD;

    if ($arr[0])
    {
        mail($email, "$site_name{$lang['text_user_conf']}", $body, "{$lang['text_from']}$site_email", "-f$site_email");
    }
    else
    {
        logincookie($id, $wantpasshash);
    }

    header("Refresh: 0; url=ok.php?type=" . (!$arr[0] ? "manager" : ("signup&email=" . urlencode($email))));

    //-- End Email Confirmation --//

}

?>