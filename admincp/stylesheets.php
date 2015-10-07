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

$lang = array_merge(load_language('adm_stylesheets'),
                    load_language('adm_global'));

$vactg   = array("delete",
                 "edit",
                 "statusa",
                 "statusd",
                 "");

$actiong = (isset($_GET['action']) ? $_GET['action'] : '');

if (!in_array($actiong, $vactg))
{
    error_message_center("error",
                  "{$lang['gbl_adm_error']}",
                  "{$lang['err_invalid_action']}");
}

if (($actiong == 'edit' || $actiong == 'delete' || $actiong == 'statusa' || $actiong == 'statusd' ) && $_GET['sid'] == 0)
{
    error_message_center("error",
                  "{$lang['gbl_adm_error']}",
                  "{$lang['err_miss_arg']}");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $vaction = array("edit",
                     "add",
                     "delete",
                     "statusa",
                     "statusd");

    $action = ((isset($_POST['action']) && in_array($_POST['action'], $vaction)) ? $_POST['action'] : '');

    if (!$action)
    {
        error_message_center("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_missing']}");
    }

    //-- Start Add Stylesheet --//
    if ($action == 'add')
    {
        $name = htmlentities($_POST['sname']);

        if (empty($name))
        {
            error_message_center("error",
                          "{$lang['gbl_adm_error']}",
                          "{$lang['err_missing_name']}");
        }

        $uri = htmlentities($_POST['suri']);

        if (empty($uri))
        {
            error_message_center("error",
                          "{$lang['gbl_adm_error']}",
                          "{$lang['err_missing_css']}");
        }

        $add = $db->query("INSERT INTO stylesheets (name ,uri)
                           VALUES (" . sqlesc($name) . ", " . sqlesc($uri) . ") ") or sqlerr(__FILE__, __LINE__);

        if ($add)
        {
            write_stafflog(user::$current['username'] . "{$lang['stafflog_style_added']}" . $name);

            error_message_center("success",
                                 "{$lang['gbl_adm_success']}",
                                 "<strong>{$lang['text_style_created']}</strong><br />
                                 <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=7'>{$lang['text_stylesheets']}</a>
                                 <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br />{$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }
    }
    //-- End Add Stylesheet --//

    //-- Start Edit Stylesheet --//
    if ($action == "edit")
    {
        $sid        = (isset($_POST['sid']) ? intval(0 + $_POST['sid']) : "");
        $sname_edit = htmlentities($_POST['sname_edit']);

        if (empty($sname_edit))
        {
            error_message_center("error",
                          "{$lang['gbl_adm_error']}",
                          "{$lang['err_missing_name']}");
        }

        $suri_edit = htmlentities($_POST['suri_edit']);

        if (empty($suri_edit))
        {
            error_message_center("error",
                          "{$lang['gbl_adm_error']}",
                          "{$lang['err_missing_css']}");
        }

        $edit = $db->query("UPDATE stylesheets
                            SET name = " . sqlesc($sname_edit) . ", uri=" . sqlesc($suri_edit) . "
                            WHERE id = " . sqlesc($sid) . " ") or sqlerr(__FILE__, __LINE__);
		
		$Memcache->delete_value('themes::preview::' . $sid);

        if ($edit)
        {
            error_message_center("success",
                                 "{$lang['gbl_adm_success']}",
                                 "<strong>{$lang['text_style_edited']}</strong><br />
                                 <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=7'>{$lang['text_stylesheets']}</a>
                                 <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br />{$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }
    }
    //-- End Edit Stylesheet --//
}

//-- Start Edit Stylesheet Form --//
if ($actiong == 'edit')
{
    $styid = (isset($_GET['sid']) ? intval(0 + $_GET['sid']) : '');

    site_header("{$lang['title_edit_style']}", false);

    $res = $db->query("SELECT id, name, uri
                       FROM stylesheets
                       WHERE id = " . sqlesc($styid) . "
                       LIMIT 1 ") or sqlerr(__FILE__, __LINE__);

    $arr   = $res->fetch_assoc();
    $sname = htmlentities($arr['name']);
    $suri  = htmlentities($arr['uri']);

    write_stafflog(user::$current['username'] . "{$lang['stafflog_style_edited']}" . $arr['name']);

    begin_frame($lang['title_edit_style']);

    print("<form method='post' action='controlpanel.php?fileaction=7'>");
    print("<table class='main' border='1' align='center' cellspacing='0' cellpadding='5'>");

    print("<tr>
            <td class='colhead'>
                <label for='sname_edit'>{$lang['form_style_name']}</label>
            </td>
            <td class='rowhead' align='left'>
                <input type='text' name='sname_edit' size='50' id='sname_edit' value='$sname' onclick=\"select()\" />
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>
                <label for='suri_edit'>{$lang['form_style_css']}</label>
            </td>
            <td class='rowhead' align='left'>
                <input type='text' name='suri_edit' id='suri_edit' size='50' value='$suri' onclick=\"select()\" />
            </td>
        </tr>");

    print("<tr>
            <td class='std' align='center' colspan='2'>
                <input type='submit' class='btn' name='submit' value='{$lang['btn_edit_style']}' />
                <input type='hidden' name='action' value='edit' />
                <input type='hidden' name='sid' value='" . (int)$arr['id'] . "' />
            </td>
        </tr>");

    print("</table>");
    print("</form>");

    end_frame();
    site_footer();

}
//-- Start Delete Existing Stylesheet --//
elseif ($actiong == 'delete')
{
    $styid = (isset($_GET['sid']) ? intval(0 + $_GET['sid']) : '');

    $res = $db->query("SELECT id, name
                       FROM stylesheets
                       WHERE id = " . sqlesc($styid)) or sqlerr(__FILE__, __LINE__);

    $arr      = $res->fetch_assoc();
    $count    = $res->num_rows;
    $returnto = isset($_GET['returnto']) ? htmlentities($_GET['returnto']) : '';
    $sure     = isset($_GET['sure']) ? (int) $_GET['sure'] : 0;

    if (!$sure)
    {
        error_message_center("warn",
                             "{$lang['gbl_adm_warning']}",
                             "{$lang['text_del_sure']}<br /><br />
                             <a class='btn' href='controlpanel.php?fileaction=7&amp;action=delete&amp;sid={$arr['id']};returnto=$returnto&amp;sure=1'>{$lang['btn_confirm']}</a>");
    }

    if ($count == 1)
    {
        $delete = $db->query("DELETE
                             FROM stylesheets
                             WHERE id = " . sqlesc($styid)) or sqlerr(__FILE__, __LINE__);
		
		$Memcache->delete_value('themes::preview::' . $styid);

        if ($delete)
        {
            write_stafflog(user::$current['username'] . "{$lang['stafflog_style_del']}" . $arr['name']);

            error_message_center("success",
                                 "{$lang['gbl_adm_success']}",
                                 "<strong>{$lang['text_style_deleted']}</strong><br />
                                 <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=7'>{$lang['text_stylesheets']}</a>
                                 <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br />{$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }
    }
    else
    {
        error_message("error",
                      "{$lang[gbl_adm_error]}",
                      "{$lang['err_no_style_id']}");
    }

    }
    elseif ($actiong == 'statusa')
    {
    $styid = (isset($_GET['sid']) ? intval(0 + $_GET['sid']) : '');

    $res = $db->query("SELECT id, name
                       FROM stylesheets
                       WHERE id = " . sqlesc($styid)) or sqlerr(__FILE__, __LINE__);

    $arr      = $res->fetch_assoc();
    $count    = $res->num_rows;
    $returnto = isset($_GET['returnto']) ? htmlentities($_GET['returnto']) : '';
    $sure     = isset($_GET['sure']) ? (int) $_GET['sure'] : 0;

    $check = $arr['active'];

    if ($check == 'yes')
    {
        error_message("error",
                      "{$lang[gbl_adm_error]}",
                      "{$lang['err_style_active']}");
    }
    if (!$sure)
    {
        error_message_center("warn",
                             "{$lang['gbl_adm_warning']}",
                             "{$lang['btn_activate_style']}<br /><br />
                             <a class='btn' href='controlpanel.php?fileaction=7&amp;action=statusa&amp;sid={$arr['id']};returnto=$returnto&amp;sure=1'>{$lang['btn_confirm']}</a>");
    }

    if ($count == 1)
    {
        $activate = $db->query("UPDATE stylesheets
                                SET active = 'yes'
                                WHERE id = " . sqlesc($arr['id'])) or sqlerr(__FILE__, __LINE__);

        if ($activate)
        {
            write_stafflog(user::$current['username'] . "{$lang['stafflog_activated']}" . $arr['name']);

            error_message_center("success",
                                 "{$lang['gbl_adm_success']}",
                                 "<strong>{$lang['text_style_active']}</strong><br />
                                 <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=7'>{$lang['text_stylesheets']}</a>
                                 <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br />{$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }
    }
    else
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_no_style_id']}");
    }

}
elseif ($actiong == 'statusd')
{
    $styid = (isset($_GET['sid']) ? intval(0 + $_GET['sid']) : '');

    $res = $db->query("SELECT id, name
                       FROM stylesheets
                       WHERE id = " . sqlesc($styid)) or sqlerr(__FILE__, __LINE__);

    $arr      = $res->fetch_assoc();
    $count    = $res->num_rows;
    $returnto = isset($_GET['returnto']) ? htmlentities($_GET['returnto']) : '';
    $sure     = isset($_GET['sure']) ? (int) $_GET['sure'] : 0;

    $check = $arr['active'];

    if ($arr['id'] == '1')
    {
        error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_style_default']}");
    }

    if ($check == 'no')
    {
        error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_style_deactive']}");
    }

    if (!$sure)
    {
        error_message_center("warn",
                             "{$lang[gbl_adm_warn]}",
                             "{$lang['btn_deactivate_style']}<br /><br />
                             <a class='btn' href='controlpanel.php?fileaction=7&amp;action=statusd&amp;sid={$arr['id']};returnto=$returnto&amp;sure=1'>{$lang['btn_confirm']}</a>");
    }

    if ($count == 1)
    {
        $activate = $db->query("UPDATE stylesheets
                                SET active = 'no'
                                WHERE id = " . sqlesc($arr['id'])) or sqlerr(__FILE__, __LINE__);

        if ($activate)
        {
            write_stafflog(user::$current['username'] . "{$lang['stafflog_deactivated']}" . $arr['name']);

            error_message_center("success",
                                 "{$lang['gbl_adm_success']}",
                                 "<strong>{$lang['test_style_deactive']}</strong><br />
                                 <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=7'>{$lang['text_stylesheets']}</a>
                                 <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br />{$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }
    }
    else
    {
        error_message_center("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_no_style_id']}");
    }
}
//-- End Delete Existing Stylesheet --//
else
{
    site_header("{$lang['title_stylesheets']}", false);

    //-- Start Add Stylesheet Form --//
    begin_frame($lang['title_add_style']);

    print("<form method='post' action='controlpanel.php?fileaction=7'>");
    print("<table class='main' border='1' align='center' cellspacing='0' cellpadding='5'>");

    print("<tr>
            <td class='colhead'>
                <label for='suri'>{$lang['form_style_css']}</label>
            </td>
            <td class='rowhead' align='left'>
                <input type='text' name='suri' id='suri' size='50' />
            </td>
        </tr>");

    print("<tr>
            <td class='colhead'>
                <label for='sname'>{$lang['form_style_name']}</label>
            </td>
            <td class='rowhead' align='left'>
                <input type='text' name='sname' id='sname' size='50' />
            </td>
        </tr>");

    print("<tr>
            <td align='center' colspan='2'>
                <input type='submit' class='btn' name='submit' value='{$lang['btn_add_style']}' />
                <input type='hidden' name='action' value='add' />
            </td>
        </tr>");

    print("</table>");
    print("</form>");

    end_frame();
    //-- End Add Stylesheet Form --//

    //-- Start Display Existing Stylesheets --//
    begin_frame($lang['title_existing_styles']);

    $res = $db->query("SELECT id, uri, name, active
                       FROM stylesheets
                       ORDER BY id ASC") or sqlerr(__FILE__, __LINE__);

    $count = $res->num_rows;

    if ($count > 0)
    {
        print("<table class='main' border='1' align='center' cellspacing='0' cellpadding='5'>");
        print("<tr>");
        print("<td class='colhead'>{$lang['table_style_id']}</td>");
        print("<td class='colhead'>{$lang['table_style_css']}</td>");
        print("<td class='colhead'>{$lang['table_style_name']}</td>");
        print("<td class='colhead' align='center' colspan='4'>{$lang['table_style_action']}</td>");
        print("</tr>");

        while ($arr = $res->fetch_assoc())
        {
            $edit = "<a href='controlpanel.php?fileaction=7&amp;action=edit&amp;sid={$arr['id']}'><img src='{$image_dir}admin/edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit_style']}' title='{$lang['img_alt_edit_style']}'style='border : none;padding : 3px;' /></a>";

            $delete = "<a href='controlpanel.php?fileaction=7&amp;action=delete&amp;sid={$arr['id']}'><img src='{$image_dir}admin/delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_del_style']}' title='{$lang['img_alt_del_style']}' style='border : none; padding : 3px;' /></a>";

            if($arr['active'] == 'yes')
            {
            	$status = "<a href='controlpanel.php?fileaction=7&amp;action=statusd&amp;sid={$arr['id']}'><img src='{$image_dir}admin/active2.png' width='16' height='16' border='0' alt='{$lang['img_alt_deactive_style']}' title='{$lang['img_alt_deactive_style']}' style='border : none; padding : 3px;' /></a>";
            }
            else
            {
                $status = "<a href='controlpanel.php?fileaction=7&amp;action=statusa&amp;sid={$arr['id']}'><img src='{$image_dir}admin/deactive2.png' width='16' height='16' border='0' alt='{$lang['img_alt_active_style']}' title='{$lang['img_alt_active_style']}' style='border : none; padding : 3px;' /></a>";
            }

            $preview = "<a href='theme_preview.php?id={$arr['id']}'><img src='{$image_dir}admin/preview.png' width='16' height='16' border='0' alt='{$lang['img_alt_preview_style']}' title='{$lang['img_alt_preview_style']}' style='border : none; padding : 3px;' /></a>";

            print("<tr>");
            print("<td class='rowhead' align='center'>{$arr['id']}</td>");
            print("<td class='rowhead' align='center'>{$arr['uri']}</td>");
            print("<td class='rowhead' align='center'>" . security::html_safe($arr['name']) . "</td>");
            print("<td class='rowhead' align='center'>$edit</td>");
            print("<td class='rowhead' align='center'>$delete</td>");
			print("<td class='rowhead' align='center'>$status</td>");
            print("<td class='rowhead' align='center'>$preview</td>");
            print("</tr>");
        }

        print("</table>");
    }
    else
    {
        display_message("info",
                        "{$lang['gbl_adm_sorry']}",
                        "{$lang['text_no_styles']}");
    }

    end_frame();
    //-- End Display Existing Stylesheets --//

    site_footer();
}

?>