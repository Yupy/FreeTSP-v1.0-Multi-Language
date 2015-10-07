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

if (!defined("IN_FTSP_ADMIN"))
{
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

            <title><?php if (isset($_GET['error']))
            {
                echo security::html_safe($_GET['error']);
            }
            ?> Error</title>

            <link rel='stylesheet' type='text/css' href='/errors/error-style.css' />
        </head>
        <body>
            <div id='container'>
                <div align='center' style='padding-top:15px'>
                    <img src='/errors/error-images/alert.png' width='89' height='94' alt='404 Page Not Found' title='404 Page Not Found' />
                </div>
                <h1 class='title'>Error 404 - Page Not Found</h1>
                <p class='sub-title' align='center'>The page that you are looking for does not appear to exist on this site.</p>
                <p>If you typed the address of the page into the address bar of your browser, please check that you typed it in correctly.</p>
                <p>If you arrived at this page after you used an old Boomark or Favorite, the page in question has probably been moved. Try locating the page via the navigation menu and then updating your bookmark.</p>
            </div>
        </body>
    </html>

<?php

exit();

}

$lang = array_merge(load_language('adm_reports'),
                    load_language('func_vfunctions'),
                    load_language('adm_global'));

//-- Cute Solved In Thing Taken From Helpdesk Mod --//
function round_time($ts)
{
    global $lang;

    $mins  = floor($ts / 60);
    $hours = floor($mins / 60);
    $mins  -= $hours * 60;
    $days  = floor($hours / 24);
    $hours -= $days * 24;
    $weeks = floor($days / 7);
    $days  -= $weeks * 7;
    $t     = "";

    if ($weeks > 0)
    {
        return "$weeks{$lang['text_week']}" . ($weeks > 1 ? "{$lang['text_post_s']}" : '');
    }

    if ($days > 0)
    {
        return "$days{$lang['text_day']}" . ($days > 1 ? "{$lang['text_post_s']}" : '');
    }

    if ($hours > 0)
    {
        return "$hours{$lang['text_hour']}" . ($hours > 1 ? "{$lang['text_post_s']}" : '');
    }

    if ($mins > 0)
    {
        return "$mins{$lang['text_min']}" . ($mins > 1 ? "{$lang['text_post_s']}" : '');
    }
    return "< 1{$lang['text_min']}";
}

//-- All Reports Just Use A Single Var $id And A Type --//
if ($_GET['id'])
{
    $id = ($_GET['id'] ? (int)$_GET['id'] : (int)$_POST['id']);

    if (!is_valid_id($id))
    {
        error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_bad_id']}");
    }
}

if ($_GET['type'])
{
    $type = ($_GET['type'] ? $_GET['type'] : $_POST['type']);

    $typesallowed = array("User",
                          "Comment",
                          "Request_Comment",
                          "Offer_Comment",
                          "Request",
                          "Offer",
                          "Torrent",
                          "Hit_And_Run",
                          "Post");

    if (!in_array($type, $typesallowed))
    {
        error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_bad_report']}");
    }
}

