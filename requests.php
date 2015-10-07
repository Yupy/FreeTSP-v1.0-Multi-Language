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

$lang = array_merge(load_language('requests'),
                    load_language('func_bbcode'),
                    load_language('global'));

if (user::$current['class'] < UC_POWER_USER)
{
     error_message_center("error",
                          "{$lang['gbl_sorry']}",
                          "{$lang['err_pu_only']}");
}

//-- Possible Stuff To Be $_GETting lol --//
$id              = (isset($_GET['id']) ? intval($_GET['id']) :  (isset($_POST['id']) ? intval($_POST['id']) : 0));
$comment_id      = (isset($_GET['comment_id']) ? intval($_GET['comment_id']) :  (isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0));
$category        = (isset($_GET['category']) ? intval($_GET['category']) : (isset($_POST['category']) ? intval($_POST['category']) : 0));
$requested_by_id = isset($_GET['requested_by_id']) ? intval($_GET['requested_by_id']) : 0;
$vote            = isset($_POST['vote']) ? intval($_POST['vote']) : 0;
$posted_action   = strip_tags((isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '')));

//-- Add All Possible Actions Here And Check Them To Be Sure They Are Ok --//
$valid_actions = array('add_new_request',
                       'delete_request',
                       'edit_request',
                       'request_details',
                       'vote',
                       'add_comment',
                       'edit_comment',
                       'view_orig_comment',
                       'delete_comment');

//-- Check Posted Action, And If No Action Was Posted, Show The Default Page --//
$action = (in_array($posted_action, $valid_actions) ? $posted_action : 'default');

//-- Top Menu :D --//
$top_menu = "<p style='text-align: center;'><a class='altlink' href='requests.php'>{$lang['text_view_requests']}</a> || <a class='altlink' href='requests.php?action=add_new_request'>{$lang['text_make_request']}</a></p>";

