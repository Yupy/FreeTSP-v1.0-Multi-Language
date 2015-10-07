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

$lang = array_merge(load_language('adm_credit_mods'),
                    load_language('adm_global'));

$posted_action = strip_tags((isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '')));

//-- Add All Possible Actions Here And Check Them To Be Sure They Are Ok --//
$valid_actions = array('add_new_credit',
                       'edit_credit',
                       'update_credit',
                       'delete_credit',
                       'delete_credit_yes');

//-- Check Posted Action, And If No Action Was Posted, Show The Default Page --//
$action = (in_array(
                    $posted_action,
                    $valid_actions) ? $posted_action : 'default');

switch ($action)
{
    //-- Start Add New Credit --//
    case 'add_new_credit';
        $name        = ($_POST['name']);
        $description = ($_POST['description']);
        $category    = ($_POST['category']);
        $link        = ($_POST['link']);
        $status      = ($_POST['status']);
        $credit      = ($_POST['credit']);
        $modified    = ($_POST['modified']);

        if (!$name)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_no_name']}");
        }

        if (!$description)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_no_desc']}");
        }

        if (!$link)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_no_link']}");
        }

        if (!$credit)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_no_orig']}");
        }

        if (!$modified)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_no_modif']}");
        }

        $db->query("INSERT INTO modscredits (name, description,  category,  mod_link,  status, credit, modified)
                    VALUES(" . sqlesc($name) . ", " . sqlesc($description) . ", " . sqlesc($category) . ", " . sqlesc($link) . ", " . sqlesc($status) . ", " . sqlesc($credit) . ", " . sqlesc($modified) . ")") or sqlerr(__FILE__, __LINE__);

        display_message_center("success",
                               "{$lang['gbl_adm_success']}",
                               "{$lang['text_mod_created']}<a href='credits.php'>{$lang['text_here']}</a><br />
                               <br /> {$lang['gbl_adm_return_to']}{$lang['text_credits']}<a href='controlpanel.php?fileaction=29&amp;action=default'>{$lang['text_mod_add']}</a>
                               <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");

        site_footer();
        die();
    break;

    //-- Start Edit A Credit --//
    case 'edit_credit';
        $id = (int)$_GET['id'];

        $res = $db->query("SELECT name, description, category, mod_link, status, credit, modified
                           FROM modscredits
                           WHERE id = $id") or sqlerr(__FILE__, __LINE__);

        if ($res->num_rows == 0)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_inv_id']}");
        }

        while($mod = $res->fetch_assoc())
        {
            print("<form method='post' action='controlpanel.php?fileaction=29&amp;action=update_credit&amp;id=$id'>
                        <input type='hidden' name='action' value='add' />");

            print("<table border='1' align='center' width='81%' cellpadding='8' cellspacing='0'>");

            print("<tr>
                    <td class='colhead' align='center' colspan='2'>{$lang[title_edit_credit]}</td>
                </tr>");

            print("<tr>
                    <td class='colhead'>{$lang['form_name']}</td>
                    <td class='rowhead'>
                        <input type='text' name='name' size='120' maxlength='120' value='" . security::html_safe($mod['name']) . "' />
                    </td>
                </tr>");

            print("<tr>
                    <td class='colhead'>{$lang['form_desc']}</td>
                    <td class='rowhead'>
                        <input type='text' name='description' size='120' maxlength='120' value='" . security::html_safe($mod['description']) . "' />
                    </td>
                </tr>");

            print("<tr>
                    <td class='colhead'>{$lang['form_category']}</td>
                    <td class='rowhead'>
                        <select name='modcategory'>");

            $result = $db->query("SHOW COLUMNS
                                  FROM modscredits
                                  WHERE field = 'category'");

            while ($row = $result->fetch_row())
            {
                foreach(explode("','", substr($row[1], 6, -2)) AS $y)
                {
                    print("<option value='$y'" . ($mod['category'] == $y ? " selected='selected' " : "") . ">$y</option>");
                }
            }

            print("</select>
                    </td>
                    </tr>");

            print("<tr>
                    <td class='colhead'>{$lang['form_link']}</td>
                    <td class='rowhead'>
                        <input type='text' name='link' size='120' maxlength='120' value='" . security::html_safe($mod['mod_link']) . "' />
                    </td>
                </tr>");

            print("<tr>
                    <td class='colhead'>{$lang['form_status']}</td>
                    <td class='rowhead'>
                        <select name='modstatus'>");

            $result = $db->query("SHOW COLUMNS
                                  FROM modscredits
                                  WHERE field = 'status'");

            while ($row = $result->fetch_row())
            {
                foreach(explode("','", substr($row[1], 6, -2)) AS $y)
                {
                    print("<option value='$y'" . ($mod['status'] == $y ? " selected='selected' " : "") . ">$y</option>");
                    }
            }

            print("</select>
                     </td>
                     </tr>");

            print("<tr>
                    <td class='colhead'>{$lang['form_orig']}</td>
                    <td class='rowhead'>
                        <input type='text' name='credits' size='120' maxlength='120' value='" . security::html_safe($mod['credit']) . "' /><br />
                        <font class='small'>{$lang['form_comma']}</font>
                    </td>
                </tr>");

            print("<tr>
                    <td class='colhead'>{$lang['form_modify']}</td>
                    <td class='rowhead'>
                        <input type='text' name='modified' size='120' maxlength='120' value='" . security::html_safe($mod['modified']) . "' /><br />
                        <font class='small'>{$lang['form_comma']}</font>
                    </td>
                </tr>");

            print("<tr>
                    <td class='rowhead' align='center' colspan='2'>
                        <input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' />
                    </td>
                </tr>");

            print("</table>");
            print("</form>");
        }
    break;

    //-- Start Update A Credit --//
    case 'update_credit';
        $id = (int)$_GET['id'];

        if (!is_valid_id($id))
        {
             error_message_center("error",
                                  "{$lang['gbl_adm_error']}",
                                  "{$lang['err_inv_id']}");
        }

        $res = $db->query('SELECT id
                           FROM modscredits
                           WHERE id = ' . sqlesc($id));

        if ($res->num_rows == 0)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_mod_id']}");
        }

        $name        = $_POST['name'];
        $description = $_POST['description'];
        $modcategory = $_POST['modcategory'];
        $link        = $_POST['link'];
        $modstatus   = $_POST['modstatus'];
        $credit      = $_POST['credits'];
        $modified    = $_POST['modified'];

        if (!$name)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_no_name']}");
        }

        if (!$description)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_no_desc']}");
        }

        if (!$link)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_no_link']}");
        }

        if (!$credit)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_no_orig']}");
        }

        if (!$modified)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_no_modif']}");
        }

        $db->query("UPDATE modscredits
                    SET name = " . sqlesc($name) . ", category = " . sqlesc($modcategory) . ", status = " . sqlesc($modstatus) . ",  mod_link = " . sqlesc($link) . ", credit = " . sqlesc($credit) . ", modified = " . sqlesc($modified) . ", description = " . sqlesc($description) . "
                    WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);

        display_message_center("success",
                               "{$lang['gbl_adm_success']}",
                               "{$lang['text_mod_update']}<a href='credits.php'>{$lang['text_here']}</a><br />
                               <br /> {$lang['gbl_adm_return_to']}{$lang['text_credits']}<a href='controlpanel.php?fileaction=29&amp;action=default'>{$lang['text_mod_add']}</a>
                               <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
        //exit();
    break;

    //-- Start Delete A Credit --//
    case 'delete_credit';
        $id = (int)$_GET['id'];

        if (!is_valid_id($id))
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_inv_id']}");
        }

        $res = $db->query('SELECT id, name
                           FROM modscredits
                           WHERE id = ' . sqlesc($id));

        if ($res->num_rows == 0)
        {
            error_message_center("error",
                                 "{$lang['gbl_adm_error']}",
                                 "{$lang['err_mod_id']}");
        }

        while ($arr = $res->fetch_assoc())
        {
            if (is_valid_id($id))
            {
                error_message_center("warn",
                                     "{$lang['gbl_adm_sanity']}",
                                     "{$lang['text_del_sure']}&nbsp;{$arr['name']}!<br /><br />
                                     <a class='btn' href='controlpanel.php?fileaction=29&amp;action=delete_credit_yes&amp;id=$id'>{$lang['btn_del_yes']}</a>&nbsp;&nbsp;/&nbsp;&nbsp;
                                     <a class='btn' href='credits.php'>{$lang['btn_del_no']}</a>");
            }
        }
    break;

    case 'delete_credit_yes';
        $id = (int)$_GET['id'];

        $db->query("DELETE
                    FROM modscredits
                    WHERE id = '$id'") or sqlerr(__FILE__, __LINE__);

        error_message_center("success",
                             "{$lang['gbl_adm_success']}",
                             "{$lang['text_deleted']}<br />
                             <br /> {$lang['gbl_adm_return_to']}<a href='credits.php'>{$lang['text_credits1']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    break;

    //-- Start Default Page --//
    case 'default';
        print("<form method='post' action='controlpanel.php?fileaction=29&amp;action=add_new_credit'>
                    <input type='hidden' name='action' value='add' />");

        print("<table border='1' align='center' width='81%' cellpadding='8' cellspacing='0'>");

        print("<tr>
                <td class='colhead' align='center' colspan='2'>{$lang['title_add_credits']}</td>
            </tr>");

        print("<tr>
                <td class='colhead'>{$lang['form_name']}</td>
                <td class='rowhead'>
                    <input type='text' name='name' size='120' />
                </td>
            </tr>");

        print("<tr>
                <td class='colhead'>{$lang['form_desc']}</td>
                <td class='rowhead'>
                    <input type='text' name='description' size='120' />
                </td>
            </tr>");

        print("<tr>
                <td class='colhead'>{$lang['form_category']}</td>
                <td class='rowhead'>
                    <select name='category'>
                        <option value='Addon'>{$lang['form_cat_addon']}</option>
                        <option value='Forum'>{$lang['form_cat_forum']}</option>
                        <option value='Message/Email'>{$lang['form_cat_pm']}</option>
                        <option value='Display/Style'>{$lang['form_cat_style']}</option>
                        <option value='Staff/Tools'>{$lang['form_cat_staff']}</option>
                        <option value='Browse/Torrent/Details'>{$lang['form_cat_browse']}</option>
                        <option value='Misc'>{$lang['form_cat_misc']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td class='colhead'>{$lang['form_link']}</td>
                <td class='rowhead'>
                    <input type='text' name='link' size='120' />
                </td>
            </tr>");

        print("<tr>
                <td class='colhead'>{$lang['form_status']}</td>
                <td class='rowhead'>
                    <select name='status'>
                        <option value='In-Progress'>{$lang['form_progress']}</option>
                        <option value='Completed'>{$lang['form_complete']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td class='colhead'>{$lang['form_orig']}</td>
                <td>
                    <input type='text' name='credit' size='120' /><br />
                    <font class='small'>{$lang['form_comma']}</font>
                </td>
            </tr>");

        print("<tr>
                <td class='colhead'>{$lang['form_modify']}</td>
                <td>
                    <input type='text' name='modified' size='120' /><br />
                    <font class='small'>{$lang['form_comma']}</font>
                </td>
            </tr>");

        print("<tr>
                <td class='rowhead' align='center' colspan='2'>
                    <input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' />
                </td>
            </tr>");

        print("</table>");
        print("</form>");
    break;

} //-- End All Actions --//
?>