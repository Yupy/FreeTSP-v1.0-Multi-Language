<?php

/**
**************************
** FreeTSP Version: 1.0 **
**************************
** http://www.freetsp.info
** https://github.com/Krypto/FreeTSP
** Licence Info: GPL
** Copyright (C) 2010 FreeTSP v1.0 -
** A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
** Project Leaders: Krypto, Fireknight.
**/

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_page_verify.php');

db_connect(false);
logged_in();

$lang = array_merge(load_language('modtask'),
                    load_language('global'));

$newpage = new page_verify();
$newpage->check('_modtask_');

if (user::$current['class'] < UC_MODERATOR)
{
    die();
}

//-- Correct Call To Script --//
if ((isset($_POST['action'])) && ($_POST['action'] == 'edituser'))
{
    // Set user id
    if (isset($_POST['userid']))
    {
        $userid = (int)$_POST['userid'];
    }
    else
    {
        die();
    }

    //-- And Verify... --//
    if (!is_valid_id($userid))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_bad_id']}");
    }

    //-- Handle CSRF (Modtask Posts Form Other Domains, Especially To Update Class) --//
    require_once(FUNC_DIR . 'function_user_validator.php');

    if (!validate($_POST[validator], "ModTask_$userid"))
    {
        //die ("Invalid");
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    // Fetch Current User Data... --//
    $res = $db->query("SELECT *
                      FROM users
                      WHERE id = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);

    $user = $res->fetch_assoc() or sqlerr(__FILE__, __LINE__);

    //--Used In Writing To Staff Log --//
    $username = $user['username'];

    //-- Check To Make Sure Your Not Editing Someone Of The Same Or Higher Class --//
    if (user::$current['class'] <= $user['class'] && (user::$current['id'] != $userid && user::$current['class'] < UC_ADMINISTRATOR))
    {
        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "{$lang['err_same_class']}");
    }

    $updateset = array();

    $modcomment = (isset($_POST['modcomment']) && user::$current['class'] >= UC_SYSOP) ? $_POST['modcomment'] : $user['modcomment'];

    //-- Set Class --//
    if ((isset($_POST['class'])) && (($class = $_POST['class']) != $user['class']))
    {
        $curclass = $user['class'];

        if ($class >= UC_MANAGER || ($class >= user::$current['class']) || ($user['class'] >= user::$current['class']))
        {
            error_message_center("error",
                                 "{$lang['err_user_error']}",
                                 "{$lang['err_try_again']}");
        }

        if (!is_valid_user_class($class) || user::$current['class'] <= $_POST['class'])
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_bad_class']}");
        }

        //-- Notify User --//
        $what    = ($class > $user['class'] ? "{$lang['text_promoted']}" : "{$lang['text_demoted']}");
        $msg     = sqlesc("{$lang['msg_you_have']}$what{$lang['msg_to']}'" . get_user_class_name($class) . "'{$lang['msg_by']}" . user::$current['username']);
        $subject = sqlesc("{$lang['msg_class_subject']}");
        $added   = sqlesc(get_date_time());

        $db->query("INSERT INTO messages (sender, receiver, msg, added, subject)
                    VALUES(0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);

        $updateset[] = "class = " . sqlesc($class);

        $modcomment = gmdate("Y-m-d") . " - $what{$lang['text_to']}'" . get_user_class_name($class) . "'{$lang['text_by']}" . user::$current['username'] . ".\n\n" . $modcomment;

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
                       -- {$lang['stafflog_was']}$what{$lang['stafflog_from']}" . get_user_class_name($curclass) . "{$lang['stafflog_to']}" . get_user_class_name($class) . "
                       {$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
    }

    //-- Invite Rights --//
    if ((isset($_POST['invite_rights'])) && (($invite_rights = $_POST['invite_rights']) != $user['invite_rights']))
    {
        if ($invite_rights == 'yes')
        {
            $modcomment = gmdate("Y-m-d") . " -{$lang['text_invite_enabled']}" . security::html_safe(user::$current['username']) . ".\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_can_invite']}" . security::html_safe(user::$current['username']) . ".{$lang['msg_can_invite1']}");
            $subject    = sqlesc("{$lang['msg_invite_subject']}");
            $added      = sqlesc(get_date_time());

            $db->query("INSERT INTO messages (sender, receiver, msg, added, subject)
                        VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_invite_enabled']}--
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
        }

        if ($invite_rights == 'no')
        {
            $modcomment = gmdate("Y-m-d") . " -{$lang['text_invite_disabled']}" . security::html_safe(user::$current['username']) . ".\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_invite_removed']}" . security::html_safe(user::$current['username']) . ",{$lang['msg_bad_user']}");
            $subject    = sqlesc("{$lang['msg_invite_subject']}");
            $added      = sqlesc(get_date_time());

            $db->query("INSERT INTO messages (sender, receiver, msg, added, subject)
                        VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_invite_disabled']}--
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
        }

        $updateset[] = "invite_rights = " . sqlesc($invite_rights);
    }

    //-- Change Invite Amount --//
    if ((isset($_POST['invites'])) && (($invites = $_POST['invites']) != ($curinvites = $user['invites'])))
    {
		$modcomment = gmdate("Y-m-d") . " -{$lang['text_invite_change']}'$curinvites'{$lang['text_to']}'$invites'{$lang['text_by']}" . security::html_safe(user::$current['username']) . ".\n\n" . $modcomment;
		$updateset[] = "invites = " . sqlesc($invites);

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
		                -- {$lang['stafflog_invite_change']}'$invites'{$lang['stafflog_from']}'$curinvites'
		                {$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
    }

    //-- Clear Warning - Code Not Called For Setting Warning --//
    if (isset($_POST['warned']) && (($warned = $_POST['warned']) != $user['warned']))
    {
        $updateset[] = "warned = " . sqlesc($warned);
        $updateset[] = "warneduntil = '0000-00-00 00:00:00'";

        if ($warned == 'no')
        {
            $modcomment = gmdate("Y-m-d")." - {$lang['text_warn_removed']}" . user::$current['username'] . ".\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_warn_removed']}" . user::$current['username'] . ".");
            $subject    = sqlesc("{$lang['msg_warning']}");
            $added      = sqlesc(get_date_time());

            $db->query("INSERT INTO messages (sender, receiver, msg, added, subject)
                        VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_warn_removed']}--
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
        }
    }

    //-- Set Warning - Time Based --//
    if (isset($_POST['warnlength']) && ($warnlength = 0 + $_POST['warnlength']))
    {
        unset($warnpm);
        if (isset($_POST['warnpm']))
        {
            $warnpm = $_POST['warnpm'];
        }

        if ($warnlength == 255)
        {
            $modcomment  = gmdate("Y-m-d") . " - {$lang['text_warn_by']}" . user::$current['username'] . ".\n{$lang['text_reason']}$warnpm\n\n" . $modcomment;
            $msg         = sqlesc("{$lang['msg_warn_by']}" . user::$current['username'] . "" . ($warnpm ? "\n\n{$lang['msg_reason']}$warnpm" : ""));
            $updateset[] = "warneduntil = '0000-00-00 00:00:00'";

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_warn_reason']}'<strong>$warnpm</strong>'
							{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
        }
        else
        {
            $warneduntil = get_date_time(gmtime() + $warnlength * 604800);
            $dur         = $warnlength . "{$lang[text_week]}" . ($warnlength > 1 ? "{$lang[text_s]}" : "");
            $msg         = sqlesc("{$lang['msg_you_have_rcvd']} $dur {$lang['msg_rules_warn']}" . user::$current['username'] . "" . ($warnpm ? "\n\n{$lang['msg_reason']}$warnpm" : ''));
            $modcomment  = gmdate("Y-m-d") . " - {$lang['text_warn_for']}$dur {$lang['text_by']}" . user::$current['username'] . ".\n{$lang['text_reason']}$warnpm\n\n" . $modcomment;
            $updateset[] = "warneduntil = " . sqlesc($warneduntil);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_warn_for']}$dur {$lang['stafflog_reason']}'<strong>$warnpm</strong>'
							{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
        }
        $added = sqlesc(get_date_time());

        $db->query("INSERT INTO messages (sender, receiver, msg, added)
                    VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);

        $updateset[] = "warned = 'yes'";
    }

    //-- Clear Donor - Code Not Called For Setting Donor --//
    if (isset($_POST['donor']) && (($donor = $_POST['donor']) != $user['donor']))
    {
        $updateset[] = "donor = " . sqlesc($donor);

        //$updateset[] = "donoruntil = '0000-00-00 00:00:00'";
        if ($donor == 'no')
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_donor_removed']}{$lang['text_by']}" . user::$current['username'] . ".\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_donor_expired']}");
            $added      = sqlesc(get_date_time());

            $db->query("INSERT INTO messages (sender, receiver, msg, added)
                        VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_donor_removed']}--
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
        }
        elseif ($donor == 'yes')
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_donor_added']}" . user::$current['username'] . ".\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_donor_added_by']}--
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
        }
    }

    //-- Set Donor - Time Based --//
