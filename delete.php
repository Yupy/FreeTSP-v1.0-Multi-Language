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

function deletetorrent($id)
{
    global $torrent_dir, $Memcache, $db;

    $db->query("DELETE
               FROM torrents
               WHERE id = " . $id);
	
	$Memcache->delete_value('torrent::details::' . $id);

    foreach (explode(".", "peers.files.comments.ratings")
             AS
             $x)

    {
        $db->query("DELETE
                   FROM " . $x . "
                   WHERE torrent = " . $id);
    }

    unlink("$torrent_dir/$id.torrent");
}

if (!mkglobal("id"))
{
    error_message("error",
                  "{$lang['err_del_failed']}",
                  "{$lang['err_missing_data']}");
}

$id = intval(0 + $id);

if (!is_valid_id($id))
{
    die();
}

db_connect();
logged_in();

$lang = array_merge(load_language('delete'),
                    load_language('global'));

$res = $db->query("SELECT name, owner, seeders
                  FROM torrents
                  WHERE id = " . $id);

$row = $res->fetch_assoc();

if (!$row)
{
    die();
}

if (user::$current['id'] != $row['owner'] && get_user_class() < UC_MODERATOR)
{
    error_message("error",
                  "{$lang['err_del_failed']}",
                  "{$lang['err_not_owner']}");
}

$rt = intval(0 + $_POST['reasontype']);

if (!is_int($rt) || $rt < 1 || $rt > 5)
{
    error_message("error",
                  "{$lang['err_del_failed']}",
                  "{$lang['err_inv_reason']}$rt.");
}

$r      = $_POST['r'];
$reason = $_POST['reason'];

if ($rt == 1)
{
    $reasonstr = "{$lang['text_dead']}";
}
elseif ($rt == 2)
{
    $reasonstr = "{$lang['text_dupe']}" . ($reason[0] ? (": " . trim($reason[0])) : "!");
}
elseif ($rt == 3)
{
    $reasonstr = "{$lang['text_nuked']}" . ($reason[1] ? (": " . trim($reason[1])) : "!");
}
elseif ($rt == 4)
{
    if (!$reason[2])
    {
        error_message("error",
                      "{$lang['err_del_failed']}",
                      "{$lang['text_violated']}");
    }

    $reasonstr = $site_name . "{$lang['text_rules_broken']}" . trim($reason[2]);
}
else
{
    if (!$reason[3])
    {
        error_message("error",
                      "{$lang['err_del_failed']}",
                      "{$lang['text_del_reason']}");
    }

    $reasonstr = trim($reason[3]);
}

deletetorrent($id);

write_log("{$lang['log_torrent']}$id ({$row['name']}){$lang['log_del_by']}" . user::$current['username'] . " ($reasonstr)\n");

site_header("{$lang['title_deleted']}");

if (isset($_POST['returnto']))
{
    echo $ret = display_message_center("info",
                                " ",
                                "<a href='" . security::html_safe("{$site_url}/{$_POST['returnto']}") . "'>{$lang['text_return']}</a>");
}
else
{
    echo $ret = display_message_center("info",
                                "{$lang['text_deleted']}",
                                "{$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");
}

?>

<p><?php $ret ?></p>

<?php

site_footer();

?>