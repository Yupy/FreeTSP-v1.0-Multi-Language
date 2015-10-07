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
error_reporting(0);
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_torrenttable.php');
require_once(FUNC_DIR . 'function_benc.php');
require_once(FUNC_DIR . 'function_page_verify.php');

ini_set("upload_max_filesize", $max_torrent_size);

db_connect();
logged_in();

$newpage = new page_verify();
$newpage->check('_upload_');

$lang = array_merge(load_language('takeupload'),
                    load_language('global'));

if (get_user_class() < UC_USER)
{
    die;
}

foreach (explode(":", "descr:type:name")
         AS
         $v)
{
    if (!isset($_POST[$v]))
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_missing_data']}");
    }
}

if (!isset($_FILES['file']))
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_missing_data']}");
}

$f     = $_FILES['file'];
$fname = unesc($f['name']);

if (empty($fname))
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_empty_filename']}");
}

if ($_POST['uplver'] == 'yes')
{
    $anonymous = "yes";
    $anon      = "{$lang['text_anon']}";
}
else
{
    $anonymous = "no";
    $anon      = user::$current['username'];
}

if ($_POST['freeleech'] == 'yes')
{
    $freeleech = "yes";
}
else
{
    $freeleech = "no";
}

$nfo = sqlesc('');

if (isset($_FILES['nfo']) && !empty($_FILES['nfo']['name']))
{
    $nfofile = $_FILES['nfo'];

    if ($nfofile['name'] == '')
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_no_nfo']}");
    }

    if ($nfofile['size'] == 0)
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_zero_byte']}");
    }

    if ($nfofile['size'] > 65535)
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_large_nfo']}");
    }

    $nfofilename = $nfofile['tmp_name'];

    if (@!is_uploaded_file($nfofilename))
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_nfo_fail']}");
    }

    $nfo = sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename)));
}

$request = (((isset($_POST['request']) && is_valid_id($_POST['request'])) ? intval($_POST['request']) : 0));
$offer   = (((isset($_POST['offer']) && is_valid_id($_POST['offer'])) ? intval($_POST['offer']) : 0));

$descr = utf8::to_utf8(unesc($_POST['descr']));

if (!$descr)
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_enter_desc']}");
}

$catid = (0 + (int)$_POST['type']);

if (!is_valid_id($catid))
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_select_cat']}");
}

if (!validfilename($fname))
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_inv_filename']}");
}

if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_inv_filename_tor']}");
}

$shortfname = $torrent = $matches[1];

if (!empty($_POST['name']))
{
    $torrent = unesc($_POST['name']);
}

if (!empty($_POST['poster']))
{
     $poster = unesc($_POST['poster']);
}

$tmpname = $f['tmp_name'];

if (!is_uploaded_file($tmpname))
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_eek']}");
}

if (!filesize($tmpname))
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_empty_file']}");
}

$dict = bdec_file($tmpname, $max_torrent_size);

if (!isset($dict))
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_bencoded']}");
}

function dict_check($d, $s)
{
    if ($d['type'] != "dictionary")
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_dict']}");
    }

    $a   = explode(":", $s);
    $dd  = $d['value'];
    $ret = array();
    $t   = '';

    foreach ($a
             AS
             $k)
    {
        unset($t);

        if (preg_match('/^(.*)\((.*)\)$/', $k, $m))
        {
            $k = $m[1];
            $t = $m[2];
        }

        if (!isset($dd[$k]))
        {
            error_message_center("error",
                                 "{$lang['err_upload_fail']}",
                                 "{$lang['err_dict_key']}");
        }

        if (isset($t))
        {
            if ($dd[$k]['type'] != $t)
            {
                error_message_center("error",
                                     "{$lang['err_upload_fail']}",
                                     "{$lang['err_inv_dict']}");
            }
            $ret[] = $dd[$k]['value'];
        }
        else
        {
            $ret[] = $dd[$k];
        }
    }
    return $ret;
}

