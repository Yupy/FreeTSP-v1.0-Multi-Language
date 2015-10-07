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
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_user.php');

db_connect(true);
logged_in();

$lang = array_merge(load_language('global'));

$id = (int) $_GET['id'];

if (!is_valid_id($id))
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_issues']}", 1);
}

$key = 'themes::preview::' . $id;
if (($row = $Memcache->get_value($key)) === false) {
    $query = $db->query("SELECT *
                         FROM stylesheets
                         WHERE id = " . $db->real_escape_string($id)) or sqlerr(__FILE__, __LINE__);

    $row = $query->fetch_array(MYSQLI_BOTH);
    $Memcache->cache_value($key, $row, 43200);
}

if (!$row)
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_theme_missing']}", 1);
}

require_once("stylesheets/{$row['uri']}/theme_function.php");

if (!is_file("stylesheets/{$row['uri']}/site_header.php"))
{
    $message = "{$lang['err_header_missing']}";
}

if (!is_file("stylesheets/{$row['uri']}/site_footer.php"))
{
    $message = "{$lang['err_footer_missing']}";
}

if (isset($message))
{
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "$message", 1);
}

require_once("stylesheets/{$row['uri']}/site_header.php");

print("<table width='100%'>");

$lang = array_merge(load_language('theme_preview'));

print("<tr>
        <td class='rowhead' align='center'>{$lang['table_viewing']}<strong>{$row['name']}</strong>{$lang['table_theme']}<br /><br />
            <a href='{$_SERVER['HTTP_REFERER']}'><strong>{$lang['table_return']}</strong></a>
        </td>
    </tr>");

print("</table>");

require_once("stylesheets/{$row['uri']}/site_footer.php");

?>