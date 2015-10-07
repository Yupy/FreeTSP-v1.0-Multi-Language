<?php

$lang = array_merge(load_language('style_unique_footer'));

?>
            </td>
            </td>

            <td class='theme_border' width='20%' valign='top'>

            <!-- Start Side Status Block -->
                <table>
                    <?php print StatusBar(); ?>
                </table>
                <br /><br />
            <!-- Finish Side Status Block - -->

                <!-- Start Side Navigation Menu -->
                <?php if (get_user_class() >= UC_MODERATOR)
                { ?>
                    <table width='200'>
                        <tr>
                            <td class='signed' align='center' height='30'><?php echo $lang['table_staff_only']?></td>
                        </tr>
                    </table>

                    <div class='menu'>
                         <a href='controlpanel.php'>
                            <img src='stylesheets/unique/images/admin.png' width='16' height='16' alt='<?php echo $lang['img_alt_admincp']?>' title='<?php echo $lang['img_alt_admincp']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_admincp']?>
                        </a>
                    </div>
                    <br /><br />
                <?php
                } ?>

                <table width='200'>
                    <tr>
                        <td class='signed' align='center' height='30'><?php echo $lang['table_nav']?></td>
                    </tr>
                </table>

                <div class='menu'>
                    <a href='index.php'>
                        <img src='stylesheets/unique/images/home.png' width='16' height='16' alt='<?php echo $lang['img_alt_home']?>' title='<?php echo $lang['img_alt_home']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_home']?>
                    </a>
                    <a href='altusercp.php'>
                        <img src='stylesheets/unique/images/usercp.png' width='16' height='16' alt='<?php echo $lang['img_alt_usercp']?>' title='<?php echo $lang['img_alt_usercp']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_usercp']?>
                    </a>
                    <a href='browse.php'>
                        <img src='stylesheets/unique/images/browse.png' width='16' height='16' alt='<?php echo $lang['img_alt_browse']?>' title='<?php echo $lang['img_alt_browse']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_browse']?>
                    </a>
                    <a href='upload.php'>
                        <img src='stylesheets/unique/images/upload.png' width='16' height='16' alt='<?php echo $lang['img_alt_upload']?>' title='<?php echo $lang['img_alt_upload']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_upload']?>
                    </a>
                    <a href='forums.php'>
                        <img src='stylesheets/unique/images/forums.png' width='16' height='16' alt='<?php echo $lang['img_alt_forums']?>' title='<?php echo $lang['img_alt_forums']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_forums']?>
                    </a>
                    <a href='logout.php'>
                        <img src='stylesheets/unique/images/leave.png' width='16' height='16' alt='<?php echo $lang['img_alt_logout']?>' title='<?php echo $lang['img_alt_logout']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_logout']?>
                    </a>
                </div>

                <br /><br />

                <table width='200'>
                    <tr>
                        <td class='signed' align='center' height='30'><?php echo $lang['table_info']?></td>
                    </tr>
                </table>

                <div class='menu'>
                    <a href='topten.php'>
                        <img src='stylesheets/unique/images/top10.png' width='16' height='16' alt='<?php echo $lang['img_alt_topten']?>' title='<?php echo $lang['img_alt_topten']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_topten']?>
                    </a>
                    <a href='credits.php'>
                        <img src='stylesheets/unique/images/log.png' width='16' height='16' alt='<?php echo $lang['img_alt_credits']?>' title='<?php echo $lang['img_alt_credits']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_credits']?>
                    </a>
                    <a href='rules.php'>
                        <img src='stylesheets/unique/images/rules.png' width='16' height='16' alt='<?php echo $lang['img_alt_rules']?>' title='<?php echo $lang['img_alt_rules']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_rules']?>
                    </a>
                    <a href='faq.php'>
                        <img src='stylesheets/unique/images/faq.png' width='16' height='16' alt='<?php echo $lang['img_alt_faq']?>' title='<?php echo $lang['img_alt_faq']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_faq']?>
                    </a>
                    <a href='helpdesk.php'>
                        <img src='stylesheets/unique/images/help.png' width='16' height='16' alt='<?php echo $lang['img_alt_helpdesk']?>' title='<?php echo $lang['img_alt_helpdesk']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_helpdesk']?>
                    </a>
                    <a href='staff.php'>
                        <img src='stylesheets/unique/images/staff.png' width='16' height='16' alt='<?php echo $lang['img_alt_staff']?>' title='<?php echo $lang['img_alt_staff']?>' />&nbsp;&nbsp;&nbsp;<?php echo $lang['table_staff']?>
                    </a>
                </div>
                <!-- Finish Side Navigation Menu -->

            </td>

        <!--  No Support From FreeTSP Will Be Given - If The Credits Below Are Removed Or Altered  -->
        <tr>
            <td class='theme_border' align='center' colspan='2'><?php copyright(); ?> <?php misc::debug(); ?>
                <br />Original <a href='http://www.freetsp.info/topic/773-uniquefreetsp-10-alpha-by-kidvision/'>Unique</a> Theme By KidVision - Modified For v1.0 By Fireknight<br /><br />
            </td>
            <td class='theme_border' align='right' colspan='3'>
                <a href="#"><img src='stylesheets/unique/images/top.png' width='25' height='25' alt='' title='<?php echo $lang['img_alt_top_page']?>' /></a>
            </td>
        </tr>
        <!-- End Of Credits  -->

    </tr>
</table>
</body></html>