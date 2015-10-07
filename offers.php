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

$lang = array_merge(load_language('offers'),
                    load_language('func_bbcode'),
                    load_language('global'));

if (user::$current['class'] < UC_POWER_USER)
{
    error_message_center("error",
                         "{$lang['gbl_sorry']}",
                         "{$lang['err_pu_above']}");
}

//-- Possible Stuff To Be $_GETting lol --//
$id            = (isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0));
$comment_id    = (isset($_GET['comment_id']) ? intval($_GET['comment_id']) : (isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0));
$category      = (isset($_GET['category']) ? intval($_GET['category']) : (isset($_POST['category']) ? intval($_POST['category']) : 0));
$offered_by_id = isset($_GET['offered_by_id']) ? intval($_GET['offered_by_id']) : 0;
$vote          = isset($_POST['vote']) ? intval($_POST['vote']) : 0;
$posted_action = strip_tags((isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '')));

//=== Add All Possible Actions Here And Check Them To Be Sure They Are OK --//
$valid_actions = array('add_new_offer',
                       'delete_offer',
                       'edit_offer',
                       'offer_details',
                       'vote',
                       'add_comment',
                       'edit_comment',
                       'view_original',
                       'delete_comment',
                       'alter_status');

//-- Check Posted Action, And If No Action Was Posted, Show The Default Page --//
$action = (in_array($posted_action, $valid_actions) ? $posted_action : 'default');

//-- Top Menu --//
$top_menu = "<p style='text-align : center;'><a class='altlink' href='offers.php'>{$lang['text_view_offer']}</a> || <a class='altlink' href='offers.php?action=add_new_offer'>{$lang['text_make_offer']}</a></p>";

