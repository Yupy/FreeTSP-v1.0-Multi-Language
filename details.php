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
require_once(FUNC_DIR . 'function_commenttable.php');

function ratingpic($num)
{
    global $image_dir, $lang;

    $r = round($num);

    //-- Comment Out The Above And Uncomment The Below If You Want More Flexibility --//
    /*$rn = ($num);

    //-- Round Down To Nearest Number --//
    if ($rn >= 1 && $rn <= 1.4 ||
        $rn >= 2 && $rn <= 2.4 ||
        $rn >= 3 && $rn <= 3.4 ||
        $rn >= 4 && $rn <= 4.4)
    {
        $r = floor($num);
    }

    //-- Round Up To Nearest Number --//
    if ($rn >= 1.5 && $rn <= 1.9 ||
        $rn >= 2.5 && $rn <= 2.9 ||
        $rn >= 3.5 && $rn <= 3.9 ||
        $rn >= 4.5 && $rn <= 5)
    {
        $r = ceil($num);
    }*/

    if ($r < 1 || $r > 5)
    {
        return;
    }

    return "<img src='{$image_dir}ratings/{$r}.png' width='auto' height='26' border='0' alt='{$lang['img_alt_rating']}: $num / 5' title='{$lang['img_alt_rating']}: $num /5' />";
}

function getagent($httpagent = '', $peer_id = '')
{
    global $lang;

    return ($httpagent ? $httpagent : ($peer_id ? $peer_id : $lang['text_unknown']));
}

