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

$lang = array_merge(load_language('adm_inviteadd'),
                    load_language('adm_global'));

$class = (isset($_POST['class']) ? $_POST['class'] : '');
$give  = (isset($_POST['give']) ? $_POST['give'] : '');

if ($give)
{
    $invite_options = array('>= 0' => 1,
                            '= 0'  => 2,
                            '= 1'  => 3,
                            '= 2'  => 4,
                            '>= 3' => 5);

    if (!isset($invite_options[$class]))
    {
        error_message("warn",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_invalid']}");
    }

    if ($give != 0)
    {
        $res = $db->query("UPDATE users
                           SET invites = invites + $give
                           WHERE class $class") or sqlerr(__FILE__, __LINE__);

        $expires   = get_date_time((strtotime(get_date_time()) + (86400 * 7))); // 86400 seconds in one day.
        $created   = get_date_time();

        $ann_query = ("SELECT u.id ".
                      "FROM users AS u ".
                      "WHERE class $class");

        $subject   = ("{$lang['msg_subject']} $site_name");
        $body      = ("{$lang['msg_body']} $give {$lang['msg_invite']}" . ($give > 1 ? "{$lang['text_post_s']}" : "") . "{$lang['msg_added']}");

        $query = sprintf('INSERT INTO announcement_main ' . '(owner_id, created, expires, sql_query, subject, body) ' .
                         'VALUES (%s, %s, %s, %s, %s, %s)', sqlesc(user::$current['id']), sqlesc($created), sqlesc($expires), sqlesc($ann_query), sqlesc($subject), sqlesc($body));

        $db->query($query);

        error_message_center("success",
                             "{$lang['gbl_adm_success']}",
                             "<strong>{$lang['text_added']}</strong><br />
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=27'>{$lang['text_invite']}</a>{$lang['text_create_more']}
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    }
    else
    {
    $res = $db->query("UPDATE users
                       SET invites = 0
                       WHERE class $class");
    }
}

site_header("{$lang['title_add_invites']}", false);

print("<div align='center'><h2>{$lang['title_per_class']}</h2></div>");
print("<form method='post' action='controlpanel.php?fileaction=27'>");
print("<table class='main' align='center' width='81%'>");

print("<tr>
        <td class='colhead' align='center' width='20%'>{$lang['form_add_all']}</td>
        <td class='colhead' align='center' width='20%'>{$lang['form_add_users']}</td>
        <td class='colhead' align='center' width='20%'>{$lang['form_add_pu']}</td>
        <td class='colhead' align='center' width='20%'>{$lang['form_add_vip']}</td>
        <td class='colhead' align='center' width='20%'>{$lang['form_add_staff']}</td>
    </tr>");

print("<tr>
        <td class='rowhead' align='center' width='20%'>
            <input type='radio' name='class' value='>= 0' checked='checked' />
        </td>

        <td class='rowhead' align='center' width='20%'>
            <input type='radio' name='class' value='= 0' />
        </td>

        <td class='rowhead' align='center' width='20%'>
            <input type='radio' name='class' value='= 1' />
        </td>

        <td class='rowhead' align='center' width='20%'>
            <input type='radio' name='class' value='= 2' />
        </td>

        <td class='rowhead' align='center' width='20%'>
            <input type='radio' name='class' value='>= 3' />
        </td>
    </tr>");

print("<tr>
        <td class='rowhead' align='center' colspan='5'><br />
            <select name='give'>
                <option value='1'>{$lang['text_add_one']}</option>
                <option value='2'>{$lang['text_add_two']}</option>
                <option value='3'>{$lang['text_add_three']}</option>
                <option value='5'>{$lang['text_add_five']}</option>
                <option value='10'>{$lang['text_add_ten']}</option>
            </select>&nbsp;&nbsp;
            <input type='submit' class='btn' value='{$lang['btn_add']}' /><br /><br />
        </td>
    </tr>");

print("<tr>
            <td class='colhead' align='center' colspan='5'>
                <a class='btn' href=\"javascript: klappe_news('a5')\"><span class='add_invites'>{$lang['btn_remove_all']}</span></a>
                <div id='ka5' style='display : none'><br />
                    <input type='submit' class='btn' name='give' value='{$lang['btn_confirm']}' />
                </div>
            </td>
        </tr>");

print("</table>");
print("</form>");

site_footer();

die;

?>