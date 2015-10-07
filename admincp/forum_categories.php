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

$lang = array_merge(load_language('adm_forum_categories'),
                    load_language('adm_global'));

//-- Presets --//
$act    = $_GET['act'];
$id     = intval(0 + $_GET['id']);
$action = $_GET['action'];

if (!$act)
{
    $act = 'forum';
}

//-- Delete Forum Action --//
if ($act == 'del')
{
    if (!$id)
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_no_id']}");
    }

    $db->query("DELETE
                FROM forum_category
                WHERE id = $id") or sqlerr(__FILE__, __LINE__);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "<strong>{$lang['text_deleted']}</strong><br />
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=14'>{$lang['text_return_cat']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
}

//-- Edit Forum Action --//
if ($_POST['action'] == 'editforum')
{
    $name = $_POST['name'];
    $desc = $_POST['desc'];

    if (!$name && !$desc && !$id)
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_no_name']}");
    }

    $db->query("UPDATE forum_category
                SET sort = '" . (int)$_POST['sort'] . "', name = " . sqlesc($_POST['name']) . ", description = " . sqlesc($_POST['desc']) . ", forid = 0, minclassview = '" . (int)$_POST['viewclass'] . "'
                WHERE id = '" . (int)$_POST['id'] . "'") or sqlerr(__FILE__, __LINE__);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "<strong>{$lang['text_edited']}</strong><br />
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=14'>{$lang['text_return_cat']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
}

//--Add Forum Action --//
if ($_POST['action'] == 'addforum')
{
    $name = trim($_POST['name']);
    $desc = trim($_POST['desc']);

    if (!$name && !$desc)
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_no_name']}");
    }

    $db->query("INSERT INTO forum_category (sort, name,  description,  minclassview, forid)
                VALUES(" . (int)$_POST['sort'] . ", " . sqlesc($_POST['name']) . ", " . sqlesc($_POST['desc']) . ", " . (int)$_POST['viewclass'] . ", 1)") or sqlerr(__FILE__, __LINE__);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "<strong>{$lang['text_created']}</strong><br />
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=14'>{$lang['text_return_cat']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
}

site_header("{$lang['title_edit_cat']}", false);

