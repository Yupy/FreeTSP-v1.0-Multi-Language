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

db_connect();

$lang = array_merge(load_language('ok'),
                    load_language('func_bbcode'),
                    load_language('global'));

if (!mkglobal('type'))
{
    die();
}

$type = isset($_GET['type']) ? security::html_safe($_GET['type']) : '';

if ($type == "signup" && mkglobal("email"))
{
    site_header("{$lang['title_signup']}");

    display_message("success",
                    "{$lang['text_signup_success']}",
                    "{$lang['text_conf_email1']}(" . security::html_safe($email) . ").{$lang['text_conf_email2']}");

    site_footer();
}
elseif ($type == "sysop")
{
    site_header("{$lang['title_sysop']}");

    display_message("success",
                    "{$lang['gbl_success']}",
                    "{$lang['text_sysop_active']}");

    if (isset(user::$current))
    {
        display_message("info",
                        "{$lang['gbl_info']}",
                        "{$lang['text_auto_login1']}<strong><a href='index.php'>{$lang['gbl_main_page']}</a></strong>{$lang['text_auto_login2']}");
    }
    else
    {
        display_message("info",
                        "{$lang['gbl_info']}",
                        "{$lang['text_manual_login1']}<a href='login.php'>{$lang['text_manual_login2']}</a>{$lang['text_manual_login3']}");
    }
    site_footer();
}

elseif ($type == 'confirmed')
{
    site_header("{$lang['title_confirmed']}");

    if (get_user_id() == UC_TRACKER_MANAGER)
    {
        display_message_center("info",
                               "{$lang['text_tracker_manager']}",
                               "{$lang['text_proceed1']}<a href='controlpanel.php?fileaction=9'>{$lang['text_proceed2']}</a><br />{$lang['text_proceed3']}");
    }
    else
    {
        display_message_center("info",
                               "{$lang['text_confirmed']}",
                               "{$lang['text_proceed1']}<a href='index.php'>{$lang['text_home']}</a><br />{$lang['text_welcome']}$site_name.");
}

site_footer();
}


elseif ($type == 'confirm')
{
    if (isset(user::$current))
    {
        site_header("{$lang['title_signup_conf']}");

        display_message("success",
                        "{$lang['text_success_conf']}",
                        "{$lang['text_account_active']}<strong><a href='/'>{$lang['text_home']}</a></strong>{$lang['text_start_using']}<br/><br/>{$lang['text_before_using']}<?php echo $site_name?>{$lang['text_read']}<strong><a href='rules.php'>{$lang['text_rules']}</a></strong>{$lang['text_and_the']}<strong><a href=\"faq.php\">{$lang['text_faq']}</a></strong>.");

        site_footer();
    }
    else
    {
        site_header("{$lang['text_signup_conf']}");

        display_message("success",
                        "{$lang['text_success_conf']}",
                        "{$lang['text_manual_login1']}<a href='login.php'>{$lang['text_manual_login2']}</a>{$lang['text_manual_login3']}");

        site_footer();
    }
}
else
{
    die();
}

?>