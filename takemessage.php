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
logged_in();

$lang = array_merge(load_language('takemessage'),
                    load_language('global'));

if ($_SERVER['REQUEST_METHOD'] != "POST")
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['text_method']}");
}

$newpage = new page_verify();
$newpage->check('_sendmessage_');

$n_pms = isset($_POST['n_pms']) ? $_POST['n_pms'] : false;

if ($n_pms)
{
    if (get_user_class() < UC_MODERATOR)
    {
        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "{$lang['err_denied']}");
    }

    $msg = trim($_POST['msg']);

    if (!$msg)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['text_enter_text']}");
    }

    $subject   = trim($_POST['subject']);
    $sender_id = ($_POST['sender'] == 'system' ? 0 : user::$current['id']);

    $from_is = (int)$_POST['pmees'];

    $query = "INSERT INTO messages (sender, receiver, added, msg, subject, location, poster)
              SELECT $sender_id, u.id, '" . get_date_time() . "', " . sqlesc($msg) . ", " . sqlesc($subject) . ", 1, $sender_id " . $from_is;

    $db->query($query) or sqlerr(__FILE__, __LINE__);

    $n = $db->affected_rows;

    $comment  = isset($_POST['comment']) ? $_POST['comment'] : '';
    $snapshot = isset($_POST['snap']) ? $_POST['snap'] : '';

    //-- Add A Custom Text Or Stats Snapshot To Comments In Profile --//
    if ($comment || $snapshot)
    {
        $res = $db->query("SELECT u.id, u.uploaded, u.downloaded, u.modcomment " . $from_is) or sqlerr(__FILE__, __LINE__);

        if ($res->num_rows > 0)
        {
            $l = 0;

            while ($user = $res->fetch_assoc())
            {
                unset($new);

                $old = $user['modcomment'];

                if ($comment)
                {
                    $new = $comment;
                }

                if ($snapshot)
                {
                    $new .= ($new ? "\n" : "") . "MMed, " . gmdate("Y-m-d") . ", " . "UL: " . mksizegb($user['uploaded']) . ", " . "DL: " . mksizegb($user['downloaded']) . ", " . "r: " . ratios($user['uploaded'], $user['downloaded'], false) . " - " . ($_POST['sender'] == "system" ? "System" : user::$current['username']);
                }

                $new .= $old ? ("\n" . $old) : $old;

                $db->query("UPDATE users
                           SET modcomment = " . sqlesc($new) . "
                           WHERE id = " . (int)$user['id']) or sqlerr(__FILE__, __LINE__);

                if ($db->affected_rows)
                {
                    $l++;
                }
            }
        }
    }
}
else
{
    $receiver = isset($_POST['receiver']) ? $_POST['receiver'] : false;
    $origmsg  = isset($_POST['origmsg']) ? $_POST['origmsg'] : false;
    $save     = isset($_POST['save']) ? $_POST['save'] : false;

    if (!isset($save))
    {
        $save = "no";
    }

    $returnto = isset($_POST['returnto']) ? $_POST['returnto'] : '';

    if (!is_valid_id($receiver) || ($origmsg && !is_valid_id($origmsg)))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    $msg = trim($_POST['msg']);

    if (!$msg)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['text_enter_text']}");
    }

    $save = ($save == 'yes') ? "yes" : "no";

    $res = $db->query("SELECT acceptpms, email, notifs, parked, CAST(flags AS SIGNED) AS flags, UNIX_TIMESTAMP(last_access) AS la
                      FROM users
                      WHERE id = $receiver") or sqlerr(__FILE__, __LINE__);

    $user = $res->fetch_assoc();

    if (!$user)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_no_id']}");
    }

    //-- Make Sure Recipient Wants This Message --//
    if (get_user_class() < UC_MODERATOR)
    {
        if ($user['parked'] == "yes")
        {
            error_message_center("info",
                                 "{$lang['err_refused']}",
                                 "{$lang['err_acc_parked']}");
        }

        if ($user['acceptpms'] == "yes")
        {
            $res2 = $db->query("SELECT *
                               FROM blocks
                               WHERE userid = $receiver
                               AND blockid = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

            if ($res2->num_rows == 1)
            {
                error_message_center("info",
                                     "{$lang['err_refused']}",
                                     "{$lang['err_user_blocked']}");
            }
        }
        elseif ($user['acceptpms'] == "friends")
        {
            $res2 = $db->query("SELECT *
                               FROM friends
                               WHERE userid = $receiver
                               AND friendid = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

            if ($res2->num_rows != 1)
            {
                error_message_center("info",
                                     "{$lang['err_refused']}",
                                     "{$lang['err_friend_list']}");
            }
        }
        elseif ($user['acceptpms'] == "no")
        {
            error_message_center("info",
                                 "{$lang['err_refused']}",
                                 "{$lang['err_deny_pm']}");
        }
    }

    $subject = trim($_POST['subject']);

    $db->query("INSERT INTO messages (poster, sender, receiver, added, msg, subject, saved, location)
               VALUES(" . user::$current['id'] . ", " . user::$current['id'] . ", $receiver, '" . get_date_time() . "', " . sqlesc($msg) . ", " . sqlesc($subject) . ", " . sqlesc($save) . ", 1)") or sqlerr(__FILE__, __LINE__);

    if ($user['flags'] & options::USER_PM_NOTIFICATION)
    {
        if (gmtime() - $user['la'] >= 300)
        {
            $username = security::html_safe(user::$current['username']);

$body = <<<EOD
{$lang['text_email']}$username!

{$lang['text_email1']}

$site_url/messages.php

--
$site_name
EOD;
            //@sendmail($user['email'], "{$lang['text_email2']}" . $username . "!", $body, "{$lang['text_email2']}$site_email", "-f$site_email");
            sendMail($site_email,$site_name . ' ' . "{$lang['text_email2']}" . $username . "!", $body, "{$lang['text_email3']}$site_email", "-f$site_email");
        }
    }
    $delete = isset($_POST['delete']) ? $_POST['delete'] : '';

    if ($origmsg)
    {
        if ($delete == "yes")
        {
            //-- Make Sure Receiver Of $origmsg Is Current User --//
            $res = $db->query("SELECT *
                              FROM messages
                              WHERE id = $origmsg") or sqlerr(__FILE__, __LINE__);

            if ($res->num_rows == 1)
            {
                $arr = $res->fetch_assoc();

                if ($arr['receiver'] != user::$current['id'])
                {
                    error_message_center("error",
                                         "{$lang['gbl_error']}",
                                         "{$lang['err_something_wrong']}");
                }

                if ($arr['saved'] == "no")
                {
                    $db->query("DELETE
                               FROM messages
                               WHERE id = $origmsg") or sqlerr(__FILE__, __LINE__);
                }

                elseif ($arr['saved'] == "yes")
                {
                    $db->query("UPDATE messages
                               SET location = '0'
                               WHERE id = $origmsg") or sqlerr(__FILE__, __LINE__);
                }
            }
        }
        if (!$returnto)
        {
            $returnto = "messages.php";
        }
    }
    if ($returnto)
    {
        header("Location: $returnto");
        die;
    }

    site_header();

    //header("refresh:1; $site_url/index.php");
    display_message_center("success",
                           "{$lang['err_success']}",
                           "{$lang['text_pm_sent']}");
}

site_footer();

exit;

?>