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

$lang = array_merge(load_language('adm_warned'),
                    load_language('func_vfunctions'),
                    load_language('adm_global'));

db_connect();
logged_in();

if (isset($_POST['nowarned']) && ($_POST['nowarned'] == 'nowarned'))
{
    if (empty($_POST['usernw']) && empty($_POST['desact']) && empty($_POST['delete']))
    {
        error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_tick_box']}");
    }

    if (!empty($_POST['usernw']))
    {
        $msg    = sqlesc("{$lang['msg_warn_removed']} : " . user::$current['username']);
        $added  = sqlesc(get_date_time());
        $userid = implode(", ", (int)$_POST['usernw']);

        $warn = $db->query("SELECT username
                            FROM users
                            WHERE id IN (" . implode(", ", (int)$_POST['usernw']) . ")") or sqlerr(__FILE__, __LINE__);

        $user     = $warn->fetch_array(MYSQLI_BOTH);
        $username = security::html_safe($user['username']);

        write_stafflog("<strong><a href='userdetails.php?id=$userid'>$username.</a></strong>{$lang['stafflog_removed_by']}" . user::$current['username']);

        $do  = "UPDATE users
                SET warned = 'no', warneduntil = '0000-00-00 00:00:00'
                WHERE id IN (" . implode(", ", (int)$_POST['usernw']) . ")";

        $res = $db->query($do);
		
		$Memcache->delete_value('details::dltable::user::stuff::' . $_POST['usernw']);
    }

    if (!empty($_POST['desact']))
    {
        $userid   = implode(", ", (int)$_POST['desact']);
        $disable  = $db->query("SELECT username
                                FROM users
                                WHERE id IN (" . implode(", ", (int)$_POST['desact']) . ")") or sqlerr(__FILE__, __LINE__);

        $user     = $disable->fetch_array(MYSQLI_BOTH);
        $username = security::html_safe($user['username']);

        write_stafflog("<strong><a href='userdetails.php?id=$userid'>$username.</a></strong>{$lang['stafflog_disabled_by']}" . user::$current['username']);

        $do  = "UPDATE users
                SET enabled = 'no'
                WHERE id IN (" . implode(", ", (int)$_POST['desact']) . ")";

        $res = $db->query($do);
    }
}

site_header("{$lang['title_warned_members']}", false);

$warned = number_format(get_row_count("users", "WHERE warned = 'yes' AND enabled = 'yes'"));

list($pagertop, $pagerbottom, $limit) = pager(25, $warned, "warned.php?");

$res = $db->query("SELECT id, username, uploaded, downloaded, added, last_access, class, donor, warned, enabled
                   FROM users
                   WHERE warned = 'yes'
                   AND enabled = 'yes'
                   ORDER BY username " . $limit) or sqlerr();

$num = $res->num_rows;

$res = $db->query("SELECT id, username, uploaded, downloaded, added, last_access, class, donor, warned, warneduntil
                   FROM users
                   WHERE warned = 'yes'
                   AND enabled = 'yes'
                   ORDER BY (users.uploaded / users.downloaded)") or sqlerr(__FILE__, __LINE__);

$num = $res->num_rows;

print("<h1>{$lang['title_warned_accounts']} : (<span class='warned_accounts'>$warned</span>)</h1>");

print($pagertop);

print("<form method='post' action='controlpanel.php?fileaction=20'><table border='1' width='81%' cellspacing='0' cellpadding='2'>");

print("<tr align='center'>
        <td class='colhead' width='250'>{$lang['form_username']}</td>
        <td class='colhead' width='70'>{$lang['form_registered']}</td>
        <td class='colhead' width='75'>{$lang['form_last_access']}</td>
        <td class='colhead' width='75'>{$lang['form_user_class']}</td>
        <td class='colhead' width='70'>{$lang['form_downloaded']}</td>
        <td class='colhead' width='70'>{$lang['form_uploaded']}</td>
        <td class='colhead' width='45'>{$lang['form_ratio']}</td>
        <td class='colhead' width='125'>{$lang['form_end']}<br/>{$lang['form_of_warning']}</td>
        <td class='colhead' width='65'>{$lang['form_remove']}<br/>{$lang['form_warning']}</td>
        <td class='colhead' width='65'>{$lang['form_disable']}<br/>{$lang['form_account']}</td>
    </tr>");

for ($i = 1;
     $i <= $num;
     $i++)
{
    $arr = $res->fetch_assoc();

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
        $ratio       = "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
    }
    else
    {
        $ratio       = '---';
    }

    $uploaded    = misc::mksize($arr['uploaded']);
    $downloaded  = misc::mksize($arr['downloaded']);
    $added       = substr($arr['added'],0, 10);
    $last_access = substr($arr['last_access'],0, 10);
    $class       = get_user_class_name($arr['class']);

    print("<tr>
            <td align='center'>" . format_username($arr) . "</td>
            <td class='rowhead' align='center'>$added</td>
            <td class='rowhead' align='center'>$last_access</td>
            <td class='rowhead' align='center'>$class</td>
            <td class='rowhead' align='center'>$downloaded</td>
            <td class='rowhead' align='center'>$uploaded</td>
            <td class='rowhead' align='center'>$ratio</td>
            <td class='rowhead' align='center'>{$arr['warneduntil']}</td>
            <td class='remove_warning' align='center'>
                <input type='checkbox' name='usernw[]' value='{$arr['id']}' />
            </td>
            <td class='disable_account' align='center'>
                <input type='checkbox' name='desact[]' value='{$arr['id']}' />
            </td>
        </tr>");
}

    print("<tr>
            <td align='center' colspan='10'>
                <input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' />
                <input type='hidden' name='nowarned' value='nowarned' />
            </td>
        </tr>");

    print("</table></form>");

print($pagerbottom);

site_footer();

?>