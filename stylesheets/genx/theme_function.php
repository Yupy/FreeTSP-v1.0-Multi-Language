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

$lang = array_merge(load_language('style_genx_theme'));

//-- Start Theme Based Functions --//
function begin_frame ($caption='', $center=false, $padding=10)
{
    $tdextra = "";

    if ($caption)
    {
        print("<h2>$caption</h2>");
    }

    if ($center)
    {
        $tdextra .= " align='center'";
    }

    print("<table border='1' width='100%' cellspacing='0' cellpadding='$padding'><tr><td $tdextra>\n");
}

function end_frame()
{
    print("</td></tr></table>");
}

function begin_table ($fullwidth=false, $padding=5)
{
    $width = "";

    if ($fullwidth)
    {
        $width .= " width='100%'";
    }

    print("<table class='main' border='1' $width cellspacing='0' cellpadding='$padding'>");
}

function end_table()
{
    echo("</table>");
}
//-- Finish Theme Based Functions --//

//-- Start Shoutbox Functions --//
function sb_images()
{
    global $image_dir, $lang, $db;

    $res = $db->query("SELECT s.id, s.userid, s.date, s.text, s.to_user, u.username, u.class, u.donor, u.warned, u.avatar
                       FROM shoutbox AS s
                       LEFT JOIN users AS u ON s.userid = u.id
                       ORDER BY s.date DESC
                       LIMIT 30") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows == 0)
    {
        print ("{$lang['text_no_shouts']}");
    }
    else
    {
        print ("<table class='main_font' border='0' align='left' width='100%' cellspacing='0' cellpadding='2'>");

        while ($arr = $res->fetch_assoc())
        {
/*
            //-- Private Shout Mod --//

            if (($arr['to_user'] != user::$current['id'] && $arr['to_user'] != 0) && $arr['userid'] != user::$current['id'])
            continue;

            elseif
                ($arr['to_user'] == user::$current['id'] || ($arr['userid'] == user::$current['id'] && $arr['to_user'] !=0) )
                $private = "<a href=\"javascript:private_reply('{$arr['username']}')\"><img src='{$image_dir}private-shout.png'  width='16' height='16' border='0' alt='{$lang['img_alt_private']}' title='{$lang['img_alt_private']}{$arr['username']}' style='padding-left : 2px; padding-right : 2px;' /></a>";
            else
                $private = '';*/
            //-- Private Shout Mod End --//

            //-- Original Code Does Not Allow Self Edit Of Posts For Power Users & Above --//
        /*
            $edit = (get_user_class() >= UC_MODERATOR ? "<a href='shoutbox.php?edit={$arr['id']}'><img src='{$image_dir}button_edit2.gif' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' /></a> " : "");
        */

            //-- Power Users & Above Can Edit Their Own Posts - Correction By Fireknight --//
            $edit = (get_user_class() >= UC_MODERATOR || ($arr['userid'] == user::$current['id']) && (user::$current['class'] >= UC_POWER_USER && user::$current['class'] <= UC_MODERATOR) ? "<a href='shoutbox.php?edit={$arr['id']}&amp;user={$arr['userid']}'><img src='{$image_dir}edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' style='vertical-align : bottom;' /></a> " : '');

            $delall = (get_user_class() >= UC_SYSOP ? "<a href='shoutbox.php?delall' onclick=\"confirm_delete(); return false; \"><img src='{$image_dir}delete_all.png' width='16' height='16' border='0' alt='{$lang['img_alt_empty']}' title='{$lang['img_alt_empty']}' style='vertical-align : bottom;' /></a> " : "");

            $del = (get_user_class() >= UC_MODERATOR ? "<a href='shoutbox.php?del={$arr['id']}'><img src='{$image_dir}delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_del']}' title='{$lang['img_alt_del']}' style='vertical-align : bottom;' /></a> " : "");

            $pm = "<a target='_blank' href='sendmessage.php?receiver={$arr['userid']}'><img src='{$image_dir}mail.png' width='16' height='16' border='0' alt='{$lang['img_alt_pm']}' title='{$lang['img_alt_pm']}' style='vertical-align : bottom;'/></a>";

            $avatar = "";

            // Uncomment If You Wish To Have The Members Avatar Shown --//
        /*
            if (!$arr['avatar'])
            {
                $avatar = ("<a target='_blank' href='userdetails.php?id={$arr['userid']}'></a>\n");
            }
            else
            {
                $avatar = ("<a target='_blank' href='userdetails.php?id={$arr['userid']}'><img src='" . security::html_safe($arr['avatar']) . "' width='50' height='50' border='0' alt='' title='' /></a>\n");
            }

        */

        /*
            $private = (get_user_class() >= UC_MODERATOR ? "<a href=\"javascript:private_reply('{$arr['username']}')\"><img src='{$image_dir}private-shout.png' width='16' height='16' border='0' alt='{$lang['img_alt_private1']}' title='{$lang['img_alt_private1']}' /></a>&nbsp;": "");
        */

            $user_stuff       = $arr;
            $user_stuff['id'] = (int)$arr['userid'];
            $datum            = gmdate("d M h:i", $arr['date']);

            print("<tr>
                    <td style='width : 85px; vertical-align : bottom;'>
                        <span class='date'>['$datum']</span>
                    </td>
                    <td>
                        $delall $del $edit $pm $avatar
                        " . format_username($user_stuff) . "
                        " . format_comment($arr['text']) . "
                    </td>
                </tr>");
        }
        print("</table>");
    }
}

function sb_style()
{
    ?>
    <style type='text/css'>

    /*-- Start Main Shout --*/
    body
    {
        background-color : transparent;
    }

    .main_font
    {
        color       : #DADADA;
        font-size   : 9pt;
        font-family : arial;
    }

    a
    {
        color           : #356AA0;
        font-weight     : bold;
        font-size       : 9pt;
        text-decoration : none;
    }

    a:hover
    {
        color : #0B610B;
    }

    .date
    {
        color     : #DADADA;
        font-size : 9pt;
    }

    .error
    {
        color            : #990000;
        background-color : #FFF0F0;
        padding          : 7px;
        margin-top       : 5px;
        margin-bottom    : 10px;
        border           : 1px dashed #990000;
    }
    /*-- Finish Main Shout --*/

    /*-- Start Staff Edit Box --*/
    #staff_specialbox
    {
        border     : 1px solid gray;
        width      : 600px;
        background : #FBFCFA;
        font       : 11px verdana, sans-serif;
        color      : #000000;
        padding    : 3px;
        outline    : none;
    }
    /*-- Finish Staff Edit Box --*/

    /*-- Start Member Edit Box --*/
    #member_specialbox
    {
        border     : 1px solid gray;
        width      : 600px;
        background : #FBFCFA;
        font       : 11px verdana, sans-serif;
        color      : #000000;
        padding    : 3px;
        outline    : none;
    }
    /*-- Finish Member Edit Box --*/

    </style>
    <?php
}
//-- Finish Shoutbox Functions --//

//-- Start Theme Requirements --//

// Stats Begin - Credits Bigjoos
global $Memcache;

    if (($row2 = $Memcache->get_value('theme::genx::stats')) === false) {
	    $stats = $db->query("SELECT *, seeders + leechers AS peers, seeders / leechers AS ratio, unconnectables / (seeders + leechers) AS ratiounconn
                             FROM stats
                             WHERE id = '1'
                             LIMIT 1") or sqlerr(__FILE__, __LINE__);

	    $row2 = $stats->fetch_assoc();
        $Memcache->cache_value('theme::genx::stats', $row2, 300);
    }

    $registered2 = number_format($row2['regusers']);
    $unverified2 = number_format($row2['unconusers']);
    $torrents2   = number_format($row2['torrents']);
    $numactive2  = number_format($row2['numactive']);
    $forumposts2 = number_format($row2['forumposts']);

    $stats_key = 'statusbar::user::stats::' . user::$current['id'];
    if (($statusbar_stats = $Memcache->get_value($stats_key)) === false) {
        $get_stats = $db->query('SELECT uploaded, downloaded, invites FROM users WHERE id = ' . user::$current['id']);
        $statusbar_stats = $get_stats->fetch_assoc();
	    $statusbar_stats['uploaded'] = (float)$statusbar_stats['uploaded'];
	    $statusbar_stats['downloaded'] = (float)$statusbar_stats['downloaded'];
	    $statusbar_stats['invites'] = (int)$statusbar_stats['invites'];
        $Memcache->cache_value($stats_key, $statusbar_stats, 1800);
    }

    $ratio = $statusbar_stats['downloaded'] > 0 ? $statusbar_stats['uploaded'] / $statusbar_stats['downloaded'] : 0;
    $ratio = number_format($ratio, 2);

	if (($unread = $Memcache->get_value('statusbar::pm::count::' . user::$current['id'])) === false) {
        $res1 = $db->query("SELECT COUNT(id)
                            FROM messages
                            WHERE receiver = " . user::$current['id'] . "
                            AND unread = 'yes'") or print($db->error);

        $arr1   = $res1->fetch_row();
        $unread = (int)$arr1[0];
        $Memcache->cache_value('statusbar::pm::count::' . user::$current['id'], $unread, 43200);
    }

    $inbox  = ($unread == 1 ? "$unread&nbsp;{$lang['gbl_table_new_msg']}" : "$unread&nbsp;{$lang['gbl_table_new_msgs']}");

    if (get_user_class() < UC_MODERATOR)
    {
        $usrclass = "&nbsp;" . get_user_class_name(user::$current['class']) . "";
    }

    if (get_user_class() >= UC_MODERATOR)
    {
        $usrclass = "&nbsp;<a href='setclass.php'>" . get_user_class_name(user::$current['class']) . "</a>";
    }

	if ( user::$current['reputation'] >= 1 )
	{
	    $rep = "<img src='stylesheets/genx/images/001_63.png' alt='{$lang['img_alt_rep']}' title='{$lang['img_alt_rep']}' />&nbsp;{$lang['table_rep']}";
	    $reputation = "<span style='color : #4DDB4D'>" . security::html_safe(user::$current['reputation']) . "</span>";
	}
	else
	{
        $rep = "<img src='stylesheets/genx/images/001_62.png' alt='{$lang['img_alt_rep']}' title='{$lang['img_alt_rep']}' />&nbsp;{$lang['table_rep']}";
        $reputation = "<span style='color : #FF0000'>&nbsp;0</span>";
	}

	if ($statusbar_stats['invites'] >= 1)
	{
	    $invites = "<a href='invite.php'><span style='color : #4DDB4D'>" . security::html_safe($statusbar_stats['invites']) . "</span></a>";
	}
	else
	{
        $invites = "<span style='color : #FF0000'>&nbsp;0</span>";
	}

	if (user::$current['donor'] == "yes")
	{
	    $donor = "&nbsp;<span style='color : #4DDB4D'>&nbsp;{$lang['table_yes']}</span>";
	}
	else
	{
	    $donor = "<span style='color : #FF0000'>&nbsp;{$lang['table_no']}</span>";
	}


	$q1 = $db->query("SELECT connectable
                      FROM peers
                      WHERE userid = " . user::$current['id'] . " LIMIT 1") or sqlerr();

	if ($a = $q1->fetch_row())
	{
		$connect = $a[0];

		if ( $connect == "yes" )
		{
			$connectable = "<span style='color : #00ff00'>&nbsp;{$lang['table_yes']}</span>";
		}
		else
		{
			$connectable = "<span style='color : #ff0000'>&nbsp;{$lang['table_no']}</span>";
		}
	}
	else
	{
		$connectable = "<span style='color : #99AD99'>&nbsp;???</span>";
	}

    $nameuser  = "" . format_username(user::$current) . "";
    $menuspace = "&nbsp;&nbsp;&nbsp;&nbsp; |&nbsp;&nbsp;&nbsp;&nbsp;";
//-- Finish Theme Requirements --//

?>