function dict_get($d, $k, $t)
{
    if ($d['type'] != "dictionary")
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_dict']}");
    }

    $dd = $d['value'];

    if (!isset($dd[$k]))
    {
        return;
    }

    $v = $dd[$k];

    if ($v['type'] != $t)
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_inv_dict_type']}");
    }
    return $v['value'];
}

list($ann, $info)            = dict_check($dict, "announce(string):info");
list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

if (!in_array($ann, $announce_urls, 1))
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_inv_announce']}<strong>" . $announce_urls[0] . "</strong>");
}

if (strlen($pieces) % 20 != 0)
{
    error_message_center("error",
                         "{$lang['err_upload_fail']}",
                         "{$lang['err_inv_pieces']}");
}

$filelist = array();
$totallen = dict_get($info, "length", "integer");

if (isset($totallen))
{
    $filelist[] = array($dname,
                        $totallen);

    $type = "single";
}
else
{
    $flist = dict_get($info, "files", "list");

    if (!isset($flist))
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_miss_files']}");
    }

    if (!count($flist))
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_no_files']}");
    }

    $totallen = 0;

    foreach ($flist
             AS
             $fn)
    {
        list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
        $totallen += $ll;
        $ffa = array();

        foreach ($ff
                 AS
                 $ffe)
        {
            if ($ffe['type'] != "string")
            {
                error_message_center("error",
                                     "{$lang['err_upload_fail']}",
                                     "{$lang['err_filename_err']}");
            }

            $ffa[] = $ffe['value'];
        }

        if (!count($ffa))
        {
            error_message_center("error",
                                 "{$lang['err_upload_fail']}",
                                 "{$lang['err_filename_err']}");
        }

        $ffe        = implode("/", $ffa);
        $filelist[] = array($ffe,
                            $ll);
    }
    $type = "multi";
}

$infohash = pack("H*", sha1($info['string']));

//-- Replace Punctuation Characters With Spaces --//

$torrent = str_replace("_", " ", $torrent);
$torrent = str_replace(".torrent", " ", $torrent);
$torrent = str_replace(".rar", " ", $torrent);
$torrent = str_replace(".avi", " ", $torrent);
$torrent = str_replace(".mpeg", " ", $torrent);
$torrent = str_replace(".exe", " ", $torrent);
$torrent = str_replace(".zip", " ", $torrent);
$torrent = str_replace(".wmv", " ", $torrent);
$torrent = str_replace(".iso", " ", $torrent);
$torrent = str_replace(".bin", " ", $torrent);
$torrent = str_replace(".txt", " ", $torrent);
$torrent = str_replace(".nfo", " ", $torrent);
$torrent = str_replace(".7z", " ", $torrent);
$torrent = str_replace(".mp3", " ", $torrent);
$torrent = str_replace(".", " ", $torrent);

$nfo = sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename)));
$poster = unesc($_POST['poster']);

$ret = $db->query("INSERT INTO torrents (search_text, filename, owner, visible, anonymous, freeleech,
info_hash, name, size, numfiles, type, descr, ori_descr, category, save_as, added, last_action, nfo, offer, request, poster)
                  VALUES (" . implode(",", array_map("sqlesc", array(searchfield("$shortfname $dname $torrent"),
                                                                    $fname,
                                                                    user::$current['id'],
                                                                    "no",
                                                                    $anonymous,
                                                                    $freeleech,
                                                                    $infohash,
                                                                    $torrent,
                                                                    $totallen,
                                                                    count($filelist),
                                                                    $type,
                                                                    $descr,
                                                                    $descr,
                                                                    intval(0 + $_POST['type']),
                                                                    $dname))).",
                                                                    '" . get_date_time() . "',
                                                                    '" . get_date_time() . "',
                                                                    $nfo,
                                                                    $offer,
                                                                    $request,
                                                                    '" . $poster . "')");

