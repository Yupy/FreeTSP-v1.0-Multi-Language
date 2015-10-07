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

$lang = array_merge(load_language('stdmenu'));

?>

<!-- Menu -->
<table class='mainouter' border='1' width='100%' cellspacing='0' cellpadding='10'>
    <?php print StatusBar(); ?>
    <tr>
        <td class='outer' align='center'>
            <table class='main' border='0' width='100%' cellspacing='0' cellpadding='5'>
                <tr>

                    <?php if (!user::$current)

                {
                    header("Refresh: 3; url='index.php'");
                }
                else
                {
                    if (user::$current['menu'] == "2")
                    {

                        ?>
                        <td class='navigation' align='center'>
                            <a href='/index.php'><?php echo $lang['table_home']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/browse.php'><?php echo $lang['table_browse']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/requests.php'><?php echo $lang['table_off_reg']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/search.php'><?php echo $lang['table_search']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/upload.php'><?php echo $lang['table_upload']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/altusercp.php'><?php echo $lang['table_profile']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/forums.php'><?php echo $lang['table_forums']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/topten.php'><?php echo $lang['table_topten']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/rules.php'><?php echo $lang['table_rules']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/faq.php'><?php echo $lang['table_faq']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/links.php'><?php echo $lang['table_links']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/credits.php'><?php echo $lang['table_credits']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/helpdesk.php'><?php echo $lang['table_help']?></a>
                        </td>

                        <td class='navigation' align='center'>
                            <a href='/staff.php'><?php echo $lang['table_staff']?></a>
                        </td>

                        <?php

                        if (get_user_class() >= UC_MODERATOR)
                        {
                            ?>
                            <td class='navigation' align='center'>
                                <a href='/controlpanel.php'><?php echo $lang['table_tools']?></a>
                            </td>
                            <?php
                        }
                    }
                }
                    ?>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br /><br />