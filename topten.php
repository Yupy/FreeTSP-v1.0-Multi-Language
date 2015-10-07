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

db_connect();
logged_in();

$lang = array_merge(load_language('topten'),
                    load_language('func_bbcode'),
                    load_language('global'));

if (get_user_class() < UC_POWER_USER)
{
    error_message_center("warn",
                         "{$lang['gbl_warning']}",
                         "{$lang['err_perm_denied']}");
}

function usertable($res, $frame_caption)
{
    global $lang, $db;

    begin_frame($frame_caption, true);
    begin_table();

echo ("<tr>
        <td class='colhead'>{$lang['table_rank']}</td>
        <td class='colhead' align='left'>{$lang['table_user']}</td>
        <td class='colhead'>{$lang['table_uploaded']}</td>
        <td class='colhead' align='left'>{$lang['table_up_speed']}</td>
        <td class='colhead'>{$lang['table_download']}</td>
        <td class='colhead' align='left'>{$lang['table_dl_speed']}</td>
        <td class='colhead' align='right'>{$lang['table_ratio']}</td>
        <td class='colhead' align='left'>{$lang['table_joined']}</td>
    </tr>");

    $num = 0;

    while ($a = $res->fetch_assoc())
    {
        ++$num;

        $highlight = user::$current['id'] == $a['userid'] ? " bgcolor='#BBAF9B'" : "";

        if ($a['downloaded'])
        {
            $ratio = $a['uploaded'] / $a['downloaded'];
            $color = get_ratio_color($ratio);
            $ratio = number_format($ratio, 2);

            if ($color)
            {
                $ratio = "<span style='color : $color'>$ratio</span>";
            }
        }
        else
        {
            $ratio = "Inf.";
        }

        echo ("<tr $highlight>
                <td class='rowhead' align='center'>$num</td>
                <td class='rowhead' align='left' $highlight>
                    <a href='userdetails.php?id={$a['userid']}'><span style='font-weight : bold;'>" . security::html_safe($a['username']) . "</span></a>"."
                </td>
                <td class='rowhead' align='right'$highlight>" . misc::mksize($a['uploaded']) . "</td>
                <td class='rowhead' align='right' $highlight>" . misc::mksize($a['upspeed']) . "/s" . "</td>
                <td class='rowhead' align='right '$highlight>" . misc::mksize($a['downloaded']) . "</td>
                <td class='rowhead' align='right '$highlight>" . misc::mksize($a['downspeed']) . "/s" . "</td>
                <td class='rowhead' align='right' $highlight>$ratio</td>
                <td class='rowhead' align='left'>" . gmdate("Y-m-d", strtotime($a['added'])) . " (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($a['added'])) . "{$lang['table_ago']})</td>
            </tr>");
    }

    end_table();
    end_frame();
}

function _torrenttable($res, $frame_caption)
{
    global $lang, $db;

    begin_frame($frame_caption, true);
    begin_table();

echo ("<tr>
        <td class='colhead' align='center'>{$lang['table_rank']}</td>
        <td class='colhead' align='left'>{$lang['table_name']}</td>
        <td class='colhead' align='right'>{$lang['table_snatched']}</td>
        <td class='colhead' align='right'>{$lang['table_data']}</td>
        <td class='colhead' align='right'>{$lang['table_seeds']}</td>
        <td class='colhead' align='right'>{$lang['table_leech']}</td>
        <td class='colhead' align='right'>{$lang['table_total']}</td>
        <td class='colhead' align='right'>{$lang['table_ratio']}</td>
    </tr>");

    $num = 0;

    while ($a = $res->fetch_assoc())
    {
        ++$num;

        if ($a['leechers'])
        {
            $r     = $a['seeders'] / $a['leechers'];
            $ratio = "<font color='" . get_ratio_color($r) . "'>" . number_format($r, 2) . "</font>";
        }
        else
        {
            $ratio = "Inf.";
        }
        echo ("<tr>
                <td class='rowhead' align='center'>$num</td>
                <td class='rowhead' align='left'>
                    <a href='details.php?id={$a['id']}&amp;hit=1'><span style='font-weight : bold;'>" . security::html_safe($a['name']) . "</span></a>
                </td>
                <td class='rowhead' align='right'>" . number_format($a['times_completed']) . "</td>
                <td class='rowhead' align='right'>" . misc::mksize($a['data']) . "</td>
                <td align='right'>" . number_format($a['seeders']) . "</td>
                <td class='rowhead' align='right'>" . number_format($a['leechers']) . "</td>
                <td align='right'>" . ($a['leechers'] + $a['seeders']) . "</td>
                <td class='rowhead' align='right'>$ratio</td>
            </tr>\n");
    }
    end_table();
    end_frame();
}

function countriestable($res, $frame_caption, $what)
{
    global $image_dir, $lang, $db;

    begin_frame($frame_caption, true);
    begin_table();

echo ("<tr>
        <td class='colhead'>{$lang['table_rank']}</td>
        <td class='colhead' align='left'>{$lang['table_country']}</td>
        <td class='colhead' align='right'>$what</td>
    </tr>");

    $num = 0;

    while ($a = $res->fetch_assoc())
    {
        ++$num;

        if ($what == "{$lang['table_users']}")
        {
            $value = number_format($a['num']);
        }

        elseif ($what == "{$lang['table_uploaded']}")
        {
            $value = misc::mksize($a['ul']);
        }

        elseif ($what == "{$lang['table_average']}")
        {
            $value = misc::mksize($a['ul_avg']);
        }

        elseif ($what == "{$lang['table_ratio']}")
        {
            $value = number_format($a['r'], 2);
        }

        echo ("<tr>
                <td class='rowhead' align='center'>$num</td>
                <td class='rowhead' align='left'>
                    <table class='main' border='0' cellspacing='0' cellpadding='0'>
                        <tr>
                            <td class='embedded'>
                                <img style='text-align : center;' src='{$image_dir}flag/{$a['flagpic']}' width='32' height='20' alt='" . security::html_safe($a['name']) . "' title='" . security::html_safe($a['name']) . "' />
                            </td>
                            <td class='embedded' style='padding-left : 5px'>" . security::html_safe($a['name']) . "</td>
                        </tr>
                    </table>
                </td>
                <td class='rowhead' align='right'>$value</td>
            </tr>\n");
    }

    end_table();
    end_frame();

}

site_header("{$lang['title_topten']}", false);

echo ("<br /><br />
<div style='text-align : center;'>
    <span style='font-size : small;'>{$lang['title_welcome']}<br />
        <span style='font-weight : bold;'>$site_name.</span>
        <br />{$lang['title_topten_menu']}<br />
    </span>
</div>
<br /><br />

<table width='81%' cellpadding='4'>
    <tr>
        <td class='std' align='center'>
            <div id='featured'><br />
                <div style='text-align : center; text-decoration : underline; font-weight : bold;'>{$lang['table_members']}</div>
                <br />
                <ul>
                    <li><a href='#fragment-0'></a></li>
                    <li><a class='btn' href='#fragment-1'>{$lang['table_uploaders']}</a></li>
                    <li><a class='btn' href='#fragment-2'>{$lang['table_fast_uploaders']}</a></li>
                    <li><a class='btn' href='#fragment-3'>{$lang['table_downloaders']}</a></li>
                    <li><a class='btn' href='#fragment-4'>{$lang['table_fast_downloaders']}</a></li>
                    <li><a class='btn' href='#fragment-5'>{$lang['table_best_sharers']}</a></li>
                    <li><a class='btn' href='#fragment-6'>{$lang['table_worst_sharers']}</a>

                        <br /><br />

                        <div style='text-align : center; text-decoration : underline; font-weight : bold;'>{$lang['table_torrents']}</div>
                        <br />
                    </li>

                    <li><a class='btn' href='#fragment-7'>{$lang['table_most_active']}</a></li>
                    <li><a class='btn' href='#fragment-8'>{$lang['table_most_snatched']}</a></li>
                    <li><a class='btn' href='#fragment-9'>{$lang['table_most_data_xfer']}</a></li>
                    <li><a class='btn' href='#fragment-10'>{$lang['table_best_seeded']}</a></li>
                    <li><a class='btn' href='#fragment-11'>{$lang['table_worst_seeded']}</a>

                        <br /><br />

                        <div style='text-align : center; text-decoration : underline; font-weight : bold;'>{$lang['table_countries']}</div>
                        <br />
                    </li>

                    <li><a class='btn' href='#fragment-12'>{$lang['table_members']}</a></li>
                    <li><a class='btn' href='#fragment-13'>{$lang['table_total_uploaded']}</a></li>
                    <li><a class='btn' href='#fragment-14'>{$lang['table_avg_total_uploaded']}</a></li>
                    <li><a class='btn' href='#fragment-15'>{$lang['table_ratio']}</a></li>
                </ul>

                <div class='ui-tabs-panel' id='fragment-1'>

                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_uploaders']}</td>
                        </tr>
                    </table>
                    <br />");

                    $pu = get_user_class() >= UC_POWER_USER;

                    if (!$pu)
                    {
                        $limit = 10;
                    }

                    $mainquery = "SELECT id AS userid, username, added, uploaded, downloaded, uploaded / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS upspeed, downloaded / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS downspeed
                                  FROM users
                                  WHERE enabled = 'yes'";

                    $limit   = 10;
                    $subtype = "";

                    if ($limit == 10 || $subtype == "ul")
                    {
                        $order = "uploaded DESC";
                        $r = $db->query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr();

                        usertable($r, "" . ($limit == 10 && $pu ? "" : ""));
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-2'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_fast_uploaders']}</td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "uls")
                    {
                        $order = "upspeed DESC";
                        $r     = $db->query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr();

                        usertable($r, "" . ($limit == 10 && $pu ? "" : ""));
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-3'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_downloaders']}</td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                        if ($limit == 10 || $subtype == "dl")
                        {
                            $order = "downloaded DESC";
                            $r     = $db->query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr();

                            usertable($r, "" . ($limit == 10 && $pu ? "" : ""));
                        }

                echo ("</div>
                    <div class='ui-tabs-panel' id='fragment-4'>
                        <table width='81%' cellpadding='4'>
                            <tr>
                                <td class='colhead' align='center'>{$lang['table_topten_fast_downloaders']}</td>
                            </tr>
                        </table>
                        <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "dls")
                    {
                        $order = "downspeed DESC";
                        $r     = $db->query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr();

                        usertable($r, "".($limit == 10 && $pu ? "" : ""));
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-5'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_best_sharers']}</td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "bsh")
                    {
                        $order      = "uploaded / downloaded DESC";
                        $extrawhere = " and downloaded > 1073741824";

                        $r = $db->query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr();

                        usertable($r, "" . ($limit == 10 && $pu ? "" : ""));
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-6'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_worst_sharers']}</td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "wsh")
                    {
                        $order      = "uploaded / downloaded ASC, downloaded DESC";
                        $extrawhere = " and downloaded > 1073741824";

                        $r = $db->query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr();

                        usertable($r, "" . ($limit == 10 && $pu ? "" : ""));
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-7'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_most_active']}</td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "act")
                    {
                        $r = $db->query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data
                                        FROM torrents AS t
                                        LEFT JOIN peers AS p ON t.id = p.torrent
                                        WHERE p.seeder = 'no'
                                        GROUP BY t.id
                                        ORDER BY seeders + leechers DESC, seeders DESC, added ASC
                                        LIMIT $limit") or sqlerr();

                        _torrenttable($r, "" . ($limit == 10 && $pu ? "" : ""));
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-8'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_most_snatched']}</td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "sna")
                    {
                        $r = $db->query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data
                                        FROM torrents AS t
                                        LEFT JOIN peers AS p ON t.id = p.torrent
                                        WHERE p.seeder = 'no'
                                        GROUP BY t.id
                                        ORDER BY times_completed DESC
                                        LIMIT $limit") or sqlerr();

                        _torrenttable($r, "" . ($limit == 10 && $pu ? "" : ""));
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-9'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_most_xfered']}s</td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "mdt")
                    {
                        $r = $db->query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data
                                        FROM torrents AS t
                                        LEFT JOIN peers AS p ON t.id = p.torrent
                                        WHERE p.seeder = 'no' and leechers >= 5 AND times_completed > 0
                                        GROUP BY t.id
                                        ORDER BY data DESC, added ASC
                                        LIMIT $limit") or sqlerr();

                        _torrenttable($r, "" . ($limit == 10 && $pu ? "" : ""));
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-10'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_best_seeded']}</td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "bse")
                    {
                        $r = $db->query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data
                                        FROM torrents AS t
                                        LEFT JOIN peers AS p ON t.id = p.torrent
                                        WHERE p.seeder = 'no'
                                        AND seeders >= 5
                                        GROUP BY t.id
                                        ORDER BY seeders / leechers DESC, seeders DESC, added ASC
                                        LIMIT $limit") or sqlerr();

                        _torrenttable($r, "" . ($limit == 10 && $pu ? "" : ""));
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-11'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_worst_seeded']}
                            </td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "wse")
                    {
                        $r = $db->query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data
                                        FROM torrents AS t
                                        LEFT JOIN peers AS p ON t.id = p.torrent
                                        WHERE p.seeder = 'no'
                                        AND leechers >= 5
                                        AND times_completed > 0
                                        GROUP BY t.id
                                        ORDER BY seeders / leechers ASC, leechers DESC
                                        LIMIT $limit") or sqlerr();

                        _torrenttable($r, "" . ($limit == 10 && $pu ? "" : ""));
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-12'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_country_user']}</td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "us")
                    {
                        $r = $db->query("SELECT name, flagpic, COUNT(users.country) AS num
                                        FROM countries
                                        LEFT JOIN users ON users.country = countries.id
                                        GROUP BY name
                                        ORDER BY num DESC
                                        LIMIT $limit") or sqlerr();

                        countriestable($r, "" . ($limit == 10 && $pu ? "" : ""), "{$lang['table_topten_users']}");
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-13'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_country_upload']}</td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "ul")
                    {
                        $r = $db->query("SELECT c.name, c.flagpic, sum(u.uploaded) AS ul
                                        FROM users AS u
                                        LEFT JOIN countries AS c ON u.country = c.id
                                        WHERE u.enabled = 'yes'
                                        GROUP BY c.name
                                        ORDER BY ul DESC
                                        LIMIT $limit") or sqlerr();

                        countriestable($r, "" . ($limit == 10 && $pu ? "" : ""), "{$lang['table_topten_uploaders']}");
                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-14'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_country_avg']}
                            </td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "avg")
                    {
                        $r = $db->query("SELECT c.name, c.flagpic, sum(u.uploaded)/count(u.id) AS ul_avg FROM users AS u
                                        LEFT JOIN countries AS c ON u.country = c.id
                                        WHERE u.enabled = 'yes'
                                        GROUP BY c.name HAVING sum(u.uploaded) > 1099511627776
                                        AND count(u.id) >= 100
                                        ORDER BY ul_avg DESC
                                        LIMIT $limit") or sqlerr();

                        countriestable($r, "" . ($limit == 10 && $pu ? "" : ""), "{$lang['table_topten_avg']}");

                    }

                echo ("</div>
                <div class='ui-tabs-panel' id='fragment-15'>
                    <table width='81%' cellpadding='4'>
                        <tr>
                            <td class='colhead' align='center'>{$lang['table_topten_country_ratio']}
                            </td>
                        </tr>
                    </table>
                    <br />");

                    $limit = 10;

                    if ($limit == 10 || $subtype == "r")
                    {
                        $r = $db->query("SELECT c.name, c.flagpic, sum(u.uploaded)/sum(u.downloaded) AS r
                                        FROM users AS u
                                        LEFT JOIN countries AS c ON u.country = c.id
                                        WHERE u.enabled = 'yes'
                                        GROUP BY c.name HAVING sum(u.uploaded) > 1099511627776
                                        AND sum(u.downloaded) > 1099511627776
                                        AND count(u.id) >= 100
                                        ORDER BY r DESC
                                        LIMIT $limit") or sqlerr();

                        countriestable($r, "" . ($limit == 10 && $pu ? "" : ""), "{$lang['table_topten_ratio']}");
                    }

                echo ("</div>
                <br />
            </div>
        </td>
    </tr>
</table>");

?>
<script type="text/javascript" src="js/jquery-1.8.2.js" ></script>
<script type="text/javascript" src="js/jquery-ui-1.9.0.custom.min.js" ></script>

<script type="text/javascript">
    $(document).ready(function()
    {
        $("#featured").tabs({fx:{opacity: "toggle"}}).tabs("rotate", 5000, true);
    });
</script>

<?php

site_footer();

?>