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

$lang = array_merge(load_language('adm_docleanup'),
                    load_language('adm_global'));

site_header("{$lang['title_cleanup']}", false);

$now = vars::$timestamp;

$res = $db->query("SELECT value_u
                   FROM avps
                   WHERE arg = 'lastcleantime'");

$row = $res->fetch_array(MYSQLI_BOTH);

if (!$row)
{
    $db->query("INSERT INTO avps (arg, value_u)
                VALUES ('lastcleantime', " . $now . ")");

    require_once(FUNC_DIR . 'function_cleanup.php');

    return;
}

$ts = $row[0];

$db->query("UPDATE avps
            SET value_u = $now
            WHERE arg = 'lastcleantime'
            AND value_u = $ts");

require_once(FUNC_DIR . 'function_cleanup.php');

if (!$db->affected_rows)
{
    return;
}

docleanup(true);

        error_message_center("success",
                             "{$lang['gbl_adm_success']}",
                             "<strong>{$lang['text_performed']}</strong><br />
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");

site_footer();

?>