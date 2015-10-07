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

require_once(FUNC_DIR . 'function_main.php');

function get_row_count($table, $suffix = "") {
	global $db;
	
    if ($suffix) {
        $suffix = " $suffix";
    }

    ($r = $db->query("SELECT COUNT(*)
                      FROM $table$suffix")) or die($db->error);

    ($a = $r->fetch_row()) or die($db->error);

    return $a[0];
}

function docleanup() {
    global $torrent_dir, $signup_timeout, $max_dead_torrent_time, $days, $oldtorrents, $autoclean_interval, $posts_read_expiry,
           $parked_users, $inactive_users, $old_login_attempts, $old_help_desk, $promote_upload, $promote_ratio, $promote_time_member, $demote_ratio, $Memcache, $db;

    $lang = array_merge(load_language('func_cleanup'));

    set_time_limit(0);
    ignore_user_abort(1);

    do {
        $res = $db->query("SELECT id
                           FROM torrents");

        $ar = array();

        while ($row = $res->fetch_array(MYSQLI_BOTH)) {
            $id      = (int)$row[0];
            $ar[$id] = 1;
        }

        if (!count($ar)) {
            break;
        }

        $dp = @opendir($torrent_dir);

        if (!$dp) {
            break;
        }

        $ar2 = array();

        while (($file = readdir($dp)) !== false) {
            if (!preg_match('/^(\d+)\.torrent$/', $file, $m)) {
                continue;
            }

            $id       = $m[1];
            $ar2[$id] = 1;

            if (isset($ar[$id]) && $ar[$id]) {
                continue;
            }

            $ff = $torrent_dir . "/$file";
            unlink($ff);
        }

        closedir($dp);

        if (!count($ar2)) {
            break;
        }

        $delids = array();

        foreach (array_keys($ar)
                 AS
                 $k)
        {
            if (isset($ar2[$k]) && $ar2[$k]) {
                continue;
            }

            $delids[] = $k;

            unset($ar[$k]);
        }

        if (count($delids)) {
            $db->query("DELETE
                       FROM torrents
                       WHERE id IN (" . join(", ", $delids) . ")");
        }
		
		foreach ($delids as $delid) {
		    cached::remove_torrent_peers($delid);
	    }

        $res = $db->query("SELECT torrent
                           FROM peers
                           GROUP BY torrent");

        $delids = array();

        while ($row = $res->fetch_array(MYSQLI_BOTH)) {
            $id = $row[0];

            if (isset($ar[$id]) && $ar[$id]) {
                continue;
            }

            $delids[] = $id;
        }

        if (count($delids)) {
            $db->query("DELETE
                        FROM peers
                        WHERE torrent IN (" . join(", ", $delids) . ")");
        }

        $res = $db->query("SELECT torrent
                           FROM files
                           GROUP BY torrent");

        $delids = array();

        while ($row = $res->fetch_array(MYSQLI_BOTH)) {
            $id = $row[0];

            if ($ar[$id]) {
                continue;
            }

            $delids[] = $id;
        }

        if (count($delids)) {
            $db->query("DELETE
                        FROM files
                        WHERE torrent IN (" . join(", ", $delids) . ")");
        }
    }
    while (0);

    $deadtime = deadtime();

    $db->query("DELETE
                FROM peers
                WHERE last_action < FROM_UNIXTIME($deadtime)");

    $deadtime -= $max_dead_torrent_time;

    $db->query("UPDATE torrents
                SET visible = 'no'
                WHERE visible = 'yes'
                AND last_action < FROM_UNIXTIME($deadtime)");

    $deadtime = vars::$timestamp - $signup_timeout;

    $db->query("DELETE
                FROM users
                WHERE status = 'pending'
                AND added < FROM_UNIXTIME($deadtime)
                AND last_login < FROM_UNIXTIME($deadtime)
                AND last_access < FROM_UNIXTIME($deadtime)");

    $torrents = array();

    $res = $db->query("SELECT torrent, seeder, COUNT(*) AS c
                       FROM peers
                       GROUP BY torrent, seeder");

    while ($row = $res->fetch_assoc()) {
        if ($row['seeder'] == "yes") {
            $key = "seeders";
        } else {
            $key = "leechers";
        }
        $torrents[$row['torrent']][$key] = $row['c'];
    }

    $res = $db->query("SELECT torrent, COUNT(*) AS c
                       FROM comments
                       GROUP BY torrent");

    while ($row = $res->fetch_assoc()) {
        $torrents[$row['torrent']]['comments'] = $row['c'];
    }

    $fields = explode(":", "comments:leechers:seeders");

    $res = $db->query("SELECT id, seeders, leechers, comments
                       FROM torrents");

    while ($row = $res->fetch_assoc()) {
        $id   = (int)$row['id'];
        $torr = $torrents[$id];

        foreach ($fields
                 AS
                 $field)
        {
            if (!isset($torr[$field])) {
                $torr[$field] = 0;
            }
        }
        $update = array();

        foreach ($fields
                 AS
                 $field)
        {
            if ($torr[$field] != $row[$field]) {
                $update[] = "$field = " . $torr[$field];
            }
        }

        if (count($update)) {
            $db->query("UPDATE torrents
                        SET " . implode(", ", $update) . "
                        WHERE id = $id");
        }
    }

    //-- Delete Parked User Accounts --//
    $secs     = $parked_users;
    $dt       = sqlesc(get_date_time(gmtime() - $secs));
    $maxclass = UC_POWER_USER;

    $db->query("DELETE
                FROM users
                WHERE parked = 'yes'
                AND status = 'confirmed'
                AND class <= $maxclass
                AND last_access < $dt");

    //-- Delete Inactive User Accounts --//
    $secs     = $inactive_users;
    $dt       = sqlesc(get_date_time(gmtime() - $secs));
    $maxclass = UC_POWER_USER;

    $db->query("DELETE
                FROM users
                WHERE parked = 'no'
                AND status = 'confirmed'
                AND class <= $maxclass
                AND last_access > '0000-00-00 00:00:00'
                AND last_access < $dt");


    //-- Delete Old Login Attempts --//
    $secs = $old_login_attempts;
    $dt   = sqlesc(get_date_time(gmtime() - $secs));

    $db->query("DELETE
                FROM loginattempts
                WHERE banned = 'no'
                AND added < $dt");

    //-- Remove Expired Warnings --//
    $res = $db->query("SELECT id, username, modcomment
                       FROM users
                       WHERE warned = 'yes'
                       AND warneduntil < NOW()
                       AND warneduntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows > 0) {
        $dt      = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_sub_warn_expire']}");
        $msg     = sqlesc("{$lang['msg_warn_expire']}\n");

        while ($arr = $res->fetch_assoc()) {
            $modcomment = security::html_safe($arr['modcomment']);
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_warn_rem']}\n" . $modcomment;
            $modcom     = sqlesc($modcomment);

            $db->query("UPDATE users
                        SET warned = 'no', warneduntil = '0000-00-00 00:00:00', modcomment = $modcom
                        WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);
		    
			$Memcache->delete_value('details::dltable::user::stuff::' . $arr['id']);

            $db->query("INSERT INTO messages (sender, receiver, added, subject, msg, poster)
                        VALUES(0, " . (int)$arr['id'] . ", $dt, $subject, $msg, 0)") or sqlerr(__FILE__, __LINE__);
        }
    }
    //--End of Remove Expired Warnings --//

    //-- Remove Expired Upload Bans --//
    $res = $db->query("SELECT id, username, modcomment
                       FROM users
                       WHERE uploadpos = 'no'
                       AND uploadposuntil < NOW()
                       AND uploadposuntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows > 0) {
        $dt      = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_sub_ul_enable']}");
        $msg     = sqlesc("{$lang['msg_ul_enable']}\n");

        while ($arr = $res->fetch_assoc()) {
            $modcomment = security::html_safe($arr['modcomment']);
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_ul_enable']}\n" . $modcomment;
            $modcom     = sqlesc($modcomment);

            $db->query("UPDATE users
                        SET uploadpos = 'yes', uploadposuntil = '0000-00-00 00:00:00', modcomment = $modcom
                        WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);

            $db->query("INSERT INTO messages (sender, receiver, added, subject, msg, poster)
                        VALUES(0, " . (int)$arr['id'] . ", $dt, $subject, $msg, 0)") or sqlerr(__FILE__, __LINE__);
        }
    }
    //--End of Remove Expired Upload Ban --//

    //-- Remove Expired Download Bans --//
    $res = $db->query("SELECT id, username, modcomment
                       FROM users
                       WHERE downloadpos = 'no'
                       AND downloadposuntil < NOW()
                       AND downloadposuntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows > 0) {
        $dt      = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_sub_dl_enable']}");
        $msg     = sqlesc("{$lang['msg_dl_enable']}\n");

        while ($arr = $res->fetch_assoc()) {
            $modcomment = security::html_safe($arr['modcomment']);
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_dl_enable']}\n" . $modcomment;
            $modcom     = sqlesc($modcomment);

            $db->query("UPDATE users
                        SET downloadpos = 'yes', downloadposuntil = '0000-00-00 00:00:00', modcomment = $modcom
                        WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);

            $db->query("INSERT INTO messages (sender, receiver, added, subject, msg, poster)
                        VALUES(0, " . (int)$arr['id'] . ", $dt, $subject, $msg, 0)") or sqlerr(__FILE__, __LINE__);
        }
    }
    //--End of Remove Expired Download Ban --//

    //-- Remove Expired Shoutbox Bans --//
    $res = $db->query("SELECT id, username, modcomment
                       FROM users
                       WHERE shoutboxpos = 'no'
                       AND shoutboxposuntil < NOW()
                       AND shoutboxposuntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows > 0) {
        $dt      = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_sub_sb_enable']}");
        $msg     = sqlesc("{$lang['msg_sb_enable']}\n");

        while ($arr = $res->fetch_assoc()) {
            $modcomment = security::html_safe($arr['modcomment']);
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_sb_enable']}\n" . $modcomment;
            $modcom     = sqlesc($modcomment);

            $db->query("UPDATE users
                        SET shoutboxpos = 'yes', shoutboxposuntil = '0000-00-00 00:00:00', modcomment = $modcom
                        WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);

            $db->query("INSERT INTO messages (sender, receiver, added, subject, msg, poster)
                        VALUES(0, " . (int)$arr['id'] . ", $dt, $subject, $msg, 0)") or sqlerr(__FILE__, __LINE__);
        }
    }
    //--End of Remove Expired Shoutbox Ban --//

    //-- Remove Expired Torrent Comment Bans --//
    $res = $db->query("SELECT id, username, modcomment
                       FROM users
                       WHERE torrcompos = 'no'
                       AND torrcomposuntil < NOW()
                       AND torrcomposuntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows > 0) {
        $dt      = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_sub_tor_com_enable']}");
        $msg     = sqlesc("{$lang['msg_tor_com_enable']}\n");

        while ($arr = $res->fetch_assoc()) {
            $modcomment = security::html_safe($arr['modcomment']);
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_torcom_enable']}\n" . $modcomment;
            $modcom     = sqlesc($modcomment);

            $db->query("UPDATE users
                        SET torrcompos = 'yes', torrcomposuntil = '0000-00-00 00:00:00', modcomment = $modcom
                        WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);

            $db->query("INSERT INTO messages (sender, receiver, added, subject, msg, poster)
                        VALUES(0, " . (int)$arr['id'] . ", $dt, $subject, $msg, 0)") or sqlerr(__FILE__, __LINE__);
        }
    }
    //--End of Remove Expired Torrent Comment Ban --//

    //-- Delete Old Help Desk Questions --//
    $secs = $old_help_desk;
    $dt   = sqlesc(get_date_time(gmtime() - $secs)); //-- Calculate Date & Time Based On GMT.

    $db->query("DELETE
                FROM helpdesk
                WHERE added < $dt");

    //-- Remove Disabled Offer Comment Status Time Based --//
    $res = $db->query("SELECT id, username, modcomment
                       FROM users
                       WHERE offercompos = 'no'
                       AND offercomposuntil < NOW()
                       AND offercomposuntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows > 0) {
        $dt      = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_subject_comment']}");
        $msg     = sqlesc("{$lang['msg_offer_com_enable']}\n{$lang['msg_be_careful']}");

        while ($arr = $res->fetch_assoc()) {
            $modcomment = security::html_safe($arr['modcomment']);
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_offcom_enable']}\n".  $modcomment;
            $modcom     = sqlesc($modcomment);

            $db->query("UPDATE users
                        SET offercompos = 'yes', offercomposuntil = '0000-00-00 00:00:00', modcomment = $modcom
                        WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);

            $db->query("INSERT INTO messages (sender, receiver, added, msg, subject, poster)
                        VALUES(0, " . (int)$arr['id'] . ", $dt, $subject, $msg, 0)") or sqlerr(__FILE__, __LINE__);

            write_stafflog("<a href=userdetails.php?id={$arr['id']}><strong>{$arr['username']}</strong></a> - {$lang['stafflog_offcom_enable']} - <strong>{$lang['stafflog_cleanup']}</strong>");
        }
    }

    //-- Remove Disabled Request Comment Status Time Based --//
    $res = $db->query("SELECT id, username, modcomment
                       FROM users
                       WHERE requestcompos = 'no'
                       AND requestcomposuntil < NOW()
                       AND requestcomposuntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows > 0) {
        $dt      = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_subject_request']}");
        $msg     = sqlesc("{$lang['msg_reqcom_enable']}\n{$lang['msg_be_careful']}");

        while ($arr = $res->fetch_assoc()) {
            $modcomment = security::html_safe($arr['modcomment']);
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_reqcom_enable']}\n" . $modcomment;
            $modcom     = sqlesc($modcomment);

            $db->query("UPDATE users
                        SET requestcompos = 'yes', requestcomposuntil = '0000-00-00 00:00:00', modcomment = $modcom
                        WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);

            $db->query("INSERT INTO messages (sender, receiver, added, subject, msg, poster)
                        VALUES(0, " . (int)$arr['id']. ", $dt, $subject, $msg, 0)") or sqlerr(__FILE__, __LINE__);

            write_stafflog("<a href=userdetails.php?id={$arr['id']}><strong>{$arr['username']}</strong></a> - {$lang['stafflog_reqcom_enable']} - <strong>{$lang['stafflog_cleanup']}</strong>");
        }
    }

    //-- Promote Power Users --//
    $limit    = $promote_upload;
    $minratio = $promote_ratio;
    $maxdt    = sqlesc(get_date_time(gmtime() - $promote_time_member));

    $res = $db->query("SELECT id, username, modcomment
                       FROM users
                       WHERE class = 0
                       AND uploaded >= $limit
                       AND uploaded / downloaded >= $minratio
                       AND added < $maxdt") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows > 0) {
        $dt      = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_sub_auto_promote']}");
        $msg     = sqlesc("{$lang['msg_auto_promote']}[b]{$lang['msg_power_user']}[/b]. :)\n");

        while ($arr = $res->fetch_assoc())
        {
            $modcomment = security::html_safe($arr['modcomment']);
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_promote_pu']}\n" . $modcomment;
            $modcom     = sqlesc($modcomment);

            $db->query("UPDATE users
                        SET class = 1, modcomment = $modcom
                        WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);
			
			$Memcache->delete_value('details::dltable::user::stuff::' . $arr['id']);

            $db->query("INSERT INTO messages (sender, receiver, added, subject, msg, poster)
                        VALUES(0, " . (int)$arr['id'] . ", $dt, $subject, $msg, 0)") or sqlerr(__FILE__, __LINE__);

            //status_change($arr['id']);
        }
    }

    //-- Demote Power Users --//
    $minratio = $demote_ratio;

    $res = $db->query("SELECT id, username, modcomment
                       FROM users
                       WHERE class = 1
                       AND uploaded / downloaded < $minratio") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows > 0) {
        $dt      = sqlesc(get_date_time());
        $subject = sqlesc("{$lang['msg_sub_auto_demote']}");
        $msg     = sqlesc("{$lang['msg_auto_demote']}[b]{$lang['msg_power_user']}[/b]{$lang['msg_to']}[b]{$lang['msg_user']}[/b]{$lang['msg_share_ratio']}$minratio.\n");

        while ($arr = $res->fetch_assoc()) {
            $modcomment = security::html_safe($arr['modcomment']);
            $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_demote_user']}\n" . $modcomment;
            $modcom     = sqlesc($modcomment);

            $db->query("UPDATE users
                        SET class = 0, modcomment = $modcom
                        WHERE id = " . (int)$arr['id']) or sqlerr(__FILE__, __LINE__);
			
			$Memcache->delete_value('details::dltable::user::stuff::' . $arr['id']);

            $db->query("INSERT INTO messages (sender, receiver, added, subject, msg, poster)
                        VALUES(0, " . (int)$arr['id'] . ", $dt, $subject, $msg, 0)") or sqlerr(__FILE__, __LINE__);

            //status_change($arr['id']);
        }
    }

    //-- Delete Orphaned Announcement Processors --//
    $db->query("DELETE announcement_process
                FROM announcement_process
                LEFT JOIN users ON announcement_process.user_id = users.id
                WHERE users.id IS NULL");

    //-- Delete Expired Announcements And Processors --//
    $db->query("DELETE FROM announcement_main
                WHERE expires < " . sqlesc(vars::$timestamp));

    $db->query("DELETE announcement_process
                FROM announcement_process
                LEFT JOIN announcement_main ON announcement_process.main_id = announcement_main.main_id
                WHERE announcement_main.main_id IS NULL");

    $registered     = get_row_count('users');
    $unverified     = get_row_count('users', "WHERE status = 'pending'");
    $torrents       = get_row_count('torrents');
    $seeders        = get_row_count('peers', "WHERE seeder = 'yes'");
    $leechers       = get_row_count('peers', "WHERE seeder = 'no'");
    $torrentstoday  = get_row_count('torrents', 'WHERE added > DATE_SUB(NOW(), INTERVAL 1 DAY)');
    $donors         = get_row_count('users', "WHERE donor = 'yes'");
    $unconnectables = get_row_count('peers', "WHERE connectable = 'no'");
    $forumposts     = get_row_count('posts');
    $forumtopics    = get_row_count('topics');
    $dt             = sqlesc(get_date_time(gmtime() - 300)); //-- Active Users Last 5 Minutes --//
    $numactive      = get_row_count('users', "WHERE last_access >= $dt");
    $Users          = get_row_count('users', "WHERE class = '0'");
    $Poweruser      = get_row_count('users', "WHERE class = '1'");
    $Vip            = get_row_count('users', "WHERE class = '2'");
    $Uploaders      = get_row_count('users', "WHERE class = '3'");
    $Moderator      = get_row_count('users', "WHERE class = '4'");
    $Adminisitrator = get_row_count('users', "WHERE class = '5'");
    $Sysop          = get_row_count('users', "WHERE class = '6'");
    $Manager        = get_row_count('users', "WHERE class = '7'");

    $db->query("UPDATE stats
                SET regusers = '$registered', unconusers = '$unverified', torrents = '$torrents', seeders = '$seeders', leechers = '$leechers', unconnectables = '$unconnectables', torrentstoday = '$torrentstoday', donors = '$donors', forumposts = '$forumposts', forumtopics = '$forumtopics', numactive = '$numactive', Users = '$Users', Poweruser = '$Poweruser', Vip = '$Vip', Uploaders = '$Uploaders', Moderator = '$Moderator', Adminisitrator = '$Adminisitrator', Sysop ='$Sysop', Manager = '$Manager'
                WHERE id = '1'
                LIMIT 1");

    if ($oldtorrents) {
        $dt = sqlesc(get_date_time(gmtime() - ($days * 86400)));

        $res = $db->query("SELECT id, name
                           FROM torrents
                           WHERE added < $dt
                           AND seeders = '0' AND leechers = '0'");

        while ($arr = $res->fetch_assoc()) {
            @unlink("$torrent_dir/{$arr['id']}.torrent");

            $db->query("DELETE
                        FROM torrents
                        WHERE id = " . (int)$arr['id']);

            $db->query("DELETE
                        FROM peers
                        WHERE torrent = " . (int)$arr['id']);

            $db->query("DELETE
                        FROM comments
                        WHERE torrent = " . (int)$arr['id']);

            $db->query("DELETE
                        FROM files
                        WHERE torrent = " . (int)$arr['id']);

            $db->query("DELETE
                        FROM thanks
                        WHERE torrentid = " . (int)$arr['id']);
			
			$Memcache->delete_value('torrent::details::' . $arr['id']);

            write_log("{$lang['writelog_torrent']}{$arr['id']} ({$arr['name']}){$lang['writelog_del_system']}$days{$lang['writelog_days']}");
        }
    }
}

?>