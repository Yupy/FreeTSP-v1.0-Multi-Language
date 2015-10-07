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
require_once(FUNC_DIR . 'function_torrenttable.php');
require_once(FUNC_DIR . 'function_bbcode.php');
require_once(FUNC_DIR . 'function_page_verify.php');

db_connect(false);
logged_in();

$lang = array_merge(load_language('userdetails'),
                    load_language('func_vfunctions'),
                    load_language('func_bbcode'),
                    load_language('global'));

$newpage = new page_verify();
$newpage->create('_modtask_');

function snatchtable($res)
{
    global $image_dir, $lang, $db;

    $table = "<table class='main' border='1' width='100%' cellspacing='0' cellpadding='5'>
                <tr>
                    <td class='colhead' width='5%' align='center'>{$lang['table_snatch_cat']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_tor']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_up']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_rate']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_down']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_rate']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_ratio']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_activity']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_finished']}</td>
                </tr>";

    while ($arr = $res->fetch_assoc())
    {
        $upspeed   = ($arr['upspeed'] > 0 ? misc::mksize($arr['upspeed']) : ($arr['seedtime'] > 0 ? misc::mksize($arr['uploaded'] / ($arr['seedtime'] + $arr['leechtime'])) : misc::mksize(0)));

        $downspeed = ($arr['downspeed'] > 0 ? misc::mksize($arr['downspeed']) : ($arr['leechtime'] > 0 ? misc::mksize($arr['downloaded'] / $arr['leechtime']) : misc::mksize(0)));

        $ratio     = ($arr['downloaded'] > 0 ? number_format($arr['uploaded'] / $arr['downloaded'], 3) : ($arr['uploaded'] > 0 ? "{$lang['table_snatch_inf']}" : "---"));
        $table     .= "<tr>
                    <td class='rowhead' align='center' style='padding : 0px'>
                        <img src='{$image_dir}caticons/" . security::html_safe($arr['catimg']) . "' width='60' height='54' border='0' alt='" . security::html_Safe($arr['catname']) . "' title='" . security::html_safe($arr['catname']) . "' />
                    </td>
                    <td class='rowhead' align='center'>
                        <a href='details.php?id={$arr['torrentid']}'><strong>" . (strlen($arr['name']) > 50 ? substr($arr['name'], 0, 50 - 3) . '...' : security::html_safe($arr['name'])) . "</strong></a>
                    </td>
                    <td class='rowhead' align='center'>" . misc::mksize($arr['uploaded']) . "</td>
                    <td class='rowhead' align='center'>$upspeed/s</td>
                    <td class='rowhead' align='center'>" . misc::mksize($arr['downloaded']) . "</td>
                    <td class='rowhead' align='center'>$downspeed/s</td>
                    <td class='rowhead' align='center'>$ratio</td>
                    <td class='rowhead' align='center'>" . mkprettytime($arr['seedtime'] + $arr['leechtime']) . "</td>
                    <td class='rowhead' align='center'>
                        " . ($arr['complete_date'] <> "0000-00-00 00:00:00" ? "<span class='userdetails_snatched_complete_yes'><strong>{$lang['table_snatch_yes']}</strong></span>" : "<span class='userdetails_snatched_complete_no'><strong>{$lang['table_snatch_no']}</strong></span>") . "
                    </td>
                </tr>";
    }
    $table .= "</table>\n";

    return $table;
}

function snatch_table($res)
{
    global $image_dir, $lang, $db;

    $table1 = "<table class='main' border='1' width='100%' cellspacing='0' cellpadding='5'>
                <tr>
                    <td class='colhead' align='center'>{$lang['table_snatch_cat']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_tor']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_sl']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_up_down']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_tor_size']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_ratio']}</td>
                    <td class='colhead' align='center'>{$lang['table_snatch_client']}</td>
                </tr>";

    while ($arr = $res->fetch_assoc())
    {
        //-- Speed Color Red Fast Green Slow ;) --//
        if ($arr['upspeed'] > 0)
        {
            $ul_speed = ($arr['upspeed'] > 0 ? misc::mksize( $arr['upspeed']) : ($arr['seedtime'] > 0 ? misc::mksize( $arr['uploaded'] / ($arr['seedtime'] + $arr['leechtime'])) : misc::mksize(0)));
        }
        else
        {
            $ul_speed = misc::mksize(($arr['uploaded'] / ($arr['l_a'] - $arr['s'] + 1)));
        }

        if ($arr['downspeed'] > 0)
        {
            $dl_speed = ($arr['downspeed'] > 0 ? misc::mksize($arr['downspeed']) : ($arr['leechtime'] > 0 ? misc::mksize( $arr['downloaded'] / $arr['leechtime'] ) : misc::mksize(0)));
        }
        else
        {
            $dl_speed = misc::mksize(($arr['downloaded'] / ($arr['c'] - $arr['s'] + 1)));
        }

        if ($arr['downloaded'] > 0)
        {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $ratio = "<font color='" . get_ratio_color( $ratio ) . "'><b>{$lang['table_snatch_ratio']}:</b><br />$ratio</font>";
        }
        else
        {
            if ($arr['uploaded'] > 0)
            {
                $ratio = "{$lang['table_snatch_inf']}";
            }
            else
            {
                $ratio = "{$lang['table_snatch_na']}";
            }
        }
        $table1 .= "<tr>
            <td align='center'>
                " . ($arr['owner'] == $id ? "<b>{$lang['table_snatch_owner']}</b><br />" : "
                " . ($arr['complete_date'] != '0000-00-00 00:00:00' ? "<b>{$lang['table_snatch_finished']}</b><br />" : "<b>{$lang['table_snatch_not_finished']}</b><br />") . "") . "
                <img src='{$image_dir}caticons/" . security::html_safe($arr['image']) . "' width='60' height='54' border='0' alt='" . security::html_safe($arr['name']) . "' title='" . security::html_safe($arr['name']) . "' />
            </td>

            <td align='center'>
                <a class='altlink' href='details.php?id={$arr['torrentid']}'><b>" . security::html_safe($arr['torrent_name']) . "</b></a>
                " . ($arr['complete_date'] != '0000-00-00 00:00:00' ? "<br />

                <b>{$lang['table_snatch_started']}</b><br />{$arr['start_date']}<br />

                <b>{$lang['table_snatch_completed']}</b><br />{$arr['complete_date']}" : "<br />

                <b>{$lang['table_snatch_last_action']}</b>{$arr['last_action']}
                " . ($arr['complete_date'] == '0000-00-00 00:00:00' ? "
                " . ($arr['owner'] == $id ? "" : "[ " . misc::mksize( $arr['size'] - $arr['downloaded']) . "{$lang['table_snatch_to_go']}]") . "" : "") . "") . "
                " . ($arr['complete_date'] != '0000-00-00 00:00:00' ? "<br />

                <b>{$lang['table_snatch_time_dl']}</b>" . ($arr['leechtime'] != '0' ? mkprettytime($arr['leechtime']) : mkprettytime($arr['c'] - $arr['s']) . "" ) . "<br />

                [{$lang['table_snatch_dl_at']}$dl_speed ]<br />" : "<br />" ) . "

                " . ( $arr['seedtime'] != '0' ? "<b>{$lang['table_snatch_total_seed']}</b>" . mkprettytime($arr['seedtime']) . " " : "<b>{$lang['table_snatch_total_seed']}</b>{$lang['table_snatch_na']}" ) . "<br />

                [{$lang['table_snatch_up_at']}@ $ul_speed ]

                " . ($arr['complete_date'] == '0000-00-00 00:00:00' ? "<br />

                <b>{$lang['table_snatch_dl_speed']}</b> $dl_speed" : "") . "
            </td>

            <td align='center'>
                <span class='userdetails_snatched_seeders'>{$lang['table_snatch_seeds']}<b>{$arr['seeders']}</b></span><br />
                <span class='userdetails_snatched_leechers'>{$lang['table_snatch_leech']}<b>{$arr['leechers']}</b></span>
            </td>

            <td align='center'>
                <span class='userdetails_snatched_ul'>{$lang['table_snatch_up']}<br /><b>" . $uploaded = misc::mksize($arr['uploaded']) . "</b></span><br />
                <span class='userdetails_snatched_dl'>{$lang['table_snatch_down']}<br /><b>" . $downloaded = misc::mksize($arr['downloaded']) . "</b></span>
            </td>

            <td align='center'>
                <span class='userdetails_snatched_dl_diff'>" . misc::mksize($arr['size']) . "</span><br /><b>{$lang['table_snatch_diff']}</b><br /><b>
                <span class='userdetails_snatched_dl_size'>" . misc::mksize($arr['size'] - $arr['downloaded']) . "</span></b>
            </td>

            <td align='center'>$ratio<br />" . ($arr['seeder'] == "yes" ? "
                <span class='userdetails_snatched_seeding'><b>{$lang['table_snatch_seeding']}</b></span>" : "
                <span class='userdetails_snatched_leeching'><b>{$lang['table_snatch_not_seeding']}</b></span>") . "
            </td>

            <td align='center'>
                {$arr['agent']}<br /><b>{$lang['table_snatch_port']}</b>{$arr['port']}<br />

                " . ($arr['connectable'] == "yes" ? "<b>{$lang['table_snatch_connectable']}<span class='userdetails_snatched_con'>{$lang['table_snatch_yes']}</span></b>" : "<b>{$lang['table_snatch_connectable']}</b><span class='userdetails_snatched_uncon'><b>{$lang['table_snatch_no']}</b></span>") . "
            </td>
        </tr>\n";
    }
    $table1 .= "</table>\n";

    return $table1;
}

