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

function commenttable($rows)
{
    global $image_dir, $lang, $db;

    $lang = array_merge(load_language('func_commenttable'));

    begin_frame();

    //$count = 0;

    foreach ($rows
             AS
             $row)
    {
        print("<p class='sub'>#{$row['id']}{$lang['text_by']}");

        if (isset($row['username']))
        {
            $title = $row['title'];

            if ($title == "")
            {
                $title = get_user_class_name($row['class']);
            }
            else
            {
                $title = security::html_safe($title);
            }

            print("<a name='comm{$row['id']}' href='userdetails.php?id={$row['user']}'><span style='font-weight : bold;'>" . security::html_safe($row['username']) . "</span></a>" . ($row['donor'] == 'yes' ? "<img src='{$image_dir}star.png' width='16' height='16' border='0' alt='{$lang['img_alt_donor']}' title='{$lang['img_alt_donor']}' />" : "") . ($row['warned'] == 'yes' ? "<img src='{$image_dir}warned.png' width='16' height='16' border='0' alt='{$lang['img_alt_warned']}' title='{$lang['img_alt_warned']}' />" : "") . " ($title)\n");
        }
        else
        {
            print("<a name='comm{$row['id']}'><span style='font-style : italic;'>({$lang['text_orphan']})</span></a>\n");
        }

        if (user::$current['torrcompos'] == 'no')
        {
            if ($row['user'] == user::$current['id'])
            {
                print("{$lang['text_at']}{$row['added']}{$lang['text_gmt']}&nbsp;&nbsp;<a class='btn'>{$lang['text_edit_disabled']}</a> ");
            }
        }
        else
        {
            print("{$lang['text_at']}{$row['added']}{$lang['text_gmt']}&nbsp;&nbsp;" . ($row['user'] <> user::$current['id'] ? "<a class='btn' href='report.php?type=Comment&amp;id={$row['id']}'>{$lang['text_rep_comment']}</a>" : "") . ($row['user'] == user::$current['id'] || get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='/comment.php?action=edit&amp;cid={$row['id']}'>{$lang['text_edit']}</a>" : "") . (get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='/comment.php?action=delete&amp;cid={$row['id']}'>{$lang['text_delete']}</a>" : "") . ($row['editedby'] && get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='/comment.php?action=vieworiginal&amp;cid={$row['id']}'>{$lang['text_view_orig']}</a>" : "") . "</p>\n");
        }

		$avatar = ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? security::html_safe($row['avatar']) : "");

        if (!$avatar)
        {
            $avatar = "{$image_dir}default_avatar.gif";
        }

        $text = format_comment($row['text']);

        if ($row['editedby'])
        {
            $res_user = $db->query("SELECT username
                                    FROM users
                                    WHERE id = " . sqlesc($row['editedby'])) or sqlerr(__FILE__, __LINE__);

            $arr_user = $res_user->fetch_assoc();

            $text .= "<p><span style='font-size : x-small; '>{$lang['text_last_edit']}<a href='/userdetails.php?id=" . (int)$row['editedby'] . "'><span style='font-weight : bold;'>" . htmlsafechars($arr_user['username']) . "</span></a>{$lang['text_at']}{$row['editedat']} {$lang['text_gmt']}</span></p>\n";
        }

        begin_table(true);

        print("<tr valign='top'>\n");
        print("<td align='center' width='125'><img src='{$avatar}' width='125' height='125' border='0' alt='' title='' /></td>\n");
        print("<td class='text'>$text</td>\n");
        print("</tr>\n");

        end_table();
    }

    end_frame();

}

?>