//-- Deal With This Report --//
if ((isset($_GET['deal_with_report'])) || (isset($_POST['deal_with_report'])))
{
    if (!is_valid_id($_POST['id']))
    {
        error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_whoops']}");
    }

    $how_delt_with  = "how_delt_with = " . sqlesc($_POST['how_delt_with']);
    $when_delt_with = "when_delt_with = " . sqlesc(get_date_time());

    $db->query("UPDATE reports
                SET delt_with = 1, $how_delt_with, $when_delt_with , who_delt_with_it = {$CURUSER['id']}
                WHERE delt_with != 1
                AND id = {$_POST['id']}") or sqlerr(__FILE__, __LINE__);
	
	$Memcache->delete_value('reports::count');
}

//-- Main Reports Page --//
site_header("{$lang['title_reports']}", false);

    echo("<table width='95%'>
            <tr>
                <td class='colhead'>
                    <h1>{$lang['title_reports']}</h1></td>
            </tr>
            <tr>
                <td class='rowhead' align='center'>");

//-- Delete The Report --//
if ((isset($_GET['delete'])) && (get_user_class() >= UC_SYSOP))
{
    $res = $db->query("DELETE
                       FROM reports
                       WHERE id = $id") or sqlerr(__FILE__, __LINE__);
	
	$Memcache->delete_value('reports::count');

    echo("<h1>{$lang['text_deleted']}</h1>\n");
}

//-- Get The Count Make The Page --//
$res = $db->query("SELECT COUNT(id)
                   FROM reports") or sqlerr(__FILE__, __LINE__);

$row     = $res->fetch_array(MYSQLI_BOTH);
$count   = (int)$row[0];
$perpage = 25;

list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, security::esc_url($_SERVER['PHP_SELF']) . "?type=$type&" );

if ($count == '0')
{
    echo("<p align='center'><strong>{$lang['text_no_reports']}</strong></p></td></tr>");
}
else
{
    echo($pagertop);
    echo("<form method='post' action='controlpanel.php?fileaction=26&amp;deal_with_report=1'>
            <table width='95%'>
                <tr>
                    <td class='colhead' align='center'>{$lang['form_added']}</td>
                    <td class='colhead' align='center'>{$lang['form_report_by']}</td>
                    <td class='colhead' align='center'>{$lang['form_report_what']}</td>
                    <td class='colhead' align='center'>{$lang['form_type']}</td>
                    <td class='colhead' align='center'>{$lang['form_reason']}</td>
                    <td class='colhead' align='center'>{$lang['form_dealt_with']}</td>
                    <td class='colhead' align='center'>{$lang['form_deal with']}</td>
                        " . (get_user_class() >= UC_SYSOP ? "
                    <td class='colhead' align='center'>{$lang['form_delete']}</td>" : "") . "
                </tr>");

    /*if (get_user_class() >= UC_SYSOP)
    {
        echo("<td class='colhead' align='center'>{$lang['form_delete']}</td>");
    }

    echo("</tr>");*/

    //-- Get The Info For The Report --//
    $res_info = $db->query("SELECT reports.id, reports.reported_by, reports.reporting_what, reports.reporting_type, reports.reason, reports.who_delt_with_it, reports.delt_with, reports.added, reports.how_delt_with, reports.when_delt_with, reports.2nd_value, users.username
                            FROM reports INNER JOIN users ON reports.reported_by = users.id $where
                            ORDER BY id desc $limit");

    while ($arr_info = $res_info->fetch_assoc())
    {
        //-- Cute Solved In Thing Taken From Helpdesk Mod By Nuerher --//
        $added       = $arr_info['added'];
        $solved_date = $arr_info['when_delt_with'];

        if ($solved_date == '0000-00-00 00:00:00')
        {
            $solved_in    = " [N/A]";
            $solved_color = "rep_solved_na";
        }
        else
        {
            $solved_in_wtf = sql_timestamp_to_unix_timestamp($arr_info['when_delt_with']) - sql_timestamp_to_unix_timestamp($arr_info['added']);
            $solved_in     = "&nbsp;[" . round_time($solved_in_wtf) . "]";

            if ($solved_in_wtf > 4 * 3600)
            {
                $solved_color = "rep_solved_more4hours";
            }
            else if  ($solved_in_wtf > 2 * 3600)
            {
                $solved_color = "rep_solved_more2hours";
            }
            else if ($solved_in_wtf <= 3600)
            {
                    $solved_color = "rep_solved_less1hour";
            }
        }

        //-- Has It Been Delt With Yet? --//
        if ($arr_info['delt_with'])
        {
            $res_who   = $db->query("SELECT username
                                     FROM users
                                     WHERE id = " . (int)$arr_info['who_delt_with_it']);

            $arr_who   = $res_who->fetch_assoc();

            $dealtwith = "<span class='dealtwith_yes_reports'>{$lang['text_yes']}</span> - {$lang['text_by']}<a class='altlink' href='userdetails.php?id={$arr_info['who_delt_with_it']}'><strong>" . security::html_safe($arr_who['username']) . "</strong></a><br /> {$lang['text_in']}<span class='$solved_color'>$solved_in</span>";

            $checkbox = "<input type='radio' name='id' value='" . (int)$arr_info['id'] . "' disabled='disabled' />";
        }
        else
        {
            $dealtwith = "<span class='dealtwith_no_reports'>{$lang['text_no']}</span>";
            $checkbox  = "<input type='radio' name='id' value='" . (int)$arr_info['id'] . "' />";
        }

        //-- Make A Link To The Reported Item --//
        if ($arr_info['reporting_type'] != "")
        {
            switch($arr_info['reporting_type'])
            {
                case "User":
                    $res_who2 = $db->query("SELECT username
                                            FROM users
                                            WHERE id = " . (int)$arr_info['reporting_what']);

                    $arr_who2      = $res_who2->fetch_assoc();
                    $link_to_thing = "<a class='altlink' href='userdetails.php?id={$arr_info['reporting_what']}'><strong>" . security::html_safe($arr_who2['username']) . "</strong></a>";
                break;

                case "Comment":
                    $res_who2 = $db->query("SELECT comments.user, users.username, torrents.id
                                            FROM comments, users, torrents
                                            WHERE comments.user = users.id
                                            AND comments.id = " . (int)$arr_info['reporting_what']);

                    $arr_who2      = $res_who2->fetch_assoc();
                    $link_to_thing = "<a class='altlink' href='details.php?id={$arr_who2['id']}&amp;viewcomm={$arr_info['reporting_what']}#comm{$arr_info['reporting_what']}'><strong>" . security::html_safe($arr_who2['username']) . "'{$lang['text_post_s']} - {$lang['text_comment']}</strong></a>";
                break;

                case "Offer":
                    $res_who2 = $db->query("SELECT offer_name
                                            FROM offers
                                            WHERE id = " . (int)$arr_info['reporting_what']);

                    $arr_who2      = $res_who2->fetch_assoc();
                    $link_to_thing = "<a class='altlink' href='offers.php?action=offer_details&amp;id={$arr_info['reporting_what']}'><strong>" . security::html_safe($arr_who2['offer_name']) . "</strong></a>";
                break;

                case "Offer_Comment":
                    $res_who2 = $db->query("SELECT comments_offer.user, users.username, comments_offer.offer
                                            FROM comments_offer, users, offers
                                            WHERE comments_offer.user = users.id
                                            AND comments_offer.id = " . (int)$arr_info['reporting_what']);

                    $arr_who2      = $res_who2->fetch_assoc();
                    $link_to_thing = "<a class='altlink' href='offers.php?action=offer_details&id={$arr_who2['offer']}&amp;viewcomm={$arr_info['reporting_what']}#comm{$arr_info['reporting_what']}'><strong>" . security::html_safe($arr_who2['username']) . "'{$lang['text_post_s']} {$lang['text_offer_com']}</strong></a>";
                break;

                case "Request":
                    $res_who2 = $db->query("SELECT request_name
                                            FROM requests
                                            WHERE id = " . (int)$arr_info['reporting_what']);

                    $arr_who2      = $res_who2->fetch_assoc();
                    $link_to_thing = "<a class='altlink' href='requests.php?action=request_details&amp;id={$arr_info['reporting_what']}'><strong>" . security::html_safe($arr_who2['request_name']) . "</strong></a>";
                break;

                case "Request_Comment":
                    $res_who2 = $db->query("SELECT comments_request.user, users.username, comments_request.request
                                            FROM comments_request, users, requests
                                            WHERE comments_request.user = users.id
                                           AND comments_request.id = " . (int)$arr_info['reporting_what']);
 
                    $arr_who2      = $res_who2->fetch_assoc();
                    $link_to_thing = "<a class='altlink' href='requests.php?action=request_details&amp;id={$arr_who2['request']}&amp;viewcomm={$arr_info['reporting_what']}#comm{$arr_info['reporting_what']}'><strong>" . security::html_safe($arr_who2['username']) . "'{$lang['text_post_s']} {$lang['text_request_com']}</strong></a>";
                break;

                case "Torrent":
                    $res_who2 = $db->query("SELECT name
                                            FROM torrents
                                            WHERE id = " . (int)$arr_info['reporting_what']);

                    $arr_who2 = $res_who2->fetch_assoc();
                    $link_to_thing = "<a class='altlink' href='details.php?id={$arr_info['reporting_what']}'><strong>" . security::html_safe($arr_who2['name']) . "</strong></a>";
                break;

                case "Hit_And_Run":
                    $res_who2 = $db->query("SELECT users.username, torrents.name, r.2nd_value
                                            FROM users, torrents
                                            LEFT JOIN reports AS r ON r.2nd_value = torrents.
                                            WHERE users.id =  " . (int)$arr_info['reporting_what']);

                    $arr_who2      = $res_who2->fetch_assoc();
                    $link_to_thing = "<strong>{$lang['text_user']}</strong> <a class='altlink' href='userdetails.php?id=" . $arr_info['reporting_what'] . "&completed=1'><strong>" . security::html_safe($arr_who2['username']) . "</strong></a><br />{$lang['text_hit_run']}<br /> <a class='altlink' href=details.php?id={$arr_info['2nd_value']}&page2=0#snatched'><strong>" . htmlspecialchars($arr_who2['name']) . "</strong></a>";
                break;

                case "Post":
                    $res_who2 = $db->query("SELECT subject
                                            FROM topics
                                            WHERE id = " . (int)$arr_info['2nd_value']);

                    $arr_who2 = $res_who2->fetch_assoc();
                    $link_to_thing = "<strong>{$lang['text_topic']}&nbsp;</strong> <a class='altlink' href='forums.php?action=viewtopic&amp;topicid={$arr_info['2nd_value']}&amp;page=last#{$arr_info['reporting_what']}'><strong>" . security::html_safe($arr_who2['subject']) . "</strong></a>";
                break;
            }
        }

        echo("<tr>
                <td class='rowhead' align='center' valign='middle'>{$arr_info['added']}</td>
                <td class='rowhead' align='center' valign='middle'>
                    <a class='altlink' href='userdetails.php?id={$arr_info['reported_by']}'>" . "<strong>{$arr_info['username']}</strong></a>
                </td>
                <td class='rowhead' align='center' valign='middle'>$link_to_thing</td>
                <td class='rowhead' align='center' valign='middle'><strong>" . str_replace("_" , " ",$arr_info['reporting_type']) . "</strong>" . "</td>
                <td class='rowhead' align='center' valign='middle'>" . security::html_safe($arr_info['reason']) . "</td>
                <td class='rowhead' align='center' valign='middle'>$dealtwith $delt_link</td>
                <td class='rowhead' align='center' valign='middle'>$checkbox</td>
                    " . (get_user_class() >= UC_SYSOP ? "
                <td class='rowhead' align='center' valign='middle'>
                    <a class='btn' href='controlpanel.php?fileaction=26&amp;id={$arr_info['id']}&amp;delete=1'><span class='delete_reports'>{$lang['btn_delete']}</span></a>
                </td>" : "") . "
            </tr>\n");

        if (get_user_class() < UC_SYSOP)
        {
            //-- Who Dealt With It, When It Was Dealt With --//
            if ($arr_info['how_delt_with'])

            echo("<tr>
                    <td class='rowhead' align='center' colspan='7'>
                        <strong>{$lang['text_dealt_by']}" . security::html_safe($arr_who['username']) . ":</strong>{$lang['text_dealt_on']}{$arr_info['when_delt_with']} (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr_info['when_delt_with'])) . "{$lang['text_dealt_ago']})
                    </td>
            </tr>");
        }

        if (get_user_class() >= UC_SYSOP)
        {
            //-- Who Dealt With It, When It Was Dealt With & How It Was Dealt With --//
            if ($arr_info['how_delt_with'])
            {
                echo("<tr>
                        <td class='colhead' align='center' colspan='8'>
                            <strong>{$lang['text_dealt_by']}" . security::html_safe($arr_who['username']) . "</strong>{$lang['text_dealt_on']}{$arr_info['when_delt_with']} (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr_info['when_delt_with'])) . " ago)</td>
                    </tr>");

                echo("<tr>
                        <td class='rowhead' align='center' colspan='8'><em><strong>{$lang['text_dealt_how']}</strong></em><br /><br />
                            " . security::html_safe($arr_info['how_delt_with']) . "<br /><br />
                        </td>
                    </tr>
                    <tr>
                        <td  class='colhead' align='center' height='4' colspan='8'></td>
                    </tr>");
            }
        }
    }
}

echo("</table>");

if ($count > '0')
{
    //-- Explain How The Report Was Dealt With --//
    echo("<br /><br /><p align='center'><strong>{$lang['text_info_1']}</strong><br />
          {$lang['text_info_2']}<br />{$lang['text_info_3']}</p>
          <textarea name='how_delt_with' cols='80' rows='5'></textarea><br />
          <p align='center'>{$lang['text_info_4']}<br />
          {$lang['text_info_5']}<br />
          {$lang['text_info_6']}</p>");

    echo("<input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' /><br /><br />");

    echo("</form></td></tr></table>");
}
//-- End If Count --//

site_footer();
die;

?>