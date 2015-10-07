<?php

/**
**************************
** FreeTSP Version: 1.0 **
**************************
** https://github.com/Krypto/FreeTSP
** http://www.freetsp.info
** Licence Info: GPL
** Copyright (C) 2010 FreeTSP v1.0
** A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
** Project Leaders: Krypto, Fireknight.
**/

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_bbcode.php');

db_connect(true);
logged_in();

$lang = array_merge(load_language('helpdesk'),
                    load_language('func_bbcode'),
                    load_language('global'));

site_header("{$lang['title_help_desk']}");

if (get_user_class() >= UC_MODERATOR)
{
    display_message_center("info",
                           "{$lang['gbl_info']}",
                           "{$lang['text_staff']}<br />{$lang['text_help_staff']}");
    site_footer();
    die();
}

if (($msg_problem != "") && ($title != ""))
{
    $dt = sqlesc(get_date_time());

    $db->query("INSERT INTO helpdesk (title, msg_problem, added, added_by)
                VALUES (" . sqlesc($title) . ", " . sqlesc($msg_problem) . ", $dt, " . user::$current['id'] . ")") or sqlerr();
	
	$Memcache->delete_value('helpdesk::problems::count');

    display_message_center("info",
                           "{$lang['gbl_info']}",
                           "{$lang['text_msg_sent']}<br />{$lang['text_wait_reply']}");
    site_footer();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $msg_problem = trim((isset($_POST['msg_problem']) ? $_POST['msg_problem'] : ''));
    $title       = trim((isset($_POST['title']) ? $_POST['title'] : ''));

    // Check Values Before Inserting Into Row --//
    if (empty($msg_problem))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_no_question']}");
    }

    if (!$title)
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_no_title']}");
    }

    if (($msg_problem != "") && ($title != ""))
    {
        $dt = sqlesc(get_date_time());

        $db->query("INSERT INTO helpdesk (title, msg_problem, added, added_by)
                    VALUES (" . sqlesc($title) . ", " . sqlesc($msg_problem) . ", $dt, " . user::$current['id'] . ")") or sqlerr();
		
		$Memcache->delete_value('helpdesk::problems::count');

        display_message_center("info",
                               "{$lang['gbl_info']}",
                               "{$lang['text_msg_sent']}<br />{$lang['text_wait_reply']}");
        site_footer();
        exit;
    }
}
//-- Main Help Desk --//

    print("<div align='center'>
               {$lang['text_helpdesk']}<br />
               {$lang['text_read']}
               <a href='faq.php'><strong>{$lang['text_faq']}</strong></a>{$lang['text_section']}<br />
               {$lang['text_search']}
               <a href='forums.php'><strong>{$lang['text_forums']}</strong></a>{$lang['text_first']}
           </div><br />");

    print("<div align='center'>
                {$lang['text_answered']}<strong>{$lang['text_ignore']}</strong>
           </div><br />");

    print("<form method='post' action='helpdesk.php'>");
    print("<table border='0' align='center' cellpadding='5' cellspacing='0'>");

    print("<tr>
            <td align='center' colspan='2'><strong>{$lang['table_title']}&nbsp;&nbsp;:-&nbsp;&nbsp;</strong>
                <input type='text' name='title' size='73' maxlength='60' />
            </td>
           </tr>");

    print("<tr>
            <td colspan='2'>
                " . textbbcode("compose", "msg_problem", $body) . "
            </td>
           </tr>");

    print("<tr>
            <td align='center' colspan='2'>
                <input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' />
            </td>
        </tr>");

    print("</table>");
    print("</form>");

site_footer();

?>