function maketable($res)
{
    global $image_dir, $lang, $db;

    $ret = "<table class='main' border='1' width='100%' cellspacing='0' cellpadding='5'>
                <tr>
                    <td class='colhead' width='5%' align='center'>{$lang['table_seed_cat']}</td>
                    <td class='colhead' align='center'>{$lang['table_seed_name']}</td>
                    <td class='colhead' align='center' width='7%'>{$lang['table_seed_size']}</td>
                    <td class='colhead' align='right' width='5%'>{$lang['table_seed_seeds']}</td>
                    <td class='colhead' align='right' width='5%'>{$lang['table_seed_leech']}</td>
                    <td class='colhead' align='center' width='7%'>{$lang['table_seed_ul']}</td>
                    <td class='colhead' align='center' width='7%'>{$lang['table_seed_dl']}</td>
                    <td class='colhead' align='center' width='7%'>{$lang['table_seed_ratio']}</td>
                </tr>\n";

    foreach ($res
             AS
             $arr)
    {
        if ($arr['downloaded'] > 0)
        {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $ratio = "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
        }
        else
        {
            if ($arr['uploaded'] > 0)
            {
                $ratio = "{$lang['table_seed_inf']}";
            }
            else
            {
                $ratio = "---";
            }
        }

        $catimage = "{$image_dir}caticons/{$arr['image']}";
        $catname  = security::html_safe($arr['catname']);
        $catimage = "<img src='" . security::html_safe($catimage) . "' width='60' height='54' border='0' alt='$catname' title='$catname' />";

/*
        $ttl = (28*24) - floor((gmtime() - sql_timestamp_to_unix_timestamp($arr['added'])) / 3600);
        if ($ttl == 1) $ttl .= "<br />{$lang['table_seed_hour']}"; else $ttl .= "<br />{$lang['table_seed_hours']}";
*/
        $size       = str_replace(" ", "<br />", misc::mksize($arr['size']));
        $uploaded   = str_replace(" ", "<br />", misc::mksize($arr['uploaded']));
        $downloaded = str_replace(" ", "<br />", misc::mksize($arr['downloaded']));
        $seeders    = number_format($arr['seeders']);
        $leechers   = number_format($arr['leechers']);

        $ret .= "<tr>
                    <td class='rowhead' align='center' style='padding: 0px'>$catimage</td>
                    <td class='rowhead' align='left'>
                        <a href='details.php?id={$arr['torrent']}&amp;hit=1'><strong>" . security::html_safe($arr['torrentname']) . "</strong></a>
                    </td>
                    <td class='rowhead' align='center'>$size</td>
                    <td class='rowhead' align='right'>$seeders</td>
                    <td class='rowhead' align='right'>$leechers</td>
                    <td class='rowhead' align='center'>$uploaded</td>
                    <td class='rowhead' align='center'>$downloaded</td>
                    <td class='rowhead' align='center'>$ratio</td>
                </tr>\n";
    }
    $ret .= "</table>\n";
    return $ret;
}

$id = intval(0 + $_GET['id']);

if (!is_valid_id($id))
{
    error_message("error",
                  "{$lang['gbl_error']}",
                  "{$lang['err_bad_id']}$id.");
}

#Other...
if (($get_user = $Memcache->get_value('user::profile::' . $id)) === false) {
    $user = $db->query('SELECT added, country, username, avatar, class, pcoff, parked, email, title, supportfor, acceptpms, info, signature, donor, support, support_lang, modcomment FROM users WHERE id = ' . $id);
    $get_user = $user->fetch_array(MYSQLI_BOTH);
    $get_user['country'] = (int)$get_user['country'];
    $get_user['class'] = (int)$get_user['class'];
    $Memcache->cache_value('user::profile::' . $id, $get_user, 14400);
}


#Stats Upped, Downed, etc...
if (($user_stats = $Memcache->get_value('user::profile::stats::' . $id)) === false) {
    $stats = $db->query('SELECT uploaded, downloaded, invites FROM users WHERE id = ' . $id);
    $user_stats = $stats->fetch_array(MYSQLI_BOTH);
    $user_stats['uploaded'] = (float)$user_stats['uploaded'];
    $user_stats['downloaded'] = (float)$user_stats['downloaded'];
	$user_stats['invites'] = (int)$user_stats['invites'];
    $Memcache->cache_value('user::profile::stats::' . $id, $user_stats, 1800);
}

