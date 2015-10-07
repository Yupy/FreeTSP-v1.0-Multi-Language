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

db_connect();
logged_in();

$lang = array_merge(load_language('users'),
                    load_language('func_vfunctions'),
                    load_language('global'));

$search = isset($_GET['search']) ? strip_tags(trim($_GET['search'])) : '';
$class  = isset($_GET['class']) ? unesc($_GET['class']) : '-';
$letter = '';
$q      = '';

if ($class == '-' || !ctype_digit($class))
{
    $class = '';
}

if ($search != '' || $class)
{
    $query = "username LIKE " . sqlesc("%$search%") . " AND status = 'confirmed'";

    if ($search)
    {
        $q = "search = " . securty::html_safe($search);
    }
}
else
{
    $letter = isset($_GET['letter']) ? trim((string) $_GET['letter']) : '';

    if (strlen($letter) > 1)
    {
        die;
    }

    if ($letter == "" || strpos("abcdefghijklmnopqrstuvwxyz0123456789", $letter) === false)
    {
        $letter = "";
    }
    $query = "username LIKE '$letter%' AND status = 'confirmed'";
    $q     = "letter = $letter";
}

if (ctype_digit($class))
{
    $query .= " AND class = $class";
    $q .= ($q ? "&amp;" : "") . "class = $class";
}

site_header("{$lang['title_users']}");

echo("<h1>{$lang['title_users']}</h1>\n");

echo("<div align='center'>");
echo("<form method='get' action='users.php?'>\n");
echo("{$lang['form_field_search']}<input type='text' name='search' size='30' />\n");
echo("<select name='class'>\n");
echo("<option value='-'>{$lang['form_opt_class']}</option>\n");

for ($i = 0;;
     ++$i)
{
    if ($c = get_user_class_name($i))
    {
        echo("<option value='$i' " . (ctype_digit($class) && $class == $i ? " selected='selected' " : "") . ">$c</option>\n");
    }
    else
    {
        break;
    }
}

echo("</select>\n");
echo("<input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' />\n");
echo("</form>\n");
echo("<p>\n");
echo("<a href='users.php'><span style='font-weight : bold;'>{$lang['text_all']}</span></a> - \n");

for ($i = 97;
     $i < 123;
     ++$i)
{
    $l = chr($i);
    $L = chr($i - 32);

    if ($l == $letter)
    {
        echo("<span style='font-weight : bold;'>$L</span>\n");
    }
    else
    {
        echo("<a href='?letter=$l'><span style='font-weight : bold;'>$L</span></a>\n");
    }
}

echo("</p>\n");

$page       = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perpage    = 25;
$browsemenu = '';
$pagemenu   = '';

$res = $db->query("SELECT COUNT(id)
                  FROM users
                  WHERE $query") or sqlerr(__FILE__, __LINE__);

$arr = $res->fetch_row();

if ($arr[0] > $perpage)
{
    $pages = floor($arr[0] / $perpage);

    if ($pages * $perpage < $arr[0])
    {
        ++$pages;
    }

    if ($page < 1)
    {
        $page = 1;
    }
    else
    {
        if ($page > $pages)
        {
            $page = $pages;
        }
    }

    for ($i = 1;
         $i <= $pages;
         ++$i)
    {
        $PageNo = $i + 1;

        if ($PageNo < ($page - 2))
        {
            continue;
        }

        if ($i == $page)
        {
            $pagemenu .= "<span style='font-weight : bold;'>$i</span>\n";
        }
        else
        {
            $pagemenu .= "<a href='?$q&amp;page=$i'><span style='font-weight : bold;'>$i</span></a>\n";
        }

        if ($PageNo > ($page + 3))
        {
            break;
        }
    }

    if ($page == 1)
    {
        $browsemenu .= "<span style='font-weight : bold;'>&lsaquo;{$lang['text_prev']}</span>";
    }
    else
    {
        $browsemenu .= "<a href='users.php?$q&amp;page=" . ($page - 1) . "'><span style='font-weight : bold;'>&laquo;{$lang['text_prev']}</span></a>";
    }

    $browsemenu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    if ($page == $pages)
    {
        $browsemenu .= "<span style='font-weight : bold;'>{$lang['text_next']}&rsaquo;</span>";
    }
    else
    {
        $browsemenu .= "<a href='users.php?$q&amp;page=" . ($page + 1) . "'><span style='font-weight : bold;'>{$lang['text_next']}&raquo;</span></a>";
    }
}

echo("<p>$browsemenu<br />$pagemenu</p></div>");

$offset = ($page * $perpage) - $perpage;

if ($arr[0] > 0)
{
    $res = $db->query("SELECT users.*, countries.name, countries.flagpic
                      FROM users FORCE INDEX (username)
                      LEFT JOIN countries ON country = countries.id
                      WHERE $query
                      ORDER BY username
                      LIMIT $offset, $perpage") or sqlerr(__FILE__, __LINE__);

    echo("<table border='1' cellspacing='0' cellpadding='5'>");
    echo("<tr>
            <td class='colhead' align='left'>{$lang['table_username']}</td>
            <td class='colhead'>{$lang['table_registered']}</td>
            <td class='colhead'>{$lang['table_access']}</td>
            <td class='colhead' align='left'>{$lang['table_class']}</td>
            <td class='colhead'>{$lang['table_country']}</td>
            </tr>");

    while ($row = $res->fetch_assoc())
    {
        $country = ($row['name'] != NULL) ? "<td class='rowhead'style='padding : 0px' align='center'><img src='{$image_dir}flag/{$row['flagpic']}' width='32' height='20' border='0' alt='" . security::html_safe($row['name']) . "' title='" . security::html_safe($row['name']) . "'/></td>" : "<td class='rowhead' align='center'>---</td>";

        echo("<tr>
                <td class='rowhead' align='left'>
                    <a href='userdetails.php?id={$row['id']}'><span style='font-weight : bold;'>{$row['username']}</span></a>&nbsp;&nbsp;
                    " . ($row['donor'] == 'yes' ? "<img src='{$image_dir}star.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_donor']}' title='{$lang['gbl_img_alt_donor']}' />" : "") . "&nbsp;
                    " . ($row['warned'] == 'yes' ? "<img src='{$image_dir}warned.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_warned']}' title='{$lang['gbl_img_alt_warned']}' />" : "") . "
                </td>
                <td class='rowhead'>{$row['added']}</td>
                <td class='rowhead'>{$row['last_access']}</td>
                <td class='rowhead' align='left'>" . get_user_class_name($row['class']) . "</td>
                $country
            </tr>");
    }
    echo("</table>\n");
}

echo("<p>$pagemenu<br />$browsemenu</p>");

site_footer();

die;

?>