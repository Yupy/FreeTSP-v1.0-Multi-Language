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
require_once(FUNC_DIR . 'function_page_verify.php');

db_connect();
logged_in();

$lang = array_merge(load_language('takeeditaltusercp'),
                    load_language('func_bbcode'));

$newpage = new page_verify();
$newpage->check('_altusercp_');

$setflags = 0;
$clrflags = 0;

$action = security::html_safe($_GET['action']);

$updateset = array();

if ($action == "avatar")
{
    $title   = (isset($_POST['title']) ? $_POST['title'] : '');
    $avatars = (isset($_POST['avatars']) ? $_POST['avatars'] : '');

    if ($avatars != ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS))) {
	    if ($avatars)
		    $setflags |= options::USER_SHOW_AVATARS;
	    else
		    $clrflags |= options::USER_SHOW_AVATARS;
    }

    $avatar  = trim(urldecode($_POST['avatar']));

    $updateset[] = "title = " . sqlesc($title);
    $updateset[] = "avatar = " . sqlesc($avatar);
    $updateset[] = "avatars = '$avatars'";

    if (preg_match("/^http:\/\/$/i", $avatar)

            or preg_match("/[?&;]/", $avatar)
            or preg_match("#javascript:#is", $avatar)
            or !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $avatar)
    )

    {
        $avatar = '';
    }
}
else {
    if ($action == "signature")
    {
        $signature  = $_POST['signature'];
        $signatures = ($_POST['signatures'] != "" ? "yes" : "no");
        $info       = $_POST['info'];

        $updateset[] = "signature = " . sqlesc($signature);
        $updateset[] = "signatures = '$signatures'";
        $updateset[] = "info = " . sqlesc($info);

        $action = "signature";
    }

    else
    {
        if ($action == "security")
        {
            if (!mkglobal("email:chpassword:passagain"))
            {
                error_message_center("error",
                                     "{$lang['err_update_failed']}",
                                     "{$lang['err_missing_data']}");
            }

            if ($chpassword != "")
            {
                if (strlen($chpassword) > 40)
                {
                    error_message_center("error",
                                         "{$lang['err_update_failed']}",
                                         "{$lang[err_pass_long]}");
                }

                if ($chpassword != $passagain)
                {
                    error_message_center("error",
                                         "{$lang['err_update_failed']}",
                                         "{$lang['err_pass_mismatch']}");
                }

                $sec      = mksecret();
                $passhash = md5($sec . $chpassword . $sec);

                $updateset[] = "secret = " . sqlesc($sec);
                $updateset[] = "passhash = " . sqlesc($passhash);

                logincookie(user::$current['id'], $passhash);
            }

            if ($email != user::$current['email'])
            {
                if (!security::valid_email($email))
                {
                    error_message_center("error",
                                         "{$lang['err_update_failed']}",
                                         "{$lang['err_inv_email']}");
                }

                $r = $db->query("SELECT id
                                FROM users
                                WHERE email = " . sqlesc($email)) or sqlerr();

                if ($r->num_rows > 0)

                {
                    error_message_center("error",
                                         "{$lang['err_update_failed']}",
                                         "{$lang['err_email_used']}");
                }

                $changedemail = 1;
            }

            if ($_POST['resetpasskey'] == 1)
            {
                $res = $db->query("SELECT username, passhash, passkey
                                  FROM users
                                  WHERE id = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

                $arr = $res->fetch_assoc() or puke();

                $newpasskey = md5($arr['username'] . get_date_time() . $arr['passhash']);
                $modcomment = gmdate("Y-m-d") . " -{$lang['text_modcom_passkey']}{$arr['passkey']}{$lang['text_modcom_reset']}$newpasskey{$lang['text_modcom_by']}" . user::$current['username'] . ".\n\n" . $modcomment;

                $updateset[] = "passkey = " . sqlesc($newpasskey);
            }

            $urladd = "";

            if ($changedemail)
            {
                $sec     = mksecret();
                $hash    = md5($sec . $email . $sec);
                $obemail = urlencode($email);

                $updateset[] = "editsecret = " . sqlesc($sec);

                $thishost   = $_SERVER['HTTP_HOST'];
                $thisdomain = preg_replace('/^www\./is', "", $thishost);

				$curr_username = user::$current['username'];
                $curr_id = user::$current['id'];
                $var_ip = vars::$realip;

$body = <<<EOD
{$lang['text_email']}{$curr_name})
{$lang['text_email1']}$thisdomain{$lang['text_email2']}($email){$lang['text_email3']}

{$lang['text_email4']}{$var_ip}{$lang['text_email5']}

{$lang['text_email6']}

$site_url/confirmemail.php/{$curr_id}/$hash/$obemail

{$lang['text_email7']}
EOD;

                mail($email, "$thisdomain{$lang['text_email8']}", $body, "{$lang['text_email9']}$site_email", "-f$site_email");

                $urladd .= "&mailsent=1";
            }
            $action = "security";
        }

        //-- Torrent Stuffs --//
        elseif ($action == "torrents")
        {
            $pmnotif    = $_POST['pmnotif'];
            $emailnotif = $_POST['emailnotif'];
            $notifs     = ($pmnotif == 'yes' ? "[pm]" : "");
            $notifs     .= ($emailnotif == 'yes' ? "[email]" : "");

            if ($pmnotif != ((bool)(user::$current['flags'] & options::USER_PM_NOTIFICATION))) {
	            if ($pmnotif)
		            $setflags |= options::USER_PM_NOTIFICATION;
	            else
		            $clrflags |= options::USER_PM_NOTIFICATION;
            }

            $r = $db->query("SELECT id
                            FROM categories") or sqlerr();

            $rows = $r->num_rows;

            for ($i = 0;
                 $i < $rows;
                 ++$i)
            {
                $a = $r->fetch_assoc();

                if ($_POST["cat{$a['id']}"] == 'yes')
                {
                    $notifs .= "[cat{$a['id']}]";
                }
            }

            $updateset[] = "notifs = '$notifs'";

            if ($_POST['resetpasskey'] == 1)
            {
                $res = $db->query("SELECT username, passhash, passkey
                                  FROM users
                                  WHERE id = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

                $arr = $res->fetch_assoc() or puke();

                $passkey = md5($arr['username'] . get_date_time() . $arr['passhash']);
                $updateset[] = "passkey = " . sqlesc($passkey);
            }

            $action = "torrents";
        }

        else
        {
            if ($action == "personal")
            {
                $stylesheet = $_POST['stylesheet'];
                $language   = $_POST['language'];
                $parked     = $_POST['parked'];
                $pcoff      = $_POST['pcoff'];
                $menu       = $_POST['menu'];
                $country    = $_POST['country'];

                $updateset[] = "parked = " . sqlesc($parked);
                $updateset[] = "language = " . sqlesc($language);
                $updateset[] = "pcoff = " . sqlesc($pcoff);
                $updateset[] = "menu = " . sqlesc($menu);
                $updateset[] = "torrentsperpage = " . min(100, 0 + $_POST['torrentsperpage']);
                $updateset[] = "topicsperpage = " . min(100, 0 + $_POST['topicsperpage']);
                $updateset[] = "postsperpage = " . min(100, 0 + $_POST['postsperpage']);

                if (is_valid_id($stylesheet))
                {
                    $updateset[] = "stylesheet = '$stylesheet'";
                }

                if (is_valid_id($country))
                {
                    $updateset[] = "country = $country";
                }

                $action = "personal";
            }

            else
            {
                if ($action == "pm")
                {
                    $acceptpms = $_POST['acceptpms'];
                    $deletepms = ($_POST['deletepms'] != "" ? "yes" : "no");
                    $savepms   = ($_POST['savepms'] != "" ? "yes" : "no");

                    $updateset[] = "acceptpms = " . sqlesc($acceptpms);
                    $updateset[] = "deletepms = '$deletepms'";
                    $updateset[] = "savepms = '$savepms'";

                    $action = "";
                }
            }
        }
    }
}

if ($setflags)
	$updateset[] = 'flags = (flags | '.$setflags.')';
if ($clrflags)
	$updateset[] = 'flags = (flags & ~'.$clrflags.')';

$db->query("UPDATE users
            SET " . implode(",", $updateset) . "
            WHERE id = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

$Memcache->delete_value('user::profile::' . user::$current['id']);

header("Location: $site_url/altusercp.php?edited=1&action=$action" . $urladd);

?>