function dltable($name, $arr, $torrent)
{
    global $moderator, $revived, $lang, $Memcache, $db;

    $s = "<span style='font-weight : bold;'>" . count($arr) . " $name</span>\n";

    if (!count($arr))
    {
        return $s;
    }

    $s .= "\n";
    $s .= "<table class='main' border='1' width='100%' cellspacing='0' cellpadding='5'>\n";
    $s .= "<tr>
            <td class='colhead'>{$lang['table_user']}</td>
            <td class='colhead' align='center'>{$lang['table_connectable']}</td>
            <td class='colhead' align='right'>{$lang['table_uploaded']}</td>
            <td class='colhead' align='center'>{$lang['table_rate']}</td>
            <td class='colhead' align='right'>{$lang['table_downloaded']}</td>
            <td class='colhead' align='center'>{$lang['table_rate']}</td>
            <td class='colhead' align='center'>{$lang['table_ratio']}</td>
            <td class='colhead' align='right'>{$lang['table_complete']}</td>
            <td class='colhead' align='right'>{$lang['table_connected']}</td>
            <td class='colhead' align='center'>{$lang['table_idle']}</td>
            <td class='colhead' align='left'>{$lang['table_client']}</td>
        </tr>\n";

    $now       = vars::$timestamp;
    $moderator = (isset(user::$current) && get_user_class() >= UC_MODERATOR);
    $mod       = get_user_class() >= UC_MODERATOR;

    foreach ($arr
             AS
             $e)
    {
        //-- User, IP, Port - Check If Anyone Has This IP --//
		$key = 'details::dltable::user::stuff::' . $e['userid'];
		if (($una = $Memcache->get_value($key)) === false) {
            ($unr = $db->query("SELECT id, class, username, privacy, donor, warned, enabled
                                FROM users
                                WHERE id = " . (int)$e['userid'] . "
                                ORDER BY last_access DESC
                                LIMIT 1")) or sqlerr(__FILE__, __LINE__);

            $una = $unr->fetch_assoc();
            $Memcache->cache_value($key, $una, 14400);
        }

        if ($una['privacy'] == 'strong')
        {
            continue;
        }

        $highlight = user::$current['id'] == $una['id'] ? " class='sticky'" : '';
        $s .= "<tr $highlight>\n";

        if ($una['username'])
        {
            if (get_user_class() >= UC_MODERATOR || $torrent['anonymous'] != 'yes' || $e['userid'] != $torrent['owner'])
            {
                $s .= "<td class='rowhead'>" . format_username($una) . "</td>\n";
            }
            elseif (get_user_class() >= UC_MODERATOR || $torrent['anonymous'] = 'yes')
            {
                $s .= "<td><em>{$lang['text_anon']}</em></td>\n";
            }
        }
        else
        {
            $s .= "<td class='rowhead'>" . ($mod ? $e['ip'] : preg_replace('/\.\d+$/', ".xxx", $e['ip'])) . "</td>\n";
        }

        $secs    = max(1, (int)($now - $e['st']) - ($now - $e['la']));
        $revived = $e['revived'] == 'yes';

        $s .= "<td class='rowhead' align='center'>" . ($e['connectable'] == 'yes' ? "<span class='connectable'>{$lang['text_yes']}</span>" : "<span class='unconnectable'>{$lang['text_no']}</span>") . "</td>\n";

        $s .= "<td class='rowhead' align='right'>" . misc::mksize($e['uploaded']) . "</td>\n";

        $s .= "<td class='rowhead' align='center'><span style='white-space : nowrap;'>" . misc::mksize(($e['uploaded'] - $e['uploadoffset']) / $secs) . "/s</span></td>\n";

        $s .= "<td class='rowhead' align='right'>" . misc::mksize($e['downloaded']) . "</td>\n";

        if ($e['seeder'] == 'no')
        {
            $s .= "<td class='rowhead' align='center'><span style='white-space: nowrap;'>" . misc::mksize(($e['downloaded'] - $e['downloadoffset']) / $secs) . "/s</span></td>\n";
        }
        else
        {
            $s .= "<td class='rowhead' align='center'><span style='white-space: nowrap;'>" . misc::mksize(($e['downloaded'] - $e['downloadoffset']) / max(1, (int)$e['finishedat'] - $e['st'])) . "/s</span></td>\n";
        }

        if ($e['downloaded'])
        {
            $ratio = floor(($e['uploaded'] / $e['downloaded']) * 1000) / 1000;
            $s .= "<td class='rowhead' align='right'><span style='color : " . get_ratio_color($ratio) . "'>" . number_format($ratio, 3) . "</span></td>\n";
        }
        else {
            if ($e['uploaded'])
            {
                $s .= "<td class='rowhead' align='center'>{$lang['text_no']}</td>\n";
            }
            else
            {
                $s .= "<td class='rowhead' align='center'>---</td>\n";
            }
        }
        $s .= "<td class='rowhead' align='right'>" . sprintf("%.2f%%", 100 * (1 - ($e['to_go'] / $torrent['size']))) . "</td>\n";
        $s .= "<td class='rowhead' align='right'>" . mkprettytime($now - $e['st']) . "</td>\n";
        $s .= "<td class='rowhead' align='center'>" . mkprettytime($now - $e['la']) . "</td>\n";
        $s .= "<td class='rowhead' align='left'>" . security::html_safe(getagent($e['agent'])) . "</td>\n";
        $s .= "</tr>\n";
    }

    $s .= "</table>\n";

    return $s;
}

db_connect(false);
logged_in();

$lang = array_merge(load_language('details'),
                    load_language('func_vfunctions'),
                    load_language('func_bbcode'),
                    load_language('global'));

parked();

$id    = intval(0 + $_GET['id']);
$added = sqlesc(get_date_time());

if (!isset($id) || !$id)
{
    die();
}

$res = $db->query("SELECT torrents.seeders, torrents.leechers, LENGTH(torrents.nfo) AS nfosz, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(torrents.last_action) AS lastseed, torrents.numratings, IF(torrents.numratings < $min_votes, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.owner, torrents.comments, torrents.save_as, torrents.visible, torrents.size, torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.numfiles, torrents.anonymous, categories.name AS cat_name, users.username
                FROM torrents
                LEFT JOIN categories ON torrents.category = categories.id
                LEFT JOIN users ON torrents.owner = users.id
                WHERE torrents.id = $id") or sqlerr();

$row = $res->fetch_assoc();

if (($torrent_details = $Memcache->get_value('torrent::details::' . $id)) === false) {
    $tdetails = $db->query('SELECT banned, freeleech, info_hash, filename, name, descr, poster FROM torrents WHERE id = ' . $id);
    $torrent_details = $tdetails->fetch_assoc();
    $Memcache->cache_value('torrent::details::' . $id, $torrent_details, 43200);
}

$owned = $moderator = 0;

if (get_user_class() >= UC_MODERATOR)
{
    $owned = $moderator = 1;
}

elseif (user::$current['id'] == $row['owner'])
{
    $owned = 1;
}

if (!$row || ($torrent_details['banned'] == 'yes' && !$moderator))
{
    error_message("error",
                  "{$lang['gbl_error']}",
                  "{$lang['err_no_torr_id']}");
}
else
{
    if ($_GET['hit'])
    {
        $db->query("UPDATE torrents
                   SET views = views + 1
                   WHERE id = $id");

        if ($_GET['tocomm'])
        {
            header("Location: $site_url/details.php?id=$id&page=0#startcomments");
        }

        elseif ($_GET['filelist'])
        {
            header("Location: $site_url/details.php?id=$id&filelist=1#filelist");
        }

        elseif ($_GET['toseeders'])
        {
            header("Location: $site_url/details.php?id=$id&dllist=1#seeders");
        }

        elseif ($_GET['todlers'])
        {
            header("Location: $site_url/details.php?id=$id&dllist=1#leechers");
        }

        else
        {
            header("Location: $site_url/details.php?id=$id");
        }
        exit();
    }

    if (!isset($_GET['page']))
    {
        site_header("{$lang['title_details']}" . security::html_safe($torrent_details['name']) . '', false);

        if (user::$current['id'] == $row['owner'] || get_user_class() >= UC_MODERATOR)
        {
            $owned = 1;
        }
        else
        {
            $owned = 0;
        }

        $spacer = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

        if ($_GET['uploaded'])
        {
            echo display_message("success",
                                 "{$lang['text_success']}",
                                 "{$lang['text_auto_down']}{$lang['text_note']}{$lang['text_start_seed']}");

            echo("<meta http-equiv='refresh' content='1;url=download.php/$id/" . rawurlencode($torrent_details['filename']) . "'/>");
        }
        elseif ($_GET['edited'])
        {
            echo display_message("success",
                                 "{$lang['gbl_success']}",
                                 "{$lang['text_success_edit']}");

            if (isset($_GET['returnto']))
            {
                print("<p><span style='font-weight : bold;'>{$lang['text_return']}<a href='" . security::html_safe("{$site_url}/{$_GET['returnto']}") . "'></a></span></p>\n");
            }
        }
        elseif (isset($_GET['searched']))
        {
            print("<h2>{$lang['text_search']}'" . security::html_safe($_GET['searched']) . "'{$lang['text_result']}</h2>\n");
        }
        elseif ($_GET['rated'])
        {
            $redirectid = (int)$row['id'];

            echo error_message("success",
                               "{$lang['gbl_success']}",
                               "{$lang['text_add_rating']}<a href='/details.php?id=$redirectid'>{$lang['text_click_here']}</a>{$lang['text_go_back']}");
        }

        $s = security::html_safe($torrent_details['name']);

        print("<h1>" . $s . "</h1>\n");

        $url = "edit.php?id={$row['id']}";

        if (isset($_GET['returnto']))
        {
            $addthis = "&amp;returnto=" . urlencode($_GET['returnto']);
            $url     .= $addthis;
            $keepget .= $addthis;
        }

        $editlink = "a href='$url' class='btn'";

        global $waittime, $max_class_wait, $ratio_1, $ratio_2, $ratio_3, $ratio_4, $gigs_1, $gigs_2, $gigs_3, $gigs_4, $wait_1, $wait_2, $wait_3, $wait_4;

        if (user::$current['class'] <= $max_class_wait)
        {
            $gigs = user::$current['uploaded'] / (1024*1024*1024);
            $ratio = ((user::$current['downloaded'] > 0) ? (user::$current['uploaded'] / user::$current['downloaded']) : 0);

            if ($ratio < $ratio_1 || $gigs < $gigs_1)
            {
                $wait = $wait_1;
            }
            elseif ($ratio < $ratio_2 || $gigs < $gigs_2)
            {
                $wait = $wait_2;
            }
            elseif ($ratio < $ratio_3 || $gigs < $gigs_3)
            {
                $wait = $wait_3;
            }
            elseif ($ratio < $ratio_4 || $gigs < $gigs_4)
            {
                $wait = $wait_4;
            }
            else
            {
                $wait = 0;
            }
        }

        $elapsed = floor((gmtime() - strtotime($row['added'])) / 3600);

        if (($elapsed < $wait) && ($waittime == 'true'))
        {
            print("<span class='waittime'><strong>{$lang['text_wait']}<br />{$lang['text_has']}" . number_format($wait - $elapsed) . "{$lang['text_hrs_remain']}</strong></span><br /><br />");
        }

        elseif (user::$current['downloadpos'] == 'no')
        {
            print("<span class='downloadpos'><strong>{$lang['text_download_removed']}<br />{$lang['text_contact_staff']}</strong></span><br/><br />");
        }
        else
        {
            print("<p align='center'>
                   <a class='main' href='download.php/$id/" .rawurlencode($torrent_details['filename']) . "'>
                   <img src='{$image_dir}download1.png' width='184' height='55' border='0' alt='{$lang['img_alt_download']}' title='{$lang['img_alt_download']}' />
                   </a></p>");
        }

        print("<table border='1' width='100%' cellspacing='0' cellpadding='5'>\n");

        function hex_esc($matches)
        {
            return sprintf("%02x", ord($matches[0]));
        }

        echo("<tr>
                <td class='detail' width='20%'>{$lang['table_hash']}</td>
                <td class='rowhead'>" . preg_replace_callback('/./s', "hex_esc", hash_pad($torrent_details['info_hash'])) . "</td>
            </tr>");

        $downl = (user::$current['downloaded'] + $row['size']);
        $sr    = user::$current['uploaded'] / $downl;

        switch (true)
        {
            case ($sr >= 4):
                $s = "bigsmile";
                break;

            case ($sr >= 2):
                $s = "smitten";
                break;

            case ($sr >= 1):
                $s = "grin";
                break;

            case ($sr >= 0.5):
                $s = "bashful";
                break;

            case ($sr >= 0.25):
                $s = "chuckle";
                break;

                case ($sr > 0.00):
                $s = "winktongue";
                break;

            default;
                $s = "smug";
                break;
        }

        $sr = floor($sr * 1000) / 1000;
        $sr = "<font color='" . get_ratio_color($sr) . "'>" . number_format($sr, 3) .
              "</font>&nbsp;&nbsp;<img src='{$image_dir}smilies/{$s}.png' width='16' height='16' alt='$s' title='$s' />";

        echo("<tr>
                <td class='detail'>{$lang['table_ratio_after']}</td>
                <td class='rowhead'>{$sr}&nbsp;&nbsp;{$lang['table_ratio_download']}</td>
            </tr>");

        if (!empty($torrent_details['poster']))
        {
            echo("<tr>
                    <td class='detail'>
                        <a href='" . $torrent_details['poster'] . "' rel='lightbox'><img src='" . security::html_safe($torrent_details['poster']) . "' width='200' height='auto' border='0' align='left' alt='{$lang['img_alt_poster']}' title='{$lang['img_alt_poster']}' /></a>
                    </td>
                    <td valign='top'>" . str_replace(array("\n",
                                            "  "), array("\n",
                                                         "&nbsp; "), format_comment(security::html_safe($torrent_details['descr']))) . "</td>
                </tr>");
        }
        else
        {
            echo("<tr>
                    <td class='detail'>
                        <a href='{$image_dir}poster.png' rel='lightbox'><img src='{$image_dir}poster.png' width='200' height='auto' border='0' align='left' alt='{$lang['img_alt_poster']}' title='{$lang['img_alt_poster']}' /></a>
                    </td>
                    <td valign='top'>" . str_replace(array("\n",
                                            "  "), array("\n",
                                                         "&nbsp; "), format_comment(security::html_safe($torrent_details['descr']))) . "</td>
                         </tr>");
        }

        if (get_user_class() >= UC_POWER_USER && $row['nfosz'] > 0)
        {
            echo("<tr>
                    <td class='detail'>{$lang['table_nfo']}</td>
                    <td class='rowhead'>
                        <a href='viewnfo.php?id={$row['id']}'><span style='font-weight : bold;'>{$lang['table_view_nfo']}</span></a>
                        (" . misc::mksize($row['nfosz']) . ")
                    </td>
                </tr>");
        }

        if ($row['visible'] == 'no')
        {
            echo("<tr>
                    <td class='detail'>{$lang['table_visible']}</td>
                    <td class='rowhead'>
                        <span style='font-weight : bold;'>{$lang['text_no']}</span>{$lang['table_dead']}
                    </td>
                </tr>");
        }

        if ($moderator)
        {
            echo("<tr>
                    <td class='detail'>{$lang['table_banned']}</td>
                    <td class='rowhead'>" . $torrent_details['banned'] . "</td>
                </tr>");
        }

        if ($torrent_details['freeleech'] == 'yes')
        {
            echo("<tr>
                    <td class='detail'>{$lang['table_freeleech']}</td>
                    <td class='rowhead'>" . $torrent_details['freeleech'] . "</td>
                </tr>");
        }

        if (isset($row['cat_name']))
        {
            echo("<tr>
                    <td class='detail'>{$lang['table_type']}</td>
                    <td class='rowhead'>{$row['cat_name']}</td>
                </tr>");
        }
        else
        {
            echo("<tr>
                    <td class='detail'>{$lang['table_type']}</td>
                    <td class='rowhead'>{$lang['table_non_select']}</td>
                </tr>");
        }

        echo("<tr>
                <td class='detail'>{$lang['table_last_seeder']}</td>
                <td class='rowhead'>{$lang['table_last_activity']}" . mkprettytime($row['lastseed']) . "{$lang['table_ago']}</td>
            </tr>");

        echo("<tr>
                <td class='detail'>{$lang['table_size']}</td>
                <td class='rowhead'>" . misc::mksize($row['size']) . " (" . number_format($row['size']) . "{$lang['table_bytes']})</td>
            </tr>");

        $s = "";
        $s .= "<table border='0' cellpadding='0' cellspacing='0'><tr><td valign='top' class='embedded'>";

        if (!isset($row['rating']))
        {
            if ($min_votes > 1)
            {
                $s .= "{$lang['table_non_yet']}$min_votes{$lang['table_votes_received']}";

                if ($row['numratings'])
                {
                    $s .= "{$lang['table_only']}" . $row['numratings'];
                }
                else
                {
                    $s .= "{$lang['table_none']}";
                }
                $s .= ")";
            }
            else
            {
                $s .= "{$lang['table_no_votes']}";
            }
        }
        else
        {
            $rpic = ratingpic($row['rating']);

            if (!isset($rpic))
            {
                $s .= "{$lang['table_invalid']}";
            }
            else
            {
                $s .= "$rpic ({$row['rating']}{$lang['table_out_of_five']}{$row['numratings']}{$lang['table_votes_total']})";
            }
        }
        $s .= "\n";
        $s .= "</td><td class='embedded'>$spacer</td><td valign='top' class='embedded'>";

        if (!isset(user::$current))
        {
            $s .= "(<a href='login.php?returnto=" . urlencode(substr($_SERVER['REQUEST_URI'], 1)) . "&amp;nowarn=1'>{$lang['table_log_in']}</a>{$lang['table_rate_torrent']})";
        }
        else
        {
            $ratings = array(5 => "{$lang['table_vote_great']}",
                             4 => "{$lang['table_vote_good']}",
                             3 => "{$lang['table_vote_decent']}",
                             2 => "{$lang['table_vote_bad']}",
                             1 => "{$lang['table_vote_terrible']}",);

            if (!$owned || $moderator)
            {
				if (($xrow = $Memcache->get_value('torrent::details::rating::' . $id)) === false) {
                    $xres = $db->query("SELECT rating, added
                                        FROM ratings
                                        WHERE torrent = " . $id . "
                                        AND user = " . user::$current['id']);

                    $xrow = $xres->fetch_assoc();
                    $Memcache->add_value('torrent::details::rating::' . $id, $xrow, 7200);
                }

                if ($xrow)
                {
                    $s .= "({$lang['table_you_rated']}'{$xrow['rating']} - " . $ratings[$xrow['rating']] . "')";
                }
                else
                {
                    $s .= "<form method='post' action='takerate.php'><input type='hidden' name='id' value='$id' />\n";
                    $s .= "<select name='rating'>\n";
                    $s .= "<option value='0'>{$lang['table_add_rating']}</option>\n";

                    foreach ($ratings
                             AS
                             $k => $v)
                    {
                        $s .= "<option value='$k'>$k - $v</option>\n";
                    }

                    $s .= "</select>\n";
                    $s .= "<input type='submit' class='btn' value='{$lang['btn_vote']}' />";
                    $s .= "</form>\n";
                }
            }
        }
        $s .= "</td></tr></table>";

        echo("<tr>
                <td class='detail'>{$lang['table_rating']}</td>
                <td class='rowhead'>$s</td>
            </tr>");

        echo("<tr>
                <td class='detail'>{$lang['table_added']}</td>
                <td class='rowhead'>{$row['added']}</td>
            </tr>");

        echo("<tr>
                <td class='detail'>{$lang['table_views']}</td>
                <td class='rowhead'>{$row['views']}</td>
            </tr>");

        echo("<tr>
                <td class='detail'>{$lang['table_hits']}</td>
                <td class='rowhead'>{$row['hits']}</td>
            </tr>");


        echo("<tr>
                <td class='detail'>{$lang['table_snatched']}</td>
                <td class='rowhead'>" . ($row['times_completed'] > 0 ? "<a href='snatches.php?id=$id'>" . $row['times_completed'] . "{$lang['table_times']}</a>" : "{$lang['table_zero_times']}") . "</td>

            </tr>");

        $keepget = '';

        if ($row['anonymous'] == 'yes')
        {
            if (get_user_class() < UC_UPLOADER)
                $uprow = "<em>{$lang['text_anon']}</em>";
            else
                $uprow = "<em>{$lang['text_anon']}</em> (<a href='userdetails.php?id={$row['owner']}'><strong>{$row['username']}</strong></a>)";
        }
        else
        {
            $uprow = (isset($row['username']) ? ("<a href='userdetails.php?id={$row['owner']}'><strong>" . security::html_safe($row['username']) . "</strong></a>") : "<em>{$lang['table_unknown']}</em>");
        }

        if ($owned)
        {
            $uprow .= " $spacer<$editlink>{$lang['table_edit_torrent']}</a>";
        }

        echo("<tr>
                <td class='detail'>{$lang['table_upped_by']}</td>
                <td class='rowhead'>" . $uprow . "</td>
            </tr>");

        if ($row['type'] == 'multi')
        {
            if (!$_GET['filelist'])
            {
                echo("<tr>
                        <td class='detail'>{$lang['table_num_files']}<br />
                            <a href='details.php?id=$id&amp;filelist=1$keepget#filelist' class='sublink'>{$lang['table_show_list']}</a>
                        </td>
                        <td class='rowhead'>{$row['numfiles']}{$lang['table_files']}</td>
                    </tr>");
            }
            else
            {
                echo("<tr>
                        <td class='detail'>{$lang['table_num_files']}</td>
                        <td class='rowhead'>{$row['numfiles']}{$lang['table_files']}</td>
                    </tr>");

                $s = "<table class='main' border='1' cellspacing='0' cellpadding='5'>\n";

                $subres = $db->query("SELECT *
                                     FROM files
                                     WHERE torrent = $id
                                     ORDER BY id");

                $s .= "<tr><td class='colhead'>{$lang['table_path']}</td><td class='colhead' align='right'>{$lang['table_size']}</td></tr>\n";

                while ($subrow = $subres->fetch_assoc())
                {
                    $s .= "<tr><td class='detail'>" . security::html_safe($subrow['filename']) . "</td><td class='rowhead' align='right'>" . misc::mksize($subrow['size']) . "</td></tr>\n";
                }

                $s .= "</table>\n";

                echo("<tr>
                        <td class='detail'>
                            <a name='filelist'>{$lang['table_file_list']}</a><br />
                            <a href='details.php?id=$id$keepget' class='sublink'>{$lang['table_hide_list']}</a>
                        </td>
                        <td class='rowhead'>" . $s . "</td>
                    </tr>");
            }
        }

        if (!$_GET['dllist'])
        {
            echo("<tr>
                    <td class='detail'>{$lang['table_peers']}<br />
                        <a href='details.php?id=$id&amp;dllist=1$keepget#seeders' class='sublink'>{$lang['table_show_list']}</a>
                    </td>
                    <td class='rowhead'>{$row['seeders']} seeder(s), {$row['leechers']} leecher(s) = {$row['seeders']} + {$row['leechers']}{$lang['table_peers_total']}</td>
                </tr>");
        }
        else
        {
            $downloaders = array();
            $seeders     = array();

            $subres = $db->query("SELECT seeder, finishedat, downloadoffset, uploadoffset, ip, port, uploaded, downloaded, to_go, UNIX_TIMESTAMP(started) AS st, connectable, agent, UNIX_TIMESTAMP(last_action) AS la, userid
                                 FROM peers
                                 WHERE torrent = $id") or sqlerr();

            while ($subrow = $subres->fetch_assoc())
            {
                if ($subrow['seeder'] == 'yes')
                {
                    $seeders[] = $subrow;
                }
                else
                {
                    $downloaders[] = $subrow;
                }
            }

            function leech_sort($a, $b)
            {
                if (isset($_GET['usort']))
                {
                    return seed_sort($a, $b);
                }

                $x = $a['to_go'];
                $y = $b['to_go'];

                if ($x == $y)
                {
                    return 0;
                }

                if ($x < $y)
                {
                    return -1;
                }

                return 1;
            }

            function seed_sort($a, $b)
            {
                $x = $a['uploaded'];
                $y = $b['uploaded'];

                if ($x == $y)
                {
                    return 0;
                }

                if ($x < $y)
                {
                    return 1;
                }

                return -1;
            }

            usort($seeders, "seed_sort");
            usort($downloaders, "leech_sort");

            echo("<tr>
                    <td class='detail'>
                        <a name='seeders'>{$lang['table_seeders']}</a><br />
                        <a href='details.php?id=$id$keepget' class='sublink'>{$lang['table_hide_list']}</a>
                    </td>
                    <td class='rowhead'>" . dltable("{$lang['table_seeders1']}", $seeders, $row) . "</td>
                </tr>");


            echo("<tr>
                    <td class='detail'>
                        <a name='leechers'>{$lang['table_leechers']}</a><br />
                        <a href='details.php?id=$id$keepget' class='sublink'>{$lang['table_hide_list']}</a>
                    </td>
                    <td class='rowhead'>" . dltable("{$lang['table_leechers1']}", $downloaders, $row) . "</td>
                </tr>");
        }

        $rt = $db->query("SELECT th.userid,u.username,u.class, u.donor, u.warned, u.id, u.enabled
                         FROM thanks AS th INNER JOIN users AS u ON u.id = th.userid
                         WHERE th.torrentid=" . $id . " ORDER BY u.class DESC") or sqlerr();
        $ids = array();

        if ($rt->num_rows > 0)
        {
            $list = '';
            $i    = 0;

            while ($ar = $rt->fetch_assoc())
            {
                $ids[] = (int)$ar['userid'];
                $list .= "" . format_username($ar) . "" . (($rt->num_rows - 1) == $i ? "" : "") . "";
                ++$i;
            }

            echo("<tr>
                    <td class='detail' width='20%'>{$lang['table_thanks_list']}</td>
                    <td class='rowhead' width='80%'>$list</td>
                </tr>");
        }
        else
        {
            $list ="&nbsp;{$lang['table_non_yet']}";

            if (user::$current['id'] != $row['owner'] && !in_array(user::$current['id'], $ids))
            {
                echo("<tr>
                        <td class='detail'>{$lang['table_thanks_list']}</td>
                        <td class='rowhead'>$list<form method='post' action='thanks.php'>
                            <input type='submit' name='submit' class='btn' value='{$lang['btn_thanks']}' />
                            <input type='hidden' name='torrentid' value='$id' />
                    </form></td></tr>");
            }
        }

        if ($row['owner'] != user::$current['id'])
        {
            echo("<tr>
                    <td class='detail'>{$lang['table_report']}</td>
                    <td class='rowhead'>
                        <form method='post' action='report.php?type=Torrent&amp;id=$id'>
                            <input type='submit' class='btn' name='submit' value='{$lang['btn_report_torrent']}' />
                            &nbsp;&nbsp;{$lang['table_breaking']}<a href='rules.php'>{$lang['table_rules']}</a>
                        </form>
                    </td>
            </tr>");
        }

        print("</table>\n");
    }
    else
    {
        site_header("{$lang['title_comments_torrent']}" . security::html_safe($torrent_details['name']), false);

        print("<h1>{$lang['title_comments']}<a href='details.php?id=$id'>" . security::html_safe($torrent_details['name']) . "</a></h1>\n");
    }

    print("<p><a name='startcomments'></a></p>\n");

    if (user::$current['torrcompos'] == 'no')
    {
        $commentbar = "<p align='center'><a class='btn'>{$lang['btn_comment_disabled']}</a></p>\n";
    }
    else
    {
        $commentbar = "<p align='center'><a class='btn' href='comment.php?action=add&amp;tid=$id'>{$lang['btn_add_comment']}</a></p>\n";
    }

    $count = (int)$row['comments'];

    if (!$count)
    {
        echo display_message("info",
                             "{$lang['gbl_info']}",
                             "{$lang['text_no_comments']}");
    }
    else
    {
        list($pagertop, $pagerbottom, $limit) = pager(5, $count, "details.php?id=$id&amp;", array(lastpagedefault => 1));

        $subres = $db->query("SELECT comments.id, text, user, comments.added, editedby, editedat, avatar, warned, username, title, class, donor, enabled
                             FROM comments
                             LEFT JOIN users ON comments.user = users.id
                             WHERE torrent = $id
                             ORDER BY comments.id $limit") or sqlerr(__FILE__, __LINE__);

        $allrows = array();

        while ($subrow = $subres->fetch_assoc())
        {
            $allrows[] = $subrow;
        }

        print($commentbar);
        print($pagertop);

        commenttable($allrows);

        print($pagerbottom);
    }

    print($commentbar);
}

site_footer();

?>