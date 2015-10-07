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
                security::html_safe($_GET['error']);
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

$lang = array_merge(load_language('adm_site_announcement'),
                    load_language('func_bbcode'),
                    load_language('adm_global'));

if ($_SERVER['REQUEST_METHOD'] == 'POST')

{
    $class = (isset($_POST['class']) ? $_POST['class'] : '');

    if (!$_POST['class'])
    {
        error_message_center("warn",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_inv_class']}");
    }

    $body = trim((isset($_POST['message']) ? $_POST['message'] : ''));

    if (!$_POST['message'])
    {
        error_message_center("warn",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_miss_announce']}");
    }

    $subject = trim((isset($_POST['subject']) ? $_POST['subject'] : ''));

    if (!$_POST['subject'])
    {
        error_message_center("warn",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_no_subject']}");
    }

    $expiry  = 0 + (isset($_POST['expiry']) ? (int)$_POST['expiry'] : 0);
    $expires = get_date_time((strtotime(get_date_time()) + (86400 * $expiry))); // 86400 seconds in one day.
    $created = get_date_time();

    $ann_query = ("SELECT u.id ".
                  "FROM users AS u ".
                  "WHERE class $class");

    $query = sprintf('INSERT INTO announcement_main ' . '(owner_id, created, expires, sql_query, subject, body) ' .
                     'VALUES (%s, %s, %s, %s, %s, %s)', sqlesc(user::$current['id']), sqlesc($created), sqlesc($expires), sqlesc($ann_query), sqlesc($subject), sqlesc($body));

    $db->query($query);

    error_message_center("info",
                         "{$lang['gbl_adm_info']}",
                         "{$lang['text_announce_made']}<br /><br />
                          {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=28'>{$lang['text_announment_pg']}</a><br />
                          {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a><br />
                          {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
}

site_header("{$lang['title_mass_announce']}", false);

    print("<div align='center'><h2>{$lang['text_announce_class']}</h2></div>
           <div align='center'>( {$lang['text_click']}<a class='btn' href='controlpanel.php?fileaction=3'>{$lang['text_here']}</a> )</div>
           <div align='center'>( {$lang['text_example']}</div><br />");

    print("<form name='compose' method='post' action='controlpanel.php?fileaction=28'>
           <table class='main' width='81%' cellspacing='0' cellpadding='5' >");

    print("<tr>
               <td class='rowhead' align='center' colspan='5'>{$lang['text_veiwable_by']}</td>
           </tr>");

    print("<tr>
                <td class='colhead' align='center' width='20%'>{$lang['form_all_mbrs']}</td>
                <td class='colhead' align='center' width='20%'>{$lang['form_users']}</td>
                <td class='colhead' align='center' width='20%'>{$lang['form_pwr_user']}</td>
                <td class='colhead' align='center' width='20%'>{$lang['form_vip']}</td>
                <td class='colhead' align='center' width='20%'>{$lang['form_staff']}</td>
            </tr>");

    print("<tr>
                <td class='rowhead' align='center' width='20%'>
                    <input type='checkbox' name='class' value='>= 0' />
                </td>

                <td class='rowhead' align='center' width='20%'>
                    <input type='checkbox' name='class' value='= 0' />
                </td>

                <td class='rowhead' align='center' width='20%'>
                    <input type='checkbox' name='class' value='= 1' />
                </td>

                <td class='rowhead' align='center' width='20%'>
                    <input type='checkbox' name='class' value='= 2' />
                </td>

                <td class='rowhead' align='center' width='20%'>
                    <input type='checkbox' name='class' value='>= 3' />
                </td>
            </tr>");

    print("<tr>
                <td class='rowhead' align='center' colspan='5'>{$lang['form_subject']}&nbsp;&nbsp;
                    <input type='text' name='subject' size='76' />
                </td>
           </tr>");

    print("<tr>
                <td class='rowhead' align='center' colspan='5'>" . textbbcode("compose", "message", $body) . "</td>
            </tr>");

    print("<tr><td class='rowhead' align='center' colspan='5'>");

    print("<select name='expiry'>");

           $days = array(
                         array(7, '7 Days'),
                         array(14, '14 Days'),
                         array(21, '21 Days'),
                         array(28, '28 Days'),
                         array(56, '2 Months')
                         );
           reset($days);
           foreach($days AS $x)

    print("<option value='" . $x[0] . "'" . (($expiry == $x[0] ? "" : "")) . ">" . $x[1] . "</option>");

    print("</select>&nbsp;&nbsp;<input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' />");

    print("</td></tr>");

    print("</table>
           </form>");

site_footer();
?>