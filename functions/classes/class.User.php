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
require_once(CLASS_DIR . 'class.Template.php');

class user {
    public static $current = NULL;

	const USER_FIELDS = 'u.*'; #TO-DO...
	const ANN_FIELDS = 'ann_main.subject AS curr_ann_subject, ann_main.body AS curr_ann_body, ann_main.expires AS curr_ann_expires';

	public static function prepare_user(&$user, $curuser = false) {
		if ($curuser && empty($user))
			die;

		if (isset($user['id']))
		    $user['id'] = (int)$user['id'];
		if (isset($user['curr_ann_last_check']))
		    $user['curr_ann_last_check'] = (int)$user['curr_ann_last_check'];
		if (isset($user['curr_ann_id']))
		    $user['curr_ann_id'] = (int)$user['curr_ann_id'];
		if (isset($user['stylesheet']))
		    $user['stylesheet'] = (int)$user['stylesheet'];
		if (isset($user['class']))
		    $user['class'] = (int)$user['class'];
		if (isset($user['override_class']))
		    $user['override_class'] = (int)$user['override_class'];
		if (isset($user['uploaded']))
		    $user['uploaded'] = (float)$user['uploaded'];
		if (isset($user['downloaded']))
		    $user['downloaded'] = (float)$user['downloaded'];
		if (isset($user['country']))
		    $user['country'] = (int)$user['country'];
		if (isset($user['torrentsperpage']))
		    $user['torrentsperpage'] = (int)$user['torrentsperpage'];
		if (isset($user['topicsperpage']))
		    $user['topicsperpage'] = (int)$user['topicsperpage'];
		if (isset($user['postsperpage']))
		    $user['postsperpage'] = (int)$user['postsperpage'];
		if (isset($user['last_access_numb']))
		    $user['last_access_numb'] = (int)$user['last_access_numb'];
		if (isset($user['onlinetime']))
		    $user['onlinetime'] = (int)$user['onlinetime'];
		if (isset($user['last_browse']))
		    $user['last_browse'] = (int)$user['last_browse'];
		if (isset($user['menu']))
		    $user['menu'] = (int)$user['menu'];
		if (isset($user['invites']))
		    $user['invites'] = (int)$user['invites'];
		if (isset($user['invitedby']))
		    $user['invitedby'] = (int)$user['invitedby'];
		if (isset($user['flags']))
		    $user['flags'] = (int)$user['flags'];
	}

	public static function valid_class($class) {
		$class = (int)$class;
		return (bool)($class >= UC_USER && $class <= UC_MANAGER);
	}

