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

//-- Bleach Forums Improved and Optimized for TBDEV.NET by Alex2005 --//

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_torrenttable.php');
require_once(FUNC_DIR . 'function_bbcode.php');
require_once(FUNC_DIR . 'function_forum.php');

db_connect(true);
logged_in();

$lang = array_merge(load_language('forums'),
                    load_language('func_vfunctions'),
                    load_language('func_bbcode'),
                    load_language('global'));

parked();

//-- Configs Start --//

//-- Set's The Max File Size In php.ini, No Need To Change  --//
ini_set("upload_max_filesize", $maxfilesize);

//-- The Extensions That Are Allowed To Be Uploaded By The Users  --//
//-- Note: You Need To Have The Pics In The $image_dir Folder, ie zip.gif, rar.gif  --//
$allowed_file_extensions = array('rar',
                                 'zip');

//--  Just A Check, So That The Default URL, Wont Have A Ending Backslash (to Double Backslash The Links), No Need To Edit Or Delete --//
$site_url_rev = strrev($site_url);

if ($site_url_rev[0] == '/')
{
    $site_url_rev[0] = '';
    $site_url        = strrev($site_url_rev);
}

//-- Configs End --//
$action = (isset($_GET['action']) ? security::html_safe($_GET['action']) : (isset($_POST['action']) ? security::html_safe($_POST['action']) : ''));

