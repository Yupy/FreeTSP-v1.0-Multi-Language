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

$lang = array_merge(load_language('adm_forum_manager'),
                    load_language('adm_global'));

$id = intval(0 + $_GET['id']);

//-- Delete Forum Action --//
if ($_GET['action'] == 'del')
{
    if (!$id)
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_no_forum_id']}");
    }

    $result = $db->query("SELECT *
                          FROM topics
                          WHERE forumid = $id");

    if ($row = $result->fetch_array(MYSQLI_BOTH))
    {
        do
        {
            $db->query("DELETE
                        FROM posts
                        WHERE topicid = $id") or sqlerr(__FILE__, __LINE__);
        }
        while ($row = $result->fetch_array(MYSQLI_BOTH));
    }

    $db->query("DELETE
                FROM topics
                WHERE forumid = $id") or sqlerr(__FILE__, __LINE__);

    $db->query("DELETE
                FROM forums
                WHERE id = $id") or sqlerr(__FILE__, __LINE__);
	
	$Memcache->delete_value('forum::access::levels::' . $id);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "<strong>{$lang['text_forum_deleted']}</strong><br />
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=13'>{$lang['text_ret_forum_mgr']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang[gbl_adm_main_page]}</a>");
}

//-- Edit Forum Action --//
if ($_POST['action'] == 'editforum')
{
    $name = ($_POST['name']);
    $desc = ($_POST['desc']);

    if (!$name && !$desc && !$id)
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_no_forum_id']}");
    }

    $db->query("UPDATE forums
                SET sort = '" . (int)$_POST['sort'] . "', name = " . sqlesc($_POST['name']) . ", description = " . sqlesc($_POST['desc']) . ", forid = " . sqlesc(($_POST['forum_category'])) . ", minclassread = '" . (int)$_POST['readclass'] . "', minclasswrite = '" . (int)$_POST['writeclass'] . "', minclasscreate = '" . (int)$_POST['createclass'] . "'
                WHERE id = '" . (int)$_POST['id'] . "'") or sqlerr(__FILE__, __LINE__);
	
	$Memcache->delete_value('forum::access::levels::' . $id);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "<strong>{$lang['text_forum_edited']}</strong><br />
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=13'>{$lang['text_ret_forum_mgr']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang[gbl_adm_main_page]}</a>");
}

//-- Add Forum Action --//
if ($_POST['action'] == 'addforum')
{
    $name = ($_POST['name']);
    $desc = ($_POST['desc']);

    if (!$name && !$desc)
    {
        //header("Location: $site_url/forummanage.php");
        //die();
    }

    $db->query("INSERT INTO forums (sort, name,  description,  minclassread,  minclasswrite, minclasscreate, forid)
                VALUES(" . (int)$_POST['sort'] . ", " . sqlesc($_POST['name']) . ", " . sqlesc($_POST['desc']) . ", " . (int)$_POST['readclass'] . ", " . (int)$_POST['writeclass'] . ", " . (int)$_POST['createclass'] . ", " . sqlesc(($_POST['forum_category'])) . ")") or sqlerr(__FILE__, __LINE__);
	
	$Memcache->delete_value('forum::access::levels::' . $id);
}

//-- Show Forums With Forum Managment Tools --//
site_header("{$lang['title_forummanage']}", false);

begin_frame("{$lang['title_forums']}");

$result = $db->query("SELECT *
                      FROM forums
                      ORDER BY sort ASC");

if ($result->num_rows == 0)
{
    display_message("info",
                    "{$lang['gbl_adm_sorry']}",
                    "{$lang['text_no_record']}");
}
else
{
    echo "<table border='0' align='center' width='100%' cellpadding='2' cellspacing='0'>";
    echo "<tr>
            <td class='colhead' align='left'>{$lang['table_name']}</td>
            <td class='colhead'>{$lang['table_forum_cat']}</td>
            <td class='colhead'>{$lang['table_read']}</td>
            <td class='colhead'>{$lang['table_write']}</td>
            <td class='colhead'>{$lang['table_create']}</td>
            <td class='colhead'>{$lang['table_modify']}</td>
        </tr>";

    if ($row = $result->fetch_array(MYSQLI_BOTH))
    {
        do
        {
            $forid = intval(0 + $row['forid']);

            $res2 = $db->query("SELECT name
                                FROM forum_category
                                WHERE id = $forid");

            $arr2 = $res2->fetch_array(MYSQLI_BOTH);
            $name = security::html_safe($arr2['name']);

            echo "<tr>
                    <td class='rowhead'>
                        <a href='forums.php?action=viewforum&amp;forumid={$row['id']}'>
                            <span style='font-weight : bold;'>" . security::html_safe($row['name']) . "</span>
                        </a><br />" . security::html_safe($row['description']) . "
                    </td>";

            echo "<td class='rowhead'>$name</td>
                <td class='rowhead'>" . get_user_class_name($row['minclassread']) . "</td>
                <td class='rowhead'>" . get_user_class_name($row['minclasswrite']) . "</td>
                <td class='rowhead'>" . get_user_class_name($row['minclasscreate']) . "</td>
                <td align='center'>
                    <span style='font-weight : bold;'>
                        <a href='controlpanel.php?fileaction=13&amp;action=editforum&amp;id={$row['id']}'>{$lang['table_edit']}</a> | <a href='javascript:confirm_delete({$row['id']});'>
                            <span class='delete_forum_manager'>{$lang['table_delete']}</span>
                        </a>
                    </span>
                </td></tr>";
        }
        while ($row = $result->fetch_array());
    }
    echo "</table>";
}
?>

<br /><br />
<form method='post' action='<?php security::esc_url($_SERVER['PHP_SELF']);?>'>
    <table border='0' align='center' width='100%' cellspacing='0' cellpadding='3'>
        <tr align='center'>
            <td class='colhead' colspan='2' ><?php echo $lang['title_make_new']?></td>
        </tr>
        <tr>
            <td class='rowhead'>
                <span style='font-weight : bold;'>
                    <label for='name'><?php echo $lang['form_name']?></label>
                </span>
            </td>
            <td class='rowhead'>
                <input type='text' name='name' id='name' size='20' maxlength='60' />
            </td>
        </tr>
        <tr>
            <td class='rowhead'>
                <span style='font-weight : bold;'>
                    <label for='desc'><?php echo $lang['form_desc']?></label>
                </span>
            </td>
            <td class='rowhead'>
                <input type='text' name='desc' id='desc' size='30' maxlength='200' />
            </td>
        </tr>
        <tr>
            <td class='rowhead'>
                <span style='font-weight : bold;'><?php echo $lang['form_forum_cat']?></span>
            </td>
            <td class='rowhead'>
                <select name='forum_category'>

                    <?php

                    $forid = intval(0 + $row['forid']);
                    $res   = $db->query("SELECT *
                                         FROM forum_category");

                    while ($arr = $res->fetch_array(MYSQLI_BOTH))
                    {

                        $name = security::html_safe($arr['name']);
                        $i    = intval(0 + $arr['id']);

                        print("<option value='$i'" . ($forid == $i ? " selected='selected' " : "") . ">$prefix" . $name . "</option>\n");
                    }

                    ?>

                </select>
            </td>
        </tr>

        <tr>
            <td class='rowhead'>
                <span style='font-weight : bold;'><?php echo $lang['form_min_read']?></span>
            </td>
            <td class='rowhead'>
                <select name='readclass'>

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
                <span style='font-weight : bold;'><?php echo $lang['form_min_write']?></span>
            </td>
            <td class='rowhead'>
                <select name='writeclass'>

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
                <span style='font-weight : bold;'><?php echo $lang['form_min_create']?></span>
            </td>
            <td class='rowhead'>
                <select name='createclass'>

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
                <span style='font-weight : bold;'><?php echo $lang['form_rank']?></span>
            </td>
            <td class='rowhead'>
                <select name='sort'>

                    <?php

                    $res = $db->query("SELECT sort
                                       FROM forums");

                    $nr = $res->num_rows;

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
            <td colspan='2'>
                <input type='hidden' name='action' value='addforum' />
                <input type='submit' class='btn' name='submit' value='<?php echo $lang['btn_make_forum']?>' />
            </td>
        </tr>
    </table>
</form>

<?php

print("<table border='0' align='center' width='100%' cellspacing='0' cellpadding='3'>
        <tr>
            <td align='center' colspan='1'>
                <a href='controlpanel.php?fileaction=14'>
                  <input type='submit' class='btn' value='{$lang['btn_over_forum']}' />
                </a>
            </td>
        </tr>
</table>\n");

if ($_GET['action'] == 'editforum')
{
    //-- Edit Page For The Forums --//
    $id = 0 + (int)($_GET['id']);

    begin_frame($lang['title_edit']);

    $result = $db->query("SELECT *
                          FROM forums
                          WHERE id = " . sqlesc($id));

    if ($row = $result->fetch_array(MYSQLI_BOTH))
    {
        //-- Get OverForum Name - To Be Written --//
        do
        {
            ?>

        <form method='post' action='<?php security::esc_url($_SERVER['PHP_SELF']);?>'>
            <table border='0' align='center' width='100%' cellspacing='0' cellpadding='3'>
                <tr align='center'>
                    <td class='colhead' colspan='2'><?php echo $lang['title_edit']?>: <?php echo htmlentities($row['name'], ENT_QUOTES);?></td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        <span style='font-weight : bold;'>
                            <label for='name1'><?php echo $lang['form_name']?></label>
                        </span>
                    </td>
                    <td class='rowhead'>
                        <input type='text' name='name' id='name1' size='20' maxlength='60' value='<?php echo htmlentities($row['name'], ENT_QUOTES);?>' />
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        <span style='font-weight : bold;'>
                            <label for='desc1'><?php echo $lang['form_desc']?></label>
                        </span>
                    </td>
                    <td class='rowhead'>
                        <input type='text' name='desc' id='desc1' size='30' maxlength='200' value='<?php echo htmlentities($row['description'], ENT_QUOTES);?>' />
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        <span style='font-weight : bold;'><?php echo $lang['form_forum_cat']?></span>
                    </td>
                    <td class='rowhead'>
                        <select name='forum_category'>

                            <?php

                            $forid = intval(0 + $row['forid']);
                            $res   = $db->query("SELECT *
                                                 FROM forum_category");

                            while ($arr = $res->fetch_array(MYSQLI_BOTH))
                            {
                                $name = security::html_safe($arr['name']);
                                $i    = intval(0 + $arr['id']);

                                print("<option value='$i'" . ($forid == $i ? " selected='selected' " : "") . ">$prefix" . $name . "</option>\n");
                            }

                            ?>

                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        <span style='font-weight : bold;'><?php echo $lang['form_min_read']?></span>
                    </td>
                    <td class='rowhead'>
                        <select name='readclass'>

                            <?php

                            $maxclass = get_user_class();

                            for ($i = 0;
                                 $i <= $maxclass;
                                 ++$i)

                            {
                                print("<option value='$i'" . ($row['minclassread'] == $i ? " selected='selected' " : "") . ">$prefix" . get_user_class_name($i) . "</option>\n");
                            }

                            ?>

                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        <span style='font-weight : bold;'><?php echo $lang['form_min_write']?></span>
                    </td>
                    <td class='rowhead'>
                        <select name='writeclass'>

                            <?php

                            $maxclass = get_user_class();

                            for ($i = 0;
                                 $i <= $maxclass;
                                 ++$i)

                            {
                                print("<option value='$i'" . ($row['minclasswrite'] == $i ? " selected='selected' " : "") . ">$prefix" . get_user_class_name($i) . "</option>\n");
                            }

                            ?>

                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        <span style='font-weight : bold;'><?php echo $lang['form_min_create']?></span>
                    </td>
                    <td class='rowhead'>
                        <select name='createclass'>

                            <?php

                            $maxclass = get_user_class();

                            for ($i = 0;
                                 $i <= $maxclass;
                                 ++$i)

                            {
                                print("<option value='$i'" . ($row['minclasscreate'] == $i ? " selected='selected' " : "") . ">$prefix" . get_user_class_name($i) . "</option>\n");
                            }

                            ?>

                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        <span style='font-weight : bold;'><?php echo $lang['form_rank']?></span>
                    </td>
                    <td class='rowhead'>
                        <select name='sort'>

                            <?php

                            $res = $db->query("SELECT sort
                                               FROM forums");

                            $nr = $res->num_rows;

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
                        <input type='submit' class='btn' name='Submit' value='<?php echo $lang['btn_update_forum']?>' />
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
        display_message("info",
                        "{$lang['gbl_adm_sorry']}",
                        "<strong>{$lang['text_no_record']}</strong><br />
                        <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=13'>{$lang['text_ret_forum_mgr']}</a>");
    }

    end_frame();

}

print("</td></tr></table>");
echo("<br />");

?>
<script type='text/javascript'>
    <!--
    function confirm_delete(id)
    {
        if (confirm('<?php echo $lang['text_del_sure']?>'))
        {
            self.location.href = 'controlpanel.php?fileaction=13&action=del&id=' + id;
        }
    }
    //-->
</script>

<?php

site_footer();

?>