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

$lang = array_merge(load_language('adm_bans'),
                    load_language('adm_global'));

$remove = isset($_GET['remove']) ? (int) $_GET['remove'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && user::$current['class'] >= UC_MODERATOR)
{
    $first   = trim($_POST['first']);
    $last    = trim($_POST['last']);
    $comment = trim($_POST['comment']);

    if (!$first || !$last || !$comment)
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_missing']}");
    }

    $first = ip2long($first);
    $last  = ip2long($last);

    if ($first == -1 || $first === false || $last == -1 || $last === false)
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_badip']}");
    }

    $added   = sqlesc(get_date_time());
	
	for ($i = $first; $i <= $last; $i++) {
        $key = 'banned::' . long2ip($i);
        $Memcache->delete_value($key);
    }

    $db->query("INSERT INTO bans (added, addedby, first, last, comment)
                VALUES($added, " . sqlesc(user::$current['id']) . ", $first, $last, " . sqlesc($comment) . ")") or sqlerr(__FILE__, __LINE__);

      error_message_center("success",
                           "{$lang['gbl_adm_success']}",
                           "<strong>{$lang['text_added']}</strong><br />
                           <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=1'>{$lang['text_return_bans']}</a>
                           <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");

    die;
}

if (is_valid_id($remove))
{
    @$db->query("DELETE
                 FROM bans
                 WHERE id = " . sqlesc($remove) . "") or sqlerr();

    $removed = sprintf($lang['log_removed'], $remove);

    write_stafflog("{$removed}<strong><a href='userdetails.php?id=" . user::$current['id'] ."'>" . user::$current['username'] . "</a></strong>");
}

$res = $db->query("SELECT first, last, added, addedby, comment, id
                   FROM bans
                   ORDER BY added DESC") or sqlerr();

//-- Start Display Existing Bans --//
site_header("{$lang['title_bans']}", false);

print("<h1>{$lang['title_current']}</h1>");

if ($res->num_rows == 0)
{
    display_message_center("info",
                           "{$lang['gbl_adm_sorry']}",
                           "{$lang['err_nothing']}");
}
else
{
    print("<table border='1' align='center' cellspacing='0' cellpadding='5'>");
    print("<tr>
            <td class='colhead' align='center'>{$lang['table_added']}</td>
            <td class='colhead' align='center'>{$lang['table_firstip']}</td>
            <td class='colhead' align='center'>{$lang['table_lastip']}</td>
            <td class='colhead' align='center'>{$lang['table_by']}</td>
            <td class='colhead' align='center'>{$lang['table_comment']}</td>
            <td class='colhead' align='center'>{$lang['gbl_adm_btn_remove']}</td>
        </tr>");

    while ($arr = $res->fetch_assoc())
    {
        $r2 = $db->query("SELECT username
                          FROM users
                          WHERE id = " . (int)$arr['addedby']) or sqlerr();

        $a2 = $r2->fetch_assoc();

        $arr['first'] = long2ip($arr['first']);
        $arr['last']  = long2ip($arr['last']);

        print("<tr>
                <td class='rowhead'>{$arr['added']}</td>
                <td class='rowhead' align='left'>{$arr['first']}</td>
                <td class='rowhead' align='left'>{$arr['last']}</td>
                <td class='rowhead' align='left'><a href='userdetails.php?id={$arr['addedby']}'>" . security::html_safe($a2['username']) . "</a></td>
                <td class='rowhead' align='left'>" . htmlentities($arr['comment'], ENT_QUOTES) . "</td>
                <td class='rowhead'><a class='btn' href='controlpanel.php?fileaction=1&amp;remove={$arr['id']}'>{$lang['btn_del_ban']}</a></td>
            </tr>");
    }
    print("</table>");
}
//-- End Display Existing Bans --//

//-- Start Ban Form --//
print("<h2>{$lang['title_addban']}</h2>");
print("<form method='post' action='controlpanel.php?fileaction=1'>");
print("<table border='1' align='center' cellspacing='0' cellpadding='5'>");

print("<tr>
        <td class='colhead'>
            <label for='first'>{$lang['form_firstip']}</label>
        </td>
        <td class='rowhead'>
            <input type='text' name='first' id='first' size='40' />
        </td>
    </tr>");

print("<tr>
        <td class='colhead'>
            <label for='last'>{$lang['form_lastip']}</label>
        </td>
        <td class='rowhead'>
            <input type='text' name='last' id='last' size='40' />
        </td>
    </tr>");

print("<tr>
        <td class='colhead'>
            <label for='comment'>{$lang['form_comment']}</label>
        </td>
        <td class='rowhead'>
            <input type='text' name='comment' id='comment' size='40' />
        </td>
    </tr>");

print("<tr>
        <td class='std' colspan='2' align='center'>
            <input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' />
        </td>
    </tr>");

print("</table>");
print("</form><br />");
//-- End Form --//

site_footer();

?>