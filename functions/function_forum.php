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

if (!function_exists('highlight')) {
    function highlight($search, $subject, $hlstart = '<span class="forum_search">', $hlend = '</span>') {
        $srchlen = strlen($search); //-- Length Of Searched String --//

        if ($srchlen == 0) {
            return $subject;
        }

        $find = $subject;

	    while ($find = stristr($find, $search)) { //-- Find $search Text In $subject -case Insensitive --//
               $srchtxt = substr($find, 0, $srchlen); //-- Get New Search Text --//
               $find    = substr($find, $srchlen);
               $subject = str_replace($srchtxt, $hlstart . $srchtxt . $hlend, $subject); //-- Highlight Found Case Insensitive Search Text --//
        }
        return $subject;
    }
}

function catch_up($id = 0) {
    global $posts_read_expiry, $db;

    $userid = user::$current['id'];

    $res = $db->query("SELECT t.id, t.lastpost, r.id AS r_id, r.lastpostread
                      FROM topics AS t
                      LEFT JOIN posts AS p ON p.id = t.lastpost
                      LEFT JOIN readposts AS r ON r.userid = " . sqlesc($userid) . " AND r.topicid = t.id
                      WHERE p.added > " . sqlesc(get_date_time(gmtime() - $posts_read_expiry)) . (!empty($id) ? 'AND t.id ' . (is_array($id) ? 'IN (' . implode(', ', $id) . ')' : '= ' . sqlesc($id)) : '')) or sqlerr(__FILE__, __LINE__);

    while ($arr = $res->fetch_assoc()) {
        $postid = (int)$arr['lastpost'];

        if (!is_valid_id($arr['r_id']))
        {
            $db->query("INSERT INTO readposts (userid, topicid, lastpostread)
                       VALUES (" . $userid . ", " . (int)$arr['id'] . ", " . $postid . ")") or sqlerr(__FILE__, __LINE__);
        } else {
            if ($arr['lastpostread'] < $postid) {
                $db->query("UPDATE readposts
                           SET lastpostread = " . $postid . "
                           WHERE id = " . (int)$arr['r_id']) or sqlerr(__FILE__, __LINE__);
            }
        }
    }
    $res->free();
}

function show_forums($forid) {
    global $image_dir, $posts_read_expiry, $site_url, $lang, $db;

    $forums_res = $db->query("SELECT f.id, f.name, f.description, f.postcount, f.topiccount, f.minclassread, p.added, p.topicid, p.userid, p.id AS pid, u.username, t.subject, t.lastpost, r.lastpostread
                             FROM forums AS f
                             LEFT JOIN posts AS p ON p.id = (SELECT MAX(lastpost) FROM topics WHERE forumid = f.id)
                             LEFT JOIN users AS u ON u.id = p.userid
                             LEFT JOIN topics AS t ON t.id = p.topicid
                             LEFT JOIN readposts AS r ON r.userid = " . sqlesc(user::$current['id']) . " AND r.topicid = p.topicid
                             WHERE f.forid = " . $forid . "
                             ORDER BY sort ASC") or sqlerr(__FILE__, __LINE__);

    while ($forums_arr = $forums_res->fetch_assoc()) {
        if (user::$current['class'] < $forums_arr['minclassread']) {
            continue;
        }

        $forumid    = (int)$forums_arr['id'];
        $lastpostid = (int)$forums_arr['lastpost'];

        if (is_valid_id($forums_arr['pid'])) {
            $lastpost = "<div style='white-space : nowrap;'>{$forums_arr['added']}<br />{$lang['table_by']}<a class='altlink_user' href='$site_url/userdetails.php?id=" . (int)$forums_arr['userid'] . "'><span style='font-weight : bold;'>" . security::html_safe($forums_arr['username']) . "</span></a><br />{$lang['table_in']}<a href='forums.php?action=viewtopic&amp;topicid=" . (int)$forums_arr['topicid'] . "&amp;page=p$lastpostid#$lastpostid'><span style='font-weight : bold;'>" . security::html_safe($forums_arr['subject']) . "</span></a></div>";

            $img = 'unlocked' . ((($forums_arr['added'] > (get_date_time(gmtime() - $posts_read_expiry))) ? ((int)$forums_arr['pid'] > $forums_arr['lastpostread']) : 0) ? 'new' : '');
        } else {
            $lastpost = $lang['table_na'];
            $img      = "unlocked";
        }

        ?>
        <tr>
            <td align='left'>
                <table border='0' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td class='embedded' style='padding-right: 5px'>
                            <img src='<?php echo $image_dir . $img; ?>.png' width='32' height='32' border='0' alt='<?php echo $lang['img_alt_no_new_posts']?>' title='<?php echo $lang['img_alt_no_new_posts']?>' />
                        </td>
                        <td class='embedded'>
                            <a href='forums.php?action=viewforum&amp;forumid=<?php echo $forumid; ?>'>
                            <span style='font-weight : bold;'><?php echo security::html_safe($forums_arr['name']); ?></span>
                            </a>

                        <?php

                        if (!empty($forums_arr['description'])) {
                            ?><br /><?php echo security::html_safe($forums_arr['description']);
                        }
                        ?></td>
                    </tr>
                </table>
            </td>
            <td align='center'><?php echo number_format($forums_arr['topiccount']); ?></td>
            <td align='center'><?php echo number_format($forums_arr['postcount']); ?></td>
            <td align='left'><?php echo $lastpost; ?></td>
        </tr>

    <?php
    }
}

//-- Returns The Id Of The Last Post Of A Forum --//
function update_topic_last_post($topicid) {
    global $lang, $db;

    $res = $db->query("SELECT MAX(id) AS id
                      FROM posts
                      WHERE topicid = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

    $arr = $res->fetch_assoc() or die ("{$lang['err_inv_post']}");

    $db->query("UPDATE topics
               SET lastpost = " . (int)$arr['id'] . "
               WHERE id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

    //-- Update Forum Post/Topic Count --//
    $forums = $db->query("SELECT id
                         FROM forums");

    while ($forum = $forums->fetch_assoc()) {
        $postcount  = 0;
        $topiccount = 0;

        $topics = $db->query("SELECT id
                             FROM topics
                             WHERE forumid = " . (int)$forum['id']);

        while ($topic = $topics->fetch_assoc()) {
            $res = $db->query("SELECT COUNT(*)
                              FROM posts
                              WHERE topicid = " . (int)$topic['id']);

            $arr = $res->fetch_row();

            $postcount += (int)$arr[0];
            ++$topiccount;
        }
        $db->query("UPDATE forums
                   SET postcount = $postcount, topiccount = $topiccount
                   WHERE id = " . (int)$forum['id']);
    }
}

//-- Inserts a Quick Jump Menu --//
function insert_quick_jump_menu($currentforum = 0) {
    global $lang, $db;

    ?>
    <div style='text-align : center;'>
        <form name='jump' method='get' action='forums.php'>
            <input type='hidden' name='action' value='viewforum' />
            <?php print("<span style='font-weight : bold;'>{$lang['form_jump']}:</span>"); ?>
            <select name='forumid' onchange="if(this.options[this.selectedIndex].value != -1){ forms['jump'].submit() }">

                <?php

                $res = $db->query("SELECT id, name, minclassread
                                  FROM forums
                                  ORDER BY name") or sqlerr(__FILE__, __LINE__);

                while ($arr = $res->fetch_assoc()) {
                    if (user::$current['class'] >= $arr['minclassread']) {
                        echo "<option value='" . (int)$arr['id'] . ($currentforum == (int)$arr['id'] ? "' selected='selected' " : "'") . '>' . security::html_safe($arr['name']) . "</option>";
                    }
                }

                ?>

            </select>
            <input type='submit' class='btn' value='<?php echo $lang['gbl_btn_submit']?>' />
        </form>
    </div>
    <br />

    <?php
}

//-- Inserts a Compose Frame --//
function insert_compose_frame($id, $newtopic = true, $quote = false, $attachment = false) {
    global $maxsubjectlength, $maxfilesize, $image_dir, $use_attachment_mod, $site_url, $image_dir, $lang, $db;

    if (user::$current['forumpos'] == 'no') {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_unauth_post']})");
    }

    if ($newtopic)
    {
        $res = $db->query("SELECT name
                          FROM forums
                          WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);

        $arr = $res->fetch_assoc() or die ("{$lang['err_bad_id']}");

        ?><h3><?php echo $lang['text_new_topic']?>"<a href='forums.php?action=viewforum&amp;forumid=<?php echo (int)$id; ?>'><?php echo security::html_safe($arr['name']); ?>"</a><?php echo $lang['text_in_forum']?></h3>

    <?php
    } else {
        $res = $db->query("SELECT t.forumid, t.subject, t.locked, f.minclassread
                          FROM topics AS t
                          LEFT JOIN forums AS f ON f.id = t.forumid
                          WHERE t.id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);

        $arr = $res->fetch_assoc() or die ("{$lang['err_no_topic']}");

        if (user::$current['class'] < $arr['minclassread'])
        {
            error_message_center("warn",
                          "{$lang['gbl_sorry']}",
                          "{$lang['err_not_allowed']}");

            end_table();
            exit();
        }

        if ($arr['locked'] == 'yes' && user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("warn",
                          "{$lang['gbl_sorry']}",
                          "{$lang['err_topic_locked']}");

            end_table();
            exit();
        }
            ?><h3 align='center'><?php echo $lang['text_reply_topic']?>"<a href='forums.php?action=viewtopic&amp;topicid=<?php echo (int)$id; ?>'><?php echo security::html_safe($arr['subject']); ?>"</a></h3>

    <?php
    }

    if ($quote)
    {
        $postid = (int)$_GET['postid'];

        if (!is_valid_id($postid))
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_inv_id']}");
        }

        $res = $db->query("SELECT posts.*, users.username
                          FROM posts
                          JOIN users ON posts.userid = users.id
                          WHERE posts.id = " . $postid) or sqlerr(__FILE__, __LINE__);

        if ($res->num_rows == 0)
        {
            error_message_center("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_no_post_id']}");
        }

        $arr = $res->fetch_assoc();
    }

    begin_frame("{$lang['title_compose']}", true);

    ?>

    <form name='compose' method='post' action='forums.php' enctype='multipart/form-data'>
        <input type='hidden' name='action' value='post' />
        <input type='hidden' name='<?php echo ($newtopic ? 'forumid' : 'topicid'); ?>' value='<?php echo (int)$id; ?>' />

    <?php

    begin_table(true);

    if ($newtopic) {
        ?>

        <tr>
            <td class='rowhead' width='10%'><strong><?php echo $lang['table_subject']?></strong></td>
            <td align='left'>
                <input type='text' name='subject' size='100' maxlength='<?php echo (int)$maxsubjectlength; ?>' style='border :  0px; height : 19px' />
            </td>
        </tr>

        <?php
    }

    ?>
    <tr>
        <td class='rowhead' width='10%'><strong><?php echo $lang['table_body']?></strong></td>
        <td class='rowhead'>

        <?php

        $qbody = ($quote ? "[quote=" . security::html_safe($arr['username']) . "]" . security::html_safe(unesc($arr['body'])) . "[/quote]" : '');

        if (function_exists('textbbcode')) {
            echo("" . textbbcode('compose', 'body', $qbody) . '');
        } else {
            ?>
            <textarea name='body' rows='7' cols='5' style='width:99%'><?php echo $qbody; ?></textarea><?php
        }
        echo("</td></tr>");
        if ($use_attachment_mod && $attachment) {
            ?>
            <tr>
                <td colspan='2'>
                    <fieldset class='fieldset'>
                        <legend><strong><?php echo $lang['table_add_file']?></strong></legend>
                        <input type='checkbox' name='uploadattachment' value='yes' />
                        <input type='file' name='file' size='60' />
                        <div class='error'><strong><?php echo $lang['table_allowed_files']?><br /><?php echo $lang['table_size_limit']?><span class='error'><?php echo misc::mksize($maxfilesize); ?></span></strong></div>
                    </fieldset>
                </td>
            </tr>

            <?php
        }

        ?>
        <tr>
            <td align='center' colspan='2'>
                <input type='submit' class='btn' value='<?php echo $lang['gbl_btn_submit']?>' />
            </td>
        </tr>

        <?php

        end_table();

        ?>
    </form>

    <p align='center'><a class='btn' href='<?php echo $site_url; ?>/smilies.php' target='_blank'><?php echo $lang['table_smilies']?></a></p>

    <?php

    end_frame();

    //-- Get Last 10 Posts If This Is A Reply --//
    if (!$newtopic) {
        $postres = $db->query("SELECT p.id, p.added, p.body, u.id AS uid, u.username, u.avatar
                              FROM posts AS p
                              LEFT JOIN users AS u ON u.id = p.userid
                              WHERE p.topicid = " . sqlesc($id) . "
                              ORDER BY p.id DESC
                              LIMIT 10") or sqlerr(__FILE__, __LINE__);

        if ($postres->num_rows > 0) {
            ?>

            <br />

            <?php

            begin_frame("{$lang['title_last_ten']}");

            while ($post = $postres->fetch_assoc()) {
                $avatar = ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? security::html_safe($post['avatar']) : '');

                if (empty($avatar)) {
                    $avatar = $image_dir . "default_avatar.gif";
                }

                ?>

                <p class='sub'>#<?php echo (int)$post['id']; ?><?php echo $lang['table_by'], (!empty($post['username']) ? security::html_safe($post['username']) : "{$lang['table_unknown_user']}{$post['uid']}"); ?><?php echo $lang['table_at'], $post['added'], $lang['table_gmt']?></p><?php

                begin_table(true);

                ?>

            <tr>
                <td class='rowhead' align='center' width='100' height='100' style='padding : 0px' valign='top'>
                    <img src='<?php echo $avatar ?>' width='125' height='125' border='0' alt='<?php echo $lang['img_alt_avatar']?>' title='<?php echo $lang['img_alt_avatar']?>' />
                </td>
                <td class='comment' valign='top'><?php echo format_comment($post['body']); ?></td>
            </tr>

            <?php

                end_table();
            }

            end_frame();
        }
    }

    insert_quick_jump_menu();
}

?>