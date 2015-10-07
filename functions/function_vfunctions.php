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

function pager($rpp, $count, $href, $opts = array())
{
    global $lang;

    $pages = ceil($count / $rpp);

    if (!$opts['lastpagedefault'])
    {
        $pagedefault = 0;
    }
    else
    {
        $pagedefault = floor(($count - 1) / $rpp);

        if ($pagedefault < 0)
        {
            $pagedefault = 0;
        }
    }

    if (isset($_GET['page']))
    {
        $page = intval(0 + $_GET['page']);

        if ($page < 0)
        {
            $page = $pagedefault;
        }
    }
    else
    {
        $page = $pagedefault;
    }
    $pager = "";
    $mp    = $pages - 1;
    $as    = "<span style='font-weight : bold;'>&lt;&lt;&nbsp;{$lang['text_prev']}</span>";

    if ($page >= 1)
    {
        $pager .= "<a href='{$href}page=" . ($page - 1) . "'>";
        $pager .= $as;
        $pager .= "</a>";
    }
    else
    {
        $pager .= $as;
    }

    $pager .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $as    = "<span style='font-weight : bold;'>{$lang['text_next']}&nbsp;&gt;&gt;</span>";

    if ($page < $mp && $mp >= 0)
    {
        $pager .= "<a href='{$href}page=" . ($page + 1) . "'>";
        $pager .= $as;
        $pager .= "</a>";
    }
    else
    {
        $pager .= $as;
    }

    if ($count)
    {
        $pagerarr    = array();
        $dotted      = 0;
        $dotspace    = 3;
        $dotend      = $pages - $dotspace;
        $curdotend   = $page - $dotspace;
        $curdotstart = $page + $dotspace;

        for ($i = 0;
             $i < $pages;
             $i ++)
        {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend))
            {
                if (!$dotted)
                {
                    $pagerarr[] = "...";
                }

                $dotted = 1;

                continue;
            }
            $dotted = 0;
            $start  = $i * $rpp + 1;
            $end    = $start + $rpp - 1;

            if ($end > $count)
            {
                $end = $count;
            }

            $text = "$start&nbsp;-&nbsp;$end";

            if ($i != $page)
            {
                $pagerarr[] = "<a href='{$href}&amp;page=$i'><span style='font-weight : bold;'>$text</span></a>";
            }
            else
            {
                $pagerarr[] = "<span style='font-weight : bold;'>$text</span>";
            }
        }
        $pagerstr    = join(" | ", $pagerarr);
        $pagertop    = "<p align='center'>$pager<br />$pagerstr</p>\n";
        $pagerbottom = "<p align='center'>$pagerstr<br />$pager</p>\n";
    }
    else
    {
        $pagertop    = "<p align='center'>$pager</p>\n";
        $pagerbottom = $pagertop;
    }

    $start = $page * $rpp;

    return array($pagertop,
                 $pagerbottom,
                 "LIMIT $start, $rpp");
}

