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

$lang = array_merge(load_language('adm_quickdelete'),
                    load_language('adm_global'));

$posted_action   = strip_tags((isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '')));

$valid_actions = array('del_member',
                       'con_del_member');

//-- Check Posted Action, And If No Action Was Posted, Show The Default Page --//
$action = (in_array($posted_action, $valid_actions) ? $posted_action : 'default');

switch ($action)
{
    case 'del_member';
        $username = $_POST['username'];

        if (!$username)
        {
           error_message_center("error",
                                "{$lang['gbl_adm_error']}",
                                "{$lang['err_no_username']}");
        }

        $res = $db->query("SELECT id, username, class
                           FROM users
                           WHERE username = " . sqlesc($username)) or sqlerr(__FILE__, __LINE__);

        $arr      = $res->fetch_assoc();
        $id       = (int)$arr['id'];
        $username = security::html_safe$arr['username'];
        $class    = (int)$arr['class'];

        if ($res->num_rows != 1)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "<strong>{$lang['err_not_exist']}</strong><br />
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=17'>{$lang['text_quick_del']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }

        if ($username == user::$current['username'])
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "<strong>{$lang['text_del_you']}</strong><br />
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=17'>{$lang['text_quick_del']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }

        if ($class >= user::$current['class'])
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "<strong>{$lang['text_no_perm']}</strong><br />
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=17'>{$lang['text_quick_del']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }

        error_message_center("warn",
                             "{$lang['gbl_adm_warn']}",
                             "{$lang['text_del_sure']}&nbsp;<strong>$username ?</strong><br />
                             <br /> {$lang['text_click']}&nbsp;<a href='controlpanel.php?fileaction=17&amp;action=con_del_member&amp;id=$id'>{$lang['text_here']}</a><br />
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=17'>{$lang['text_quick_del']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    break;

    case 'con_del_member';
        $id = (int)$_GET['id'];

        $db->query("DELETE
                    FROM users
                    WHERE id = $id") or sqlerr(__FILE__, __LINE__);
		
		$Memcache->delete_value('user::profile::' . $id);

        error_message_center("success",
                             "{$lang['gbl_adm_success']}",
                             "<strong>{$lang['text_del_success']}</strong><br />
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=17'>{$lang['text_quick_del']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    break;

    case 'default';
        print("<h1>{$lang['title_del_member']}</h1>");
        print("<form method='post' action='controlpanel.php?fileaction=17&amp;action=del_member'>");
        print("<table border='1' cellspacing='0' cellpadding='5'>");

        print("<tr>
                <td class='colhead'>
                    <label for='delete'>{$lang['form_username']}</label></td>
                <td class='rowhead'>
                    <input type='text' name='username' id='delete' size='40' />
                </td>
            </tr>");

        print("<tr>
                <td class='std' align='center' colspan='2'>
                    <input type='submit' class='btn' value='{$lang['btn_del']}' />
                </td>
            </tr>");

        print("</table>");
        print("</form>");
  break;
}

?>