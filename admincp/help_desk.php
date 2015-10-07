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

$lang = array_merge(load_language('adm_helpdesk'),
                    load_language('func_bbcode'),
                    load_language('adm_global'));

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
    //$t     = "";

    if ($weeks > 0)
    {
        return "$weeks{$lang['text_week']}" . ($weeks > 1 ? "{$lang['text_post_s']}" : "");
    }

    if ($days > 0)
    {
        return "$days{$lang['text_day']}" . ($days > 1 ? "{$lang['text_post_s']}" : "");
    }

    if ($hours > 0)
    {
        return "$hours{$lang['text_hour']}" . ($hours > 1 ? "{$lang['text_post_s']}" : "");
    }

    if ($mins > 0)
    {
        return "$mins{$lang['text_min']}" . ($mins > 1 ? "{$lang['text_post_s']}" : "");
    }
    return "< 1{$lang['text_min']}";
}

    $msg_problem = trim($_POST['msg_problem']);
    $msg_answer  = trim($_POST['msg_answer']);
    $id          = (int)$_POST['id'];
    $addedbyid   = (int)$_POST['addedbyid'];
    $title       = trim($_POST['title']);
    $action      = security::html_safe($_GET['action']);
    $solve       = security::html_safe$_GET['solve'];

//-- Action: Cleanuphd --//
if ($action == 'cleanuphd')
{
    $db->query("DELETE
                FROM helpdesk
                WHERE solved = 'yes'
                OR solved = 'ignored'");
	
	$Memcache->delete_value('helpdesk::problems::count');

    $action = 'problems';
}

//-- Action: Problems --//
if ($action == 'problems')
{
    //-- Post & Get --//
    $id  = intval(0 + $_GET['id']);

    begin_frame($lang['title_problems']);

    //-- View Problem Details --//
    if ($id != 0)
    {
        $res = $db->query("SELECT *
                           FROM helpdesk
                           WHERE id = $id");

        $arr = $res->fetch_array();

        $problem = format_comment($arr['msg_problem']);
        $answer  = format_comment($arr['msg_answer']);

        $zap = $db->query("SELECT username
                           FROM users
                           WHERE id = " . (int)$arr['added_by']);

        $wyn = $zap->fetch_array(MYSQLI_BOTH);

        $added_by_name = security::html_safe($wyn['username']);

        $zap_s = $db->query("SELECT username
                             FROM users
                             WHERE id = " . (int)$arr['solved_by']);

        $wyn_s = $zap_s->fetch_array(MYSQLI_BOTH);

        $solved_by_name = securty::html_safe($wyn_s['username']);

        print("<form method='post' action='controlpanel.php?fileaction=23'>");
        print("<table border='1' align='center' width='70%' cellpadding='5' cellspacing='0'>");

        print("<tr>
                <td class='colhead' align='right'>
                    <strong>{$lang['table_added']}</strong>
                </td>

                <td align='left'>
                    {$lang['table_on']}<strong>{$arr['added']}</strong>{$lang['table_by']}
                    <a href='userdetails.php?id={$arr['added_by']}'><strong>$added_by_name</strong></a>
                </td>
               </tr>");

//-- Start View Question Answered --//
        if ($arr['solved'] == 'yes')
        {
            print("<tr>
                    <td class='colhead' align='right'>
                        <strong>{$lang['table_problem']}</strong>
                    </td>

                    <td class='comment'>$problem</td>
                   </tr>");

            print("<tr>
                    <td class='colhead' align='right'>
                        <strong>{$lang['table_solved']}</strong>
                    </td>

                    <td align='left'>
                        <span class='solved_yes_help_desk'>{$lang['table_solved_yes']}</span>{$lang['table_on']}
                        <strong>{$arr['solved_date']}</strong>{$lang['table_by']}
                        <a href='userdetails.php?id={$arr['solved_by']}'><strong>$solved_by_name</strong></a>
                    </td>
                   </tr>");

            print("<tr>
                    <td class='colhead' align='right'>
                        <strong>{$lang['table_answer']}</strong>
                    </td>

                    <td class='comment'>$answer</td>
                   </tr>");

            print("</table>");
            print("</form>");
        }
//-- Finish View Question Answered --//

//-- Start View Question Ignored --//
        else if ($arr['solved'] == 'ignored')
        {
            print("<tr>
                    <td class='colhead' align='right'>
                        <strong>{$lang['table_problem']}</strong>
                    </td>

                    <td class='comment'>$problem</td>
                   </tr>");

            print("<tr>
                    <td class='colhead' align='right'>
                        <strong>{$lang['table_answer']}</strong>
                    </td>

                    <td class='comment'>$answer</td>
                   </tr>");

            print("<tr>
                    <td class='colhead' align='right'>
                        <strong>{$lang['table_solved']}</strong>
                    </td>

                    <td class='rowhead' align='left'>
                        <span class='solved_ignored_help_desk'>{$lang['table_ignored']}</span>{$lang['table_on']}
                        <strong>{$arr['solved_date']}</strong>{$lang['table_by']}
                        <a href='userdetails.php?id={$arr['solved_by']}'><strong>$solved_by_name</strong></a>
                    </td>
                   </tr>");

            print("</table>");
            print("</form>");
        }
//-- Finish View Question Ignored --//

//-- Start View Question --//
        else if ($arr['solved'] == 'no')
        {
            $addedbyid = (int)$arr['added_by'];

            print("<tr>
                    <td class='colhead' align='right'><strong>{$lang['table_problem']}</strong></td>
                    <td class='comment'>$problem</td>
                   </tr>");

            print("<tr>
                    <td class='colhead' align='right'><strong>{$lang['table_solved']}</strong></td>
                    <td class='colhead' align='center'><span class='solved_no_help_desk'>{$lang['table_no']}</span></td>
                   </tr>");

            print("<tr>
                    <td class='colhead' align='right'><strong>{$lang['form_answer']}</strong></td>
                    <td>
                        " . textbbcode("compose", "msg_answer", $body) . "
                        <input type='hidden' name='id' value='$id' />
                        <input type='hidden' name='addedbyid' value='$addedbyid' />
                    </td>
                   </tr>");

            print("<tr>
                    <td class='rowhead' colspan='2' align='center'>
                        <input type='submit' class='btn' value='{$lang['btn_answer']}' />
                        <a class='btn' href='controlpanel.php?fileaction=23&amp;action=solve&amp;pid=$id&amp;solved=ignored'><span class='solved_ignore_help_desk'>{$lang['table_ignore']}</span></a>
                    </td>
                   </tr>");

            print("</table>");
            print("</form>");
        }
    }
//-- Finish View Question --//

//-- Start Question List --//
    else
    {
        print("<table border='1' align='center' cellpadding='5' cellspacing='0'>
                <tr>
                   <td class='colhead' align='center'>{$lang['table_added']}</td>
                   <td class='colhead' align='center'>{$lang['table_added_by']}</td>
                   <td class='colhead' align='center'>{$lang['table_problem']}</td>
                   <td class='colhead' align='center'>{$lang['table_solved_by']}</td>
                   <td class='colhead' align='center'>{$lang['table_solved_in']}</td>
                </tr>");

        $res = $db->query("SELECT *
                           FROM helpdesk
                           ORDER BY added DESC");

        while($arr = $res->fetch_array(MYSQLI_BOTH))
        {
            $zap = $db->query("SELECT username
                               FROM users
                               WHERE id = " . (int)$arr['added_by']);

            $wyn = $zap->fetch_array(MYSQLI_BOTH);

            $added_by_name = security::html_safe($wyn['username']);

            $zap_s = $db->query("SELECT username
                                 FROM users
                                 WHERE id = " . (int)$arr['solved_by']);

            $wyn_s = $zap_s->fetch_array(MYSQLI_BOTH);

            $solved_by_name = security::html_safe($wyn_s['username']);

            //-- Solved In --//
            $added       = $arr['added'];
            $solved_date = $arr['solved_date'];

            if ($solved_date == '0000-00-00 00:00:00')
            {
                $solved_in = ' [N/A]';
                $solved_color = "'solved_na'";
            }
            else
            {
                $solved_in_wtf = sql_timestamp_to_unix_timestamp($arr['solved_date']) - sql_timestamp_to_unix_timestamp($arr['added']);
                $solved_in  = " [" . round_time($solved_in_wtf) . "]";

                if ($solved_in_wtf > 2 * 3600)
            {
                $solved_color = "'solved_more2hours'";
            }
            else if ($solved_in_wtf > 3600)
            {
                $solved_color = "'solved_more1hour'";
            }
            else if ($solved_in_wtf <= 1800)
            {
                $solved_color = "'solved_less30mins'";
            }
        }

            print("<tr>
                    <td>{$arr['added']}</td>
                    <td><a href='userdetails.php?id={$arr['added_by']}'>$added_by_name</a></td>
                    <td><a href='controlpanel.php?fileaction=23&amp;action=problems&amp;id={$arr['id']}'><strong>" . security::html_safe($arr['title']) . "</strong></a></td>");

            if ($arr['solved'] == 'no')
            {
            $solved_by = 'N/A';

            print("<td><span class='solved_no_help_desk'>{$lang['table_no']}</span> - $solved_by</td>");
            }
            else if ($arr['solved'] == 'yes')
            {
                $solved_by = "<a href='userdetails.php?id={$arr['solved_by']}'>$solved_by_name</a>";

                print("<td><span class='solved_yes_help_desk'>{$lang['table_yes']}</span> - $solved_by</td>");
            }
            else if ($arr['solved'] == 'ignored')
            {
                $solved_by = "<a href='userdetails.php?id={$arr['solved_by']}'>$solved_by_name</a>";

                print("<td><span class='solved_ignored_help_desk'>{$lang['table_ignored']}</span> - $solved_by</td>");
            }

            print("<td><span class=$solved_color>$solved_in</span></td></tr>");
        }

        if (get_user_class() >= UC_SYSOP)
        {
            print("<tr>
                        <td colspan='5' align='center'>
                            <form method='post' action='controlpanel.php?fileaction=23&amp;action=cleanuphd'>
                                <br /><input type='submit' class='btn' value='{$lang['btn_del_probs']}' />
                            </form><br />
                       </td>
                    </tr>
                </table>");
        }
    }
//-- Finish Question List --//

    end_frame();

    site_footer();
    exit;
}

//-- Main File --//
site_header("{$lang['title_header']}", false);

//--- Start Ignored Updates --//
if ($action == 'solve')
{
    $pid = intval(0 + $_GET['pid']);

    if ($solve = 'ignored')
    {
        $answer = sqlesc($lang['title_ignored']);
        $dt     = sqlesc(get_date_time());

        $db->query("UPDATE helpdesk
                    SET solved = 'ignored', solved_by = " . user::$current['id'] . ", solved_date = " . $dt . ", msg_answer = " . sqlesc($msg_answer) . "
                    WHERE id = " . $pid);
		
		$Memcache->delete_value('helpdesk::problems::count');

        display_message_center("info",
                               "{$lang['gbl_adm_info']}",
                               "{$lang['text_solved']}<br />
                               {$lang['text_return']}<a href='controlpanel.php?fileaction=23&amp;action=problems'>
                               <strong>{$lang['text_help']}</strong></a>{$lang['text_solve_more']}<br />");

        site_footer();
        exit;
    }
}
//-- Finish Ignored Updates --//

//-- Start Answer Updates --//
if (($msg_answer != "") && ($id != 0))
{
    $zap_usr = $db->query("SELECT username
                           FROM users
                           WHERE id = $addedbyid");

    $wyn_usr = $zap_usr->fetch_array(MYSQLI_BOTH);

    $addedby_name = security::html_safe($wyn_usr['username']);
    $system_name  = sqlesc($lang['title_header']);
    $subject      = sqlesc($lang['title_reply_from']);
    $msg          = sqlesc("[b]{$lang['title_reply_msg']}[/b]\n\n" . $msg_answer . "\n\n{$lang['message_regards']}\n\n{$lang['message_from']}" . $site_name . "");
    $dt           = sqlesc(get_date_time());

    $db->query("UPDATE helpdesk
                SET solved = 'yes', solved_by = " . user::$current['id'] . ", solved_date = $dt, msg_answer = " . sqlesc($msg_answer) . "
                WHERE id = $id");
	
	$Memcache->delete_value('helpdesk::problems::count');

    $db->query("INSERT INTO messages (sender, receiver, added, subject, msg, poster, unread)
                VALUES($system_name, $addedbyid, $dt, $subject, $msg, " . user::$current['id'] . ", 'yes')");

    $res1 = $db->query("SELECT title
                        FROM helpdesk
                        WHERE id = $id");

    $arr1  = $res1->fetch_array(MYSQLI_BOTH);
    $title = format_comment($arr1['title']);

    display_message_center("success",
                           "{$lang['gbl_adm_success']}",
                           "{$lang['text_prob_id']}&nbsp;&nbsp;$id<br />
                            {$lang['text_prob_title']}<strong>$title</strong><br />
                            {$lang['text_req_by']}<strong>$addedby_name</strong><br />
                            {$lang['text_answer_by']}<strong>" . security::html_safe(user::$current['username']) . "</strong><br />
                            {$lang['text_msg_sent']}<strong>$addedby_name</strong><br /><br />
                            {$lang['text_return']}<a href='controlpanel.php?fileaction=23&amp;action=problems'><strong>
                            {$lang['text_help']}</strong></a>{$lang['text_solve_more']}<br />");

  site_footer();
  exit;
}
//--- Finish Answer Updates --//

display_message_center("info",
                       "{$lang['text_help_desk']}",
                       "<a class='btn' href='controlpanel.php?fileaction=23&amp;action=problems'>{$lang['btn_view_all']}</a>");

?>