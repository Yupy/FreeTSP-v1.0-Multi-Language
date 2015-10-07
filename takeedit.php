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
require_once(FUNC_DIR . 'function_page_verify.php');

db_connect();
logged_in();

$lang = array_merge(load_language('takeedit'),
                    load_language('global'));

$newpage = new page_verify();
$newpage->check('_edit_');

if (!mkglobal("id:name:descr:type"))
{
    error_message("error",
                  "{$lang['err_edit_failed']}",
                  "{$lang['err_missing_data']}");
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if (!is_valid_id($id))
{
    die();
}

$res = $db->query("SELECT owner, filename, save_as
                  FROM torrents
                  WHERE id = $id");

$row = $res->fetch_assoc();

if (!$row)
{
    die();
}

if (user::$current['id'] != $row['owner'] && get_user_class() < UC_MODERATOR)
{
    error_message("error",
                  "{$lang['err_edit_failed']}",
                  "{$lang['err_not_owner']}");
}

$updateset = array();
$fname     = $row['filename'];

preg_match('/^(.+)\.torrent$/si', $fname, $matches);

$shortfname = $matches[1];
$dname      = $row['save_as'];
$nfoaction  = $_POST['nfoaction'];

if (!empty($_POST['poster']))
{
    $poster = unesc($_POST['poster']);
}

if ($nfoaction == 'update')
{
    $nfofile = $_FILES['nfo'];

    if (!$nfofile)
    {
        die("{$lang['err_no_data']}" . var_dump($_FILES));
    }

    if ($nfofile['size'] > 65535)
    {
        error_message("error",
                      "{$lang['err_edit_failed']}",
                      "{$lang['err_nfo_too_big']}");
    }

    $nfofilename = $nfofile['tmp_name'];

    if (@is_uploaded_file($nfofilename) && @filesize($nfofilename) > 0)
    {
        $updateset[] = "nfo = " . sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", file_get_contents($nfofilename)));
    }
}
else
{
    if ($nfoaction == 'remove')
    {
        $updateset[] = 'nfo = ""';
    }
}

$updateset[] = "name = " . sqlesc($name);
$updateset[] = "anonymous = '" . ($_POST['anonymous'] ? "yes" : "no") . "'";
$updateset[] = "search_text = " . sqlesc(searchfield("$shortfname $dname $torrent"));
$updateset[] = "descr = " . sqlesc(utf8::to_utf8($descr));
$updateset[] = "ori_descr = " . sqlesc(utf8::to_utf8($descr));
$updateset[] = "category = " . (intval(0 + $type));

if (get_user_class() >= UC_MODERATOR)
{
    if (isset($_POST['banned']))
    {
        $updateset[]      = 'banned = "yes"';
        $_POST['visible'] = 0;
    }
    else
    {
        $updateset[] = 'banned = "no"';
    }

    if ($_POST['sticky'] == "yes")
    {
        $updateset[] = "sticky = 'yes'";
    }
    else
    {
        $updateset[] = "sticky = 'no'";
    }
}

$updateset[] = "freeleech = '" . ( isset($_POST['freeleech']) ? 'yes' : 'no') . "'";
$updateset[] = "visible = '" . (isset($_POST['visible']) ? 'yes' : 'no') . "'";
$updateset[] = "poster = " . sqlesc($poster);

$db->query("UPDATE torrents
            SET " . join(",", $updateset) . "
            WHERE id = $id");

$Memcache->delete_value('edit::torrent::' . $id);
$Memcache->delete_value('torrent::details::' . $id);

write_log("{$lang['writelog_torrent']}" . $id ." (" . security::html_safe($name) . "){$lang['writelog_edit_by']}" . security::html_safe(user::$current['username']));

$returl = "details.php?id=$id&edited=1";

if (isset($_POST['returnto']))
{
    $returl .= "&returnto=" . urlencode($_POST['returnto']);
}

header("Refresh: 0; url=$returl");

?>