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

function get_ratio_color($ratio)
{
    if ($ratio < 0.1)
    {
        return "#FF0000";
    }
    if ($ratio < 0.2)
    {
        return "#EE0000";
    }
    if ($ratio < 0.3)
    {
        return "#DD0000";
    }
    if ($ratio < 0.4)
    {
        return "#CC0000";
    }
    if ($ratio < 0.5)
    {
        return "#BB0000";
    }
    if ($ratio < 0.6)
    {
        return "#AA0000";
    }
    if ($ratio < 0.7)
    {
        return "#990000";
    }
    if ($ratio < 0.8)
    {
        return "#880000";
    }
    if ($ratio < 0.9)
    {
        return "#770000";
    }
    if ($ratio < 1)
    {
        return "#660000";
    }
    return "#009900";
}

function get_slr_color($ratio)
{
    if ($ratio < 0.025)
    {
        return "#FF0000";
    }
    if ($ratio < 0.05)
    {
        return "#EE0000";
    }
    if ($ratio < 0.075)
    {
        return "#DD0000";
    }
    if ($ratio < 0.1)
    {
        return "#CC0000";
    }
    if ($ratio < 0.125)
    {
        return "#BB0000";
    }
    if ($ratio < 0.15)
    {
        return "#AA0000";
    }
    if ($ratio < 0.175)
    {
        return "#990000";
    }
    if ($ratio < 0.2)
    {
        return "#880000";
    }
    if ($ratio < 0.225)
    {
        return "#770000";
    }
    if ($ratio < 0.25)
    {
        return "#660000";
    }
    if ($ratio < 0.275)
    {
        return "#550000";
    }
    if ($ratio < 0.3)
    {
        return "#440000";
    }
    if ($ratio < 0.325)
    {
        return "#330000";
    }
    if ($ratio < 0.35)
    {
        return "#220000";
    }
    if ($ratio < 0.375)
    {
        return "#110000";
    }
    return "#009900";
}

function parked()
{
    global $lang;

    if (user::$current['parked'] == 'yes')

        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "{$lang['gbl_parked']}");
}

function get_user_id()
{
    return user::$current['id'];
}

function get_user_class()
{
    return user::$current['class'];
}

function get_user_class_name($class)
{
    switch ($class)
    {
        case UC_USER:
            return "User";

        case UC_POWER_USER:
            return "Power User";

        case UC_VIP:
            return "VIP";

        case UC_UPLOADER:
            return "Uploader";

        case UC_MODERATOR:
            return "Moderator";

        case UC_ADMINISTRATOR:
            return "Administrator";

        case UC_SYSOP:
            return "SysOp";

        case UC_MANAGER:
            return "Manager";
    }
    return "";
}

function get_user_class_color($class)
{
    switch ($class)
    {
        case UC_USER:
            return "8E35EF";
        case UC_POWER_USER:
            return "F9A200";
        case UC_VIP:
            return "009F00";
        case UC_UPLOADER:
            return "#CC0099";
        case UC_MODERATOR:
            return "#B24700";
        case UC_ADMINISTRATOR:
            return "#6A006A";
        case UC_SYSOP:
            return "0000FF";
        case UC_MANAGER:
            return "FF0000";
    }
    return "";
}

function format_username($user, $icons = true)
{
    global $image_dir, $site_url, $lang;

    $user['id']    = (int) $user['id'];
    $user['class'] = (int) $user['class'];

    if ($user['id'] == 0)
    {
        return "{$lang['text_system']}";
    }

    elseif ($user['username'] == '')
    {
        return "{$lang['text_unknown']}[{$user['id']}]";
    }

    $username = "<span style='font-weight : bold; color : #" . get_user_class_color($user['class']) . ";'>" . security::html_safe($user['username']) . "&nbsp;</span>";

    $str = "<span style='white-space : nowrap;'><a href='{$site_url}/userdetails.php?id={$user['id']}' target='_blank'>" . $username . "</a>";

    if ($icons != false)
    {
        $str .= ($user['donor'] == 'yes' ? "<img src='{$image_dir}star.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_donor']}' title='{$lang['gbl_img_alt_donor']}' />" : "");
        $str .= ($user['warned'] == 'yes' ? "<img src='{$image_dir}warned.png' width='15' height='16' border='0' alt='{$lang['gbl_img_alt_warned']}' title='{$lang['gbl_img_alt_warned']}' />" : "");
        $str .= ($user['enabled'] == 'no' ? "<img src='{$image_dir}disabled.png' width='16' height='15' border='0' alt='{$lang['gbl_img_alt_disabled']}' title='{$lang['gbl_img_alt_disabled']}' />" : "");
    }
    $str .= "</span>\n";

    return $str;
}

function format_user($user)
{
    global $site_url, $lang;

    return "<a href='{$site_url}/userdetails.php?id={$user['id']}' title=" . get_user_class_name($user['class']) . ">
                <span style='color : " . get_user_class_color($user['class']) . ";'>" . security::html_safe($user['username']) . "</span>
            </a>" . get_user_icons($user) . "";
}

function print_user_stuff($arr)
{
    global $lang;

    return "<a href='userdetails.php?id={$arr['id']} title=" . get_user_class_name($arr['class']) . ">
                <span style='font-weight : bold; color : " . get_user_class_color($arr['class']) . "; '>" . security::html_safe($arr['username']) . "</span>
            </a>";
  }

function is_valid_user_class($class)
{
    return user::valid_class($class);
}

function is_valid_id($id)
{
    return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}

function get_user_icons($arr, $big = false)
{
    global $image_dir, $lang;

    if ($big)
    {
        $donorpic    = "starbig.png";
        $warnedpic   = "warnedbig.png";
        $disabledpic = "disabledbig.png";
        $style       = "style='margin-left : 4pt'";
    }
    else
    {
        $donorpic    = "star.png";
        $warnedpic   = "warned.png";
        $disabledpic = "disabled.png";
        $style       = "style='margin-left : 2pt'";
    }

    $pics = $arr['donor'] == "yes" ? "<img src='{$image_dir}{$donorpic}' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_donor']}' title='{$lang['gbl_img_alt_donor']}' $style />" : "";

    if ($arr['enabled'] == "yes")
    {
        $pics .= $arr['warned'] == "yes" ? "<img src='{$image_dir}{$warnedpic}' width='15' height='16' border='0' alt='{$lang['gbl_img_alt_warned']}' title='{$lang['gbl_img_alt_warned']}' $style />" : "";
    }
    else
    {
        $pics .= "<img src='{$image_dir}{$disabledpic}' width='16' height='15' border='0' alt='{$lang['gbl_img_alt_disabled']}' title='{$lang['gbl_img_alt_disabled']}' $style />\n";
    }

    return $pics;
}

?>