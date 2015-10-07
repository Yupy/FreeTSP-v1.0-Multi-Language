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

$lang = array_merge(load_language('adm_news'),
                    load_language('func_bbcode'),
                    load_language('func_vfunctions'),
                    load_language('adm_global'));

$action = isset($_GET['action']) ? security::html_safe($_GET['action']) : '';

//-- Start Delete News Item --//
if ($action == 'delete')
{
    $newsid = isset($_GET['newsid']) ? (int) $_GET['newsid'] : 0;

    if (!is_valid_id($newsid))
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "<strong>{$lang['err_inv_id1']}</strong><br />
                      <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=6'>{$lang['title_news']}</a>
                      <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                      <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    }

    $returnto = isset($_GET['returnto']) ? htmlentities($_GET['returnto']) : '';
    $sure     = isset($_GET['sure']) ? (int) $_GET['sure'] : 0;

    if (!$sure)
    {
        error_message_center("warn",
                      "{$lang['gbl_adm_warn']}",
                      "{$lang['text_del_sure']}<br /><br />
                      <a class='btn' href='controlpanel.php?fileaction=6&amp;action=delete&amp;newsid=$newsid&amp;returnto=$returnto&amp;sure=1'>{$lang['btn_confirm']}</a>");
    }

    $db->query("DELETE
                FROM news
                WHERE id = $newsid
                AND userid = " . user::$current['id']);

    $Memcache->delete_value('current::news');

    if ($returnto != "")
    {
        $warning = "{$lang['text_news_deleted']}";
    }
}
//-- End Delete News Item --//

//-- Start Add News Item --//
if ($action == 'add')
{
    $body = isset($_POST['body']) ? (string) $_POST['body'] : 0;

    if (!$body)
    {
        error_message_center("error",
                      "{$lang['gbl_adm_error']}",
                      "<strong>{$lang['err_empty']}</strong><br />
                      <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=6'>{$lang['title_news']}</a>
                      <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                      <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    }

    $body  = sqlesc($body);
    $added = isset($_POST['added']) ? $_POST['added'] : 0;

    if (!$added)
    {
        $added = sqlesc(get_date_time());
    }

    @$db->query("INSERT INTO news (userid, added, body)
                 VALUES (" . user::$current['id'] . ", $added, $body)") or sqlerr(__FILE__, __LINE__);

    $Memcache->delete_value('current::news');

    if ($db->affected_rows == 1)
    {
        $warning = error_message_center("success",
                                 "{$lang['gbl_adm_success']}",
                                 "<strong>{$lang['text_news_added']}</strong><br />
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=6'>{$lang['title_news']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    }
    else
    {
        error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "<strong>{$lang['text_weird']}</strong><br />
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=6'>{$lang['title_news']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    }
}
//-- End Add News Item --//

//-- Start Edit News Item --//
if ($action == 'edit')
{
    $newsid = isset($_GET['newsid']) ? (int) $_GET['newsid'] : 0;

    if (!is_valid_id($newsid))
    {
        error_message_center("error",
                      "{$lang['gbl_adm_error']}",
                      "<strong>{$lang['text_inv_id2']}</strong><br />
                      <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=6'>{$lang['title_news']}</a>
                      <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                      <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    }

    $res = @$db->query("SELECT *
                        FROM news
                        WHERE id = $newsid") or sqlerr(__FILE__, __LINE__);

    if ($res->num_rows != 1)
    {
        error_message_center("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_no_id']}");
    }

    $arr = $res->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $body = isset($_POST['body']) ? $_POST['body'] : '';

        if ($body == '')
        {
            error_message_center("error",
                          "{$lang['gbl_adm_error']}",
                          "<strong>{$lang['err_empty_body']}</strong><br />
                          <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=6'>{$lang['title_news']}</a>
                          <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                          <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }

        $body     = sqlesc($body);
        $editedat = sqlesc(get_date_time());

        @$db->query("UPDATE news
                     SET body = $body
                     WHERE id = $newsid") or sqlerr(__FILE__, __LINE__);

        $Memcache->delete_value('current::news');

        $warning = error_message_center("success",
                                 "{$lang['gbl_adm_success']}",
                                 "<strong>{$lang['text_news_edited']}</strong><br />
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=6'>{$lang['title_news']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    }
    else
    {
        site_header("{$lang['title_news']}", false);
        echo("<h1>{$lang['text_edit_news']}</h1>\n");
        echo("<form name='ednews' method='post' action='controlpanel.php?fileaction=6&amp;action=edit&amp;newsid=$newsid'>\n");
        echo("<table border='1' width='100%' cellspacing='0' cellpadding='5'>\n");
        echo("<tr><td class='std'><input type='hidden' name='returnto' value='$returnto' /></td></tr>\n");
        echo("<tr><td class='std' style='padding : 0px'>" . textbbcode("ednews", "body", security::html_safe($arr['body'])) . "</td></tr>\n");
        echo("<tr><td class='std' align='center'><input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' /></td></tr>\n");
        echo("</table>\n");
        echo("</form>\n");
        echo("<br />");
        site_footer();
        die;
    }
}
//-- End Edit News Item --//

//-- Start Display News Form --//
site_header("{$lang['title_news']}", false);

echo("<h1>{$lang['text_submit']}</h1>\n");

if ($warning)
{
    echo("<p><span style='font-size : small;'>($warning)</span></p>");
}

echo("<form name='news' method='post' action='controlpanel.php?fileaction=6&amp;action=add'>\n");
echo("<table border='1' width='100%' cellspacing='0' cellpadding='5'>\n");
echo("<tr><td class='std' style='padding : 10px'>" . textbbcode("news", "body", security::html_safe($arr['body'])) . "\n");
echo("<br /><br /><div align='center'><input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' /></div></td></tr>\n");
echo("</table></form><br /><br />\n");

$res = @$db->query("SELECT *
                    FROM news
                    ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);

if ($res->num_rows > 0)
{
    begin_frame();

    while ($arr = $res->fetch_assoc())
    {
        $newsid = (int)$arr['id'];
        $body   = format_comment($arr['body']);
        $userid = (int)$arr['userid'];
        $added  = $arr['added'] . "{$lang['text_gmt']}(" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr['added']))) . "{$lang['text_ago']})";

        $res2 = @$db->query("SELECT username, donor
                             FROM users
                             WHERE id = $userid") or sqlerr(__FILE__, __LINE__);

        $arr2       = $res2->fetch_assoc();
        $postername = security::html_safe($arr2['username']);

        if ($postername == "")
        {
            $by = "unknown[$userid]";
        }
        else
        {
            $by = "<a href='userdetails.php?id=$userid'><span style='font-weight : bold;'>$postername</span></a>" . ($arr2['donor'] == "yes" ? "<img src='{$image_dir}star.png' width='16' height='16' border='0' alt='Donor' title='Donor' />" : "");
        }

        echo("<table border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>");
        echo("$added&nbsp;---&nbsp;by&nbsp;$by");
        echo("&nbsp;&nbsp;<a class='btn' href='controlpanel.php?fileaction=6&amp;action=edit&amp;newsid=$newsid'><span style='font-weight : bold;'>{$lang['btn_edit']}</span></a>");
        echo("&nbsp;&nbsp;<a class='btn' href='controlpanel.php?fileaction=6&amp;action=delete&amp;newsid=$newsid'><span style='font-weight : bold;'>{$lang['btn_delete']}</span></a>");
        echo("</td></tr></table>\n");

        begin_table(true);
            echo("<tr valign='top'><td class='comment'>$body</td></tr>\n");
        end_table();
    }
    end_frame();
    echo("<br />");
}
else
{
    error_message_center("info",
                         "{$lang['gbl_adm_sorry']}",
                         "{$lang['err_no_news']}");
}
//-- End Display News Form --//

site_footer();
die;

?>