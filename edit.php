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
require_once(FUNC_DIR . 'function_page_verify.php');

if (!mkglobal('id'))
{
    die();
}

$id = intval(0 + $id);

if (!$id)
{
    die();
}

db_connect();
logged_in();

$lang = array_merge(load_language('edit'),
                    load_language('func_bbcode'),
                    load_language('global'));

$newpage = new page_verify();
$newpage->create('_edit_');

if (($row = $Memcache->get_value('edit::torrent::' . $id)) === false) {
    $res = $db->query("SELECT *
                       FROM torrents
                       WHERE id = " . $id);

    $row = $res->fetch_assoc();
    $Memcache->cache_value('edit::torrent::' . $id, $row, 86400);
}

if (!$row)
{
    die();
}

site_header("{$lang['title_edit']} '{$row['name']}'");

if (!isset(user::$current) || (user::$current['id'] != $row['owner'] && get_user_class() < UC_MODERATOR))
{
    echo display_message("warn",
                         "{$lang['err_cannot_edit']}",
                         "{$lang['text_owner']}<a href='login.php?returnto=" . urlencode(substr($_SERVER['REQUEST_URI'], 1)) . "&amp;nowarn=1'>{$lang['text_logged_in']}</a>{$lang['text_properly']}");
}
else
{
    print("<form name='editupload' method='post' action='takeedit.php' enctype='multipart/form-data'>\n");
    print("<input type='hidden' name='id' value='$id' />\n");

    if (isset($_GET['returnto']))
    {
        print("<input type='hidden' name='returnto' value='" . security::html_safe($_GET['returnto']) . "' />\n");
    }

    print("<table border='1' align='center' cellspacing='0' cellpadding='10'>\n");
    print("<tr>
            <td class='rowhead'>
                <label for='name'>{$lang['table_name']}</label>
            </td>
            <td class='rowhead'>
                <input type='text' name='name' id='name' size='80' value='" . security::html_safe($row['name']) . "' />
            </td>
        </tr>");

    print("<tr>
         <td class='rowhead'>
            <label for='poster'>{$lang['table_poster']}</label>
        </td>
         <td class='rowhead'>
            <input type='text' name='poster' id='poster' size='80' value='" . security::html_safe($row['poster']) . "' /><br />
                {$lang['table_poster_label']}
         </td>
     </tr>");

    print("<tr>
            <td class='rowhead'>{$lang['table_nfo']}</td>
            <td class='rowhead'>
                <input type='radio' name='nfoaction' value='keep' checked='checked' />&nbsp;{$lang['table_nfo_current']}<br />
                <input type='radio' name='nfoaction' value='update' />&nbsp;{$lang['table_nfo_update']}<br /><br />
                <input type='file' name='nfo' size='80' />
            </td>
        </tr>");

    if ((strpos($row['ori_descr'], "<") === false) || (strpos($row['ori_descr'], "&lt;") !== false))
    {
        $c = '';
    }
    else
    {
        $c = 'checked';
    }

    print("<tr>
            <td class='rowhead' style='padding : 10px'>{$lang['table_desc']}</td>
            <td class='rowhead' align='center' style='padding : 3px'>" . textbbcode('editupload', 'descr', security::html_safe($row['ori_descr'])) . "</td>
        </tr>\n");

    $s = "<select name='type'>\n";

    $cats = cached::genrelist();

    foreach ($cats
             AS
             $subrow)
    {
        $s .= "<option value='" . (int)$subrow['id'] . "'";

        if ($subrow['id'] == $row['category'])
        {
            $s .= "selected='selected'";
        }

        $s .= ">" . security::html_safe($subrow['name']) . "</option>\n";
    }

    $s .= "</select>\n";

    print("<tr>
            <td class='rowhead'>{$lang['table_type']}</td>
            <td class='rowhead'>$s</td>
        </tr>");

    print("<tr>
            <td class='rowhead'>{$lang['table_visible']}</td>
            <td class='rowhead'>
                <input type='checkbox' name='visible'" . (($row['visible'] == 'yes') ? "checked='checked' " : "") . " value='1' /> {$lang['table_visible_main']}<br />
                    <table border='0' width='540' cellspacing='0' cellpadding='0'>
                        <tr>
                            <td class='embedded'><strong>{$lang['table_visible_note']}</strong>&nbsp;&nbsp;{$lang['table_visible_note1']}
                            </td>
                        </tr>
                    </table>
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead'>{$lang['table_anon']}</td>
            <td class='rowhead'>
                <input type='checkbox' name='anonymous'" . (($row['anonymous'] == 'yes') ? " checked='checked'" : '' ) . " value='1' />&nbsp;{$lang['table_anon_note']}
            </td>
        </tr>");

    if (get_user_class() >= UC_MODERATOR)
    {
        print("<tr>
                <td class='rowhead'>{$lang['table_banned']}</td>
                <td class='rowhead'>
                    <input type='checkbox' name='banned'" . (($row['banned'] == 'yes') ? " checked='checked'" : '') . " value='1' />&nbsp;{$lang['table_banned_note']}
                </td>
            </tr>");

        print("<tr>
                <td class='rowhead'>{$lang['table_sticky']}</td>
                <td class='rowhead'>
                    <input type='checkbox' name='sticky'" . (($row['sticky'] == 'yes') ? " checked='checked'" : '' ) . " value='yes' />&nbsp;{$lang['table_sticky_note']}
                </td>
            </tr>");
    }

    print("<tr>
            <td class='rowhead'>{$lang['table_free']}</td>
            <td class='rowhead'>
                <input type='checkbox' name='freeleech'" . (($row['freeleech'] == 'yes') ? " checked='checked'" : '') . " value='yes' />&nbsp;{$lang['table_free_note']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead' align='center' colspan='2'>
                <input type='reset' class='btn' value='{$lang['gbl_btn_cancel']}' style='height : 25px; width : 100px' />&nbsp;&nbsp;&nbsp;&nbsp;
                <input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' style='height : 25px; width : 100px' />
            </td>
        </tr>\n");

    print("</table>\n");
    print("</form>\n");
    print("<br />\n");

    if (get_user_class() >= UC_MODERATOR)
    {
        print("<form method='post' action='delete.php'>\n");
        print("<table border='1' align='center' cellspacing='0' cellpadding='5'>\n");

        print("<tr>
                <td class='rowhead' style='padding-bottom: 5px' colspan='2'>
                <span style='font-weight : bold;'>{$lang['table_del_reason']}</span></td>
            </tr>");

        print("<tr>
                <td class='rowhead'>
                    <input type='radio' name='reasontype' value='1' />&nbsp;{$lang['table_del_dead']}
                </td>
                <td class='rowhead'>&nbsp;{$lang['table_del_dead_info']}</td>
            </tr>\n");

        print("<tr>
                <td class='rowhead'>
                    <input type='radio' name='reasontype' value='2' />&nbsp;{$lang['table_del_dupe']}
                </td>
                <td class='rowhead'><input type='text' name='reason[]' size='40' />&nbsp;<em>{$lang['table_del_url']}</em></td>
            </tr>");

        print("<tr>
                <td class='rowhead'>
                    <input type='radio' name='reasontype' value='3' />&nbsp;{$lang['table_del_nuked']}
                </td>
                <td class='rowhead'>
                    <input type='text' name='reason[]' size='40' />&nbsp;<em>{$lang['table_del_info']}</em>
                </td>
            </tr>");

        print("<tr>
                <td class='rowhead'>
                    <input type='radio' name='reasontype' value='4' />&nbsp;$site_name&nbsp;{$lang['table_del_rules']}
                </td>
                <td class='rowhead'>
                    <input type='text' name='reason[]' size='40' />&nbsp;<em>{$lang['table_del_req_info']}</em>
                </td>
            </tr>");

        print("<tr>
                <td class='rowhead'>
                    <input type='radio' name='reasontype' value='5' checked='checked' />&nbsp;{$lang['table_del_other']}
                </td>
                <td class='rowhead'>
                    <input type='text' name='reason[]' size='40' />&nbsp;<em>{$lang['table_del_req_info']}</em>");

        print("<input type='hidden' name='id' value='$id' /></td></tr>\n");

        if (isset($_GET['returnto']))
        {
            print("<input type='hidden' name='returnto' value='" . security::html_safe($_GET['returnto']) . "' />\n");
        }
        print("<tr>
                <td align='center' colspan='2'>
                    <input type='submit' class='btn' value='{$lang['gbl_btn_remove']}' style='height : 25px' />
                </td>
            </tr>");
        print("</table>");
        print("</form><br />");
    }
}

site_footer();

?>