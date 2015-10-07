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
require_once(FUNC_DIR . 'function_pager_new.php');

db_connect(true);
logged_in();

parked();

$lang = array_merge(load_language('credits'),
                    load_language('global'),
                    load_language('func_bbcode'));

    //-- Get Stuff For The Pager --//
    $count_query = $db->query("SELECT COUNT(id)
                              FROM modscredits");

    $count_arr = $count_query->fetch_row();
    $count     = (int)$count_arr[0];
    $page      = isset($_GET['page']) ? (int)$_GET['page'] : 0;
    $perpage   = isset($_GET['perpage']) ? (int)$_GET['perpage'] : 10;

    list($menu, $LIMIT) = pager_new($count, $perpage, $page, "credits.php?" . ($perpage == 10 ? "" : "&amp;perpage=" . $perpage));

    $res = $db->query("SELECT *
                      FROM modscredits
                      ORDER BY id ASC $LIMIT");

    site_header("{$lang['title_credits']}", false);

    print("<div align='center'><strong>{$lang['text_installed']}$site_name{$lang['text_source']}</strong></div><br />
           <div align='center'>$menu<br /><br /></div>
                <table border='1' align='center' width='90%' cellpadding='10' cellspacing='1'>
                    <tr>
                         <td class='colhead' align='center' width='51%'>{$lang['table_name']}</td>
                         <td class='colhead' align='center' width='15%'>{$lang['table_cat']}</td>
                         <td class='colhead' align='center' width='10%'>{$lang['table_status']}</td>
                         <td class='colhead' align='center' width='10%'>{$lang['table_original']}</td>
                         <td class='colhead' align='center' width='10%'>{$lang['table_modified']}</td>");

    if (get_user_class() >= UC_MANAGER)
    {
        print("<td class='colhead' align='center' width='4%' colspan='2'>{$lang['table_action']}</td>");
    }

    print("</tr>");

while ($row = $res->fetch_assoc())
{
    $id       = (int) $row['id'];
    $name     = $row['name'];
    $category = $row['category'];

    if ($row['status'] == 'In-Progress')
    {
        $status = "<span class='in_progress_status'>{$row['status']}</span>";
    }
    else
    {
        $status = "<span class='complete_status'>{$row['status']}</span>";
    }

    $link     = $row['mod_link'];
    $credit   = $row['credit'];
    $modified = $row['modified'];
    $descr    = $row['description'];

    print("<tr><td class='rowhead'><a class='altlink' href='" . $link . "' target='_blank'>" . security::html_safe(_string::cut_word($name, 90)) . "</a>");

    print("<br/><font class='small'>" . security::html_safe($descr) . "</font></td>");
    print("<td class='rowhead' align='center'><strong>" . security::html_safe($category) . "</strong></td>");
    print("<td class='rowhead' align='center'>" . $status . "</td>");
    print("<td class='rowhead' align='center'><strong>" . security::html_safe($credit) . "</strong></td>");
    print("<td class='rowhead' align='center'><strong>" . security::html_safe($modified) . "</strong></td>");

    if (get_user_class() >= UC_MANAGER)
    {
        print("<td class='rowhead'><a href='controlpanel.php?fileaction=29&amp;action=edit_credit&amp;id={$id}'><img src='{$image_dir}admin/edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' /></a></td>");

        print("<td class='rowhead'><a href='controlpanel.php?fileaction=29&amp;action=delete_credit&amp;id={$id}'><img src='{$image_dir}admin/delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' /></a></td>");
    }

    print("</tr>");
}

    print("</table><br /><br />");
    print("<div align='center'>$menu<br /><br /></div>");

    site_footer();
?>