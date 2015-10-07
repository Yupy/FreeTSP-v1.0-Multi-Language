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

$lang = array_merge(load_language('adm_cleartable'),
                    load_language('adm_global'));

site_header("{$lang['title_clean']}", false);

$action = ($_POST['action'] ? security::html_safe($_POST['action']) : ($_GET['action'] ? security::html_safe($_GET['action']) : ''));

//-- Clear Shout Box --//
if ($action == 'clshout')
{
    error_message_center("warn",
                         "{$lang['gbl_adm_sanity']}",
                         "{$lang['text_clear_sure']}<strong>{$lang['text_clear_shout']}</strong><br />
                         <form method='post' action='controlpanel.php?fileaction=25&amp;action=clearshout'>
                            <input type='hidden' name='username' size='20' value='" . $username . "' />
                            <input type='submit' class='btn' value='{$lang['btn_confirm']}' />
                         </form>");

}

if ($action == 'clearshout')
{
    $db->query('TRUNCATE TABLE shoutbox') or sqlerr(__FILE__, __LINE__);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "{$lang['text_shout_empty']}<br />
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=25'>{$lang['text_return_clean']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>");
}

//-- Clear Private Messages --//
if ($action == 'clmsg')
{
    error_message_center("warn",
                         "{$lang['gbl_adm_sanity']}",
                         "{$lang['text_clear_sure']}<strong>{$lang['text_clear_pm']}</strong><br />
                         <form method='post' action='controlpanel.php?fileaction=25&amp;action=clearmsg'>
                            <input type='hidden'name='username' size='20' value='" . $username . "' />
                            <input type='submit' class='btn' value='{$lang['btn_confirm']}' />
                         </form>");
}

if ($action == 'clearmsg')
{
    $db->query('TRUNCATE TABLE messages') or sqlerr(__FILE__, __LINE__);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "{$lang['text_pm_empty']}<br />
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=25'>{$lang['text_return_clean']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>");
}

//-- Clear Staff Log --//
if ($action == 'clstlog')
{
    error_message_center("warn",
                         "{$lang['gbl_adm_sanity']}",
                         "{$lang['text_clear_sure']}<strong>{$lang['text_clear_staff_log']}</strong><br />
                         <form method='post' action='controlpanel.php?fileaction=25&amp;action=clearstlog'>
                            <input type='hidden' name='username' size='20' value='" . $username . "' />
                            <input type='submit' class='btn' value='{$lang['btn_confirm']}' />
                         </form>");
}

if ($action == 'clearstlog')
{
    $db->query('TRUNCATE TABLE stafflog') or sqlerr(__FILE__, __LINE__);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "{$lang['text_staff_log_empty']}<br />
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=25'>{$lang['text_return_clean']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>");
}

//-- Clear Site Log --//
if ($action == 'clsitelog')
{
    error_message_center("warn",
                         "{$lang['gbl_adm_sanity']}",
                         "{$lang['text_clear_sure']}<strong>{$lang['text_clear_site_log']}</strong><br />
                         <form method='post' action='controlpanel.php?fileaction=25&amp;action=clearsitelog'>
                            <input type='hidden' name='username' size='20' value='" . $username . "' />
                            <input type='submit' class='btn' value='{$lang['btn_confirm']}' />
                         </form>");
}

if ($action == 'clearsitelog')
{
    $db->query('TRUNCATE TABLE sitelog') or sqlerr(__FILE__, __LINE__);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "{$lang['text_site_log_empty']}<br />
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=25'>{$lang['text_return_clean']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>");
}

//-- Clear Max Login Attempts --//
if ($action == 'clsmaxlogin')
{
    error_message_center("warn",
                         "{$lang['gbl_adm_sanity']}",
                         "{$lang['text_clear_sure']}<strong>{$lang['text_clear_logins']}</strong><br />
                         <form method='post' action='controlpanel.php?fileaction=25&amp;action=clearmaxlogin'>
                            <input type='hidden' name='username' size='20' value='" . $username . "' />
                            <input type='submit' class='btn' value='{$lang['btn_confirm']}' />
                         </form>");
}

if ($action == 'clearmaxlogin')
{
    $db->query('TRUNCATE TABLE loginattempts') or sqlerr(__FILE__, __LINE__);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "{$lang['text_logins_empty']}<br />
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=25'>{$lang['text_return_clean']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>");
}

//-- Viewable Message On Entry To File --//
error_message_center("info",
                     "{$lang['text_select_table']}",
                     "{$lang['text_empty_site_log']}<a href='controlpanel.php?fileaction=25&amp;action=clsitelog'>{$lang['text_here']}</a>
                     <br /> {$lang['text_empty_staff_log']}<a href='controlpanel.php?fileaction=25&amp;action=clstlog'>{$lang['text_here']}</a>
                     <br /> {$lang['text_empty_pm']}<a href='controlpanel.php?fileaction=25&amp;action=clmsg'>{$lang['text_here']}</a>
                     <br /> {$lang['text_empty_shout']}<a href='controlpanel.php?fileaction=25&amp;action=clshout'>{$lang['text_here']}</a>
                     <br /> {$lang['text_empty_logins']}<a href='controlpanel.php?fileaction=25&amp;action=clsmaxlogin'>{$lang['text_here']}</a>");

site_footer();

?>