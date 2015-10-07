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
    global $image_dir, $db;

    $lang = array_merge(load_language('style_darkx_theme'));

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
                    <td style='width : 85px;vertical-align : bottom;'>
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
        color       : #FFFFFF;
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
        color     : #FFFFFF;
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

function StatusBar()
{
    global $image_dir, $site_reputation, $lang, $Memcache, $db;

    if (!user::$current)
    {
        return '';
    }

	$stats_key = 'statusbar::user::stats::' . user::$current['id'];
	if (($statusbar_stats = $Memcache->get_value($stats_key)) === false) {
        $get_stats = $db->query('SELECT uploaded, downloaded, invites FROM users WHERE id = ' . user::$current['id']);
        $statusbar_stats = $get_stats->fetch_assoc();
		$statusbar_stats['uploaded'] = (float)$statusbar_stats['uploaded'];
		$statusbar_stats['downloaded'] = (float)$statusbar_stats['downloaded'];
		$statusbar_stats['invites'] = (int)$statusbar_stats['invites'];
        $Memcache->cache_value($stats_key, $statusbar_stats, 1800);
    }

    $upped   = misc::mksize($statusbar_stats['uploaded']);
    $downed  = misc::mksize($statusbar_stats['downloaded']);
    $ratio   = $statusbar_stats['downloaded'] > 0 ? $statusbar_stats['uploaded'] / $statusbar_stats['downloaded'] : 0;
    $ratio   = number_format($ratio, 2);

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

    $res2 = $db->query("SELECT seeder, COUNT(id) AS pCount
                        FROM peers
                        WHERE userid = " . user::$current['id'] . "
                        GROUP BY seeder") or print($db->error);

    $seedleech = array('yes' => '0',
                       'no'  => '0');

    while ($row = $res2->fetch_assoc())
    {
        if ($row['seeder'] == 'yes')
        {
            $seedleech['yes'] = (int)$row['pCount'];
        }
        else
        {
            $seedleech['no'] = (int)$row['pCount'];
        }
    }

    //-- Start Temp Demote By Retro 3 of 3 --//
    $usrclass = '';

    if (user::$current['override_class'] != 255)
    {
        $usrclass = "&nbsp;<strong>(" . get_user_class_name(user::$current['class']) . ")</strong>&nbsp;";
    }

    if (get_user_class() >= UC_MODERATOR)
    {
        $usrclass = "&nbsp;<a href='setclass.php'><strong>(" . get_user_class_name(user::$current['class']) . ")</strong></a>&nbsp;";
    }

    $StatusBar = '';

    if ($site_reputation == true)
    {
         $StatusBar = "<tr>" . "<td class='status'>" . "<div id='statusbar'>"."<p class='home'>{$lang['gbl_table_welcome']}" . format_username(user::$current) . "&nbsp; $usrclass&nbsp;[<a href='logout.php'>{$lang['gbl_table_logout']}</a>]{$lang['gbl_table_invites']}: <a href='invite.php'>" . security::html_safe($statusbar_stats['invites']) . "</a>{$lang['gbl_table_rep']}: " . user::$current['reputation'] . "</p>" . "<p>" . date(DATE_RFC822) . "</p><p>";
    }
    else
    {
         $StatusBar = "<tr>" . "<td class='status'>" . "<div id='statusbar'>" . "<p class='home'>{$lang['gbl_table_welcome']}" . format_username(user::$current) . "&nbsp; $usrclass&nbsp;[<a href='logout.php'>{$lang['gbl_table_logout']}</a>]{$lang['gbl_table_invites']}: <a href='invite.php'>" . security::html_safe($statusbar_stats['invites']) . "</a></p>" . "<p>" . date(DATE_RFC822) . "</p><p>";
    }
    //-- Finish Temp Demote By Retro 3 of 3 --//

    $StatusBar .= "" . "</p><p class='home'>{$lang['gbl_table_ratio']}:$ratio" . "&nbsp;&nbsp;{$lang['gbl_table_uploaded']}:$upped"."&nbsp;&nbsp;{$lang['gbl_table_downloaded']}:$downed" . "&nbsp;&nbsp;{$lang['gbl_table_active']}:&nbsp;<img src='{$image_dir}up.png' width='9' height='7' border='0' alt='{$lang['gbl_img_alt_seeding']}' title='{$lang['gbl_img_alt_seeding']}' />&nbsp;{$seedleech['yes']}" . "&nbsp;&nbsp;<img src='{$image_dir}dl.png' width='9' height='7' border='0' alt='{$lang['gbl_img_alt_leeching']}' title='{$lang['gbl_img_alt_leeching']}' />&nbsp;{$seedleech['no']}</p>";

    $StatusBar .= "<p>" . "<a href='messages.php'>$inbox</a>" . "</p></div></td></tr></table>";

    return $StatusBar;
}

function Dropmenu()
{
    $lang = array_merge(load_language('style_darkx_theme'));
?>
    <table class='mainouter' width='100%' border='0' cellspacing='0' cellpadding='10'>
        <tr>
            <td align='center' class='std' style='padding-left: 1%; padding-right: 1%'>
                <div class='navigation'>
                    <ul class='stn-menu TSP'>

                        <li><a href='index.php'><?php echo $lang['table_home']?></a></li>

                        <li class='hasSubNav hasArrow'><a href='javascript:'><?php echo $lang['table_torrents']?></a>
                            <span class='arrow'></span>
                            <ul>
                                <li><a href='browse.php'><?php echo $lang['table_browse']?></a></li>
                                <li><a href='search.php'><?php echo $lang['table_search']?></a></li>
                                <li><a href='upload.php'><?php echo $lang['table_upload']?></a></li>
                                <li><a href='offers.php'><?php echo $lang['table_offers']?></a></li>
                                <li><a href='requests.php'><?php echo $lang['table_requests']?></a></li>
                                <li><a href='mytorrents.php'><?php echo $lang['table_my_torrents']?></a></li>
                            </ul>
                        </li>

                        <li class='hasSubNav hasArrow'><a href='javascript:'><?php echo $lang['table_usercp']?></a>
                            <span class='arrow'></span>
                            <ul>
                                <li><a href='usercp.php?action=avatar'><?php echo $lang['table_avatar']?></a></li>
                                <li><a href='usercp.php?action=signature'><?php echo $lang['table_signature']?></a></li>
                                <li><a href='usercp.php'><?php echo $lang['table_messages']?></a></li>
                                <li><a href='usercp.php?action=security'><?php echo $lang['table_security']?></a></li>
                                <li><a href='usercp.php?action=torrents'><?php echo $lang['table_torrents']?></a></li>
                                <li><a href='usercp.php?action=personal'><?php echo $lang['table_personal']?></a></li>
                                <li><a href='logout.php'><?php echo $lang['table_logout']?></a></li>
                            </ul>
                        </li>

                        <li><a href='forums.php'><?php echo $lang['table_forums']?></a></li>

                        <li class='hasSubNav hasArrow'><a href='javascript:'><?php echo $lang['table_info']?></a>
                            <span class='arrow'></span>
                            <ul>
                                <li><a href='rules.php'><?php echo $lang['table_rules']?></a></li>
                                <li><a href='faq.php'><?php echo $lang['table_faq']?></a></li>
                                <li><a href='topten.php'><?php echo $lang['table_topten']?></a></li>
                                <li><a href='links.php'><?php echo $lang['table_links']?></a></li>
                                <li><a href='credits.php'><?php echo $lang['table_credits']?></a></li>
                            </ul>
                        </li>

                        <li><a href='helpdesk.php'><?php echo $lang['table_helpdesk']?></a></li>
                        <li><a href='staff.php'><?php echo $lang['table_staff']?></a></li>

                        <?php if (get_user_class() >= UC_MODERATOR) { ?>
                        <li><a href='controlpanel.php'><?php echo $lang['table_tools']?></a></li>
                        <?php }?>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
<?php
}

function Stdmenu()
{
    $lang = array_merge(load_language('style_darkx_theme'));
?>
    <table class='mainouter' width='100%' border='0' cellspacing='0' cellpadding='10'>
        <tr>
            <td align='center' class='navigation'><a href='/index.php'><?php echo $lang['table_home']?></a></td>
            <td align='center' class='navigation'><a href='/browse.php'><?php echo $lang['table_browse']?></a></td>
            <td align='center' class='navigation'><a href='/offers.php'><?php echo $lang['table_offers']?></a></td>
            <td align='center' class='navigation'><a href='/requests.php'><?php echo $lang['table_requests']?></a></td>
            <td align='center' class='navigation'><a href='/search.php'><?php echo $lang['table_search']?></a></td>
            <td align='center' class='navigation'><a href='/upload.php'><?php echo $lang['table_upload']?></a></td>
            <td align='center' class='navigation'><a href='/altusercp.php'><?php echo $lang['table_usercp']?></a></td>
            <td align='center' class='navigation'><a href='/forums.php'><?php echo $lang['table_forums']?></a></td>
            <td align='center' class='navigation'><a href='/topten.php'><?php echo $lang['table_topten']?></a></td>
            <td align='center' class='navigation'><a href='/rules.php'><?php echo $lang['table_rules']?></a></td>
            <td align='center' class='navigation'><a href='/faq.php'><?php echo $lang['table_faq']?></a></td>
            <td align='center' class='navigation'><a href='/links.php'><?php echo $lang['table_links']?></a></td>
            <td align='center' class='navigation'><a href='/credits.php'><?php echo $lang['table_credits']?></a></td>
            <td align='center' class='navigation'><a href='/helpdesk.php'><?php echo $lang['table_helpdesk']?></a></td>
            <td align='center' class='navigation'><a href='/staff.php'><?php echo $lang['table_staff']?></a></td>

            <?php if (get_user_class() >= UC_MODERATOR) { ?>
            <td align='center' class='navigation'><a href='/controlpanel.php'><?php echo $lang['table_tools']?></a></td>
            <?php } ?>
        </tr>
    </table>
<?php
}

?>