$r = @$db->query("SELECT id, status, ip, last_access, onlinetime, enabled, invitedby, reputation, invite_rights, warned, warneduntil, uploadpos, uploadposuntil, downloadpos, shoutboxpos, shoutboxposuntil, torrcompos, torrcomposuntil, offercompos, offercomposuntil, requestcompos, requestcomposuntil, forumpos, forumposuntil
                 FROM users
                 WHERE id = " . $db->real_escape_string($id)) or sqlerr();

$user = $r->fetch_assoc() or error_message("error",
                                               "{$lang['gbl_error']}",
                                               "{$lang['err_no_userid']}$id.");

if ($user['status'] == 'pending')
{
    error_message("error",
                  "{$lang['gbl_sorry']}",
                  "{$lang['err_pending']}");
}

$r = $db->query("SELECT id, name, seeders, leechers, category
                FROM torrents
                WHERE owner = $id
                ORDER BY name") or sqlerr();

if ($r->num_rows > 0)
{
    $torrents = "<table class='main' border='1' width='100%' cellspacing='0' cellpadding='5'>
    <tr>
        <td class='colhead' align='center' width='5%' style='padding : 0px'>{$lang['table_up_cat']}</td>
        <td class='colhead' align='center'>{$lang['table_up_name']}</td>
        <td class='colhead' align='center' width='10%'>{$lang['table_up_seeds']}</td>
        <td class='colhead' align='center' width='10%'>{$lang['table_up_leech']}</td>
    </tr>";

    while ($a = $r->fetch_assoc())
    {
		if (($a2 = $Memcache->get_value('profile::torrents::categories::' . $a['category'])) === false) {
            $r2 = $db->query("SELECT name, image
                              FROM categories
                              WHERE id = " . (int)$a['category']) or sqlerr(__FILE__, __LINE__);

            $a2 = $r2->fetch_assoc();
            $Memcache->cache_value('profile::torrents::categories::' . $a['category'], $a2, 7200);
        }

        $cat = "<img src='" . security::html_safe("{$image_dir}caticons/{$a2['image']}") . "' width='60' height='54' border='0' alt='" . security::html_safe($a2['name']) . "' title='" . security::html_safe($a2['name']) . "' />";

        $torrents .= "<tr>
                        <td class='rowhead' style='padding : 0px'>$cat</td>
                        <td class='rowhead' align='left' style='padding : 0px'>
                            <a href='details.php?id={$a['id']}&amp;hit=1'>&nbsp;<strong>" . security::html_safe($a['name']) . "</strong></a>
                        </td>
                        <td class='rowhead' align='right'>{$a['seeders']}</td>
                        <td class='rowhead' align='right'>{$a['leechers']}</td>
                    </tr>";
    }
    $torrents .= "</table>";
}

if ($user['ip'] && (get_user_class() >= UC_MODERATOR || $user['id'] == user::$current['id']))
{
    $ip  = unesc($user['ip']);
    $dom = @gethostbyaddr($user['ip']);

    if ($dom == $user['ip'] || @gethostbyname($dom) != $user['ip'])
    {
        $addr = $ip;
    }
    else
    {
        $dom      = strtoupper($dom);
        $domparts = explode(".", $dom);
        $domain   = $domparts[count($domparts) - 2];

        if ($domain == "COM" || $domain == "CO" || $domain == "NET" || $domain == "NE" || $domain == "ORG" || $domain == "OR")
        {
            $l = 2;
        }
        else
        {
            $l = 1;
        }
        $addr = "$ip ($dom)";
    }
}

if ($get_user['added'] == "0000-00-00 00:00:00")
{
    $joindate = "{$lang['table_general_na']}";
}
else
{
    $joindate = $get_user['added'] . " (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($get_user['added'])) . "{$lang['table_general_ago']})";
    $lastseen = $user['last_access'];
}

if ($lastseen == "0000-00-00 00:00:00")
{
    $lastseen = "{$lang['table_general_never']}";
}
else
{
    $lastseen .= " (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($lastseen)) . "{$lang['table_general_ago']})";
}

if ($user['onlinetime'] > 0)
{
    $onlinetime = time_return($user['onlinetime']);
}
else
{
    $onlinetime = "{$lang['table_general_never']}";
}

if ($user['last_access'] > (get_date_time(gmtime() - 60)))
{
    $status = "<span class='userdetails_online'>{$lang['table_general_online']}</span>";
}
else
{
    $status = "<span class='userdetails_offline'>{$lang['table_general_offline']}</span>";
}

if (($torrentcomments = $Memcache->get_value('user::profile::comments::count::' . $user['id'])) === false) {
    $res = $db->query("SELECT COUNT(id)
                       FROM comments
                       WHERE user = " . (int)$user['id']) or sqlerr();

    $arr3            = $res->fetch_row();
    $torrentcomments = (int)$arr3[0];
    $Memcache->cache_value('user::profile::comments::count::' . $user['id'], $torrentcomments, 14400);
}

if (($forumposts = $Memcache->get_value('user::profile::forum::posts::' . $user['id'])) === false) {
    $res = $db->query("SELECT COUNT(id)
                       FROM posts
                       WHERE userid = " . (int)$user['id']) or sqlerr();

    $arr3       = $res->fetch_row();
    $forumposts = (int)$arr3[0];
    $Memcache->cache_value('user::profile::forum::posts::' . $user['id'], $forumposts, 7200);
}

$country = '';
$res = $db->query("SELECT name, flagpic
                  FROM countries
                  WHERE id = " . (int)$get_user['country'] . "
                  LIMIT 1") or sqlerr();

if ($res->num_rows == 1)
{
    $arr     = $res->fetch_assoc();
    $country = "<td class='embedded'>
                    <img src='{$image_dir}flag/{$arr['flagpic']}' width='32' height='20' border='0' alt='" . security::html_safe($arr['name']) . "' title='" . security::html_safe($arr['name']) . "' style='margin-left : 8pt' />
                </td>";
}

$res = $db->query("SELECT p.torrent, p.uploaded, p.downloaded, p.seeder, t.added, t.name AS torrentname, t.size, t.category, t.seeders, t.leechers, c.name AS catname, c.image
                  FROM peers p
                  LEFT JOIN torrents t ON p.torrent = t.id
                  LEFT JOIN categories c ON t.category = c.id
                  WHERE p.userid = $id") or sqlerr();

while ($arr = $res->fetch_assoc())
{
    if ($arr['seeder'] == "yes")
    {
        $seeding[] = $arr;
    }
    else
    {
        $leeching[] = $arr;
    }
}

if ($user_stats['downloaded'] > 0)
{
    $sr = $user_stats['uploaded'] / $user_stats['downloaded'];
    if ($sr >= 4)
        $s = "cool";

    else if ($sr >= 2)
        $s = "grin";

    else if ($sr >= 1)
        $s = "happy";

    else if ($sr >= 0.5)
        $s = "expressionless";

    else if ($sr >= 0.25)
        $s = "sad";

    else
        $s = "reallyevil";

    $sr = floor($sr * 1000) / 1000;
    $sr = "<table border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td class='embedded'>
                    <font color='" . get_ratio_color($sr) . "'>" . number_format($sr, 3) . "</font>
                </td>
                <td class='embedded'>&nbsp;&nbsp;
                    <img src='{$image_dir}smilies/{$s}.png' width='16' height='16' border='0' alt='$s' title='$s' />
                </td>
            </tr>
        </table>";
}

//-- Connectable And Port Shit --//
$q1 = $db->query("SELECT connectable, port, agent
                 FROM peers
                 WHERE userid = $id LIMIT 1") or sqlerr();

if ($a = $q1->fetch_row())
{
    $connect = $a[0];

    if ($connect == "yes")
    {
        $connectable = "<span class='userdetails_connectable'>{$lang['table_general_yes']}</span>";
    }
    else
    {
        $connectable = "<span class='userdetails_unconnectable'>{$lang['table_general_no']}</span>";
    }
}
else
{
    $connectable = "<img src='{$image_dir}smilies/expressionless.png' width='16' height='16' border='0' alt='{$lang['img_alt_not_connected']}' title='{$lang['img_alt_not_connected']}' style='border : none; padding : 2px;' /><span class='userdetails_notconnected'>{$lang['img_alt_not_connected']}</span>";
}

site_header("{$lang['title_details_for']}" . security::html_safe($get_user['username']));

//-- Start Reset Members Password Part 1 Of 2 --//
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $username = trim($_POST['username']);

    $res = $db->query("SELECT *
                      FROM users
                      WHERE username = " . sqlesc($username)) or sqlerr();

    $arr = $res->fetch_assoc();

    $nick         = ($username . mt_rand(1000, 9999));
    $id           = (int)$arr['id'];
    $wantpassword = $nick;
    $secret       = mksecret();
    $wantpasshash = md5($secret . $wantpassword . $secret);

    $db->query("UPDATE users
               SET passhash = '$wantpasshash'
               WHERE id = $id");

    $db->query("UPDATE users
               SET secret = '$secret'
               WHERE id = $id");

    if ($db->affected_rows != 1)

        error_message("warn",
                      "{$lang['gbl_warning']}",
                      "{$lang['err_fail_reset_pass']}");

    error_message("success",
                  "{$lang['gbl_success']}",
                  "{$lang['err_pass_for']}<strong>$username</strong>{$lang['err_pass_reset_to']}<br /><br /><strong>$nick</strong><br /><br />{$lang['err_pass_inform_user']}");
}
//-- Finish Reset Members Password Part 1 Of 2 --//

$enabled = $user['enabled'] == "yes";

print("<br /><table class='main' border='0' align='center' cellspacing='0' cellpadding='0'>");
print("<tr>
        <td class='embedded'>
            <h1 style='margin:0px'>" . security::html_safe($get_user['username']) . "" . get_user_icons($user, true) . "</h1>
        </td>" . $country . "
    </tr>");

print("</table><br />");
print("<table class='main' border='0' align='center' cellspacing='0' cellpadding='0'>");

if (!empty($get_user['avatar']))
{
	$avatar = ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? security::html_safe($get_user['avatar']) : '');
    if (empty($avatar)) {
        $avatar = $image_dir . "default_avatar.gif";
    }
    print("<tr>
            <td class='rowhead' align='left'>
                <img src='" . $avatar . "' width='125' height='125' border='0' alt='' title='' />
            </td>
        </tr>");
}
else
{
    print("<tr>
            <td class='rowhead' align='left'>
                <img src='{$image_dir}default_avatar.gif' width='125' height='125' border='0' alt='' title='' />
            </td>
        </tr>");
}

print("</table><br />");

if (!$enabled)
{
    print("<p><strong>{$lang['text_acc_disabled']}</strong></p>\n");
}
elseif (user::$current['id'] <> $user['id'])
{
    $r = $db->query("SELECT id
                    FROM friends
                    WHERE userid = " . user::$current['id'] . "
                    AND friendid = $id") or sqlerr(__FILE__, __LINE__);

    $friend = $r->num_rows;

    $r = $db->query("SELECT id
                    FROM blocks
                    WHERE userid = " . user::$current['id'] . "
                    AND blockid = $id") or sqlerr(__FILE__, __LINE__);

    $block = $r->num_rows;

    if ($friend)
    {
        print("<div align='center'>
                <a class='btn' href='friends.php?action=delete&amp;type=friend&amp;targetid=$id'>{$lang['btn_del_friend']}</a>
            </div><br />\n");
    }
    elseif ($block)
    {
        print("<div align='center'>
                <a class='btn' href='friends.php?action=delete&amp;type=block&amp;targetid=$id'>{$lang['btn_del_block']}</a>
            </div><br />\n");
    }
    else
    {
        print("<div align='center'>
                <a class='btn' href='friends.php?action=add&amp;type=friend&amp;targetid=$id'>{$lang['btn_add_friend']}</a>");
        print("&nbsp;&nbsp;&nbsp;&nbsp;<a href='friends.php?action=add&amp;type=block&amp;targetid=$id' class='btn'>{$lang['btn_add_block']}</a></div><br />\n");
    }
}

if ($user['enabled'] == "yes")
{
    if (get_user_class() >= UC_SYSOP && $get_user['class'] < get_user_class())

    print("<a class='btn' href='quickban.php?id=" . $user['id'] . "'>{$lang['btn_quick_ban']}</a><br /><br />");
}

if ($get_user['pcoff'] == "yes")
{
    print("<span class='userdetails_pcon'>{$lang['text_pc_on']}</span>");
}
else
{
    print("<span class='userdetails_pcoff'>{$lang['text_pc_off']}</span>");
}

if ($get_user['parked'] == "yes")
{
    print("<br /><br /><span class='userdetails_parked'>{$lang['text_pc_parked']}</span>");
}

print("<div align='center' id='featured'>");
print("<br />");
print("<ul>
       <li><a class='btn' href='#fragment-1'>{$lang['btn_general']}</a></li>
       <li><a class='btn' href='#fragment-2'>{$lang['btn_torrents']}</a></li>
       <li><a class='btn' href='#fragment-3'>{$lang['btn_info']}</a></li>
       ");

if (get_user_class() >= UC_MODERATOR || $user['id'] == user::$current['id'])
{
    print("<li><a class='btn' href='#fragment-4'>{$lang['btn_snatch_list']}</a></li>");
}

if (get_user_class() >= UC_MODERATOR || $user['id'] == user::$current['id'])
{
    print("<li><a class='btn' href='#fragment-8'>{$lang['btn_invited']}</a></li>");
}

if (get_user_class() >= UC_SYSOP && $get_user['class'] < get_user_class())
{
    print("<li><a class='btn' href='#fragment-5'>{$lang['btn_alter_ratio']}</a></li>");
}

if (get_user_class() >= UC_MODERATOR && $get_user['class'] < get_user_class())
{
    print("<li><a class='btn' href='#fragment-6'>{$lang['btn_edit_user']}</a></li>");
}

if (get_user_class() >= UC_SYSOP && $get_user['class'] < get_user_class())
{
    print("<li><a class='btn' href='#fragment-7'>{$lang['btn_reset_pass']}</a></li>");
}

print("</ul>");
print("<br />");

//-- Start General Details Content --//
print("<div class='ui-tabs-panel' id='fragment-1'>");
print("<table class='coltable' width='70%'>");

print("<tr>
        <td class='std' align='center' colspan='2'>
            <h2>{$lang['table_general_details']}</h2>
        </td>
    </tr>");

print("<tr>
        <td class='colhead' width='20%'>&nbsp;{$lang['table_general_joined']}</td>
        <td class='rowhead' align='left' width='99%'>&nbsp;$joindate</td>
    </tr>");

print("<tr>
        <td class='colhead' width='20%'>&nbsp;{$lang['table_general_last_seen']}</td>
        <td class='rowhead' align='left'>&nbsp;$lastseen</td>
    </tr>");

print("<tr>
        <td class='colhead'>&nbsp;{$lang['table_general_time_online']}</td>
        <td class='rowhead' align='left'>&nbsp;$onlinetime</td>
    </tr>");

print("<tr>
        <td class='colhead'>&nbsp;{$lang['table_general_status']}</td>
        <td class='rowhead' align='left'>&nbsp;$status</td>
    </tr>");

if (get_user_class() >= UC_MODERATOR && $user['invitedby'] > 0 || $user['id'] == user::$current['id'] && $user['invitedby'] > 0)
{
    $invitedby  = $db->query("SELECT username
                             FROM users
                             WHERE id = ". sqlesc($user['invitedby']));

    $invitedby2 = $invitedby->fetch_array(MYSQLI_BOTH);

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['table_general_invited_by']}</td>
            <td class='rowhead' align='left'>
                &nbsp;<a href='userdetails.php?id={$user['invitedby']}'>" . security::html_safe($invitedby2['username']) . "</a>
            </td>
        </tr>");
}

if ( get_user_class() >= UC_MODERATOR )
{
    print("<tr>
            <td class='colhead' width='20%'>&nbsp;{$lang['table_general_email']}</td>
            <td class='rowhead' align='left'>
                <a href='mailto:" . $get_user['email'] . "'>&nbsp;" . security::html_safe($get_user['email']) . "</a>
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['table_general_address']}</td>
            <td class='rowhead' align='left'>&nbsp;$addr</td>
        </tr>");
}

if ($site_reputation == true)
{
    print("<tr>
            <td class='colhead'>&nbsp;{$lang['table_general_give_rep']}</td>
            <td class='rowhead' align='left'>
                <form method='post' action='takereppoints.php?id=" . $user['id'] . "'>
                    <input type='submit' class='btn' name='givepoints' value='{$lang['btn_give_rep']}' />
                </form>
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['table_general_cur_rep']}</td>
            <td class='rowhead' align='left'>&nbsp;{$lang['table_general_rep_points']}{$user['reputation']}");

    $total   = 0 + $user['reputation'];
    $nbrpics = 0 + $total / 5;
    $nbrpics = (int) $nbrpics;

    while ($nbrpics > 0)
    {
        echo "&nbsp;<img src='{$image_dir}rep.png' width='24' height='25' border='0' alt='{$lang['img_alt_rep']}' title='{$lang['img_alt_rep']}' />&nbsp;";

        $nbrpics = 0 + $nbrpics - 1;
    }

    print("<br /></td></tr>");

}

if ($get_user['title'])
{
    print("<tr>
           <td class='colhead' width='20%'>&nbsp;{$lang['table_general_title']}</td>
           <td class='rowhead' align='left'>&nbsp;" . security::html_safe($get_user['title']) . "</td>
        </tr>");
}

print("<tr>
       <td class='colhead' width='20%'>&nbsp;{$lang['table_general_class']}</td>
       <td class='rowhead' align='left'>&nbsp;" . get_user_class_name($get_user['class']) . "</td>
    </tr>");

if ($get_user['supportfor'])
{
    print("<tr>
            <td class='colhead'>&nbsp;{$lang['table_general_fls']}</td>
            <td class='rowhead' align='left'>&nbsp;" . security::html_safe($get_user['supportfor']) . "</td>
        </tr>");
}

print("<tr>
       <td class='colhead' width='20%'>&nbsp;{$lang['table_general_forum_posts']}</td>");

if ($forumposts && (($get_user['class'] >= UC_POWER_USER && $user['id'] == user::$current['id']) || get_user_class() >= UC_MODERATOR))
{
    print("<td class='rowhead' align='left'>
            <a href='userhistory.php?action=viewposts&amp;id=$id'>&nbsp;$forumposts</a>
        </td>
    </tr>");
}
else
{
    print("<td class='rowhead' align='left'>&nbsp;$forumposts</td>
       </tr>");
}

if (user::$current['id'] != $user['id'])
{
    if (get_user_class() >= UC_MODERATOR)
    {
        $showpmbutton = 1;
    }
}

if ($get_user['acceptpms'] == "yes")
{
    $r = $db->query("SELECT id
                    FROM blocks
                    WHERE userid = " . $user['id'] . "
                    AND blockid = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

    $showpmbutton = ($r->num_rows == 1 ? 0 : 1);
}

if ($get_user['acceptpms'] == "friends")
{
    $r = $db->query("SELECT id
                    FROM friends
                    WHERE userid = " . $user['id'] . "
                    AND friendid = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

    $showpmbutton = ($r->num_rows == 1 ? 1 : 0);
}

if ($user['id'] != user::$current['id'])
{
    print("<tr>
            <td class='std' align='center' colspan='2'>
                <form method='post' action='report.php?type=User&amp;id=$id'>
                    <input type='submit' class='btn' value='{$lang['btn_report_user']}' />
                </form>
            </td>
        </tr>");

    if (isset($showpmbutton))
    {
        print("<tr>
                <td class='std' align='center' colspan='2'>
                    <form method='get' action='sendmessage.php'>
                        <input type='hidden' name='receiver' value='" . $user['id'] . "' />
                        <input type='submit' class='btn' value='{$lang['btn_send_msg']}' style='height : 23px' />
                    </form>
                </td>
            </tr>");
    }
}

print("</table>");
print("</div>");
//-- Finish General Details Content --//

//-- Start Torrent Details Content --//
print("<div class='ui-tabs-panel' id='fragment-2'>");
print("<table class='coltable' width='70%'>");

print("<tr>
       <td class='std' align='center' colspan='2'>
            <h2>{$lang['table_torrents_details']}</h2>
        </td>
    </tr>");

$port  = $a[1];
$agent = $a[2];

if (!empty($port))
{
    print("<tr>
            <td class='colhead'>&nbsp;{$lang['table_torrents_port']}</td>
            <td align='left'>&nbsp;$port</td>
        </tr>
        <tr>
            <td class='colhead'>&nbsp;{$lang['table_torrents_client']}</td>
            <td align='left'>&nbsp;" . htmlentities($agent) . "</td>
        </tr>");

    print("<tr>
            <td class='colhead' width='20%'>&nbsp;{$lang['table_torrents_connectable']}</td>
            <td class='rowhead' align='left'>&nbsp;" . $connectable . "</td>
       </tr>");
}

    print("<tr>
            <td class='colhead' width='20%'>&nbsp;{$lang['table_torrents_uploaded']}</td>
            <td class='rowhead' align='left'>&nbsp;" . misc::mksize($user_stats['uploaded']) . "</td>
       </tr>");

    print("<tr>
            <td class='colhead' width='20%'>&nbsp;{$lang['table_torrents_downloaded']}</td>
            <td class='rowhead' align='left'>&nbsp;" . misc::mksize($user_stats['downloaded']) . "</td>
       </tr>");

    print("<tr>
            <td class='colhead' width='20%'>&nbsp;{$lang['table_torrents_ratio']}</td>
            <td class='rowhead' align='left' style='font-weight : bold;'>&nbsp;$sr</td>
       </tr>");

if ($torrentcomments && (($get_user['class'] >= UC_POWER_USER && $user['id'] == user::$current['id']) || get_user_class() >= UC_MODERATOR))
{
    print("<tr>
            <td class='colhead' width='20%'>&nbsp;{$lang['table_torrents_comments']}</td>");

    print("<td class='rowhead' align='left'>
            <a href='userhistory.php?action=viewcomments&amp;id=$id'>&nbsp;$torrentcomments</a>
        </td>
    </tr>");
}
else
{
    print("<tr>
            <td class='colhead' width='20%'>&nbsp;{$lang['table_torrents_comments']}</td>");

    print("<td class='rowhead' align='left'>&nbsp;$torrentcomments</td>
        </tr>");
}

print("<tr>
        <td class='colhead' width='20%'>&nbsp;{$lang['table_torrents_flush']}</td>
        <td align='left'>
            <a href='flushghosts.php?id=" . $user['id'] . "'>&nbsp;<strong>{$lang['table_torrents_flush1']}</strong></a>
            {$lang['table_torrents_flush2']}
        </td>
    </tr>");

print("</table>");
print("</div>");
//-- Finish Torrent Details Content --//

//-- Start Information Details Content --//
print("<div class='ui-tabs-panel' id='fragment-3'>");
print("<table class='coltable' width='70%'>");

print("<tr>
        <td class='std' align='center' colspan='2'>
            <h2>{$lang['table_info_details']}</h2>
        </td>
    </tr>");

if ($get_user['info'])
{
print("<tr>
       <td class='colhead' width='20%'>&nbsp;{$lang['table_info_share_info']}</td>
       <td class='rowhead' align='left'>&nbsp;" . format_comment($get_user['info']) . "</td>
    </tr>");
}

if ($get_user['signature'])
{
print("<tr>
        <td class='colhead' width='20%'>&nbsp;{$lang['table_info_signature']}</td>
        <td class='rowhead' align='left'>&nbsp;" . format_comment($get_user['signature']) . "</td>
    </tr>");
}

print("</table>");
print("</div>");
//-- Finish Information Details Content --//

//-- Start Snatch List Details Content --//
if (get_user_class() >= UC_MODERATOR || $user['id'] == user::$current['id'])
{
    print("<div class='ui-tabs-panel' id='fragment-4'>");
    print("<table class='coltable' width='70%'>");

    print("<tr>
            <td class='std' align='center' colspan='2'>
                <h2>{$lang['table_snatched_details']}</h2>
            </td>
        </tr>");

    //-- Start Recently Snatched Expanding Table --//
    $snatches = '';

    $r = $db->query("SELECT id, name, seeders, leechers, category
                   FROM torrents
                   WHERE owner = $id
                   ORDER BY name") or sqlerr();

    if ($r->num_rows > 0)
    {
        $numbupl = $r->num_rows;
    }

    if (isset($torrents))
    {
        print("<tr valign='top'>
                <td class='colhead' width='15%'>&nbsp;{$lang['table_snatched_uploaded']}&nbsp;</td>
                <td class='rowhead' align='left' colspan='85%'>
                    <a href=\"javascript: klappe_news('a1')\"><img src='{$image_dir}plus.png' width='16' height='16' border='0' id='pica1' alt='{$lang['img_alt_hide_show']}' title='{$lang['img_alt_hide_show']}' /></a>
                    <span class='userdetails_hide_show'>&nbsp;&nbsp;$numbupl</span>
                    <div id='ka1' style='display : none; overflow : auto; width : 100%; height : 200px'>$torrents</div>
                </td>
            </tr>");
    }

    //-- Start Expanding Currently Seeding --//
    if ($res->num_rows > 0)
    {
        $numbseeding = $res->num_rows;
    }

    if (isset($seeding))
    {
        print("<tr valign='top'>
                <td class='colhead' width='15%'>&nbsp;{$lang['table_snatched_seeding']}&nbsp;</td>
                <td class='rowhead' align='left' colspan='85%'>
                    <a href=\"javascript: klappe_news('a2')\"><img src='{$image_dir}plus.png' width='16' height='16' border='0' id='pica2' alt='{$lang['img_alt_hide_show']}' title='{$lang['img_alt_hide_show']}' /></a>
                    <span class='userdetails_hide_show'>&nbsp;&nbsp;$numbseeding</span>
                    <div id='ka2' style='display : none; overflow : auto; width : 100%; height : 200px'>&nbsp" . maketable($seeding) . "</div>
                </td>
            </tr>");
    }
    //-- Finish Expanding Currently Seeding --//

    //-- Start Expanding Currently Leeching --//
    if ($res->num_rows > 0)
    {
        $numbleeching = $res->num_rows;
    }

    if (isset($leeching))
    {
        print("<tr valign='top'>
                <td class='colhead' width='15%'>&nbsp;{$lang['table_snatched_leeching']}&nbsp;</td>
                <td class='rowhead' align='left' width='85%'>
                    <a href=\"javascript: klappe_news('a3')\"><img src='{$image_dir}plus.png' width='16' height='16' border='0' id='pica3' alt='{$lang['img_alt_hide_show']}' title='{$lang['img_alt_hide_show']}' /></a>
                    <span class='userdetails_hide_show'>&nbsp;&nbsp;$numbleeching</span>
                    <div id='ka3' style='display : none; overflow : auto; width : 100%; height : 200px'>&nbsp;" . maketable($leeching) . "</div>
                </td>
            </tr>");
    }
    //-- Finish Expanding Currently Leeching --//

    //-- Start Snatched Table --//
    $snatches = '';

    $res = $db->query("SELECT s.*, t.name AS name, c.name AS catname, c.image AS catimg
                        FROM snatched AS s
                        INNER JOIN torrents AS t ON s.torrentid = t.id
                        LEFT JOIN categories AS c ON t.category = c.id
                        WHERE s.userid = " . $user['id']) or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows > 0)
    {
        $snatches = snatchtable($res);
    }

    $numbsnatched = $res->num_rows;

    if (isset($snatches))
    {
        print("<tr valign='top'>
                <td class='colhead' width='15%'>&nbsp;{$lang['table_snatched_recent']}&nbsp;</td>
                <td class='rowhead' align='left' width='85%'>
                    <a href=\"javascript: klappe_news('a4')\"><img src='{$image_dir}plus.png' width='16' height='16' border='0'  id='pica4' alt='{$lang['img_alt_hide_show']}' title='{$lang['img_alt_hide_show']}' /></a>
                    <span class='userdetails_hide_show'>&nbsp;&nbsp;$numbsnatched</span>
                    <div id='ka4' style='display : none; overflow : auto; width : 100%; height : 200px'>$snatches</div>
                </td>
            </tr>");
    }
    //-- Finish Snatched Table --//

    //-- Finish Recently Snatched Expanding Table --//
    $res1 = $db->query("SELECT UNIX_TIMESTAMP(sn.start_date) AS s,
                              UNIX_TIMESTAMP(sn.complete_date) AS c,
                              UNIX_TIMESTAMP(sn.last_action) AS l_a,
                              UNIX_TIMESTAMP(sn.seedtime) AS s_t,
                              sn.seedtime,
                              UNIX_TIMESTAMP(sn.leechtime) AS l_t,
                              sn.leechtime, sn.downspeed, sn.upspeed, sn.uploaded, sn.downloaded, sn.torrentid, sn.start_date, sn.complete_date, sn.seeder, sn.last_action, sn.connectable, sn.agent, sn.seedtime, sn.port, cat.name, cat.image, t.size, t.seeders, t.leechers, t.owner, t.name AS torrent_name
                       FROM snatched AS sn
                       LEFT JOIN torrents AS t ON t.id = sn.torrentid
                       LEFT JOIN categories AS cat ON cat.id = t.category
                       WHERE sn.userid = $id
                       ORDER BY sn.start_date DESC") or die($db->error);

    if ($res1->num_rows > 0)
    {
        $snatched = snatch_table($res1);
    }

    $numbsnatched = $res1->num_rows;

    if (isset($snatched))
    {
        print("<tr valign='top'>
                <td class='colhead' width='15%'>&nbsp;{$lang['table_snatched_status']}&nbsp;</td>
                <td class='rowhead' align='left' width='85%'>
                    <a href=\"javascript: klappe_news('a5')\"><img src='{$image_dir}plus.png' width='16' height='16' border='0' id='pica5' alt='{$lang['img_alt_hide_show']}' title='{$lang['img_alt_hide_show']}' /></a>
                    <span class='userdetails_hide_show'>&nbsp;&nbsp;$numbsnatched</span>
                    <div id='ka5' style='display : none; overflow : auto; width : 100%; height : 200px'>$snatched</div>
                </td>
            </tr>");
    }

    print("</table>");
    print("</div>");}
//-- Finish Snatch List Details Content --//

//-- Start Alter Ratio Details Content --//
if (get_user_class() >= UC_SYSOP && $get_user['class'] < get_user_class())
{
    print("<div class='ui-tabs-panel' id='fragment-5'>");
    print("<form method='post' action='ratio.php'>");
    print("<table class='coltable' width='70%'>");

    print("<tr>
            <td class='std' align='center' colspan='2'>
                <h2>{$lang['table_ratio_details']}</h2>
            </td>
        </tr>");

//-- Start Create Ratio By Fireknight Based On The Original Code By Dodge --//
    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_ratio_user']}</td>
            <td class='rowhead'>
                <input name='username' value='" . security::html_safe($get_user['username']) . "' size='40' readonly='readonly' />
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_ratio_uploaded']}</td>
            <td class='rowhead'>
                <input type='text' name='uploaded' value='0' size='40' />
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_ratio_downloaded']}</td>
            <td class='rowhead'>
                <input type='text' name='downloaded' value='0' size='40' />
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_ratio_select']}</td>
            <td class='rowhead'>
                <select name='bytes'>");

    print("<option value='1'>{$lang['form_opt_ratio_mb']}</option>");
    print("<option value='2'>{$lang['form_opt_ratio_gb']}</option>");
    print("<option value='3'>{$lang['form_opt_ratio_tb']}</option>");
    print("</select></td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_ratio_action']}</td>
            <td class='rowhead'>
                <select name='action'>");

    print("<option value='1'>{$lang['form_opt_ratio_add']}</option>");
    print("<option value='2'>{$lang['form_opt_ratio_del']}</option>");
    print("<option value='3'>{$lang['form_opt_ratio_replace_ul']}</option>");
    print("<option value='4'>{$lang['form_opt_ratio_replace_dl']}</option>");
    print("</select></td></tr>");

    print("<tr>
            <td class='std' align='center' colspan='2'>
                <input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' />
            </td>
        </tr>");
//-- End Create Ratio By Fireknight Based On The Original Code By Dodge --//

    print("</table>");
    print("</form>");
    print("</div>");
}
//--- Finish Alter Ratio Details Content --//

//-- Start Edit User Details Content --//
if (get_user_class() >= UC_MODERATOR && $get_user['class'] < get_user_class())
{
    print("<div class='ui-tabs-panel' id='fragment-6'>");
    print("<form method='post' action='modtask.php'>");

    require_once(FUNC_DIR.'function_user_validator.php');

    print(validatorForm("ModTask_" . $user['id']));
    print("<input type='hidden' name='action' value='edituser' />");
    print("<input type='hidden' name='userid' value='$id' />");
    print("<input type='hidden' name='returnto' value='userdetails.php?id=$id' />");
    print("<table class='coltable' width='70%'>");

    print("<tr>
            <td class='std' align='center' colspan='3'>
                <h2>{$lang['table_edit_user_details']}</h2>
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_title']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='text' name='title' size='60' value='" . htmlsafechars($get_user['title']) . "' />
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_username']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='text' name='username' size='60' value='" . security::html_safe($get_user['username']) . "' />
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_email']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='text' name='email' size='60' value='" . security::html_safe($get_user['email']) . "' />
            </td>
        </tr>");

    $avatar = security::html_safe($get_user['avatar']);

    print("<tr>
            <td class='colhead' align='left'>&nbsp;{$lang['form_field_edit_avatar']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='text' name='avatar' size='60' value='" . security::html_safe($get_user['avatar']) . "' />
            </td>
        </tr>");

    $info = security::html_safe($get_user['info']);

    print("<tr>
            <td class='colhead'>&nbsp;User&nbsp;{$lang['form_field_edit_info']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='text' name='info' size='60' value='" . security::html_safe($get_user['info']) . "' />
            </td>
        </tr>");

    $signature = security::html_safe($get_user['signature']);

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_sig']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='text' name='signature' size='60' value='$signature' />
            </td>
        </tr>");

    //-- We Do Not Want Mods To Be Able To Change User Classes Or Amount Donated... --//
    if (user::$current['class'] >= UC_ADMINISTRATOR)
    {
        print("<tr>
                <td class='colhead'>&nbsp;{$lang['form_field_edit_donor']}</td>
                <td class='rowhead' align='left' colspan='2'>
                    <input type='radio' name='donor' value='yes' " . ($get_user['donor'] == "yes" ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                    <input type='radio' name='donor' value='no' " . ($get_user['donor'] == "no" ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
                </td>
            </tr>");
    }

    if ($get_user['class'] >= user::$current['class'])

    {
        print("<input type='hidden' name='class' value='" . $get_user['class'] . "' />");
    }
    else
    {
        print("<tr>
                <td class='colhead'>&nbsp;{$lang['form_field_edit_class']}</td>
                <td class='rowhead' align='left' colspan='2'>
                    <select name='class'>");

        if (get_user_class() == UC_MODERATOR)
        {
            $maxclass = UC_VIP;
        }
        else
        {
            $maxclass = get_user_class() - 1;
        }

        for ($i = 0;
             $i <= $maxclass;
             ++$i)
        {
            print("<option value='$i' " . ($get_user['class'] == $i ? " selected='selected' " : "") . ">$prefix " . get_user_class_name($i) . "</option>\n");
        }

        print("</select></td></tr>\n");
    }

    //-- First Line Support --//
    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_support']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='radio' name='support' value='yes' " . ($get_user['support'] == "yes" ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                <input type='radio' name='support' value='no' " . ($get_user['support'] == "no" ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
            </td>
        </tr>");

    $supportfor = security::html_safe($get_user['supportfor']);

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_sup_for']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <textarea name='supportfor' cols='60' rows='3'>$supportfor</textarea>
            </td>
        </tr>");

    $support_language = "<option value=''>----{$lang['form_field_opt_edit_non_sel']}----</option>";

    $sl_r = $db->query("SELECT name
                       FROM support_lang
                       ORDER BY name") or sqlerr(__FILE__, __LINE__);

    while ($sl_a = $sl_r->fetch_assoc())
    {
        $support_language .= "<option value='" . security::html_safe($sl_a['name']) . "' " . ($get_user['support_lang'] == $sl_a['name'] ? " selected='selected' " : "") . ">" . security::html_safe($sl_a['name']) . "</option>";
    }

    print("<tr>
             <td class='colhead'>&nbsp;{$lang['form_field_edit_lang']}</td>
             <td class='rowhead' colspan='2'>
                <select name='support_lang'>$support_language</select>
             </td>
         </tr>");
    //-- End --//

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_invite']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='radio' name='invite_rights' value='yes' " . ($user['invite_rights'] == "yes" ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                <input type='radio' name='invite_rights' value='no' " . ($user['invite_rights'] == "no" ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_invites']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='text' size='3' name='invites' value='" . security::html_safe($user_stats['invites']) . "' />
            </td>
        </tr>");

    $modcomment = security::html_safe($get_user['modcomment']);

    if (get_user_class() < UC_SYSOP)
    {
        print("<tr>
                <td class='colhead'>&nbsp;{$lang['form_field_edit_comment']}</td>
                <td class='rowhead' align='left' colspan='2'>
                    <textarea name='modcomment' cols='60' rows='18' readonly='readonly'>$modcomment</textarea>
                </td>
            </tr>");
    }
    else
    {
        print("<tr>
                <td class='colhead'>&nbsp;{$lang['form_field_edit_comment']}</td>
                <td class='rowhead' align='left' colspan='2'>
                    <textarea name='modcomment' cols='60' rows='18'>$modcomment</textarea>
                </td>
            </tr>");
    }

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_add_comm']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <textarea name='addcomment' cols='60' rows='2'></textarea>
            </td>
        </tr>");

    $warned = $user['warned'] == "yes";

    print("<tr>
            <td class='colhead' " . (!$warned ? " rowspan='2' " : "") . ">&nbsp;{$lang['form_field_edit_warned']}</td>
            <td class='rowhead' align='left' width='20%'>&nbsp;" . ($warned ? "
                <input type='radio' name='warned' value='yes' checked='checked' />{$lang['form_field_opt_edit_yes']}
                <input type='radio' name='warned' value='no' />{$lang['form_field_opt_edit_no']}" : "{$lang['form_field_opt_edit_no']}") . "
            </td>");

    if ($warned)
    {
        $warneduntil = $user['warneduntil'];

        if ($warneduntil == '0000-00-00 00:00:00')
        {
            print("<td class='rowhead'>{$lang['table_edit_user_warning']}</td></tr>");
        }
        else
        {
            print("<td class='rowhead' align='center'>{$lang['table_edit_user_until']}$warneduntil");
            print(" (" . mkprettytime(strtotime($warneduntil) - gmtime()) . "{$lang['table_edit_user_to_go']})</td></tr>");
        }
    }
    else
    {
        print("<td class='rowhead'>&nbsp;{$lang['form_field_edit_worn_for']}<select name='warnlength'>\n");
        print("<option value='0'>------</option>\n");
        print("<option value='1'>{$lang['form_field_opt_edit_1week']}</option>\n");
        print("<option value='2'>{$lang['form_field_opt_edit_2week']}</option>\n");
        print("<option value='4'>{$lang['form_field_opt_edit_4week']}</option>\n");
        print("<option value='8'>{$lang['form_field_opt_edit_8week']}</option>\n");
        print("<option value='255'>{$lang['form_field_opt_edit_unlimited']}</option>\n");
        print("</select></td></tr>");

        print("<tr>
                <td class='rowhead' align='left' colspan='2'>&nbsp;{$lang['form_field_edit_comment']}:-&nbsp;&nbsp;&nbsp;
                    <input type='text' name='warnpm' size='60' />
                </td>
            </tr>");
    }

    //-- Start Upload Enable / Disable --//
    if ($user['uploadpos'] == "no")
    {
        $uploadposuntil = $user['uploadposuntil'];
        $uploadpos      = $user['uploadpos'];

        print("<tr>
                <td class='colhead'>&nbsp;{$lang['form_field_edit_ul_enabled']}</td>
                <td class='rowhead' align='left' width='20%'>
                    <input type='radio' name='uploadpos' value='yes' " . (!$uploadpos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                    <input type='radio' name='uploadpos' value='no' " . ($uploadpos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
            </td>");

        if ($user['uploadposuntil'] == "0000-00-00 00:00:00")
        {
            print("<td class='rowhead'>&nbsp;<strong>{$lang['table_edit_user_total_ban']}</strong> - {$lang['table_edit_user_advice']}</td></tr>");
        }
        else
        {
            print("<td class='rowhead'>&nbsp;{$lang['table_edit_user_until']}$uploadposuntil");
            print(" (" . mkprettytime(strtotime($uploadposuntil) - gmtime()) . "{$lang['table_edit_user_to_go']})</td></tr>");
        }
    }

    if ($user['uploadpos'] == "yes")
    {
        print("<tr>
                <td class='colhead' rowspan='2'>&nbsp;{$lang['form_field_edit_ul_enabled']}</td>
                <td class='rowhead'>&nbsp;{$lang['form_field_opt_edit_yes']}</td>");

        print("<td class='rowhead'>&nbsp;{$lang['table_edit_user_disable']}:-&nbsp;<select name='uploadposuntillength'>\n");
        print("<option value='0'>------</option>\n");
        print("<option value='1'>{$lang['form_field_opt_edit_1week']}</option>\n");
        print("<option value='2'>{$lang['form_field_opt_edit_2week']}</option>\n");
        print("<option value='4'>{$lang['form_field_opt_edit_4week']}</option>\n");
        print("<option value='8'>{$lang['form_field_opt_edit_8week']}</option>\n");
        print("<option value='255'>{$lang['form_field_opt_edit_unlimited']}</option>\n");
        print("</select></td></tr>");

        print("<tr>
                <td class='rowhead' align='left' colspan='2'>&nbsp;{$lang['form_field_edit_comment']}:-&nbsp;&nbsp;&nbsp;
                    <input type='text' name='uploadposuntilpm' size='60' />
                </td>
            </tr>");
    }
    //-- Finish Upload Enable / Disable --//

    //-- Start Download Enable - Disable --//
    if ($user['downloadpos'] == "no")
    {
        $downloadposuntil = $user['downloadposuntil'];
        $downloadpos      = $user['downloadpos'];

        print("<tr>
                <td class='colhead'>&nbsp;{$lang['form_field_edit_dl_enabled']}</td>
                <td class='rowhead' align='left' width='20%'>
                    <input type='radio' name='downloadpos' value='yes' " . (!$downloadpos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                    <input type='radio' name='downloadpos' value='no' " . ($downloadpos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
                </td>");

        if ($user['downloadposuntil'] == "0000-00-00 00:00:00")
        {
            print("<td class='rowhead'>&nbsp;<strong>{$lang['table_edit_user_total_ban']}</strong> - {$lang['table_edit_user_advice']}</td></tr>");
        }
        else
        {
            print("<td class='rowhead'>&nbsp;{$lang['table_edit_user_until']}$downloadposuntil");
            print(" (" . mkprettytime(strtotime($downloadposuntil) - gmtime()) . "{$lang['table_edit_user_to_go']})</td></tr>");
        }
    }

    if ($user['downloadpos'] == "yes")
    {
        print("<tr>
                <td class='colhead' rowspan='2'>&nbsp;{$lang['form_field_edit_dl_enabled']}</td>
                <td class='rowhead'>&nbsp;{$lang['form_field_opt_edit_yes']}</td>");

        print("<td class='rowhead'>&nbsp;{$lang['table_edit_user_disable']}:-&nbsp;<select name='downloadposuntillength'>\n");
        print("<option value='0'>------</option>\n");
        print("<option value='1'>{$lang['form_field_opt_edit_1week']}</option>\n");
        print("<option value='2'>{$lang['form_field_opt_edit_2week']}</option>\n");
        print("<option value='4'>{$lang['form_field_opt_edit_4week']}</option>\n");
        print("<option value='8'>{$lang['form_field_opt_edit_8week']}</option>\n");
        print("<option value='255'>{$lang['form_field_opt_edit_unlimited']}</option>\n");
        print("</select></td></tr>");

        print("<tr>
                <td class='rowhead' align='left' colspan='2'>&nbsp;{$lang['form_field_edit_comment']}:-&nbsp;&nbsp;&nbsp;
                    <input type='text' name='downloadposuntilpm' size='60' />
                </td>
            </tr>");
    }
    //-- Finish Download Enable - Disable --//

    //-- Start Shoutbox Enable - Disable --//
    if ($user['shoutboxpos'] == "no")
    {
        $shoutboxposuntil = $user['shoutboxposuntil'];
        $shoutboxpos      = $user['shoutboxpos'];

        print("<tr>
                <td class='colhead'>&nbsp;{$lang['form_field_edit_sb_enabled']}</td>
                <td class='rowhead' align='left' width='20%'>
                    <input type='radio' name='shoutboxpos' value='yes' " . (!$shoutboxpos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                    <input type='radio' name='shoutboxpos' value='no' " . ($shoutboxpos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
                </td>");

        if ($user['shoutboxposuntil'] == "0000-00-00 00:00:00")
        {
            print("<td class='rowhead'>&nbsp;<strong>{$lang['table_edit_user_total_ban']}</strong> - {$lang['table_edit_user_advice']}</td></tr>");
        }
        else
        {
            print("<td class='rowhead'>{$lang['table_edit_user_until']}$shoutboxposuntil");
            print(" (" . mkprettytime(strtotime($shoutboxposuntil) - gmtime()) . "{$lang['table_edit_user_to_go']})</td></tr>");
        }
    }

    if ($user['shoutboxpos'] == "yes")
    {
        print("<tr>
                <td class='colhead' rowspan='2'>&nbsp;{$lang['form_field_edit_sb_enabled']}</td>
                <td class='rowhead'>&nbsp;{$lang['form_field_opt_edit_yes']}</td>");

        print("<td class='rowhead'>&nbsp;{$lang['table_edit_user_disable']}:-&nbsp;<select name='shoutboxposuntillength'>\n");
        print("<option value='0'>------</option>\n");
        print("<option value='1'>{$lang['form_field_opt_edit_1week']}</option>\n");
        print("<option value='2'>{$lang['form_field_opt_edit_2week']}</option>\n");
        print("<option value='4'>{$lang['form_field_opt_edit_4week']}</option>\n");
        print("<option value='8'>{$lang['form_field_opt_edit_8week']}</option>\n");
        print("<option value='255'>{$lang['form_field_opt_edit_unlimited']}</option>\n");
        print("</select></td></tr>");

        print("<tr>
                <td class='rowhead' align='left' colspan='2'>&nbsp;{$lang['form_field_edit_comment']}:-&nbsp;&nbsp;&nbsp;
                    <input type='text' name='shoutboxposuntilpm' size='60' />
                </td>
            </tr>");
    }
    //-- Finish Shoutbox Enable - Disable --//

    //-- Start Torrent Comment Enable - Disable --//
    if ($user['torrcompos'] == "no")
    {
        $torrcomposuntil = $user['torrcomposuntil'];
        $torrcompos      = $user['torrcompos'];

        print("<tr>
                <td class='colhead'>&nbsp;{$lang['form_field_edit_com_enabled']}</td>
                <td class='rowhead' align='left' width='20%'>
                    <input type='radio' name='torrcompos' value='yes' " . (!$torrcompos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                    <input type='radio' name='torrcompos' value='no' " . ($torrcompos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
                </td>");

        if ($user['torrcomposuntil'] == "0000-00-00 00:00:00")
        {
            print("<td class='rowhead'>&nbsp;<strong>{$lang['table_edit_user_total_ban']}</strong> - {$lang['table_edit_user_advice']}</td></tr>");
        }
        else
        {
            print("<td class='rowhead'>{$lang['table_edit_user_until']}$torrcomposuntil");
            print(" (" . mkprettytime(strtotime($torrcomposuntil) - gmtime()) . "{$lang['table_edit_user_to_go']})</td></tr>");
        }
    }

    if ($user['torrcompos'] == "yes")
    {
        print("<tr>
                <td class='colhead' rowspan='2'>&nbsp;{$lang['form_field_edit_com_enabled']}</td>
                <td class='rowhead'>&nbsp;{$lang['form_field_opt_edit_yes']}</td>");

        print("<td class='rowhead'>&nbsp;{$lang['table_edit_user_disable']}:-&nbsp;<select name='torrcomposuntillength'>\n");
        print("<option value='0'>------</option>\n");
        print("<option value='1'>{$lang['form_field_opt_edit_1week']}</option>\n");
        print("<option value='2'>{$lang['form_field_opt_edit_2week']}</option>\n");
        print("<option value='4'>{$lang['form_field_opt_edit_4week']}</option>\n");
        print("<option value='8'>{$lang['form_field_opt_edit_8week']}</option>\n");
        print("<option value='255'>{$lang['form_field_opt_edit_unlimited']}</option>\n");
        print("</select></td></tr>");

        print("<tr>
                <td class='rowhead' align='left' colspan='2'>&nbsp;{$lang['form_field_edit_comment']}:-&nbsp;&nbsp;&nbsp;
                    <input type='text' name='torrcomposuntilpm' size='60' />
                </td>
            </tr>");
    }
    //-- Finish Torrent Comment Enable - Disable --//

    //-- Start Offer Comment Enable - Disable --//
if ($user['offercompos'] == "no")
{
     $offercomposuntil = $user['offercomposuntil'];
     $offercompos    = $user['offercompos'];

     print("<tr>
             <td class='colhead'>&nbsp;{$lang['form_field_edit_offer_enabled']}</td>
             <td class='rowhead' align='left' width='20%'>
                <input type='radio' name='offercompos' value='yes' " . (!$offercompos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                <input type='radio' name='offercompos' value='no' " . ($offercompos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
             </td>");

     if ($user['offercomposuntil'] == "0000-00-00 00:00:00")
     {
         print("<td class='rowhead'>&nbsp;<strong>{$lang['table_edit_user_total_ban']}</strong> - {$lang['table_edit_user_advice']}</td></tr>");
     }
     else
     {
         print("<td class='rowhead'>{$lang['table_edit_user_until']}$offercomposuntil");
         print(" (" . mkprettytime(strtotime($offercomposuntil) - gmtime()) . "{$lang['table_edit_user_to_go']})</td></tr>");
     }
}

if ($user['offercompos'] == "yes")
{
     print("<tr>
             <td class='colhead' rowspan='2'>&nbsp;{$lang['form_field_edit_offer_enabled']}</td>
             <td class='rowhead'>&nbsp;{$lang['form_field_opt_edit_yes']}</td>");

     print("<td class='rowhead'>&nbsp;{$lang['table_edit_user_disable']}:-&nbsp;<select name='offercomposuntillength'>\n");
     print("<option value='0'>------</option>\n");
     print("<option value='1'>{$lang['form_field_opt_edit_1week']}</option>\n");
     print("<option value='2'>{$lang['form_field_opt_edit_2week']}</option>\n");
     print("<option value='4'>{$lang['form_field_opt_edit_4week']}</option>\n");
     print("<option value='8'>{$lang['form_field_opt_edit_8week']}</option>\n");
     print("<option value='255'>{$lang['form_field_opt_edit_unlimited']}</option>\n");
     print("</select></td></tr>");

     print("<tr>
             <td class='rowhead' align='left' colspan='2'>&nbsp;{$lang['form_field_edit_comment']}:-&nbsp;&nbsp;&nbsp;
                <input type='text' name='offercomposuntilpm' size='60' />
             </td>
         </tr>");
}
//-- Finish Offer Comment Enable - Disable --//

//-- Start Request Comment Enable - Disable --//
if ($user['requestcompos'] == "no")
{
     $requestcomposuntil = $user['requestcomposuntil'];
     $requestcompos  = $user['requestcompos'];

     print("<tr>
             <td class='colhead'>&nbsp;{$lang['form_field_edit_req_com_enabled']}</td>
             <td class='rowhead' align='left' width='20%'>
                <input type='radio' name='requestcompos' value='yes' " . (!$requestcompos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                <input type='radio' name='requestcompos' value='no' " . ($requestcompos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
             </td>");

     if ($user['requestcomposuntil'] == "0000-00-00 00:00:00")
     {
         print("<td class='rowhead'>&nbsp;<strong>{$lang['table_edit_user_total_ban']}</strong> - {$lang['table_edit_user_advice']}</td></tr>");
     }
     else
     {
         print("<td class='rowhead'>{$lang['table_edit_user_until']}$requestcomposuntil");
         print(" (" . mkprettytime(strtotime($requestcomposuntil) - gmtime()) . "{$lang['table_edit_user_to_go']})</td></tr>");
     }
}

if ($user['requestcompos'] == "yes")
{
     print("<tr>
             <td class='colhead' rowspan='2'>&nbsp;{$lang['form_field_edit_req_com_enabled']}</td>
             <td class='rowhead'>&nbsp;{$lang['form_field_opt_edit_yes']}</td>");

     print("<td class='rowhead'>&nbsp;{$lang['table_edit_user_disable']}:-&nbsp;<select name='requestcomposuntillength'>\n");
     print("<option value='0'>------</option>\n");
     print("<option value='1'>{$lang['form_field_opt_edit_1week']}</option>\n");
     print("<option value='2'>{$lang['form_field_opt_edit_2week']}</option>\n");
     print("<option value='4'>{$lang['form_field_opt_edit_4week']}</option>\n");
     print("<option value='8'>{$lang['form_field_opt_edit_8week']}</option>\n");
     print("<option value='255'>{$lang['form_field_opt_edit_unlimited']}</option>\n");
     print("</select></td></tr>");

     print("<tr>
             <td class='rowhead' align='left' colspan='2'>&nbsp;{$lang['form_field_edit_comment']}:-&nbsp;&nbsp;&nbsp;
                 <input type='text' name='requestcomposuntilpm' size='60' />
             </td>
         </tr>");
}
//-- Finish Request Comment Enable - Disable --//


    //-- Start Forum Enable - Disable --//
    if ($user['forumpos'] == "no")
    {
        $forumposuntil = $user['forumposuntil'];
        $forumpos = $user['forumpos'];

        print("<tr>
                <td class='colhead'>&nbsp;{$lang['form_field_edit_forum_enabled']}</td>
                <td class='rowhead' align='left' width='20%'>
                    <input type='radio' name='forumpos' value='yes' " . (!$forumpos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                    <input type='radio' name='forumpos' value='no' " . ($forumpos ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
                </td>");

        if ($user['forumposuntil'] == "0000-00-00 00:00:00")
        {
            print("<td class='rowhead'>&nbsp;<strong>{$lang['table_edit_user_total_ban']}</strong> - {$lang['table_edit_user_advice']}</td></tr>");
        }
        else
        {
            print("<td class='rowhead'>{$lang['table_edit_user_until']}$forumposuntil");
            print(" (" . mkprettytime(strtotime($forumposuntil) - gmtime()) . "{$lang['table_edit_user_to_go']})</td></tr>");
        }
    }

    if ($user['forumpos'] == "yes")
    {
        print("<tr>
                <td class='colhead' rowspan='2'>&nbsp;{$lang['form_field_edit_forum_enabled']}</td>
                <td class='rowhead'>&nbsp;{$lang['form_field_opt_edit_yes']}</td>");

        print("<td class='rowhead'>&nbsp;{$lang['table_edit_user_disable']}:-&nbsp;<select name='forumposuntillength'>\n");
        print("<option value='0'>------</option>\n");
        print("<option value='1'>{$lang['form_field_opt_edit_1week']}</option>\n");
        print("<option value='2'>{$lang['form_field_opt_edit_2week']}</option>\n");
        print("<option value='4'>{$lang['form_field_opt_edit_4week']}</option>\n");
        print("<option value='8'>{$lang['form_field_opt_edit_8week']}</option>\n");
        print("<option value='255'>{$lang['form_field_opt_edit_unlimited']}</option>\n");
        print("</select></td></tr>");

        print("<tr>
                <td class='rowhead' align='left' colspan='2'>&nbsp;{$lang['form_field_edit_comment']}:-&nbsp;&nbsp;&nbsp;
                    <input type='text' name='forumposuntilpm' size='60' />
                </td>
            </tr>");
    }
    //-- Finish Forum Enable - Disable --//

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_acc_enabled']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='radio' name='enabled' value='yes' " . ($enabled ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                <input type='radio' name='enabled' value='no' " . (!$enabled ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}</td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_acc_parked']}</td>
            <td class='rowhead' colspan='2' align='left'>
                <input type='radio' name='parked' value='yes' " . ($get_user['parked']=='yes' ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_yes']}
                <input type='radio' name='parked' value='no' " . ($get_user['parked']=='no' ? " checked='checked' " : "") . " />{$lang['form_field_opt_edit_no']}
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>&nbsp;{$lang['form_field_edit_passkey']}</td>
            <td class='rowhead' align='left' colspan='2'>
                <input type='checkbox' name='resetpasskey' value='1' />{$lang['form_opt_reset_passkey']}
            </td>
        </tr>");

    print("<tr>
            <td colspan='3' align='center'>
                <input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' />
            </td>
        </tr>");

    print("</table>");
    print("</form>");

    //-- Start Delete Member By Wilba --//
    if (get_user_class() >= UC_SYSOP && $get_user['class'] < get_user_class())
    {
    print("<br />");
    print("<form method='post' action='delete_member.php?&amp;action=deluser'>");
    print("<table class='coltable' width='70%'>");
    print("<tr>
            <td class='rowhead' align='center'>
                <h2>{$lang['table_edit_del_user']}</h2>
            </td>
        </tr>");

    $username = security::html_safe($get_user['username']);

    print("<tr><td class='rowhead' align='center'>");
    print("<input name='username' size='20' value='" . $username . "' type='hidden' />");
    print("<input type='submit' class='btn' value='{$lang['btn_del_user']}\"$username\"' />");
    print("<br />");
    print("</td></tr>");
    print("</table>");
    print("</form>");
    }
    //-- Finish Delete Member By Wilba --//

    print("</div>");
}
//-- Finish Edit User Details Content --//

//-- Start Reset Password Content --//
if (get_user_class() >= UC_SYSOP && $get_user['class'] < get_user_class())
{
    print("<div class='ui-tabs-panel' id='fragment-7'>");
    print("<form method='post' action=''>");
    print("<table class='main' border='0' cellspacing='0' cellpadding='0'>\n" );
    print("<tr><td style='border:none;'>");
    print("<input type='hidden' name='username' value='" . security::html_safe($get_user['username']) . "' size='40' readonly='readonly' />");
    print("<input type='submit' class='btn' value='{$lang['btn_reset']}' />");
    print("</td></tr>");
    print("</table>");
    print("</form>");
    print("</div>");
}
//-- Finish Reset Password Content --//

//-- Start Invite Tree --//
if (get_user_class() >= UC_MODERATOR || $user['id'] == user::$current['id'])
{
    print("<div class='ui-tabs-panel' id='fragment-8'>");

    $query = $db->query("SELECT id, username, uploaded, downloaded, status, warned, enabled, donor
                        FROM users
                        WHERE invitedby = " . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);

    $rows = $query->num_rows;

    print("<table border='1' width='81%' cellspacing='0' cellpadding='5'>");

    print("<tr>
            <td class='colhead' align='center' colspan='7'><strong>{$lang['table_invite_users']}</strong></td>
        </tr>");

    if (!$rows)
    {
        print("<tr>
                <td class='rowhead' align='center' colspan='7'>{$lang['table_invite_none']}</td>
            </tr>");

        print("</table><br />");
    }
    else
    {
        print("<tr>
                <td class='rowhead' align='center'><strong>{$lang['table_invite_username']}</strong></td>
                <td class='rowhead' align='center'><strong>{$lang['table_invite_uploaded']}</strong></td>
                <td class='rowhead' align='center'><strong>{$lang['table_invite_downloaded']}</strong></td>
                <td class='rowhead' align='center'><strong>{$lang['table_invite_ratio']}</strong></td>
                <td class='rowhead' align='center'><strong>{$lang['table_invite_status']}</strong></td>
            </tr>");

        for ($i = 0; $i < $rows; ++$i)
        {
            $arr = $query->fetch_assoc();

            if ($arr['status'] == 'pending')
            {
                $user = "" . security::html_safe($arr['username']) . "";
            }
            else
            {
                $user = "<a href='userdetails.php?id={$arr['id']}'>" . security::html_safe($arr['username']) . "</a>
                " . ($arr['warned'] == "yes" ?"&nbsp;<img src='{$image_dir}warned.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_warned']}' title='{$lang['gbl_img_alt_warned']}' />" : "") . "&nbsp;
                " . ($arr['enabled'] == "no" ?"&nbsp;<img src='{$image_dir}disabled.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_disabled']}' title='{$lang['gbl_img_alt_disabled']}' />" : "") . "&nbsp;
                " . ($arr['donor'] == "yes" ?"<img src='{$image_dir}star.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_donor']}' title='{$lang['gbl_img_alt_donor']}' />" : "") . " ";
            }

            if ($arr['downloaded'] > 0)
            {
                $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
                $ratio = "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
            }
            else
            {
                if ($arr['uploaded'] > 0)
                {
                    $ratio = "{$lang['table_invite_inf']}";
                }
                else
                {
                    $ratio = "---";
                }
            }

            if ($arr['status'] == 'confirmed')
            {
                $status = "<span class='userdetails_inv_confirmed'>{$lang['table_invite_confirmed']}</span>";
            }
            else
            {
                $status = "<span class='userdetails_inv_pending'>{$lang['table_invite_pending']}</span>";
            }

            print("<tr>
                    <td class='rowhead'align='center'>$user</td>
                    <td class='rowhead'align='center'>" . misc::mksize($arr['uploaded']) . "</td>
                    <td class='rowhead'align='center'>" . misc::mksize($arr['downloaded']) . "</td>
                    <td class='rowhead'align='center'>$ratio</td>
                    <td class='rowhead'align='center'>$status</td></tr>");
        }

        print("</table><br />");
    }
        print("</div>");
}
//-- Finish Invite Tree --//

print("</div>");

?>

<script type="text/javascript" src="/js/jquery-1.8.2.js" ></script>
<script type="text/javascript" src="/js/jquery-ui-1.9.0.custom.min.js" ></script>
<script type="text/javascript">
$(document).ready(function()
{
$("#featured").tabs({fx:{opacity: "toggle"}}).tabs("rotate", 5000, true);
});
</script>

<?php

site_footer();

?>