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

$lang = array_merge(load_language('adm_ipcheck'));

site_header("{$lang['title_dupe']}", false);

$res = $db->query("SELECT id, username, ip, email, uploaded, downloaded, added, last_access, class, donor, warned
                   FROM users
                   WHERE enabled = 'yes'
                   AND status = 'confirmed'
                   AND ip <> ''
                   ORDER BY ip") or sqlerr();

$num = $res->num_rows;

print("<h1>{$lang['title_dupe']}</h1>");

print("<table class='main' border='1' cellspacing='0' cellpadding='5'>");
print("<tr align='center'>
        <td class='colhead' width='90'>{$lang['table_user']}</td>
        <td class='colhead' width='70'>{$lang['table_email']}</td>
        <td class='colhead' width='70'>{$lang['table_registered']}</td>
        <td class='colhead' width='75'>{$lang['table_access']}</td>
        <td class='colhead' width='70'>{$lang['table_downloaded']}</td>
        <td class='colhead' width='70'>{$lang['table_uploaded']}</td>
        <td class='colhead' width='45'>{$lang['table_ratio']}</td>
        <td class='colhead' width='125'>{$lang['table_ip']}</td>
    </tr>");

$uc = '0';
$ip = '';

while($ras = $res->fetch_assoc())
{
    if ($ip <> $ras['ip'])
    {
        $ros = sql_query("SELECT id, username, ip, email, uploaded, downloaded, added, last_access, class, donor, warned
                          FROM users
                          WHERE ip = '" . $db->real_escape_string($ras['ip']) . "'
                          ORDER BY id") or sqlerr(__FILE__, __LINE__);

        $num2 = $ros->num_rows;

        if ($num2 > 1)
        {
            $uc++;

            while($arr = $ros->fetch_assoc())
            {
                if ($arr['added'] == '0000-00-00 00:00:00')
                {
                    $arr['added'] = '-';
                }

                if ($arr['last_access'] == '0000-00-00 00:00:00')
                {
                    $arr['last_access'] = '-';
                }

                if ($arr['downloaded'] != 0)
                {
                     $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
                     $ratio  = "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
                }
                else
                {
                     $ratio  = '---';
                }

                $uploaded = misc::mksize($arr['uploaded']);
                $downloaded = misc::mksize($arr['downloaded']);
                $added   = substr($arr['added'],0, 10);
                $last_access = substr($arr['last_access'],0, 10);
                $class   = get_user_class_name($arr['class']);

                if ($uc %2 == 0)
                {
                    $utc = "a08f74";
                }
                else
                {
                    $utc = "bbaf9b";
                }

                print("<tr bgcolor='#$utc'>
                        <td class='rowhead' align='left'>" . format_username($arr) . "</td>
                        <td class='rowhead' align='center'>" . security::html_safe($arr['email']) . "</td>
                        <td class='rowhead' align='center'>$added</td>
                        <td class='rowhead' align='center'>$last_access</td>
                        <td class='rowhead' align='center'>$downloaded</td>
                        <td class='rowhead' align='center'>$uploaded</td>
                        <td class='rowhead' align='center'>$ratio</td>
                        <td class='rowhead' align='center'>" . unesc($arr['ip']) . "</td>
                    </tr>");

                $ip = $arr['ip'];
            }
        }
    }
}

print("</table>");

site_footer();

?>