switch ($action)
{

//-- Let Them Vote On It --//
    case 'vote':

    if (!isset($id) || !is_valid_id($id) || !isset($vote) || !is_valid_id($vote))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_bad_id_vote']}");
    }

    //-- See If They Voted Yet --//
    $res_did_they_vote = $db->query("SELECT vote
                                    FROM request_votes
                                    WHERE user_id = " . user::$current['id'] . "
                                    AND request_id = $id");

    $row_did_they_vote = $res_did_they_vote->fetch_row();

    if ($row_did_they_vote[0] == "")
    {
        $yes_or_no = ($vote == 1 ? "yes" : "no");

        $db->query("INSERT INTO request_votes (request_id, user_id, vote)
                   VALUES ($id, " . user::$current['id'] . ", '" . $yes_or_no . "')");

        $db->query("UPDATE requests
                   SET " . ($yes_or_no == 'yes' ? 'vote_yes_count = vote_yes_count + 1' : 'vote_no_count = vote_no_count + 1') . "
                   WHERE id = $id");

        header("Location: /requests.php?action=request_details&voted=1&id=$id");
        die();
    }
    else
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_voted']}");
    }

break;

//-- Default First Page With All The Requests --//
case 'default':

//-- Get Stuff For The Pager --//
$count_query = $db->query("SELECT COUNT(id)
                          FROM requests");

$count_arr = $count_query->fetch_row();
$count     = (int)$count_arr[0];
$page      = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$perpage   = isset($_GET['perpage']) ? (int)$_GET['perpage'] : 20;

list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'requests.php?' . ($perpage == 20 ? '' : '&amp;perpage=' . $perpage));

$main_query_res = $db->query("SELECT r.id AS request_id, r.request_name, r.category, r.added, r.requested_by_user_id, r.filled_by_user_id, r.filled_by_username, r.filled_torrent_id, r.vote_yes_count, r.vote_no_count, r.comments, u.id, u.username, u.warned, u.enabled, u.donor, u.class, t.id, t.anonymous, c.id AS cat_id, c.name AS cat_name, c.image AS cat_image
                             FROM requests AS r
                             LEFT JOIN categories AS c ON r.category = c.id
                             LEFT JOIN torrents AS t ON r.filled_torrent_id = t.id
                             LEFT JOIN users AS u ON r.requested_by_user_id = u.id
                             ORDER BY r.added
                             DESC $LIMIT");

echo site_header("{$lang['title_requests']}", false);

echo (isset($_GET['new']) ? "<h1>{$lang['text_req_added']}</h1>" : "" ) . (isset($_GET['request_deleted']) ? "<h1>{$lang['text_req_del']}</h1>" : "" ) . "";
echo "<div align='center'>$top_menu<br /></div>";
echo "<div align='center'>$menu<br /><br /></div>";

if ($count == 0)
{
     error_message_center("info",
                          "{$lang['gbl_sorry']}",
                          "{$lang['err_no_reqs']}");
}

echo "<table border='0' align='center' width='80%' cellspacing='0' cellpadding='5'>
         <tr>
             <td class='colhead' align='center'>{$lang['table_type']}</td>
             <td class='colhead' align='center'>{$lang['table_name']}</td>
             <td class='colhead' align='center'>{$lang['table_added']}</td>
             <td class='colhead' align='center'>{$lang['table_comm']}</td>
             <td class='colhead' align='center'>{$lang['table_votes']}</td>
             <td class='colhead' align='center'>{$lang['table_req_by']}</td>
             <td class='colhead' align='center'>{$lang['table_filled']}</td>
             <td class='colhead' align='center'>{$lang['table_filled_by']}</td>
         </tr>";

while ($main_query_arr = $main_query_res->fetch_assoc())
{
    echo '<tr>
            <td class="rowhead" align="center" style="margin : 0; padding : 1;"><img src="' . $image_dir . 'caticons/' . security::html_safe($main_query_arr['cat_image']) . '" width="60" height="54" border="0" alt="' . security::html_safe($main_query_arr['cat_name']) . '"  title="' . security::html_safe($main_query_arr['cat_name']) . '"/></td>

            <td class="rowhead" align="center"><a class="altlink" href="requests.php?action=request_details&amp;id=' . $main_query_arr['request_id'] . '">' . security::html_safe($main_query_arr['request_name']) . '</a></td>

            <td class="rowhead" align="center">' . get_date_time($main_query_arr['added'], 'LONG') . '</td>

            <td class="rowhead" align="center">' . number_format($main_query_arr['comments']) . '</td>

            <td class="rowhead" align="center">' . $lang['table_yes'] . ': ' . number_format($main_query_arr['vote_yes_count']) . '<br />' . $lang['table_no'] . ': ' . number_format($main_query_arr['vote_no_count']) . '</td>

            <td class="rowhead" align="center">' . security::html_safe($main_query_arr['username']) . '</td>

            <td class="rowhead" align="center">' . ($main_query_arr['filled_by_user_id'] > 0 ? '<a href="details.php?id=' . $main_query_arr['filled_torrent_id'] . '" title="' . $lang['title_goto_page'] . '"><span class="requests_filled_yes">' . $lang['table_yes'] . '</span></a>' :'<span class="requests_filled_no">' . $lang['table_no'] . '</span>') . '</td>';

    if ($main_query_arr['filled_torrent_id'] == 0)
    {
        echo'<td class="rowhead" align="center">' . $lang['table_still'] . '<br />' . $lang['table_waiting'] . '</td>';
    }

    if ($main_query_arr['filled_torrent_id'] >= 1)
    {
        if ($main_query_arr['anonymous'] == "no")
        {
            echo'<td class="rowhead" align="center">' . security::html_safe($main_query_arr['filled_by_username']) . '</td>';
        }

        if ($main_query_arr['anonymous'] == "yes")
        {
            echo'<td class="rowhead" align="center"><i>' . $lang['table_anonymous'] . '</i></td>';
        }
    }
    echo '</tr>';
}

echo '</table>';
echo '<div align="center"><br />' . $menu . '<br /></div>';

echo site_footer();

break;

//-- Details Page For The Request --//
case 'request_details':

    if (!isset($id) || !is_valid_id($id))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_try_again']}");
    }

    $res = $db->query("SELECT r.id AS request_id, r.request_name, r.category, r.added, r.requested_by_user_id, r.filled_by_user_id, r.filled_torrent_id, r.vote_yes_count, r.vote_no_count, r.image, r.link, r.description, r.comments, u.id, u.username, u.warned, u.enabled, u.donor, u.class, u.uploaded, u.downloaded, c.name AS cat_name, c.image AS cat_image
                      FROM requests AS r
                      LEFT JOIN categories AS c ON r.category = c.id
                      LEFT JOIN users AS u ON r.requested_by_user_id = u.id
                      WHERE r.id = $id");

    $arr = $res->fetch_assoc();

    //-- See If They Voted Yet --//
    $res_did_they_vote = $db->query("SELECT vote
                                    FROM request_votes
                                    WHERE user_id = " . user::$current['id'] . "
                                    AND request_id = $id");

    $row_did_they_vote = $res_did_they_vote->fetch_row();

    if ($row_did_they_vote[0] == '')
    {
        $vote_yes = '<form method="post" action="requests.php">
                        <input type="hidden" name="action" value="vote" />
                        <input type="hidden" name="id" value="' . $id . '" />
                        <input type="hidden" name="vote" value="1" />
                        <input type="submit" class="btn" value="' . $lang['btn_vote_yes'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                    </form> ~ ' . $lang['text_notif_filled'] . '';

        $vote_no = '<form method="post" action="requests.php">
                        <input type="hidden" name="action" value="vote" />
                        <input type="hidden" name="id" value="' . $id . '" />
                        <input type="hidden" name="vote" value="2" />
                        <input type="submit" class="btn" value="' . $lang['btn_vote_no'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                    </form> ~ ' . $lang['text_stick'] . '';

        $your_vote_was = '';
    }
    else
    {
        $vote_yes      = '';
        $vote_no       = '';
        $your_vote_was = '' . $lang['text_your_vote'] . ': ' . $row_did_they_vote[0] . ' ';
    }

    //-- Start Page --//
    echo site_header('' . $lang['title_header']  . ': ' . security::html_safe($arr['request_name']), false);

    echo (isset($_GET['voted']) ? '<h1>' . $lang['text_vote_added'] . '</h1>' : '' ) . (isset($_GET['comment_deleted']) ? '<h1>' . $lang['text_comment_delete'] . '</h1>' : '' ) . $top_menu . '

       <table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">
           <tr>
                <td class="colhead" align="center" colspan="2">
                    <h1>'.security::html_safe($arr['request_name']).(user::$current['class'] < UC_MODERATOR ? '' : ' [ <a href="requests.php?action=edit_request&amp;id=' . $id . '">' . $lang['table_edit'] . '</a> ][ <a href="requests.php?action=delete_request&amp;id=' . $id . '">' . $lang['table_delete'] . '</a> ]') . '</h1>
                </td>
            </tr>

        <tr>
            <td class="rowhead" align="left" width="20%">&nbsp;<strong>' . $lang['table_image'] . ':</strong></td>
            <td class="rowhead" align="left"><a href="' . $arr['image'] . '" rel="lightbox"><img src="' . strip_tags($arr['image']) . '" width="" height="" alt="' . $lang['img_alt_posted_image'] . '" title="' . $lang['img_alt_posted_image'] . '" style="max-width:600px;" /></a></td>
        </tr>

        <tr>
            <td class="rowhead" align="left">&nbsp;<strong>' . $lang['table_desc'] . ':</strong></td>
            <td class="rowhead" align="left">' . format_comment($arr['description']) . '</td>
        </tr>

        <tr>
            <td class="rowhead" align="left">&nbsp;<strong>' . $lang['table_cat'] . ':</strong></td>
            <td class="rowhead"><img src="' . $image_dir . 'caticons/' . security::html_safe($arr['cat_image']) . '" width="60" height="54" border="0" alt="' . security::html_safe($arr['cat_name']) . '" title="' . security::html_safe($arr['cat_name']) . '" /></td>
        </tr>

        <tr>
            <td class="rowhead" align="left">&nbsp;<strong>' . $lang['table_link'] . ':</strong></td>
            <td class="rowhead" align="left"><a class="altlink" href="' . security::html_safe($arr['link']) . '" target="_blank">' . security::html_safe($arr['link']) . '</a></td>
        </tr>

        <tr>
            <td class="rowhead" align="left">&nbsp;<strong>' . $lang['table_votes'] . ':</strong></td>
            <td class="rowhead" align="left">
                <span class="requests_vote_yes">' . $lang['table_yes'] . ': ' . number_format($arr['vote_yes_count']) . '</span> ' . $vote_yes . '<br />
                <span class="requests_vote_no">' . $lang['table_no'] . ': ' . number_format($arr['vote_no_count']) . '</span> ' . $vote_no . '<br /> ' . $your_vote_was . '
            </td>
        </tr>

        <tr>
            <td class="rowhead" align="left">&nbsp;<strong>' . $lang['table_req_by'] . ':</strong></td>
            <td class="rowhead" align="left">' . security::html_safe($arr['username']) . '</td>
        </tr>';

    if ($arr['filled_torrent_id'] > 0)
    {
        echo'<tr>
                 <td class="rowhead" align="left">&nbsp;<strong>' . $lang['table_filled'] . ':</strong></td>
                 <td class="rowhead" align="left"><a class="altlink" href="details.php?id=' . $arr['filled_torrent_id'] . '">' . $lang['table_click_view'] . '</a></td>
             </tr>';
    }

    echo'<tr>
             <td class="rowhead" align="left">&nbsp;<strong>' . $lang['table_report'] . '</strong></td>
             <td class="rowhead" align="left">
                <form method="post" action="report.php?type=Request&amp;id=' . $id . '">
                    <input type="submit" class="btn" value="' . $lang['btn_report'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />&nbsp;' . $lang['table_break'] . '<a class="altlink" href="rules.php">' . $lang['table_rules'] . '</a>
                </form>
            </td>
        </tr>

        </table>';

    echo'<h1>' . $lang['text_comm_for'] . '' . htmlentities($arr['request_name'], ENT_QUOTES ) . '</h1>
         <p><a name="startcomments"></a></p>';

    if (user::$current['requestcompos'] == 'no')
    {
        $commentbar = '<p align="center">' . $lang['text_comm_disabled'] . '</p>';
    }
    else
    {
        $commentbar = '<p align="center"><a class="btn" href="requests.php?action=add_comment&amp;id=' . $id . '">' . $lang['text_add_comm'] . '</a></p>';
    }

    $count = $arr['comments'];

    if (!$count)
    {
        echo '<h2>' . $lang['text_no_comm'] . '</h2>';
    }
    else
    {
        //-- Get Stuff For The Pager --//
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 0;
        $perpage = isset($_GET['perpage']) ? (int)$_GET['perpage'] : 20;

        list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'requests.php?action=request_details&amp;id=' . $id, ($perpage == 20 ? '' : '&amp;perpage=' . $perpage) . '#comments');

        $subres = $db->query("SELECT comments_request.id, text, user, comments_request.added, editedby, editedat, avatar, warned, username, title, class, donor
                             FROM comments_request
                             LEFT JOIN users ON comments_request.user = users.id
                             WHERE request = $id
                             ORDER BY comments_request.id $LIMIT") or sqlerr(__FILE__, __LINE__);

        while ($subrow = $subres->fetch_assoc())

        $allrows[] = $subrow;

        echo $commentbar.'<a name="comments"></a>';
        echo ($count > $perpage) ? '<p>' . $menu . '<br /></p>' : '<br />';

        echo comment_table($allrows);

        echo ($count > $perpage) ? '<p>' . $menu . '<br /></p>' : '<br />';
    }

    echo $commentbar;
    echo site_footer();

break;

//-- Add A New Request --//
case 'add_new_request':

    $request_name = strip_tags(isset($_POST['request_name']) ? trim($_POST['request_name']) : '');
    $image        = strip_tags(isset($_POST['image']) ? trim($_POST['image']) : '');
    $body         = (isset($_POST['description']) ? trim($_POST['description']) : '');
    $link         = strip_tags(isset($_POST['link']) ? trim($_POST['link']) : '');

    //-- Do The Cat List --//
    $category_drop_down = '<select name="category" class="required"><option class="body" value="">' . $lang['form_select_cat'] . '</option>';
    $cats               = cached::genrelist();

    foreach ($cats
             AS
             $row)

    {
        $category_drop_down .= '<option class="body" value="' . $row['id'] . '" ' . ($category == $row['id'] ? ' selected="selected"' : '') . ' >' . security::html_safe($row['name']) . '</option>';
    }

    $category_drop_down .= '</select>';

    if (isset($_POST['category']))
    {
        $cat_res = $db->query("SELECT id AS cat_id, name AS cat_name, image AS cat_image
                              FROM categories
                              WHERE id = $category");

        $cat_arr   = $cat_res->fetch_assoc();
        $cat_image = security::html_safe($cat_arr['cat_image']);
        $cat_name  = security::html_safe($cat_arr['cat_name']);
    }

    //-- If Posted And Not Preview, Process It :D --//
    if (isset($_POST['button']) && $_POST['button'] == 'Submit')
    {
        $db->query('INSERT INTO requests (request_name, image, description, category, added, requested_by_user_id, link)
                    VALUES (' . sqlesc($request_name) . ', ' . sqlesc($image) . ', ' . sqlesc($body) . ', ' . $category . ', ' . vars::$timestamp . ', ' . user::$current['id'] . ',  ' . sqlesc($link) . ');');

        $new_request_id = $db->insert_id;

        header('Location: requests.php?action=request_details&new=1&id=' . $new_request_id);

        die();
    }

    //-- Start Page --//
    echo site_header('' . $lang['title_add_req'] . '', false);

    echo'<table class="main" border="0" align="center" width="80%" cellspacing="0" cellpadding="0">
            <tr>
                <td class="embedded" align="center">
                    <h1 style="text-align: center;">' . $lang['table_new_req'] . '</h1>' . $top_menu . '
                    <form method="post" action="requests.php?action=add_new_request" name="request_form" id="request_form">
                    ' . (isset($_POST['button']) && $_POST['button'] == 'Preview' ? '<br />

                        <table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">
                            <tr>
                                <td class="colhead" align="center" colspan="2"><h1>' . $lang['table_preview'] . '</h1></td>
                            </tr>

                            <tr>
                                <td class="rowhead" align="right">' . $lang['table_image'] . ':</td>
                                <td class="rowhead" align="left"><img src="' . security::html_safe($image) . '" width="" height="" border="0" alt="' . $lang['img_alt_posted_image'] . '" title="' . $lang['img_alt_posted_image'] . '" style="max-width:600px;" /></td>
                            </tr>

                            <tr>
                                <td class="rowhead" align="right">' . $lang['table_desc'] . ':</td>
                                <td class="rowhead" align="left">' . format_comment($body) . '</td>
                            </tr>

                            <tr>
                                <td class="rowhead" align="right">' . $lang['table_cat'] . ':</td>
                                <td class="rowhead" align="left"><img src="' . $image_dir . 'caticons/' . security::html_safe($cat_image) . '" width="60" height="54" border="0" alt="' . security::html_safe($cat_name) . '" title="' . security::html_safe($cat_name) . '" /></td>
                            </tr>

                            <tr>
                                <td class="rowhead" align="right">' . $lang['table_link'] . ':</td>
                                <td class="rowhead" align="left"><a class="altlink" href="' . security::html_safe($link) . '" target="_blank">' . security::html_safe($link) . '</a></td>
                            </tr>

                            <tr>
                                <td class="rowhead" align="right">' . $lang['table_req_by'] . ':</td>
                                <td class="rowhead" align="left">' . security::html_safe(user::$current['username']) . '</td>
                            </tr>

                        </table><br />' : '') . ' ';

    echo'<table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">
            <tr>
                <td class="colhead" align="center" colspan="2"><h1>' . $lang['table_make_req'] . '</h1></td>
            </tr>

            <tr>
                <td class="rowhead" align="center" colspan="2">' . $lang['table_before_make'] . '<a class="altlink" href="search.php">' . $lang['table_before_make1'] . '</a>' . $lang['table_before_make2'] . '<br /><br />' . $lang['table_before_make3'] . '</td>
            </tr>

            <tr>
                <td class="rowhead" align="right">' . $lang['table_name'] . ':</td>
                <td class="rowhead" align="left">
                    <input type="text" class="required" name="request_name" size="80" value="' . security::html_safe($request_name) . '" />
                </td>
            </tr>

            <tr>
                <td class="rowhead" align="right">' . $lang['table_image'] . ':</td>
                <td class="rowhead" align="left">
                    <input type="text" class="required" name="image" size="80" value="' . security::html_safe($image) . '" />
                </td>
            </tr>

            <tr>
                <td class="rowhead" align="right">' . $lang['table_link'] . ':</td>
                <td class="rowhead" align="left">
                    <input type="text" class="required" name="link" size="80" value="' . security::html_safe($link) . '" />
                </td>
            </tr>

            <tr>
                <td class="rowhead" align="right">' . $lang['table_cat'] . ':</td>
                <td class="rowhead" align="left">' . $category_drop_down . '</td>
            </tr>

            <tr>
                <td class="rowhead" align="right">' . $lang['table_desc'] . ':</td>
                <td class="rowhead" align="left">' . textbbcode("compose", "description",$body) . '</td>
            </tr>

            <tr>
                <td class="rowhead" align="center" colspan="2">
                    <input type="submit" class="btn" name="button" value="' . $lang['btn_preview'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                    <input type="submit" class="btn" name="button" value="' . $lang['gbl_btn_submit'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                </td>
            </tr>
        </table></form>
    </td></tr></table><br />';

    echo'<script type="text/javascript" src="scripts/jquery.validate.min.js"></script>
         <script type="text/javascript">
         <!--

         $(document).ready(function()
         {
             //=== form validation
             $("#request_form").validate();
         }
         );

         -->
         </script>';

    echo site_footer();

break;

//-- Edit A Request --//
case 'edit_request':

    if (user::$current['class'] < UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_denied']}");
    }

    if (!isset($id) || !is_valid_id($id))
    {
        error_message_center("error",
                             "{$lang['gbl_sorry']}",
                             "{$lang['err_bad_id']}");
    }

    $edit_res = $db->query("SELECT request_name, image, description, category, requested_by_user_id, filled_by_user_id, filled_torrent_id, link
                           FROM requests
                           WHERE id = $id") or sqlerr(__FILE__,__LINE__);

    $edit_arr = $edit_res->fetch_assoc();

    if (user::$current['class'] < UC_MODERATOR && user::$current['id'] !== $edit_arr['requested_by_user_id'])
    {
        error_message_center("error",
                             "{$lang['gbl_sorry']}",
                             "{$lang['err_not_yours']}");
    }

/*
    $filled_by = '';

    if ($edit_arr['filled_by_user_id'] > 0)
    {
        $filled_by_res = $db->query('SELECT id, username, warned, enabled, donor, class
                                    FROM users
                                    WHERE id = ' . sqlesc($edit_arr['filled_by_user_id'])) or sqlerr(__FILE__,__LINE__);

        $filled_by_arr = $filled_by_res->fetch_assoc();
        $filled_by     = '' . $lang['text_filled_by'] . '' . format_user($filled_by_arr);
    }
*/

    $request_name = strip_tags(isset($_POST['request_name']) ? trim($_POST['request_name']) : $edit_arr['request_name']);
    $image        = strip_tags(isset($_POST['image']) ? trim($_POST['image']) : $edit_arr['image']);
    $body         = (isset($_POST['description']) ? trim($_POST['description']) : $edit_arr['description']);
    $link         = strip_tags(isset($_POST['link']) ? trim($_POST['link']) : $edit_arr['link']);
    $category     = (isset($_POST['category']) ? intval($_POST['category']) : $edit_arr['category']);

    //-- Do The Cat List :D --//
    $category_drop_down = '<select name="category" class="required"><option class="body" value="">' . $lang['form_select_cat'] . '</option>';
    $cats               = cached::genrelist();

    foreach ($cats
             AS
             $row)
    {
        $category_drop_down .= '<option class="body" value="' . $row['id'] . '" ' . ($category == $row['id'] ? ' selected="selected"' : '') . ' >' . security::html_safe($row['name']) . '</option>';
    }

    $category_drop_down .= '</select>';

    $cat_res = $db->query("SELECT id AS cat_id, name AS cat_name, image AS cat_image
                          FROM categories
                          WHERE id = $category");

    $cat_arr = $cat_res->fetch_assoc();

    $cat_image = security::html_safe($cat_arr['cat_image']);
    $cat_name  = security::html_safe($cat_arr['cat_name']);

    //-- If Posted And Not Preview, Process It :D --//
    if (isset($_POST['button']) && $_POST['button'] == 'Edit')
    {
        if (isset($_POST['filled_by']) && $_POST['filled_by'] == '1')
        {
            $filled_by_user_id = ("0");
            $filled_torrent_id = ("0");

            $db->query('UPDATE requests
                        SET request_name = ' . sqlesc($request_name) . ', image = ' . sqlesc($image) . ', description = ' . sqlesc($body) . ', category = ' . sqlesc($category) . ', link = ' . sqlesc($link) . ', filled_by_user_id = ' . sqlesc($filled_by_user_id) . ', filled_torrent_id = ' . sqlesc($filled_torrent_id) . '
                        WHERE id = '.$id);
        }
        else
        {
            $db->query('UPDATE requests
                        SET request_name = ' . sqlesc($request_name) . ', image = ' . sqlesc($image) . ', description = ' . sqlesc($body) . ', category = ' . sqlesc($category) . ', link = ' . sqlesc($link) . '
                        WHERE id = ' . $id);
        }

        header('Location: requests.php?action=request_details&edited=1&id=' . $id);
        die();
    }

    //-- Start Page --//
    echo site_header('' . $lang['title_edit_req'] . '', false);

    echo'<table border="0" align="center" width="80%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="embedded" align="center">
                <h1 style="text-align: center;">' . $lang['table_edit_req'] . '</h1>' . $top_menu . '
                <form method="post" action="requests.php?action=edit_request" name="request_form" id="request_form">
                    <input type="hidden" name="id" value="' . $id . '" />' . (isset($_POST['button']) && $_POST['button'] == 'Preview' ? '<br />' : '') . ' ';

    echo'<table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">
        <tr>
            <td class="colhead" align="center" colspan="2"><h1>' . security::html_safe($request_name) . '</h1></td>
        </tr>

        <tr>
            <td class="rowhead" align="right">' . $lang['table_image'] . ':</td>
            <td class="rowhead" align="left"><a href="' . $image . '" rel="lightbox"><img src="' . security::html_safe($image) . '" width="" height="" alt="' . $lang['img_alt_posted_image'] . '" title="' . $lang['img_alt_posted_image'] . '" style="max-width:600px;" /></a></td>
        </tr>

        <tr>
            <td class="rowhead" align="right">' . $lang['table_desc'] . ':</td>
            <td class="rowhead" align="left">' . format_comment($body) . '</td>
        </tr>

        <tr>
            <td class="rowhead" align="right">' . $lang['table_cat'] . ':</td>
            <td class="rowhead" align="left"><img src="' . $image_dir . 'caticons/'.security::html_safe($cat_image).'" width="60" height="54" border="0" alt="' . security::html_safe($cat_name) . '" title="' . security::html_safe($cat_name) . '" /></td>
        </tr>

        <tr>
            <td class="rowhead" align="right">' . $lang['table_link'] . ':</td>
            <td class="rowhead" align="left"><a class="altlink" href="' . security::html_safe($link) . '" target="_blank">' . security::html_safe($link) . '</a></td>
        </tr>
        </table><br />';

    echo'<table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">
        <tr>
            <td class="colhead" align="center" colspan="2"><h1>' . $lang['table_edit_req'] . '</h1></td>
        </tr>

        <tr>
            <td align="center" colspan="2" class="rowhead">' . $lang['table_be_sure'] . '</td>
        </tr>

        <tr>
            <td class="rowhead" align="right">' . $lang['table_name'] . ':</td>
            <td class="rowhead" align="left">
                <input type="text" class="required" name="request_name" size="80" value="' . security::html_safe($request_name) . '" />
            </td>
        </tr>

        <tr>
            <td class="rowhead" align="right">' . $lang['table_image'] . ':</td>
            <td class="rowhead" align="left">
                <input type="text" class="required" name="image" size="80" value="' . security::html_safe($image) . '" /></td>
        </tr>

        <tr>
            <td class="rowhead" align="right">' . $lang['table_link'] . ':</td>
            <td class="rowhead" align="left">
                <input type="text" class="required" name="link" size="80" value="' . security::html_safe($link) . '" /></td>
        </tr>

        <tr>
            <td class="rowhead" align="right">' . $lang['table_cat'] . ':</td>
            <td class="rowhead" align="left">' . $category_drop_down . '</td>
        </tr>

        <tr>
            <td class="rowhead" align="right">' . $lang['table_desc'] . ':</td>
            <td class="rowhead" align="left">' . textbbcode("compose", "description",  $body) . '</td>
        </tr>';

    if ($edit_arr['filled_torrent_id'] > 0)
    {
        echo'<tr>
                <td class="rowhead" align="right">' . $lang['table_reset_req'] . ':</td>
                <td class="rowhead" align="left">
                    <input type="radio" name="filled_by" value="0" ' . ($filled_by ? ' checked="checked"' : '') . ' />No
                    <input type="radio" name="filled_by" value="1" ' . (!$filled_by ? ' checked="checked"' : '') . ' />Yes
                </td>
            </tr>';
    }

    echo'<tr>
            <td class="rowhead" align="center" colspan="2">
                <input type="submit" class="btn" name="button" value="' . $lang['btn_preview'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
                <input type="submit" class="btn" name="button" value="' . $lang['btn_edit'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
            </td>
        </tr>
        </table></form>
        </td></tr></table><br />

        <script type="text/javascript" src="scripts/jquery.validate.min.js"></script>
        <script type="text/javascript">
        <!--

        $(document).ready(function()
        {
            //=== form validation
            $("#request_form").validate();
        });

        -->
        </script>';

    echo site_footer();

break;

//-- Delete A Request --//
case 'delete_request':

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

        $res = $db->query('SELECT request_name, requested_by_user_id
                          FROM requests
                          WHERE id = ' . $id) or sqlerr(__FILE__,__LINE__);

        $arr = $res->fetch_assoc();

    if (!$arr)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    if ($arr['requested_by_user_id'] !== user::$current['id'] && user::$current['class'] < UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_denied']}");
    }

    if (!isset($_GET['do_it']))
    {
        error_message_center("warn",
                             "{$lang['gbl_sanity']}",
                             "{$lang['text_del_sure']}<b> : - " . security::html_safe($arr['request_name']) . "</b>.<br /><br />
                             &nbsp; <a class='btn' href='requests.php?action=delete_request&amp;id=" . $id . "&amp;do_it=666' >{$lang['text_click_confirm']}</a>");
    }
    else
    {
        $db->query('DELETE
                   FROM requests
                   WHERE id = ' . $id);

        $db->query('DELETE
                   FROM request_votes
                   WHERE request_id = ' . $id);

        $db->query('DELETE
                   FROM comments_request
                   WHERE request = ' . $id);

        header('Location: /requests.php?request_deleted=1');
        die();
    }

    echo site_footer();

break;

//-- Add A Comment --//
case 'add_comment':

    if (user::$current['requestcompos'] == 'no')
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_comm_disabled']}");
    }

    global $image_dir;

    if (!isset($id) || !is_valid_id($id))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_try_again']}");
    }

        $res = $db->query('SELECT request_name
                          FROM requests
                          WHERE id = ' . $id) or sqlerr(__FILE__,__LINE__);

        $arr = $res->fetch_array(MYSQLI_BOTH);

    if (!$arr)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_no_req_id']}");
    }

    if(isset($_POST['button']) && $_POST['button'] == 'Save')
    {
        $text = (isset($_POST['text']) ? trim($_POST['text']) : '');

        if (!$text)
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_comm_empty']}");
        }

        $db->query("INSERT INTO comments_request (user, request, added, text, ori_text)
                   VALUES (" . user::$current['id'] . ", $id, '" . get_date_time() . "', " . sqlesc($text) . ", " . sqlesc($text) . ")");

        $newid = $db->insert_id;

        $db->query("UPDATE requests
                   SET comments = comments + 1
                   WHERE id = $id");

        header('Location: /requests.php?action=request_details&id=' . $id . '&viewcomm=' . $newid . '#comm' . $newid);
        die();
    }

    $text = security::html_safe((isset($_POST['text']) ? $_POST['text'] : ''));

    $res = $db->query("SELECT avatar
                      FROM users
                      WHERE id = " . user::$current['id']) or sqlerr(__FILE__,__LINE__);

    $row = $res->fetch_array(MYSQLI_BOTH);

    $avatar = ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? security::html_safe($row['avatar']) : "");

    if (!$avatar)
    {
        $avatar = "{$image_dir}default_avatar.gif";
    }

    echo site_header('' . $lang['title_add_com'] . '', false);

    echo $top_menu.'<form method="post" action="requests.php?action=add_comment">
                    <input type="hidden" name="id" value="' . $id . '"/>
                    ' . (isset($_POST['button']) && $_POST['button'] == 'Preview' ? '

    <table border="0" align="center" width="80%" cellspacing="5" cellpadding="5">

    <tr>
        <td class="colhead" colspan="2"><h1>' . $lang['table_preview'] . '</h1></td>
    </tr>

    <tr>
        <td align="center" width="125"><img src="' . $avatar . '" width="125" height="125" border="0" alt="" title="" /></td>
        <td class="rowhead" align="left" valign="top">' . format_comment($text) . '</td>
    </tr>

    </table><br />' : '') . '

    <table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">

    <tr>
        <td class="colhead" align="center" colspan="2">
        <h1>' . $lang['table_add_comm'] . '"' . security::html_safe($arr['request_name']) . '"</h1>
        </td>
    </tr>

    <tr>
        <td class="rowhead" align="right" valign="top"><b>' . $lang['table_comment'] . ':</b></td>
        <td class="rowhead">' . textbbcode("compose", "text", $text) . '</td>
    </tr>

    <tr>
        <td class="rowhead" align="center" colspan="2">
            <input type="submit" class="btn" name="button" value="' . $lang['btn_preview'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
            <input type="submit" class="btn" name="button" value="' . $lang['btn_save'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
        </td>
    </tr>

    </table></form>';

//-- View Existing Comments --//
    $res = $db->query('SELECT r.request, r.id, r.text, r.added, r.editedby, r.editedat, u.id, u.username, u.warned, u.enabled, u.donor, u.class, u.avatar, u.title
                      FROM comments_request AS r
                      LEFT JOIN users AS u ON r.user = u.id
                      WHERE request = ' . $id . '
                      ORDER BY r.id DESC LIMIT 5');

    $allrows    = array();
    while ($row = $res->fetch_assoc())
    $allrows[]  = $row;

    if (count($allrows))
    {
        echo '<h2>' . $lang['text_recent_comm'] . '</h2>';
        echo comment_table($allrows);
    }

    echo site_footer();

break;

//-- Edit A Comment --//
case 'edit_comment':

    if (user::$current['requestcompos'] == 'no')
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_comm_disabled']}");
    }

    if (!isset($comment_id) || !is_valid_id($comment_id))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_try_again']}");
    }

    $res = $db->query('SELECT c.*, r.request_name
                      FROM comments_request AS c
                      LEFT JOIN requests AS r ON c.request = r.id
                      WHERE c.id = ' . $comment_id) or sqlerr(__FILE__,__LINE__);

    $arr = $res->fetch_assoc();

    if (!$arr)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id_again']}");
    }

    if ($arr['user'] != user::$current['id'] && user::$current['class'] < UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_denied']}");
    }

    $text = security::html_safe((isset($_POST['edit']) ? $_POST['edit'] : $arr['text']));

    $body = security::html_safe((isset($_POST['edit']) ? $_POST['edit'] : $arr['text']));

    if(isset($_POST['button']) && $_POST['button'] == 'Edit')
    {
        if ($body == '')
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_body_empty']}");
        }

        $text     = sqlesc($body);
        $editedat = sqlesc(get_date_time());

        $db->query("UPDATE comments_request
                   SET text = $text, editedat = $editedat, editedby = " . user::$current['id'] . "
                   WHERE id = $comment_id") or sqlerr(__FILE__, __LINE__);

        header('Location: /requests.php?action=request_details&id=' . $id . '&viewcomm=' . $comment_id . '#comm' . $comment_id);
        die();
    }

    $res = $db->query("SELECT avatar
                      FROM users
                      WHERE id = " . user::$current['id']) or sqlerr(__FILE__,__LINE__);

    $row = $res->fetch_array(MYSQLI_BOTH);

    $avatar = ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? security::html_safe($row['avatar']) : "");

    if (!$avatar)
    {
        $avatar = "{$image_dir}default_avatar.gif";
    }

    echo site_header('' . $lang['title_edit_comm'] . '"' . htmlentities($arr['request_name'], ENT_QUOTES ) . '"', false);

    echo $top_menu.'<form method="post" action="requests.php?action=edit_comment">
                        <input type="hidden" name="id" value="' . $arr['request'] . '"/>
                        <input type="hidden" name="comment_id" value="' . $comment_id . '"/>' .
                        (isset($_POST['button']) && $_POST['button'] == 'Preview' ? '

    <table border="0" align="center" width="80%" cellspacing="5" cellpadding="5">

    <tr>
        <td class="colhead" colspan="2"><h1>' . $lang['table_preview' ] . '</h1></td>
    </tr>

    <tr>
        <td align="center" width="125"><img src="' . $avatar . '" width="125" height="125" border="0" alt="" title="" /></td>
        <td class="rowhead" align="left" valign="top">' . format_comment($text) . '</td>
    </tr>

    </table><br />' : '') . '

    <table border="0" align="center" width="80%" cellspacing="0" cellpadding="5">

    <tr>
        <td class="colhead" align="center" colspan="2"><h1>' . $lang['table_edit_comm'] . '"' . security::html_safe($arr['request_name']) . '"</h1></td>
    </tr>

    <tr>
        <td class="rowhead" align="right" valign="top"><b>' . $lang['table_comment'] . ':</b></td>
        <td class="rowhead">' . textbbcode("compose", "edit", $body) . '</td>
    </tr>

    <tr>
        <td class="rowhead" align="center" colspan="2">
            <input type="submit" class="btn" name="button" value="' . $lang['btn_preview'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" />
            <input type="submit" class="btn" name="button" value="' . $lang['btn_edit'] . '" onmouseover="this.className=\'btn\'" onmouseout="this.className=\'btn\'" /></td>
    </tr>

    </table></form>';

    echo site_footer();

break;

//-- View Original Comment --//
case 'view_orig_comment':

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
                             "{$lang['err_try_again']}");
    }

    $res = $db->query('SELECT c.*, r.request_name
                      FROM comments_request AS c
                      LEFT JOIN requests AS r ON c.request = r.id
                      WHERE c.id = ' . $comment_id) or sqlerr(__FILE__,__LINE__);

    $arr = $res->fetch_assoc();

    if (!$arr)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id_again']}");
    }

    if ($arr['user'] != user::$current['id'] && user::$current['class'] < UC_MODERATOR)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_denied']}");
    }

    site_header("{$lang['title_orig_comm']}", false);

    print("<h1>{$lang['text_orig_comm']}#$comment_id</h1>\n");
    print("<table border='1' width='100%' cellspacing='0' cellpadding='5'>");
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
                             "{$lang['err_return_to']}<br /><br />
                              <a class='btn' href='$returnto'>{$lang['text_click_here']}</a>");
    }

    site_footer();

break;

//-- Delete A Comment --//
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
                             "{$lang['err_try_again']}");
    }

    $res = $db->query('SELECT user, request
                      FROM comments_request
                      WHERE id = ' . $comment_id) or sqlerr(__FILE__,__LINE__);

    $arr = $res->fetch_assoc();

    if (!$arr)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id_again']}");
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
                             <a class='btn' href='requests.php?action=delete_comment&amp;id={$arr['request']}&amp;comment_id=" . $comment_id . "&amp;do_it=666'>{$lang['text_click_confirm']}</a>");
    }
    else
    {
        $db->query("DELETE
                   FROM comments_request
                   WHERE id = $comment_id");

        $db->query("UPDATE requests
                   SET comments = comments - 1
                   WHERE id = " . (int)$arr['request']);

        header('Location: /requests.php?action=request_details&id=' . $id . '&comment_deleted=1');
        die();
    }

break;

}
//-- End All Actions / Switch --//

function comment_table($rows)
{
    global $image_dir, $lang, $db;

    begin_frame();

    //$count = 0;

    foreach ($rows
             AS
             $row)
    {
        print("<p class='sub'>#{$row['id']} by ");

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
            print("<a name='comm{$row['id']}'><span style='font-style: italic;'>({$lang['text_orphan']})</span></a>\n");
        }

        if (user::$current['requestcompos'] == 'no')
        {
            if ($row['user'] == user::$current['id'])
            {
            print("{$lang['text_at']}" . $row['added'] . "{$lang['text_gmt']}&nbsp;&nbsp;<span class='requests_comment_disabled'>{$lang['text_edit_disabled']}</span></p> ");
            }
        }
        else
        {
            print("{$lang['text_at']}{$row['added']}{$lang['text_gmt']}&nbsp;&nbsp;
            " . ($row['user'] != user::$current['id'] ? "<a class='btn' href='report.php?type=Request_Comment&amp;id={$row['id']}'>{$lang['btn_report_comm']}</a>" : "") .
            ($row['user'] == user::$current['id'] || get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='requests.php?action=edit_comment&amp;comment_id={$row['id']}'>{$lang['btn_edit']}</a>" : "") .
            (get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='/requests.php?action=delete_comment&amp;comment_id={$row['id']}'>{$lang['btn_del']}</a>" : "") .
            ($row['editedby'] && get_user_class() >= UC_MODERATOR ? "&nbsp;&nbsp;<a class='btn' href='requests.php?action=view_orig_comment&amp;comment_id={$row['id']}'>{$lang['btn_view_orig']}</a>" : "") . "</p>\n");
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
        print("<td align='center' width='125'><img src='{$avatar}' width='125' height='125' border='0' alt='' title='' /></td>\n");
        print("<td class='text'>$text</td>\n");
        print("</tr>\n");

        end_table();
    }

    end_frame();

}

?>