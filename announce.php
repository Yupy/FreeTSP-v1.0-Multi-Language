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
error_reporting(0);
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_benc.php');
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_announce.php');
require_once(FUNC_DIR . 'ann_vars.php');

//-- Deny Access Made With A Browser --//
if (stripos($agent, 'Mozilla') !== false || stripos($agent, 'Opera') !== false || stripos($agent, 'Links') !== false || stripos($agent, 'Lynx') !== false || stripos($agent, 'Wget') !== false || strpos($peer_id, 'OP') === 0) {
    err("Sorry, this torrent is not Registered with " . $site_name);
}

if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || isset($_SERVER['HTTP_ACCEPT_CHARSET']) || isset($_SERVER['HTTP_COOKIE']) || isset($_SERVER['HTTP_REFERER'])) {
	err('Your client is banned !');
}

if (is_float($uploaded) || is_float($downloaded) || is_float($left)) {
	err('Stats gone crazy !');
}

if (!$sp_compact) {
	err('Your client does not support the "compact" tracker feature, please change/upgrade your torrent client to one that does.');
}

if (!$port || $port > 0xffff) {
    err("Invalid Port");
}

if (!isset($event)) {
    $event = '';
}

$selectnot_bits = 0;

$seeder = ($left == 0) ? 'yes' : 'no';

// If peer is a seed, don't send peer list with seeders, save some bandwith.
if ($seeder)
	$selectnot_bits |= options::PEER_SEEDER;

db_connect();