    public static function login() {
        global $site_online, $lang, $db, $banned, $tpl;

		raintpl::configure('base_url', null);
        raintpl::configure('tpl_dir', 'stylesheets/tpl/');
        raintpl::configure('cache_dir', 'cache/');
        $tpl = new RainTPL;

        unset($GLOBALS['CURUSER']);

        $dt = get_date_time();
        $ip  = vars::$ip;
        $nip = ip2long($ip);
        $ipf = vars::$realip;

        if (cached::bans($ip, $reason))
            $banned = true;
        else {
            if ($ip != $ipf) {
                if (cached::bans($ipf, $reason))
                    $banned = true;
            }
        }

        if ($banned) {
            header($lang['gbl_forbidden']);
			
			$lang_403 = $lang['gbl_403'];
	        $tpl->assign('lang_403', $lang_403);

			$lang_unauth_ip = $lang['gbl_unauth_ip'];
	        $tpl->assign('lang_unauth_ip', $lang_unauth_ip);

			$banned_reason = security::html_safe($reason);
	        $tpl->assign('banned_reason', $banned_reason);

			$banned_msg = $tpl->draw('banned_message', $return_string = true);
            echo $banned_msg;

            die;
        }

        if (!$site_online || empty($_COOKIE['uid']) || empty($_COOKIE['pass'])) {
            return;
        }

        $id = intval(0 + $_COOKIE['uid']);

        if (!$id || strlen($_COOKIE['pass']) != 32) {
            return;
        }

        $res = $db->query("SELECT " . self::USER_FIELDS . ", " . self::ANN_FIELDS . "
                           FROM users AS u
                           LEFT JOIN announcement_main AS ann_main ON ann_main.main_id = u.curr_ann_id
                           WHERE u.id = " . $id . " AND u.enabled = 'yes' AND u.status = 'confirmed'") or sqlerr(__FILE__, __LINE__);

        $row = $res->fetch_array(MYSQLI_BOTH);
	
	    user::prepare_user($row);

        if (!$row) {
            return;
        }

        $sec = hash_pad($row['secret']);

        if ($_COOKIE['pass'] !== $row['passhash']) {
            return;
        }

        //-- If curr_ann_id > 0 But curr_ann_body IS NULL, Then Force A Refresh --//
        if (($row['curr_ann_id'] > 0) AND ($row['curr_ann_body'] == NULL)) {
            $row['curr_ann_id'] = 0;
            $row['curr_ann_last_check'] = "0";
        }

        // If Elapsed > 10 Minutes, Force An Announcement Refresh. --//
        if (($row['curr_ann_last_check'] != '0') AND ($row['curr_ann_last_check']) < (time($dt) - 600)) {
            $row['curr_ann_last_check'] = '0';
        }

        if (($row['curr_ann_id'] == 0) AND ($row['curr_ann_last_check'] == '0')) {
            //-- Force An Immediate Check... --//
            $query = sprintf("SELECT m.*, p.process_id FROM announcement_main AS m
                              LEFT JOIN announcement_process AS p ON m.main_id = p.main_id
                              AND p.user_id = %s
                              WHERE p.process_id IS NULL
                              OR p.status = 0
                              ORDER BY m.main_id ASC
                              LIMIT 1",

            sqlesc((int)$row['id']));
            $result = $db->query($query);

            if ($result->num_rows) {
                //-- Main Result Set Exists --//
                $ann_row = $result->fetch_assoc();

                $query = $ann_row['sql_query'];

                //-- Ensure It Only Selects... --//
                if (!preg_match("/\\ASELECT.+?FROM.+?WHERE.+?\\z/", $query)) die();

                //-- The Following Line Modifies The Query To Only Return The Current User --//
                //-- Row If The Existing Query Matches Any Attributes. --//
                $query .= " AND u.id = " . sqlesc((int)$row['id']) . " LIMIT 1";

                $result = $db->query($query);
                if ($result->num_rows) {
                    //-- Announcement Valid For Member --//
                    $row['curr_ann_id'] = $ann_row['main_id'];

                    //-- Create Three Row Elements To Hold Announcement Subject, Body And Expiry Date. --//
                    $row['curr_ann_subject'] = $ann_row['subject'];
                    $row['curr_ann_body']    = $ann_row['body'];
                    $row['curr_ann_expires'] = $ann_row['expires'];

                    //-- Create Additional Set For Main UPDATE Query. --//
                    $add_set = ", curr_ann_id = " . sqlesc($ann_row['main_id']);
                    $status  = 2;
                } else {
                    $add_set = ", curr_ann_last_check = " . sqlesc($dt);
                    $status  = 1;
                }

                //-- Create Or Set Status Of Process --//
                if ($ann_row['process_id'] === NULL) {
                    //-- Insert Process Result Set Status = 1 (Ignore) --//
                    $query = sprintf("INSERT INTO announcement_process (main_id, user_id, status)
                                      VALUES (%s, %s, %s)", sqlesc($ann_row['main_id']), sqlesc($row['id']), sqlesc($status));
                } else {
                    $query = sprintf("UPDATE announcement_process
                                      SET status = %s
                                      WHERE process_id = %s", sqlesc($status), sqlesc($ann_row['process_id']));
                }
                $db->query($query);
            } else {
                $add_set = ", curr_ann_last_check = " . sqlesc($dt);
            }

            unset($result);
            unset($ann_row);
        }

        $time = vars::$timestamp;

        if ($time - $row['last_access_numb'] < 300) {
            $onlinetime   = vars::$timestamp - $row['last_access_numb'];
            $userupdate[] = "onlinetime = onlinetime + " . sqlesc($onlinetime);
        }

        //-- Start Hide Staff IP Address by Fireknight --//
        if ($row['class'] >= UC_MODERATOR) {
            $ip = '127.0.0.1';
        }
        //-- End Hide Staff IP Address by Fireknight --//

        $add_set = (isset($add_set)) ? $add_set : '';

        $userupdate[] = 'last_access_numb = ' . sqlesc($time);
        $userupdate[] = 'last_access = ' . sqlesc($dt);
        $userupdate[] = 'ip = ' . sqlesc($ip) . $add_set;

        $db->query("UPDATE users
                    SET " . implode(", ", $userupdate) . "
                    WHERE id = " . (int)$row['id']);

        $row['ip'] = $ip;

        //-- Start Temp Demote By Retro 1 of 3 --//
        if ($row['override_class'] < $row['class']) {
            $row['class'] = (int)$row['override_class']; //-- Override Class And Save In Global Array Below. --//
        }
        //-- Finish Temp Demote By Retro 1 of 3 --//

        #$GLOBALS['CURUSER'] = $row;

        user::$current = $row;
        $GLOBALS['CURUSER'] =& user::$current;
        unset($row);
    }

}

?>