if ($action == 'updatetopic' && user::$current['class'] >= UC_MODERATOR)
{
    $topicid = (isset($_GET['topicid']) ? (int) $_GET['topicid'] : (isset($_POST['topicid']) ? (int) $_POST['topicid'] : 0));

    if (!is_valid_id($topicid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang[err_inv_topic_id]}");
    }

    $topic_res = $db->query('SELECT t.sticky, t.locked, t.subject, t.forumid, f.minclasswrite,
                           (SELECT COUNT(id) FROM posts WHERE topicid = t.id) AS post_count
                           FROM topics AS t
                           LEFT JOIN forums AS f ON f.id = t.forumid
                           WHERE t.id = ' . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

    if ($topic_res->num_rows == 0)
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_no_topic_id']}");
    }

    $topic_arr = $topic_res->fetch_assoc();

    if (user::$curret['class'] < (int) $topic_arr['minclasswrite'])
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_unauth_edit_topic']}");
    }

    $forumid = (int) $topic_arr['forumid'];
    $subject = security::html_safe($topic_arr['subject']);

    if ((isset($_GET['delete']) ? $_GET['delete'] : (isset($_POST['delete']) ? $_POST['delete'] : '')) == 'yes')
    {
        if ((isset($_GET['sure']) ? $_GET['sure'] : (isset($_POST['sure']) ? $_POST['sure'] : '')) != 'yes')
        {
            error_message_center("error",
                                 "{$lang['gbl_sanity']}",
                                 "{$lang['text_del_topic_sure']}<br /><br />
                                 <a class='btn' href='forums.php?action=$action&amp;topicid=$topicid&amp;delete=yes&amp;sure=yes'>{$lang['text_click_confirm']}</a>");
        }

        write_log("{$lang['log_del_topic']}<span style='font-weight : bold;'>$subject</span>{$lang['log_del_topic_by']}<a class='altlink_user' href='$site_url/userdetails.php?id=" . user::$current['id'] . "'>" . user::$current['username'] . "</a>.");

        if ($use_attachment_mod)
        {
            $res = $db->query("SELECT attachments.filename
                              FROM posts
                              LEFT JOIN attachments ON attachments.postid = posts.id
                              WHERE posts.topicid = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

            while ($arr = $res->fetch_assoc())
            {
                if (!empty($arr['filename']) && is_file($attachment_dir . "/" . $arr['filename']))
                {
                    unlink($attachment_dir . "/" . $arr['filename']);
                }
            }
        }

        $db->query("DELETE posts, topics " . ($use_attachment_mod ? ", attachments, attachmentdownloads " : "") . ($use_poll_mod ? ", postpolls, postpollanswers " : "") . "
                   FROM topics
                   LEFT JOIN posts ON posts.topicid = topics.id " . ($use_attachment_mod ? "
                   LEFT JOIN attachments ON attachments.postid = posts.id
                   LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " : "") . ($use_poll_mod ? "
                   LEFT JOIN postpolls ON postpolls.id = topics.pollid
                   LEFT JOIN postpollanswers ON postpollanswers.pollid = postpolls.id " : "") . "
                   WHERE topics.id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

        header('Location: ' . security::esc_url($_SERVER['PHP_SELF']) . '?action=viewforum&forumid=' . $forumid);
        exit();
    }

    $returnto  = security::esc_url($_SERVER['PHP_SELF']) . '?action=viewtopic&topicid=' . $topicid;
    $updateset = array();
    $locked    = ($_POST['locked'] == 'yes' ? 'yes' : 'no');

    if ($locked != $topic_arr['locked'])
    {
        $updateset[] = 'locked = ' . sqlesc($locked);
    }

    $sticky = ($_POST['sticky'] == 'yes' ? 'yes' : 'no');

    if ($sticky != $topic_arr['sticky'])
    {
        $updateset[] = 'sticky = ' . sqlesc($sticky);
    }

    $new_subject = security::html_safe($_POST['subject']);

    if ($new_subject != $subject)
    {
        if (empty($new_subject))
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_missing_topic_name']}");
        }

        $updateset[] = 'subject = ' . sqlesc($new_subject);
    }

    $new_forumid = (int) $_POST['new_forumid'];

    if (!is_valid_id($new_forumid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_forum_id']}");
    }

    if ($new_forumid != $forumid)
    {
        $post_count = (int) $topic_arr['post_count'];

        $res = $db->query("SELECT minclasswrite
                          FROM forums
                          WHERE id = " . sqlesc($new_forumid)) or sqlerr(__FILE__, __LINE__);

        if ($res->num_rows != 1)
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_missing_forum']}");
        }

        $arr = $res->fetch_assoc();

        if (user::$current['class'] < (int) $arr['minclasswrite'])
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_inv_forum_move']}");
        }

        $updateset[] = 'forumid = ' . sqlesc($new_forumid);

        $db->query("UPDATE forums
                   SET topiccount = topiccount - 1, postcount = postcount - " . sqlesc($post_count) . "
                   WHERE id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

        $db->query("UPDATE forums
                   SET topiccount = topiccount + 1, postcount = postcount + " . sqlesc($post_count) . "
                   WHERE id = " . sqlesc($new_forumid)) or sqlerr(__FILE__, __LINE__);

        $returnto = security::esc_url($_SERVER['PHP_SELF']) . '?action=viewforum&forumid=' . $new_forumid;
    }

    if (sizeof($updateset) > 0)
    {
        $db->query("UPDATE topics
                   SET " . implode(', ', $updateset) . "
                   WHERE id = " . sqlesc($topicid));
		
		$Memcache->delete_value('get::topic::forum::' . $topicid);
    }

    header('Location: ' . $returnto);
    exit();
}

else if ($action == 'newtopic') //-- Action: New Topic --//
{
    $forumid = (int) $_GET['forumid'];

    if (!is_valid_id($forumid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_id']}");
    }

    site_header("{$lang['title_new_topic']}", false);
    insert_compose_frame($forumid, true, false, true);

    site_footer();

    exit();
}

  //-------- Action: Delete topic

else if ($action == 'deletetopic')
{
    $topicid = (int) $_GET['topicid'];
    $forumid = (int) $_GET['forumid'];

    if (!is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      die;

    $sure = (int) $_GET['sure'];

    if (!$sure)
    {
        error_message_center("error",
                             "{$lang['gbl_sanity']}",
                             "<a class='btn' href=?action=deletetopic&topicid=$topicid&sure=1>{$lang['text_del_topic_sure']}</a>");
    }

    if ($sure == '1')
    {

        $res = $db->query("SELECT id, filename
                          FROM attachments
                          WHERE topicid = $topicid") or sqlerr(__FILE__, __LINE__);

        $arr = $res->fetch_assoc();

        $att_id = (int) $arr['id'];

        $db->query("DELETE
                   FROM topics
                   WHERE id = $topicid") or sqlerr(__FILE__, __LINE__);
		
		$Memcache->delete_value('get::topic::forum::' . $topicid);

        $db->query("DELETE
                   FROM posts
                   WHERE topicid = $topicid") or sqlerr(__FILE__, __LINE__);

        $db->query("DELETE
                   FROM attachments
                   WHERE topicid = $topicid") or sqlerr(__FILE__, __LINE__);

        $db->query("DELETE
                   FROM attachmentdownloads
                   WHERE fileid = $att_id") or sqlerr(__FILE__, __LINE__);


            if ($use_attachment_mod && !empty($arr['filename']))
            {
                $filename = $attachment_dir . "/" . $arr['filename'];

                if (is_file($filename))
                {
                    unlink($filename);
                }
            }

        header ("Refresh: 3; url=forums.php");

        error_message_center("info",
                             "{$lang['gbl_success']}",
                             "{$lang['text_topic_deleted']}");

    }
}

else if ($action == 'post') //-- Action: Post --//
{
    $forumid = (isset($_POST['forumid']) ? (int) $_POST['forumid'] : NULL);

    if (isset($forumid) && !is_valid_id($forumid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_forum_id']}");
    }

    $topicid = (isset($_POST['topicid']) ? (int) $_POST['topicid'] : NULL);

    if (isset($topicid) && !is_valid_id($topicid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_topic_id']}");
    }

    $newtopic = is_valid_id($forumid);

    $subject = (isset($_POST['subject']) ? $_POST['subject'] : '');

    if ($newtopic)
    {
        $subject = trim($subject);

        if (empty($subject))
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_no_subject']}");
        }

        if (strlen($subject) > $maxsubjectlength)
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['text_subject_limit']}" . $maxsubjectlength . "{$lang['text_characters']}");
        }
    }
    else
    {
        $forumid = cached::get_topic_forum($topicid) or die ("{$lang['err_bad_topic_id']}");
    }

    if (user::$current['forumpos'] == 'no')
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_unauth_post']})");
    }

    //-- Make Sure Sure User Has Write Access In Forum --//
    $arr = cached::get_forum_access_levels($forumid) or die ("{$lang['err_bad_forum_id']}");

    if (user::$current['class'] < $arr['write'] || ($newtopic && user::$current['class'] < $arr['create']))
    {
        error_message_center("warn",
                      "{$lang['gbl_warning']}",
                      "{$lang['err_perm_denied']}");
    }

    $body = trim($_POST['body']);

    if (empty($body))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_no_body']}");
    }

    if (substr_count(strtolower($body), '[quote') > 3)
    {
        error_message_center("info",
                      "{$lang['gbl_sorry']}",
                      "{$lang['err_quote_reached']}");
    }

    $userid = user::$current['id'];

    if ($use_flood_mod && user::$current['class'] < UC_MODERATOR)
    {
        $res = $db->query("SELECT COUNT(id) AS c
                          FROM posts
                          WHERE userid = " . user::$current['id'] . "
                          AND added > '" . get_date_time(gmtime() - ($minutes * 60)) . "'");

        $arr = $res->fetch_assoc();

        if ($arr['c'] > $limit)
        {
            error_message_center("info",
                          "{$lang['err_flood']}",
                          "{$lang['text_more_than']}" . $limit . "{$lang['text_posts_last']}" . $minutes . "{$lang['text_mins']}");
        }
    }

    if ($newtopic)
    {
        $db->query("INSERT INTO topics (userid, forumid, subject)
                   VALUES ($userid, $forumid, " . sqlesc($subject) . ")") or sqlerr(__FILE__, __LINE__);

        $topicid = $db->insert_id or error_message_center("error",
                                                      "{$lang['gbl_error']}",
                                                      "{$lang['err_no_topic_ret']}");

        $db->query("INSERT INTO posts (topicid, userid, added, body)
                   VALUES ($topicid, $userid, " . sqlesc(get_date_time()) . ", " . sqlesc($body) . ")") or sqlerr(__FILE__, __LINE__);

	    $Memcache->delete_value('user::profile::forum::posts::' . $userid);
		$Memcache->delete_value('get::topic::forum::' . $topicid);

        $postid = $db->insert_id or error_message_center("error",
                                                     "{$lang['gbl_error']}",
                                                     "{$lang['err_no_post_ret']}");
    }
    else
    {
        //-- Make Sure Topic Exists And Is Unlocked --//
        $res = $db->query("SELECT locked
                          FROM topics
                          WHERE id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

        if ($res->num_rows == 0)
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_non_exist_topic']}");
        }

        $arr = $res->fetch_assoc();

        if ($arr['locked'] == 'yes' && user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("info",
                          "{$lang['gbl_info']}",
                          "{$lang['err_topic_locked']}");
        }

        //-- Check Double Post --//
        $doublepost = $db->query("SELECT p.id, p.added, p.userid, p.body, t.lastpost, t.id
                                 FROM posts AS p
                                 INNER JOIN topics AS t ON p.id = t.lastpost
                                 WHERE t.id = $topicid
                                 AND p.userid = $userid
                                 AND p.added > " . sqlesc(get_date_time(gmtime() - 1 * 86400)) . "
                                 ORDER BY p.added DESC
                                 LIMIT 1") or sqlerr(__FILE__, __LINE__);

        if ($doublepost->num_rows == 0 || user::$current['class'] >= UC_MODERATOR)
        {
            $db->query("INSERT INTO posts (topicid, userid, added, body)
                       VALUES ($topicid, $userid, " . sqlesc(get_date_time()) . ", " . sqlesc($body) . ")") or sqlerr(__FILE__, __LINE__);
		
		    $Memcache->delete_value('user::profile::forum::posts::' . $userid);

            $postid = $db->insert_id or die ("{$lang['err_post_id_na']}");
        }
        else
        {
            $results = $doublepost->fetch_assoc();
            $postid  = (int) $results['lastpost'];

            $db->query("UPDATE posts
                       SET body = " . sqlesc(trim($results['body']) . "\n\n" . $body) . ", editedat = " . sqlesc(get_date_time()) . ", editedby = $userid
                       WHERE id = $postid") or sqlerr(__FILE__, __LINE__);
        }
    }

    update_topic_last_post($topicid);

    if ($use_attachment_mod && ((isset($_POST['uploadattachment']) ? $_POST['uploadattachment'] : '') == 'yes'))
    {
        $file        = $_FILES['file'];
        $fname       = trim(stripslashes($file['name']));
        $size        = $file['size'];
        $tmpname     = $file['tmp_name'];
        $tgtfile     = $attachment_dir . "/" . $fname;
        $pp          = pathinfo($fname = $file['name']);
        $error       = $file['error'];
        $type        = $file['type'];
        $uploaderror = '';

        if (empty($fname))
        {
            $uploaderror = "{$lang['err_inv_filename']}";
        }

        if (!validfilename($fname))
        {
            $uploaderror = "{$lang['err_inv_filename']}";
        }

        foreach ($allowed_file_extensions
                 AS
                 $allowed_file_extension)

        if (!preg_match('/^(.+)\.[' . join(']|[', $allowed_file_extensions) . ']$/si', $fname, $matches))
        {
            $uploaderror = "{$lang['err_allowed_ext']}" . join(", ", $allowed_file_extensions) . ".";
        }

        if ($size > $maxfilesize)
        {
            $uploaderror = error_message_center("warn",
                                         "{$lang['gbl_sorry']}",
                                         "{$lang['err_large_file']}");
        }

        if ($pp['basename'] != $fname)
        {
            $uploaderror = error_message_center("warn",
                                         "{$lang['gbl_sorry']}",
                                         "{$lang['err_bad_filename']}");
        }

        if (file_exists($tgtfile))
        {
            $uploaderror = error_message_center("warn",
                                         "{$lang['gbl_sorry']}",
                                         "{$lang['err_dupe_file']}");
        }

        if (!is_uploaded_file($tmpname))
        {
            $uploaderror = error_message_center("warn",
                                         "{$lang['gbl_sorry']}",
                                         "{$lang['err_no_upload']}");
        }

        if (!filesize($tmpname))
        {
            $uploaderror = error_message_center("warn",
                                         "{$lang['gbl_sorry']}",
                                         "{$lang['err_file_empty']})");
        }

        if ($error != 0)
        {
            $uploaderror = error_message_center("error",
                                         "{$lang['gbl_sorry']}",
                                         "{$lang['err_upload_error']}");
        }

        if (empty($uploaderror))
        {
            $db->query("INSERT INTO attachments (topicid, postid, filename, size, owner, added, type)
                       VALUES ('$topicid', '$postid', " . sqlesc($fname) . ", " . sqlesc($size) . ", '$userid', " . sqlesc(get_date_time()) . ", " . sqlesc($type) . ")") or sqlerr(__FILE__, __LINE__);

            move_uploaded_file($tmpname, $tgtfile);
        }
    }

    $headerstr = "Location: forums.php?action=viewtopic&topicid=$topicid" . ($use_attachment_mod && !empty($uploaderror) ? "&uploaderror=$uploaderror" : "") . "&page=last";

    header($headerstr . ($newtopic ? '' : "#$postid"));
    exit();
}
else if ($action == 'viewtopic') //-- Action: View Topic --//
{
    $userid = user::$current['id'];

    if ($use_poll_mod && $_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $choice = $_POST['choice'];
        $pollid = (int) $_POST['pollid'];

        if (ctype_digit($choice) && $choice < 256 && $choice == floor($choice))
        {
            $res = $db->query("SELECT pa.id
                              FROM postpolls AS p
                              LEFT JOIN postpollanswers AS pa ON pa.pollid = p.id AND pa.userid = " . sqlesc($userid) . "
                              WHERE p.id = " . sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);

            $arr = $res->fetch_assoc() or error_message_center("error",
                                                            "{$lang['gbl_sorry']}",
                                                            "{$lang['err_no_poll']}");

            if (is_valid_id($arr['id']))
            {
                error_message_center("error",
                              "{$lang['gbl_error']}",
                              "{$lang['err_dupe_vote']}");
            }

            $db->query("INSERT INTO postpollanswers
                       VALUES(id, " . sqlesc($pollid) . ", " . sqlesc($userid) . ", " . sqlesc($choice) . ")") or sqlerr(__FILE__, __LINE__);

            if ($db->affected_rows != 1)
            {
                error_message_center("error",
                              "{$lang['gbl_error']}",
                              "{$lang['err_vote_error']}");
            }
        }
        else
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_select_opt']}");
        }
    }

    $topicid = (int) $_GET['topicid'];

    if (!is_valid_id($topicid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_topic_id']}");
    }

    $page = (isset($_GET['page']) ? $_GET['page'] : 0);

    //-- Get topic info
    $res = $db->query("SELECT " . ($use_poll_mod ? 't.pollid, ' : '') . "t.locked, t.subject, t.sticky, t.userid AS t_userid, t.forumid, f.name AS forum_name, f.minclassread, f.minclasswrite, f.minclasscreate, (SELECT COUNT(id) FROM posts WHERE topicid = t.id) AS p_count
                      FROM topics AS t
                      LEFT JOIN forums AS f ON f.id = t.forumid
                      WHERE t.id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

    $arr = $res->fetch_assoc() or error_message_center("error",
                                                    "{$lang['gbl_error']}",
                                                    "{$lang['err_topic_found']}");

    $res->free();

    ($use_poll_mod ? $pollid = (int) $arr['pollid'] : NULL);

    $t_userid  = (int) $arr['t_userid'];
    $locked    = ($arr['locked'] == 'yes' ? true : false);
    $subject   = $arr['subject'];
    $sticky    = ($arr['sticky'] == 'yes' ? true : false);
    $forumid   = (int) $arr['forumid'];
    $forum     = $arr['forum_name'];
    $postcount = (int) $arr['p_count'];

    if (user::$current['class'] < $arr['minclassread'])
    {
        error_message_center("warn",
                      "{$lang['gbl_warning']}",
                      "{$lang['err_deny_view_topic']}");
    }

    //-- Update Hits Column --//
    $db->query("UPDATE topics
               SET views = views + 1
               WHERE id = $topicid") or sqlerr(__FILE__, __LINE__);

    //-- Make Page Menu--//
    $pagemenu1 = "<p align='center'>";
    //$perpage   = $postsperpage;
    $perpage       = (empty(user::$current['postsperpage']) ? 25 : user::$current['postsperpage']); # Get The Users Posts Per Page, No Need To Change
    $pages     = ceil($postcount / $perpage);

    if ($page[0] == 'p')
    {
        $findpost = substr($page, 1);

        $res = $db->query("SELECT id
                          FROM posts
                          WHERE topicid = $topicid
                          ORDER BY added") or sqlerr(__FILE__, __LINE__);

        $i = 1;

        while ($arr = $res->fetch_row())
        {
            if ($arr[0] == $findpost)
            {
                break;
            }
            ++$i;
        }
        $page = ceil($i / $perpage);
    }

    if ($page == 'last')
    {
        $page = $pages;
    }
    else
    {
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
    }

    $offset    = ((int) $page * $perpage) - $perpage;
    $offset    = ($offset < 0 ? 0 : $offset);
    $pagemenu2 = '';

    for ($i = 1;
         $i <= $pages;
         ++$i)
    {
        $pagemenu2 .= ($i == $page ? "[<span style='text-decoration : underline; font-weight : bold;'>$i</span>]" : "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=$i'><span style='font-weight : bold;'>$i</span></a>");
    }

    $pagemenu1 .= ($page == 1 ? "<span style='font-weight : bold;'>&lt;&lt;&nbsp;{$lang['text_prev']}</span>" : "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=" . ($page - 1) . "'><span style='font-weight : bold;'>&lt;&lt;&nbsp;{$lang['text_prev']}</span></a>");

    $pmlb = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    $pagemenu3 = ($page == $pages ? "<span style='font-weight : bold;'>{$lang['text_next']}&nbsp;&gt;&gt;</span></p>" : "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=" . ($page + 1) . "'><span style='font-weight : bold;'>{$lang['text_next']}&nbsp;&gt;&gt;</span></a></p>");

    site_header("{$lang['title_view_topic']}$subject");

    if ($use_poll_mod && is_valid_id($pollid))
    {
        $res = $db->query("SELECT p.*, pa.id AS pa_id, pa.selection
                          FROM postpolls AS p
                          LEFT JOIN postpollanswers AS pa ON pa.pollid = p.id AND pa.userid = " . user::$current['id'] . "
                          WHERE p.id = " . sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);

        if ($res->num_rows > 0)
        {
            $arr1 = $res->fetch_assoc();

            $userid   = user::$current['id'];
            $question = security::html_safe($arr1['question']);

            $o = array($arr1['option0'],
                       $arr1['option1'],
                       $arr1['option2'],
                       $arr1['option3'],
                       $arr1['option4'],
                       $arr1['option5'],
                       $arr1['option6'],
                       $arr1['option7'],
                       $arr1['option8'],
                       $arr1['option9'],
                       $arr1['option10'],
                       $arr1['option11'],
                       $arr1['option12'],
                       $arr1['option13'],
                       $arr1['option14'],
                       $arr1['option15'],
                       $arr1['option16'],
                       $arr1['option17'],
                       $arr1['option18'],
                       $arr1['option19']);

            ?>
        <table cellpadding='5' width='<?php echo $forum_width; ?>' align='center'>
            <tr>
                <td class='colhead' align='left'><h2><?php echo $lang['table_poll']?>
                    <?php

                    if ($userid == $t_userid || user::$current['class'] >= UC_MODERATOR)
                    {
                        ?>
                        <span style='font-size : xx-small; font-weight : bold;'> - [<a href='forums.php?action=makepoll&amp;subaction=edit&amp;pollid=<?php echo $pollid; ?>'><?php echo $lang['table_edit']?></a>]</span><?php

                        if (user::$current['class'] >= UC_MODERATOR)
                        {
                            ?>
                            <span style='font-size : xx-small; font-weight : bold;'> - [<a href='forums.php?action=deletepoll&amp;pollid=<?php echo $pollid; ?>'><?php echo $lang['table_delete']?></a>]</span>
                            <?php
                        }
                    }
                    ?>
                </h2></td>
            </tr>
            <tr>
                <td align='center'>
                    <table width='55%'>
                        <tr>
                            <td class='rowhead'>
                                <div align='center'>
                                    <span style='font-weight : bold;'><?php echo $question; ?></span>
                                </div>
                                <?php

                                $voted = (is_valid_id($arr1['pa_id']) ? true : false);

                                if (($locked && user::$current['class'] < UC_MODERATOR) ? true : $voted)
                                {
                                    $uservote = ($arr1['selection'] != '' ? (int) $arr1['selection'] : -1);

                                    $res3 = $db->query("SELECT selection
                                                       FROM postpollanswers
                                                       WHERE pollid = " . sqlesc($pollid) . "
                                                       AND selection < 20");

                                    $tvotes = $res3->num_rows;

                                    $vs = $os = array();

                                    while ($arr3 = $res3->fetch_row())
                                    {
                                        $vs[$arr3[0]] += 1;
                                    }

                                    reset($o);

                                    for ($i = 0;
                                         $i < count($o);
                                         ++$i)
                                    {
                                        if ($o[$i])
                                        {
                                            $os[$i] = array($vs[$i],
                                                            $o[$i]);
                                        }
                                    }

                                    function srt($a, $b)
                                    {
                                        if ($a[0] > $b[0])
                                        {
                                            return -1;
                                        }

                                        if ($a[0] < $b[0])
                                        {
                                            return 1;
                                        }

                                        return 0;
                                    }

                                    if ($arr1['sort'] == 'yes')
                                    {
                                        usort($os, 'srt');
                                    }

                                    ?>
                                    <br />
                                    <table width='100%' cellpadding='5'>

                                        <?php

                                        for ($i = 0;
                                             $a = $os[$i];
                                             ++$i)
                                        {
                                            if ($i == $uservote)
                                            {
                                                $a[1] .= " *";
                                            }

                                            $p = ($tvotes == 0 ? 0 : round($a[0] / $tvotes * 100));
                                            $c = ($i % 2 ? '' : 'poll');

                                            ?>

                                            <tr>
                                                <td class='main<?php echo $c; ?>' width='1%' style='padding : 3px;' >
                                                    <div style='white-space : nowrap;'><?php echo security::html_safe($a[1]); ?></div>
                                                </td>
                                                <td class='main<?php echo $c; ?>' align='center' width='99%' >
                                                    <img src='<?php echo $image_dir; ?>bar_left.gif' width='2' height='9' border='0' alt='' title='' /><img src='<?php echo $image_dir; ?>bar.gif' width='<?php echo ($p * 3); ?>' height='9' border='0' alt='' title='' /><img src='<?php echo $image_dir; ?>bar_right.gif' width='2' height='9' border='0' alt='' title='' />&nbsp;<?php echo $p; ?>%
                                                </td>
                                            </tr>

                                        <?php
                                        }
                                        ?>

                                    </table>
                                    <p align='center'><?php echo $lang['table_votes']?><span style='font-weight : bold;'><?php echo number_format($tvotes); ?></span></p>

                                    <?php
                                }
                                else
                                {
                                    ?>

                                    <form method='post' action='forums.php?action=viewtopic&amp;topicid=<?php echo $topicid; ?>'>
                                        <input type='hidden' name='pollid' value='<?php echo $pollid; ?>' />

                                        <?php

                                        for ($i = 0;
                                             $a = $o[$i];
                                             ++$i)
                                        {
                                            echo "<input type='radio' name='choice' value='$i' />" . security::html_safe($a) . "<br />";
                                        }

                                        ?>

                                        <br />

                                        <p align='center'><input type='submit' class='btn' value='<?php echo $lang['btn_vote']?>' /></p>
                                    </form>

                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <?php

            $listvotes = (isset($_GET['listvotes']) ? true : false);

            if (user::$current['class'] >= UC_ADMINISTRATOR)
            {
                if (!$listvotes)
                {
                    echo "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;listvotes'>{$lang['text_voters']}</a>";
                }
                else
                {
                    $res4 = $db->query("SELECT pa.userid, u.username
                                       FROM postpollanswers AS pa
                                       LEFT JOIN users AS u ON u.id = pa.userid
                                       WHERE pa.pollid = " . sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);
                    $voters = '';

                    while ($arr4 = $res4->fetch_assoc())
                    {
                        if (!empty($voters) && !empty($arr4['username']))
                        {
                            $voters .= ', ';
                        }

                        $voters .= "<a class='altlink_user' href='$site_url/userdetails.php?id=" . (int) $arr4['userid'] . "'><span style='font-weight : bold;'>" . security::html_safe($arr4['username']) . "</span></a>";
                    }

                    echo $voters." (<span style='font-size : xx-small;'><a href='" . security::esc_url($_SERVER['PHP_SELF']) . "?action=viewtopic&amp;topicid=$topicid'>{$lang['text_hide']}</a></span>)";
                }
            }
        }
        else
        {
            ?>

        <br />

        <?php
            error_message_center("error",
                          "{$lang['gbl_sorry']}",
                          "{$lang['err_non_exist_poll']}");
        }
        ?>
    <br />
    <?php
    }
    ?>

<a name='top'></a>
<h1 align='left'><a href='forums.php?action=viewforum&amp;forumid=<?php echo $forumid; ?>'><?php echo $forum; ?>
</a> &gt; <?php echo security::html_safe($subject); ?></h1>
<?php

    //-- Print table
    begin_frame();

    $res = $db->query("SELECT p.id, p.added, p.userid, p.added, p.body, p.editedby, p.editedat, u.id AS uid, u.username AS uusername, u.class, u.avatar, u.donor, u.title, u.country, u.enabled, u.warned, u.uploaded, u.downloaded, u.signature, u.last_access, (SELECT COUNT(id) FROM posts WHERE userid = u.id) AS posts_count, u2.username AS u2_username " . ($use_attachment_mod ? ", at.id AS at_id, at.filename AS at_filename, at.postid AS at_postid, at.size AS at_size, at.downloads AS at_downloads, at.owner AS at_owner " : "") . ", (SELECT lastpostread FROM readposts WHERE userid = " . sqlesc(user::$current['id']) . "" . "AND topicid = p.topicid LIMIT 1) AS lastpostread " . "FROM posts AS p " . "LEFT JOIN users AS u ON p.userid = u.id " . ($use_attachment_mod ? "LEFT JOIN attachments AS at ON at.postid = p.id " : "") . "LEFT JOIN users AS u2 ON u2.id = p.editedby " . "WHERE p.topicid = " . sqlesc($topicid) . " ORDER BY id LIMIT $offset, $perpage") or sqlerr(__FILE__, __LINE__);

    $pc = $res->num_rows;
    $pn = 0;

    while ($arr = $res->fetch_assoc())
    {
        ++$pn;

        $lpr      = $arr['lastpostread'];
        $postid   = (int) $arr['id'];
        $postadd  = $arr['added'];
        $posterid = (int) $arr['userid'];
        $added    = $arr['added'] . "{$lang['table_gmt']}<span style='font-size : x-small;'>(" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr['added']))) . ")</span>";

        //-- Get Poster Details --//
        $uploaded    = misc::mksize($arr['uploaded']);
        $downloaded  = misc::mksize($arr['downloaded']);
        $last_access = $arr['last_access'];

        if ($arr['downloaded'] > 0)
        {
            $ratio = $arr['uploaded'] / $arr['downloaded'];
            $color = get_ratio_color($ratio);
            $ratio = number_format($ratio, 3);

            if ($color)
            {
                $ratio = "<span style='color : $color'>$ratio</span>";
            }
        }
        else {
            if ($arr['uploaded'] > 0)
            {
                $ratio = "&infin;";
            }
            else
            {
                $ratio = "---";
            }
        }

        if (($postid > $lpr) && ($postadd > (get_date_time(gmtime() - $posts_read_expiry))))
        {
            $newp = "&nbsp;&nbsp;<span class='red'>({$lang['table_new']})</span>";
        }

        $signature = (user::$current['signatures'] == 'yes' ? format_comment($arr['signature']) : '');

        $postername = $arr['uusername'];

        $avatar = (!empty($postername) ? ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? security::html_safe($arr['avatar']) : '') : '');

        $title = (!empty($postername) ? (empty($arr['title']) ? "(" . get_user_class_name($arr['class']) . ")" : "(" . format_comment($arr['title']) . ")") : '');

        $forumposts = (!empty($postername) ? ($arr['posts_count'] != 0 ? $arr['posts_count'] : 'N/A') : 'N/A');

        $by = (!empty($postername) ? "<a class='altlink_user' href='$site_url/userdetails.php?id=$posterid'>$postername</a>" . ($arr['donor'] == 'yes' ? "<img src='{$image_dir}star.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_donor']}' title='{$lang['gbl_img_alt_donor']}'  />" : '') . ($arr['enabled'] == 'no' ? "<img src='{$image_dir}disabled.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_ac_disabled']}' title='{$lang['gbl_img_alt_ac_disabled']}' style='margin-left : 2px' />" : ($arr['warned'] == 'yes' ? "<img src='{$image_dir}warned.png' width='16' height='16' border='0' alt='{$lang['gbl_img_alt_warned']}' title='{$lang['gbl_img_alt_warned']}' />" : '')) : "unknown[$posterid]");

        if (empty($avatar))
        {
            $avatar = $image_dir . "default_avatar.gif";
        }

        echo "<a name='$postid'></a>";
        echo ($pn == $pc ? '<a name="last"></a>' : '');

        begin_table(true);

        ?>
    <tr>
        <td class='rowhead' width='100%' colspan='2'>
            <table class='main'>
                <tr>
                    <td width='100%' style='border : none;' ><a href='forums.php?action=viewtopic&amp;topicid=<?php echo $topicid;?>&amp;page=p<?php echo $postid;?>#<?php echo $postid;?>'>#<?php echo $postid;?></a><?php echo $lang['table_by']?><?php echo $by;?> <?php echo $title;?><?php echo $lang['table_at']?><?php echo $added;
                        if (isset($newp))
                        {
                            echo ("$newp");
                        }
                        ?>

                    </td>
                    <td style='border : none;'><a href='#top'>
                        <input type='submit' class='btn' value='<?php echo $lang['btn_top']?>' /></a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <?php

        $highlight = (isset($_GET['highlight']) ? $_GET['highlight'] : '');

        $body = (!empty($highlight) ? highlight(security::html_safe(trim($highlight)), format_comment($arr['body'])) : format_comment($arr['body']));

        if (is_valid_id($arr['editedby']))
        {
            $body .= "<p><span style='font-size : xx-small;'>{$lang['table_last_edit']}<a class='altlink_user' href='$site_url/userdetails.php?id={$arr['editedby']}'><span style='font-weight : bold;'>" . security::html_safe($arr['u2_username']) ."</span></a>{$lang['table_at']}{$arr['editedat']}{$lang['table_gmt']}</span></p>";
        }

        if ($use_attachment_mod && ((!empty($arr['at_filename']) && is_valid_id($arr['at_id'])) && $arr['at_postid'] == $postid))
        {
            foreach ($allowed_file_extensions
                     AS
                     $allowed_file_extension)
            {
                if (substr($arr['at_filename'], -3) == $allowed_file_extension)
                {
                    $aimg = $allowed_file_extension;
                }
            }

            $body .= "<div style='padding : 6px'><fieldset class='fieldset'>
                    <legend>{$lang['table_attach']}</legend><br />

                    <img class='inlineimg' src='{$image_dir}{$aimg}.gif' width='16' height='16' border='0' alt='{$lang['img_alt_download']}' title='{$lang['img_alt_download']}' style='vertical-align : baseline' />&nbsp;
                    <a href='forums.php?action=attachment&amp;attachmentid={$arr['at_id']}' target='_blank'>" . security::html_safe($arr['at_filename']) . "</a> (" . misc::mksize($arr['at_size']) . ", {$arr['at_downloads']}&nbsp;{$lang['table_downloads']})&nbsp;&nbsp;
                    <input type='button' class='btn' value=\"{$lang['btn_who_download']}\" tabindex='1' onclick=\"window.open('forums.php?action=whodownloaded&amp;fileid={$arr['at_id']}', 'whodownloaded', 'toolbar=no, scrollbars=yes, resizable=yes, width=600, height=250, top=50, left=50'); return false;\" />".(user::$current['class'] >= UC_MODERATOR ? "&nbsp;&nbsp;
                    <input type='button' class='btn' value='{$lang['btn_delete']}' tabindex='2' onclick=\"window.open('forums.php?action=attachment&amp;subaction=delete&amp;attachmentid={$arr['at_id']}', 'attachment', 'toolbar=no, scrollbars=yes, resizable=yes, width=600, height=250, top=50, left=50'); return false;\" />" : "") . "<br /><br />
                    </fieldset>
                    </div>";
        }

        if (!empty($signature))
        {
            $body .= "<p style='vertical-align:bottom'><br />____________________<br /></p>" . $signature;
        }

        ?>
    <tr valign='top'>
        <td class='rowhead' align='center' width='150' style='padding : 0px'>
            <img src='<?php echo $avatar;?>' width='125' height='125' border='0' alt='' title='' /><br />
            <fieldset style='text-align : left; border : none;'>
                <div style='white-space : nowrap;'>
                    <span style='font-weight : bold;'><?php echo $lang['table_posts']?>:</span>&nbsp;&nbsp;&nbsp;<?php echo $forumposts;?><br />
                    <span style='font-weight : bold;'><?php echo $lang['table_ratio']?>:</span>&nbsp;&nbsp;&nbsp;<?php echo $ratio;?><br />
                    <span style='font-weight : bold;'><?php echo $lang['table_uploaded']?>:</span>&nbsp;&nbsp;&nbsp;<?php echo $uploaded;?><br />
                    <span style='font-weight : bold;'><?php echo $lang['table_downloaded']?>:</span>&nbsp;&nbsp;&nbsp;<?php echo $downloaded;?>
                </div>
            </fieldset>
        </td>
        <td class='rowhead' width='100%'><?php echo $body;?></td>
    </tr>
    <tr>
        <td class='rowhead'>
            <input type='submit' class='btn' value='<?php echo ($last_access > get_date_time(gmtime() - 360) || $posterid == user::$current['id'] ? $lang['btn_online'] : $lang['btn_offline'])?>' />&nbsp;

        <?php
        if ($posterid != user::$current['id'])
        {
         echo("<a href='sendmessage.php?receiver=$posterid'>
                <input type='submit' class='btn' value='{$lang['btn_pm']}' /></a>&nbsp;
                <a href='report.php?type=Post&amp;id=$postid&amp;id_2=$topicid'>
                <input type='submit' class='btn' value='{$lang['btn_report']}' />
            </a>");
        }
        ?>

        </td>
        <td class='rowhead' align='right'>

        <?php

            if (user::$current['forumpos'] == 'no')
            {
                #Do Nothing...
            }
            else
            {
                if (!$locked || user::$current['class'] >= UC_MODERATOR)
                {
                    ?>
                    <a href='forums.php?action=quotepost&amp;topicid=<?php echo $topicid; ?>&amp;postid=<?php echo $postid; ?>'>
                        <input type='submit' class='btn' value='<?php echo $lang['btn_quote']?>' />
                    </a>&nbsp;
                    <?php
                }

                if ((user::$current['id'] == $posterid && !$locked) || user::$current['class'] >= UC_MODERATOR)
                {
                    ?>
                    <a href='forums.php?action=editpost&amp;postid=<?php echo $postid; ?>'>
                        <input type='submit' class='btn' value='<?php echo $lang['btn_edit']?>' />
                    </a>
                    <?php
                }
            }

            if (user::$current['class'] >= UC_MODERATOR)
            {
                ?>

                <a href='forums.php?action=deletepost&amp;postid=<?php echo $postid; ?>'>
                    <input type='submit' class='btn' value='<?php echo $lang['btn_delete']?>' />
                </a> &nbsp;

                <?php
            }

            ?>

        </td>
    </tr>
    <?php

        end_table();

        ?>

    <br />

    <?php
    }

    if ($use_poll_mod && (($userid == $t_userid || user::$current['class'] >= UC_MODERATOR) && !is_valid_id($pollid)))
    {
        ?>
    <form method='post' action='forums.php'>
        <table width='<?php echo $forum_width; ?>' cellpadding='5'>
            <tr>
                <td align='right'>
                    <input type='hidden' name='action' value='makepoll' />
                    <input type='hidden' name='topicid' value='<?php echo $topicid; ?>' />
                    <input type='submit' class='btn' value='<?php echo $lang['btn_add_poll']?>' />
                </td>
            </tr>
        </table>
    </form>
    <br />

    <?php
    }

    if (($postid > $lpr) && ($postadd > (get_date_time(gmtime() - $posts_read_expiry))))
    {
        if ($lpr)
        {
            $db->query("UPDATE readposts
                       SET lastpostread = $postid
                       WHERE userid = $userid
                       AND topicid = $topicid") or sqlerr(__FILE__, __LINE__);
        }
        else
        {
            $db->query("INSERT INTO readposts (userid, topicid, lastpostread)
                       VALUES($userid, $topicid, $postid)") or sqlerr(__FILE__, __LINE__);
        }
    }

    //-- Mod Options --//
    if (user::$current['class'] >= UC_MODERATOR)
    {
        ?>
        <form method='post' action='forums.php'>
            <input type='hidden' name='action' value='updatetopic' />
            <input type='hidden' name='topicid' value='<?php echo $topicid; ?>' />
            <?php

            begin_table();

            ?>
            <tr>
                <td class='colhead' colspan='2'><?php echo $lang['table_options']?></td>
            </tr>

            <tr>
                <td class='rowhead' width='1%'><?php echo $lang['form_sticky']?></td>
                <td class='rowhead'>
                    <select name='sticky'>
                        <option value='yes'<?php echo ($sticky ? " selected='selected' " : ''); ?>><?php echo $lang['form_yes']?></option>
                        <option value='no'<?php echo (!$sticky ? " selected='selected' " : ''); ?>><?php echo $lang['form_no']?></option>
                    </select>
                </td>
            </tr>

            <tr>
                <td class='rowhead'><?php echo $lang['form_locked']?></td>
                <td class='rowhead'>
                    <select name='locked'>
                        <option value='yes'<?php echo ($locked ? " selected='selected' " : ''); ?>><?php echo $lang['form_yes']?></option>
                        <option value='no'<?php echo (!$locked ? " selected='selected' " : ''); ?>><?php echo $lang['form_no']?></option>
                    </select>
                </td>
            </tr>

            <tr>
                <td class='rowhead'><?php echo $lang['form_topic_name']?></td>
                <td class='rowhead'>
                    <input type='text' name='subject' size='60' maxlength='<?php echo $maxsubjectlength; ?>' value='<?php echo security::html_safe($subject); ?>' />
                </td>
            </tr>

            <tr>
                <td class='rowhead'><?php echo $lang['form_move_topic']?></td>
                <td class='rowhead'>
                    <select name='new_forumid'>
                        <?php
                        $res = $db->query("SELECT id, name, minclasswrite
                                          FROM forums
                                          ORDER BY name") or sqlerr(__FILE__, __LINE__);

                        while ($arr = $res->fetch_assoc())
                        {
                            if (user::$current['class'] >= $arr['minclasswrite'])
                            {
                                echo '<option value="' . (int) $arr['id'] . '"' . ($arr['id'] == $forumid ? ' selected="selected"' : '') . '>' . security::html_safe($arr['name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td class='rowhead'>
                    <div style='white-space : nowrap;'><?php echo $lang['form_del_topic']?></div>
                </td>
                <td class='rowhead'>
                    <select name='delete'>
                        <option value='no' selected='selected'><?php echo $lang['form_no']?></option>
                        <option value='yes'><?php echo $lang['form_yes']?></option>
                    </select>

                    <br />
                    <span style='font-weight : bold;'><?php echo $lang['text_note']?></span><?php echo $lang['text_changes']?>
                </td>
            </tr>

            <tr>
                <td colspan='2' align='center'>
                    <input type='submit' class='btn' value='<?php echo $lang['btn_update_topic']?>' />
                </td>
            </tr>

            <?php

            end_table();

            ?>

        </form>

    <?php
    }

    end_frame();

    echo $pagemenu1 . $pmlb . $pagemenu2 . $pmlb . $pagemenu3;

    if ($locked && user::$current['class'] < UC_MODERATOR)
    {
        display_message("warn",
                        "{$lang['gbl_sorry']}",
                        "{$lang['err_topic_locked']}");
    }
    else
    {
        $arr = cached::get_forum_access_levels($forumid);

        if (user::$current['class'] < $arr['write'])
        {

            display_message("warn",
                            "{$lang['gbl_sorry']}",
                            "{$lang['err_deny_post']}");

            $maypost = false;
        }
        else
        {
            $maypost = true;
        }
    }

    //-- "View Unread" / "Add Reply" Buttons --//
    ?>

    <table class='main' border='0' align='center' cellspacing='0' cellpadding='0'>
        <tr>
            <td class='embedded'>
                <form method='get' action='forums.php'>
                    <input type='hidden' name='action' value='viewunread' />
                    <input type='submit' class='btn' value='<?php echo $lang['btn_show_new']?>' />
                </form>
            </td>

            <?php

            if ($maypost)
            {
                if (user::$current['forumpos'] == 'no')
                {
                    #Do Nothing...
                }
                else
                {
                ?>
                    <td class='embedded' style='padding-left : 10px'>
                        <form method='get' action='forums.php'>
                            <input type='hidden' name='action' value='reply' />
                            <input type='hidden' name='topicid' value='<?php echo $topicid; ?>' />
                            <input type='submit' class='btn' value='<?php echo $lang['btn_answer']?>' />
                        </form>
                    </td>
                <?php
                }
            }
            ?>
        </tr>
    </table>

    <?php

    if (user::$current['forumpos'] == 'no')
    {
		#Do Nothing...
    }
    else
    {
        if ($maypost)
        {
            ?>
            <table align='center' style='border : 1px solid #000000;'>
                <tr>
                    <td style='padding : 10px; text-align : center;'>
                        <span style='font-weight : bold;'><?php echo $lang['table_quick_reply']?></span>
                        <form name='compose' method='post' action='forums.php'>
                            <input type='hidden' name='action' value='post' />
                            <input type='hidden' name='topicid' value='<?php echo $topicid; ?>' />
                            <textarea name='body' rows='4' cols='70'></textarea><br />
                            <input type='submit' class='btn' value='<?php echo $lang['gbl_btn_submit']?>' />
                        </form>
                    </td>
                </tr>
            </table>
            <?php
        }
    }

    //-- Forum Quick Jump Drop-Down --//
    insert_quick_jump_menu($forumid);

    site_footer();

    $uploaderror = (isset($_GET['uploaderror']) ? security::html_safe($_GET['uploaderror']) : '');

    if (!empty($uploaderror))
    {
        ?>
        <script>alert("<?php echo $lang['err_upload_fail']?><?php echo $uploaderror; ?>\n<?php echo $lang['text_post_saved']?>\n\n<?php echo $lang['text_click_ok']?>");</script>
        <?php
    }

    exit();
    }
    else if ($action == 'quotepost') //-- Action: Quote --//
    {
        $topicid = (int) $_GET['topicid'];

        if (!is_valid_id($topicid))
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_inv_id']}");
        }

        if (user::$current['forumpos'] == 'no')
        {
            site_header("{$lang['title_post_reply']}");

            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_deny_forum']}");

            site_footer();

            exit();
        }
        else
        {
            site_header("{$lang['title_post_reply']}");

            insert_compose_frame($topicid, false, true);

            site_footer();

            exit();
        }
    }

else if ($action == 'reply') //-- Action: Reply --//
{
    $topicid = (int) $_GET['topicid'];

    if (!is_valid_id($topicid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_id']}");
    }

    if (user::$current['forumpos'] == 'no')
    {
        site_header("{$lang['title_post_reply']}");

        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_deny_forum']}");

        site_footer();

        exit();
    }
    else
    {
        site_header("{$lang['title_post_reply']}");

        insert_compose_frame($topicid, false, false, true);

        site_footer();

        exit();
    }
}
else if ($action == 'editpost') //-- Action: Edit Post --//
{
    $postid = (int) $_GET['postid'];

    if (!is_valid_id($postid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_id']}");
    }

    $res = $db->query("SELECT p.userid, p.topicid, p.body, t.locked
                      FROM posts AS p
                      LEFT JOIN topics AS t ON t.id = p.topicid
                      WHERE p.id = " . sqlesc($postid)) or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows == 0)
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_no_post_id']}!");
    }

    $arr = $res->fetch_assoc();

    if ((user::$current['id'] != $arr['userid'] || $arr['locked'] == 'yes') && user::$current['class'] < UC_MODERATOR)
    {
        error_message_center("warn",
                      "{$lang['gbl_warning']}",
                      "{$lang['err_access_denied']}");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $body = trim($_POST['body']);

        if (empty($body))
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_body_empty']}!");
        }

        if (substr_count(strtolower($body), '[quote') > 3)
        {
            error_message_center("info",
                          "{$lang['gbl_sorry']}",
                          "{$lang['err_quote_reached']}");
        }

        $db->query("UPDATE posts
                    SET body = " . sqlesc($body) . ", editedat = " . sqlesc(get_date_time()) . ", editedby = " . user::$current['id'] . "
                    WHERE id = $postid") or sqlerr(__FILE__, __LINE__);

        header("Location: " . security::esc_url($_SERVER['PHP_SELF']) . "?action=viewtopic&topicid={$arr['topicid']}&page=p$postid#$postid");
        exit();
    }

    site_header();

    if (user::$current['forumpos'] == 'no')
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_deny_forum']}");

        site_footer();

        exit();
    }

?>
<h3><?php echo $lang['title_edit_post']?></h3>

<form name='edit' method='post' action='forums.php?action=editpost&amp;postid=<?php echo $postid; ?>'>
    <table border='1' width='100%' cellspacing='0' cellpadding='5'>
        <tr>
            <td class='rowhead' width='10%'><?php echo $lang['table_body']?></td>
            <td align='left' style='padding : 0px'>

                <?php
                $ebody = security::html_safe(unesc($arr['body']));

                if (function_exists('textbbcode'))
                {
                    echo("" . textbbcode('compose', 'body', security::html_safe($arr['body'])) . "");
                }
                else
                {
                    ?>
                    <textarea name='body' rows='7' style='width : 99%'><?php echo $ebody; ?></textarea>
                    <?php
                }
                ?>

            </td>
        </tr>
        <tr>
            <td align='center' colspan='2'>
                <input type='submit' class='btn' value='<?php echo $lang['btn_update']?>' />
            </td>
        </tr>
    </table>
</form>
<br />

<?php

    site_footer();
    exit();
}
else if ($action == 'deletepost' && user::$current['class'] >= UC_MODERATOR) //-- Action: Delete Post --//
{
    $postid = (int) $_GET['postid'];

    if (!is_valid_id($postid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_id']}");
    }

    $res = $db->query("SELECT p.topicid" . ($use_attachment_mod ? ", a.filename" : "") . ", (SELECT COUNT(id) FROM posts WHERE topicid=p.topicid) AS posts_count, (SELECT MAX(id) FROM posts WHERE topicid=p.topicid AND id < p.id) AS p_id FROM posts AS p " . ($use_attachment_mod ? "LEFT JOIN attachments AS a ON a.postid = p.id " : "") . "
                    WHERE p.id = " . sqlesc($postid)) or sqlerr(__FILE__, __LINE__);

    $arr = $res->fetch_assoc() or error_message_center("error",
                                                    "{$lang['gbl_error']}",
                                                    "{$lang['err_post_found']}");

    $topicid = (int) $arr['topicid'];

    if ($arr['posts_count'] < 2)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_only_post']}<br />{$lang['text_del_topic_instead']}<br /><br /><a class='btn' href='forums.php?action=deletetopic&amp;topicid=$topicid'>{$lang['text_click_confirm']}</a>");
    }

    $redirtopost = (is_valid_id($arr['p_id']) ? "&page=p" . $arr['p_id'] . "#" . $arr['p_id'] : '');

    $sure = (int) $_GET['sure'];

    if (!$sure)
    {
        error_message_center("warn",
                      "{$lang['gbl_sanity']}",
                      "{$lang['text_del_post_sure']}<br /><br />
                      <a class='btn' href='forums.php?action=deletepost&amp;postid=$postid&amp;sure=1'>{$lang['text_click_confirm']}</a>");
    }

    $db->query("DELETE posts.* " . ($use_attachment_mod ? ", attachments.*, attachmentdownloads.* " : "") . "FROM posts " . ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " . "LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " : "") . "WHERE posts.id = " . sqlesc($postid)) or sqlerr(__FILE__, __LINE__);

    if ($use_attachment_mod && !empty($arr['filename']))
    {
        $filename = $attachment_dir . "/" . $arr['filename'];

        if (is_file($filename))
        {
            unlink($filename);
        }
    }

    $headerstr = "Location: forums.php?action=viewtopic&amp;topicid=$topicid" . ($use_attachment_mod && !empty($uploaderror) ? "&amp;uploaderror=$uploaderror" : "") . "&amp;page=last";

    update_topic_last_post($topicid);

    header("Location: " . security::esc_url($_SERVER['PHP_SELF']) . "?action=viewtopic&topicid=" . $topicid . $redirtopost);
    exit();
}
else if ($use_poll_mod && ($action == 'deletepoll' && user::$current['class'] >= UC_MODERATOR))
{
    $pollid = (int) $_GET['pollid'];

    if (!is_valid_id($pollid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_id']}");
    }

    $res = $db->query("SELECT pp.id, t.id AS tid
                      FROM postpolls AS pp
                      LEFT JOIN topics AS t ON t.pollid = pp.id
                      WHERE pp.id = " . sqlesc($pollid));

    if ($res->num_rows == 0)
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_no_poll_id']}");
    }

    $arr = $res->fetch_array(MYSQLI_BOTH);

    $sure = (int) isset($_GET['sure']) && (int) $_GET['sure'];

    if (!$sure || $sure != 1)
    {
        error_message_center("error",
                      "{$lang['gbl_sanity']}",
                      "{$lang['text_del_poll_sure']}<br /><br />
                      <a class='btn' href='" . security::esc_url($_SERVER['PHP_SELF']) . "?action=" . security::html_safe($action) . "&amp;pollid=" . $arr['id'] . "&amp;sure=1'>{$lang['text_click_confirm']}</a>");
    }

    $db->query("DELETE pp.*, ppa.*
               FROM postpolls AS pp
               LEFT JOIN postpollanswers AS ppa ON ppa.pollid = pp.id
               WHERE pp.id = " . sqlesc($pollid));

    if ($db->affected_rows == 0)
    {
        error_message_center("error",
                      "{$lang['gbl_sorry']}",
                      "{$lang['err_del_poll']}");
    }

    $db->query("UPDATE topics
               SET pollid = '0'
               WHERE pollid = " . sqlesc($pollid));

    header('Location: ' . security::esc_url($_SERVER['PHP_SELF']) . '?action=viewtopic&topicid=' . (int) $arr['tid']);
    exit();
}
else if ($use_poll_mod && $action == 'makepoll')
{
    $subaction = (isset($_GET['subaction']) ? $_GET['subaction'] : (isset($_POST['subaction']) ? $_POST['subaction'] : ''));
    $pollid = (isset($_GET['pollid']) ? (int) $_GET['pollid'] : (isset($_POST['pollid']) ? (int) $_POST['pollid'] : 0));
    $topicid = (isset($_POST['topicid']) ? (int) $_POST['topicid'] : 0);

    if ($subaction == 'edit')
    {
        if (!is_valid_id($pollid))
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_inv_id']}");
        }

        $res = $db->query("SELECT pp.*, t.id AS tid
                          FROM postpolls AS pp
                          LEFT JOIN topics AS t ON t.pollid = pp.id
                          WHERE pp.id = " . sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);

        if ($res->num_rows == 0)
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_no_poll_id']}");
        }

        $poll = $res->fetch_assoc();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$topicid)
    {
        $topicid = (int) ($subaction == 'edit' ? $poll['tid'] : $_POST['updatetopicid']);

        $question = $_POST['question'];
        $option0  = $_POST['option0'];
        $option1  = $_POST['option1'];
        $option2  = $_POST['option2'];
        $option3  = $_POST['option3'];
        $option4  = $_POST['option4'];
        $option5  = $_POST['option5'];
        $option6  = $_POST['option6'];
        $option7  = $_POST['option7'];
        $option8  = $_POST['option8'];
        $option9  = $_POST['option9'];
        $option10 = $_POST['option10'];
        $option11 = $_POST['option11'];
        $option12 = $_POST['option12'];
        $option13 = $_POST['option13'];
        $option14 = $_POST['option14'];
        $option15 = $_POST['option15'];
        $option16 = $_POST['option16'];
        $option17 = $_POST['option17'];
        $option18 = $_POST['option18'];
        $option19 = $_POST['option19'];
        $sort     = $_POST['sort'];

        if (!$question || !$option0 || !$option1)
        {
            error_message_center("warn",
                          "{$lang['gbl_warning']}",
                          "{$lang['err_missing_data']}");
        }

        if ($subaction == 'edit' && is_valid_id($pollid))
        {
            $db->query("UPDATE postpolls
                       SET " . "question = " . sqlesc($question) . ",
                           " . "option0 = " . sqlesc($option0) . ",
                           " . "option1 = " . sqlesc($option1) . ",
                           " . "option2 = " . sqlesc($option2) . ",
                           " . "option3 = " . sqlesc($option3) . ",
                           " . "option4 = " . sqlesc($option4) . ",
                           " . "option5 = " . sqlesc($option5) . ",
                           " . "option6 = " . sqlesc($option6) . ",
                           " . "option7 = " . sqlesc($option7) . ",
                           " . "option8 = " . sqlesc($option8) . ",
                           " . "option9 = " . sqlesc($option9) . ",
                           " . "option10 = " . sqlesc($option10) . ",
                           " . "option11 = " . sqlesc($option11) . ",
                           " . "option12 = " . sqlesc($option12) . ",
                           " . "option13 = " . sqlesc($option13) . ",
                           " . "option14 = " . sqlesc($option14) . ",
                           " . "option15 = " . sqlesc($option15) . ",
                           " . "option16 = " . sqlesc($option16) . ",
                           " . "option17 = " . sqlesc($option17) . ",
                           " . "option18 = " . sqlesc($option18) . ",
                           " . "option19 = " . sqlesc($option19) . ",
                           " . "sort = " . sqlesc($sort) . "  " . "
                        WHERE id = " . sqlesc((int) $poll['id'])) or sqlerr(__FILE__, __LINE__);
        }
        else
        {
            if (!is_valid_id($topicid))
            {
                error_message_center("error",
                              "{$lang['gbl_error']}",
                              "{$lang['err_inv_topic_id']}");
            }

            $db->query("INSERT INTO postpolls
                       VALUES(id" . ",
                              " . sqlesc(get_date_time()) . ",
                              " . sqlesc($question) . ",
                              " . sqlesc($option0) . ",
                              " . sqlesc($option1) . ",
                              " . sqlesc($option2) . ",
                              " . sqlesc($option3) . ",
                              " . sqlesc($option4) . ",
                              " . sqlesc($option5) . ",
                              " . sqlesc($option6) . ",
                              " . sqlesc($option7) . ",
                              " . sqlesc($option8) . ",
                              " . sqlesc($option9) . ",
                              " . sqlesc($option10) . ",
                              " . sqlesc($option11) . ",
                              " . sqlesc($option12) . ",
                              " . sqlesc($option13) . ",
                              " . sqlesc($option14) . ",
                              " . sqlesc($option15) . ",
                              " . sqlesc($option16) . ",
                              " . sqlesc($option17) . ",
                              " . sqlesc($option18) . ",
                              " . sqlesc($option19) . ",
                              " . sqlesc($sort) . ")") or sqlerr(__FILE__, __LINE__);

            $pollnum = $db->insert_id;

            $db->query("UPDATE topics
                       SET pollid = " . sqlesc($pollnum) . "
                       WHERE id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
        }

        header("Location: " . security::esc_url($_SERVER['PHP_SELF']) . "?action=viewtopic&topicid=$topicid");
        exit();
    }
    site_header();

    if ($subaction == 'edit')
    {
        echo "<h1>{$lang['title_edit_poll']}</h1>";
    }
    ?>
<form method='post' action='forums.php'>
    <input type='hidden' name='action' value='<?php echo $action; ?>' />
    <input type='hidden' name='subaction' value='<?php echo $subaction; ?>' />
    <input type='hidden' name='updatetopicid' value='<?php echo (int) $topicid; ?>' />

    <?php
    if ($subaction == 'edit')
    {
        ?>
        <input type='hidden' name='pollid' value='<?php echo (int) $poll['id']; ?>' /><?php
    }
    ?>
    <table border='1' align='center' cellspacing='0' cellpadding='5'>
        <tr>
            <td class='rowhead'><?php echo $lang['form_question']?>&nbsp;
                <span class='forum_poll'>*</span>
            </td>
            <td align='left'>
                <textarea name='question' cols='70' rows='4'><?php echo ($subaction == 'edit' ? security::html_safe($poll['question']) : ''); ?></textarea>
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>1&nbsp;
                <span class='forum_poll'>*</span>
            </td>
            <td align='left'>
                <input name='option0' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option0']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>2&nbsp;
                <span class='forum_poll'>*</span>
            </td>
            <td align='left'><input name='option1' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option1']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>3</td>
            <td align='left'>
                <input name='option2' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option2']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>4</td>
            <td align='left'>
                <input name='option3' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option3']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>5</td>
            <td align='left'>
                <input name='option4' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option4']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>6</td>
            <td align='left'>
                <input name='option5' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option5']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>7</td>
            <td align='left'>
                <input name='option6' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option6']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>8</td>
            <td align='left'>
                <input name='option7' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option7']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>9</td>
            <td align='left'>
                <input name='option8' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option8']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>10</td>
            <td align='left'>
                <input name='option9' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option9']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>11</td>
            <td align='left'>
                <input name='option10' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option10']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>12</td>
            <td align='left'>
                <input name='option11' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option11']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>13</td>
            <td align='left'>
                <input name='option12' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option12']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>14</td>
            <td align='left'>
                <input name='option13' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option13']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>15</td>
            <td align='left'>
                <input name='option14' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option14']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>16</td>
            <td align='left'>
                <input name='option15' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option15']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>17</td>
            <td align='left'>
                <input name='option16' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option16']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>18</td>
            <td align='left'>
                <input name='option17' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option17']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>19</td>
            <td align='left'>
                <input name='option18' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option18']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_option']?>20</td>
            <td align='left'>
                <input name='option19' size='80' maxlength='40' value='<?php echo ($subaction == 'edit' ? security::html_safe($poll['option19']) : ''); ?>' /><br />
            </td>
        </tr>

        <tr>
            <td class='rowhead'><?php echo $lang['form_sort']?></td>
            <td class='rowhead'>
                <input type='radio' name='sort' value='yes' <?php echo ($subaction == 'edit' ? ($poll['sort'] != 'no' ? " checked='checked'" : '') : ''); ?> /><?php echo $lang['form_yes']?>
                <input type='radio' name='sort' value='no' <?php echo ($subaction == 'edit' ? ($poll['sort'] == 'no' ? " checked='checked'" : '') : ''); ?> /><?php echo $lang['form_no']?>

            </td>
        </tr>

        <tr>
            <td colspan='2' align='center'>
                <input type='submit' class='btn' value=<?php echo $pollid ? "'{$lang['btn_edit_poll']}'" : "'{$lang['btn_create_poll']}'"?> style='height: 20pt' />
            </td>
        </tr>
    </table>
    <p align='center'>
        <span class='forum_poll'>*</span>&nbsp;=&nbsp;<?php echo $lang['text_required']?>
    </p>
</form>
<br />

    <?php

    site_footer();
    //end_main_frame();
}
else if ($use_attachment_mod && $action == 'attachment')
{
    @ini_set('zlib.output_compression', 'Off');
    @set_time_limit(0);

    if (@ini_get('output_handler') == 'ob_gzhandler' && @ob_get_length() !== false)
    {
        @ob_end_clean();
        header('Content-Encoding:');
    }

    $id = (int) $_GET['attachmentid'];

    if (!is_valid_id($id))
    {
        die("{$lang['err_inv_attach_id']}");
    }

    $at = $db->query("SELECT filename, owner, type
                     FROM attachments
                     WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);

    $resat = $at->fetch_assoc() or die ("{$lang['err_no_attach_id']}");

    $filename = $attachment_dir . '/' . $resat['filename'];

    if (!is_file($filename))
    {
        die("{$lang['err_no_attachment']}");
    }

    if (!is_readable($filename))
    {
        die("{$lang['err_attach_unreadable']}");
    }

    if ((isset($_GET['subaction']) ? $_GET['subaction'] : '') == 'delete')
    {
        if (user::$current['id'] <> $resat['owner'] && user::$current['class'] < UC_MODERATOR)
        {
            die ("{$lang['err_deny_del_attach']}");
        }

        unlink($filename);

        $db->query("DELETE attachments, attachmentdownloads
                   FROM attachments
                   LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id
                   WHERE attachments.id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);

        die("<span class='forum_del_attachment'>{$lang['text_del_success']}</span>");
    }

    $db->query("UPDATE attachments
               SET downloads = downloads + 1
               WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);

    $res = $db->query("SELECT fileid
                      FROM attachmentdownloads
                      WHERE fileid = " . sqlesc($id) . "
                      AND userid = " . sqlesc(user::$current['id']));

    if ($res->num_rows == 0)
    {
        $db->query("INSERT INTO attachmentdownloads (fileid, username, userid, date, downloads)
                   VALUES (" . sqlesc($id) . ", " . sqlesc(user::$current['username']) . ", " . sqlesc(user::$current['id']) . ", " . sqlesc(get_date_time()) . ", 1)") or sqlerr(__FILE__, __LINE__);
    }
    else
    {
        $db->query("UPDATE attachmentdownloads
                   SET downloads = downloads + 1
                   WHERE fileid = " . sqlesc($id) . "
                   AND userid = " . sqlesc(user::$current['id']));
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false); //-- Required For Certain Browsers --//
    header("Content-Type: " . $arr['type']);
    header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . filesize($filename));
    readfile($filename);
    exit();
}
else if ($use_attachment_mod && $action == 'whodownloaded')
{
    $fileid = (int) $_GET['fileid'];

    if (!is_valid_id($fileid))
    {
        die("{$lang['err_inv_id']}");
    }

    $res = $db->query("SELECT fileid, at.filename, userid, username, atdl.downloads, date, at.downloads AS dl
                      FROM attachmentdownloads AS atdl
                      LEFT JOIN attachments AS at ON at.id = atdl.fileid
                      WHERE fileid = " . sqlesc($fileid) . (user::$current['class'] < UC_MODERATOR ? "
                      AND owner = " . user::$current['id'] : '')) or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows == 0)
    {
        die("<h2 align='center'>'{$lang['err_not_found']}'</h2>");
    }
    else
    {
        ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

    <html xmlns='http://www.w3.org/1999/xhtml'>
    <head>

        <meta name='generator' content='FreeTSP.info' />
        <meta name='MSSmartTagsPreventParsing' content='true' />

        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <title><?php echo $lang['text_who_download']?></title>
        <link rel='stylesheet' href='./stylesheets/default/default.css' type='text/css' />
    </head>
    <body>
    <table border='1' width='100%' cellpadding='5'>
        <tr align='center'>
            <td class='rowhead'>
                <span style='font-weight : bold;'><?php echo $lang['table_filename']?></span>
            </td>
            <td style='white-space: nowrap;'>
                <span style='font-weight : bold;'><?php echo $lang['table_down_by']?></span>
            </td>
            <td class='rowhead'>
                <span style='font-weight : bold;'><?php echo $lang['table_downloads']?></span>
            </td>
            <td class='rowhead'>
                <span style='font-weight : bold;'><?php echo $lang['table_date']?></span>
            </td>
        </tr>

        <?php

        $dls = 0;

        while ($arr = $res->fetch_assoc())
        {
            echo "<tr align='center'>
                    <td style='white-space : nowrap;'>" . security::html_safe($arr['filename']) . "</td>
                    <td style='white-space : nowrap;'><span style='cursor : pointer'>
                        <a class='pointer' onclick=\"opener.location=('/userdetails.php?id=" . (int) $arr['userid'] . "'); self.close();\">" . security::html_safe($arr['username']) . "</a>
                        </span>
                    </td>
                    <td style='white-space : nowrap;'>" . (int) $arr['downloads'] . "</td>
                    <td style='white-space : nowrap;'>" . $arr['date'] . " (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr['date'])) . ")</td>
            </tr>";

            $dls += (int) $arr['downloads'];
        }
        ?>
        <tr>
            <td colspan='4'>
                <span style='font-weight : bold;'><?php echo $lang['table_total_down']?></span>
                <span style='font-weight : bold;'><?php echo number_format($dls); ?></span>
            </td>
        </tr>
    </table>
    </body>
    </html>
    <?php
    }
}
else if ($action == 'viewforum') //-- Action: View Forum --//
{
    $forumid = (int) $_GET['forumid'];

    if (!is_valid_id($forumid))
    {
        error_message_center("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_id']}");
    }

    $page   = (isset($_GET['page']) ? (int) $_GET['page'] : 0);
    $userid = user::$current['id'];

    //--  Get Forum Details --//
    $res = $db->query("SELECT f.name AS forum_name, f.minclassread, (SELECT COUNT(id) FROM topics WHERE forumid = f.id) AS t_count FROM forums AS f
                      WHERE f.id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

    $arr = $res->fetch_assoc() or error_message_center("error",
                                                    "{$lang['gbl_error']}",
                                                    "{$lang['err_no_forim_id']}");

    if (user::$current['class'] < $arr['minclassread'])
    {
        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "{$lang['err_denied']}");
    }

    $perpage = (empty(user::$current['topicsperpage']) ? 20 : user::$current['topicsperpage']);
    $num     = (int)$arr['t_count'];

    if ($page == 0)
    {
        $page = 1;
    }

    $first = ($page * $perpage) - $perpage + 1;
    $last  = $first + $perpage - 1;

    if ($last > $num)
    {
        $last = $num;
    }

    $pages = floor($num / $perpage);

    if ($perpage * $pages < $num)
    {
        ++$pages;
    }

    //-- Build Menu --//
    $menu1 = "<p class='success' align='center'>";
    $menu2 = '';

    $lastspace = false;

    for ($i = 1;
         $i <= $pages;
         ++$i)
    {
        if ($i == $page)
        {
            $menu2 .= "[<span style='text-decoration : underline; font-weight : bold;'>$i</span>]\n";
        }

        else
        {
            if ($i > 3 && ($i < $pages - 2) && ($page - $i > 3 || $i - $page > 3))
            {
                if ($lastspace)
                {
                    continue;
                }

                $menu2 .= "... \n";

                $lastspace = true;
            }
            else
            {
                $menu2 .= "<a href='forums.php?action=viewforum&amp;forumid=$forumid&amp;page=$i'><span style='font-weight : bold;'>$i</span></a>\n";

                $lastspace = false;
            }
        }

        if ($i < $pages)
        {
            $menu2 .= "|";
        }
    }

    $menu1 .= ($page == 1 ? "<span style='font-weight : bold;'>&lt;&lt;&nbsp;{$lang['text_prev']}</span>" : "<a href='forums.php?action=viewforum&amp;forumid=$forumid&amp;page=" . ($page - 1) . "'><span style='font-weight : bold;'>&lt;&lt;&nbsp;{$lang['text_prev']}</span></a>");

    $mlb = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    $menu3 = ($last == $num ? "<span style='font-weight : bold;'>{$lang['text_next']}&nbsp;&gt;&gt;</span></p>" : "<a href='forums.php?action=viewforum&amp;forumid=$forumid&amp;page=" . ($page + 1) . "'><span style='font-weight : bold;'>{$lang['text_next']}&nbsp;&gt;&gt;</span></a></p>");

    $offset = $first - 1;

    $topics_res = $db->query("SELECT t.id, t.userid, t.views, t.locked, t.sticky" . ($use_poll_mod ? ', t.pollid' : '') . ", t.subject, u1.username, r.lastpostread, p.id AS p_id, p.userid AS p_userid, p.added AS p_added, (SELECT COUNT(id) FROM posts WHERE topicid=t.id) AS p_count, u2.username AS u2_username
                             FROM topics AS t
                             LEFT JOIN users AS u1 ON u1.id=t.userid
                             LEFT JOIN readposts AS r ON r.userid = " . sqlesc($userid) . " AND r.topicid = t.id
                             LEFT JOIN posts AS p ON p.id = (SELECT MAX(id) FROM posts WHERE topicid = t.id)
                             LEFT JOIN users AS u2 ON u2.id = p.userid
                             WHERE t.forumid = " . sqlesc($forumid) . "
                             ORDER BY t.sticky, t.lastpost DESC
                             LIMIT $offset, $perpage") or sqlerr(__FILE__, __LINE__);

    site_header("{$lang['title_forum']}");

    ?>
    <h1 align='center'><?php echo security::html_safe($arr['forum_name']); ?></h1>
    <?php

    if ($topics_res->num_rows > 0)
    {
        ?>
        <table border='1' width='<?php echo $forum_width; ?>' cellspacing='0' cellpadding='5'>
            <tr>
                <td class='colhead' align='center'><?php echo $lang['table_topic_title']?></td>
                <td class='colhead' align='center'><?php echo $lang['table_replies']?></td>
                <td class='colhead' align='center'><?php echo $lang['table_views']?></td>
                <td class='colhead' align='center'><?php echo $lang['table_author']?></td>
                <td class='colhead' align='center'><?php echo $lang['table_last_post']?></td>
            </tr>
        <?php
        while ($topic_arr = $topics_res->fetch_assoc())
        {
            $topicid      = (int) $topic_arr['id'];
            $topic_userid = (int) $topic_arr['userid'];
            $sticky       = ($topic_arr['sticky'] == 'yes');

            ($use_poll_mod ? $topicpoll = is_valid_id($topic_arr['pollid']) : NULL);

            if (!empty($postsperpage)) {
                $tpages = floor($topic_arr['p_count'] / $postsperpage);
			}

            if (($tpages * $postsperpage) != $topic_arr['p_count'])
            {
                ++$tpages;
            }

            if ($tpages > 1)
            {
                $topicpages = "&nbsp;(<img src='{$image_dir}multipage.gif' width='8' height='10' border='0' alt='{$lang['img_alt_multiple']}' title='{$lang['img_alt_multiple']}' />";

                $split = ($tpages > 10) ? true : false;
                $flag  = false;

                for ($i = 1;
                     $i <= $tpages;
                     ++$i)
                {
                    if ($split && ($i > 4 && $i < ($tpages - 3)))
                    {
                        if (!$flag)
                        {
                            $topicpages .= '&nbsp;...';
                            $flag = true;
                        }
                        continue;
                    }
                    $topicpages .= "&nbsp;<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=$i'>$i</a>";
                }
                $topicpages .= ")";
            }
            else
            {
                $topicpages = '';
            }

            $lpusername = (is_valid_id($topic_arr['p_userid']) && !empty($topic_arr['u2_username']) ? "<a class='altlink_user' href='$site_url/userdetails.php?id=" . (int) $topic_arr['p_userid'] . "'><span style='font-weight : bold;'>" . security::html_safe($topic_arr['u2_username']) . "</span></a>" : "{$lang['table_unknown']}[$topic_userid]");

            $lpauthor = (is_valid_id($topic_arr['userid']) && !empty($topic_arr['username']) ? "<a class='altlink_user' href='$site_url/userdetails.php?id=$topic_userid'><span style='font-weight : bold;'>" . security::html_safe($topic_arr['username']) . "</span></a>" : "{$lang['table_unknown']}[$topic_userid]");

            $new = ($topic_arr['p_added'] > (get_date_time(gmtime() - $posts_read_expiry))) ? ((int) $topic_arr['p_id'] > $topic_arr['lastpostread']) : 0;

            $topicpic = ($topic_arr['locked'] == 'yes' ? ($new ? 'lock' : 'lockednew') : ($new ? 'newpost' : 'post'));

            ?>
            <tr>
                <td class='forum' align='left' width='100%'>
                    <img src='<?php echo $image_dir . $topicpic; ?>.png' width='32' height='32' border='0' alt='' title='' />
                    <?php echo ($sticky ? $lang['table_sticky'] : ''); ?><a href='forums.php?action=viewtopic&amp;topicid=<?php echo $topicid; ?>'>
                    <?php echo security::html_safe($topic_arr['subject']); ?></a><?php echo $topicpages; ?>
                </td>

                <td class='forum' align='center'><?php echo max(0, (int)$topic_arr['p_count'] - 1); ?></td>
                <td class='forum' align='center'><?php echo number_format($topic_arr['views']); ?></td>
                <td class='forum' align='center'>&nbsp;<?php echo $lpauthor; ?>&nbsp;</td>

                <td class='forum' align='center'>
                    <div style='white-space : nowrap;'>
                        &nbsp;<?php echo $topic_arr['p_added']; ?>&nbsp;
                        <br />by - <?php echo $lpusername; ?>
                    </div>
                </td>
            </tr>
            <?php
        }

        end_table();
    }
    else
    {
        display_message("{$lang['gbl_info']}",
                        "{$lang['gbl_sorry']}",
                        "{$lang['err_no_topic_found']}");
    }

    echo $menu1 . $mlb . $menu2 . $mlb . $menu3;
    ?>
    <table class='main' border='0' align='center' cellspacing='0' cellpadding='0'>
        <tr valign='middle'>
            <td class='embedded'>
                <img src='<?php echo $image_dir; ?>new-post.png' width='48' height='48' border='0' alt='<?php echo $lang['img_alt_new_posts']?>' title='<?php echo $lang['img_alt_new_posts']?>' style='margin-right : 5px' />
            </td>
            <td class='embedded'><?php echo $lang['table_new_posts']?></td>
            <td class='embedded'>
                <img src='<?php echo $image_dir; ?>lock.png' width='48' height='48' border='0' alt='<?php echo $lang['img_alt_topic_lock']?>' title='<?php echo $lang['img_alt_topic_lock']?>' style='margin-left : 10px; margin-right: 5px' />
            </td>
            <td class='embedded'><?php echo $lang['table_topic_lock']?></td>
        </tr>
    </table>

    <?php

    $arr = cached::get_forum_access_levels($forumid) or die();

    $maypost = (user::$current['class'] >= $arr['write'] && user::$current['class'] >= $arr['create']);

    if (!$maypost)
    {
        display_message("warn",
                        "{$lang['gbl_sorry']}",
                         "{$lang['err_deny_start_topic']}");
    }

    ?>
    <table class='main' border='0' align='center' cellspacing='0' cellpadding='0'>
        <tr>
            <td class='embedded'>
                <form method='get' action='forums.php'>
                    <input type='hidden' name='action' value='viewunread' />
                    <input type='submit' class='btn' value='<?php echo $lang['btn_view_unread']?>' />
                </form>
            </td>

        <?php

        if ($maypost)
        {
            ?>
            <td class='embedded'>
                <form method='get' action='forums.php'>
                    <input type='hidden' name='action' value='newtopic' />
                    <input type='hidden' name='forumid' value='<?php echo $forumid; ?>' />
                    <input type='submit' class='btn' value='<?php echo $lang['btn_new_topic']?>' style='margin-left : 10px' />
                </form>
            </td>

            <?php
        }

        ?>

        </tr>
    </table>
    <br />

    <?php

    insert_quick_jump_menu($forumid);

    site_footer();

    exit();
}
else if ($action == 'viewunread') //-- Action: View Unread Posts --//
{
    if ((isset($_POST[$action . '_action']) ? $_POST[$action . '_action'] : '') == 'clear')
    {
        $topic_ids = (isset($_POST['topic_id']) ? $_POST['topic_id'] : array());

        if (empty($topic_ids))
        {
            header('Location: ' . security::esc_url($_SERVER['PHP_SELF']) . '?action=' . $action);
            exit();
        }

        foreach ($topic_ids
                 AS
                 $topic_id)
        {
            if (!is_valid_id($topic_id))
            {
                error_message_center("error",
                              "{$lang['gbl_error']}",
                              "{$lang['err_inv_id']}");
            }
        }

        catch_up($topic_ids);

        header('Location: ' . security::esc_url($_SERVER['PHP_SELF']) . '?action=' . $action);
        exit();
    }
    else
    {
        $added = sqlesc(get_date_time(gmtime() - $posts_read_expiry));

        $res = $db->query('SELECT t.lastpost, r.lastpostread, f.minclassread
                          FROM topics AS t
                          LEFT JOIN posts AS p ON t.lastpost = p.id
                          LEFT JOIN readposts AS r ON r.userid = ' . sqlesc(user::$current['id']) . ' AND r.topicid = t.id
                          LEFT JOIN forums AS f ON f.id = t.forumid
                          WHERE p.added > ' . $added) or sqlerr(__FILE__, __LINE__);
        $count = 0;

        while ($arr = $res->fetch_assoc())
        {
            if ($arr['lastpostread'] >= $arr['lastpost'] || user::$current['class'] < $arr['minclassread'])
            {
                continue;
            }

            $count++;
        }
        $res->free();

        if ($count > 0)
        {
            list($pagertop, $pagerbottom, $limit) = pager(25, $count, security::esc_url($_SERVER['PHP_SELF']) . '?action=' . $action . '&');

            site_header();

            echo "<h1 align='center'>{$lang['title_unread']}</h1>";

            echo $pagertop;

            ?>

            <script type='text/javascript'>
                var checkflag = 'false';

                function check(a)
                {
                    if (checkflag == 'false')
                    {
                        for (i = 0; i < a.length; i++)
                            a[i].checked = true;

                        checkflag = 'true';

                        value = 'Uncheck';
                    }
                    else
                    {
                        for (i = 0; i < a.length; i++)
                            a[i].checked = false;

                        checkflag = 'false';

                        value = 'Check';
                    }

                    return value + ' All';
                }
            </script>

        <form method='post' action='<?php echo security::esc_url($_SERVER['PHP_SELF']) . '?action=' . $action; ?>'>
            <input type='hidden' name='<?php echo $action . '_action'; ?>' value='clear' />
                <table width='<?php echo $forum_width; ?>' cellpadding='5'>
                    <tr align='left'>
                        <td class='colhead' colspan='2'><?php echo $lang['table_topic']?></td>
                        <td class='colhead' width='1%'><?php echo $lang['table_clear']?></td>
                    </tr>

                <?php

                $res = $db->query('SELECT t.id, t.forumid, t.subject, t.lastpost, r.lastpostread, f.name, f.minclassread
                                  FROM topics AS t
                                  LEFT JOIN posts AS p ON t.lastpost = p.id
                                  LEFT JOIN readposts AS r ON r.userid = ' . sqlesc(user::$current['id']) . ' AND r.topicid = t.id
                                  LEFT JOIN forums AS f ON f.id = t.forumid
                                  WHERE p.added > ' . $added . '
                                  ORDER BY t.forumid ' . $limit) or sqlerr(__FILE__, __LINE__);

                while ($arr = $res->fetch_assoc())
                {
                    if ($arr['lastpostread'] >= $arr['lastpost'] || user::$current['class'] < $arr['minclassread'])
                    {
                        continue;
                    }

                    $post_res = $db->query("SELECT id
                                           FROM posts
                                           WHERE topicid = " . (int) $arr['id']) or sqlerr(__FILE__, __LINE__);

                    while ($post_arr = $post_res->fetch_assoc())

                    {
                        if ($arr['lastpostread'] < $post_arr['id'] && !isset($post[$i]))
                        {
                            $post[$i] = $post_arr['id'];
                        }
                    }
                    ?>
                    <tr>
                        <td align='center' width='1%'>
                            <img src='<?php echo $image_dir; ?>newpost.png' width='32' height='32' border='0' alt='' title='' />
                        </td>
                        <td align='left'>
                            <a href='forums.php?action=viewtopic&amp;topicid=<?php echo (int) $arr['id']; ?>&amp;page=last#last'><?php echo security::html_safe($arr['subject']); ?></a><br /><?php echo $lang['table_in']?>&nbsp;<span style='font-size : small;'><a href='forums.php?action=viewforum&amp;forumid=<?php echo (int) $arr['forumid']; ?>'><?php echo security::html_safe($arr['name']); ?></a></span>
                        </td>
                        <td align='center'>
                            <input type='checkbox' name='topic_id[]' value='<?php echo (int) $arr['id']; ?>' />
                        </td>
                    </tr>

                    <?php

                    $i++;
                }
                $res->free();

                ?>
                <tr>
                    <td align='center' colspan='3'>
                        <input type='button' class='btn' value='<?php echo $lang['btn_check_all']?>' onclick='this.value = check(form);' />&nbsp;
                        <input type='submit' class='btn' value='<?php echo $lang['btn_clear_all']?>' />
                    </td>
                </tr>

                <?php

                end_table();

                ?>

            </form>

            <?php

            echo $pagerbottom;

            echo "<div align='center' class='btn'><a href='" . security::esc_url($_SERVER['PHP_SELF']) . "?catchup'>{$lang['btn_mark_read']}</a></div><br />";

            site_footer();

            die();
        }
        else
        {
            error_message_center("info",
                                 "{$lang['gbl_sorry']}",
                                 "{$lang['text_no_unread']}<br /><br />{$lang['text_click']}<a href='forums.php?action=getdaily'>{$lang['text_here']}</a>{$lang['text_today_last_24']}");
        }
    }
}
else
{
    if ($action == 'getdaily')
    {
        $res = $db->query('SELECT COUNT(p.id) AS post_count
                          FROM posts AS p
                          LEFT JOIN topics AS t ON t.id = p.topicid
                          LEFT JOIN forums AS f ON f.id = t.forumid
                          WHERE ADDDATE(p.added, INTERVAL 1 DAY) > ' . sqlesc(get_date_time()) . '
                          AND f.minclassread <= ' . user::$current['class']) or sqlerr(__FILE__, __LINE__);

        $arr = $res->fetch_assoc();
        $res->free();

        $count = (int) $arr['post_count'];

        if (empty($count))
        {
            error_message_center("info",
                          "{$lang['gbl_sorry']}",
                          "{$lang['text_no_last_24']}");
        }

        site_header("{$lang['title_last_24']}");

        list($pagertop, $pagerbottom, $limit) = pager(20, $count, security::esc_url($_SERVER['PHP_SELF']) . '?action=' . $action . '&');

        ?>

        <h2 align='center'><?php echo $lang['title_last_24']?></h2>

        <?php

        echo $pagertop;

        ?>

    <table width='<?php echo $forum_width; ?>' cellpadding='5'>
        <tr class='colhead' align='center'>
            <td width='100%' align='left'>
                <span style='font-weight : bold;'><?php echo $lang['table_topic_title']?></span>
            </td>
            <td class='rowhead'>
                <span style='font-weight : bold;'><?php echo $lang['table_views']?></span>
            </td>
            <td class='rowhead'>
                <span style='font-weight : bold;'><?php echo $lang['table_author']?></span>
            </td>
            <td class='rowhead'>
                <span style='font-weight : bold;'><?php echo $lang['table_posted_at']?></span>
            </td>
        </tr>
        <?php

        $res = $db->query("SELECT p.id AS pid, p.topicid, p.userid AS userpost, p.added, t.id AS tid, t.subject, t.forumid, t.lastpost, t.views, f.name, f.minclassread, f.topiccount, u.username
                          FROM posts AS p
                          LEFT JOIN topics AS t ON t.id = p.topicid
                          LEFT JOIN forums AS f ON f.id = t.forumid
                          LEFT JOIN users AS u ON u.id = p.userid
                          LEFT JOIN users AS topicposter ON topicposter.id = t.userid
                          WHERE ADDDATE(p.added, INTERVAL 1 DAY) > " . sqlesc(get_date_time()) . "
                          AND f.minclassread <= " . user::$current['class'] . "
                          ORDER BY p.added DESC " . $limit) or sqlerr(__FILE__, __LINE__);

        while ($getdaily = $res->fetch_assoc())
        {
            $postid   = (int) $getdaily['pid'];
            $posterid = (int) $getdaily['userpost'];

            ?>

        <tr>
            <td class='rowhead' align='left'>
                <a href='forums.php?action=viewtopic&amp;topicid=<?php echo $getdaily['tid']; ?>&amp;page=<?php echo $postid; ?>#<?php echo $postid ?>'><?php echo security::html_safe($getdaily['subject']); ?></a><br />
                <span style='font-weight : bold;'><?php echo $lang['table_in']?></span>&nbsp;
                <a href='forums.php?action=viewforum&amp;forumid=<?php echo (int) $getdaily['forumid']; ?>'><?php echo security::html_safe($getdaily['name']); ?></a>
            </td>
            <td class='rowhead' align='center'><?php echo number_format($getdaily['views']); ?></td>
            <td class='rowhead' align='center'>

                <?php

                if (!empty($getdaily['username']))
                {
                    ?>

                    <a class='altlink_user'
                         href='<?php echo $site_url; ?>/userdetails.php?id=<?php echo $posterid; ?>'><?php echo security::html_safe($getdaily['username']); ?></a>

                    <?php
                }
                else
                {
                    ?>

                    <span style='font-weight : bold;'><?php echo $lang['table_unknown']?>[<?php echo $posterid; ?>]</span>

                    <?php
                }
                ?>

            </td>
            <td class='rowhead'>
                <div style='white-space : nowrap;'><?php echo $getdaily['added'];?><br />

                    <?php
                    echo get_elapsed_time(strtotime($getdaily['added']));
                    ?>

                </div>
            </td>
        </tr>

            <?php
        }

        $res->free();

        end_table();

        echo $pagerbottom;

        site_footer();
    }
    else
    {
        if ($action == 'search') //-- Action: Search --//
        {
            site_header("{$lang['title_search']}");

            $error    = false;
            $found    = '';
            $keywords = (isset($_GET['keywords']) ? trim($_GET['keywords']) : '');

            if (!empty($keywords))
            {
                $res = $db->query("SELECT COUNT(id) AS c
                                  FROM posts
                                  WHERE body LIKE " . sqlesc("%" . sqlwildcardesc($keywords) . "%")) or sqlerr(__FILE__, __LINE__);

                $arr = $res->fetch_assoc();

                $count    = (int) $arr['c'];
                $keywords = security::html_safe($keywords);

                if ($count == 0)
                {
                    $error = true;
                }
                else
                {
                    list($pagertop, $pagerbottom, $limit) = pager(10, $count, security::esc_url($_SERVER['PHP_SELF']) . '?action=' . $action . '&keywords=' . $keywords . '&');

                    $res = $db->query("SELECT p.id, p.topicid, p.userid, p.added, t.forumid, t.subject, f.name, f.minclassread, u.username
                                      FROM posts AS p
                                      LEFT JOIN topics AS t ON t.id = p.topicid
                                      LEFT JOIN forums AS f ON f.id = t.forumid
                                      LEFT JOIN users AS u ON u.id = p.userid
                                      WHERE p.body LIKE " . sqlesc("%" . $keywords . "%") . " $limit");

                    $num = $res->num_rows;
                    echo $pagertop;

                    ?>

            <table border='0' width='100%' cellspacing='0' cellpadding='5'>
                <tr align='left'>
                    <td class='colhead'><?php echo $lang['table_post']?></td>
                    <td class='colhead'><?php echo $lang['table_topic']?></td>
                    <td class='colhead'><?php echo $lang['table_forum']?></td>
                    <td class='colhead'><?php echo $lang['table_post_by']?></td>
                </tr>

                    <?php

                    for ($i = 0;
                         $i < $num;
                         ++$i)
                    {
                        $post = $res->fetch_assoc();

                        if ($post['minclassread'] > user::$current['class'])
                        {
                            --$count;
                            continue;
                        }

                        echo "<tr>
                                <td align='center'>{$post['id']}</td>

                                <td align='left' width='100%'>
                                    <a href='forums.php?action=viewtopic&amp;highlight=$keywords&amp;topicid={$post['topicid']}&amp;page=p{$post['id']}#{$post['id']}'><span style='font-weight : bold;'>" . security::html_safe($post['subject']) . "</span></a>
                                </td>

                                <td align='left' style='white-space: nowrap'>" . (empty($post['name']) ? "{$lang['table_unknown']}[{$post['forumid']}]" : "
                                    <a href='forums.php?action=viewforum&amp;forumid={$post['forumid']}'>
                                        <span style='font-weight : bold;'>" . security::html_safe($post['name']) . "</span>
                                    </a>") . "
                                </td>

                                <td align='left' style='white-space: nowrap'>" . (empty($post['username']) ? "{$lang['table_unknown']}[{$post['userid']}]" : "

                                    <span style='font-weight : bold;'>
                                        <a class='altlink_user' href='$site_url/userdetails.php?id={$post['userid']}'>" . security::html_safe($post['username']) . "</a>
                                    </span>")."<br />{$lang['table_at']}{$post['added']}
                                </td>
                            </tr>";
                    }
                    end_table();

                    echo $pagerbottom;

                    $found = "[<span class='forum_search_found'>{$lang['table_found']}$count{$lang['text_post']}" . ($count != 1 ? "{$lang['text_post_s']}" : "") . " </span> ]";

                }
            }
            ?>
            <div>
                <div style='text-align : center;'><h1><?php echo $lang['title_search_forum']?></h1> <?php echo ($error ? "[<span class='forum_search_none'>{$lang['text_not_found']}</span> ]" : $found)?>
                </div>
                <div style='margin-left : 53px; margin-top : 13px;'>
                    <form method='get' action='forums.php' id='search_form' style='margin : 0pt; padding : 0pt; font-family : Tahoma, Arial, Helvetica, sans-serif; font-size : 11px;'>
                        <input type='hidden' name='action' value='search' />
                        <table border='0' width='50%' cellpadding='0' cellspacing='0'>
                            <tbody>
                            <tr>
                                <td valign='top' colspan='2'>
                                    <span style='font-weight : bold;'><?php echo $lang['table_by_keyword']?></span>
                                </td>
                            </tr>
                            <tr>
                                <td valign='top'>
                                    <input type='text' name='keywords' size='65' value='<?php echo $keywords; ?>' />
                                    <input type='submit' class='btn' value='<?php echo $lang['btn_search']?>' /><br />
                                    <span style='font-size : xx-small; font-weight : bold;'><?php echo $lang['table_note_search']?>
                                        <span style='text-decoration : underline;'><?php echo $lang['table_only']?></span>
                                         <?php echo $lang['table_in_posts']?>
                                     </span>
                                 </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div><br />

            <?php

            site_footer();

            exit();
        }
        else
        {
            if ($action == 'forumview')
            {
                $ovfid = (isset($_GET['forid']) ? (int) $_GET['forid'] : 0);

                if (!is_valid_id($ovfid))
                {
                    error_message_center("error",
                                  "{$lang['gbl_error']}",
                                  "{$lang['err_inv_id']}");
                }

                $res = $db->query("SELECT name
                                  FROM forum_category
                                  WHERE id = $ovfid
                                  ORDER BY sort") or sqlerr(__FILE__, __LINE__);

                $arr = $res->fetch_assoc() or error_message_center("error",
                                                                       "{$lang['gbl_sorry']}",
                                                                       "{$lang['err_no_forum_id']}");

                $db->query("UPDATE users
                           SET forum_access = " . sqlesc(get_date_time()) . "
                           WHERE id = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

                site_header("{$lang['title_forums']}");

                ?>

                <h1 align='center'>
                    <span style='font-weight : bold;'>
                        <a href='forums.php'><?php echo $lang['title_forums']?></a>
                    </span>
                    <?php echo security::html_safe($arr['name']); ?>
                </h1>

                <table border='1' width='<?php echo $forum_width; ?>' cellspacing='0' cellpadding='5'>
                    <tr>
                        <td class='colhead' align='left'><?php echo $lang['table_forums']?></td>
                        <td class='colhead' align='right'><?php echo $lang['table_topics']?></td>
                        <td class='colhead' align='right'><?php echo $lang['table_posts']?></td>
                        <td class='colhead' align='left'><?php echo $lang['table_last_post']?></td>
                    </tr>

                <?php

                show_forums($ovfid);

                end_table();

                site_footer();

                exit();
            }
            else //-- Default Action: View Forums --//
            {
                if (isset($_GET['catchup']))
                {
                    catch_up();

                    header('Location: ' . security::esc_url($_SERVER['PHP_SELF']));
                    exit();
                }

                $db->query("UPDATE users
                           SET forum_access = '" . get_date_time() . "'
                           WHERE id = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

                site_header("{$lang['title_forums']}");

                ?>

                <h1 align='center'><span style='font-weight : bold;'><?php echo $site_name; ?> - <?php echo $lang['title_forum']?></span></h1>

                <table border='1' cellspacing='0' cellpadding='5' width='<?php echo $forum_width; ?>'>

                    <?php

                    $ovf_res = $db->query("SELECT id, name, minclassview
                                          FROM forum_category
                                          ORDER BY sort ASC") or sqlerr(__FILE__, __LINE__);

                    while ($ovf_arr = $ovf_res->fetch_assoc())
                    {
                        if (user::$current['class'] < $ovf_arr['minclassview'])
                        {
                            continue;
                        }

                        $ovfid   = (int) $ovf_arr['id'];
                        $ovfname = $ovf_arr['name'];

                        ?>
                        <tr>
                            <td class='colhead' align='left' width='100%'>
                                <a class='altlink_forum' href='forums.php?action=forumview&amp;forid=<?php echo $ovfid; ?>'>
                                    <span style='font-weight : bold;'><?php echo security::html_safe($ovfname); ?></span>
                                </a>
                            </td>

                            <td class='colhead' align='right'>
                                <span style='font-weight : bold;'><?php echo $lang['table_topics']?></span>
                            </td>

                            <td class='colhead' align='right'>
                                <span style='font-weight : bold;'><?php echo $lang['table_posts']?></span>
                            </td>

                            <td class='colhead' align='left'>
                                <span style='font-weight : bold;'><?php echo $lang['table_last_post']?></span>
                            </td>
                        </tr>

                        <?php

                        show_forums($ovfid);
                    }
                    end_table();

                    if ($forum_stats_mod)
                    {
                        cached::forum_stats();
                    }

                    ?>

                    <p align='center'>
                        <a href='forums.php?action=search'>
                            <span style='font-weight : bold;'><?php echo $lang['table_search']?></span>
                        </a> |

                        <a href='forums.php?action=viewunread'>
                            <span style='font-weight : bold;'><?php echo $lang['table_new_posts']?></span>
                        </a> |

                        <a href='forums.php?action=getdaily'>
                            <span style='font-weight : bold;'><?php echo $lang['table_todays_posts']?></span>
                        </a> |

                        <a href='forums.php?catchup'>
                            <span style='font-weight : bold;'><?php echo $lang['table_mark_read']?></span>
                        </a>
                    </p><br />

                <?php

                site_footer();
            }
        }
    }
}

?>