$valid = @$db->query("SELECT COUNT(id)
                      FROM users
                      WHERE passkey = " . sqlesc($passkey));
$valid = @$valid->fetch_row();

if ($valid[0] != 1) {
    err("Invalid Passkey! Download the .torrent file again from $site_url");
}

$torrent = cached::get_torrent_from_hash($info_hash);
if (!$torrent) {
    err("Sorry, this torrent is not Registered with " . $site_name);
}

$torrentid = (int)$torrent['id'];

$fields = "seeder, peer_id, ip, port, uploaded, downloaded, userid, (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(last_action)) AS announcetime";

$numpeers = (int)$torrent['numpeers'];
$limit    = '';

if ($numpeers > $rsize) {
    $limit = "ORDER BY RAND() LIMIT " . $rsize;
}

$res = $db->query("SELECT " . $fields . "
                    FROM peers
                    WHERE torrent = " . $torrentid . ($selectnot_bits ? ' AND (flags & ' . $selectnot_bits . ') = 0' : '') . "
                    AND connectable = 'yes' " . $limit);

$resp = "d" . benc_str("interval") . "i" . $announce_interval . "e" . benc_str("peers") . "l";

unset($self);
while ($row = $res->fetch_assoc()) {
    $row['peer_id'] = hash_pad($row['peer_id']);

    if ($row['peer_id'] === $peer_id) {
        $userid = (int)$row['userid'];
        $self   = $row;
        continue;
    }

    $resp .= "d" . benc_str("ip") . benc_str($row['ip']) . benc_str("peer id") . benc_str($row['peer_id']) . benc_str("port") . "i" . $row['port'] . "e" . "e";
}

$resp .= "ee";

$selfwhere = "torrent = " . $torrentid . " AND " . hash_where("peer_id", $peer_id);

if (!isset($self)) {
    $res = $db->query("SELECT " . $fields . "
                      FROM peers
                      WHERE " . $selfwhere);

    $row = $res->fetch_assoc();

    if ($row) {
        $userid = (int)$row['userid'];
        $self   = $row;
    }
}

//-- Start of Upload & Download Stats --//
if (!isset($self)) {
    $valid = @$db->query("SELECT COUNT(id)
                          FROM peers
                          WHERE torrent = " . $torrentid . "
                          AND passkey = " . sqlesc($passkey) . ";");
	$valid = @$valid->fetch_row();

    if ($valid[0] >= 1 && $seeder == 'no') {
        err("Connection Limit Exceeded! You may Only Leech from One Location at a time.");
    }

    if ($valid[0] >= 3 && $seeder == 'yes') {
        err("Connection Limit Exceeded!");
    }

    $rz = $db->query("SELECT id, uploaded, downloaded, class, parked, downloadpos
                      FROM users
                      WHERE passkey = " . sqlesc($passkey) . "
                      AND enabled = 'yes'
                      ORDER BY last_access DESC
                      LIMIT 1") or err("Tracker Error 2");

    if ($members_only && $rz->num_rows == 0) {
        err("Unknown Passkey. Please redownload the torrent from " . $site_url);
    }

    $az     = $rz->fetch_assoc();
    $userid = (int)$az['id'];

    if ($az['downloadpos'] == 'no') {
        err("Your Download Privilege Has Been Removed! Please Contact A Member Of Staff To Resolve This Problem.");
    }

    if ($az['parked'] == 'yes') {
        err("Your Account is Parked! (Read the FAQ)");
    }

    global $max_class_wait;

    if ($az['class'] <= $max_class_wait) {
        $gigs    = $az['uploaded'] / (1024 * 1024 * 1024);
        $elapsed = floor((gmtime() - $torrent['ts']) / 3600);
        $ratio   = (($az['downloaded'] > 0) ? ($az['uploaded'] / $az['downloaded']) : 1);

        global $waittime, $ratio_1, $ratio_2, $ratio_3, $ratio_4, $gigs_1, $gigs_2, $gigs_3, $gigs_4, $wait_1, $wait_2, $wait_3, $wait_4;

        if ($ratio < $ratio_1 || $gigs < $gigs_1) {
            $wait = $wait_1;
        }
        elseif ($ratio < $ratio_2 || $gigs < $gigs_2) {
            $wait = $wait_2;
        }
        elseif ($ratio < $ratio_3 || $gigs < $gigs_3) {
            $wait = $wait_3;
        }
        elseif ($ratio < $ratio_4 || $gigs < $gigs_4) {
            $wait = $wait_4;
        } else {
            $wait = 0;
        }

        if (($elapsed < $wait) && ($waittime == 'true')) {
            err("Not Authorized (" . ($wait - $elapsed) . "h) - READ THE FAQ!");
        }
    }
} else {
    global $torrents_allfree;

    $freeleech    = $torrent['freeleech'];
    $upthis       = max(0, $uploaded - $self['uploaded']);
    $downthis     = max(0, $downloaded - $self['downloaded']);
    $upspeed      = ($upthis > 0 ? $upthis / $self['announcetime'] : 0);
    $downspeed    = ($downthis > 0 ? $downthis / $self['announcetime'] : 0);
    $announcetime = ($self['seeder'] == 'yes' ? "seedtime = seedtime + " . $self['announcetime'] . "" : "leechtime = leechtime + " . $self['announcetime']);

    if ($freeleech == 'yes')
		$downthis = 0;

    if ($torrents_allfree == 'true')
		$downthis = 0;

    if ($upthis > 0 || $downthis > 0) {
        $db->query("UPDATE users
                    SET uploaded = uploaded + " . $upthis . ", downloaded = downloaded + " . $downthis . "
                    WHERE id = " . $userid) or err("Tracker error 3");
    }
}
//-- End of Upload & Download Stats --//

if (portblacklisted($port)) {
    err("Port " . $port . " is Blacklisted.");
} else {
	$connectable_key = 'connectable::' . sha1($ip . '::' . $port);
    if (($connectable = $Memcache->get_value($connectable_key)) === false) {
        $sockres = @fsockopen($ip, $port, $errno, $errstr, 5);

        if (!$sockres) {
            $connectable = 'no';
		    $connectable_ttl = 15;
        } else {
            $connectable = 'yes';
		    $connectable_ttl = 900;
            @fclose($sockres);
        }
	    $Memcache->cache_value($connectable_key, $connectable, $connectable_ttl);
    }
}

$updateset = array();

if (isset($self) && $event == 'stopped') {
    $seeder = 'no';

    $db->query("DELETE
                FROM peers
                WHERE " . $selfwhere) or err("D Err");

    if ($db->affected_rows) {
		if ($self['seeder'] == 'yes')
			cached::adjust_torrent_peers($torrentid, -1, 0, 0);
        else
			cached::adjust_torrent_peers($torrentid, 0, -1, 0);
		
        $updateset[] = ($self['seeder'] == 'yes' ? 'seeders = seeders - 1' : 'leechers = leechers - 1');

        $db->query("UPDATE snatched
                    SET ip = " . sqlesc($ip) . ", port = " . $port . ", connectable = '" . $connectable . "', uploaded = uploaded + " . $upthis . ", downloaded = downloaded + " . $downthis . ", to_go = " . $left . ", upspeed = " . $upspeed . ", downspeed = " . $downspeed . ", " . $announcetime . ", last_action = '" . get_date_time() . "', seeder = '" . $seeder . "', agent = " . sqlesc($agent) . "
                    WHERE torrentid = " . $torrentid . "
                    AND userid = " . $userid) or err("SL Err 1");
    }
}
elseif (isset($self)) {
    if ($event == 'completed') {
        $updateset[] = "times_completed = times_completed + 1";
        $finished    = ", finishedat = UNIX_TIMESTAMP()";
        $finished1   = ", complete_date = '" . get_date_time() . "'";
		
		cached::adjust_torrent_peers($torrentid, 0, 0, 1);
    }

    $db->query("UPDATE peers
                SET ip = " . sqlesc($ip) . ", port = " . $port . ", connectable = '" . $connectable . "', uploaded = " . $uploaded . ", downloaded = " . $downloaded . ", to_go = " . $left . ", last_action = NOW(), seeder = '" . $seeder . "', agent = " . sqlesc($agent) . " " . $finished . "
                WHERE " . $selfwhere) or err("PL Err 1");

    if ($db->affected_rows) {
        if ($seeder <> $self['seeder']) {
			if ($seeder == 'yes')
				cached::adjust_torrent_peers($torrentid, 1, -1, 0);
            else
				cached::adjust_torrent_peers($torrentid, -1, 1, 0);
			
            $updateset[] = ($seeder == 'yes' ? 'seeders = seeders + 1, leechers = leechers - 1' : 'seeders = seeders - 1, leechers = leechers + 1');
        }

        $db->query("UPDATE snatched
                    SET ip = " . sqlesc($ip) . ", port = " . $port . ", connectable = '" . $connectable . "', uploaded = uploaded + " . $upthis . ", downloaded = downloaded + " . $downthis . ", to_go = " . $left . ", upspeed = " . $upspeed . ", downspeed = " . $downspeed . ", " . $announcetime . ", last_action = '" . get_date_time() . "', seeder = '" . $seeder . "', agent = " . sqlesc($agent) . " " . $finished1 . "
                    WHERE torrentid = " . $torrentid . "
                    AND userid = " . $userid) or err("SL Err 2");
    }
} else {
    $db->query("INSERT INTO peers (torrent, userid, peer_id, ip, port, connectable, uploaded, downloaded, to_go, started, last_action, seeder, agent, downloadoffset, uploadoffset, passkey)
                VALUES (" . $torrentid . ", " . $userid . ", " . sqlesc($peer_id) . ", " . sqlesc($ip) . ", " . $port . ", '" . $connectable . "', " . $uploaded . ", " . $downloaded . ", " . $left . ", NOW(), NOW(), '" . $seeder . "', " . sqlesc($agent) . ", " . $downloaded . ", " . $uploaded . ", " . sqlesc(unesc($passkey)) . ")") or err("PL Err 2");

    if ($db->affected_rows) {
        $updateset[] = ($seeder == 'yes' ? 'seeders = seeders + 1' : 'leechers = leechers + 1');
		
		if ($seeder == 'yes')
			cached::adjust_torrent_peers($torrentid, 1, 0, 0);
        else
			cached::adjust_torrent_peers($torrentid, 0, 1, 0);

        $db->query("UPDATE snatched
                    SET ip = " . sqlesc($ip) . ", port = " . $port . ", connectable = '" . $connectable . "', to_go = " . $left . ", last_action = '" . get_date_time() . "', seeder = '" . $seeder . "', agent = " . sqlesc($agent) . "
                    WHERE torrentid = " . $torrentid . "
                    AND userid = " . $userid) or err("SL Err 3");

        if (!$db->affected_rows && $seeder == 'no') {
            $db->query("INSERT INTO snatched (torrentid, userid, peer_id, ip, port, connectable, uploaded, downloaded, to_go, start_date, last_action, seeder, agent)
                        VALUES (" . $torrentid . ", " . $userid . ", " . sqlesc($peer_id) . ", " . sqlesc($ip) . ", " . $port . ", '" . $connectable . "', " . $uploaded . ", " . $downloaded . ", " . $left . ", '" . get_date_time() . "', '" . get_date_time() . "', '" . $seeder . "', " . sqlesc($agent) . ")") or err("SL Err 4");
        }
    }
}

if ($seeder == 'yes') {
    if ($torrent['banned'] != 'yes') {
        $updateset[] = "visible = 'yes'";
    }

    $updateset[] = "last_action = NOW()";
}

if (count($updateset)) {
    $db->query("UPDATE LOW_PRIORITY torrents
                SET " . join(",", $updateset) . "
                WHERE id = " . $torrentid);
}

benc_resp_raw($resp);

?>
