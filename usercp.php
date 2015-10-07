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
require_once(FUNC_DIR . 'function_page_verify.php');

db_connect(false);
logged_in();

$lang = array_merge(load_language('usercp'),
                    load_language('global'));

$newpage = new page_verify();
$newpage->create('_usercp_');

$action = isset($_GET['action']) ? security::html_safe(trim($_GET['action'])) : '';

site_header(htmlentities(user::$current['username'], ENT_QUOTES) . "{$lang['title_private_page']}", false);

if (isset($_GET['edited']))
{
    display_message_center("success",
                           "{$lang['gbl_success']}",
                           "<a href='/usercp.php'>{$lang['text_profile_updated']}</a>");

    if (isset($_GET['mailsent']))
    {
        display_message_center("success",
                               "{$lang['gbl_success']}",
                               "<a href='/usercp.php'>{$lang['text_conf_email_sent']}</a>");
    }
}

elseif (isset($_GET['emailch']))
{
    display_message_center("success",
                           "{$lang['gbl_success']}",
                           "<a href='/usercp.php'>{$lang['text_email_changed']}</a>");
}

print("<h1>{$lang['text_welcome']}<a href='userdetails.php?id=" . user::$current['id'] . "'>" . security::html_safe(user::$current['username']) . "</a> !</h1>\n
        <form method='post' action='takeeditusercp.php'>
            <table border='1' align='center' width='600' cellspacing='0' cellpadding='3'>
                <tr>
                    <td width='600' valign='top'>");

print("<table border='1' width='502'>");

$maxbox = 100;
$maxpic = "warn";

//-- Check For Messages --//
$res1 = $db->query("SELECT COUNT(id)
                   FROM messages
                   WHERE receiver = " . user::$current['id'] . "
                   AND location >= '1'") or print($db->error);

$arr1 = $res1->fetch_row();

$messages = (int)$arr1[0];

$res1 = $db->query("SELECT COUNT(id)
                   FROM messages
                   WHERE receiver = " . user::$current['id'] . "
                   AND location >= '1'
                   AND unread = 'yes'") or print($db->error);

$arr1 = $res1->fetch_row();

$unread = (int)$arr1[0];

$res1 = $db->query("SELECT COUNT(id)
                   FROM messages
                   WHERE sender = " . user::$current['id'] . "
                   AND saved = 'yes'") or print($db->error);

$arr1 = $res1->fetch_row();

$outmessages = (int)$arr1[0];

$res1 = $db->query("SELECT COUNT(id)
                   FROM messages
                   WHERE receiver = " . user::$current['id'] . "
                   AND unread = 'yes'") or die("{$lang['err_oops']}");

$arr1   = $res1->fetch_row();
$unread = (int)$arr1[0];

print("<tr>
        <td class='colhead' align='center' width='166' height='18'>
            <a href='messages.php'>{$lang['table_inbox']}</a>
        </td>
        <td class='colhead' align='center' width='166'>
            <a href='messages.php?action=viewmailbox&amp;box=-1'>{$lang['table_sentbox']}</a>
        </td>
    </tr>");

print("<tr align='center'>
        <td> ($messages)</td><td>($outmessages)</td>
    </tr>");

print("<tr>
        <td align='center' height='25' colspan='3'>
            <span style='font-weight : bold;'>{$lang['table_you_have']}$unread{$lang['table_new_msg']}</span>
        </td>
    </tr>");

print("<tr>
        <td align='center' height='25' colspan='3'>
            <a href='friends.php'>
                <span style='font-weight : bold;'>{$lang['table_friends']}</span>
            </a>
        </td>
    </tr>");

print("<tr>
        <td align='center' height='25' colspan='3'>
            <a href='users.php'>
                <span style='font-weight : bold;'>{$lang['table_find_users']}</span>
            </a>
        </td>
    </tr>");

print("</table>");

//-- Avatar --//
if ($action == "avatar")
{
    begin_table(true);

    print("<tr>
            <td class='colhead' align='center' style='height:25px;' colspan='2'>
                <input type='hidden' name='action' value='avatar' />{$lang['title_opt_avatar']}
            </td>
        </tr>");

    if (get_user_class() >= UC_SYSOP)
    {
        print("<tr>
                <td class='rowhead_form'>
                    <label for='title'>{$lang['form_title']}</label>
                </td>
                <td class='rowhead_form'>
                    <input type='text' name='title' id='title' size='50' value='" . htmlsafechars(user::$current['title']) . "' />
                </td>
            </tr>");
    }

    print("<tr>
            <td class='rowhead_form'>
                <label for='avatar'>{$lang['form_field_avatar_url']}</label>
            </td>
            <td class='rowhead'>
                <input type='text' name='avatar' id='avatar' size='50' value='" . security::html_safe(user::$current['avatar']) . "' /><br />&nbsp;{$lang['form_info_avatar_url']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_show_avatar']}</td>
            <td class='rowhead'>
                <input type='checkbox' name='avatars' " . ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? " checked='checked' " : "") . " value='yes' />&nbsp;{$lang['form_info_bandwidth']}<br />
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form' align='center' colspan='2'>
                <input type='submit' class='btn' value='{$lang['btn_submit']}' />
            </td>
        </tr>");

    end_table();
}

//-- Signature --//
elseif ($action == "signature")
{
    begin_table(true);

    print("<tr>
            <td class='colhead' align='center' style='height:25px;' colspan='2'>
                <input type='hidden' name='action' value='signature' />{$lang['title_opt_signature']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>
                <label for='signature'>{$lang['form_field_sig']}</label>
            </td>
            <td class='rowhead'>
                <textarea name='signature' id='signature' cols='50' rows='4'>" . security::html_safe(user::$current['signature']) . "</textarea><br />
                &nbsp;{$lang['form_info_sig']}\n<br />&nbsp;{$lang['form_bbcodes']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_view_sig']}</td>
            <td class='rowhead'>
                <input type='checkbox' name='signatures' " . (user::$current['signatures'] == "yes" ? " checked='checked' " : "") . " /> &nbsp;{$lang['form_info_bandwidth']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>
                <label for='info'>{$lang['form_field_info']}</label>
            </td>
            <td class='rowhead'>
                <textarea name='info' id='info' cols='50' rows='4'>" . htmlentities(user::$current['info'], ENT_QUOTES) . "</textarea><br />&nbsp;{$lang['form_info_info']}<br />&nbsp;{$lang['form_bbcodes']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form' align='center' colspan='2'>
                <input type='submit' class='btn' value='{$lang['btn_submit']}' />
            </td>
        </tr>");

    end_table();
}

//-- Security --//

elseif ($action == "security")
{
    begin_table(true);

    print("<tr>
            <td class='colhead' align='center' colspan='2' style='height:25px;'>
                <input type='hidden' name='action' value='security' />{$lang['title_opt_security']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_passkey']}</td>
            <td class='rowhead'>
                <input type='checkbox' name='resetpasskey' value='1' />&nbsp;{$lang['form_info_passkey']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>
                <label for='email'>{$lang['form_field_email']}</label>
            </td>
            <td class='rowhead_form'>
                <input type='text' name='email' id='email' size='50' value='" . security::html_safe(user::$current['email']) . "' />
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_info_email1']}</td>
            <td class='rowhead' align='left'>{$lang['form_info_email2']}</td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>
                <label for='chpassword'>{$lang['form_field_pass']}</label>
            </td>
            <td class='rowhead_form'>
                <img src='{$image_dir}password/tooshort.gif' id='strength' alt='' />
                <input type='password' name='chpassword' maxlength='15' onkeyup='updatestrength( this.value );' id='chpassword' size='50' />
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>
                <label for='passagain'>{$lang['form_field_pass_again']}</label>
            </td>
            <td class='rowhead_form'>
                <input type='password' name='passagain' id='passagain' size='50' />
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead' align='center' colspan='2'>
                <input type='submit' class='btn' value='{$lang['btn_submit']}' />
            </td>
        </tr>");

    end_table();
}

//-- Torrents --//
elseif ($action == "torrents")
{
    begin_table(true);

    print("<tr>
            <td class='colhead' align='center' colspan='2' style='height:25px;'>
                <input type='hidden' name='action' value='torrents' />{$lang['title_opt_torrent']}
            </td>
        </tr>");

    $categories = '';

    $r = @$db->query("SELECT id, name
                     FROM categories
                     ORDER BY name") or sqlerr();

    if ($r->num_rows > 0)
    {
        $categories .= "<table><tr>\n";

        $i = 0;

        while ($a = $r->fetch_assoc())
        {
            $categories .= ($i && $i % 2 == 0) ? "</tr><tr>" : "";

            $categories .= "<td class='bottom' style='padding-right: 5px'><input name='cat{$a['id']}' type='checkbox' " . (strpos(user::$current['notifs'], "[cat{$a['id']}]") !== false ? " checked='checked' " : "") . " value='yes' />&nbsp;" . security::html_safe($a['name']) . "</td>\n";

            ++$i;
        }

        $categories .= "</tr></table>\n";
    }

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_email']}</td>
            <td class='rowhead'>
                <input type='checkbox' name='pmnotif' " . (strpos(user::$current['notifs'], "[pm]") !== false ? " checked='checked' " : "") . " value='yes' />&nbsp;{$lang['form_info_email']}<br />

                <input type='checkbox' name='emailnotif' " . (strpos(user::$current['notifs'], "[email]") !== false ? " checked='checked' " : "") . " value='yes' />&nbsp;{$lang['form_field_torrent_notif']}<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$lang['form_field_torrent_notif1']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_browse']}</td>
            <td class='rowhead'>$categories</td>
        </tr>");

    print("<tr>
            <td class='rowhead' align='center' colspan='2'>
                <input type='submit' class='btn' value='{$lang['btn_submit']}' />
            </td>
        </tr>");

    end_table();
}

//-- Personal --//
elseif ($action == "personal")
{
    $ss_r = $db->query("SELECT id, name
                       FROM stylesheets
                       WHERE active = 'yes'
                       ORDER BY id ") or die;

    $ss_sa = array();

    while ($ss_a = $ss_r->fetch_array(MYSQLI_BOTH))
    {
        $ss_id           = (int)$ss_a['id'];
        $ss_name         = security::html_safe($ss_a['name']);
        $ss_sa[$ss_name] = $ss_id;
    }

    ksort($ss_sa);
    reset($ss_sa);

    $stylesheets = '';

    while (list($ss_name, $ss_id) = each($ss_sa))
    {
        if ($ss_id == user::$current['stylesheet'])
        {
            $ss = " selected='selected' ";
        }
        else
        {
            $ss = "";
        }
        $stylesheets .= "<option value='$ss_id'$ss>$ss_name</option>\n";
    }

    $countries = "<option value='0'>---- {$lang['form_country_select']} ----</option>\n";

    $ct_r = $db->query("SELECT id, name
                       FROM countries
                       ORDER BY name") or sqlerr(__FILE__, __LINE__);

    while ($ct_a = $ct_r->fetch_assoc())
    {
        $countries .= "<option value='{$ct_a['id']}' " . (user::$current['country'] == $ct_a['id'] ? " selected='selected' " : "") . ">" . security::html_safe($ct_a['name']) . "</option>\n";
    }

    $language = "<option value='English'>---- {$lang['form_language_select']} ----</option>\n";

    $lg_r = $db->query("SELECT name
                       FROM languages
                       ORDER BY name") or sqlerr(__FILE__, __LINE__);

    while ($lg_a = $lg_r->fetch_assoc())
    {
        $language .= "<option value='{$lg_a['name']}' " . (user::$current['language'] == $lg_a['name'] ? " selected='selected' " : "") . ">" . security::html_safe($lg_a['name']) . "</option>\n";
    }

    begin_table(true);

    print("<tr>
            <td class='colhead' align='center' colspan='2' style='height:25px;'>
                <input type='hidden' name='action' value='personal' />{$lang['title_opt_personal']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_stylesheet']}</td>
            <td class='rowhead_form'>
                <select name='stylesheet'>\n$stylesheets\n</select>
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_language']}</td>
            <td class='rowhead_form'>
                <select name='language'>\n$language\n</select>
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_park']}</td>
            <td class='rowhead'>
                <input type='radio' name='parked' " . (user::$current['parked'] == 'yes' ? " checked='checked' " : "") . " value='yes' />&nbsp;{$lang['form_yes_select']}

                <input type='radio' name='parked' " . (user::$current['parked'] == 'no' ? " checked='checked' " : "") . " value='no' />&nbsp;{$lang['form_no_select']}<br />{$lang['form_info_park']}<br />{$lang['form_info_park1']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_pcon']}</td>
            <td class='rowhead'>
                <input type='radio' name='pcoff' " . (user::$current['pcoff'] == 'yes' ? " checked='checked' " : "") . " value='yes' />&nbsp;{$lang['form_yes_select']}

                <input type='radio' name='pcoff' " . (user::$current['pcoff'] == 'no' ? " checked='checked' " : "") . " value='no' />&nbsp;{$lang['form_no_select']}
            </td>
        </tr>");

    if (user::$current['menu'] == "2")
    {
        print("<tr>
                <td class='rowhead_form'>{$lang['form_field_menu']}</td>
                <td class='rowhead' align='left'>
                    <select name='menu' id='input'>
                        <option value='2'>{$lang['form_std_select']}</option>
                        <option value='1'>{$lang['form_drop_select']}</option>
                    </select>
                </td>
            </tr>");
    }
    else
    {
        print("<tr>
                <td class='rowhead_form'>{$lang['form_field_menu']}</td>
                <td class='rowhead' align='left'>
                    <select name='menu' id='input'>
                        <option value='1'>{$lang['form_drop_select']}</option>
                        <option value='2'>{$lang['form_std_select']}</option>
                    </select>
                </td>
            </tr>");
    }

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_country']}</td>
            <td class='rowhead_form'>
                <select name='country'>\n$countries\n</select>
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>
                <label for='torrentsperpage'>{$lang['form_field_torrents']}</label>
            </td>
            <td class='rowhead'>
                <input type='text' size='10' name='torrentsperpage' id='torrentsperpage' value='" . user::$current['torrentsperpage'] . "' />&nbsp;({$lang['form_info_per_page']})
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>
                <label for='topicsperpage'>{$lang['form_field_topics']}</label>
            </td>
            <td class='rowhead'>
                <input type='text' size='10' name='topicsperpage' id='topicsperpage' value='" . user::$current['topicsperpage'] . "' />&nbsp;({$lang['form_info_per_page']})
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>
                <label for='postsperpage'>{$lang['form_field_posts']}</label>
            </td>
            <td class='rowhead'>
                <input type='text' size='10' name='postsperpage' id='postsperpage' value='" . user::$current['postsperpage'] . "' />&nbsp;({$lang['form_info_per_page']})
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead' align='center' colspan='2'>
                <input type='submit' class='btn' value='{$lang['btn_submit']}' />
            </td>
        </tr>");

    end_table();
}
elseif ($action == "pm")
{
    //-- PMs --//
    begin_table(true);

    print("<tr>
            <td class='colhead' align='center' colspan='2' style='height:25px;'>
                <input type='hidden' name='action' value='pm' />{$lang['title_opt_pm']}
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['title_opt_pm']}</td>
            <td class='rowhead'>
                <input type='radio' name='acceptpms' " . (user::$current['acceptpms'] == "yes" ? " checked='checked' " : "") . " value='yes' />&nbsp;{$lang['form_all_select']}

                <input type='radio' name='acceptpms' " . (user::$current['acceptpms'] == "friends" ? " checked='checked' " : "") . " value='friends' />&nbsp;{$lang['form_friends_select']}

                <input type='radio' name='acceptpms' " . (user::$current['acceptpms'] == "no" ? " checked='checked' " : "") . " value='no' />&nbsp;{$lang['form_staff_select']}</td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_delete']}</td>
            <td class='rowhead'>
                <input type='checkbox' name='deletepms' " . (user::$current['deletepms'] == "yes" ? " checked='checked' " : "") . " />&nbsp;({$lang['form_info_delete']})
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead_form'>{$lang['form_field_save']}</td>
            <td class='rowhead'>
                <input type='checkbox' name='savepms' " . (user::$current['savepms'] == "yes" ? " checked='checked' " : "") . " />&nbsp;({$lang['form_info_save']})
            </td>
        </tr>");

    print("<tr>
            <td class='rowhead' align='center' colspan='2'>
                <input type='submit' class='btn' value='{$lang['btn_submit']}' />
            </td>
        </tr>");

    end_table();
}

elseif ($action == "preview")
{
//-- Preview Themes --//
begin_table(true);

print("<tr>
         <td class='colhead' align='center' colspan='2' style='height:25px;'>
             <input type='hidden' name='action' value='preview' />{$lang['title_opt_preview']}
         </td>
     </tr>");

$res = $db->query("SELECT *
                  FROM stylesheets
                  ORDER BY id ASC") or sqlerr(__FILE__, __LINE__);

     print("<tr>");

     print("<td class='colhead' align='center'>{$lang['table_stylesheet']}</td>
           <td class='colhead' align='center'>{$lang['table_preview']}</td>");

     print("</tr>");

     while ($arr = $res->fetch_assoc())
     {
         print("<tr>");

         print("<td class='rowhead' align='center'>" . security::html_safe($arr['name']) . "</td>");
         print("<td class='rowhead' align='center'>
                    <a href='theme_preview.php?id={$arr['id']}'>
                        <img src='{$image_dir}admin/themes_2.png' width='32' height='32' border='0' alt='{$lang['img_alt_preview']}' title='{$lang['img_alt_preview']}' />
                    </a>
               </td>");

         print("</tr>");
     }

end_table();
}

print("</td>
        <td width='95' valign='top' >
            <table border='1'>");

print("<tr>
        <td class='colhead' align='center' width='95' style='height : 25px;' >" . htmlentities(user::$current['username'], ENT_QUOTES) . "{$lang['text_avatar']}</td>
    </tr>");

if (!empty(user::$current['avatar']))
{
	$avatar = ((bool)(user::$current['flags'] & options::USER_SHOW_AVATARS) ? security::html_safe(user::$current['avatar']) : '');
    if (empty($avatar)) {
        $avatar = $image_dir . "default_avatar.gif";
    }
    print("<tr>
            <td>
                <img src='" . $avatar . "' width='125' height='125' border='0' alt='' title='' />
            </td>
        </tr>");
}
else
{
    print("<tr>
            <td class='std'>
                <img src='{$image_dir}default_avatar.gif' height='125' width='125' border='0' alt='' title='' />
            </td>
        </tr>");
}

print("<tr>
        <td class='colhead' align='center' width='95' style='height : 25px;'>" . htmlentities(user::$current['username'], ENT_QUOTES) . "{$lang['text_menu']}</td>
    </tr>");

print("<tr>
        <td class='rowhead' align='left'>
            <a href='usercp.php?action=avatar'>&nbsp;{$lang['table_avatar']}</a>
        </td>
    </tr>");

print("<tr>
        <td class='rowhead' align='left'>
            <a href='usercp.php?action=signature'>&nbsp;{$lang['table_signature']}</a>
        </td>
    </tr>");

print("<tr>
        <td class='rowhead' align='left'>
            <a href='usercp.php?action=security'>&nbsp;{$lang['table_security']}</a>
        </td>
    </tr>");

print("<tr>
        <td class='rowhead' align='left'>
            <a href='usercp.php?action=torrents'>&nbsp;{$lang['table_torrent']}</a>
        </td>
    </tr>");

print("<tr>
        <td class='rowhead' align='left'>
            <a href='usercp.php?action=personal'>&nbsp;{$lang['table_personal']}</a>
        </td>
    </tr>");

print("<tr>
        <td class='rowhead' align='left'>
            <a href='usercp.php?action=pm'>&nbsp;{$lang['table_pm']}</a>
        </td>
    </tr>");

print("<tr>
        <td class='rowhead' align='left'>
            <a href='usercp.php?action=preview'>&nbsp;{$lang['table_preview']}</a>
        </td>
    </tr>");

print("</table></td></tr></table></form>");

?>

<script type="text/javascript" src="js/password.js"></script>

<?php

site_footer();

?>