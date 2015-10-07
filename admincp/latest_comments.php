<?php

/**
**************************
** FreeTSP Version: 1.0 **
**************************
** https://github.com/Krypto/FreeTSP
** http://www.freetsp.info
** Licence Info: GPL
** Copyright (C) 2010 FreeTSP v1.0
** A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
** Project Leaders: Krypto, Fireknight.
**/

if (!defined("IN_FTSP_ADMIN"))
{
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

            <title><?php if (isset($_GET['error']))
            {
                echo security::html_safe($_GET['error']);
            }
            ?> Error</title>

            <link rel='stylesheet' type='text/css' href='/errors/error-style.css' />
        </head>
        <body>
            <div id='container'>
                <div align='center' style='padding-top:15px'>
                    <img src='/errors/error-images/alert.png' width='89' height='94' alt='404 Page Not Found' title='404 Page Not Found' />
                </div>
                <h1 class='title'>Error 404 - Page Not Found</h1>
                <p class='sub-title' align='center'>The page that you are looking for does not appear to exist on this site.</p>
                <p>If you typed the address of the page into the address bar of your browser, please check that you typed it in correctly.</p>
                <p>If you arrived at this page after you used an old Boomark or Favorite, the page in question has probably been moved. Try locating the page via the navigation menu and then updating your bookmark.</p>
            </div>
        </body>
    </html>

<?php

exit();

}

$lang = array_merge(load_language('adm_latest_comments'),
                    load_language('adm_global'));

site_header("{$lang['title_comments']}", false);

$limit = 25;

if (isset($_GET['amount']) && (int)$_GET['amount'])
{
    if (intval($_GET['amount']) != $_GET['amount'])
    {
        error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_integer']}");
    }

    $limit = intval(0 + $_GET['amount']);

    if ($limit > 999)
    {
        $limit = 1000;
    }

    if ($limit < 10)
    {
        $limit = 10;
    }
}

$subres = $db->query("SELECT comments.id, torrent, text, user, comments.added , editedby, editedat, avatar, warned, username, title, class, donor
                      FROM comments
                      LEFT JOIN users ON comments.user = users.id
                      ORDER BY comments.id
                      DESC limit 0," . $limit) or sqlerr(__FILE__, __LINE__);

$allrows = array();

while ($subrow = $subres->fetch_assoc())

    $allrows[] = $subrow;

    print("<h2>{$lang['text_show_latest']}&nbsp;{$limit}&nbsp;{$lang['text_show_comms']}</h2>");
    print("<table class='main' width='100%'><tr>");

function commenttable_new($rows)
{
    global $image_dir, $lang, $db;

    foreach ($rows
             AS
             $row)
    {
        $subres = $db->query("SELECT name
                              FROM torrents
                              WHERE id = " . sqlesc($row['torrent'])) or sqlerr(__FILE__, __LINE__);

        $subrow = $subres->fetch_assoc();

        print("<td align='left' colspan='2'>");
        print("{$lang['text_torr_name']}:&nbsp;<span style='font-weight : bold;'>
                <a href='details.php?id=" . security::html_safe($row['torrent']) . "'>" . security::html_safe($subrow['name']) . "</a>
            </span><br />");

        print("{$lang['text_comment_no']}{$row['id']}{$lang['text_by']}");

        if (isset($row['username']))
        {
            $title = $row['title'];

            if ($title == '')
            {
                $title = get_user_class_name($row['class']);
            }
            else
            {
                $title = security::html_safe($title);
            }

            print("<a name='comm{$row['id']}' href='userdetails.php?id={$row['user']}'>
                   <span style='font-weight : bold;'>" . security::html_safe($row['username']) . "</span>
                   </a>" . ($row['donor'] == "yes" ? "<img src='{$image_dir}star.png' width='16' height='16' border='0' alt='{$lang['img_alt_donor']}' title='{$lang['img_alt_donor']}' />" : "") . ($row['warned'] == "yes" ? "<img src=" .
                   "'{$image_dir}warned.png' width='16' height='16' border='0' alt='{$lang['img_alt_warned']}' title='{$lang['img_alt_warned']}'>" : "") . " ($title)");
        }
        else
        {
            print("({$lang['text_not_member']} - <span style='font-style : italic;'>{$lang['text_orphaned']})</span>");
        }

        print("{$lang['text_at']}{$row['added']}{$lang['text_gmt']}" .
             ($row['user'] == user::$current['id'] || get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='/comment.php?action=edit&amp;cid={$row['id']}'>{$lang['btn_edit']}</a>" : "") .
             (get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='/comment.php?action=delete&amp;cid={$row['id']}'>{$lang['btn_delete']}</a>" : "") .
             ($row['editedby'] && get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='/comment.php?action=vieworiginal&amp;cid={$row['id']}'>{$lang['btn_view_orig']}</a>" : "") . "");
        print("</td></tr>");

        $text = format_comment($row['text']);

        if ($row['editedby'])
        {
            $res_user = $db->query("SELECT username
                                    FROM users
                                    WHERE id = " . sqlesc($row['editedby'])) or sqlerr(__FILE__, __LINE__);

            $arr_user = $res_user->fetch_assoc();

            $text .= "<p><span style='font-size : x-small; '>{$lang['text_last_edit']}<a href='/userdetails.php?id=" . (int)$row['editedby'] . "'><span style='font-weight : bold;'>" . htmlsafechars($arr_user['username']) . "</span></a>{$lang['text_at']}{$row['editedat']} {$lang['text_gmt']}</span></p>\n";
        }
            print("<tr valign='top'>");

        if (!empty($row['avatar']))
        {
            print("<td align='center' width='150' height='150' style='padding : 0px'><img src='" . security::html_safe($row['avatar']) . "'  width='125' height='125' alt='' title='' /></td>\n");
        }
        else
        {
            print("<td align='center' width='150' height='150' style='padding : 0px'><img src='{$image_dir}default_avatar.gif'  width='125' height='125' alt='' title='' /></td>\n");
        }
        print("<td class='text'>$text</td>");
        print("</tr>");
    }
}

commenttable_new($allrows);

print("</table><br />");

site_footer();

?>