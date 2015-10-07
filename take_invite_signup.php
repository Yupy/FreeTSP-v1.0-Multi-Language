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

$lang = array_merge(load_language('take_invite_signup'));

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
		global $HTTP_SERVER_VARS;

		$sd = @fsockopen($HTTP_SERVER_VARS['REMOTE_ADDR'], $port, $errno, $errstr, 1);

		if ($sd)
    	{
    		fclose($sd);
    		return true;
    	}
    	else
    	return false;
	}

	function isproxy()
	{
		$ports = array(80, 88, 1075, 1080, 1180, 1182, 2282, 3128, 3332, 5490, 6588, 7033, 7441, 8000, 8080, 8085, 8090, 8095, 8100, 8105, 8110, 8888, 22788);

		for ($i = 0;
        $i < count($ports);
        ++$i)

		if (isportopen($ports[$i]))
        {
            return true;
        }
		return false;
	}
*/

	if (empty($wantusername) || empty($wantpassword) || empty($email) || empty($invite))
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
	if ($_POST['rulesverify'] != "yes" || $_POST['faqverify'] != "yes" || $_POST['ageverify'] != "yes")
	{
		error_message_center("error",
                             "{$lang['err_signup_fail']}",
                             "{$lang['err_unqualified']}");
	}

	//-- Check If Email Addy Is Already In Use --//
	$a = @$db->query("SELECT COUNT(*)
                      FROM users
                      WHERE email = ". sqlesc($email)) or die($db->error);
	$a = @$a->fetch_row();

	if ($a[0] != 0)
	{
		error_message_center("error",
                             "{$lang['err_signup_fail']}",
                             "{$lang['err_email_addy']}<b>" . security::html_safe($email) . "</b>{$lang['err_email_used']}");
	}

	$select_inv = $db->query("SELECT sender, receiver, status
                             FROM invite_codes
                             WHERE code = " . sqlesc($invite)) or die($db->error);

	$rows = $select_inv->num_rows;
	$assoc = $select_inv->fetch_assoc();

	if ($rows == 0)
	{
		error_message_center("error",
                             "{$lang['err_signup_fail']}",
                             "{$lang['err_inv_not_found']}\n{$lang['err_req_invite']}");
	}

	if ($assoc['receiver'] != 0)
	{
		error_message_center("error",
                             "{$lang['err_signup_fail']}",
                             "{$lang['err_inv_taken']}\n{$lang['err_inv_req_new']}");
	}

/*
	//-- Do Simple Proxy Check --//
	if (isproxy())
	{
		error_message_center("error",
                             "{$lang['err_signup_fail']}",
                             "{$lang['text_proxy']}<a href=" . $site_url . :81"login.php>{$lang['text_port']}</a>{$lang['text_bypass']}<p><b>{$lang['text_note']}</b>{$lang['text_accessible']}");
	}
*/

	$secret       = mksecret();
	$wantpasshash = md5($secret . $wantpassword . $secret);
	$editsecret   = (!$arr[0] ? "" : mksecret());

	$new_user = $db->query("INSERT INTO users (username, passhash, secret, editsecret, invitedby, email, " . (!$arr[0]?"class, " : "") . "added)
                           VALUES (" . implode(",", array_map("sqlesc", array($wantusername, $wantpasshash, $secret, $editsecret, (int) $assoc['sender'], $email))) . ", " . (!$arr[0] ? UC_USER . ", " : "") . "'" . get_date_time() . "')");

	if (!$new_user)
	{
		if ($db->errno == 1062)
		{
			error_message_center("error",
                                 "{$lang['err_signup_fail']}",
                                 "{$lang['err_user_taken']}");
		}
	}

	$id = $db->insert_id;

	$db->query("UPDATE invite_codes
               SET receiver = " . sqlesc($id) . ", status = 'Confirmed'
               WHERE sender = " . sqlesc((int)$assoc['sender']) . "
               AND code = " . sqlesc($invite)) or sqlerr(__FILE__, __LINE__);

	write_log("{$lang['writelog_user_acc']}" . security::html_safe($wantusername) . "{$lang['writelog_created']}");

	error_message_center("success",
                         "{$lang['text_success']}",
                         "{$lang['text_confirm']}");

?>