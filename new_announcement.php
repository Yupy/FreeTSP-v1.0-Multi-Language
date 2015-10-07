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

db_connect();
logged_in();

$lang = array_merge(load_language('new_announcement'),
                    load_language('func_bbcode'),
                    load_language('global'));

site_header("{$lang['title_create_announce']}", false);

if (get_user_class() < UC_ADMINISTRATOR)
{
    error_message_center("error",
                         "{$lang['err_denied']}",
                         "{$lang['err_restricted']}");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    //-- The Expiry Days. --//
    $days = array(
                  array(7, '7' . $lang['form_days'] . ''),
                  array(14, '14' . $lang['form_days'] . ''),
                  array(21, '21' . $lang['form_days'] . ''),
                  array(28, '28' . $lang['form_days'] . ''),
                  array(56, '2' . $lang['form_months'] . '')
                 );

    //-- Usersearch POST Data... --//
    $n_pms     = (isset($_POST['n_pms']) ? (int) $_POST['n_pms'] : 0);
    $ann_query = (isset($_POST['ann_query']) ? rawurldecode(trim($_POST['ann_query'])) : '');
    $ann_hash  = (isset($_POST['ann_hash']) ? trim($_POST['ann_hash']) : '');

    if (hashit($ann_query, $n_pms) != $ann_hash) //-- Validate POST... --//
        error_message_center("info",
                             "{$lang['gbl_info']}",
                             "{$lang['err_use_pm']}");

    if (!preg_match('/\\ASELECT.+?FROM.+?WHERE.+?\\z/', $ann_query))
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_query']}");

    if (!$n_pms)
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_no_recip']}");

    //-- Preview POST Data ... --//
    $body    = trim((isset($_POST['msg']) ? $_POST['msg'] : ''));
    $subject = trim((isset($_POST['subject']) ? $_POST['subject'] : ''));
    $expiry  = 0 + (isset($_POST['expiry']) ? $_POST['expiry'] : 0);

    if ((isset($_POST['buttonval']) AND $_POST['buttonval'] == 'Submit'))
    {
        //-- Check Values Before Inserting Into Row... --//
        if (empty($body))
             error_message_center("error",
                                  "{$lang['gbl_error']}",
                                  "{$lang['err_no_announce']}");

        if (!$subject)
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_no_subject']}");

        unset ($flag);
        reset ($days);

        foreach ($days
                    AS
                    $x)

        if ($expiry == $x[0]) $flag = 1;

        if (!isset($flag))
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_inv_choice']}");

        $expires = get_date_time((strtotime(get_date_time()) + (86400 * $expiry))); //-- 86400 seconds in one day. --//
        $created = get_date_time();

        $query = sprintf('INSERT INTO announcement_main ' . '(owner_id, created, expires, sql_query, subject, body) ' .
                         'VALUES (%s, %s, %s, %s, %s, %s)', sqlesc(user::$current['id']), sqlesc($created), sqlesc($expires), sqlesc($ann_query), sqlesc($subject), sqlesc($body));

        $db->query($query);

        if ($db->affected_rows == 1)
            error_message_center("success",
                                 "{$lang['gbl_success']}",
                                 "{$lang['text_announce_success']}");

        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_contact_staff']}");
    }

    echo("<div align='center'><h1>{$lang['text_create']}(" . $n_pms . "){$lang['text_members']}</h1></div>");
    echo("<form name='compose' method='post' action='new_announcement.php'>");
    echo("<table border='1' cellspacing='0' cellpadding='5'>");

    echo("<tr>
            <td align='center' colspan='2'>
            <strong>{$lang['form_subject']}</strong>
            <input type='text' name='subject' size='76' value='" . htmlspecialchars_decode($subject) . "' />
            </td>
        </tr>");

    echo("<tr>
            <td class='std' style='padding : 10px'>
                " . textbbcode("compose", "msg", $body) . "
            </td>
        </tr>");

    echo("<tr>
            <td align='center' colspan='2'>");

    echo("<select name='expiry'>");

    reset ($days);

    foreach ($days
             AS
             $x)

    echo('<option value="' . $x[0] . '" ' . (($expiry == $x[0] ? '' : '')) . '>' . $x[1] . '</option>');

    echo("</select>&nbsp;&nbsp;");

    echo("&nbsp;&nbsp;<input type='submit' class='btn' name='buttonval' value='{$lang['btn_preview']}' />
         &nbsp;&nbsp;<input type='submit' class='btn' name='buttonval' value='{$lang['gbl_btn_submit']}' />
         <input type='hidden' name='n_pms' value='" . $n_pms . "' />
         <input type='hidden' name='ann_query' value='" . rawurlencode($ann_query) . "' />
         <input type='hidden' name='ann_hash' value='" . $ann_hash . "' />
         </td>
    </tr>");

    echo("</table></form><br /><br />");

    if ($body)
    {
        $newtime = (strtotime(get_date_time()) + (86400 * $expiry));

        echo("<table class='main' border='0' width='700' cellspacing='1' cellpadding='1'>");
        echo("<tr><td align='center' class='announcement'><h3>{$lang['table_preview']} :-&nbsp;&nbsp;");
        echo("{$lang['table_title']}$subject");
        echo("</h3></td></tr>");
        echo("<tr><td class='text'>");
        echo(format_comment($body) . '<br /><hr />' . $lang['table_expires'] . get_date_time($newtime));
        echo("</td></tr></table>");
    }
}

site_footer();

?>