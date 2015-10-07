<?php
$lang = array_merge(load_language('style_clean_header'));
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <!-- ******************************************************* -->
        <!-- *       This website is powered by FreeTSP v1.0       * -->
        <!-- *              Download and support at:               * -->
        <!-- *              http://www.freetsp.info                * -->
        <!-- ******************************************************* -->
        <title><?php echo $title ?></title>
        <meta name="title" content="FreeTSP" />
        <meta name="description"
              content="The FreeTSP idea was conceived by a bunch of like minded folk who wanted to create a BitTorrent source that was fundamentally different and was easy for new comers to get a site up and running and was also easy to learn" />
        <meta name="keywords"
              content="freetsp, free, ftsp, bittorrent, simple, kiss, tracker, code, free torrent source project, free torrent downloader, source code torrent, torrent programs" />
        <meta name="author" content="Krypto, Fireknight" />
        <meta name="owner" content="Krypto" />
        <meta name="copyright" content="(c) 2010" />

        <link rel="stylesheet" href="stylesheets/clean/clean.css" type="text/css" />
        <link rel="stylesheet" href="css/notification.css" type="text/css" media="screen" />

        <script type='text/javascript' src='js/jquery.js'></script>
        <script type="text/javascript" src="js/java_klappe.js"></script>
        <!-- Uncomment If You Wish To Ise Image-Resize Instead Of LightBox -->
        <!--<link type='text/css' rel='stylesheet' href='css/resize.css'  />
        <script type='text/javascript' src='js/core-resize.js'></script> -->
        <!-- COmment Out The Two Lines Below And The LightBox Section If You Wish To Use Image-Resize Instead Of LightBox -->
        <script type='text/javascript' src='js/jquery.lightbox-0.5.min.js'></script>
        <link rel='stylesheet' type='text/css' href='css/jquery.lightbox-0.5.css' media='screen' />

        <script type='text/javascript'>
            function popUp(URL)
            {
                day = new Date();
                id = day.getTime();
                eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0, scrollbars=1, location=0, statusbar=0, menubar=0, resizable=1, width=740, height=380, left=340, top=280');");
            }
        </script>

        <script type="text/javascript">
        jQuery.noConflict();
        jQuery(document).ready(function($)
        {
            $('#topmenu li.sublnk').hover(
            function()
            {
                $(this).addClass("selected");
                $(this).find('ul').stop(true, true);
                $(this).find('ul').show('fast');
            },
            function()
            {
                $(this).find('ul').hide('fast');
                $(this).removeClass("selected");
            });
        });
        </script>

        <!-- Comment Out To Use Core-Resize Instead -->
        <script type='text/javascript'>
            /*<![CDATA[*/
            //$(function () {
            $('document').ready(function () {
            $('a[rel=\"lightbox\"]').lightBox(); //-- Select All Links That Contains Lightbox In The Attribute rel --//
            });
            /*]]>*/
        </script>
        <!-- Comment Out To Use Core-Resize Instead -->

    </head>
    <body>

    <div class='headerwidth'>
        <div class='mbar' id='menubar'>
            <div class='mbar'>
                <div class='mbar dpad'>
                    <div class='menubar'>
                        <ul id='topmenu' class='lcol reset'>
                            <li><a href='index.php'><b><?php echo $lang['table_home']?></b></a>&nbsp;</li>
                            <li class='sublnk'>&nbsp;<a href='#'><b><?php echo $lang['table_torrents']?></b></a>&nbsp;
                                <ul>
                                    <li><a href='browse.php'><b><?php echo $lang['table_browse']?></b></a></li>
                                    <li><a href='upload.php'><b><?php echo $lang['table_upload']?></b></a></li>
                                    <li><a href='offers.php'><b><?php echo $lang['table_offers']?></b></a></li>
                                    <li><a href='requests.php'><b><?php echo $lang['table_requests']?></b></a></li>
                                    <li><a href='search.php'><b><?php echo $lang['table_search']?></b></a></li>
                                </ul>
                            </li>

                            <li class='sublnk'><a href='#'><b><?php echo $lang['table_usercp']?></b></a>&nbsp;
                                <ul>
                                    <li><a href='usercp.php?action=avatar'><b><?php echo $lang['table_avatar']?></b></a></li>
                                    <li><a href='usercp.php?action=signature'><b><?php echo $lang['table_signature']?></b></a></li>
                                    <li><a href='usercp.php?action=security'><b><?php echo $lang['table_security']?></b></a></li>
                                    <li><a href='usercp.php?action=torrents'><b><?php echo $lang['table_torrents']?></b></a></li>
                                    <li><a href='usercp.php?action=personal'><b><?php echo $lang['table_personal']?></b></a></li>
                                    <li><a href='usercp.php?action=pm'><b><?php echo $lang['table_messages']?></b></a></li>
                                </ul>
                            </li>

                            <li><a href='forums.php'><b><?php echo $lang['table_forums']?></b></a></li>
                            <li><a href='rules.php'><b><?php echo $lang['table_rules']?></b></a></li>
                            <li><a href='faq.php'><b><?php echo $lang['table_faq']?></b></a></li>
                            <li><a href='topten.php'><b><?php echo $lang['table_topten']?></b></a></li>
                            <li><a href='helpdesk.php'><b><?php echo $lang['table_helpdesk']?></b></a></li>
                            <li><a href='staff.php'><b><?php echo $lang['table_staff']?></b></a></li>
                            <li><a href='donate.php'><b><?php echo $lang['table_donate']?></b></a></li>
                            <li><a href='logout.php'><b><?php echo $lang['table_logout']?></b></a></li>

                            <?php if (get_user_class() >= UC_MODERATOR) { ?>
                            <li><a href='controlpanel.php'><b><?php echo $lang['table_tools']?></b></a></li>
                            <?php } ?>
                        </ul>

                        <a class='thide hrss' href='/rss.php' title='RSS'><?php echo $lang['img_alt_rss']?></a>

                    </div>
                </div>
            </div>
        </div><br /><br /><br /><br /><br />

        <div class='logo' style='float : left; width : auto;'>
            <a href='index.php'><img src='<?php echo $image_dir?>logo.png' width='486' height='100' border='0'
                                             alt='<?php echo $site_name?>' title='<?php echo $site_name?>'
                                             style='vertical-align : middle;' /></a>
        </div>

        <div align='right' class='smlmenu'>
            <?php print StatusBar(); ?>
        </div>

    </div><br />

    <div id='mainbox'>
        <div class='main_frame' align='center'>