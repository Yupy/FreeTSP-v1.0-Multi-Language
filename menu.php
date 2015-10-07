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

$lang = array_merge(load_language('menu'));

?>

<table class='mainouter' width='100%' border='1' cellspacing='0' cellpadding='10'>
    <?php print StatusBar(); ?>
    <tr>
        <td class='outer' align='center'>
            <div class='navigation'>
                <ul class='stn-menu TSP'>
                    <li><a href='index.php'><?php echo $lang['table_home']?></a></li>
                    <li class='hasSubNav hasArrow'>
                        <a href='javascript:'><?php echo $lang['table_torrents']?></a>
                        <span class='arrow'></span>
                        <ul>
                            <li><a href='browse.php'><?php echo $lang['table_browse']?></a></li>
                            <li><a href='search.php'><?php echo $lang['table_search']?></a></li>
                            <li><a href='upload.php'><?php echo $lang['table_upload']?></a></li>
                            <li><a href='offers.php'><?php echo $lang['table_offers']?></a></li>
                            <li><a href='requests.php'><?php echo $lang['table_requests']?></a></li>
                            <li><a href='mytorrents.php'><?php echo $lang['table_my_uploads']?></a></li>
                        </ul>
                    </li>

                    <li class='hasSubNav hasArrow'>
                        <a href="javascript:"><?php echo $lang['table_usercp']?></a>
                        <span class='arrow'></span>
                        <ul>
                            <li><a href='usercp.php?action=avatar'><?php echo $lang['table_avatar']?></a></li>
                            <li><a href='usercp.php?action=signature'><?php echo $lang['table_signature']?></a></li>
                            <li><a href='usercp.php'><?php echo $lang['table_messages']?></a></li>
                            <li><a href='usercp.php?action=security'><?php echo $lang['table_security']?></a></li>
                            <li><a href='usercp.php?action=torrents'><?php echo $lang['table_torrents']?></a></li>
                            <li><a href='usercp.php?action=personal'><?php echo $lang['table_personal']?></a></li>
                            <li><a href='usercp.php?action=preview'><?php echo $lang['table_preview']?></a></li>
                            <li><a href='logout.php'><?php echo $lang['table_logout']?></a></li>
                        </ul>
                    </li>

                    <li><a href='forums.php'><?php echo $lang['table_forums']?></a></li>

                    <li class='hasSubNav hasArrow'>
                        <a href="javascript:"><?php echo $lang['table_info']?></a>
                        <span class='arrow'></span>
                        <ul>
                            <li><a href='rules.php'><?php echo $lang['table_rules']?></a></li>
                            <li><a href='faq.php'><?php echo $lang['table_faq']?></a></li>
                            <li><a href='topten.php'><?php echo $lang['table_topten']?></a></li>
                            <li><a href='links.php'><?php echo $lang['table_links']?></a></li>
                            <li><a href='credits.php'><?php echo $lang['table_credits']?></a></li>
                        </ul>
                    </li>

                    <li><a href='helpdesk.php'><?php echo $lang['table_helpdesk']?></a></li>
                    <li><a href='staff.php'><?php echo $lang['table_staff']?></a></li>

                    <?php if (get_user_class() >= UC_MODERATOR)
                { ?>
                    <li><a href='controlpanel.php'><?php echo $lang['table_tools']?></a></li>
                <?php }?>
                </ul>
            </div>
        </td>
    </tr>
</table>
<br /><br />