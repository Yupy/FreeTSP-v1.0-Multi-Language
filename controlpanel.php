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

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_bbcode.php');
require_once(ROOT_DIR . 'ofc/lib/open-flash-chart.php');
require_once(FUNC_DIR . 'function_pager_new.php');

db_connect();
logged_in();

$lang = array_merge(load_language('controlpanel'),
                    load_language('global'));

define("IN_FTSP_ADMIN", true);

if (get_user_class() < UC_MODERATOR)
{
	error_message("warn",
                  "{$lang['gbl_warning']}",
                  "{$lang['err_denied']}");
}

site_header("{$lang['title_admincp']}", false);

//-- Start Add New Tools --//
if (get_user_id() == UC_TRACKER_MANAGER)
{
    //$create = $_GET['create'];
	$create = isset($_GET['ceate']) ? $_GET['create'] : '';

    if ($create == 'true')
    {
        $mod_name   = $_GET['mod_name'];
        $mod_url    = $_GET['mod_url'];
        $mod_image  = $_GET['mod_image'];
        $mod_status = (int) $_GET['mod_status'];
        $max_class  = (int) $_GET['max_class'];

        $query = "INSERT INTO controlpanel
                  SET name = '$mod_name', url = '$mod_url', image = '$mod_image', status = '$mod_status', max_class = '$max_class'" or sqlerr(__FILE__, __LINE__);

        $sql = $db->query($query);

        if ($sql)
        {
            $success = true;
        }
        else
        {
            $success = false;
        }
    }

    if ($success == true)
    {
        error_message_center("success",
                             "{$lang['gbl_success']}",
                             "<strong>{$lang['text_file_added']}</strong><br />
                             <br /> {$lang['gbl_return_to']}<a href='controlpanel.php'>{$lang['gbl_return_admin']}</a>
                             <br /> {$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");

        site_footer();
        die();
    }
}
//-- Finish Add New Tools --//

//-- Start Remove And Edit Options For Tracker Manager --//
$trakman = $_GET['trakman'];

if ($trakman == 'yes')
{
    $deltrakmanid = (int) $_GET['deltrakmanid'];

    $query = "DELETE FROM controlpanel
              WHERE id = " . sqlesc($deltrakmanid) . "
              LIMIT 1" or sqlerr(__FILE__, __LINE__);

    $sql = $db->query($query);

    error_message_center("success",
                         "{$lang['gbl_success']}",
                         "<strong>{$lang['text_file_deleted']}</strong><br />
                         <br /> {$lang['gbl_return_to']}<a href='controlpanel.php'>{$lang['gbl_return_admin']}</a>
                         <br /> {$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");

    site_footer();
    die();
}

$deltrakmanid = (int) $_GET['deltrakmanid'];
$name         = $_GET['mod'];

if ($deltrakmanid > 0)
{
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "<strong>{$lang['text_del_sure']} $name?</strong><br />
                             <br /><a class='btn' href='controlpanel.php?deltrakmanid=$deltrakmanid&amp;mod=$name&amp;trakman=yes'>{$lang['text_del_yes']}</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a class='btn'  href='controlpanel.php'>{$lang['text_del_no']}</a>");
    }

    site_footer();
    die();
}

$edittrakman = $_GET['edittrakman'];

if ($edittrakman == 1)
{
    $id         = (int) $_GET['id'];
    $mod_name   = $_GET['mod_name'];
    $mod_url    = $_GET['mod_url'];
    $mod_image  = $_GET['mod_image'];
    $mod_status = (int) $_GET['mod_status'];
    $max_class  = (int) $_GET['max_class'];

    $query = "UPDATE controlpanel
              SET name = '$mod_name', url = '$mod_url', image = '$mod_image', status = '$mod_status', max_class = '$max_class'
              WHERE id = " . sqlesc($id) or sqlerr(__FILE__, __LINE__);

    $sql = $db->query($query);

    if ($sql)
    {
        if (get_user_id() == UC_TRACKER_MANAGER)
            {
                error_message_center("success",
                                     "{$lang['gbl_success']}",
                                     "<strong>{$lang['text_file_edited']}</strong><br />
                                     <br /> {$lang['gbl_return_to']}<a href='controlpanel.php'>{$lang['gbl_return_admin']}</a>
                                     <br /> {$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");
            }

        site_footer();
        die();
    }
}

$edittrakmanid = (int) $_GET['edittrakmanid'];
$name          = $_GET['name'];
$url           = $_GET['url'];
$image         = $_GET['image'];
$status        = (int) $_GET['status'];
$max_class     = (int) $_GET['max_class'];

if ($edittrakmanid > 0)
{
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        echo("<br />
            <form method='get' action='controlpanel.php'>
                <input type='hidden' name='edittrakman' value='1' />
                <input type='hidden' name='id' value='$edittrakmanid' />
                <table class='main' align='center' width='50%' cellspacing='0' cellpadding='5'>
                    <tr>
                        <td class='colhead'>
                            <label for='desc'>{$lang['form_desc']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_name' id='desc' size='50' value='$name' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='name'>{$lang['form_filename']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_url' id='name' size='50' value='$url' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='image'>{$lang['form_image']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_image' id='image' size='50' value='$image' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='active'>{$lang['form_active']}</label>
                        </td>
                        <td align='left'>
                            <select name='mod_status' id='active'>
                                <option value='1'>{$lang['form_yes']}</option>
                                <option value='0'>{$lang['form_no']}</option>
                            </select>
                            <input type='hidden' name='add' value='true' />
                        </td>
                    </tr>
                        <tr>
                        <td class='colhead'>
                            <label for='option'>{$lang['form_option']}</label>
                        </td>
                        <td align='left'>
                            <select name='max_class' id='option'>
                                <option value='7'>{$lang['form_manager']}</option>
                                <option value='4'>{$lang['form_moderator']}</option>
                                <option value='5'>{$lang['form_admin']}</option>
                                <option value='6'>{$lang['form_sysop']}</option>
                            </select>
                            <input type='hidden' name='add' value='true' />
                        </td>
                    </tr>
                    <tr>
                        <td class='std' align='center' colspan='2'>
                            <input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' />
                        </td>
                    </tr>
                </table>
            </form><br /><br />");
    }

    site_footer();
    die();
}
//-- Finish Remove And Edit Options For Tracker Manager --//

//-- Start Remove And Edit Options For Sysops --//
$sysop = $_GET['sysop'];

if ($sysop == 'yes')
{
    $delsysopid = (int) $_GET['delsysopid'];

    $query = "DELETE FROM controlpanel
              WHERE id = " . sqlesc($delsysopid) . "
              LIMIT 1" or sqlerr(__FILE__, __LINE__);

    $sql = $db->query($query);

    error_message_center("success",
                         "{$lang['gbl_success']}",
                         "<strong>{$lang['text_file_deleted']}</strong><br />
                         <br /> {$lang['gbl_return_to']}<a href='controlpanel.php'>{$lang['gbl_return_admin']}</a>
                         <br /> {$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");

    site_footer();
    die();
}

$delsysopid = (int) $_GET['delsysopid'];
$name       = $_GET['mod'];

if ($delsysopid > 0)
{
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "<strong>{$lang['text_del_sure']} $name?</strong><br />
                             <br /><a class='btn' href='controlpanel.php?deltrakmanid=$delsysopid&amp;mod=$name&amp;trakman=yes'>{$lang['text_del_yes']}</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a class='btn' href='controlpanel.php'>{$lang['text_del_no']}</a>");
    }

    site_footer();
    die();
}

$editsysop = $_GET['editsysop'];

if ($editsysop == 1)
{
    $id         = (int) $_GET['id'];
    $mod_name   = $_GET['mod_name'];
    $mod_url    = $_GET['mod_url'];
    $mod_image  = $_GET['mod_image'];
    $mod_status = (int) $_GET['mod_status'];
    $max_class  = (int) $_GET['max_class'];

    $query = "UPDATE controlpanel
              SET name = '$mod_name', url = '$mod_url', image = '$mod_image', status = '$mod_status', max_class = '$max_class'
              WHERE id = " . sqlesc($id) or sqlerr(__FILE__, __LINE__);

    $sql = $db->query($query);

    if ($sql)
    {
        if (get_user_id() == UC_TRACKER_MANAGER)
        {
            error_message_center("success",
                                 "{$lang['gbl_success']}",
                                 "<strong>{$lang['text_file_edited']}</strong><br />
                                 <br /> {$lang['gbl_return_to']}<a href='controlpanel.php'>{$lang['gbl_return_admin']}</a>
                                 <br /> {$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");
        }

    site_footer();
    die();
    }
}

$editsysopid = (int) $_GET['editsysopid'];
$name        = $_GET['name'];
$url         = $_GET['url'];
$image       = $_GET['image'];
$status      = (int) $_GET['status'];
$max_class   = (int) $_GET['max_class'];

if ($editsysopid > 0)
{
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        echo("<br />
            <form method='get' action='controlpanel.php'>
                <input type='hidden' name='editsysop' value='1' />
                <input type='hidden' name='id' value='$editsysopid' />
                <table class='main' align='center' width='50%' cellspacing='0' cellpadding='5'>
                    <tr>
                        <td class='colhead'>
                            <label for='desc'>{$lang['form_desc']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_name' id='desc' size='50' value='$name' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='name'>{$lang['form_filename']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_url' id='name' size='50' value='$url' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='image'>{$lang['form_image']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_image' id='image' size='50' value='$image' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='active'>{$lang['form_active']}</label>
                        </td>
                        <td align='left'>
                            <select name='mod_status' id='active'>
                                <option value='1'>{$lang['form_yes']}</option>
                                <option value='0'>{$lang['form_no']}</option>
                            </select>
                            <input type='hidden' name='add' value='true' />
                        </td>
                    </tr>
                        <tr>
                        <td class='colhead'>
                            <label for='option'>{$lang['form_option']}</label>
                        </td>
                        <td align='left'>
                            <select name='max_class' id='option'>
                                <option value='6'>{$lang['form_sysop']}</option>
                                <option value='4'>{$lang['form_moderator']}</option>
                                <option value='5'>{$lang['form_admin']}</option>
                                <option value='7'>{$lang['form_manager']}</option>
                            </select>
                            <input type='hidden' name='add' value='true' />
                        </td>
                    </tr>
                    <tr>
                        <td class='std' align='center' colspan='2'>
                            <input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' />
                        </td>
                    </tr>
                </table>
            </form><br /><br />");
    }

    site_footer();
    die();
}
//-- Finish Remove And Edit Options For Sysops --//

$admin = $_GET['admin'];

if ($admin == 'yes')
{
    $deladminid = (int) $_GET['deladminid'];

    $query = "DELETE FROM controlpanel
              WHERE id = " . sqlesc($deladminid) . "
              LIMIT 1" or sqlerr(__FILE__, __LINE__);

    $sql = $db->query($query);

    error_message_center("success",
                         "{$lang['gbl_success']}",
                         "<strong>{$lang['text_file_deleted']}</strong><br />
                         <br /> {$lang['gbl_return_to']}<a href='controlpanel.php'>{$lang['gbl_return_admin']}</a>
                         <br /> {$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");

    site_footer();
    die();
}

$deladminid = (int) $_GET['deladminid'];
$name       = $_GET['mod'];

if ($deladminid > 0)
{
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "<strong>{$lang['text_del_sure']} $name?</strong><br />
                             <br /><a class='btn' href='controlpanel.php?deltrakmanid=$deladminid&amp;mod=$name&amp;trakman=yes'>{$lang['text_del_yes']}</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a class='btn' href='controlpanel.php'>{$lang['text_del_no']}</a>");
    }

    site_footer();
    die();
}

$editadmin = $_GET['editadmin'];

if ($editadmin == 1)
{
    $id         = (int) $_GET['id'];
    $mod_name   = $_GET['mod_name'];
    $mod_url    = $_GET['mod_url'];
    $mod_image  = $_GET['mod_image'];
    $mod_status = (int) $_GET['mod_status'];
    $max_class  = (int) $_GET['max_class'];

    $query = "UPDATE controlpanel
              SET name = '$mod_name', url = '$mod_url', image = '$mod_image', status = '$mod_status', max_class = '$max_class'
              WHERE id = " . sqlesc($id) or sqlerr(__FILE__, __LINE__);

    $sql = $db->query($query);

    if ($sql)
    {
         if (get_user_id() == UC_TRACKER_MANAGER)
         {
            error_message_center("success",
                                 "{$lang['gbl_success']}",
                                 "<strong>{$lang['text_file_edited']}</strong><br />
                                 <br /> {$lang['gbl_return_to']}<a href='controlpanel.php'>{$lang['gbl_return_admin']}</a>
                                 <br /> {$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");
         }

    site_footer();
    die();

    }
}

$editadminid = (int) $_GET['editadminid'];
$name        = $_GET['name'];
$url         = $_GET['url'];
$image       = $_GET['image'];
$status      = (int) $_GET['status'];
$max_class   = (int) $_GET['max_class'];

if ($editadminid > 0)
{
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        echo("<br />
            <form method='get' action='controlpanel.php'>
                <input type='hidden' name='editadmin' value='1' />
                <input type='hidden' name='id' value='$editadminid' />
                <table class='main' align='center' width='50%' cellspacing='0' cellpadding='5'>
                    <tr>
                        <td class='colhead'>
                            <label for='desc'>{$lang['form_desc']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_name' id='desc' size='50' value='$name' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='name'>{$lang['form_filename']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_url' id='name' size='50' value='$url' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='image'>{$lang['form_image']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_image' id='image' size='50' value='$image' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='active'>{$lang['form_active']}</label>
                        </td>
                        <td align='left'>
                            <select name='mod_status' id='active'>
                                <option value='1'>{$lang['form_yes']}</option>
                                <option value='0'>{$lang['form_no']}</option>
                            </select>
                            <input type='hidden' name='add' value='true' />
                        </td>
                    </tr>
                        <tr>
                        <td class='colhead'>
                            <label for='option'>{$lang['form_option']}</label>
                        </td>
                        <td align='left'>
                            <select name='max_class' id='option'>
                                <option value='5'>{$lang['form_admin']}</option>
                                <option value='4'>{$lang['form_moderator']}</option>
                                <option value='6'>{$lang['form_sysop']}</option>
                                <option value='7'>{$lang['form_manager']}</option>
                            </select>
                            <input type='hidden' name='add' value='true' />
                        </td>
                    </tr>
                    <tr>
                        <td class='std' align='center' colspan='2'>
                            <input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' />
                        </td>
                    </tr>
                </table>
            </form><br /><br />");
    }

    site_footer();
    die();
}
//-- Finish Remove And Edit Options For Admins --//

//-- Start Remove And Edit Options For Moderators --//
$mod = $_GET['mod'];

if ($mod == 'yes')
{
    $delmodid = (int) $_GET['delmodid'];

    $query = "DELETE FROM controlpanel
              WHERE id = " . sqlesc($delmodid) . "
              LIMIT 1" or sqlerr(__FILE__, __LINE__);

    $sql = $db->query($query);

    error_message_center("success",
                         "{$lang['gbl_success']}",
                         "<strong>{$lang['text_file_deleted']}</strong><br />
                         <br /> {$lang['gbl_return_to']}<a href='controlpanel.php'>{$lang['gbl_return_admin']}</a>
                         <br /> {$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");

    site_footer();
    die();
}

$delmodid = (int) $_GET['delmodid'];
$name     = $_GET['mod'];

if ($delmodid > 0)
{
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        error_message_center("warn",
                             "{$lang['gbl_warning']}",
                             "<strong>{$lang['text_del_sure']} $name?</strong><br />
                             <br /><a class='btn' href='controlpanel.php?deltrakmanid=$delmodid&amp;mod=$name&amp;trakman=yes'>{$lang['text_del_yes']}</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a class='btn' href='controlpanel.php'>{$lang['text_del_no']}</a>");
    }

    site_footer();
    die();
}

$editmod = $_GET['editmod'];

if ($editmod == 1)
{
    $id         = (int) $_GET['id'];
    $mod_name   = $_GET['mod_name'];
    $mod_url    = $_GET['mod_url'];
    $mod_image  = $_GET['mod_image'];
    $mod_status = (int) $_GET['mod_status'];
    $max_class  = (int) $_GET['max_class'];

    $query = "UPDATE controlpanel
                SET name = '$mod_name', url = '$mod_url', image = '$mod_image', status = '$mod_status', max_class = '$max_class'
                WHERE id  = " . sqlesc($id) or sqlerr(__FILE__, __LINE__);

    $sql = $db->query($query);

    if ($sql)
    {
        if (get_user_id() == UC_TRACKER_MANAGER)
        {
            error_message_center("success",
                                 "{$lang['gbl_success']}",
                                 "<strong>{$lang['text_file_edited']}</strong><br />
                                 <br /> {$lang['gbl_return_to']}<a href='controlpanel.php'>{$lang['gbl_return_admin']}</a>
                                 <br /> {$lang['gbl_return_to']}<a href='index.php'>{$lang['gbl_main_page']}</a>");
        }

    site_footer();
    die();
    }
}

$editmodid = (int) $_GET['editmodid'];
$name      = $_GET['name'];
$url       = $_GET['url'];
$image     = $_GET['image'];
$status    = (int) $_GET['status'];
$max_class = (int) $_GET['max_class'];

if ($editmodid > 0)
{
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        echo("<br />
            <form method='get' action='controlpanel.php'>
                <input type='hidden' name='editmod' value='1' />
                <input type='hidden' name='id' value='$editmodid' />
                <table class='main' align='center' width='50%' cellspacing='0' cellpadding='5'>
                    <tr>
                        <td class='colhead'>
                            <label for='desc'>{$lang['form_desc']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_name' id='desc' size='50' value='$name' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='name'>{$lang['form_filename']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_url' id='name' size='50' value='$url' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='image'>{$lang['form_image']}</label>
                        </td>
                        <td align='left'>
                            <input type='text' name='mod_image' id='image' size='50' value='$image' />
                        </td>
                    </tr>
                    <tr>
                        <td class='colhead'>
                            <label for='active'>{$lang['form_active']}</label>
                        </td>
                        <td align='left'>
                            <select name='mod_status' id='active'>
                                <option value='1'>{$lang['form_yes']}</option>
                                <option value='0'>{$lang['form_no']}</option>
                            </select>
                            <input type='hidden' name='add' value='true' />
                        </td>
                    </tr>
                        <tr>
                        <td class='colhead'>
                            <label for='option'>{$lang['form_option']}</label>
                        </td>
                        <td align='left'>
                            <select name='max_class' id='option'>
                                <option value='4'>{$lang['form_moderator']}</option>
                                <option value='5'>{$lang['form_admin']}</option>
                                <option value='6'>{$lang['form_sysop']}</option>
                                <option value='7'>{$lang['form_manager']}</option>
                            </select>
                            <input type='hidden' name='add' value='true' />
                        </td>
                    </tr>
                    <tr>
                        <td class='std' align='center' colspan='2'>
                            <input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' />
                        </td>
                    </tr>
                </table>
            </form><br /><br />");
    }

    site_footer();
    die();
}
//-- Finish Remove And Edit Options For Moderators --//

//-- Start Output View --//

//-- Start Add Tools --//
$addaction = $_GET['addaction'];

if ($addaction == 'addtools')
{
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        print("<br />
                <form method='get' action='controlpanel.php'>
                    <table class='main' align='center' width='50%' cellspacing='0' cellpadding='5'>
                        <tr>
                            <td class='colhead'>
                                <label for='desc'>{$lang['form_desc']}</label>
                            </td>
                            <td class='rowhead' align='left'>
                                <input type='text' name='mod_name' id='desc' size='50' />
                            </td>
                        </tr>
                        <tr>
                            <td class='colhead'>
                                <label for='name'>{$lang['form_filename']}<br />({$lang['form_no_php']})</label>
                            </td>
                            <td class='rowhead' align='left'>
                                <input type='text' name='mod_url' id='name' size='50' />
                            </td>
                        </tr>
                        <tr>
                            <td class='colhead'>
                                <label for='image'>{$lang['form_image']}</label>
                            </td>
                            <td class='rowhead' align='left'>
                                <input type='text' name='mod_image' id='image' size='50' />
                            </td>
                        </tr>
                        <tr>
                            <td class='colhead'>
                                <label for='active'>{$lang['form_active']}</label>
                            </td>
                            <td class='rowhead' align='left'>
                                <select name='mod_status' id='active'>
                                    <option value='1'>{$lang['form_yes']}</option>
                                    <option value='0'>{$lang['form_no']}</option>
                                </select>
                                <input type='hidden' name='add' value='true' />
                            </td>
                        </tr>
                        <tr>
                            <td class='colhead'>
                                <label for='option'>{$lang['form_option']}</label>
                            </td>
                            <td class='rowhead' align='left'>
                                <select name='max_class' id='option'>
                                    <option value='4'>{$lang['form_moderator']}</option>
                                    <option value='5'>{$lang['form_admin']}</option>
                                    <option value='6'>{$lang['form_sysop']}</option>
                                    <option value='7'>{$lang['form_manager']}</option>
                                </select>
                                    <input type='hidden' name='create' value='true' />
                            </td>
                        </tr>
                        <tr>
                            <td colspan='3'>
                                <div align='center'>
                                    <input type='submit' class='btn' value='{$lang['gbl_btn_submit']}' />
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>");
    }

    site_footer();
    die();
}
//-- Finish Add Tools --//

//-- Start Deactive Tool List --//
$listaction = $_GET['listaction'];

if ($listaction == 'list')
{
    //-- Tracker Manager Tools --//
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        print("<br />
                <div align='center'>
                    <h2>{$lang['text_deactivated']}</h2>
                </div><br />
                <table class='main' align='center' width='50%' cellspacing='0' cellpadding='5'>
                    <tr>
                        <td class='colhead'>
                            <span style='font-weight : bold;'>{$lang['table_id']}</span>
                        </td>
                        <td class='colhead'>
                            <span style='font-weight : bold;'>{$lang['table_name']}</span>
                        </td>
                        <td class='colhead'>
                            <span style='font-weight : bold;'>{$lang['table_url']}</span>
                        </td>
                        <td class='colhead'>
                            <span style='font-weight : bold;'>{$lang['table_image']}</span>
                        </td>
                        <td class='colhead'>
                            <span style='font-weight : bold;'>{$lang['table_class']}</span>
                        </td>
                        <td class='colhead'>
                            <span style='text-align:center; font-weight : bold;'>{$lang['table_edit']}</span>
                        </td>
                        <td class='colhead'>
                            <span style='text-align:center;font-weight : bold;'>{$lang['table_delete']}</span>
                        </td>
                    </tr>");

        $query = "SELECT *
                  FROM controlpanel
                  WHERE status = 0 AND max_class = 7" or sqlerr(__FILE__, __LINE__);

        $sql = $db->query($query);

        while ($row = $sql->fetch_array(MYSQLI_BOTH))
        {
            $id        = (int) $row['id'];
            $name      = $row['name'];
            $url       = $row['url'];
            $image     = $row['image'];
            $max_class = (int) $row['max_class'];

            if ($max_class == 7)
        	{
        	   $max_class = "{$lang['text_manager']}";
            }

            print("<tr>
                    <td class='rowhead'>$id </td>
                    <td class='rowhead'>$name </td>
                    <td class='rowhead'>$url</td>
                    <td class='rowhead'>$image</td>
                    <td class='rowhead'>$max_class</td>
                    <td class='rowhead'>
                        <div align='center'>
                            <a href='controlpanel.php?edittrakmanid=$id&amp;name=$name&amp;url=$url&amp;image=$image'>
                                <img src='{$image_dir}admin/edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' />
                            </a>
                        </div>
                    </td>
                    <td class='rowhead'>
                        <div align='center'>
                            <a href='controlpanel.php?deltrakmanid=$id&amp;mod=$name'>
                                <img src='{$image_dir}admin/delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' />
                            </a>
                        </div>
                    </td>
                </tr>");
        }

        //-- Sysop Tools --//
        $query = "SELECT *
                  FROM controlpanel
                  WHERE status = 0 AND max_class = 6" or sqlerr(__FILE__, __LINE__);

        $sql = $db->query($query);

        while ($row = $sql->fetch_array(MYSQLI_BOTH))
        {
            $id        = (int) $row['id'];
            $name      = $row['name'];
            $url       = $row['url'];
            $image     = $row['image'];
            $max_class = (int) $row['max_class'];

            if ($max_class == 6)
        	{
        	   $max_class = "{$lang['text_sysop']}";
            }

            print("<tr>
                    <td class='rowhead'>$id </td>
                    <td class='rowhead'>$name </td>
                    <td class='rowhead'>$url</td>
                    <td class='rowhead'>$image</td>
                    <td class='rowhead'>$max_class</td>
                    <td class='rowhead'>
                        <div align='center'>
                            <a href='controlpanel.php?editsysopid=$id&amp;name=$name&amp;url=$url&amp;image=$image'>
                                <img src='{$image_dir}admin/edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' />
                            </a>
                        </div>
                    </td>
                    <td class='rowhead'>
                        <div align='center'>
                            <a href='controlpanel.php?delsysopid=$id&amp;mod=$name'><img src='{$image_dir}admin/delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' />
                            </a>
                        </div>
                    </td>
                </tr>");
        }

        //-- Admin Tools --//
        $query = "SELECT *
                  FROM controlpanel
                  WHERE status = 0 AND max_class = 5" or sqlerr(__FILE__, __LINE__);

        $sql = $db->query($query);

        while ($row = $sql->fetch_array(MYSQLI_BOTH))
        {
            $id        = (int) $row['id'];
            $name      = $row['name'];
            $url       = $row['url'];
            $image     = $row['image'];
            $max_class = (int) $row['max_class'];

            if ($max_class == 5)
        	{
        	   $max_class = "{$lang['text_admin']}";
            }

            print("<tr>
                    <td class='rowhead'>$id</td>
                    <td class='rowhead'>$name</td>
                    <td class='rowhead'>$url</td>
                    <td class='rowhead'>$image</td>
                    <td class='rowhead'>$max_class</td>
                    <td class='rowhead'>
                        <div align='center'>
                            <a href='controlpanel.php?editadminid=$id&amp;name=$name&amp;url=$url&amp;image=$image'>
                                <img src='{$image_dir}admin/edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' />
                            </a>
                        </div>
                    </td>
                    <td class='rowhead'>
                        <div align='center'>
                            <a href='controlpanel.php?deladmin=$id&amp;mod=$name'>
                                <img src='{$image_dir}admin/delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' />
                            </a>
                        </div>
                    </td>
                </tr>");
        }

        //-- Mod Tools --//
        $query = "SELECT *
                  FROM controlpanel
                  WHERE status = 0 AND max_class = 4" or sqlerr(__FILE__, __LINE__);

        $sql = $db->query($query);

        while ($row = $sql->fetch_array(MYSQLI_BOTH))
        {
            $id        = (int) $row['id'];
            $name      = $row['name'];
            $url       = $row['url'];
            $image     = $row['image'];
            $max_class = (int) $row['max_class'];

            if ($max_class == 4)
        	{
        	   $max_class = "{$lang['text_mod']}";
            }

            print("<tr>
                   <td class='rowhead'>$id</td>
                   <td class='rowhead'>$name</td>
                   <td class='rowhead'>$url</td>
                   <td class='rowhead'>$image</td>
                   <td class='rowhead'>$max_class</td>
                   <td class='rowhead'>
                        <div align='center'>
                            <a href='controlpanel.php?editmodid=$id&amp;name=$name&amp;url=$url&amp;image=$image'>
                                <img src='{$image_dir}admin/edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' />
                            </a>
                        </div>
                    </td>
                    <td class='rowhead'>
                        <div align='center'>
                            <a href='controlpanel.php?delmodid=$id&amp;mod=$name'>
                                <img src='{$image_dir}admin/delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' />
                            </a>
                        </div>
                    </td>
                </tr>");
        }

        print("</table><br /><br />");

    }

    site_footer();
    die();
}
//-- Finish Deactive Tool List --//

//-- Start Enter The Tools --//
$query = "SELECT *
          FROM controlpanel
          WHERE 1 = 1" or sqlerr(__FILE__, __LINE__);

$sql = $db->query($query);

while ($row = $sql->fetch_array(MYSQLI_BOTH))
{
    $file       = $row['url'];
    $id         = (int) $row['id'];
    $status     = (int) $row['status'];
    $max_class  = (int) $row['max_class'];
    $fileaction = $_GET['fileaction'];

    if ($fileaction == $row['id'] & user::$current['class'] < "$max_class")
    {
        error_message("warn",
                      "{$lang['gbl_warning']}",
                      "{$lang['err_staff_level']}");
    }

    if ($fileaction == $row['id'] & $status == 0)
    {
        error_message("warn",
                      "{$lang['gbl_warning']}",
                       "{$lang['err_deactivated']}");

        site_footer();
        die();
    }

    if ($fileaction == $row['id'] & $status == 1)
    {
        require("admincp/" . $file . ".php");
        site_footer();
        die();
    }
}
//-- Finish Enter The Tools --//

//-- Start Tool List --//

//-- Start Output View --//
if (get_user_class() >= UC_MODERATOR)
{
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        print("<br />
                <div class='btn' align='center'>
                    <a href='controlpanel.php?addaction=addtools'>{$lang['btn_add_new']}</a>
                </div><br /><br />
                <div class='btn' align='center'>
                    <a href='controlpanel.php?listaction=list'>{$lang['btn_inactive']}</a>
                </div><br /><br />
                <table border='1' align='center' width='40%' cellspacing='0' cellpadding='5'>");
    }
    else
    {
        print("<table border='1' align='center' width='30%' cellspacing='0' cellpadding='5'>");
    }

    //-- Start Tracker Manager Output --//
    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        print("<tr>
               <td class='colhead' align='center' colspan='4'>{$lang['table_manager_panel']}</td>
               </tr>");

        $query = "SELECT *
                  FROM controlpanel
                  WHERE status = 1 AND max_class = 7" or sqlerr(__FILE__, __LINE__);

        $sql = $db->query($query);

        while ($row = $sql->fetch_array(MYSQLI_BOTH))
        {
            $id    = (int) $row['id'];
            $name  = $row['name'];
            $url   = $row['url'];
            $image = $row['image'];

            print("<tr>
                    <td class='rowhead' width='40' height='40'>
                        <img src='{$image_dir}admin/{$row['image']}' width='40' height='40' border='0' alt='{$row['image']}' title='{$row['image']}' />
                    </td>
                    <td class='rowhead'>
                        <a href='controlpanel.php?fileaction=$id'>$name</a>
                    </td>
                    <td class='rowhead' width='40' height='40'>
                        <div align='center'>
                            <a href='controlpanel.php?edittrakmanid=$id&amp;name=$name&amp;url=$url&amp;image=$image'>
                                <img src='{$image_dir}admin/edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' />
                            </a>
                        </div>
                    </td>
                    <td class='rowhead' width='40' height='40'>
                        <div align='center'>
                            <a href='controlpanel.php?deltrakmanid=$id&amp;mod=$name'><img src='{$image_dir}admin/delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' />
                            </a>
                        </div>
                    </td>
                </tr>");
        }
    }
    //-- Finish Tracker Manager Output --//

    //-- Start Sysop Output --//
    if (get_user_class() >= UC_SYSOP)
    {
        print("<tr>
               <td class='colhead' align='center' colspan='4'>{$lang['table_sysop_panel']}</td>
               </tr>");

        $query = "SELECT *
                  FROM controlpanel
                  WHERE status = 1 AND max_class = 6" or sqlerr(__FILE__, __LINE__);

        $sql = $db->query($query);

        while ($row = $sql->fetch_array(MYSQLI_BOTH))
        {

            $id    = (int) $row['id'];
            $name  = $row['name'];
            $url   = $row['url'];
            $image = $row['image'];

            print("<tr>
                    <td class='rowhead' width='48' height='48'>
                        <img src='{$image_dir}admin/{$row['image']}' width='48' height='48' border='0' alt='{$row['image']}' title='{$row['image']}' />
                    </td>
                    <td class='rowhead'>
                        <a href='controlpanel.php?fileaction=$id'>$name</a>
                    </td>");

        if (get_user_id() == UC_TRACKER_MANAGER)
        {
            print("<td class='rowhead' width='40' height='40'>
                    <div align='center'>
                        <a href='controlpanel.php?editsysopid=$id&amp;name=$name&amp;url=$url&amp;image=$image'>
                            <img src='{$image_dir}admin/edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' />
                        </a>
                    </div>
                </td>
                <td class='rowhead' width='40' height='40'>
                    <div align='center'>
                        <a href='controlpanel.php?delsysopid=$id&amp;mod=$name'>
                            <img src='{$image_dir}admin/delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' />
                        </a>
                    </div>
                </td>");
        }
            print("</tr>");
        }
    }
    //-- Finish Sysop Output --//

    //-- Start Admin Output --//
    if (get_user_class() >= UC_ADMINISTRATOR)
    {
        print("<tr>
               <td class='colhead' align='center' colspan='4'>{$lang['table_admin_panel']}</td>
               </tr>");

        $query = "SELECT *
                  FROM controlpanel
                  WHERE status = 1 AND max_class = 5" or sqlerr(__FILE__, __LINE__);

        $sql = $db->query($query);

        while ($row = $sql->fetch_array(MYSQLI_BOTH))
        {
            $id    = (int) $row['id'];
            $name  = $row['name'];
            $url   = $row['url'];
            $image = $row['image'];

            print("<tr>
                    <td class='rowhead' width='40' height='40'>
                        <img src='{$image_dir}admin/{$row['image']}' width='40' height='40' border='0' alt='{$row['image']}' title='{$row['image']}' />
                    </td>
                    <td class='rowhead'>
                        <a href='controlpanel.php?fileaction=$id'>$name</a>
                    </td>");

            if (get_user_id() == UC_TRACKER_MANAGER)
            {
                print("<td class='rowhead' width='40' height='40'>
                        <div align='center'>
                            <a href='controlpanel.php?editadminid=$id&amp;name=$name&amp;url=$url&amp;image=$image'>
                                <img src='{$image_dir}admin/edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' />
                            </a>
                        </div>
                    </td>
                    <td class='rowhead' width='40' height='40'>
                        <div align='center'>
                            <a href='controlpanel.php?deladminid=$id&amp;mod=$name'>
                                <img src='{$image_dir}admin/delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' />
                            </a>
                        </div>
                    </td>");
            }
                print("</tr>");
        }
    }
    //-- Finish Admin Output --//

    //-- Start Mod Output --//
    {
        print("<tr>
               <td class='colhead' align='center' colspan='4'>{$lang['table_mod_panel']}</td>
               </tr>");

        $query = "SELECT *
                  FROM controlpanel
                  WHERE status = 1 AND max_class = 4" or sqlerr(__FILE__, __LINE__);

        $sql = $db->query($query);

        while ($row = $sql->fetch_array(MYSQLI_BOTH))
        {
            $id    = (int) $row['id'];
            $name  = $row['name'];
            $url   = $row['url'];
            $image = $row['image'];

            print("<tr>
                    <td class='rowhead' width='40' height='40'>
                        <img src='{$image_dir}admin/{$row['image']}' width='40' height='40' border='0' alt='{$row['image']}' title='{$row['image']}' />
                    </td>
                    <td class='rowhead'><a href='controlpanel.php?fileaction=$id' >$name</a></td>");

            if (get_user_id() == UC_TRACKER_MANAGER)
            {
                print("<td class='rowhead' width='40' height='40'>
                        <div align='center'>
                            <a href='controlpanel.php?editmodid=$id&amp;name=$name&amp;url=$url&amp;image=$image'>
                                <img src='{$image_dir}admin/edit.png' width='16' height='16' border='0' alt='{$lang['img_alt_edit']}' title='{$lang['img_alt_edit']}' />
                            </a>
                        </div>
                    </td>
                    <td class='rowhead' width='40' height='40'>
                        <div align='center'>
                            <a href='controlpanel.php?delmodid=$id&amp;mod=$name'>
                                <img src='{$image_dir}admin/delete.png' width='16' height='16' border='0' alt='{$lang['img_alt_delete']}' title='{$lang['img_alt_delete']}' />
                            </a>
                        </div>
                    </td>");
            }
            print("</tr>");
        }
    }
//-- Finish Mod Output --//

//-- Finish Tool List --//
    print("</table><br />");

}
//-- Finish Output View --//

site_footer();
?>