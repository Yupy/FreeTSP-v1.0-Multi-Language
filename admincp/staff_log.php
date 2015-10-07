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

$lang = array_merge(load_language('adm_staff_log'),
                    load_language('adm_global'));

db_connect(false);
logged_in();

global $staff_log;

$secs = $staff_log;

site_header("{$lang['title_staff_log']}", false);

//-- Get Stuff For The Pager --//
$count_query = $db->query("SELECT COUNT(id)
                           FROM stafflog");

$count_arr = $count_query->fetch_row();
$count     = (int)$count_arr[0];
$page      = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$perpage   = isset($_GET['perpage']) ? (int)$_GET['perpage'] : 10;

list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'controlpanel.php?fileaction=19' . ($perpage == 10 ? '' : '&amp;perpage=' . $perpage));

$db->query("DELETE
            FROM stafflog
            WHERE " . gmtime() . " - UNIX_TIMESTAMP(added) > " . $secs) or sqlerr(__FILE__, __LINE__);

$res = $db->query("SELECT added, txt
                   FROM stafflog
                   ORDER BY added DESC " . $LIMIT);

echo("<h1>{$lang['title_staff_log']}</h1>");

if ($res->num_rows == 0)
{
  echo("<strong>{$lang['text_empty_log']}</strong>");
}
else
{
    echo $menu;

    echo("<table border='1' cellspacing='0' cellpadding='5'>");
    echo("<tr>
            <td class='colhead' align='center' width='60px'>{$lang['table_date']}</td>
            <td class='colhead' align='center' width='60px'>{$lang['table_time']}</td>
            <td class='colhead' align='left' width='450px'>{$lang['table_event']}</td>
        </tr>");

    while ($arr = $res->fetch_assoc())
    {
        $date = substr($arr['added'], 0, strpos($arr['added'], " "));
        $time = substr($arr['added'], strpos($arr['added'], " ") + 1);

        echo("<tr>
                <td class='rowhead' align='center'>" . $date . "</td>
                <td class='rowhead' align='center'>" . $time . "</td>
                <td class='rowhead' align='left'>" . unesc($arr['txt']) . "</td>
            </tr>");
    }
    echo("</table>");
}
echo $menu;

echo("<p>{$lang['text_gmt']}</p>");

site_footer();

?>