/*
    if ((isset($_POST['donorlength'])) && ($donorlength = 0 + $_POST['donorlength']))
    {
        if ($donorlength == 255)
        {
            $modcomment  = gmdate("Y-m-d") . " - {$lang['text_donor_set_by']}" . user::$current['username'] . ".\n\n" . $modcomment;
            $msg         = sqlesc("{$lang['msg_donor_status']}" . user::$current['username']);
            $updateset[] = "donoruntil = '0000-00-00 00:00:00'";

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_donor_added_by']}--
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
        }
        else
        {
            $donoruntil = get_date_time(gmtime() + $donorlength * 604800);
            $dur        = $donorlength . "{$text_week}" . ($donorlength > 1 ? "{$text_s}" : '');
            $msg        = sqlesc("{$lang['msg_donor_length']}$dur {$lang['msg_from']}" . user::$current['username']);
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_donor_set_for']}$dur {$lang['text_by']}" . user::$current['username'] . "\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_donor_status_for']}$dur
							{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "donoruntil = " . sqlesc($donoruntil);
        }
        $added = sqlesc(get_date_time());

        $db->query("INSERT INTO messages (sender, receiver, msg, added)
                    VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);

        $updateset[] = "donor = 'yes'";
    }
*/

    //-- Change Users Sig --//
    if ((isset($_POST['signature'])) && (($signature = $_POST['signature']) != ($cursignature = $user['signature'])))
    {
        $modcomment = gmdate("Y-m-d") . " - {$lang['text_sig_changed']}'$cursignature'{$lang['text_from']}'$signature'{$lang['text_by']}" . user::$current['username'] . ".\n\n" . $modcomment;

        $updateset[] = "signature = " . sqlesc($signature);

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
						-- {$lang['stafflog_sig_changed']}'$signature'{$lang['stafflog_from']}'$cursignature'
						{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
    }

    //-- Enable / Disable --//
    if ((isset($_POST['enabled'])) && (($enabled = $_POST['enabled']) != $user['enabled']))
    {
        if ($enabled == 'yes')
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_account_enabled']}" . user::$current['username'] . ".\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_account_enabled']}--
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
        }
        else
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_account_disabled']}" . user::$current['username'] . ".\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_account_disabled']}--
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a></strong>.\n");
        }

        $updateset[] = "enabled = " . sqlesc($enabled);
    }

    //-- Park / Un-Park --//
    if ((isset($_POST['parked'])) && (($parked = $_POST['parked']) != $user['parked']))
    {
        if ($parked == 'yes')
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_account_parked']}"  . user::$current['username'] . ".\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_account_parked']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");
        }
        else
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_account_unparked']}"  . user::$current['username'] . ".\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_account_unparked']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");
        }

        $updateset[] = "parked = " . sqlesc($parked);
    }

    //-- Forum Permission - Enable --//
    if ((isset($_POST['forumpos'])) && (($forumpos = $_POST['forumpos']) != $user['forumpos']))
    {
        if ($forumpos == 'yes')
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_forum_enabled']}"  . user::$current['username'] . ".\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_forum_enabled']}\n{$lang['msg_be_careful']}");
            $added      = sqlesc(get_date_time());
            $subject    = sqlesc("{$lang['msg_forum_status']}");

            $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                        VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_forum_enabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");
							 }
            $updateset[] = "forumpos = " . sqlesc($forumpos);
    }

    //-- Set Forum Permission - Disabled - Time based --//
    if (isset($_POST['forumposuntillength']) && ($forumposuntillength = 0 + $_POST['forumposuntillength']))
    {
        unset($forumposuntilpm);

        if (isset($_POST['forumposuntilpm']))
        {
            $forumposuntilpm = $_POST['forumposuntilpm'];
        }

        if ($forumposuntillength == 255)
        {
            $modcomment  = gmdate("Y-m-d") . " - {$lang['text_forum_disabled']} - "  . user::$current['username'] . ".\n{$lang['text_reason']}$forumposuntilpm\n\n" . $modcomment;
            $msg         = sqlesc("{$lang['msg_forum_disabled']} \n{$lang['msg_contact_staff']}");

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_forum_disabled']}'<strong>$forumposuntilpm</strong>'
							{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "forumposuntil = '0000-00-00 00:00:00'";
        }
        else
        {
            $forumposuntil = get_date_time(gmtime() + $forumposuntillength * 604800);
            $dur           = $forumposuntillength . "{$lang['text_week']}" . ($forumposuntillength > 1 ? "{$lang['text_s']}" : '');
            $msg           = sqlesc("{$lang['msg_forum_removed_time']}- $dur{$lang['text_by']}"  . user::$current['username'] . "" . ($forumposuntilpm ? "\n\n{$lang['msg_reason']}$forumposuntilpm" :''));

            $modcomment  = gmdate("Y-m-d") . " - {$lang['text_forum_disabled_time']}$dur{$lang['text_by']}"  . user::$current['username'] . ".\n{$lang['stafflog_reason']}$forumposuntilpm\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_forum_disabled_time']}$dur. {$lang['stafflog_reason']}'<strong>$forumposuntilpm</strong>'
							{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "forumposuntil = " . sqlesc($forumposuntil);
        }

        $added   = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_forum_status']}");

        $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                    VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

        $updateset[] = "forumpos = 'no'";
    }

    //-- Change Custom Title --//
    if ((isset($_POST['title'])) && (($title = $_POST['title']) != ($curtitle = $user['title'])))
    {
        $modcomment = gmdate("Y-m-d") . " - {$lang['text_title']}'$title'{$lang['text_from']}'$curtitle'{$lang['text_by']}"  . user::$current['username'] . ".\n\n" . $modcomment;

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
						-- {$lang['stafflog_title']}'$title'{$lang['stafflog_from']}'$curtitle'
						{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

        $updateset[] = "title = " . sqlesc($title);
    }

    //-- Change Members Username --//
    if ((isset($_POST['username'])) && (($username = $_POST['username']) != ($curusername = $user['username'])))
    {
        $modcomment = gmdate("Y-m-d") . " - {$lang['text_username']}'$username'{$lang['text_from']}'user::$currentname'{$lang['text_by']}"  . user::$current['username'] . ".\n\n" . $modcomment;

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
						-- {$lang['stafflog_username']}'$username'{$lang['stafflog_from']}'user::$currentname'
						{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

        $updateset[] = "username = " . sqlesc($username);
    }

    //-- Change Members Email --//
    if ((isset($_POST['email'])) && (($email = $_POST['email']) != ($curemail = $user['email'])))
    {
        $modcomment = gmdate("Y-m-d") . " - {$lang['text_email']}'$email'{$lang['text_from']}'$curemail'{$lang['text_by']}"  . user::$current['username'] . ".\n\n" . $modcomment;

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
						-- {$lang['stafflog_email']}'$email'{$lang['stafflog_from']}'$curemail'
						{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

        $updateset[] = "email = " . sqlesc($email);
    }

    //-- Change Users Info --//
    if ((isset($_POST['info'])) && (($info = $_POST['info']) != ($curinfo = $user['info'])))
    {
        $modcomment = gmdate("Y-m-d") . " - {$lang['text_info']}'$info'{$lang['text_from']}'$curinfo'{$lang['text_by']}"  . user::$current['username'] . ".\n\n" . $modcomment;

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
						-- {$lang['stafflog_info']}'$info'{$lang['stafflog_from']}'$curinfo'
						{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

        $updateset[] = "info = " . sqlesc($info);
    }

/*
    The Following Code Will Place The Old Passkey In The Mod Comment And Create A New Passkey.
    This Is Good Practice As It Allows Usersearch To Find Old Passkeys By Searching The Mod Comments Of Members.
*/

    //-- Reset Passkey --//
    if ((isset($_POST['resetpasskey'])) && ($_POST['resetpasskey']))
    {
        $newpasskey = md5($user['username'] . get_date_time() . $user['passhash']);
        $modcomment = gmdate("Y-m-d") . " - {$lang['text_passkey']}" . sqlesc($user['passkey']) . "{$lang['text_passkey_reset']}" . sqlesc($newpasskey) . "{$lang['text_by']}"  . user::$current['username'] . ".\n\n" . $modcomment;

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
						-- {$lang['stafflog_passkey']}" . sqlesc($user['passkey']) . "{$lang['stafflog_passkey_reset']}" . sqlesc($newpasskey) . "
						{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

        $updateset[] = "passkey=" . sqlesc($newpasskey);
    }

/*
    This Code Is For Use With The Safe Mod Comment Modification.
*/

    //-- Add Comment to ModComment --//
    if ((isset($_POST['addcomment'])) && ($addcomment = trim($_POST['addcomment'])))
    {
        $modcomment = gmdate("Y-m-d") . " - $addcomment -{$lang['text_by']}"  . user::$current['username'] . ".\n\n" . $modcomment;
    }

    //-- Upload Permission - Enable --//
    if ((isset($_POST['uploadpos'])) && (($uploadpos = $_POST['uploadpos']) != $user['uploadpos']))
    {
        if ($uploadpos == 'yes')
        {
            $modcomment = gmdate("Y-m-d")." - {$lang['text_upload_enabled']}"  . user::$current['username'] . ".\n\n" . $modcomment;
            $msg        = sqlesc("\n{$lang['msg_upload_enabled']}\n{$lang['msg_upload_careful']}");
            $added      = sqlesc(get_date_time());
            $subject    = sqlesc("{$lang['msg_upload_status']}");

            $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                        VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_upload_enabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");
		}

        $updateset[] = "uploadpos = " . sqlesc($uploadpos);
    }

    //-- Set Upload Permission - Disabled - Time Based --//
    if (isset($_POST['uploadposuntillength']) && ($uploadposuntillength = 0 + $_POST['uploadposuntillength']))
    {
        unset($uploadposuntilpm);

        if (isset($_POST['uploadposuntilpm']))
        {
            $uploadposuntilpm = $_POST['uploadposuntilpm'];
        }

        if ($uploadposuntillength == 255)
        {
            $modcomment  = gmdate("Y-m-d") . " - {$lang['text_upload_disabled']} - "  . user::$current['username'] . ".\n{$lang['text_reason']}$uploadposuntilpm\n\n" . $modcomment;
            $msg         = sqlesc("{$lang['msg_upload_disabled']}\n{$lang['msg_contact_staff']}");

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_upload_disabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "uploadposuntil = '0000-00-00 00:00:00'";
        }
        else
        {
            $uploadposuntil = get_date_time(gmtime() + $uploadposuntillength * 604800);
            $dur            = $uploadposuntillength . "{$lang['text_week']}" . ($uploadposuntillength > 1 ? "{$lang['text_s']}" : '');

            $msg            = sqlesc("{$lang['msg_upload_dur']} - $dur{$lang['text_by']}"  . user::$current['username'] . "".($uploadposuntilpm ? "\n\n{$lang['msg_reason']}$uploadposuntilpm" : ''));
            $modcomment     = gmdate("Y-m-d") . " - {$lang['text_upload_disabled_for']} $dur{$lang['text_by']}"  . user::$current['username'] . ".\n{$lang['text_reason']}$uploadposuntilpm\n\n" . $modcomment;

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
						-- {$lang['stafflog_upload_disabled_for']} - $dur
						{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[]    = "uploadposuntil = " . sqlesc($uploadposuntil);
        }

        $added   = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_upload_status']}");

        $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                    VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

        $updateset[] = "uploadpos = 'no'";
    }

    //-- Download Permission - Enable --//
    if ((isset($_POST['downloadpos'])) && (($downloadpos = $_POST['downloadpos']) != $user['downloadpos']))
    {
        if ($downloadpos == 'yes')
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_download_enabled']}"  . user::$current['username'] . ".\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_download_enabled']}\n{$lang['msg_be_careful']}");
            $added      = sqlesc(get_date_time());
            $subject    = sqlesc("{$lang['msg_download_status']}");

            $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                        VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_download_enabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");
		}

        $updateset[] = "downloadpos = " . sqlesc($downloadpos);
    }

    //-- Set Download Permission - Disabled - Time Based --//
    if (isset($_POST['downloadposuntillength']) && ($downloadposuntillength = 0 + $_POST['downloadposuntillength']))
    {
        unset($downloadposuntilpm);

        if (isset($_POST['downloadposuntilpm']))
        {
            $downloadposuntilpm = $_POST['downloadposuntilpm'];
        }

        if ($downloadposuntillength == 255)
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_download_disabled']} - "  . user::$current['username'] . ".\n{$lang['text_reason']}$downloadposuntilpm\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_download_disabled']}\n{$lang['msg_contact_staff']}");

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_download_disabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "downloadposuntil = '0000-00-00 00:00:00'";
        }
        else
        {
            $downloadposuntil = get_date_time(gmtime() + $downloadposuntillength * 604800);
            $dur              = $downloadposuntillength . "{$lang['text_week']}" . ($downloadposuntillength > 1 ? "{$lang['text_s']}" : '');
            $msg              = sqlesc("{$lang['msg_download_dur']} - $dur{$lang['text_by']}"  . user::$current['username'] . "" . ($downloadposuntilpm ? "\n\n{$lang['msg_reason']}$downloadposuntilpm" : ''));
            $modcomment  = gmdate("Y-m-d") . " - {$lang['text_download_disabled_for']} $dur{$lang['text_by']}"  . user::$current['username'] . ".\n{$lang['text_reason']}$downloadposuntilpm\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_download_disabled_for']} $dur
							{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "downloadposuntil = " . sqlesc($downloadposuntil);
        }

        $added   = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_download_status']}");

        $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                    VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

        $updateset[] = "downloadpos = 'no'";
    }

    // --Shoutbox Permission - Enable --//
    if ((isset($_POST['shoutboxpos'])) && (($shoutboxpos = $_POST['shoutboxpos']) != $user['shoutboxpos']))
    {
        if ($shoutboxpos == 'yes')
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_shoutbox_enabled']} "  . user::$current['username'] . ".\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_shoutbox_enabled']}\n{$lang['msg_be_careful']}");
            $added      = sqlesc(get_date_time());
            $subject    = sqlesc("{$lang['msg_shoutbox_status']}");

            $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                        VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_shoutbox_enabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");
		}

        $updateset[] = "shoutboxpos = " . sqlesc($shoutboxpos);
    }

    //-- Set Shoutbox Permission - Disabled - Time Based --//
    if (isset($_POST['shoutboxposuntillength']) && ($shoutboxposuntillength = 0 + $_POST['shoutboxposuntillength']))
    {
        unset($shoutboxposuntilpm);

        if (isset($_POST['shoutboxposuntilpm']))
        {
             $shoutboxposuntilpm = $_POST['shoutboxposuntilpm'];
        }

        if ($shoutboxposuntillength == 255)
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_shoutbox_disabled']} - "  . user::$current['username'] . ".\n{$lang['text_reason']}$shoutboxposuntilpm\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_shoutbox_disabled']}\n{$lang['msg_contact_staff']}");

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_shoutbox_disabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "shoutboxposuntil = '0000-00-00 00:00:00'";
        }
        else
        {
            $shoutboxposuntil = get_date_time(gmtime() + $shoutboxposuntillength * 604800);
            $dur              = $shoutboxposuntillength . "{$lang['text_week']}" . ($shoutboxposuntillength > 1 ? "{$lang['text_s']}" : '');
            $msg              = sqlesc("{$lang['msg_shoutbox_dur']} - $dur{$lang['text_by']}"  . user::$current['username'] . "" . ($shoutboxposuntilpm ? "\n\n{$lang['msg_reason']}$shoutboxposuntilpm" : ''));
            $modcomment  = gmdate("Y-m-d") . " - {$lang['text_shoutbox_disabled_for']} $dur{$lang['text_by']}"  . user::$current['username'] . ".\n{$lang['text_reason']}$shoutboxposuntilpm\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_shoutbox_disabled_for']} $dur
							{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "shoutboxposuntil = " . sqlesc($shoutboxposuntil);
        }

        $added   = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_shoutbox_status']}");

        $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                    VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

        $updateset[] = "shoutboxpos = 'no'";
    }

    // Torrent Comments Permission - Enable
    if ((isset($_POST['torrcompos'])) && (($torrcompos = $_POST['torrcompos']) != $user['torrcompos']))
    {
        if ($torrcompos == 'yes')
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_comments_enabled']}"  . user::$current['username'] . ".\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_comments_enabled']}\n{$lang['msg_be_careful']}");
            $added      = sqlesc(get_date_time());
            $subject    = sqlesc("{$lang['msg_comments_status']}");

            $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                        VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_comments_enabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");
        }
            $updateset[] = "torrcompos = " . sqlesc($torrcompos);
    }

    //-- Set Torrent Comments - Disabled - Time Based --//
    if (isset($_POST['torrcomposuntillength']) && ($torrcomposuntillength = 0 + $_POST['torrcomposuntillength']))
    {
        unset($torrcomposuntilpm);

        if (isset($_POST['torrcomposuntilpm']))
        {
            $torrcomposuntilpm = $_POST['torrcomposuntilpm'];
        }

        if ($torrcomposuntillength == 255)
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_comments_disabled']} - "  . user::$current['username'] . ".\n{$lang['text_reason']}$torrcomposuntilpm\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_comments_disabled']}\n{$lang['msg_contact_staff']}");

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_comments_disabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "torrcomposuntil = '0000-00-00 00:00:00'";
        }
        else
        {
            $torrcomposuntil = get_date_time(gmtime() + $torrcomposuntillength * 604800);
            $dur             = $torrcomposuntillength . "{$lang['text_week']}" . ($torrcomposuntillength > 1 ? "{$lang['text_s']}" : '');
            $msg             = sqlesc("{$lang['msg_comments_disabled_for']} - $dur{$lang['text_by']}"  . user::$current['username'] . "" . ($torrcomposuntilpm ? "\n\n{$lang['msg_reason']}$torrcomposuntilpm" : ''));
            $modcomment  = gmdate("Y-m-d") . " - {$lang['text_comments_disabled_for']} $dur{$lang['text_by']}"  . user::$current['username'] . ".\n{$lang['text_reason']}$torrcomposuntilpm\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_comments_disabled_for']} $dur
							{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "torrcomposuntil = " . sqlesc($torrcomposuntil);
        }

        $added   = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_comments_status']}");

        $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                    VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

        $updateset[] = "torrcompos = 'no'";
    }

    //-- Offer Comments Permission - Enable --//
    if ((isset($_POST['offercompos'])) && (($offercompos = $_POST['offercompos']) != $user['offercompos']))
    {
        if ($offercompos == 'yes')
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_offer_comment_enabled']}"  . user::$current['username'] . ".\n\n" . $modcomment;
            $msg    = sqlesc("{$lang['msg_offer_comment_enabled']}\n{$lang['msg_be_careful']}");
            $added   = sqlesc(get_date_time());
            $subject = sqlesc("{$lang['msg_offer_comment_status']}");

            $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                        VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_offer_comment_enabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");
        }
            $updateset[] = "offercompos = " . sqlesc($offercompos);
    }

    //-- Set Offer Comments - Disabled - Time Based --//
    if (isset($_POST['offercomposuntillength']) && ($offercomposuntillength = 0 + $_POST['offercomposuntillength']))
    {
        unset($offercomposuntilpm);

        if (isset($_POST['offercomposuntilpm']))
        {
            $offercomposuntilpm = $_POST['offercomposuntilpm'];
        }

        if ($offercomposuntillength == 255)
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_offer_comment_disabled']} - "  . user::$current['username'] . ".\n{$lang['text_reason']}$offercomposuntilpm\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_offer_comment_disabled']}\n{$lang['msg_contact_staff']}");

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_offer_comment_disabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "offercomposuntil = '0000-00-00 00:00:00'";
        }
        else
        {
            $offercomposuntil = get_date_time(gmtime() + $offercomposuntillength * 604800);
            $dur              = $offercomposuntillength . "{$lang['text_week']}" . ($offercomposuntillength > 1 ? "{$lang['text_s']}" : '');
            $msg              = sqlesc("{$lang['msg_offer_comment_disabled_for']} - $dur{$lang['text_by']}"  . user::$current['username'] . "" . ($offercomposuntilpm ? "\n\n{$lang['msg_reason']}$offercomposuntilpm" : ''));
            $modcomment       = gmdate("Y-m-d") . " - {$lang['text_offer_comment_disabled_for']} $dur{$lang['text_by']}"  . user::$current['username'] . ".\n{$lang['text_reason']}$offercomposuntilpm\n\n" . $modcomment;

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_offer_comment_disabled_for']} $dur
							{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "offercomposuntil = " . sqlesc($offercomposuntil);
        }
        $added   = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_offer_comment_status']}");

        $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                    VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

        $updateset[] = "offercompos = 'no'";
    }

    //-- Request Comments Permission - Enable --//
        if ((isset($_POST['requestcompos'])) && (($requestcompos = $_POST['requestcompos']) != $user['requestcompos']))
        {
            if ($requestcompos == 'yes')
            {
                $modcomment = gmdate("Y-m-d") . " - {$lang['text_request_comment_enabled']}"  . user::$current['username'] . ".\n\n" . $modcomment;
                $msg        = sqlesc("{$lang['msg_request_comment_enabled']}\n{$lang['msg_be_careful']}");
                $added      = sqlesc(get_date_time());
                $subject    = sqlesc("{$lang['msg_request_comment_status']}");

                $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                            VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

				write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
								-- {$lang['stafflog_request_comment_enabled']} --
                                <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");
            }
            $updateset[] = "requestcompos = " . sqlesc($requestcompos);
        }

    //-- Set Request Comments - Disabled - Time Based --//
    if (isset($_POST['requestcomposuntillength']) && ($requestcomposuntillength = 0 + $_POST['requestcomposuntillength']))
    {
        unset($requestcomposuntilpm);

        if (isset($_POST['requestcomposuntilpm']))
        {
            $requestcomposuntilpm = $_POST['requestcomposuntilpm'];
        }

        if ($requestcomposuntillength == 255)
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_request_comment_disabled']} - "  . user::$current['username'] . ".\n{$lang['text_reason']}$requestcomposuntilpm\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_request_comment_disabled']} \n{$lang['msg_contact_staff']}");

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_request_comment_disabled']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "requestcomposuntil = '0000-00-00 00:00:00'";
        }
        else
        {
            $requestcomposuntil = get_date_time(gmtime() + $requestcomposuntillength * 604800);
            $dur                = $requestcomposuntillength . "{$lang['text_week']}" . ($requestcomposuntillength > 1 ? "{$lang['text_s']}" : '');
            $msg                = sqlesc("{$lang['msg_request_comment_disabled_for']} - $dur{$lang['text_by']}"  . user::$current['username'] . "" . ($requestcomposuntilpm ? "\n\n{$lang['msg_reason']}$requestcomposuntilpm" : ''));
            $modcomment         = gmdate("Y-m-d") . " - {$lang['text_request_comment_disabled_for']} $dur{$lang['text_by']}"  . user::$current['username'] . ".\n{$lang['text_reason']}$requestcomposuntilpm\n\n" . $modcomment;

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
						-- {$lang['stafflog_request_comment_disabled_for']} $dur
						{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $updateset[] = "requestcomposuntil = " . sqlesc($requestcomposuntil);
        }
        $added   = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_request_comment_status']}");

        $db->query("INSERT INTO messages (sender, receiver, subject, msg, added)
                    VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__FILE__, __LINE__);

        $updateset[] = "requestcompos = 'no'";
    }

    //-- Avatar Changed --//
    if ((isset($_POST['avatar'])) && (($avatar = $_POST['avatar']) != ($curavatar = $user['avatar'])))
    {
        $modcomment = gmdate("Y-m-d") . " - {$lang['text_avatar_changed']} " . security::html_safe($curavatar) . "{$lang['text_to']}" . security::html_safe($avatar) . "{$lang['text_by']}"  . user::$current['username'] . ".\n\n" . $modcomment;

		write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
						-- {$lang['stafflog_avatar_changed']} " . security::html_safe($curavatar) . "{$lang['text_to']}" . security::html_safe($avatar) . "
						{$lang['stafflog_by']}-- <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

        $updateset[] = "avatar = " . sqlesc($avatar);
    }

    //-- Set First Line Support Yes / No --//
    if ((isset($_POST['support'])) && (($support = $_POST['support']) != $user['support']))
    {
        if ($support == 'yes')
        {
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_promoted_fls']}"  . user::$current['username'] . ".\n\n" . $modcomment;
            $msg        = sqlesc("{$lang['msg_promoted_fls']}" . security::html_safe(user::$current['username']) . ".");
            $added      = sqlesc(get_date_time());

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_promote_fls']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $db->query("INSERT INTO messages (sender, receiver, msg, added)
                        VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
        }

        if ($support == 'no')
        {
            $updateset[] = "support_lang =''";
            $updateset[] = "supportfor =''";
            $modcomment  = gmdate("Y-m-d") . " - {$lang['text_demote_fls']}"  . user::$current['username'] . ".\n\n" . $modcomment;
            $msg         = sqlesc("{$lang['msg_demoted_fls']}" . security::html_safe(user::$current['username']) . ", {$lang['msg_demote_fls']}");
            $added       = sqlesc(get_date_time());

			write_stafflog("<strong><a href='userdetails.php?id=$userid'>{$user['username']}</a></strong>&nbsp;
							-- {$lang['stafflog_demote_fls']} --
                            <strong><a href='userdetails.php?id=" . user::$current['id'] . "'>"  . user::$current['username'] . "</a></strong>.\n");

            $db->query("INSERT INTO messages (sender, receiver, msg, added)
                        VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
        }

        $updateset[] = "support = " . sqlesc($support);
    }

    //-- Set First Line Support For --//
    if (isset($_POST['supportfor']) && ($supportfor = $_POST['supportfor']) != $user['supportfor'])
    {
        $updateset[] = "supportfor = " . sqlesc($supportfor);
    }

    //-- Set First Line Support Language --//
    if (isset($_POST['support_lang']) && ($support_lang = $_POST['support_lang']) != $user['support_lang'])
    {
        $updateset[] = "support_lang = " . sqlesc($support_lang);
    }

    //-- Add ModComment... (If We Changed Something We Update Otherwise We Dont Include This..) --//
    if ((user::$current['class'] >= UC_SYSOP && ($user['modcomment'] != $_POST['modcomment'] || $modcomment != $_POST['modcomment'])) || (user::$current['class'] < UC_SYSOP && $modcomment != $user['modcomment']))
    {
        $updateset[] = "modcomment = " . sqlesc($modcomment);
    }
	
    if (sizeof($updateset) > 0)
    {
        $db->query("UPDATE users
                    SET " . implode(", ", $updateset) . "
                    WHERE id = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
		
		$Memcache->delete_value('details::dltable::user::stuff::' . $userid);
		$Memcache->delete_value('user::profile::' . $userid);
		$Memcache->delete_value('user::profile::stats::' . $userid);

        status_change($userid);
    }

    $returnto = $_POST['returnto'];

    header("Location: $site_url/$returnto");

    die();
}

?>