switch ($action)
{
    //-- Let Them Vote On It --//
     case 'vote';

        if (!isset($id) || !is_valid_id($id) || !isset($vote) || !is_valid_id($vote))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_bad_id_vote']}");
        }

        //-- See If They Voted Yet --//
        $res_did_they_vote = $db->query("SELECT vote
                                        FROM offer_votes
                                        WHERE user_id = " . user::$current['id'] . "
                                        AND offer_id = $id");

        $row_did_they_vote = $res_did_they_vote->fetch_row();

        if ($row_did_they_vote[0] == '')
        {
            $yes_or_no = ($vote == 1 ? 'yes' : 'no');

            $db->query("INSERT INTO offer_votes (offer_id, user_id, vote)
                       VALUES ($id, " . user::$current['id'] . ", '" . $yes_or_no . "')");

            $db->query("UPDATE offers
                       SET " . ($yes_or_no == 'yes' ? 'vote_yes_count = vote_yes_count + 1' : 'vote_no_count = vote_no_count + 1') . "
                       WHERE id = $id");

            header("Location: /offers.php?action=offer_details&voted=1&id=$id");
            die();
        }
        else
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_voted']}");
        }

    break;

    //-- Default First Page With All The Offers --//
     case 'default';

        //-- Get Stuff For The Pager --//
        $count_query = $db->query("SELECT COUNT(id)
                                  FROM offers");

        $count_arr = $count_query->fetch_row();
        $count     = (int)$count_arr[0];
        $page      = isset($_GET['page']) ? (int)$_GET['page'] : 0;
        $perpage   = isset($_GET['perpage']) ? (int)$_GET['perpage'] : 10;

        list ($menu, $LIMIT) = pager_new($count, $perpage, $page, 'offers.php?' . ($perpage == 10 ? '' : '&amp;perpage=' . $perpage));

        echo site_header("{$lang['title_offers']}", false);

        echo (isset($_GET['new']) ? "<h1>{$lang['text_offer_add']}</h1>" : '' ) . (isset($_GET['offer_deleted']) ? "<h1>{$lang['text_offer_del']}</h1>" : '' ) . '';

        echo "<div align='center'>$top_menu<br /></div>";
        echo "<div align='center'>$menu<br /><br /></div>";

        if ($count == 0)
        {
            error_message_center("info",
                                 "{$lang['gbl_sorry']}",
                                 "{$lang['err_no_offers']}");
        }

        echo "<table border='0' align='center' width='80%' cellspacing='0' cellpadding='5'>
                <tr>
                    <td class='colhead' align='center'>{$lang['table_type']}</td>
                    <td class='colhead' align='center'>{$lang['table_name']}</td>
                    <td class='colhead' align='center'>{$lang['table_added']}</td>
                    <td class='colhead' align='center'>{$lang['table_comm']}</td>
                    <td class='colhead' align='center'>{$lang['table_votes']}</td>
                    <td class='colhead' align='center'>{$lang['table_by']}</td>
                    <td class='colhead' align='center'>{$lang['table_status']}</td>
                    <td class='colhead' align='center'>{$lang['table_uploaded']}</td>
                </tr>";

        $main_query_res = $db->query("SELECT o.id AS offer_id, o.offer_name, o.category, o.added, o.offered_by_user_id, o.filled_torrent_id, o.vote_yes_count, o.vote_no_count, o.comments, o.status,u.id, u.username, u.warned, u.donor, u.class,c.id AS cat_id, c.name AS cat_name, c.image AS cat_image
                                     FROM offers AS o
                                     LEFT JOIN categories AS c ON o.category = c.id
                                     LEFT JOIN users AS u ON o.offered_by_user_id = u.id
                                     ORDER BY o.added
                                     DESC $LIMIT");

        while ($main_query_arr = $main_query_res->fetch_assoc())
        {
            $status = ($main_query_arr['status'] == "approved" ?
                                                    "<span class='offers_approved'>{$lang['table_approved']}</span>" :
                                                    ($main_query_arr['status'] == "pending" ?
                                                    "<span class='offers_pending'>{$lang['table_pending']}</span>" :
                                                    "<span class='offers_denied'>{$lang['table_denied']}</span>"));

            $uploaded = ($main_query_arr['filled_torrent_id'] >= "1" ?
                                                     "<a href='details.php?id={$main_query_arr['filled_torrent_id']}'>
                                                     <span class='offers_filled_yes'>{$lang['table_yes']}</span></a>" :
                                                     ($main_query_arr['filled_torrent_id'] == "0" ?
                                                     "<span class='offers_filled_no'>{$lang['table_not_yet']}</span>" : ""));

            echo "<tr>
                    <td class='rowhead' align='center' style='margin : 0; padding: 1;'><img src='{$image_dir}caticons/" . security::html_safe($main_query_arr['cat_image']) . "' border='0' width='60' height='54' alt='" . security::html_safe($main_query_arr['cat_name']) . "' title='" . security::html_safe($main_query_arr['cat_name']) . "'/></td>

                    <td class='rowhead' align='center'><a class='altlink' href='offers.php?action=offer_details&amp;id={$main_query_arr['offer_id']}'>" . security::html_safe($main_query_arr['offer_name']) . "</a></td>

                    <td class='rowhead' align='center'>" . get_date_time($main_query_arr['added'], 'LONG') . "</td>
                    <td class='rowhead' align='center'>" . number_format($main_query_arr['comments']) . "</td>

                    <td class='rowhead' align='center'>{$lang['table_yes']}: " . number_format($main_query_arr['vote_yes_count']) . "<br />
                                                        {$lang['table_no']}: " . number_format($main_query_arr['vote_no_count']) . "</td>

                    <td class='rowhead' align='center'>" . format_username($main_query_arr) . "</td>
                    <td class='rowhead' align='center'>$status</td>
                    <td class='rowhead' align='center'>$uploaded</td>
                </tr>";
        }

        echo "</table>";
        echo "<div align='center'><br />$menu<br /></div><br />";

        echo site_footer();

    break;

    //-- Details Page For The Offers --//
    case 'offer_details':

        if (!isset($id) || !is_valid_id($id))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_bad_id']}");
        }

        $res = $db->query('SELECT o.id AS offer_id, o.offer_name, o.category, o.added, o.offered_by_user_id, o.vote_yes_count, o.status,o.vote_no_count, o.image, o.link, o.description, o.comments,u.id, u.username, u.warned, u.enabled, u.donor, u.class, u.uploaded, u.downloaded,c.name AS cat_name, c.image AS cat_image
                          FROM offers AS o
                          LEFT JOIN categories AS c ON o.category = c.id
                          LEFT JOIN users AS u ON o.offered_by_user_id = u.id
                          WHERE o.id = ' . $id);

        $arr = $res->fetch_assoc();

        //-- See If They Voted Yet --//
        $res_did_they_vote = $db->query("SELECT vote
                                        FROM offer_votes
                                        WHERE user_id = " . user::$current['id'] . "
                                        AND offer_id = $id");

        $row_did_they_vote = $res_did_they_vote->fetch_row();

        if ($row_did_they_vote[0] == '')
        {
            $vote_yes = "<form method='post' action='offers.php'>
                            <input type='hidden' name='action' value='vote' />
                            <input type='hidden' name='id' value='$id' />
                            <input type='hidden' name='vote' value='1' />
                            <input type='submit' class='btn' value='{$lang['btn_vote_yes']}' onmouseover='this.className=\"btn\"' onmouseout='this.className=\"btn\"' />
                        </form> ~ {$lang['text_notif_filled']}";

            $vote_no = "<form method='post' action='offers.php'>
                            <input type='hidden' name='action' value='vote' />
                            <input type='hidden' name='id' value='$id' />
                            <input type='hidden' name='vote' value='2' />
                            <input type='submit' class='btn' value='{$lang['btn_vote_no']}' onmouseover='this.className=\"btn\"' onmouseout='this.className=\"btn\"' />
                        </form> ~ {$lang['text_stick']}";

            $your_vote_was = '';
        }
        else
        {
            $vote_yes      = '';
            $vote_no       = '';
            $your_vote_was = "{$lang['table_your_vote']} : {$row_did_they_vote[0]}";
        }

        $status_drop_down = (user::$current['class'] < UC_MODERATOR ? "" : "<br />
                            <form method='post' action='offers.php'>
                                <input type='hidden' name='action' value='alter_status' />
                                <input type='hidden' name='id' value='$id' />
                                <select name='set_status'>
                                    <option class='body' value='pending' " . ($arr['status'] == 'pending' ? ' selected="selected" ' : '' ) . ">{$lang['form_pending']}</option>
                                    <option class='body' value='approved' " . ($arr['status'] == 'approved' ? ' selected="selected" ' : '' ) . ">{$lang['form_approved']}</option>
                                    <option class='body' value='denied' " . ($arr['status'] == 'denied' ? ' selected="selected" ' : '' ) . ">{$lang['form_denied']}</option>
                                </select>
                                <input type='submit' class='btn' value='{$lang['btn_status']}' onmouseover='this.className=\"btn\"' onmouseout='this.className=\"btn\"' />
                            </form>");

        //-- Start Page --//
        echo site_header("{$lang['title_details']}: " . security::html_safe($arr['offer_name']), false);

        echo (isset($_GET['status_changed']) ? "<h1>{$lang['text_offer_updated']}</h1>" : "" ) .
             (isset($_GET['voted']) ? "<h1>{$lang['text_vote_added']}</h1>" : "" ) .
             (isset($_GET['comment_deleted']) ? "<h1>{$lang['text_comment_delete']}</h1>" : "" ) . $top_menu .
             ($arr['status'] == 'approved' ? "<span class='offers_approved'>{$lang['text_approved']}</span>" :
             ($arr['status'] == 'pending' ? "<span class='offers_pending'>{$lang['text_pending']}</span>" :
             "<span class='offers_denied'>{$lang['text_denied']}</span>")) . $status_drop_down . "<br /><br />

            <table border='0' align='center' width='80%' cellspacing='0' cellpadding='5'>
                <tr>
                    <td class='colhead' align='center' colspan='2'><h1>" . security::html_safe($arr['offer_name']) .
                    (user::$current['class'] < UC_MODERATOR ? "" : " [ <a href='offers.php?action=edit_offer&amp;id=$id'>{$lang['table_edit']}</a> ]
                    [ <a href='offers.php?action=delete_offer&amp;id=$id'>{$lang['table_delete']}</a> ]") . "</h1></td>
                </tr>

                <tr>
                    <td class='rowhead' align='left' width='20%'>&nbsp;<strong>{$lang['table_image']}:</strong></td>
                    <td class='rowhead'><a href='{$arr['image']}' rel='lightbox'><img src='" . strip_tags($arr['image']) . "' width='' height='' alt='{$lang['img_alt_posted_image']}' title='{$lang['img_alt_posted_image']}' style='max-width : 600px;' /></a></td>
                </tr>

                <tr>
                    <td class='rowhead' align='left'>&nbsp;<strong>{$lang['table_desc']}:</strong></td>
                    <td class='rowhead'>" . format_comment($arr['description']) . "</td>
                </tr>

                <tr>
                    <td class='rowhead' align='left'>&nbsp;<strong>{$lang['table_cat']}:</strong></td>
                    <td class='rowhead'><img src='{$image_dir}caticons/" . security::html_safe($arr['cat_image']) . "' border='0' width='60' height='54' alt='" . security::html_safe($arr['cat_name']) . "' title='" . security::html_safe($arr['cat_name']) . "' /></td>
                </tr>

                <tr>
                    <td class='rowhead' align='left'>&nbsp;<strong>{$lang['table_link']}:</strong></td>
                    <td class='rowhead'><a class='altlink' href='" . security::html_safe($arr['link']) . "'  target='_blank'>" . security::html_safe($arr['link']) . "</a></td>
                </tr>

                <tr>
                    <td class='rowhead' align='left'>&nbsp;<strong>{$lang['table_votes']}:</strong></td>
                    <td class='rowhead'>
                        <span class='offers_vote_yes'>{$lang['table_yes']}: " . number_format($arr['vote_yes_count']) . "</span>$vote_yes<br />
                        <span class='offers_vote_no'>{$lang['table_no']}: " . number_format($arr['vote_no_count']) . "</span>$vote_no<br />$your_vote_was
                    </td>
                </tr>

                <tr>
                    <td class='rowhead' align='left'>&nbsp;<strong>{$lang['table_by']}</strong></td>
                    <td class='rowhead'>" . format_username($arr) . "</td>
                </tr>

                <tr>
                    <td class='rowhead' align='left'>&nbsp;<strong>{$lang['table_report']}</strong></td>
                    <td class='rowhead' align='left'>
                        <form method='post' action='report.php?type=Offer&amp;id=$id'>
                            <input type='submit' class='btn' value='{$lang['btn_report']}' onmouseover='this.className=\"btn\"' onmouseout='this.className=\"btn\"' />&nbsp;{$lang['table_break']}<a class='altlink' href='rules.php'>{$lang['table_rules']}</a>
                        </form>
                    </td>
                </tr>
            </table>";

        echo '<h1>' . $lang['title_comments'] . '' . htmlentities($arr['offer_name'], ENT_QUOTES) . '</h1>

        <p><a name="startcomments"></a></p>';

        if (user::$current['offercompos'] == 'no')
        {
            $commentbar = '<p align="center">' . $lang['text_comment_disabled'] . '</p>';
        }
        else
        {
            $commentbar = '<p align="center"><a class="btn" href="offers.php?action=add_comment&amp;id=' . $id . '">' . $lang['btn_add_comment'] . '</a></p>';
        }

        $count = $arr['comments'];

        if (!$count)
        {
            echo '<h2>' . $lang['text_no_comments'] . '</h2>';
        }
        else
        {
            //-- Get Stuff For The Pager --//
            $page    = isset($_GET['page']) ? (int)$_GET['page'] : 0;
            $perpage = isset($_GET['perpage']) ? (int)$_GET['perpage'] : 10;

            list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'offers.php?action=offer_details&amp;id=' . $id, ($perpage == 10 ? '' : '&amp;perpage=' . $perpage) . '#comments');

            $subres = $db->query("SELECT comments_offer.id, text, user, comments_offer.added, editedby, editedat, avatar, warned, username, title, class, donor
                                 FROM comments_offer
                                 LEFT JOIN users ON comments_offer.user = users.id
                                 WHERE offer = $id
                                 ORDER BY comments_offer.id " . $LIMIT) or sqlerr(__FILE__, __LINE__);

            $allrows       = array();
            while ($subrow = $subres->fetch_assoc())
            $allrows[]     = $subrow;

            echo $commentbar. '<a name="comments"></a>';
            echo ($count > $perpage) ? '<p>' . $menu . '<br /></p>' : '<br />';

            echo comment_table($allrows);

            echo ($count > $perpage) ? '<p>' . $menu . '<br /></p>' : '<br />';
        }

        echo $commentbar;

        echo site_footer();

    break;

    //-- Add A New Offer --//
    case 'add_new_offer':

        $offer_name = strip_tags(isset($_POST['offer_name']) ? trim($_POST['offer_name']) : '');
        $image      = strip_tags(isset($_POST['image']) ? trim($_POST['image']) : '');
        $body       = (isset($_POST['description']) ? trim($_POST['description']) : '');
        $link       = strip_tags(isset($_POST['link']) ? trim($_POST['link']) : '');

        //-- Do The Cat List :D --//
        $category_drop_down = '<select class="required" name="category"><option class="body" value="">' . $lang['form_select_cat'] . '</option>';

        $cats = cached::genrelist();

        foreach ($cats
                 AS
                 $row)
        {
            $category_drop_down .= '<option class="body" value="' . $row['id'] . '" ' . ($category == $row['id'] ? ' selected="selected" ' : '') . ' >' . security::html_safe($row['name']) . '</option>';
        }

        $category_drop_down .= '</select>';

        if (isset($_POST['category']))
            {
                $cat_res = $db->query('SELECT id AS cat_id, name AS cat_name, image AS cat_image
                                      FROM categories
                                      WHERE id = ' . $category);

                $cat_arr   = $cat_res->fetch_assoc();
                $cat_image = security::html_safe($cat_arr['cat_image']);
                $cat_name  = security::html_safe($cat_arr['cat_name']);
            }

        $username = security::html_safe(user::$current['username']);

        //-- If Posted And Not Preview, Process It :D --//
        if (isset($_POST['button']) && $_POST['button'] == 'Submit')
        {
            $db->query('INSERT INTO offers (offer_name, image, description, category, added, offered_by_user_id, link)
                        VALUES (' . sqlesc($offer_name) . ', ' . sqlesc($image) . ', ' . sqlesc($body) . ', ' . $category . ', ' . vars::$timestamp . ', ' . user::$current['id'] . ',  ' . sqlesc($link) . ');');

            $new_offer_id = $db->insert_id;

            header('Location: offers.php?action=offer_details&new=1&id=' . $new_offer_id);
            die();
        }

        //-- Start Page --//
         echo site_header('' . $lang['title_add_offer'] . '.', false);

         echo '<table class="main" border="0" align="center" width="750px" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="embedded" align="center">
                        <h1 style="text-align : center;">' . $lang['table_new_offer'] . '</h1>' . $top_menu . '
                        <form method="post" action="offers.php?action=add_new_offer" name="offer_form" id="offer_form">
                            ' . (isset($_POST['button']) && $_POST['button'] == '' . $lang['table_preview'] . '' ? '<br />

                            <table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">
                                <tr>
                                    <td class="colhead" align="center" colspan="2"><h1>' . security::html_safe($offer_name) . '</h1></td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right" width="15%">' . $lang['table_image'] . ':</td>
                                    <td class="rowhead"><img src="' . security::html_safe($image) . '" border="0" width="" height="" alt="' . $lang['img_alt_posted_image'] . '" title="' . $lang['img_alt_posted_image'] . '" style="max-width : 600px;" /></td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right" width="15%">' . $lang['table_desc'] . ':</td>
                                    <td class="rowhead">' . format_comment($body) . '</td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right" width="15%">' . $lang['table_cat'] . ':</td>
                                    <td class="rowhead"><img src="' . $image_dir . 'caticons/' . security::html_safe($cat_image) . '" border="0" width="60" height="54" alt="' . security::html_safe($cat_name) . '" title="' . security::html_safe($cat_name) . '" /></td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right" width="15%">' . $lang['table_link'] . ':</td>
                                    <td class="rowhead"><a class="altlink" href="' . security::html_safe($link) . '" target="_blank">' . security::html_safe($link) . '</a></td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right" width="15%">' . $lang['table_offer_by'] . ':</td>
                                    <td class="rowhead">' . $username . '</td>
                                </tr>

                            </table><br />' : '') . '

                            <table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">
                                <tr>
                                    <td class="colhead" align="center" colspan="2"><h1>' . $lang['table_make_offer'] . '</h1></td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="center" colspan="2">' . $lang['table_offer_text1'] . '<a class="altlink" href="search.php">' . $lang['table_offer_text2'] . '</a>' . $lang['table_offer_text3'] . '<br /><br />' . $lang['table_offer_text4'] . '</td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_name'] . ':</td>
                                    <td class="rowhead">
                                        <input class="required" type="text" size="80" name="offer_name" value="' . security::html_safe($offer_name) . '"  />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_image'] . ':</td>
                                    <td class="rowhead">
                                        <input type="text" class="required" size="80" name="image" value="' . security::html_safe($image) . '" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_link'] . ':</td>
                                    <td class="rowhead">
                                        <input type="text" size="80"  name="link" value="' . security::html_safe($link) . '" class="required" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_cat'] . ':</td>
                                    <td class="rowhead">' . $category_drop_down . '</td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_desc'] . ':</td>
                                    <td class="rowhead">' . textbbcode("compose", "description", $body) . '</td>
                                </tr>

                                <tr>
                                    <td colspan="2" align="center" class="rowhead">
                                        <input type="submit" name="button" class="btn" value="' . $lang['btn_preview'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                                        <input type="submit" name="button" class="btn" value="' . $lang['gbl_btn_submit'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>
            </table><br />

            <script type="text/javascript" src="scripts/jquery.validate.min.js"></script>
            <script type="text/javascript">
            <!--

            $(document).ready(function()
            {
                //=== form validation
                $("#offer_form").validate();
            }
            );

            -->
            </script>';

        echo site_footer();

    break;

//-- Delete An Offer --//
    case 'delete_offer':

        if (user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_denied']}");
        }

        if (!isset($id) || !is_valid_id($id))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_bad_id']}");
        }

        $res = $db->query('SELECT offer_name, offered_by_user_id
                          FROM offers
                          WHERE id = ' . $id) or sqlerr(__FILE__,__LINE__);

        $arr = $res->fetch_assoc();

        if (!$arr)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_inv_id']}");
        }

        if ($arr['offered_by_user_id'] !== user::$current['id'] && user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_denied']}");
        }

        if (!isset($_GET['do_it']))
        {
            error_message_center("warn",
                                 "{$lang['gbl_sanity']}",
                                 "{$lang['text_del_offer']}<b>: - " . security::html_safe($arr['offer_name']) . "</b>.<br /><br />
                                 <a class='btn' href='offers.php?action=delete_offer&amp;id=$id&amp;do_it=666'>{$lang['text_click_confirm']}</a>");
        }
        else
        {
            $db->query("DELETE FROM offers
                        WHERE id = $id");

            $db->query("DELETE FROM offer_votes
                        WHERE offer_id = $id");

            $db->query("DELETE FROM comments_offer
                        WHERE id = $id");

            header('Location: /offers.php?offer_deleted=1');

            die();
        }

        echo site_footer();

    break;


//-- Edit An Offer --//
    case 'edit_offer':

        if (user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_denied']}");
        }

        if (!isset($id) || !is_valid_id($id))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_bad_id']}");
        }

        $edit_res = $db->query("SELECT offer_name, image, description, category, offered_by_user_id, link
                               FROM offers
                               WHERE id = $id") or sqlerr(__FILE__,__LINE__);

        $edit_arr = $edit_res->fetch_assoc();

        if (user::$current['class'] < UC_MODERATOR && user::$current['id'] !== $edit_arr['offered_by_user_id'])
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_not_offer']}");
        }

        $offer_name = strip_tags(isset($_POST['offer_name']) ? trim($_POST['offer_name']) : $edit_arr['offer_name']);
        $image      = strip_tags(isset($_POST['image']) ? trim($_POST['image']) : $edit_arr['image']);
        $body       = (isset($_POST['description']) ? trim($_POST['description']) : $edit_arr['description']);
        $link       = strip_tags(isset($_POST['link']) ? trim($_POST['link']) : $edit_arr['link']);
        $category   = (isset($_POST['category']) ? intval($_POST['category']) : $edit_arr['category']);

        //-- Do The Cat List :D --//
        $category_drop_down = '<select class="required" name="category"><option class="body" value="">' . $lang['form_select_cat'] . '</option>';

        $cats = cached::genrelist();

        foreach ($cats
                 AS
                 $row)
        {
            $category_drop_down .= '<option class="body" value="' . $row['id'] . '" ' . ($category == $row['id'] ? ' selected="selected" ' : '') . ' >' . security::html_safe($row['name']) . '</option>';
        }

        $category_drop_down .= '</select>';

        $cat_res = $db->query('SELECT id AS cat_id, name AS cat_name, image AS cat_image
                              FROM categories
                              WHERE id = ' . $category);

        $cat_arr   = $cat_res->fetch_assoc();
        $cat_image = security::html_safe($cat_arr['cat_image']);
        $cat_name  = security::html_safe($cat_arr['cat_name']);

        //-- If Posted And Not Preview, Process It :D --//
        if (isset($_POST['button']) && $_POST['button'] == 'Edit')
        {
            $db->query('UPDATE offers
                        SET offer_name = ' . sqlesc($offer_name) . ', image = ' . sqlesc($image) . ', description = ' . sqlesc($body) . ',category = ' . sqlesc($category) . ', link = ' . sqlesc($link) . '
                        WHERE id = ' . $id);

            header('Location: offers.php?action=offer_details&edited=1&id=' . $id);
            die();
        }

        //-- Start Page --//
        echo site_header('' . $lang['title_edit'] . '.', false);

        echo '<table class="main" border="0" align="center" width="80%" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="embedded" align="center">
                        <h1 style="text-align: center;">' . $lang['table_edit_offer'] . '</h1>' . $top_menu . '
                        <form method="post" action="offers.php?action=edit_offer" name="offer_form" id="offer_form">
                            <input type="hidden" name="id" value="' . $id . '" />' . (isset($_POST['button']) && $_POST['button'] == 'Preview' ? '<br />

                            <table border="0" align="center" width="700px" cellspacing="0" cellpadding="5">
                                <tr>
                                    <td class="colhead" align="center" colspan="2"><h1>' . security::html_safe($offer_name) . '</h1></td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_image'] . ':</td>
                                    <td class="rowhead" align="left"><img src="' . security::html_safe($image) . '" alt="image" style="max-width : 600px;" /></td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_desc'] . ':</td>
                                    <td class="rowhead" align="left">' . format_comment($body) . '</td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_cat'] . ':</td>
                                    <td class="rowhead" align="left"><img src="' . $image_dir . 'caticons/' . security::html_safe($cat_image) . '"  border="0" alt="' . security::html_safe($cat_name) . '" /></td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_link'] . ':</td>
                                    <td class="rowhead" align="left"><a class="altlink" href="' . security::html_safe($link) . '" target="_blank">' . security::html_safe($link) . '</a></td>
                                </tr>

                            </table><br />' : '') . '

                            <table border="0" align="center" width="700px" cellspacing="0" cellpadding="5">
                                <tr>
                                    <td class="colhead" align="center" colspan="2"><h1>' . $lang['table_edit'] . ' :- ' . security::html_safe($offer_name) . '</h1></td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="center" colspan="2">' . $lang['table_offer_text4'] . '</td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_name'] . ':</td>
                                    <td class="rowhead" align="left">
                                        <input type="text" class="required" size="80" name="offer_name" value="' . security::html_safe($offer_name) . '" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_image'] . ':</td>
                                    <td align="left">
                                        <input type="text" class="required" size="80" name="image" value="' . security::html_safe($image) . '" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_link'] . ':</td>
                                    <td class="rowhead" align="left">
                                        <input type="text" class="required" size="80" name="link" value="' . security::html_safe($link) . '" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_cat'] . ':</td>
                                    <td class="rowhead" align="left">' . $category_drop_down . '</td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="right">' . $lang['table_desc'] . ':</td>
                                    <td class="rowhead" align="left">' . textbbcode("compose" ,"description", $body) . '</td>
                                </tr>

                                <tr>
                                    <td class="rowhead" align="center" colspan="2">
                                        <input type="submit" name="button" class="btn" value="' . $lang['btn_preview'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                                        <input type="submit" name="button" class="btn" value="' . $lang['btn_edit'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                                    </td>
                                </tr>

                            </table>
                        </form>
                    </td>
                </tr>
            </table><br />

            <script type="text/javascript" src="scripts/jquery.validate.min.js"></script>
            <script type="text/javascript">

            <!--
            $(document).ready(function()
            {
                //=== form validation
                $("#offer_form").validate();
            });
            -->

            </script>';

        echo site_footer();

    break;

    //-- Add A Comment --//
    case 'add_comment':

        if (user::$current['offercompos'] == 'no')
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_comment_disabled']}");
        }

        if (!isset($id) || !is_valid_id($id))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_bad_id']}");
        }

            $res = $db->query("SELECT offer_name
                              FROM offers
                              WHERE id = $id") or sqlerr(__FILE__,__LINE__);

            $arr = $res->fetch_array(MYSQLI_BOTH);

        if (!$arr)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_no_offer_id']}");
        }

        if(isset($_POST['button']) && $_POST['button'] == 'Save')
        {
            $text = (isset($_POST['text']) ? trim($_POST['text']) : '');

            if (!$text)
            {
                error_message_center("error",
                                     "{$lang['gbl_error']}",
                                     "{$lang['err_comment_empty']}");
            }

            $db->query("INSERT INTO comments_offer (user, offer, added, text, ori_text)
                       VALUES (" . user::$current['id'] . ", $id, '" . get_date_time() . "', " . sqlesc($text) . ", " . sqlesc($text) . ")");

            $newid = $db->insert_id;

            $db->query("UPDATE offers
                       SET comments = comments + 1
                       WHERE id = $id");

            header('Location: /offers.php?action=offer_details&id=' . $id . '&viewcomm=' . $newid . '#comm' . $newid);
            die();
        }

        $text = security::html_safe((isset($_POST['text']) ? $_POST['text'] : ''));

        $res = $db->query("SELECT avatar
                          FROM users
                          WHERE id = " . user::$current['id']) or sqlerr(__FILE__,__LINE__);

        $row = $res->fetch_array(MYSQLI_BOTH);

        $avatar = ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? security::html_safe($row['avatar']) : '');

        if (!$avatar)
        {
            $avatar = "{$image_dir}default_avatar.gif";
        }

        echo site_header("{$lang['title_add_comment']}", false);

        echo $top_menu . '<form method="post" action="offers.php?action=add_comment">
                        <input type="hidden" name="id" value="' . $id . '"/>
                        ' . (isset($_POST['button']) && $_POST['button'] == 'Preview' ? '

            <table border="0" align="center" width="80%" cellspacing="5" cellpadding="5">
                <tr>
                    <td class="colhead" colspan="2"><h1>' . $lang['table_preview'] . '</h1></td>
                </tr>

                <tr>
                    <td align="center" width="100"><img src="' . $avatar . '" width="125" height="125" border="0" alt="" title="" /></td>
                    <td class="rowhead" align="left" valign="top">' . format_comment($text) . '</td>
                </tr>
            </table><br />' : '') . '

            <table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">
                <tr>
                    <td class="colhead" align="center" colspan="2"><h1>' . $lang['table_add_comment'] . '"' . $arr['offer_name'] . '"</h1></td>
                </tr>

                <tr>
                    <td class="rowhead" align="right" valign="top"><b>' . $lang['table_comment'] . ':</b></td>
                    <td class="rowhead">' . textbbcode("compose", "text", $text) . '</td>
                </tr>

                <tr>
                    <td class="rowhead" align="center" colspan="2">
                        <input name="button" type="submit" class="btn" value="' . $lang['btn_preview'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                        <input name="button" type="submit" class="btn" value="' . $lang['btn_save'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                    </td>
                </tr>
            </table>
        </form>';

        //-- View Existing Comments --//
        $res = $db->query("SELECT c.offer, c.id, c.text, c.added, c.editedby, c.editedat, u.id, u.username, u.warned, u.enabled, u.donor, u.class, u.avatar, u.title
                          FROM comments_offer AS c
                          LEFT JOIN users AS u ON c.user = u.id
                          WHERE offer = $id
                          ORDER BY c.id DESC LIMIT 5");

        $allrows    = array();

        while ($row = $res->fetch_assoc())

        $allrows[]  = $row;

        if (count($allrows))
        {
            echo "<h2>{$lang['text_reverse_order']}</h2>";
            echo comment_table($allrows);
        }

        echo site_footer();

    break;

    //-- Edit A Comment --//
    case 'edit_comment':

        if (user::$current['offercompos'] == 'no')
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_comment_disabled']}");
        }

        if (!isset($comment_id) || !is_valid_id($comment_id))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_bad_id']}");
        }

        $res = $db->query('SELECT c.*, o.offer_name
                          FROM comments_offer AS c
                          LEFT JOIN offers AS o ON c.offer = o.id
                          WHERE c.id = ' . $comment_id) or sqlerr(__FILE__,__LINE__);

        $arr = $res->fetch_assoc();

        if (!$arr)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_inv_id']}");
        }

        if ($arr['user'] != user::$current['id'] && user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_denied']}");
        }

        $body = security::html_safe((isset($_POST['edit']) ? $_POST['edit'] : $arr['text']));

        if (isset($_POST['button']) && $_POST['button'] == 'Edit')
        {
            if ($body == '')
            {
                error_message_center("error",
                                     "{$lang['gbl_error']}",
                                     "{$lang['err_comment_empty']}");
            }

            $text     = sqlesc($body);
            $editedat = sqlesc(get_date_time());

            $db->query("UPDATE comments_offer
                       SET text = $text, editedat = $editedat, editedby = " . user::$current['id'] . "
                       WHERE id = $comment_id") or sqlerr(__FILE__, __LINE__);

            header('Location: /offers.php?action=offer_details&id=' . $id . '&viewcomm=' . $comment_id . '#comm' . $comment_id);
            die();
        }

        $avatar = ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? security::html_safe($row['avatar']) : "");

        if (!$avatar)
        {
            $avatar = "{$image_dir}default_avatar.gif";
        }

        echo site_header('' . $lang['title_edit_comment'] . ' "' . htmlentities($arr['offer_name'], ENT_QUOTES ) . '"', false);

        echo $top_menu.'<form method="post" action="offers.php?action=edit_comment">
                            <input type="hidden" name="id" value="' . $arr['offer'] . '"/>
                            <input type="hidden" name="comment_id" value="' . $comment_id . '"/>' .
                            (isset($_POST['button']) && $_POST['button'] == 'Preview' ? '

            <table border="0" align="center" width="80%" cellspacing="5" cellpadding="5">
                <tr>
                    <td class="colhead" colspan="2"><h1>' . $lang['table_preview'] . '</h1></td>
                </tr>

                <tr>
                    <td align="center" width="100"><img src=' . $avatar . ' width="125" height="125" border="0" alt="" title="" /></td>
                    <td class="rowhead" align="left" valign="top">' . format_comment($body) . '</td>
                </tr>
            </table><br />' : '') . '

            <table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">
                <tr>
                    <td class="colhead" align="center" colspan="2"><h1>' . $lang['table_edit_comment_to'] . ' "' . security::html_safe($arr['offer_name']) . '"</h1></td>
                </tr>

                <tr>
                    <td class="rowhead" align="right" valign="top" ><b>' . $lang['table_edit_comment'] . ':</b></td>
                    <td class="rowhead">' . textbbcode("compose", "edit", $body) . '</td>
                </tr>

                <tr>
                    <td align="center" colspan="2" class="rowhead">
                        <input name="button" type="submit" class="btn" value="' . $lang['btn_preview'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                        <input name="button" type="submit" class="btn" value="' . $lang['btn_edit'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                    </td>
                </tr>
            </table>
        </form>';

        echo site_footer();

    break;

    //-- View Original Comment --//
    case 'view_original';

        if (user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_denied']}");
        }

        if (!isset($comment_id) || !is_valid_id($comment_id))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_bad_id']}");
        }

        $res = $db->query('SELECT c.*, o.offer_name
                          FROM comments_offer AS c
                          LEFT JOIN offers AS o ON c.offer = o.id
                          WHERE c.id = ' . $comment_id) or sqlerr(__FILE__,__LINE__);

        $arr = $res->fetch_assoc();

        if (!$arr)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_inv_id']}");
        }

        if ($arr['user'] != user::$current['id'] && user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_denied']}");
        }

        site_header("{$lang['title_orig_comment']}", false);

        print("<h1>{$lang['text_orig_comment']}#$comment_id</h1>\n");
        print("<table width='100%' border='1' cellspacing='0' cellpadding='5'>");
        print("<tr>");
        print("<td class='comment'>\n");
        print format_comment($arr['ori_text']);
        print("</td>");
        print("</tr>");
        print("</table><br />");

        $returnto = security::html_safe($_SERVER['HTTP_REFERER']);

        if ($returnto)
        {
            error_message_center("info",
                                 "{$lang['gbl_info']}",
                                 "{$lang['text_ret_offers']}<br /><br />
                                  <a class='btn' class='altlink' href='$returnto'>{$lang['text_click_here']}</a>");
        }

        site_footer();

    break;

    //-- DELETE A COMMENT --//
     case 'delete_comment':

        if (user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_denied']}");
        }

        if (!isset($comment_id) || !is_valid_id($comment_id))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_bad_id']}");
        }

        $res = $db->query("SELECT user, offer
                          FROM comments_offer
                          WHERE id = $comment_id") or sqlerr(__FILE__,__LINE__);

        $arr = $res->fetch_assoc();

        if (!$arr)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_inv_id']}");
        }

        if ($arr['user'] != user::$current['id'] && user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_denied']}");
        }

        if (!isset($_GET['do_it']))
        {
            error_message_center("warn",
                                 "{$lang['gbl_sanity']}",
                                 "{$lang['text_del_sure']}<br /><br />
                                  <a class='btn' href='offers.php?action=delete_comment&amp;id={$arr['offer']}&amp;comment_id=$comment_id&amp;do_it=666'>{$lang['text_click_confirm']}</a>");
        }
        else
        {
            $db->query("DELETE
                       FROM comments_offer
                       WHERE id = $comment_id");

            $db->query("UPDATE offers
                       SET comments = comments - 1
                       WHERE id = " . (int)$arr['offer']);

            header('Location: /offers.php?action=offer_details&id=' . $id . '&comment_deleted=1');

            die();
        }

    break;

    //-- ALTER AN OFFER STATUS --//
    case 'alter_status':

        if (user::$current['class'] < UC_MODERATOR)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_denied']}");
        }

        $set_status = strip_tags(isset($_POST['set_status']) ? $_POST['set_status'] : '');

        //-- Add All Possible Status' Check Them To Be Sure They Are Ok --//
        $ok_stuff = array('approved',
                          'pending',
                          'denied');

        //-- Check It --//
        $change_it = (in_array($set_status, $ok_stuff) ? $set_status : 'poop');

        if ($change_it == 'poop') //-- Ok, So I Had A Bit Of Fun With That *blush --//
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_fun']}");
        }

        //-- Get Torrent Name :P --//
        $res_name = $db->query("SELECT offer_name, offered_by_user_id
                               FROM offers
                               WHERE id = $id") or sqlerr(__FILE__, __LINE__);

        $arr_name = $res_name->fetch_assoc();

        if ($change_it == 'approved')
        {
            $time_now = sqlesc(get_date_time());
            $subject  = sqlesc("{$lang['msg_subject_approved']}");
            $message  = sqlesc("{$lang['msg_approved1']}\n\n{$lang['msg_approved2']}\n\n{$lang['msg_approved3']}[url=" . $site_url . "/upload.php]{$lang['msg_approved4']}" . security::html_safe($arr_name['offer_name']) . "[/url]{$lang['msg_approved5']}\n{$lang['msg_approved6']}\n\n [url=" . $site_url . "/offers.php?action=offer_details&id=" . $id . "]{$lang['msg_approved7']}[/url]{$lang['msg_approved8']}");

            $db->query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
                       VALUES(0, ' . (int)$arr_name['offered_by_user_id'] . ', ' . $time_now . ', ' . $message . ', ' . $subject . ', \'yes\', 1)') or sqlerr(__FILE__, __LINE__);
        }

        if ($change_it == 'denied')
        {
            $time_now = sqlesc(get_date_time());
            $subject  = sqlesc("{$lang['msg_subject_denied']}");
            $message  = sqlesc("{$lang['msg_denied1']}\n\n{$lang['msg_denied2']}\n\n  [url=" . $site_url . "/offers.php?action=offer_details&id=" . $id . "]" . security::html_safe($arr_name['offer_name']) . "[/url]{$lang['msg_denied3']}" . user::$current['username'] . "{$lang['msg_denied4']}");

            $db->query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
                              VALUES(0, ' . (int)$arr_name['offered_by_user_id'] . ', ' . $time_now . ', ' . $message . ', ' . $subject . ', \'yes\', 1)') or sqlerr(__FILE__, __LINE__);
        }

        //--  Ok, Looks Good :d Let's Set That Status! --//
        $db->query("UPDATE offers
                   SET status = " . sqlesc($change_it) . "
                   WHERE id = $id") or sqlerr(__FILE__, __LINE__);

        header("Location: /offers.php?action=offer_details&status_changed=1&id=$id");
        die();

    break;

} //-- End All Actions / Switch --//

//-- Functions N' Stuff --//
function comment_table($rows)
{
    global $image_dir, $lang, $db;

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

            if ($title == '')
            {
                $title = get_user_class_name($row['class']);
            }
            else
            {
                $title = security::html_safe($title);
            }

            print("<a name='comm{$row['id']}' href='userdetails.php?id={$row['user']}'><span style='font-weight : bold;'>" . security::html_safe($row['username']) . "</span></a>" . ($row['donor'] == 'yes' ? "<img src='{$image_dir}star.png' width='16' height='16' border='0' alt='{$lang['img_alt_donor']}' title='{$lang['img_alt_donor']}' />" : '') . ($row['warned'] == 'yes' ? "<img src='{$image_dir}warned.png' width='16' height='16' border='0' alt='{$lang['img_alt_warned']}' title='{$lang['img_alt_warned']}' />" : '') . " ($title)\n");
        }
        else
        {
            print("<a name='comm{$row['id']}'><span style='font-style : italic;'>{$lang['text_orphaned']}</span></a>\n");
        }

        if (user::$current['offercompos'] == 'no')
        {
            if ($row['user'] == user::$current['id'])
            {
                print("{$lang['text_at']}{$row['added']}{$lang['text_gmt']}&nbsp;&nbsp;<span class='offers_comment_disabled'>{$lang['text_edit_disabled']}</span></p> ");
            }
        }
        else
        {
            print("{$lang['text_at']}{$row['added']}{$lang['text_gmt']}&nbsp;&nbsp;
                " . ($row['user'] != user::$current['id'] ? "<a class='btn' href='report.php?type=Offer_Comment&amp;id={$row['id']}'>{$lang['btn_report_comm']}</a>" : "") .
                ($row['user'] == user::$current['id'] || get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='offers.php?action=edit_comment&amp;comment_id={$row['id']}'>{$lang['btn_edit']}</a>" : '') .
                (get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='offers.php?action=delete_comment&amp;comment_id={$row['id']}'>{$lang['btn_delete']}</a>" : '') .
                ($row['editedby'] && get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='offers.php?action=view_original&amp;comment_id={$row['id']}'>{$lang['btn_view_orig']}</a>" : '') . "</p>\n");
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
                                   WHERE id = " . sqlesc((int)$row['editedby'])) or sqlerr(__FILE__, __LINE__);

            $arr_user = $res_user->fetch_assoc();

            $text .= "<p><span style='font-size : x-small; '>{$lang['text_last_edit']}<a href='/userdetails.php?id=" . (int)$row['editedby'] . "'><span style='font-weight : bold;'>" . htmlsafechars($arr_user['username']) . "</span></a>{$lang['text_at']}{$row['editedat']} {$lang['text_gmt']}</span></p>\n";
        }

        begin_table(true);

        print("<tr valign='top'>\n");
        print("<td align='center' width='100'><img src='{$avatar}' width='125' height='125' border='0' alt='' title='' /></td>\n");
        print("<td class='text'>$text</td>\n");
        print("</tr>\n");

        end_table();
    }

    end_frame();

}

?>