function write_log($text)
{
	global $db;
	
    $text  = sqlesc($text);
    $added = sqlesc(get_date_time());

    $db->query("INSERT INTO sitelog (added, txt)
               VALUES ($added, $text)") or sqlerr(__FILE__, __LINE__);
}

function write_stafflog($text)
{
	global $db;
	
    $text  = sqlesc($text);
    $added = sqlesc(get_date_time());

    $db->query("INSERT INTO stafflog (added, txt)
               VALUES ($added, $text)") or sqlerr(__FILE__, __LINE__);
}

function searchfield($s)
{
    return preg_replace(array('/[^a-z0-9]/si',
                              '/^\s*/s',
                              '/\s*$/s',
                              '/\s+/s'), array("    ",
                              "",
                              "",
                              " "), $s);
}

function hash_pad($hash)
{
    return str_pad($hash, 20);
}

function hash_where($name, $hash)
{
    $shhash = preg_replace('/ *$/s', "", $hash);

    return "($name = " . sqlesc($hash) . " or $name = " . sqlesc($shhash) . ")";
}

function error_message($type, $heading, $message)
{
    site_header("", false);

    echo("<table class='main' border='0' width='100%' cellpadding='0' cellspacing='0'><tr><td class='embedded'>");

    if ($heading)
    {
        echo("<div class='notice notice-$type'><h2>$heading</h2>\n");
    }

    echo("<p>" . $message . "</p><span></span></div>");
    echo("</td></tr></table>");

    site_footer();

    die;
}

function error_message_center($type, $heading, $message)
{
    site_header("", false);

    echo("<table class='main' border='0' width='100%' cellpadding='0' cellspacing='0'><tr><td class='embedded'>");

    if ($heading)
    {
        echo("<div class='notice notice-$type' align='center'><h2>$heading</h2>\n");
    }

    echo("<p>" . $message . "</p><span></span></div>");
    echo("</td></tr></table>");

    site_footer();

    die;
}

function display_message($type, $heading, $message)
{
    if ($heading)
    {
        echo("<div class='notice notice-$type' align='left'><h2>$heading</h2>\n");
    }

    echo("<p>" . $message . "</p><span></span></div>");
}

function display_message_center($type, $heading, $message)
{
    if ($heading)
    {
        echo("<div class='notice notice-$type' align='center'><h2>$heading</h2>\n");
    }

    echo("<p>" . $message . "</p><span></span></div>");
}

function sql_timestamp_to_unix_timestamp($s)
{
    global $lang;

    return mktime(substr($s, 11, 2),
                  substr($s, 14, 2),
                  substr($s, 17, 2),
                  substr($s, 5, 2),
                  substr($s, 8, 2),
                  substr($s, 0, 4));
}

function get_elapsed_time($ts)
{
    global $lang;

    $mins  = floor((gmtime() - $ts) / 60);
    $hours = floor($mins / 60);
    $mins -= $hours * 60;
    $days = floor($hours / 24);
    $hours -= $days * 24;
    $weeks = floor($days / 7);
    $days -= $weeks * 7;
    $t = "";

    if ($weeks > 0)
    {
        return "$weeks{$lang['text_week']}" . ($weeks > 1 ? "{$lang['text_post_s']}" : "");
    }

    if ($days > 0)
    {
        return "$days{$lang['text_day']}" . ($days > 1 ? "{$lang['text_post_s']}" : "");
    }

    if ($hours > 0)
    {
        return "$hours{$lang['text_hour']}" . ($hours > 1 ? "{$lang['text_post_s']}" : "");
    }

    if ($mins > 0)
    {
        return "$mins{$lang['text_min']}" . ($mins > 1 ? "{$lang['text_post_s']}" : "");
    }
    return "< 1{$lang['text_min']}";
}

function time_return($stamp)
{
    global $lang;

    $ysecs  = 365 * 24 * 60 * 60;
    $mosecs = 31 * 24 * 60 * 60;
    $wsecs  = 7 * 24 * 60 * 60;
    $dsecs  = 24 * 60 * 60;
    $hsecs  = 60 * 60;
    $msecs  = 60;

    $years = floor($stamp / $ysecs);
    $stamp %= $ysecs;
    $months = floor($stamp / $mosecs);
    $stamp %= $mosecs;
    $weeks = floor($stamp / $wsecs);
    $stamp %= $wsecs;
    $days = floor($stamp / $dsecs);
    $stamp %= $dsecs;
    $hours = floor($stamp / $hsecs);
    $stamp %= $hsecs;
    $minutes = floor($stamp / $msecs);
    $stamp %= $msecs;
    $seconds = $stamp;

    if ($years == 1)
    {
        $nicetime['years'] = "1 {$lang['text_year']}";
    }
    elseif ($years > 1)
    {
        $nicetime['years'] = $years . "{$lang['text_year']}{$lang['text_post_s']}";
    }

    if ($months == 1)
    {
        $nicetime['months'] = "1 {$lang['text_month']}";
    }
    elseif ($months > 1)
    {
        $nicetime['months'] = $months . " {$lang['text_month']}{$lang['text_post_s']}";
    }

    if ($weeks == 1)
    {
        $nicetime['weeks'] = "1 {$lang['text_week']}";
    }
    elseif ($weeks > 1)
    {
        $nicetime['weeks'] = $weeks . " {$lang['text_week']}{$lang['text_post_s']}";
    }

    if ($days == 1)
    {
        $nicetime['days'] = "1 {$lang['text_day']}";
    }
    elseif ($days > 1)
    {
        $nicetime['days'] = $days . " {$lang['text_day']}{$lang['text_post_s']}";
    }

    if ($hours == 1)
    {
        $nicetime['hours'] = "1 {$lang['text_hour']}";
    }
    elseif ($hours > 1)
    {
        $nicetime['hours'] = $hours . " {$lang['text_hour']}{$lang['text_post_s']}";
    }

    if ($minutes == 1)
    {
        $nicetime['minutes'] = "1 {$lang['text_minute']}";
    }
    elseif ($minutes > 1)
    {
        $nicetime['minutes'] = $minutes . " {$lang['text_minute']}{$lang['text_post_s']}";
    }

    if ($seconds == 1)
    {
        $nicetime['seconds'] = "1 {$lang['text_second']}";
    }
    elseif ($seconds > 1)
    {
        $nicetime['seconds'] = $seconds . " {$lang['text_second']}{$lang['text_post_s']}";
    }

    if (is_array($nicetime))
    {
        return implode(", ", $nicetime);
    }
}

function failedloginscheck()
{
    global $maxloginattempts, $lang, $db;

    $total = 0;
    $ip    = sqlesc(vars::$ip);

    $Query = $db->query("SELECT SUM(attempts)
                        FROM loginattempts
                        WHERE ip = $ip") or sqlerr(__FILE__, __LINE__);

    list($total) = $Query->fetch_array(MYSQLI_BOTH);

    if ($total >= $maxloginattempts)
    {
        $db->query("UPDATE loginattempts
                    SET banned = 'yes'
                    WHERE ip = $ip") or sqlerr(__FILE__, __LINE__);

        error_message_center("error",
                             "{$lang['err_login_locked']}",
                             "{$lang['text_you_have']}(" . htmlspecialchars($ip) . "){$lang['text_banned']}", false);
    }
}

function remaining()
{
    global $maxloginattempts, $lang, $db;

    $total = 0;
    $ip    = sqlesc(vars::$ip);

    $Query = $db->query("SELECT SUM(attempts)
                        FROM loginattempts
                        WHERE ip = $ip") or sqlerr(__FILE__, __LINE__);

    list($total) = $Query->fetch_array(MYSQLI_BOTH);
    $remaining = $maxloginattempts - $total;

    if ($remaining <= 2)
    {
        $remaining = "<span class='remaining_red'>" . $remaining . "</span>";
    }
    else
    {
        $remaining = "<span class='remaining_green'>" . $remaining . "</span>";
    }

    return $remaining;
}

?>