if (!$ret)
{
    if ($db->errno == 1062)
    {
        error_message_center("error",
                             "{$lang['err_upload_fail']}",
                             "{$lang['err_already_upload']}");
    }
    $db->error;
}

$id = $db->insert_id;

@$db->query("DELETE
            FROM files
            WHERE torrent = $id");

foreach ($filelist
         AS
         $file)
{
    @$db->query("INSERT
                INTO files (torrent, filename, size)
                VALUES ($id, " . sqlesc($file[0]) . "," . $file[1] . ")");
}

move_uploaded_file($tmpname, "$torrent_dir/$id.torrent");

//-- Start Requests And Offers Notifications --//
$filled = 0;

//-- If It Was An Offer Notify The Folks Who Liked It --//
if ($offer > 0)
{
    $res_offer = $db->query("SELECT user_id
                            FROM offer_votes
                            WHERE vote = 'yes'
                            AND user_id != " . user::$current['id'] . "
                            AND offer_id = $offer") or sqlerr(__FILE__, __LINE__);

    $subject = sqlesc("{$lang['msg_subject_voted']}");

    $message = sqlesc("{$lang['msg_hi']}\n\n{$lang['msg_offer_uploaded']}\n\n [url=$site_url/details.php?id=$id][b]" . security::html_safe($torrent) . "[/b][/url].");

    $time = sqlesc(get_date_time());

         while($arr_offer = $res_offer->fetch_assoc())
         {
             $db->query("INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
                        VALUES(0, " . (int)$arr_offer['user_id'] . ", $time, $message, $subject, 'yes', 1)") or sqlerr(__FILE__, __LINE__);
         }

    $db->query("UPDATE offers
               SET filled_torrent_id = '$id'
               WHERE id = $offer") or sqlerr(__FILE__, __LINE__);

    write_log("{$lang['writelog_offered']}$id ($torrent){$lang['writelog_uploaded']}" . user::$current['username']);

    $filled = 1;
}

//-- If It Was A Request Notify The Folks Who Voted --//
if ($request > 0)
{
    $res_req = $db->query("SELECT user_id
                          FROM request_votes
                          WHERE vote = 'yes'
                          AND request_id = $request") or sqlerr(__FILE__, __LINE__);

    $subject = sqlesc("{$lang['msg_subject_request']}");

    $message = sqlesc("{$lang['msg_hi']}\n\n{$lang['msg_request_uploaded']}\n\n [url=$site_url/details.php?id=$id][b]" . security::html_safe($torrent) . "[/b][/url].");

    $time = sqlesc(get_date_time());

    while($arr_req = $res_req->fetch_assoc())
    {
        $db->query("INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
                   VALUES(0, " . (int)$arr_req['user_id'] . ", $time, $message, $subject, 'yes', 1)") or sqlerr(__FILE__, __LINE__);
    }

    $res_req_owner = $db->query("SELECT requested_by_user_id
                                FROM requests
                                WHERE id = $request") or sqlerr(__FILE__, __LINE__);

    $subject = sqlesc("{$lang['msg_subject_req_made']}");

    $message = sqlesc("{$lang['msg_hi']}\n\n{$lang['msg_request_made']}\n\n [url=$site_url/details.php?id=$id][b]" . security::html_safe($torrent) . "[/b][/url].");

    $time = sqlesc(get_date_time());

    while($arr_req_owner = $res_req_owner->fetch_assoc())
    {
         $db->query("INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
                    VALUES(0, " . (int)$arr_req_owner['requested_by_user_id'] . ", $time, $message, $subject, 'yes', 1)") or sqlerr(__FILE__, __LINE__);
    }

    $db->query("UPDATE requests
               SET filled_by_username = '" . $db->real_escape_string(user::$current['username']) . "', filled_torrent_id = '$id', filled_by_user_id = '" . user::$current['id'] . "'
               WHERE id = $request") or sqlerr(__FILE__, __LINE__);

/*
    $db->query("DELETE FROM requests WHERE id = $request");
    $db->query("DELETE FROM request_votes WHERE request_id = $request");
    $db->query("DELETE FROM comments WHERE request = $request");
*/

    write_log("{$lang['writelog_request']}$id ($torrent){$lang['writelog_filled']}" . user::$current['username']);

    $filled = 1;
}
//-- Finish Requests And Offers Notifications --//

write_log("{$lang['writelog_torrent']}$id ($torrent){$lang['writelog_uploaded']}" . user::$current['username']);

//-- RSS Feeds --//

if (($fd1 = @fopen("rss.xml", "w")) && ($fd2 = fopen("rssdd.xml", "w")))
{
    $cats = '';
    $res  = $db->query("SELECT id, name
                       FROM categories");

    while ($arr = $res->fetch_assoc())
    {
        $cats[$arr['id']] = $arr['name'];
    }

    $s = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n<rss version=\"0.91\">\n<channel>\n" . "<title>$site_name</title>\n<description>0-week torrents</description>\n<link>$site_url/</link>\n";

    @fwrite($fd1, $s);
    @fwrite($fd2, $s);

    $r = $db->query("SELECT id, name, descr, filename, category
                    FROM torrents
                    ORDER BY added DESC
                    LIMIT 15") or sqlerr(__FILE__, __LINE__);

    while ($a = $r->fetch_assoc())
    {
        $cat = $cats[$a['category']];

        $s = "<item>\n<title>".security::html_safe($a['name'] . " ($cat)") . "</title>\n" . "<description>" . security::html_safe($a['descr']) . "</description>\n";

        @fwrite($fd1, $s);
        @fwrite($fd2, $s);
        @fwrite($fd1, "<link>$site_url/details.php?id={$a['id']}&amp;hit=1</link>\n</item>\n");
        $filename = security::html_safe($a['filename']);
        @fwrite($fd2, "<link>$site_url/download.php/{$a['id']}/$filename</link>\n</item>\n");
    }
    $s = "</channel>\n</rss>\n";

    @fwrite($fd1, $s);
    @fwrite($fd2, $s);
    @fclose($fd1);
    @fclose($fd2);
}
/*
//-- Email Notifs --//

$res = $db->query("SELECT name
                  FROM categories
                  WHERE id = $catid") or sqlerr();

$arr = $res->fetch_assoc();
$cat = $arr['name'];
$res = $db->query("SELECT email
                  FROM users
                  WHERE enabled = 'yes'
                  AND notifs LIKE '%[cat$catid]%'") or sqlerr();

$uploader = user::$current['username'];

$size = misc::mksize($totallen);
$description = ($html ? strip_tags($descr) : $descr);

$body = <<<EOD
{$lang['email_uploaded']}

{$lang['email_name']}$torrent
{$lang['email_size']}$size
{$lang['email_cat']}$cat
{$lang['email_upload_by']}$uploader

{$lang['email_desc']}
-------------------------------------------------------------------------------
$description
-------------------------------------------------------------------------------

{$lang['email_url']}

$site_url/details.php?id=$id&hit=1

--
$site_name
EOD;
$to = "";
$nmax = 100; // Max recipients per message
$nthis = 0;
$ntotal = 0;
$total = $res->num_rows;
while ($arr = $res->fetch_row())
{
    if ($nthis == 0)
    {
        $to = $arr[0];
    }
    else
    {
        $to .= ",".$arr[0];
    }
    ++$nthis;
    ++$ntotal;
    if ($nthis == $nmax || $ntotal == $total)
    {
        if (!mail("{$lang['email_']}recipients<$site_email>", "{$lang['email_new_tor']}$torrent", $body,
            "{$lang['email_from']}$site_email\r\n{$lang['email_bcc']}$to", "-f$site_email"))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['text_notif_err']}");
        }
        $nthis = 0;
    }
}
*/

header("Location: $site_url/details.php?id=$id&uploaded=1");

?>