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

db_connect();
logged_in();

$lang = array_merge(load_language('setclass'),
                    load_language('global'));

illegal_access($page, UC_MODERATOR);

if (user::$current['override_class'] != 255)
{
    error_message_center("info",
                         "{$lang['gbl_warning']}",
                         "{$lang['err_restore_class']}");
}

//-- Process The Querystring - No Security Checks Are Done As A Temporary Class Higher Than The Actual Class Mean Absoluetly Nothing. --//

if ($_GET['action'] == 'editclass')
{
    $newclass = inval(0 + $_GET['class']);
    $returnto = $_GET['returnto'];

    $db->query("UPDATE users
               SET override_class = " . sqlesc($newclass) . "
               WHERE id = " . user::$current['id']); //-- Set Temporary Class --

    header("Location: $site_url/$returnto");
    die();
}

site_header("{$lang['title_class']}" . security::html_safe(user::$current['username']));

print("<form method='get' action='setclass.php'>");
print("<input type='hidden' name='action' value='editclass' />");
print("<input type='hidden' name='returnto' value='index.php' />"); //-- Change To Any Page You Want --//

begin_frame("{$lang['title_on_fly']}", true, 5, true);

begin_table();

print("<tr>
        <td class='colhead'>{$lang['table_class']}
            <select name='class'>");

$maxclass = get_user_class() - 1;

for ($i = 0; $i <= $maxclass; ++$i)
{
    $currentclass = get_user_class_name($i);

    if ($currentclass)
        print("<option value='$i' " . ">" . get_user_class_name($i) . "</option>\n");
}

print("</select></td></tr>");

print("<tr>
        <td class='rowhead' align='center'>
            <input type='submit' class='btn' value='{$lang['btn_ok']}'/>
        </td>
    </tr>");

end_table();
end_frame();

print("</form><br />");

site_footer();

?>