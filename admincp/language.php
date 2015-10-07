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

$lang = array_merge(load_language('adm_language'),
                    load_language('adm_global'));

$vactg   = array('delete',
                 'edit',
                 '');

$actiong = (isset($_GET['action']) ? $_GET['action'] : '');

if (!in_array($actiong, $vactg))
{
    error_message_center("error",
                         "{$lang['gbl_adm_error']}",
                         "{$lang['err_invalid']}");
}

if (($actiong == 'edit' || $actiong == 'delete') && $_GET['lang_id'] == 0)
{
    error_message_center("error",
                         "{$lang['gbl_adm_error']}",
                         "{$lang['err_miss_arg']}");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $vaction = array('edit',
                     'add',
                     'delete');

    $action = ((isset($_POST['action']) && in_array($_POST['action'], $vaction)) ? $_POST['action'] : '');

    if (!$action)
    {
        error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_missing']}");
    }

    //-- Start Add A New Language --//
    if ($action == 'add')
    {
        $name = htmlentities($_POST['lang_name']);

        if (empty($name))
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_miss_name']}");
        }

        $add = $db->query("INSERT INTO languages (name)
                           VALUES (" . sqlesc($name) . ") ") or sqlerr(__FILE__, __LINE__);

        if ($add)
        {
            write_stafflog(user::$current['username'] . "{$lang['stafflog_added_new']}$name");

            error_message_center("success",
                                 "{$lang['gbl_adm_success']}",
                                 "<strong>{$lang['text_added']}</strong><br />
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=30'>{$lang['text_return_lang']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                 <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }
    }
    //-- End Add A New Language --//

    //-- Start Edit A Language --//
    if ($action == 'edit')
    {
        $lang_id        = (isset($_POST['lang_id']) ? intval(0 + $_POST['lang_id']) : '');
        $lang_name_edit = htmlentities($_POST['lang_name_edit']);

        if (empty($lang_name_edit))
        {
            error_message("error",
                          "{$lang['gbl_adm_error']}",
                          "{$lang['err_miss_name']}");
        }

        $edit = $db->query("UPDATE languages
                            SET name = " . sqlesc($lang_name_edit) . "
                            WHERE id = " . sqlesc($lang_id) . " ") or sqlerr(__FILE__, __LINE__);

        if ($edit)
        {
             error_message_center("success",
                                  "{$lang['gbl_adm_success']}",
                                  "<strong>{$lang['text_edited']}</strong><br />
                                  <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=30'>{$lang['text_return_lang']}</a>
                                  <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                  <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }
    }
    //-- End Edit A Language --//
}

//-- Start Edit Language Form -//
if ($actiong == 'edit')
{
    $langid = (isset($_GET['lang_id']) ? intval(0 + $_GET['lang_id']) : '');

    site_header("", false);

    $res = $db->query("SELECT id, name
                       FROM languages
                       WHERE id = " . sqlesc($langid) . "
                       LIMIT 1 ") or sqlerr(__FILE__, __LINE__);

    $arr       = $res->fetch_assoc();
    $lang_name = htmlentities($arr['name']);

    write_stafflog(user::$current['username'] . "{$lang['stafflog_edited_language']}{$arr['name']}");

    print("<h1>{$lang['text_edit_language']}</h1>");

    print("<form method='post' action='controlpanel.php?fileaction=30'>");
    print("<table class='main' border='1' align='center' cellspacing='0' cellpadding='5'>");

    print("<tr>
                <td class='colhead'>
                    <label for='lang_name_edit'>{$lang['text_lang_name']}</label>
                </td>
                <td class='rowhead' align='left'>
                    <input type='text' name='lang_name_edit' id='lang_name_edit' size='50' value='$lang_name' />
                </td>
            </tr>");

    print("<tr>
                <td class='std' align='center' colspan='2'>
                    <input type='submit' class='btn' name='submit' value='{$lang['btn_edit']} '/>
                    <input type='hidden' name='action' value='edit' />
                    <input type='hidden' name='lang_id' value='{$arr['id']}' />
                </td>
            </tr>");

    print("</table>");
    print("</form>");

    site_footer();

}
//-- Finish Edit Language Form --//

//-- Start Delete Language --//
elseif ($actiong == 'delete')
{
    $langid = (isset($_GET['lang_id']) ? intval(0 + $_GET['lang_id']) : '');

    $res = $db->query("SELECT id, name
                       FROM languages
                       WHERE id = " . sqlesc($langid) . "") or sqlerr(__FILE__, __LINE__);

    $arr      = $res->fetch_assoc();
    $count    = $res->num_rows;
    $returnto = isset($_GET['returnto']) ? htmlentities($_GET['returnto']) : '';
    $sure     = isset($_GET['sure']) ? (int) $_GET['sure'] : 0;

    if (!$sure)
    {
        error_message_center("warn",
                             "{$lang['gbl_adm_warn']}",
                             "{$lang['text_del_sure']}<strong>{$arr['name']}</strong> <br />
                             <br /><a class='btn' href='controlpanel.php?fileaction=30&amp;action=delete&amp;lang_id={$arr['id']};returnto=$returnto&amp;sure=1'>{$lang['text_delete']}</a>");
    }

    if ($count == 1)
    {
        $delete = $db->query("DELETE
                              FROM languages
                              WHERE id = " . sqlesc($langid) . "") or sqlerr(__FILE__, __LINE__);

        if ($delete)
        {
            write_stafflog(user::$current['username'] . "{$lang['stafflog_deleted']}{$arr['name']}");

               error_message_center("success",
                                    "{$lang['gbl_adm_success']}",
                                    "<strong>{$lang['text_deleted_success']}</strong><br />
                                    <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=30'>{$lang['text_return_lang']}</a>
                                    <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                                    <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        }
    }
    else
    {
        error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_no_id']}");
    }
}
//-- Start Delete Language --//

//-- Start Add A New Language --//
else
{
    site_header("", false);

    print("<h1>{$lang['text_add_language']}</h1>");

    print("<form method='post' action='controlpanel.php?fileaction=30'>");
    print("<table class='main' border='1' align='center' cellspacing='0' cellpadding='5'>");

    print("<tr>
                <td class='colhead'>
                    <label for='lang_name'>{$lang['text_lang_name']}</label>
                </td>
                <td class='rowhead' align='left'>
                    <input type='text' name='lang_name' id='lang_name' size='50' />
                </td>
            </tr>");

    print("<tr>
                <td align='center' colspan='2'>
                    <input type='submit' class='btn' name='submit' value='{$lang['btn_add']}' />
                    <input type='hidden' name='action' value='add' />
                </td>
           </tr>");

    print("</table>");
    print("</form>");
	//-- Finish Add A New Language --//

	//-- Start Existing Languages --//
    print("<h1>{$lang['text_ex_language']}</h1>");

    $res = $db->query("SELECT id, name
                       FROM languages
                       ORDER BY id ASC") or sqlerr(__FILE__, __LINE__);

    $count = $res->num_rows;

    if ($count > 0)
    {
        print("<table class='main' border='1' align='center' cellspacing='0' cellpadding='5'>");
        print("<tr>
                    <td class='colhead'>{$lang['text_lang_name']}</td>
                    <td class='colhead' align='center' colspan='2'>{$lang['text_action']}</td>
                </tr>");

        while ($arr = $res->fetch_assoc())
        {
            $edit = "<a href='controlpanel.php?fileaction=30&amp;action=edit&amp;lang_id={$arr['id']}'><img src='{$image_dir}edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' style='border : none; padding : 3px;' /></a>";

            $delete = "<a href='controlpanel.php?fileaction=30&amp;action=delete&amp;lang_id={$arr['id']}'><img src='{$image_dir}delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' style='border : none; padding : 3px;' /></a>";

            print("<tr>
                        <td class='rowhead' align='center'>" . security::html_safe($arr['name']) . "</td>
                        <td class='rowhead' align='center'>$edit</td>
                        <td class='rowhead' align='center'>$delete</td>
                    </tr>");
        }

        print("</table>");
    }
    else
    {
        display_message_center("info",
                               "{$lang['gbl_adm_info']}",
                               "{$lang['text_no_language']}");
    }
	//-- Finish Existing Languages --//

    site_footer();
}

?>