if ($act == 'forum')
{
    //-- Show Forums With Forum Managment Tools --//
    begin_frame($lang['title_forum_cat']);

    echo "<table border='0' align='center' width='100%' cellpadding='2' cellspacing='0'>";

    echo "<tr>
            <td class='colhead' align='left'>{$lang['table_name']}</td>
            <td class='colhead' align='left'>{$lang['table_viewable']}</td>
            <td class='colhead' align='center'>{$lang['table_modify']}</td>
        </tr>";

    $result = $db->query("SELECT *
                          FROM forum_category
                          ORDER BY SORT ASC");

    if ($row = $result->fetch_array(MYSQLI_BOTH))
    {
        do
        {
            echo "<tr>
                    <td class='rowhead'>
                        <a href='controlpanel.php?fileaction=14&amp;action=forumview&amp;forid={$row['id']}'>
                        <span style='font-weight : bold;'>" . security::html_safe($row['name']) . "</span></a><br />" . security::html_safe($row['description']) . "
                    </td>";

            echo "<td class='rowhead' width='20%'>" . get_user_class_name($row['minclassview']) . "</td>
                    <td align='center' width='20%'><span style='font-weight : bold;'>
                        <a href='controlpanel.php?fileaction=14&amp;act=editforum&amp;id={$row['id']}'>{$lang['text_edit']}</a>
                        &nbsp;|&nbsp;<a href='javascript:confirm_delete({$row['id']});'><span class='delete_forum_cat'>{$lang['text_delete']}</span></a></span>
                    </td>
                </tr>";
        }
        while ($row = $result->fetch_array(MYSQLI_BOTH));

        echo "</table>";
    }
    else
    {
        display_message_center("info",
                               "{$lang['gbl_adm_sorry']}",
                               "{$lang['err_no_record']}");
    }

?>

<br /><br />
<form method='post' action='controlpanel.php?fileaction=14&amp;action=addforum'>
    <table border='0' align='center' width='100%' cellspacing='0' cellpadding='3'>
        <tr align='center'>
            <td class='colhead' colspan='2'><?php echo $lang['form_make_cat']?></td>
        </tr>
        <tr>
            <td class='rowhead'>
                <span style='font-weight : bold;'>
                    <label for='name'><?php echo $lang['form_cat_name']?></label>
                </span>
            </td>
            <td class='rowhead'>
                <input type='text' name='name' id='name' size='20' maxlength='60' />
            </td>
        </tr>
        <tr>
            <td class='rowhead'>
                <span style='font-weight : bold;'>
                    <label for='desc'><?php echo $lang['form_cat_desc']?></label>
                </span>
            </td>
            <td class='rowhead'>
                <input type='text' name='desc' id='desc' size='30' maxlength='200' />
            </td>
        </tr>

        <tr>
            <td class='rowhead'>
                <span style='font-weight : bold;'><?php echo $lang['form_min_perm']?></span>
            </td>
            <td class='rowhead'>
                <select name='viewclass'>

                    <?php

                    $maxclass = get_user_class();

                    for ($i = 0;
                         $i <= $maxclass;
                         ++$i)

                    {
                        print("<option value='$i'" . ($user['class'] == $i ? " selected='selected' " : "") . ">$prefix" . get_user_class_name($i) . "</option>\n");
                    }

                    ?>

                </select>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>
                <span style='font-weight : bold;'><?php echo $lang['form_cat_rank']?></span>
            </td>
            <td class='rowhead'>
                <select name='sort'>

                    <?php

                    $res = $db->query("SELECT sort
                                       FROM forum_category");

                    $nr       = $res->num_rows;
                    $maxclass = $nr + 1;

                    for ($i = 0;
                         $i <= $maxclass;
                         ++$i)

                    {
                        print("<option value='$i'>$i </option>\n");
                    }

                    ?>

                </select>
            </td>
        </tr>
        <tr align='center'>
            <td class='rowhead' colspan='2'>
                <input type='hidden' name='action' value='addforum' />
                <input type='submit' class='btn' name='Submit' value='<?php echo $lang['form_make_cat']?>' />
            </td>
        </tr>
    </table>
</form>

<?php

    print("<table border='0' align='center' width='100%' cellspacing='0' cellpadding='3'>
        <tr>
            <td class='rowhead' align='center' colspan='1' height='20px'>
                <a href='controlpanel.php?fileaction=13'>
                <input type='submit' class='btn' value='{$lang['btn_forum_mgr']}' />
                </a>
            </td>
        </tr>
    </table>\n");

    end_frame();
}

if ($act == 'editforum')
{
    //--Edit Page For The Forums --//
    $id = intval(0 + $_GET['id']);

    begin_frame($lang['title_edit_cat']);

    $result = $db->query("SELECT *
                          FROM forum_category
                          WHERE id = $id");

    if ($row = $result->fetch_array(MYSQLI_BOTH))
    {
        //-- Get Forum Category Name - To Be Written --//
        do
        {
        ?>
        <form method='post' action='<?php security::esc_url($_SERVER['PHP_SELF']);?>'>
            <table border='0' align='center' width='100%' cellspacing='0' cellpadding='3'>
                <tr align='center'>
                    <td class='colhead' colspan='2'><?php echo $lang['title_edit_cat']?>: <?php echo security::html_safe($row['name']);?></td>
                </tr>
                <tr>
                    <td class='rowhead'><span style='font-weight : bold;'>
                        <label for='name'><?php echo $lang['form_cat_name']?></label></span>
                    </td>
                    <td class='rowhead'>
                        <input type='text' name='name' id='name' size='20' maxlength='60' value='<?php echo security::html_safe($row['name']);?>' />
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'><span style='font-weight : bold;'>
                        <label for='desc'><?php echo $lang['form_cat_desc']?></label></span>
                    </td>
                    <td class='rowhead'>
                        <input type='text' name='desc' id='desc' size='30' maxlength='200' value='<?php echo security::html_safe($row['description']);?>' />
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        <span style='font-weight : bold;'><?php echo $lang['form_min_perm']?></span>
                    </td>
                    <td class='rowhead'>
                        <select name='viewclass'>

                            <?php

                            $maxclass = get_user_class();

                            for ($i = 0;
                                 $i <= $maxclass;
                                 ++$i)

                            {
                                print("<option value='$i'" . ($row['minclassview'] == $i ? " selected='selected' " : "") . ">$prefix" . get_user_class_name($i) . "</option>\n");
                            }

                            ?>

                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        <span style='font-weight : bold;'><?php echo $lang['form_cat_rank']?></span>
                    </td>
                    <td class='rowhead'>
                        <select name='sort'>

                            <?php

                            $res = $db->query("SELECT sort
                                               FROM forum_category");

                            $nr       = $res->num_rows;
                            $maxclass = $nr + 1;

                            for ($i = 0;
                                 $i <= $maxclass;
                                 ++$i)

                            {
                                print("<option value='$i'" . ($row['sort'] == $i ? " selected='selected' " : "") . ">$i </option>\n");
                            }

                            ?>
                        </select>
                    </td>
                </tr>
                <tr align='center'>
                    <td colspan='2'>
                        <input type='hidden' name='action' value='editforum' />
                        <input type='hidden' name='id' value='<?php echo $id;?>' />
                        <input type='submit' class='btn' name='Submit' value='<?php echo $lang['btn_update_cat']?>' />
                    </td>
                </tr>
            </table>
        </form>

        <?php
        }
        while ($row = $result->fetch_array(MYSQLI_BOTH));
    }
    else
    {
        display_message_center("info",
                               "{$lang['gbl_adm_sorry']}",
                               "({$lang['err_no_record']}<br />
                                <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_main_page']}</a>");
    }

    end_frame();
}

echo("<br />");

?>
    <script type='text/javascript'>
    <!--
    function confirm_delete(id)
    {
        if (confirm('<?php echo $lang['text_del_sure']?>'))
        {
            self.location.href = 'controlpanel.php?fileaction=14&act=del&id=' + id;
        }
    }
    //-->
    </script>

<?php

site_footer();

?>