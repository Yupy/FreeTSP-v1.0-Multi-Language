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

$lang = array_merge(load_language('adm_tracker_manager'),
                    load_language('adm_global'));

// if (get_user_class() < UC_SYSOP)

//-- Uncomment The Line Above & Comment Out The Line Below - If You Want All Sysop's To Access This Page - They Still Need All Mysql Infomation To Alter Anything --//

if (get_user_id() <> UC_TRACKER_MANAGER) //-- Only The Person Who's Id Has Been Set In Functions/function_config.php And /config_rewrite.php Can Access --//

{
    error_message_center("warn",
                         "{$lang['gbl_adm_warning']}",
                         "{$lang['err_denied']}");
}

define('FTSP_ROOT_PATH', '');

$tracker_manager = new tracker_manager;

class tracker_manager
{
    var $VARS = array();

    function tracker_manager()
    {
        $this->VARS = array_merge($_GET, $_POST);

        switch ($this->VARS['progress'])
        {
            case '1':
                $this->do_step_one();
                break;

            default:
                $this->do_start();
                break;
        }
    }

    function do_start()
    {
        global $lang, $db;

        site_header("{$lang['title_tracker_manager']}", false);

        print("<div class='box_content'>");
        print("<form class='sky-form' method='post' action='controlpanel.php?fileaction=9'>");
        print("<div>");
        print("<input type='hidden' name='progress' value='1' />");
        print("</div>");
        print("<h2 align='center'>{$lang['form_title']}</h2><br />");

        print("<div id='featured'>
                <ul>
                    <li><a class='btn' href='#fragment-1'>{$lang['btn_db_set']}</a></li>
                    <li><a class='btn' href='#fragment-2'>{$lang['btn_site_set']}</a></li>
                    <li><a class='btn' href='#fragment-3'>{$lang['btn_torrent_set']}</a></li>
                    <li><a class='btn' href='#fragment-4'>{$lang['btn_forum_set']}</a></li>
                    <li><a class='btn' href='#fragment-5'>{$lang['btn_cleanup_set']}</a></li>
                </ul>");

        print("<div>");

        $query = "SELECT *
                  FROM config
                  WHERE 1 = 1";

        $sql = $db->query($query);

        while ($row = $sql->fetch_array(MYSQLI_BOTH))
        {
            $mysql_host            = $row['mysql_host'];
            $mysql_db              = $row['mysql_db'];
            $mysql_user            = $row['mysql_user'];
            $mysql_pass            = $row['mysql_pass'];
            $site_url              = $row['site_url'];
            $announce_url          = $row['announce_url'];
            $site_online           = $row['site_online'];
            $members_only          = $row['members_only'];
            $site_mail             = $row['site_mail'];
            $email_confirm         = $row['email_confirm'];
            $site_name             = $row['site_name'];
            $image_dic             = $row['image_dic'];
            $torrent_dic           = $row['torrent_dic'];
            $torrents_allfree      = $row['torrents_allfree'];
            $peer_limit            = $row['peer_limit'];
            $max_members           = $row['max_members'];
            $max_users_then_invite = $row['max_users_then_invite'];
            $invites               = $row['invites'];
            $signup_timeout        = $row['signup_timeout'];
            $min_votes             = $row['min_votes'];
            $autoclean_interval    = $row['autoclean_interval'];
            $announce_interval     = $row['announce_interval'];
            $max_torrent_size      = $row['max_torrent_size'];
            $max_dead_torrent_time = $row['max_dead_torrent_time'];
            $posts_read_expiry     = $row['posts_read_expiry'];
            $max_login_attempts    = $row['max_login_attempts'];
            $dictbreaker           = $row['dictbreaker'];
            $delete_old_torrents   = $row['delete_old_torrents'];
            $dead_torrents         = $row['dead_torrents'];
            $site_reputation       = $row['site_reputation'];
            $maxfilesize           = $row['maxfilesize'];
            $attachment_dir        = $row['attachment_dir'];
            $forum_width           = $row['forum_width'];
            $maxsubjectlength      = $row['maxsubjectlength'];
            $postsperpage          = $row['postsperpage'];
            $use_attachment_mod    = $row['use_attachment_mod'];
            $use_poll_mod          = $row['use_poll_mod'];
            $forum_stats_mod       = $row['forum_stats_mod'];
            $use_flood_mod         = $row['use_flood_mod'];
            $limmit                = $row['limmit'];
            $minutes               = $row['minutes'];
            $staff_log             = $row['staff_log'];
            $site_log              = $row['site_log'];
            $parked_users          = $row['parked_users'];
            $inactive_users        = $row['inactive_users'];
            $old_login_attempts    = $row['old_login_attempts'];
            $old_help_desk         = $row['old_help_desk'];
            $promote_upload        = $row['promote_upload'];
            $promote_ratio         = $row['promote_ratio'];
            $promote_time_member   = $row['promote_time_member'];
            $demote_ratio          = $row['demote_ratio'];
            $waittime              = $row['waittime'];
            $max_class_wait        = $row['max_class_wait'];
            $ratio_1               = $row['ratio_1'];
            $gigs_1                = $row['gigs_1'];
            $wait_1                = $row['wait_1'];
            $ratio_2               = $row['ratio_2'];
            $gigs_2                = $row['gigs_2'];
            $wait_2                = $row['wait_2'];
            $ratio_3               = $row['ratio_3'];
            $gigs_3                = $row['gigs_3'];
            $wait_3                = $row['wait_3'];
            $ratio_4               = $row['ratio_4'];
            $gigs_4                = $row['gigs_4'];
            $wait_4                = $row['wait_4'];
        }

        //-- Start Database Details --//
        print("<div id='fragment-1' class='ui-tabs-panel'>
                <table border='0' align='center' width='81%' cellspacing='0' cellpadding='5'>");

        print("<tr>
                <td class='colhead' align='center' colspan='2'>{$lang['table_head_database']}</td>
            </tr>");

        print("<tr>
                <td class='colhead' width='30%' align='center'>{$lang['table_head_task_desc']}</td>
                <td class='colhead' width='30%' align='center'>{$lang['table_head_settings']}</td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='host'><strong>{$lang['table_field_host']}</strong></label>
                    <br />{$lang['table_info_host']}
                </td>
                <td width='30%'>
                    <input type='text' name='mysql_host' id='host' size='20' value='$mysql_host' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='dbname'><strong>{$lang['table_field_dbname']}</strong></label>
                    <br />{$lang['table_info_dbname']}
                </td>
                <td width='30%'>
                    <input type='text' name='mysql_db' id='dbname' size='20' value='$mysql_db' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='user'><strong>{$lang['table_field_username']}</strong></label>
                    <br />{$lang['table_info_username']}
                </td>
                <td width='30%'>
                    <input type='text' name='mysql_user' id='user' size='20' value='$mysql_user' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='pass'><strong>{$lang['table_field_password']}</strong></label>
                    <br />{$lang['table_info_password']}
                </td>
                <td width='30%'>
                    <input type='text' name='mysql_pass' id='pass' size='20' value='$mysql_pass' />
                </td>
            </tr>");

        print("</table></div>");
        //-- Finish Database Details --//

        //-- Start Site Config Details --//
        print("<div id='fragment-2' class='ui-tabs-panel'>
                <table border='0' align='center' width='81%' cellspacing='0' cellpadding='5'>");

        print("<tr>
                <td class='colhead' align='center' colspan='2'>{$lang['table_head_general']}</td>
            </tr>");

        print("<tr>
                <td class='colhead' align='center' width='30%'>{$lang['table_head_task_desc']}</td>
                <td class='colhead' align='center' width='30%'>{$lang['table_head_settings']}</td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='url'>
                        <strong>{$lang['table_field_site_url']}</strong></label>
                        <br />{$lang['table_info_site_url']}
                </td>
                <td width='30%'>
                    <input type='text' name='site_url' id='url' size='50' value='$site_url' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='site_online'>
                        <strong>{$lang['table_field_site_online']}</strong></label>
                        <br />{$lang['table_info_site_online']}
                </td>
                <td width='30%'>
                    <input type='radio' name='site_online' id='site_online' " . ($site_online == "true" ? " checked='checked' " : "") . " value='true' />{$lang['form_opt_online']}
                    <input type='radio' name='site_online' id='site_online1' " . ($site_online == "false" ? " checked='checked' " : "") . " value='false' />{$lang['form_opt_offline']}
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='members_only'><strong>{$lang[table_field_membersonly]}</strong></label>
                    <br />{$lang[table_info_membersonly]}
                </td>
                <td width='30%'>
                    <input type='radio' name='members_only' id='members_only' " . ($members_only == "true" ? " checked='checked' " : "") . " value='true' />{$lang[form_opt_yes]}
                    <input type='radio' name='members_only' id='members_only1' " . ($members_only == "false" ? " checked='checked' " : "") . " value='false' />{$lang[form_opt_no]}
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='email'><strong>{$lang['table_field_site_email']}</strong></label>
                    <br />{$lang['table_info_site_email']}
                </td>
                <td width='30%'>
                       <input type='text' name='site_mail' id='email' size='50' value='$site_mail' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='emailconf'><strong>{$lang['table_field_confirm_signup']}</strong></label>
                    <br />{$lang['table_info_confirm_signup']}
                </td>
                <td width='30%'>
                    <input type='radio' name='email_confirm' id='emailconf' " . ($email_confirm == "true" ? " checked='checked' " : "") . " value='true' />{$lang['form_opt_yes']}
                    <input type='radio' name='email_confirm' id='emailconf1' " . ($email_confirm == "false" ? " checked='checked' " : "") . " value='false' />{$lang['form_opt_no']}
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='name'><strong>{$lang['table_field_site_name']}</strong></label>
                    <br />{$lang['table_info_site_name']}
                </td>
                <td width='30%'>
                    <input type='text' name='site_name' id='name' size='50' value='$site_name' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='image'><strong>{$lang['table_field_image_dir']}</strong></label>
                    <br />{$lang['table_info_image_dir']}
                </td>
                <td width='30%'>
                    <input type='text' name='image_dic' id='image' size='50' value='$image_dic' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='maxmem'><strong>{$lang['table_field_max_users']}</strong></label>
                    <br />{$lang['table_info_max_users']}
                </td>
                <td width='30%'>
                    <input type='text' name='max_users' id='maxmem' size='50' value='$max_members' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='max_users_then_invite'><strong>{$lang['table_field_invite_only']}</strong></label>
                    <br />{$lang['table_info_invite_only']}
                </td>
                <td width='30%'>
                    <input type='text' name='max_users_then_invite' id='max_users_then_invite' size='50' value='$max_users_then_invite' />
                </td>
            </tr>");

       print("<tr>
                <td width='30%'>
                    <label for='invites'><strong>{$lang['table_field_max_invites']}</strong></label>
                    <br />{$lang['table_info_max_invites']}
                </td>
                <td width='30%'>
                    <input type='text' name='invites' id='invites' size='50' value='$invites' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='signup_timeout'><strong>{$lang['table_field_signup_timeout']}</strong></label>
                    <br />{$lang['table_info_signup_timeout']}
                </td>
                <td width='30%'>
                    <select name='signup_timeout'>
                        <option id='signup_timeout' " . ($signup_timeout == "1" ? " selected='selected' " : "") . " value='1'>1{$lang['form_opt_day']}</option>
                        <option id='signup_timeout1' " . ($signup_timeout == "2" ? " selected='selected' " : "") . " value='2'>2{$lang['form_opt_days']}</option>
                        <option id='signup_timeout2' " . ($signup_timeout == "3" ? " selected='selected' " : "") . " value='3'>3{$lang['form_opt_days']}</option>
                        <option id='signup_timeout3' " . ($signup_timeout == "4" ? " selected='selected' " : "") . " value='4'>4{$lang['form_opt_days']}</option>
                        <option id='signup_timeout4' " . ($signup_timeout == "5" ? " selected='selected' " : "") . " value='5'>5{$lang['form_opt_days']}</option>
                        <option id='signup_timeout5' " . ($signup_timeout == "6" ? " selected='selected' " : "") . " value='6'>6{$lang['form_opt_days']}</option>
                        <option id='signup_timeout6' " . ($signup_timeout == "7" ? " selected='selected' " : "") . " value='7'>1{$lang['form_opt_week']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='min_votes'><strong>{$lang['table_field_min_votes']}</strong></label>
                    <br />{$lang['table_info_min_votes']}
                </td>
                <td width='30%'>
                    <select name='min_votes'>
                        <option id='min_votes' " . ($min_votes == "1" ? " selected='selected' " : "") . " value='1'>1{$lang['form_opt_vote']}</option>
                        <option id='min_votes1' " . ($min_votes == "2" ? " selected='selected' " : "") . " value='2'>2{$lang['form_opt_votes']}</option>
                        <option id='min_votes2' " . ($min_votes == "3" ? " selected='selected' " : "") . " value='3'>3{$lang['form_opt_votes']}</option>
                        <option id='min_votes3' " . ($min_votes == "4" ? " selected='selected' " : "") . " value='4'>4{$lang['form_opt_votes']}</option>
                        <option id='min_votes4' " . ($min_votes == "5" ? " selected='selected' " : "") . " value='5'>5{$lang['form_opt_votes']}</option>
                        <option id='min_votes5' " . ($min_votes == "6" ? " selected='selected' " : "") . " value='6'>6{$lang['form_opt_votes']}{$lang['table_opy_votes']}</option>
                        <option id='min_votes6' " . ($min_votes == "7" ? " selected='selected' " : "") . " value='7'>7{$lang['form_opt_votes']}</option>
                        <option id='min_votes7' " . ($min_votes == "8" ? " selected='selected' " : "") . " value='8'>8{$lang['form_opt_votes']}</option>
                        <option id='min_votes8' " . ($min_votes == "9" ? " selected='selected' " : "") . " value='9'>9{$lang['form_opt_votes']}</option>
                        <option id='min_votes9' " . ($min_votes == "10" ? " selected='selected' " : "") . " value='10'>10{$lang['form_opt_votes']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='clean'><strong>{$lang['table_field_autoclean']}</strong></label>
                    <br />{$lang['table_info_autoclean']}
                </td>
                <td width='30%'>
                    <select name='autoclean_interval'>
                        <option id='clean' " . ($autoclean_interval == "900" ? " selected='selected' " : "") . " value='900'>15{$lang['form_opt_mins']}</option>
                        <option id='clean1' " . ($autoclean_interval == "1800" ? " selected='selected' " : "") . " value='1800'>30{$lang['form_opt_mins']}</option>
                        <option id='clean2' " . ($autoclean_interval == "2700" ? " selected='selected' " : "") . " value='2700'>45{$lang['form_opt_mins']}</option>
                        <option id='clean3' " . ($autoclean_interval == "3600" ? " selected='selected' " : "") . " value='900'>60{$lang['form_opt_mins']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='attempts'><strong>{$lang['table_field_max_login']}</strong></label>
                    <br />{$lang['table_info_max_login']}
                </td>
                <td width='30%'>
                    <select name='maxloginattempts'>
                        <option id='attempts' " . ($max_login_attempts == "1" ? " selected='selected' " : "") . " value='1'>1</option>
                        <option id='attempts1' " . ($max_login_attempts == "2" ? " selected='selected' " : "") . " value='2'>2</option>
                        <option id='attempts2' " . ($max_login_attempts == "3" ? " selected='selected' " : "") . " value='3'>3</option>
                        <option id='attempts3' " . ($max_login_attempts == "4" ? " selected='selected' " : "") . " value='4'>4</option>
                        <option id='attempts4' " . ($max_login_attempts == "5" ? " selected='selected' " : "") . " value='5'>5</option>
                        <option id='attempts5' " . ($max_login_attempts == "6" ? " selected='selected' " : "") . " value='6'>6</option>
                        <option id='attempts6' " . ($max_login_attempts == "7" ? " selected='selected' " : "") . " value='7'>7</option>
                        <option id='attempts7' " . ($max_login_attempts == "8" ? " selected='selected' " : "") . " value='8'>8</option>
                        <option id='attempts8' " . ($max_login_attempts == "9" ? " selected='selected' " : "") . " value='9'>9</option>
                        <option id='attempts9' " . ($max_login_attempts == "10" ? " selected='selected' " : "") . " value='10'>10</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='dict'><strong>{$lang['table_field_dict']}</strong></label>
                    <br />{$lang['table_field_dict']}
                </td>
                <td width='30%'>
                    <input type='text' name='dictbreaker' id='dict' size='50' value='$dictbreaker' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='site_reputation'><strong>{$lang['table_field_reputation']}</strong></label>
                    <br />{$lang['table_info_reputation']}
                </td>
                <td width='30%'>
                    <input type='radio' name='site_reputation' id='site_reputation' " . ($site_reputation == "true" ? " checked='checked' " : "") . " value='true' />{$lang['form_opt_yes']}
                    <input type='radio' name='site_reputation' id='site_reputation1' " . ($site_reputation == "false" ? " checked='checked' " : "") . " value='false' />{$lang['form_opt_no']}
                </td>
            </tr>");

        print("</table></div>");
        //-- Finish Site Config Details --//

        //-- Start Torrent Config Details --//
        print("<div id='fragment-3' class='ui-tabs-panel'>
               <table border='0' align='center' width='81%' cellspacing='0' cellpadding='5'>");

        print("<tr>
                <td class='colhead' align='center' colspan='2'>{$lang['table_head_torrent']}</td>
            </tr>");

        print("<tr>
                <td class='colhead' align='center' width='30%'>{$lang['table_head_task_desc']}</td>
                <td class='colhead' align='center' width='30%'>{$lang['table_head_settings']}</td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='ann'><strong>{$lang['table_field_announce']}</strong></label>
                    <br />{$lang['table_info_announce']}
                </td>
                <td width='30%'>
                    <input type='text' name='announce_url' id='ann' size='50' value='$announce_url' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='tordir'><strong>{$lang['table_field_torrent_dir']}</strong></label>
                    <br />{$lang['table_info_torrent_dir']}
                </td>
                <td width='30%'>
                    <input type='text' name='torrent_dic' id='tordir' size='50' value='$torrent_dic' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='torrents_allfree'><strong>{$lang['table_field_all_free']}</strong></label>
                    <br />{$lang['table_info_all_free']}
                </td>
                <td width='30%'>
                    <input type='radio' name='torrents_allfree' id='torrents_allfree' " . ($torrents_allfree == "true" ? " checked='checked' " : "") . " value='true' />{$lang['form_opt_yes']}
                    <input type='radio' name='torrents_allfree' id='torrents_allfree1' " . ($torrents_allfree == "false" ? " checked='checked' " : "") . " value='false' />{$lang['form_opt_no']}
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='peer'><strong>{$lang['table_field_peers']}</strong></label>
                    <br />{$lang['table_info_peers']}
                </td>
                <td width='30%'>
                    <input type='text' name='peer_limit' id='peer' size='50' value='$peer_limit' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='annint'><strong>{$lang['table_field_announce_int']}</strong></label>
                    <br />{$lang['table_info_announce_int']}
                </td>
                <td width='30%'>
                    <select name='announce_interval'>
                        <option id='annint' " . ($announce_interval == "900" ? " selected='selected' " : "") . " value='900'>15{$lang['form_opt_mins']}</option>
                        <option id='annint1' " . ($announce_interval == "1800" ? " selected='selected' " : "") . " value='1800'>30{$lang['form_opt_mins']}</option>
                        <option id='annint2' " . ($announce_interval == "2700" ? " selected='selected' " : "") . " value='2700'>45{$lang['form_opt_mins']}</option>
                        <option id='annint3' " . ($announce_interval == "3600" ? " selected='selected' " : "") . " value='3600'>60{$lang['form_opt_mins']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='torsize'><strong>{$lang['table_field_max_torr_size']}</strong></label>
                    <br />{$lang['table_info_max_torr_size']}
                </td>
                <td width='30%'>
                    <input type='text' name='max_torrent_size' id='torsize' size='50' value='$max_torrent_size' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='dead'><strong>{$lang['table_field_max_dead']}</strong></label>
                    <br />{$lang['table_info_max_dead']}
                </td>
                <td width='30%'>
                    <select name='max_dead_torrent_time'>
                        <option id='dead' " . ($max_dead_torrent_time == "3" ? " selected='selected' " : "") . " value='3'>3{$lang['form_opt_hours']}</option>
                        <option id='dead2' " . ($max_dead_torrent_time == "4" ? " selected='selected' " : "") . " value='4'>4{$lang['form_opt_hours']}</option>
                        <option id='dead3' " . ($max_dead_torrent_time == "5" ? " selected='selected' " : "") . " value='5'>5{$lang['form_opt_hours']}</option>
                        <option id='dead4' " . ($max_dead_torrent_time == "6" ? " selected='selected' " : "") . " value='6'>6{$lang['form_opt_hours']}</option>
                        <option id='dead5' " . ($max_dead_torrent_time == "7" ? " selected='selected' " : "") . " value='7'>7{$lang['form_opt_hours']}</option>
                        <option id='dead6' " . ($max_dead_torrent_time == "8" ? " selected='selected' " : "") . " value='8'>8{$lang['form_opt_hours']}</option>
                        <option id='dead7' " . ($max_dead_torrent_time == "9" ? " selected='selected' " : "") . " value='9'>9{$lang['form_opt_hours']}</option>
                         <option id='dead8' " . ($max_dead_torrent_time == "10" ? " selected='selected' " : "") . " value='10'>10{$lang['form_opt_hours']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='autodel'><strong>{$lang['table_field_auto_delete']}</strong></label>
                    <br />{$lang['table_info_auto_delete']}
                </td>
                <td width='30%'>
                    <input type='radio' name='oldtorrents' id='autodel' " . ($delete_old_torrents == "1" ? " checked='checked' " : "") . " value='1' />{$lang['form_opt_yes']}
                    <input type='radio' name='oldtorrents' id='autodel1' " . ($delete_old_torrents == "0" ? " checked='checked' " : "") . " value='0' />{$lang['form_opt_no']}
                  </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='deadtor'><strong>{$lang['table_field_days_dead']}</strong></label>
                    <br />{$lang['table_info_days_dead']}
                </td>
                <td width='30%'>
                    <input type='text' name='days' id='deadtor' size='50' value='$dead_torrents' />
                </td>
            </tr>");

        print("</table><br /><br />");

        print("<table border='0' align='center' width='81%' cellspacing='0' cellpadding='5'>");

        print("<tr>
                   <td class='colhead' align='center' colspan='2'>{$lang['table_head_wait']}</td>
               </tr>");

        print("<tr>
                   <td class='colhead' align='center' width='30%'>{$lang['table_head_task_desc']}</td>
                   <td class='colhead' align='center' width='30%'>{$lang['table_head_settings']}</td>
               </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='waittime'><strong>{$lang['table_field_wait_times']}</strong></label>
                    <br />{$lang['table_info_wait_times']}
                </td>
                <td width='30%'>
                    <input type='radio' name='waittime' id='waittime' " . ($waittime == "true" ? " checked='checked' " : "") . " value='true' />{$lang['form_opt_on']}
                    <input type='radio' name='waittime' id='waittime1' " . ($waittime == "false" ? " checked='checked' " : "") . " value='false' />{$lang['form_opt_off']}
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='max_class_wait'><strong>{$lang['table_field_max_class']}</strong></label>
                    <br />{$lang['table_info_max_class']}
                </td>
                <td width='30%'>
                    <select name='max_class_wait'>
                        <option id='max_class_wait' " . ($max_class_wait == "0" ? " selected='selected' " : "") . " value='0'>{$lang['form_opt_user']}</option>
                        <option id='max_class_wait1' " . ($max_class_wait == "1" ? " selected='selected' " : "") . " value='1'>{$lang['form_opt_power_user']}</option>
                        <option id='max_class_wait2' " . ($max_class_wait == "2" ? " selected='selected' " : "") . " value='2'>{$lang['form_opt_vip']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='ratio_1'><strong>{$lang['table_info_1_wait']}</strong></label>
                    <br />{$lang['table_info_min_ratio']}
                </td>
                <td width='30%'>
                    <select name='ratio_1'>
                        <option id='ratio_1' " . ($ratio_1 == "0.1" ? " selected='selected' " : "") . " value='0.1'>0.1</option>
                        <option id='ratio_1_2' " . ($ratio_1 == "0.15" ? " selected='selected' " : "") . " value='0.15'>0.15</option>
                        <option id='ratio_1_3' " . ($ratio_1 == "0.2" ? " selected='selected' " : "") . " value='0.2'>0.2</option>
                        <option id='ratio_1_4' " . ($ratio_1 == "0.25" ? " selected='selected' " : "") . " value='0.25'>0.25</option>
                        <option id='ratio_1_5' " . ($ratio_1 == "0.3" ? " selected='selected' " : "") . " value='0.3'>0.3</option>
                        <option id='ratio_1_6' " . ($ratio_1 == "0.35" ? " selected='selected' " : "") . " value='0.35'>0.35</option>
                        <option id='ratio_1_7' " . ($ratio_1 == "0.4" ? " selected='selected' " : "") . " value='0.4'>0.4</option>
                        <option id='ratio_1_8' " . ($ratio_1 == "0.45" ? " selected='selected' " : "") . " value='0.45'>0.45</option>
                        <option id='ratio_1_9' " . ($ratio_1 == "0.5" ? " selected='selected' " : "") . " value='0.5'>0.5</option>
                        <option id='ratio_1_10' " . ($ratio_1 == "0.55" ? " selected='selected' " : "") . " value='0.55'>0.55</option>
                        <option id='ratio_1_11' " . ($ratio_1 == "0.6" ? " selected='selected' " : "") . " value='0.6'>0.6</option>
                        <option id='ratio_1_12' " . ($ratio_1 == "0.65" ? " selected='selected' " : "") . " value='0.65'>0.65</option>
                        <option id='ratio_1_13' " . ($ratio_1 == "0.7" ? " selected='selected' " : "") . " value='0.7'>0.7</option>
                        <option id='ratio_1_14' " . ($ratio_1 == "0.75" ? " selected='selected' " : "") . " value='0.75'>0.75</option>
                        <option id='ratio_1_15' " . ($ratio_1 == "0.8" ? " selected='selected' " : "") . " value='0.8'>0.8</option>
                        <option id='ratio_1_16' " . ($ratio_1 == "0.85" ? " selected='selected' " : "") . " value='0.85'>0.85</option>
                        <option id='ratio_1_17' " . ($ratio_1 == "0.9" ? " selected='selected' " : "") . " value='0.9'>0.9</option>
                        <option id='ratio_1_18' " . ($ratio_1 == "0.95" ? " selected='selected' " : "") . " value='0.95'>0.95</option>
                        <option id='ratio_1_19' " . ($ratio_1 == "1.0" ? " selected='selected' " : "") . " value='1.0'>1.0</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_ratio']}&nbsp;&nbsp;

                    <select name='gigs_1'>
                        <option id='gigs_1' " . ($gigs_1 == "1" ? " selected='selected' " : "") . " value='1'>1</option>
                        <option id='gigs_1_2' " . ($gigs_1 == "1.5" ? " selected='selected' " : "") . " value='1.5'>1.5</option>
                        <option id='gigs_1_3' " . ($gigs_1 == "2" ? " selected='selected' " : "") . " value='2'>2</option>
                        <option id='gigs_1_4' " . ($gigs_1 == "2.5" ? " selected='selected' " : "") . " value='2.5'>2.5</option>
                        <option id='gigs_1_5' " . ($gigs_1 == "3" ? " selected='selected' " : "") . " value='3'>3</option>
                        <option id='gigs_1_6' " . ($gigs_1 == "3.5" ? " selected='selected' " : "") . " value='3.5'>3.5</option>
                        <option id='gigs_1_7' " . ($gigs_1 == "4" ? " selected='selected' " : "") . " value='4'>4</option>
                        <option id='gigs_1_8' " . ($gigs_1 == "4.5" ? " selected='selected' " : "") . " value='4.5'>4.5</option>
                        <option id='gigs_1_9' " . ($gigs_1 == "5" ? " selected='selected' " : "") . " value='5'>5</option>
                        <option id='gigs_1_10' " . ($gigs_1 == "5.5" ? " selected='selected' " : "") . " value='5.5'>5.5</option>
                        <option id='gigs_1_11' " . ($gigs_1 == "6" ? " selected='selected' " : "") . " value='6'>6</option>
                        <option id='gigs_1_12' " . ($gigs_1 == "6.5" ? " selected='selected' " : "") . " value='6.5'>6.5</option>
                        <option id='gigs_1_13' " . ($gigs_1 == "7" ? " selected='selected' " : "") . " value='7'>7</option>
                        <option id='gigs_1_14' " . ($gigs_1 == "7.5" ? " selected='selected' " : "") . " value='7.5'>7.5</option>
                        <option id='gigs_1_15' " . ($gigs_1 == "8" ? " selected='selected' " : "") . " value='8'>8</option>
                        <option id='gigs_1_16' " . ($gigs_1 == "8.5" ? " selected='selected' " : "") . " value='8.5'>8.5</option>
                        <option id='gigs_1_17' " . ($gigs_1 == "9" ? " selected='selected' " : "") . " value='9'>9</option>
                        <option id='gigs_1_18' " . ($gigs_1 == "9.5" ? " selected='selected' " : "") . " value='9.5'>9.5</option>
                        <option id='gigs_1_19' " . ($gigs_1 == "10" ? " selected='selected' " : "") . " value='10'>10</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_upload']}&nbsp;&nbsp;

                    <select name='wait_1'>
                        <option id='wait_1' " . ($wait_1 == "48" ? " selected='selected' " : "") . " value='48'>48</option>
                        <option id='wait_1_2' " . ($wait_1 == "36" ? " selected='selected' " : "") . " value='36'>36</option>
                        <option id='wait_1_3' " . ($wait_1 == "24" ? " selected='selected' " : "") . " value='24'>24</option>
                        <option id='wait_1_4' " . ($wait_1 == "18" ? " selected='selected' " : "") . " value='18'>18</option>
                        <option id='wait_1_5' " . ($wait_1 == "12" ? " selected='selected' " : "") . " value='12'>12</option>
                        <option id='wait_1_6' " . ($wait_1 == "9" ? " selected='selected' " : "") . " value='9'>9</option>
                        <option id='wait_1_7' " . ($wait_1 == "6" ? " selected='selected' " : "") . " value='6'>6</option>
                        <option id='wait_1_8' " . ($wait_1 == "3" ? " selected='selected' " : "") . " value='3'>3</option>
                        <option id='wait_1_9' " . ($wait_1 == "1" ? " selected='selected' " : "") . " value='1'>1</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_wait']}

                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='ratio_2'><strong>{$lang['table_info_2_wait']}</strong></label>
                    <br />{$lang['table_info_min_ratio']}
                </td>
                <td width='30%'>
                    <select name='ratio_2'>
                        <option id='ratio_2' " . ($ratio_2 == "0.1" ? " selected='selected' " : "") . " value='0.1'>0.1</option>
                        <option id='ratio_2_1' " . ($ratio_2 == "0.15" ? " selected='selected' " : "") . " value='0.15'>0.15</option>
                        <option id='ratio_2_2' " . ($ratio_2 == "0.2" ? " selected='selected' " : "") . " value='0.2'>0.2</option>
                        <option id='ratio_2_3' " . ($ratio_2 == "0.25" ? " selected='selected' " : "") . " value='0.25'>0.25</option>
                        <option id='ratio_2_4' " . ($ratio_2 == "0.3" ? " selected='selected' " : "") . " value='0.3'>0.3</option>
                        <option id='ratio_2_5' " . ($ratio_2 == "0.35" ? " selected='selected' " : "") . " value='0.35'>0.35</option>
                        <option id='ratio_2_6' " . ($ratio_2 == "0.4" ? " selected='selected' " : "") . " value='0.4'>0.4</option>
                        <option id='ratio_2_7' " . ($ratio_2 == "0.45" ? " selected='selected' " : "") . " value='0.45'>0.45</option>
                        <option id='ratio_2_8' " . ($ratio_2 == "0.5" ? " selected='selected' " : "") . " value='0.5'>0.5</option>
                        <option id='ratio_2_9' " . ($ratio_2 == "0.55" ? " selected='selected' " : "") . " value='0.55'>0.55</option>
                        <option id='ratio_2_10' " . ($ratio_2 == "0.6" ? " selected='selected' " : "") . " value='0.6'>0.6</option>
                        <option id='ratio_2_11' " . ($ratio_2 == "0.65" ? " selected='selected' " : "") . " value='0.65'>0.65</option>
                        <option id='ratio_2_12' " . ($ratio_2 == "0.7" ? " selected='selected' " : "") . " value='0.7'>0.7</option>
                        <option id='ratio_2_13' " . ($ratio_2 == "0.75" ? " selected='selected' " : "") . " value='0.75'>0.75</option>
                        <option id='ratio_2_14' " . ($ratio_2 == "0.8" ? " selected='selected' " : "") . " value='0.8'>0.8</option>
                        <option id='ratio_2_15' " . ($ratio_2 == "0.85" ? " selected='selected' " : "") . " value='0.85'>0.85</option>
                        <option id='ratio_2_16' " . ($ratio_2 == "0.9" ? " selected='selected' " : "") . " value='0.9'>0.9</option>
                        <option id='ratio_2_17' " . ($ratio_2 == "0.95" ? " selected='selected' " : "") . " value='0.95'>0.95</option>
                        <option id='ratio_2_18' " . ($ratio_2 == "1.0" ? " selected='selected' " : "") . " value='1.0'>1.0</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_ratio']}&nbsp;&nbsp;

                    <select name='gigs_2'>
                        <option id='gigs_2' " . ($gigs_2 == "1" ? " selected='selected' " : "") . " value='1'>1</option>
                        <option id='gigs_2_2' " . ($gigs_2 == "1.5" ? " selected='selected' " : "") . " value='1.5'>1.5</option>
                        <option id='gigs_2_3' " . ($gigs_2 == "2" ? " selected='selected' " : "") . " value='2'>2</option>
                        <option id='gigs_2_4' " . ($gigs_2 == "2.5" ? " selected='selected' " : "") . " value='2.5'>2.5</option>
                        <option id='gigs_2_5' " . ($gigs_2 == "3" ? " selected='selected' " : "") . " value='3'>3</option>
                        <option id='gigs_2_6' " . ($gigs_2 == "3.5" ? " selected='selected' " : "") . " value='3.5'>3.5</option>
                        <option id='gigs_2_7' " . ($gigs_2 == "4" ? " selected='selected' " : "") . " value='4'>4</option>
                        <option id='gigs_2_8' " . ($gigs_2 == "4.5" ? " selected='selected' " : "") . " value='4.5'>4.5</option>
                        <option id='gigs_2_9' " . ($gigs_2 == "5" ? " selected='selected' " : "") . " value='5'>5</option>
                        <option id='gigs_2_10' " . ($gigs_2 == "5.5" ? " selected='selected' " : "") . " value='5.5'>5.5</option>
                        <option id='gigs_2_11' " . ($gigs_2 == "6" ? " selected='selected' " : "") . " value='6'>6</option>
                        <option id='gigs_2_12' " . ($gigs_2 == "6.5" ? " selected='selected' " : "") . " value='6.5'>6.5</option>
                        <option id='gigs_2_13' " . ($gigs_2 == "7" ? " selected='selected' " : "") . " value='7'>7</option>
                        <option id='gigs_2_14' " . ($gigs_2 == "7.5" ? " selected='selected' " : "") . " value='7.5'>7.5</option>
                        <option id='gigs_2_15' " . ($gigs_2 == "8" ? " selected='selected' " : "") . " value='8'>8</option>
                        <option id='gigs_2_16' " . ($gigs_2 == "8.5" ? " selected='selected' " : "") . " value='8.5'>8.5</option>
                        <option id='gigs_2_17' " . ($gigs_2 == "9" ? " selected='selected' " : "") . " value='9'>9</option>
                        <option id='gigs_2_18' " . ($gigs_2 == "9.5" ? " selected='selected' " : "") . " value='9.5'>9.5</option>
                        <option id='gigs_2_19' " . ($gigs_2 == "10" ? " selected='selected' " : "") . " value='10'>10</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_upload']}&nbsp;&nbsp;

                    <select name='wait_2'>
                        <option id='wait_2' " . ($wait_2 == "48" ? " selected='selected' " : "") . " value='48'>48</option>
                        <option id='wait_2_1' " . ($wait_2 == "36" ? " selected='selected' " : "") . " value='36'>36</option>
                        <option id='wait_2_2' " . ($wait_2 == "24" ? " selected='selected' " : "") . " value='24'>24</option>
                        <option id='wait_2_3' " . ($wait_2 == "18" ? " selected='selected' " : "") . " value='18'>18</option>
                        <option id='wait_2_4' " . ($wait_2 == "12" ? " selected='selected' " : "") . " value='12'>12</option>
                        <option id='wait_2_5' " . ($wait_2 == "9" ? " selected='selected' " : "") . " value='9'>9</option>
                        <option id='wait_2_6' " . ($wait_2 == "6" ? " selected='selected' " : "") . " value='6'>6</option>
                        <option id='wait_2_7' " . ($wait_2 == "3" ? " selected='selected' " : "") . " value='3'>3</option>
                        <option id='wait_2_8' " . ($wait_2 == "1" ? " selected='selected' " : "") . " value='1'>1</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_wait']}

                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='ratio_3'><strong>{$lang['table_info_3_wait']}</strong></label>
                    <br />{$lang['table_info_min_ratio']}
                </td>
                <td width='30%'>
                    <select name='ratio_3'>
                        <option id='ratio_3' " . ($ratio_3 == "0.1" ? " selected='selected' " : "") . " value='0.1'>0.1</option>
                        <option id='ratio_3_1' " . ($ratio_3 == "0.15" ? " selected='selected' " : "") . " value='0.15'>0.15</option>
                        <option id='ratio_3_2' " . ($ratio_3 == "0.2" ? " selected='selected' " : "") . " value='0.2'>0.2</option>
                        <option id='ratio_3_3' " . ($ratio_3 == "0.25" ? " selected='selected' " : "") . " value='0.25'>0.25</option>
                        <option id='ratio_3_4' " . ($ratio_3 == "0.3" ? " selected='selected' " : "") . " value='0.3'>0.3</option>
                        <option id='ratio_3_5' " . ($ratio_3 == "0.35" ? " selected='selected' " : "") . " value='0.35'>0.35</option>
                        <option id='ratio_3_6' " . ($ratio_3 == "0.4" ? " selected='selected' " : "") . " value='0.4'>0.4</option>
                        <option id='ratio_3_7' " . ($ratio_3 == "0.45" ? " selected='selected' " : "") . " value='0.45'>0.45</option>
                        <option id='ratio_3_8' " . ($ratio_3 == "0.5" ? " selected='selected' " : "") . " value='0.5'>0.5</option>
                        <option id='ratio_3_9' " . ($ratio_3 == "0.55" ? " selected='selected' " : "") . " value='0.55'>0.55</option>
                        <option id='ratio_3_10' " . ($ratio_3 == "0.6" ? " selected='selected' " : "") . " value='0.6'>0.6</option>
                        <option id='ratio_3_11' " . ($ratio_3 == "0.65" ? " selected='selected' " : "") . " value='0.65'>0.65</option>
                        <option id='ratio_3_12' " . ($ratio_3 == "0.7" ? " selected='selected' " : "") . " value='0.7'>0.7</option>
                        <option id='ratio_3_13' " . ($ratio_3 == "0.75" ? " selected='selected' " : "") . " value='0.75'>0.75</option>
                        <option id='ratio_3_14' " . ($ratio_3 == "0.8" ? " selected='selected' " : "") . " value='0.8'>0.8</option>
                        <option id='ratio_3_15' " . ($ratio_3 == "0.85" ? " selected='selected' " : "") . " value='0.85'>0.85</option>
                        <option id='ratio_3_16' " . ($ratio_3 == "0.9" ? " selected='selected' " : "") . " value='0.9'>0.9</option>
                        <option id='ratio_3_17' " . ($ratio_3 == "0.95" ? " selected='selected' " : "") . " value='0.95'>0.95</option>
                        <option id='ratio_3_18' " . ($ratio_3 == "1.0" ? " selected='selected' " : "") . " value='1.0'>1.0</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_ratio']}&nbsp;&nbsp;

                    <select name='gigs_3'>
                        <option id='gigs_3' " . ($gigs_3 == "1" ? " selected='selected' " : "") . " value='1'>1</option>
                        <option id='gigs_3_1' " . ($gigs_3 == "1.5" ? " selected='selected' " : "") . " value='1.5'>1.5</option>
                        <option id='gigs_3_2' " . ($gigs_3 == "2" ? " selected='selected' " : "") . " value='2'>2</option>
                        <option id='gigs_3_3' " . ($gigs_3 == "2.5" ? " selected='selected' " : "") . " value='2.5'>2.5</option>
                        <option id='gigs_3_4' " . ($gigs_3 == "3" ? " selected='selected' " : "") . " value='3'>3</option>
                        <option id='gigs_3_5' " . ($gigs_3 == "3.5" ? " selected='selected' " : "") . " value='3.5'>3.5</option>
                        <option id='gigs_3_6' " . ($gigs_3 == "4" ? " selected='selected' " : "") . " value='4'>4</option>
                        <option id='gigs_3_7' " . ($gigs_3 == "4.5" ? " selected='selected' " : "") . " value='4.5'>4.5</option>
                        <option id='gigs_3_8' " . ($gigs_3 == "5" ? " selected='selected' " : "") . " value='5'>5</option>
                        <option id='gigs_3_9' " . ($gigs_3 == "5.5" ? " selected='selected' " : "") . " value='5.5'>5.5</option>
                        <option id='gigs_3_10' " . ($gigs_3 == "6" ? " selected='selected' " : "") . " value='6'>6</option>
                        <option id='gigs_3_11' " . ($gigs_3 == "6.5" ? " selected='selected' " : "") . " value='6.5'>6.5</option>
                        <option id='gigs_3_12' " . ($gigs_3 == "7" ? " selected='selected' " : "") . " value='7'>7</option>
                        <option id='gigs_3_13' " . ($gigs_3 == "7.5" ? " selected='selected' " : "") . " value='7.5'>7.5</option>
                        <option id='gigs_3_14' " . ($gigs_3 == "8" ? " selected='selected' " : "") . " value='8'>8</option>
                        <option id='gigs_3_15' " . ($gigs_3 == "8.5" ? " selected='selected' " : "") . " value='8.5'>8.5</option>
                        <option id='gigs_3_16' " . ($gigs_3 == "9" ? " selected='selected' " : "") . " value='9'>9</option>
                        <option id='gigs_3_17' " . ($gigs_3 == "9.5" ? " selected='selected' " : "") . " value='9.5'>9.5</option>
                        <option id='gigs_3_18' " . ($gigs_3 == "10" ? " selected='selected' " : "") . " value='10'>10</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_upload']}&nbsp;&nbsp;

                    <select name='wait_3'>
                        <option id='wait_3' " . ($wait_3 == "48" ? " selected='selected' " : "") . " value='48'>48</option>
                        <option id='wait_3_1' " . ($wait_3 == "36" ? " selected='selected' " : "") . " value='36'>36</option>
                        <option id='wait_3_2' " . ($wait_3 == "24" ? " selected='selected' " : "") . " value='24'>24</option>
                        <option id='wait_3_3' " . ($wait_3 == "18" ? " selected='selected' " : "") . " value='18'>18</option>
                        <option id='wait_3_4' " . ($wait_3 == "12" ? " selected='selected' " : "") . " value='12'>12</option>
                        <option id='wait_3_5' " . ($wait_3 == "9" ? " selected='selected' " : "") . " value='9'>9</option>
                        <option id='wait_3_6' " . ($wait_3 == "6" ? " selected='selected' " : "") . " value='6'>6</option>
                        <option id='wait_3_7' " . ($wait_3 == "3" ? " selected='selected' " : "") . " value='3'>3</option>
                        <option id='wait_3_8' " . ($wait_3 == "1" ? " selected='selected' " : "") . " value='1'>1</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_wait']}

                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='ratio_4'><strong>{$lang['table_info_4_wait']}</strong></label>
                    <br />{$lang['table_info_min_ratio']}
                </td>
                <td width='30%'>
                    <select name='ratio_4'>
                        <option id='ratio_4' " . ($ratio_4 == "0.1" ? " selected='selected' " : "") . " value='0.1'>0.1</option>
                        <option id='ratio_4_1' " . ($ratio_4 == "0.15" ? " selected='selected' " : "") . " value='0.15'>0.15</option>
                        <option id='ratio_4_2' " . ($ratio_4 == "0.2" ? " selected='selected' " : "") . " value='0.2'>0.2</option>
                        <option id='ratio_4_3' " . ($ratio_4 == "0.25" ? " selected='selected' " : "") . " value='0.25'>0.25</option>
                        <option id='ratio_4_4' " . ($ratio_4 == "0.3" ? " selected='selected' " : "") . " value='0.3'>0.3</option>
                        <option id='ratio_4_5' " . ($ratio_4 == "0.35" ? " selected='selected' " : "") . " value='0.35'>0.35</option>
                        <option id='ratio_4_6' " . ($ratio_4 == "0.4" ? " selected='selected' " : "") . " value='0.4'>0.4</option>
                        <option id='ratio_4_7' " . ($ratio_4 == "0.45" ? " selected='selected' " : "") . " value='0.45'>0.45</option>
                        <option id='ratio_4_8' " . ($ratio_4 == "0.5" ? " selected='selected' " : "") . " value='0.5'>0.5</option>
                        <option id='ratio_4_9' " . ($ratio_4 == "0.55" ? " selected='selected' " : "") . " value='0.55'>0.55</option>
                        <option id='ratio_4_10' " . ($ratio_4 == "0.6" ? " selected='selected' " : "") . " value='0.6'>0.6</option>
                        <option id='ratio_4_11' " . ($ratio_4 == "0.65" ? " selected='selected' " : "") . " value='0.65'>0.65</option>
                        <option id='ratio_4_12' " . ($ratio_4 == "0.7" ? " selected='selected' " : "") . " value='0.7'>0.7</option>
                        <option id='ratio_4_13' " . ($ratio_4 == "0.75" ? " selected='selected' " : "") . " value='0.75'>0.75</option>
                        <option id='ratio_4_14' " . ($ratio_4 == "0.8" ? " selected='selected' " : "") . " value='0.8'>0.8</option>
                        <option id='ratio_4_15' " . ($ratio_4 == "0.85" ? " selected='selected' " : "") . " value='0.85'>0.85</option>
                        <option id='ratio_4_16' " . ($ratio_4 == "0.9" ? " selected='selected' " : "") . " value='0.9'>0.9</option>
                        <option id='ratio_4_17' " . ($ratio_4 == "0.95" ? " selected='selected' " : "") . " value='0.95'>0.95</option>
                        <option id='ratio_4_19' " . ($ratio_4 == "1.0" ? " selected='selected' " : "") . " value='1.0'>1.0</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_ratio']}&nbsp;&nbsp;

                    <select name='gigs_4'>
                        <option id='gigs_4' " . ($gigs_4 == "1" ? " selected='selected' " : "") . " value='1'>1</option>
                        <option id='gigs_4_1' " . ($gigs_4 == "1.5" ? " selected='selected' " : "") . " value='1.5'>1.5</option>
                        <option id='gigs_4_2' " . ($gigs_4 == "2" ? " selected='selected' " : "") . " value='2'>2</option>
                        <option id='gigs_4_3' " . ($gigs_4 == "2.5" ? " selected='selected' " : "") . " value='2.5'>2.5</option>
                        <option id='gigs_4_4' " . ($gigs_4 == "3" ? " selected='selected' " : "") . " value='3'>3</option>
                        <option id='gigs_4_5' " . ($gigs_4 == "3.5" ? " selected='selected' " : "") . " value='3.5'>3.5</option>
                        <option id='gigs_4_6' " . ($gigs_4 == "4" ? " selected='selected' " : "") . " value='4'>4</option>
                        <option id='gigs_4_7' " . ($gigs_4 == "4.5" ? " selected='selected' " : "") . " value='4.5'>4.5</option>
                        <option id='gigs_4_8' " . ($gigs_4 == "5" ? " selected='selected' " : "") . " value='5'>5</option>
                        <option id='gigs_4_9' " . ($gigs_4 == "5.5" ? " selected='selected' " : "") . " value='5.5'>5.5</option>
                        <option id='gigs_4_10' " . ($gigs_4 == "6" ? " selected='selected' " : "") . " value='6'>6</option>
                        <option id='gigs_4_11' " . ($gigs_4 == "6.5" ? " selected='selected' " : "") . " value='6.5'>6.5</option>
                        <option id='gigs_4_12' " . ($gigs_4 == "7" ? " selected='selected' " : "") . " value='7'>7</option>
                        <option id='gigs_4_13' " . ($gigs_4 == "7.5" ? " selected='selected' " : "") . " value='7.5'>7.5</option>
                        <option id='gigs_4_14' " . ($gigs_4 == "8" ? " selected='selected' " : "") . " value='8'>8</option>
                        <option id='gigs_4_15' " . ($gigs_4 == "8.5" ? " selected='selected' " : "") . " value='8.5'>8.5</option>
                        <option id='gigs_4_16' " . ($gigs_4 == "9" ? " selected='selected' " : "") . " value='9'>9</option>
                        <option id='gigs_4_17' " . ($gigs_4 == "9.5" ? " selected='selected' " : "") . " value='9.5'>9.5</option>
                        <option id='gigs_4_18' " . ($gigs_4 == "10" ? " selected='selected' " : "") . " value='10'>10</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_upload']}&nbsp;&nbsp;

                    <select name='wait_4'>
                        <option id='wait_4' " . ($wait_4 == "48" ? " selected='selected' " : "") . " value='48'>48</option>
                        <option id='wait_4_1' " . ($wait_4 == "36" ? " selected='selected' " : "") . " value='36'>36</option>
                        <option id='wait_4_2' " . ($wait_4 == "24" ? " selected='selected' " : "") . " value='24'>24</option>
                        <option id='wait_4_3' " . ($wait_4 == "18" ? " selected='selected' " : "") . " value='18'>18</option>
                        <option id='wait_4_4' " . ($wait_4 == "12" ? " selected='selected' " : "") . " value='12'>12</option>
                        <option id='wait_4_5' " . ($wait_4 == "9" ? " selected='selected' " : "") . " value='9'>9</option>
                        <option id='wait_4_6' " . ($wait_4 == "6" ? " selected='selected' " : "") . " value='6'>6</option>
                        <option id='wait_4_7' " . ($wait_4 == "3" ? " selected='selected' " : "") . " value='3'>3</option>
                        <option id='wait_4_8' " . ($wait_4 == "1" ? " selected='selected' " : "") . " value='1'>1</option>
                    </select>

                    &nbsp;&nbsp;{$lang['form_opt_wait']}

                </td>
            </tr>");

        print("</table></div>");
        //-- Finish Torrent Config Details --//

        //-- Start Forum Config Details --//
        print("<div id='fragment-4' class='ui-tabs-panel'>
               <table border='0' align='center' width='81%' cellspacing='0' cellpadding='5'>");

        print("<tr>
                   <td class='colhead' align='center' colspan='2'>{$lang['table_head_forum']}</td>
               </tr>");

        print("<tr>
                   <td class='colhead' align='center' width='30%'>{$lang['table_head_task_desc']}</td>
                   <td class='colhead' align='center' width='30%'>{$lang['table_head_settings']}</td>
               </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='filesize'><strong>{$lang['table_field_max_upload']}</strong></label>
                    <br />{$lang['table_info_max_upload']}
                </td>
                <td width='30%'>
                    <select name='maxfilesize'>
                        <option id='filesize' " . ($maxfilesize == "1" ? " selected='selected' " : "") . " value='1'>1{$lang['form_opt_mb']}</option>
                        <option id='filesize1' " . ($maxfilesize == "2" ? " selected='selected' " : "") . " value='2'>2{$lang['form_opt_mb']}</option>
                        <option id='filesize2' " . ($maxfilesize == "3" ? " selected='selected' " : "") . " value='3'>3{$lang['form_opt_mb']}</option>
                        <option id='filesize3' " . ($maxfilesize == "4" ? " selected='selected' " : "") . " value='4'>4{$lang['form_opt_mb']}</option>
                        <option id='filesize4' " . ($maxfilesize == "5" ? " selected='selected' " : "") . " value='5'>5{$lang['form_opt_mb']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='attdir'><strong>{$lang['table_field_attach_dir']}</strong></label>
                    <br />{$lang['table_info_attach_dir']}
                </td>
                <td width='30%'>
                    <input type='text' name='attachment_dir' id='attdir' size='50' value='$attachment_dir' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='width'><strong>{$lang['table_field_forum_width']}</strong></label>
                    <br />{$lang['table_info_forum_width']}
                </td>
                <td width='30%'>
                    <input type='text' name='forum_width' id='width' size='50' value='$forum_width' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='forname'><strong>{$lang['table_field_max_length']}</strong></label>
                    <br />{$lang['table_info_max_length']}
                </td>
                <td width='30%'>
                    <input type='text' name='maxsubjectlength' id='forname' size='50' value='$maxsubjectlength' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='postread'><strong>{$lang['table_field_post_expiry']}</strong></label>
                    <br />{$lang['table_info_post_expiry']}
                </td>
                <td width='30%'>
                    <select name='posts_read_expiry'>
                        <option id='postread' " . ($posts_read_expiry == "7" ? " selected='selected' " : "") . " value='7'>7{$lang['form_opt_days']}</option>
                        <option id='postread1' " . ($posts_read_expiry == "14" ? " selected='selected' " : "") . " value='14'>14{$lang['form_opt_days']}</option>
                        <option id='postread2' " . ($posts_read_expiry == "21" ? " selected='selected' " : "") . " value='21'>21{$lang['form_opt_days']}</option>
                        <option id='postread3' " . ($posts_read_expiry == "28" ? " selected='selected' " : "") . " value='28'>28{$lang['form_opt_days']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='ppp'><strong>{$lang['table_field_post_page']}</strong></label>
                    <br />{$lang['table_info_post_page']}
                </td>
                <td width='30%'>
                    <input type='text' name='postsperpage' id='ppp' size='50' value='$postsperpage' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='attach'><strong>{$lang['table_field_attach_mod']}</strong></label>
                    <br />{$lang['table_info_attach_mod']}
                </td>
                <td width='30%'>
                    <input type='radio' name='use_attachment_mod' id='attach' " . ($use_attachment_mod == "true" ? " checked='checked' " : "") . " value='true' />{$lang['form_opt_on']}
                    <input type='radio' name='use_attachment_mod' id='attach1' " . ($use_attachment_mod == "false" ? " checked='checked' " : "") . " value='false' />{$lang['form_opt_off']}
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='poll'><strong>{$lang['table_field_forum_poll']}</strong></label>
                    <br />{$lang['table_info_forum_poll']}
                </td>
                <td width='30%'>
                    <input type='radio' name='use_poll_mod' id='poll' " . ($use_poll_mod == "true" ? " checked='checked' " : "") . " value='true' />{$lang['form_opt_on']}
                    <input type='radio' name='use_poll_mod' id='poll1' " . ($use_poll_mod == "false" ? " checked='checked' " : "") . " value='false' />{$lang['form_opt_off']}
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='stats'><strong>{$lang['table_field_forum_stats']}</strong></label>
                    <br />{$lang['table_info_forum_stats']}
                </td>
                <td width='30%'>
                    <input type='radio' name='forum_stats_mod' id='stats' " . ($forum_stats_mod == "true" ? " checked='checked' " : "") . " value='true' />{$lang['form_opt_on']}
                    <input type='radio' name='forum_stats_mod' id='stats1' " . ($forum_stats_mod == "false" ? " checked='checked' " : "") . " value='false' />{$lang['form_opt_off']}
                </td>
            </tr>");

        print("</table><br />");

        print("<div align='center'><span class='flood_info_tracker_manager'>{$lang['text_info_flood']}</span></div><br />");

        print("<table border='0' align='center' width='81%' cellspacing='0' cellpadding='5'>");

        print("<tr>
                <td class='colhead' align='center' colspan='2'>{$lang['table_head_flood']}</td>
            </tr>");

        print("<tr>
                <td class='colhead' align='center' width='30%'>{$lang['table_head_task_desc']}</td>
                <td class='colhead' align='center' width='30%'>{$lang['table_head_settings']}</td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='flood'><strong>{$lang['table_field_forum_flood']}</strong></label>
                    <br />{$lang['table_info_forum_flood']}
                </td>
                <td width='30%'>
                    <input type='radio' name='use_flood_mod' id='flood' " . ($use_flood_mod == "true" ? " checked='checked' " : "") . " value='true' />{$lang['form_opt_on']}
                    <input type='radio' name='use_flood_mod' id='flood1' " . ($use_flood_mod == "false" ? " checked='checked' " : "") . " value='false' />{$lang['form_opt_off']}
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='posts'><strong>{$lang['table_field_post_limit']}</strong></label>
                    <br />{$lang['table_info_post_limit']}
                </td>
                <td width='30%'>
                    <input type='text' name='limmit' id='posts' size='50' value='$limmit' />
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='time'><strong>{$lang['table_field_post_time']}</strong></label>
                    <br />{$lang['table_info_post_time']}
                </td>
                <td width='30%'>
                    <input type='text' name='minutes' id='time' size='50' value='$minutes' />
                </td>
            </tr>");

        print("</table></div>");
        //-- Finish Forum Config Details-- //

        //-- Start Cleanup Config Details --//
        print("<div id='fragment-5' class='ui-tabs-panel'>
               <table border='0' align='center' width='81%' cellspacing='0' cellpadding='5'>");

        print("<tr>
                <td class='colhead' align='center' colspan='2'>{$lang['table_head_cleanup']}</td>
            </tr>");

        print("<tr>
                <td class='colhead' align='center' width='30%'>{$lang['table_head_task_desc']}</td>
                <td class='colhead' align='center' width='30%'>{$lang['table_head_settings']}</td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='staff_log'><strong>{$lang['table_field_staff_log']}</strong></label>
                    <br />{$lang['table_info_logs']}
                </td>
                <td width='30%'>
                    <select name='staff_log'>
                        <option id='staff_log' " . ($staff_log == "1" ? " selected='selected' " : "") . " value='1'>1{$lang['form_opt_day']}</option>
                        <option id='staff_log1' " . ($staff_log == "2" ? " selected='selected' " : "") . " value='2'>2{$lang['form_opt_days']}</option>
                        <option id='staff_log2' " . ($staff_log == "3" ? " selected='selected' " : "") . " value='3'>3{$lang['form_opt_days']}</option>
                        <option id='staff_log3' " . ($staff_log == "4" ? " selected='selected' " : "") . " value='4'>4{$lang['form_opt_days']}</option>
                        <option id='staff_log4' " . ($staff_log == "5" ? " selected='selected' " : "") . " value='5'>5{$lang['form_opt_days']}</option>
                        <option id='staff_log5' " . ($staff_log == "6" ? " selected='selected' " : "") . " value='6'>6{$lang['form_opt_days']}</option>
                        <option id='staff_log6' " . ($staff_log == "7" ? " selected='selected' " : "") . " value='7'>1{$lang['form_opt_week']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='site_log'><strong>{$lang['table_field_site_log']}</strong></label>
                    <br />{$lang['table_info_logs']}
                </td>
                <td width='30%'>
                    <select name='site_log'>
                        <option id='site_log' " . ($site_log == "1" ? " selected='selected' " : "") . " value='1'>1{$lang['form_opt_day']}</option>
                        <option id='site_log1' " . ($site_log == "2" ? " selected='selected' " : "") . " value='2'>2{$lang['form_opt_days']}</option>
                        <option id='site_log2' " . ($site_log == "3" ? " selected='selected' " : "") . " value='3'>3{$lang['form_opt_days']}</option>
                        <option id='site_log3' " . ($site_log == "4" ? " selected='selected' " : "") . " value='4'>4{$lang['form_opt_days']}</option>
                        <option id='site_log4' " . ($site_log == "5" ? " selected='selected' " : "") . " value='5'>5{$lang['form_opt_days']}</option>
                        <option id='site_log5' " . ($site_log == "6" ? " selected='selected' " : "") . " value='6'>6{$lang['form_opt_days']}</option>
                        <option id='site_log6' " . ($site_log == "7" ? " selected='selected' " : "") . " value='7'>1{$lang['form_opt_week']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='parked_users'><strong>{$lang['table_field_parked']}</strong></label>
                    <br />{$lang['table_info_parked']}
                </td>
                <td width='30%'>
                    <select name='parked_users'>
                        <option id='parked_users' " . ($parked_users == "125" ? " selected='selected' " : "") . " value='125'>125{$lang['form_opt_days']}</option>
                        <option id='parked_users1' " . ($parked_users == "150" ? " selected='selected' " : "") . " value='150'>150{$lang['form_opt_days']}</option>
                        <option id='parked_users2' " . ($parked_users == "175" ? " selected='selected' " : "") . " value='175'>175{$lang['form_opt_days']}</option>
                        <option id='parked_users3' " . ($parked_users == "200" ? " selected='selected' " : "") . " value='200'>200{$lang['form_opt_days']}</option>
                        <option id='parked_users4' " . ($parked_users == "225" ? " selected='selected' " : "") . " value='225'>225{$lang['form_opt_days']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='inactive_users'><strong>{$lang['table_field_inact_acc']}</strong></label>
                    <br />{$lang['table_info_inact_acc']}
                </td>
                <td width='30%'>
                    <select name='inactive_users'>
                        <option id='inactive_users' " . ($inactive_users == "30" ? " selected='selected' " : "") . " value='30'>30{$lang['form_opt_days']}</option>
                        <option id='inactive_users1' " . ($inactive_users == "40" ? " selected='selected' " : "") . " value='40'>40{$lang['form_opt_days']}</option>
                        <option id='inactive_users2' " . ($inactive_users == "50" ? " selected='selected' " : "") . " value='50'>50{$lang['form_opt_days']}</option>
                        <option id='inactive_users3' " . ($inactive_users == "60" ? " selected='selected' " : "") . " value='60'>60{$lang['form_opt_days']}</option>
                        <option id='inactive_users4' " . ($inactive_users == "70" ? " selected='selected' " : "") . " value='70'>70{$lang['form_opt_days']}</option>
                       </select>
                   </td>
               </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='old_login_attempts'><strong>{$lang['table_field_old_logins']}</strong></label>
                    <br />{$lang['table_info_old_logins']}
                </td>
                <td width='30%'>
                    <select name='old_login_attempts'>
                        <option id='old_login_attempts' " . ($old_login_attempts == "1" ? " selected='selected' " : "") . " value='1'>1{$lang['form_opt_day']}</option>
                        <option id='old_login_attempts1' " . ($old_login_attempts == "2" ? " selected='selected' " : "") . " value='2'>2{$lang['form_opt_days']}</option>
                        <option id='old_login_attempts2' " . ($old_login_attempts == "3" ? " selected='selected' " : "") . " value='3'>3{$lang['form_opt_days']}</option>
                        <option id='old_login_attempts3' " . ($old_login_attempts == "4" ? " selected='selected' " : "") . " value='4'>4{$lang['form_opt_days']}</option>
                        <option id='old_login_attempts4' " . ($old_login_attempts == "5" ? " selected='selected' " : "") . " value='5'>5{$lang['form_opt_days']}</option>
                        <option id='old_login_attempts5' " . ($old_login_attempts == "6" ? " selected='selected' " : "") . " value='6'>6{$lang['form_opt_days']}</option>
                        <option id='old_login_attempts6' " . ($old_login_attempts == "7" ? " selected='selected' " : "") . " value='7'>1{$lang['form_opt_week']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='old_help_desk'><strong>{$lang['table_field_old_help']}</strong></label>
                    <br />{$lang['table_info_old_help']}
                </td>
                <td width='30%'>
                    <select name='old_help_desk'>
                        <option id='old_help_desk' " . ($old_help_desk == "1" ? " selected='selected' " : "") . " value='1'>1{$lang['form_opt_day']}</option>
                        <option id='old_help_desk1' " . ($old_help_desk == "2" ? " selected='selected' " : "") . " value='2'>2{$lang['form_opt_days']}</option>
                        <option id='old_help_desk2' " . ($old_help_desk == "3" ? " selected='selected' " : "") . " value='3'>3{$lang['form_opt_days']}</option>
                        <option id='old_help_desk3' " . ($old_help_desk == "4" ? " selected='selected' " : "") . " value='4'>4{$lang['form_opt_days']}</option>
                        <option id='old_help_desk4' " . ($old_help_desk == "5" ? " selected='selected' " : "") . " value='5'>5{$lang['form_opt_days']}</option>
                        <option id='old_help_desk5' " . ($old_help_desk == "6" ? " selected='selected' " : "") . " value='6'>6{$lang['form_opt_days']}</option>
                        <option id='old_help_desk6' " . ($old_help_desk == "7" ? " selected='selected' " : "") . " value='7'>1{$lang['form_opt_week']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='promote_upload'><strong>{$lang['table_field_auto_promote']}</strong></label>
                    <br />{$lang['table_info_auto_promote']}
                </td>
                <td width='30%'>
                    <select name='promote_upload'>
                        <option id='promote_upload' " . ($promote_upload == "20" ? " selected='selected' " : "") . " value='20'>20{$lang['form_opt_gb']}</option>
                        <option id='promote_upload1' " . ($promote_upload == "25" ? " selected='selected' " : "") . " value='25'>25{$lang['form_opt_gb']}</option>
                        <option id='promote_upload2' " . ($promote_upload == "30" ? " selected='selected' " : "") . " value='30'>30{$lang['form_opt_gb']}</option>
                        <option id='promote_upload3' " . ($promote_upload == "35" ? " selected='selected' " : "") . " value='35'>35{$lang['form_opt_gb']}</option>
                        <option id='promote_upload4' " . ($promote_upload == "40" ? " selected='selected' " : "") . " value='40'>40{$lang['form_opt_gb']}</option>
                        <option id='promote_upload5' " . ($promote_upload == "45" ? " selected='selected' " : "") . " value='45'>45{$lang['form_opt_gb']}</option>
                        <option id='promote_upload6' " . ($promote_upload == "50" ? " selected='selected' " : "") . " value='50'>50{$lang['form_opt_gb']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='promote_ratio'><strong>{$lang['table_field_min_ratio_pu']}</strong></label>
                    <br />{$lang['table_info_min_ratio_pu']}
                </td>
                <td width='30%'>
                    <select name='promote_ratio'>
                        <option id='promote_ratio' " . ($promote_ratio == "1.00" ? " selected='selected' " : "") . " value='1.00'>1.00</option>
                        <option id='promote_ratio1' " . ($promote_ratio == "1.05" ? " selected='selected' " : "") . " value='1.05'>1.05</option>
                        <option id='promote_ratio2' " . ($promote_ratio == "1.10" ? " selected='selected' " : "") . " value='1.10'>1.10</option>
                        <option id='promote_ratio3' " . ($promote_ratio == "1.15" ? " selected='selected' " : "") . " value='1.15'>1.15</option>
                        <option id='promote_ratio4' " . ($promote_ratio == "1.20" ? " selected='selected' " : "") . " value='1.20'>1.20</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='promote_time_member'><strong>{$lang['table_field_min_dur']}</strong></label><br />{$lang['table_info_min_dur']}
                </td>
                <td width='30%'>
                    <select name='promote_time_member'>
                        <option id='promote_time_member' " . ($promote_time_member == "28" ? " selected='selected' " : "") . " value='28'>4{$lang['form_opt_weeks']}</option>
                        <option id='promote_time_member1' " . ($promote_time_member == "35" ? " selected='selected' " : "") . " value='35'>5{$lang['form_opt_weeks']}</option>
                        <option id='promote_time_member2' " . ($promote_time_member == "42" ? " selected='selected' " : "") . " value='42'>6{$lang['form_opt_weeks']}</option>
                        <option id='promote_time_member3' " . ($promote_time_member == "49" ? " selected='selected' " : "") . " value='49'>7{$lang['form_opt_weeks']}</option>
                        <option id='promote_time_member4' " . ($promote_time_member == "56" ? " selected='selected' " : "") . " value='56'>8{$lang['form_opt_weeks']}</option>
                    </select>
                </td>
            </tr>");

        print("<tr>
                <td width='30%'>
                    <label for='demote_ratio'><strong>{$lang['table_field_auto_demote']}</strong></label>
                    <br />{$lang['table_info_auto_demote']}
                </td>
                <td width='30%'>
                    <select name='demote_ratio'>
                        <option id='demote_ratio' " . ($demote_ratio == "0.85" ? " selected='selected' " : "") . " value='0.85'>0.85</option>
                        <option id='demote_ratio1' " . ($demote_ratio == "0.90" ? " selected='selected' " : "") . " value='0.90'>0.90</option>
                        <option id='demote_ratio2' " . ($demote_ratio == "0.95" ? " selected='selected' " : "") . " value='0.95'>0.95</option>
                        <option id='demote_ratio3' " . ($demote_ratio == "1.00" ? " selected='selected' " : "") . " value='1.00'>1.00</option>
                        <option id='demote_ratio4' " . ($demote_ratio == "1.05" ? " selected='selected' " : "") . " value='1.05'>1.05</option>
                    </select>
                </td>
            </tr>");


        print("</table></div>");

        //-- Finish Cleanup Config Details-- //
        print("<div align='center'><br /><span class='config_warning_tracker_manager'>{$lang['text_empty_box']}<br />{$lang['text_spaces_config']}<br />{$lang['text_non_function']}<br /></span></div><br />");


        print("<div class='proceed-btn-div' align='center'><input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' /></div></div>");

        print("</div></form></div>");

    ?>

    <script type="text/javascript" src="js/jquery-1.8.2.js" ></script>
    <script type="text/javascript" src="js/jquery-ui-1.9.0.custom.min.js" ></script>

    <script type="text/javascript">
      $(document).ready(function()
      {
        $("#featured").tabs({fx:{opacity: "toggle"}}).tabs("rotate", 5000, true);
      });
    </script>

    <?php

        site_footer();

    }

    function do_step_one()
    {
        //-- Open config_rewrite.php --//
        $conf_string = file_get_contents('./config_rewrite.php');

        $placeholders = array('<#mysql_host#>',
                              '<#mysql_db#>',
                              '<#mysql_user#>',
                              '<#mysql_pass#>',
                              '<#announce_url#>',
                              '<#site_url#>',
                              '<#site_online#>',
                              '<#members_only#>',
                              '<#site_mail#>',
                              '<#email_confirm#>',
                              '<#site_name#>',
                              '<#image_dic#>',
                              '<#torrent_dic#>',
                              '<#torrents_allfree#>',
                              '<#peer_limit#>',
                              '<#max_users#>',
                              '<#max_users_then_invite#>',
                              '<#invites#>',
                              '<#signup_timeout#>',
                              '<#min_votes#>',
                              '<#autoclean_interval#>',
                              '<#announce_interval#>',
                              '<#max_torrent_size#>',
                              '<#max_dead_torrent_time#>',
                              '<#posts_read_expiry#>',
                              '<#maxloginattempts#>',
                              '<#dictbreaker#>',
                              '<#oldtorrents#>',
                              '<#days#>',
                              '<#site_reputation#>',
                              '<#maxfilesize#>',
                              '<#attachment_dir#>',
                              '<#forum_width#>',
                              '<#maxsubjectlength#>',
                              '<#postsperpage#>',
                              '<#use_attachment_mod#>',
                              '<#use_poll_mod#>',
                              '<#forum_stats_mod#>',
                              '<#use_flood_mod#>',
                              '<#limmit#>',
                              '<#minutes#>',
                              '<#staff_log#>',
                              '<#site_log#>',
                              '<#parked_users#>',
                              '<#inactive_users#>',
                              '<#old_login_attempts#>',
                              '<#old_help_desk#>',
                              '<#promote_upload#>',
                              '<#promote_ratio#>',
                              '<#promote_time_member#>',
                              '<#demote_ratio#>',
                              '<#waittime#>',
                              '<#max_class_wait#>',
                              '<#ratio_1#>',
                              '<#gigs_1#>',
                              '<#wait_1#>',
                              '<#ratio_2#>',
                              '<#gigs_2#>',
                              '<#wait_2#>',
                              '<#ratio_3#>',
                              '<#gigs_3#>',
                              '<#wait_3#>',
                              '<#ratio_4#>',
                              '<#gigs_4#>',
                              '<#wait_4#>',);

        $replacements = array($this->VARS['mysql_host'],
                              $this->VARS['mysql_db'],
                              $this->VARS['mysql_user'],
                              $this->VARS['mysql_pass'],
                              $this->VARS['announce_url'],
                              $this->VARS['site_url'],
                              $this->VARS['site_online'],
                              $this->VARS['members_only'],
                              $this->VARS['site_mail'],
                              $this->VARS['email_confirm'],
                              $this->VARS['site_name'],
                              $this->VARS['image_dic'],
                              $this->VARS['torrent_dic'],
                              $this->VARS['torrents_allfree'],
                              $this->VARS['peer_limit'],
                              $this->VARS['max_users'],
                              $this->VARS['max_users_then_invite'],
                              $this->VARS['invites'],
                              $this->VARS['signup_timeout'],
                              $this->VARS['min_votes'],
                              $this->VARS['autoclean_interval'],
                              $this->VARS['announce_interval'],
                              $this->VARS['max_torrent_size'],
                              $this->VARS['max_dead_torrent_time'],
                              $this->VARS['posts_read_expiry'],
                              $this->VARS['maxloginattempts'],
                              $this->VARS['dictbreaker'],
                              $this->VARS['oldtorrents'],
                              $this->VARS['days'],
                              $this->VARS['site_reputation'],
                              $this->VARS['maxfilesize'],
                              $this->VARS['attachment_dir'],
                              $this->VARS['forum_width'],
                              $this->VARS['maxsubjectlength'],
                              $this->VARS['postsperpage'],
                              $this->VARS['use_attachment_mod'],
                              $this->VARS['use_poll_mod'],
                              $this->VARS['forum_stats_mod'],
                              $this->VARS['use_flood_mod'],
                              $this->VARS['limmit'],
                              $this->VARS['minutes'],
                              $this->VARS['staff_log'],
                              $this->VARS['site_log'],
                              $this->VARS['parked_users'],
                              $this->VARS['inactive_users'],
                              $this->VARS['old_login_attempts'],
                              $this->VARS['old_help_desk'],
                              $this->VARS['promote_upload'],
                              $this->VARS['promote_ratio'],
                              $this->VARS['promote_time_member'],
                              $this->VARS['demote_ratio'],
                              $this->VARS['waittime'],
                              $this->VARS['max_class_wait'],
                              $this->VARS['ratio_1'],
                              $this->VARS['gigs_1'],
                              $this->VARS['wait_1'],
                              $this->VARS['ratio_2'],
                              $this->VARS['gigs_2'],
                              $this->VARS['wait_2'],
                              $this->VARS['ratio_3'],
                              $this->VARS['gigs_3'],
                              $this->VARS['wait_3'],
                              $this->VARS['ratio_4'],
                              $this->VARS['gigs_4'],
                              $this->VARS['wait_4']);

        $conf_string = str_replace($placeholders, $replacements, $conf_string);

        if ($fh = fopen(FTSP_ROOT_PATH . 'functions/function_config.php', 'w'))
        {
            fputs($fh, $conf_string, strlen($conf_string));
            fclose($fh);
        }

        //-- Write To Database -- Config Setup -- To Keep Setup Changes --//
        function config()
        {
			global $db;

            $db->query("TRUNCATE TABLE config") or sqlerr(__FILE__, __LINE__);

            $mysql_host            = strip_tags(isset($_POST['mysql_host']) ? trim($_POST['mysql_host']) : '');
            $mysql_db              = strip_tags(isset($_POST['mysql_db']) ? trim($_POST['mysql_db']) : '');
            $mysql_user            = strip_tags(isset($_POST['mysql_user']) ? trim($_POST['mysql_user']) : '');
            $mysql_pass            = strip_tags(isset($_POST['mysql_pass']) ? trim($_POST['mysql_pass']) : '');
            $site_url              = strip_tags(isset($_POST['site_url']) ? trim($_POST['site_url']) : '');
            $announce_url          = strip_tags(isset($_POST['announce_url']) ? trim($_POST['announce_url']) : '');
            $site_online           = strip_tags(isset($_POST['site_online']) ? trim($_POST['site_online']) : '');
            $members_only          = strip_tags(isset($_POST['members_only']) ? trim($_POST['members_only']) : '');
            $site_mail             = strip_tags(isset($_POST['site_mail']) ? trim($_POST['site_mail']) : '');
            $email_confirm         = strip_tags(isset($_POST['email_confirm']) ? trim($_POST['email_confirm']) : '');
            $site_name             = strip_tags(isset($_POST['site_name']) ? trim($_POST['site_name']) : '');
            $image_dic             = strip_tags(isset($_POST['image_dic']) ? trim($_POST['image_dic']) : '');
            $torrent_dic           = strip_tags(isset($_POST['torrent_dic']) ? trim($_POST['torrent_dic']) : '');
            $torrents_allfree      = strip_tags(isset($_POST['torrents_allfree']) ? trim($_POST['torrents_allfree']) : '');
            $peer_limit            = strip_tags(isset($_POST['peer_limit']) ? trim($_POST['peer_limit']) : '');
            $max_users             = strip_tags(isset($_POST['max_users']) ? trim($_POST['max_users']) : '');
            $max_users_then_invite = strip_tags(isset($_POST['max_users_then_invite']) ? trim($_POST['max_users_then_invite']) : '');
            $invites               = strip_tags(isset($_POST['invites']) ? trim($_POST['invites']) : '');
            $signup_timeout        = strip_tags(isset($_POST['signup_timeout']) ? trim($_POST['signup_timeout']) : '');
            $min_votes             = strip_tags(isset($_POST['min_votes']) ? trim($_POST['min_votes']) : '');
            $autoclean_interval    = strip_tags(isset($_POST['autoclean_interval']) ? trim($_POST['autoclean_interval']) : '');
            $announce_interval     = strip_tags(isset($_POST['announce_interval']) ? trim($_POST['announce_interval']) : '');
            $max_torrent_size      = strip_tags(isset($_POST['max_torrent_size']) ? trim($_POST['max_torrent_size']) : '');
            $max_dead_torrent_time = strip_tags(isset($_POST['max_dead_torrent_time']) ? trim($_POST['max_dead_torrent_time']) : '');
            $posts_read_expiry     = strip_tags(isset($_POST['posts_read_expiry']) ? trim($_POST['posts_read_expiry']) : '');
            $maxloginattempts      = strip_tags(isset($_POST['maxloginattempts']) ? trim($_POST['maxloginattempts']) : '');
            $dictbreaker           = strip_tags(isset($_POST['dictbreaker']) ? trim($_POST['dictbreaker']) : '');
            $oldtorrents           = strip_tags(isset($_POST['oldtorrents']) ? trim($_POST['oldtorrents']) : '');
            $days                  = strip_tags(isset($_POST['days']) ? trim($_POST['days']) : '');
            $site_reputation       = strip_tags(isset($_POST['site_reputation']) ? trim($_POST['site_reputation']) : '');
            $maxfilesize           = strip_tags(isset($_POST['maxfilesize']) ? trim($_POST['maxfilesize']) : '');
            $attachment_dir        = strip_tags(isset($_POST['attachment_dir']) ? trim($_POST['attachment_dir']) : '');
            $forum_width           = strip_tags(isset($_POST['forum_width']) ? trim($_POST['forum_width']) : '');
            $maxsubjectlength      = strip_tags(isset($_POST['maxsubjectlength']) ? trim($_POST['maxsubjectlength']) : '');
            $postsperpage          = strip_tags(isset($_POST['postsperpage']) ? trim($_POST['postsperpage']) : '');
            $use_attachment_mod    = strip_tags(isset($_POST['use_attachment_mod']) ? trim($_POST['use_attachment_mod']) : '');
            $use_poll_mod          = strip_tags(isset($_POST['use_poll_mod']) ? trim($_POST['use_poll_mod']) : '');
            $forum_stats_mod       = strip_tags(isset($_POST['forum_stats_mod']) ? trim($_POST['forum_stats_mod']) : '');
            $use_flood_mod         = strip_tags(isset($_POST['use_flood_mod']) ? trim($_POST['use_flood_mod']) : '');
            $limmit                = strip_tags(isset($_POST['limmit']) ? trim($_POST['limmit']) : '');
            $minutes               = strip_tags(isset($_POST['minutes']) ? trim($_POST['minutes']) : '');
            $staff_log             = strip_tags(isset($_POST['staff_log']) ? trim($_POST['staff_log']) : '');
            $site_log              = strip_tags(isset($_POST['site_log']) ? trim($_POST['site_log']) : '');
            $parked_users          = strip_tags(isset($_POST['parked_users']) ? trim($_POST['parked_users']) : '');
            $inactive_users        = strip_tags(isset($_POST['inactive_users']) ? trim($_POST['inactive_users']) : '');
            $old_login_attempts    = strip_tags(isset($_POST['old_login_attempts']) ? trim($_POST['old_login_attempts']) : '');
            $old_help_desk         = strip_tags(isset($_POST['old_help_desk']) ? trim($_POST['old_help_desk']) : '');
            $promote_upload        = strip_tags(isset($_POST['promote_upload']) ? trim($_POST['promote_upload']) : '');
            $promote_ratio         = strip_tags(isset($_POST['promote_ratio']) ? trim($_POST['promote_ratio']) : '');
            $promote_time_member   = strip_tags(isset($_POST['promote_time_member']) ? trim($_POST['promote_time_member']) : '');
            $demote_ratio          = strip_tags(isset($_POST['demote_ratio']) ? trim($_POST['demote_ratio']) : '');
            $waittime              = strip_tags(isset($_POST['waittime']) ? trim($_POST['waittime']) : '');
            $max_class_wait        = strip_tags(isset($_POST['max_class_wait']) ? trim($_POST['max_class_wait']) : '');
            $ratio_1               = strip_tags(isset($_POST['ratio_1']) ? trim($_POST['ratio_1']) : '');
            $gigs_1                = strip_tags(isset($_POST['gigs_1']) ? trim($_POST['gigs_1']) : '');
            $wait_1                = strip_tags(isset($_POST['wait_1']) ? trim($_POST['wait_1']) : '');
            $ratio_2               = strip_tags(isset($_POST['ratio_2']) ? trim($_POST['ratio_2']) : '');
            $gigs_2                = strip_tags(isset($_POST['gigs_2']) ? trim($_POST['gigs_2']) : '');
            $wait_2                = strip_tags(isset($_POST['wait_2']) ? trim($_POST['wait_2']) : '');
            $ratio_3               = strip_tags(isset($_POST['ratio_3']) ? trim($_POST['ratio_3']) : '');
            $gigs_3                = strip_tags(isset($_POST['gigs_3']) ? trim($_POST['gigs_3']) : '');
            $wait_3                = strip_tags(isset($_POST['wait_3']) ? trim($_POST['wait_3']) : '');
            $ratio_4               = strip_tags(isset($_POST['ratio_4']) ? trim($_POST['ratio_4']) : '');
            $gigs_4                = strip_tags(isset($_POST['gigs_4']) ? trim($_POST['gigs_4']) : '');
            $wait_4                = strip_tags(isset($_POST['wait_4']) ? trim($_POST['wait_4']) : '');

            $db->query("INSERT INTO config (mysql_host, mysql_db, mysql_user, mysql_pass, site_url, announce_url, site_online,
                        members_only, site_mail, email_confirm, site_name, image_dic, torrent_dic, torrents_allfree, peer_limit,
                        max_members,max_users_then_invite, invites, signup_timeout, min_votes, autoclean_interval, announce_interval,
                        max_torrent_size, max_dead_torrent_time, posts_read_expiry,  max_login_attempts, dictbreaker,
                        delete_old_torrents, dead_torrents, site_reputation, maxfilesize, attachment_dir, forum_width,
                        maxsubjectlength, postsperpage, use_attachment_mod, use_poll_mod, forum_stats_mod, use_flood_mod, limmit,
                        minutes, staff_log, site_log, parked_users, inactive_users, old_login_attempts, old_help_desk, promote_upload,
                        promote_ratio, promote_time_member,demote_ratio, waittime, max_class_wait, ratio_1, gigs_1, wait_1, ratio_2,
                        gigs_2, wait_2, ratio_3, gigs_3, wait_3, ratio_4, gigs_4, wait_4)
                   VALUES (" . sqlesc($mysql_host) . ",
                           " . sqlesc($mysql_db) . ",
                           " . sqlesc($mysql_user) . ",
                           " . sqlesc($mysql_pass) . ",
                           " . sqlesc($site_url) . ",
                           " . sqlesc($announce_url) . ",
                           " . sqlesc($site_online) . ",
                           " . sqlesc($members_only) . ",
                           " . sqlesc($site_mail) . ",
                           " . sqlesc($email_confirm) . ",
                           " . sqlesc($site_name) . ",
                           " . sqlesc($image_dic) . ",
                           " . sqlesc($torrent_dic) . ",
                           " . sqlesc($torrents_allfree) . ",
                           " . sqlesc($peer_limit) . ",
                           " . sqlesc($max_users) . ",
                           " . sqlesc($max_users_then_invite) . ",
                           " . sqlesc($invites) . ",
                           " . sqlesc($signup_timeout) . ",
                           " . sqlesc($min_votes) . ",
                           " . sqlesc($autoclean_interval) . ",
                           " . sqlesc($announce_interval) . ",
                           " . sqlesc($max_torrent_size) . ",
                           " . sqlesc($max_dead_torrent_time) . ",
                           " . sqlesc($posts_read_expiry) . ",
                           " . sqlesc($maxloginattempts) . ",
                           " . sqlesc($dictbreaker) . ",
                           " . sqlesc($oldtorrents) . ",
                           " . sqlesc($days) . ",
                           " . sqlesc($site_reputation) . ",
                           " . sqlesc($maxfilesize) . ",
                           " . sqlesc($attachment_dir) . ",
                           " . sqlesc($forum_width) . ",
                           " . sqlesc($maxsubjectlength) . ",
                           " . sqlesc($postsperpage) . ",
                           " . sqlesc($use_attachment_mod) . ",
                           " . sqlesc($use_poll_mod) . ",
                           " . sqlesc($forum_stats_mod) . ",
                           " . sqlesc($use_flood_mod) . ",
                           " . sqlesc($limmit) . ",
                           " . sqlesc($minutes) . ",
                           " . sqlesc($staff_log) . ",
                           " . sqlesc($site_log) . ",
                           " . sqlesc($parked_users) . ",
                           " . sqlesc($inactive_users) . ",
                           " . sqlesc($old_login_attempts) . ",
                           " . sqlesc($old_help_desk) . ",
                           " . sqlesc($promote_upload) . ",
                           " . sqlesc($promote_ratio) . ",
                           " . sqlesc($promote_time_member) . ",
                           " . sqlesc($demote_ratio) . ",
                           " . sqlesc($waittime) . ",
                           " . sqlesc($max_class_wait) . ",
                           " . sqlesc($ratio_1) . ",
                           " . sqlesc($gigs_1) . ",
                           " . sqlesc($wait_1) . ",
                           " . sqlesc($ratio_2) . ",
                           " . sqlesc($gigs_2) . ",
                           " . sqlesc($wait_2) . ",
                           " . sqlesc($ratio_3) . ",
                           " . sqlesc($gigs_3) . ",
                           " . sqlesc($wait_3) . ",
                           " . sqlesc($ratio_4) . ",
                           " . sqlesc($gigs_4) . ",
                           " . sqlesc($wait_4) . ");") or sqlerr(__FILE__, __LINE__);
        }

        config();

        global $site_url, $lang;

        error_message_center("success",
                             "{$lang['gbl_adm_success']}",
                             "<strong>{$lang['text_database_updated']}</strong><br />
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=9'>{$lang['text_tracker_manager']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");

        site_header("{$lang['title_update']}", false);

        display_message_center("success",
                               "{$lang['gbl_adm_success']}",
                               "{$lang['text_config_updated']}");

        site_footer();

    }

} //-- End Class --//

?>