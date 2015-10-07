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

db_connect();
logged_in();

$newpage = new page_verify();
$newpage->create('_altusercp_');

$lang = array_merge(load_language('altusercp'),
                    load_language('func_bbcode'),
                    load_language('global'));

site_header(htmlentities(user::$current['username'], ENT_QUOTES) . "{$lang['title_private_page']}", false);

if (isset($_GET['edited']))
{
    display_message_center("success",
                           "{$lang['gbl_success']}",
                           "<a class='btn' href='altusercp.php'>{$lang['text_profile_update']}</a>");

    if (isset($_GET['mailsent']))
    {
        display_message_center("success",
                               "{$lang['gbl_success']}",
                               "<a href='altusercp.php'>{$lang['text_email_sent']}</a>");
    }

}
elseif (isset($_GET['emailch']))
{
    display_message_center("success",
                           "{$lang['gbl_success']}",
                           "<a href='altusercp.php'>{$lang['text_email_changed']}</a>");
}
else
{
    print("<h1>{$lang['text_welcome']}" . security::html_safe(user::$current['username']) . "!</h1>");
}

print("<table align='center'>");

print("<tr>
        <td class='colhead' align='center' width='125' height='18'>" . security::html_safe(user::$current['username']) . "{$lang['text_avatar']}</td>
    </tr>");

if (user::$current['avatar'])
{
    print("<tr>
            <td class='std'>
                <img src='" . security::html_safe(user::$current['avatar']) . "' width='125' height='125' border='0' alt='' title='' />
            </td>
        </tr>");
}
else
{
    print("<tr>
            <td class='std'>
                <img src='{$image_dir}default_avatar.gif' width='125' height='125' border='0' alt='' title='' />
            </td>
        </tr>");
}
print("</table>");

print("<h1><a href='userdetails.php?id=" . user::$current['id'] . "'>{$lang['text_your_details']}</a></h1>");

print("<div id='featured'>
        <ul>
            <li><a href='#fragment-0'></a></li>
            <li><a href='#fragment-1' class='btn'>{$lang['btn_avatar']}</a></li>
            <li><a href='#fragment-5' class='btn'>{$lang['btn_signature']}</a></li>
            <li><a href='#fragment-4' class='btn'>{$lang['btn_security']}</a></li>
            <li><a href='#fragment-6' class='btn'>{$lang['btn_torrents']}</a></li>
            <li><a href='#fragment-2' class='btn'>{$lang['btn_personal']}</a></li>
            <li><a href='#fragment-3' class='btn'>{$lang['btn_messages']}</a></li>
            <li><a href='#fragment-7' class='btn'>{$lang['btn_preview']}</a></li>
        </ul>
");

print("<div>");

print("<div class='ui-tabs-panel' id='fragment-1'>
        <form method='post' action='takeeditaltusercp.php?action=avatar'>
            <table border='1' align='center' width='81%'>");

print("<tr>
        <td class='colhead' align='center' colspan='2' style='height : 25px;'>{$lang['title_opt_avatar']}</td>
</tr>");

if (get_user_class() >= UC_SYSOP)
{
    print("<tr>
            <td class='rowhead_form' align='right' width='15%'>
                <label for='title'>{$lang['form_title']}&nbsp;&nbsp;&nbsp;</label>
            </td>
            <td class='rowhead_form'>
                <input type='text' name='title' id='title' size='50' value='" . htmlsafechars(user::$current['title']) . "' />
            </td>
    </tr>");
}

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>
            <label for='avatar'>{$lang['form_field_avatar_url']}&nbsp;&nbsp;&nbsp;</label>
        </td>
        <td class='rowhead'>
            <input type='text' name='avatar' id='avatar' size='50' value='" . security::html_safe(user::$current['avatar']) . "' /><br />
            &nbsp;{$lang['form_info_avatar_url']}
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>
            <label for='show'>{$lang['form_field_show_avatar']}&nbsp;&nbsp;&nbsp;</label>
        </td>
        <td class='rowhead'>
            <input type='checkbox' name='avatars' id='show' " . (user::$current['avatars'] == 'yes' ? " checked='checked'" : "") . " value='yes' />&nbsp;{$lang['form_info_bandwidth']}<br />
        </td>
</tr>");

print("<tr>
        <td class='std' align='center' height='30' colspan='2'>
            <input type='reset' class='btn' value='{$lang['btn_revert']}' />&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='submit' class='btn' value='{$lang['btn_submit']}' />
        </td>

</tr></table></form></div>");

print("<div class='ui-tabs-panel' id='fragment-2'>
        <form method='post' action='takeeditaltusercp.php?action=personal'>
            <table border='1' align='center' width='81%'>");

print("<tr>
        <td class='colhead' align='center' colspan='2' style='height : 25px;'>{$lang['title_opt_personal']}</td>
</tr>");

$ss_r = $db->query("SELECT id, name
                    FROM stylesheets
                    WHERE active = 'yes'
                    ORDER BY id ") or die;

$ss_sa = array();

while ($ss_a = $ss_r->fetch_array(MYSQLI_BOTH))
{
    $ss_id           = intval(0 + $ss_a['id']);
    $ss_name         = security::html_safe($ss_a['name']);
    $ss_sa[$ss_name] = $ss_id;
}

ksort($ss_sa);
reset($ss_sa);

while (list($ss_name, $ss_id) = each($ss_sa))
{
    if ($ss_id == user::$current['stylesheet'])
    {
        $ss = "selected='selected' ";
    }
    else
    {
        $ss = "";
    }

    $stylesheets .= "<option value='$ss_id'$ss>$ss_name</option>\n";
}

$countries = "<option value='0'>---- {$lang['form_country_select']} ----</option>\n";

$ct_r = $db->query("SELECT id,name
                    FROM countries
                    ORDER BY name") or sqlerr(__FILE__, __LINE__);

while ($ct_a = $cr_r->fetch_assoc())
{
    $countries .= "<option value='{$ct_a['id']}' " . (user::$current['country'] == $ct_a['id'] ? " selected='selected' " : "") . ">" . security::html_safe($ct_a['name']) . "</option>\n";
}

$language = "<option value='english'>---- {$lang['form_language_select']} ----</option>\n";

$lg_r = $db->query("SELECT name
                    FROM languages
                    ORDER BY name") or sqlerr(__FILE__, __LINE__);

while ($lg_a = $lg_r->fetch_assoc())
{
    $language .= "<option value='{$lg_a['name']}' " . (user::$current['language'] == $lg_a['name'] ? " selected='selected' " : "") . ">" . security::html_safe($lg_a['name']) . "</option>\n";
}

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_stylesheet']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead_form'><select name='stylesheet'>\n$stylesheets\n</select></td>
    </tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_language']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead_form'><select name='language'>\n$language\n</select></td>
    </tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_park']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='radio' name='parked' " . (user::$current['parked'] == 'yes' ? " checked='checked'" : "") . " value='yes' />&nbsp;{$lang['form_yes_select']}
            <input type='radio' name='parked' " . (user::$current['parked'] == 'no' ? " checked='checked'" : "") . " value='no' />&nbsp;{$lang['form_no_select']}<br />
                &nbsp;{$lang['form_info_park']}<br />&nbsp;{$lang['form_info_park1']}
        </td>
    </tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_pcon']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='radio' name='pcoff' " . (user::$current['pcoff'] == 'yes' ? " checked='checked'" : "") . " value='yes' />&nbsp;{$lang['form_yes_select']}
            <input type='radio' name='pcoff' " . (user::$current['pcoff'] == 'no' ? " checked='checked'" : "") . " value='no' />&nbsp;{$lang['form_no_select']}
        </td>
    </tr>");

if (user::$current['menu'] == '2')
{
    print("<tr>
            <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_menu']}</td>
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
            <td class='rowhead_form' align='right' width='15%'>{$lang['title_opt_menu']}&nbsp;&nbsp;&nbsp;</td>
            <td class='rowhead' align='left'>
                <select name='menu' id='input'>
                    <option value='1'>{$lang['form_drop_select']}</option>
                    <option value='2'>{$lang['form_std_select']}</option>
                </select>
            </td>
        </tr>");
}

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_country']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead_form'>
            <select name='country'>\n$countries\n</select>
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_torrents']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='text' name='torrentsperpage' id='torrentsperpage' size='10' value='" . user::$current['torrentsperpage'] . "' />&nbsp;({$lang['form_info_per_page']})
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_topics']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='text' name='topicsperpage' id='topicsperpage' size='10' value='" . user::$current['topicsperpage'] . "' />&nbsp;({$lang['form_info_per_page']})
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_posts']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='text' name='postsperpage' id='postsperpage' size='10' value='" . user::$current['postsperpage'] . "' />&nbsp;({$lang['form_info_per_page']})
        </td>
</tr>");

print("<tr>
        <td class='std' align='center' height='30' colspan='2'>
            <input type='reset' class='btn' value='{$lang['btn_revert']}' />&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='submit' class='btn' value='{$lang['btn_submit']}' />
        </td>
</tr></table></form></div>");

print("<div class='ui-tabs-panel' id='fragment-3'>
        <form method='post' action='takeeditaltusercp.php?action=pm'>
            <table border='1' align='center' width='81%' >");

print("<tr>
        <td class='colhead' align='center' colspan='2' style='height : 25px;'>{$lang['title_opt_pm']}</td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_pm']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='radio' name='acceptpms' " . (user::$current['acceptpms'] == 'yes' ? " checked='checked'" : "") . " value='yes' />&nbsp;{$lang['form_all_select']}
            <input type='radio' name='acceptpms' " . (user::$current['acceptpms'] == 'friends' ? " checked='checked'" : "") . " value='friends' />&nbsp;{$lang['form_friends_select']}
            <input type='radio' name='acceptpms' " . (user::$current['acceptpms'] == 'no' ? " checked='checked'" : "") . " value='no' />&nbsp;{$lang['form_staff_select']}
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_delete']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='checkbox' name='deletepms' " . (user::$current['deletepms'] == 'yes' ? " checked='checked'" : "") . " />&nbsp;({$lang['form_info_delete']})
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_save']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='checkbox' name='savepms' " . (user::$current['savepms'] == 'yes' ? " checked='checked'" : "") . " />&nbsp;({$lang['form_info_save']})
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_email']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead_form'>&nbsp;{$lang['form_info_email']}</td>
</tr>");

print("<tr>
        <td class='std' align='center' height='30' colspan='2'>
            <input type='reset' class='btn' value='{$lang['btn_revert']}' />&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='submit' class='btn' value='{$lang['btn_submit']}' />
        </td>
</tr>
</table></form></div>");

print("<div class='ui-tabs-panel' id='fragment-4'>
        <form method='post' action='takeeditaltusercp.php?action=security'>
            <table border='1' align='center' width='81%'>");

print("<tr>
        <td class='colhead' align='center' colspan='2' style='height : 25px;'>{$lang['title_opt_security']}</td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_passkey']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='checkbox' name='resetpasskey' value='1' /><br />
            &nbsp;{$lang['form_info_passkey']}
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_email']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead_form'>
            <input type='text' name='email' id='email' size='50' value='" . security::html_safe(user::$current['email']) . "' />
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_info_email1']}&nbsp;&nbsp;&nbsp;</td>
        <td align='left'>&nbsp;{$lang['form_info_email2']}</td>
</tr>\n");

print("<tr>
    <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_pass']}&nbsp;&nbsp;&nbsp;</td>
    <td class='rowhead_form'>
        <img src='{$image_dir}password/tooshort.gif' id='strength' width='240' height='27' border='0' alt='' title='' /><br />
        <input type='password' name='chpassword' maxlength='15' onkeyup='updatestrength(this.value);' id='password' size='50' />
    </td>
</tr>");

print("<tr>
    <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_pass_again']}&nbsp;&nbsp;&nbsp;</td>
    <td class='rowhead_form'>
        <input type='password' name='passagain' id='passagain' size='50' />
    </td>
</tr>");

print("<tr>
        <td class='std' align='center' height='30' colspan='2'>
            <input type='reset' class='btn' value='{$lang['btn_revert']}' />&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='submit' class='btn' value='{$lang['btn_submit']}' />
        </td>
</tr></table></form></div>");

print("<div class='ui-tabs-panel' id='fragment-5'>
        <form method='post' action='takeeditaltusercp.php?action=signature'>
            <table border='1' align='center' width='81%'>");

print("<tr>
        <td class='colhead' align='center' colspan='2' style='height : 25px;'>{$lang['title_opt_signature']}</td></tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_sig']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <textarea name='signature' id='signature' cols='50' rows='4'>" . security::html_safe(user::$current['signature']) . "</textarea><br />
            &nbsp;{$lang['form_info_sig']}<br />&nbsp;{$lang['form_bbcodes']}
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_view_sig']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='checkbox' name='signatures' " . (user::$current['signatures'] == 'yes' ? " checked='checked'" : "") . " />&nbsp;{$lang['form_info_bandwidth']}
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_info']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <textarea name='info' id='info' cols='50' rows='4'>" . htmlentities(user::$current['info'], ENT_QUOTES) . "</textarea><br />
            &nbsp;{$lang['form_info_info']}<br />&nbsp;{$lang['form_bbcodes']}
        </td>
</tr>");

print("<tr>
        <td class='std' align='center' height='30' colspan='2'>
            <input type='reset' class='btn' value='{$lang['btn_revert']}' />&nbsp;&nbsp;&nbsp;&nbsp;
         <input type='submit' class='btn' value='{$lang['btn_submit']}' />
        </td>
</tr></table></form></div>");

print("<div class='ui-tabs-panel' id='fragment-6'>
        <form method='post' action='takeeditaltusercp.php?action=torrents'>
            <table border='1' align='center' width='81%'>");

print("<tr>
        <td class='colhead' align='center' colspan='2' style='height : 25px;'>{$lang['title_opt_torrent']}</td>
</tr>");

$r = $db->query("SELECT id, name
                 FROM categories
                 ORDER BY name") or sqlerr();

if ($r->num_rows > 0)
{
    $categories = "<table><tr>\n";
    $i          = 0;

    while ($a = $r->fetch_assoc())
    {
        $categories .= ($i && $i % 2 == 0) ? "</tr><tr>" : "";
        $categories .= "<td class='bottom' style='padding-right : 5px'>
                            <input name='cat{$a['id']}' type='checkbox' " . (strpos(user::$current['notifs'], "[cat{$a['id']}]") !== false ? " checked='checked'" : "") . " value='yes' /> " . security::html_safe($a['name']) . "
                        </td>\n";
        ++$i;
    }
    $categories .= "</tr></table>\n";
}

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_email_notif']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>
            <input type='checkbox' name='pmnotif' " . (strpos(user::$current['notifs'], '[pm]') !== false ? " checked='checked'" : "") . " value='yes' />&nbsp;{$lang['form_field_pm_notif']}<br />
            <input type='checkbox' name='emailnotif' " . (strpos(user::$current['notifs'], '[email]') !== false ? " checked='checked'" : "") . " value='yes' />&nbsp;{$lang['form_field_torrent_notif']}
        </td>
</tr>");

print("<tr>
        <td class='rowhead_form' align='right' width='15%'>{$lang['form_field_browse']}&nbsp;&nbsp;&nbsp;</td>
        <td class='rowhead'>$categories</td>
</tr>");

print("<tr>
        <td class='std' align='center' height='30' colspan='2'>
            <input type='reset' class='btn' value='{$lang['btn_revert']}' />&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='submit' class='btn' value='{$lang['btn_submit']}' />
        </td>
</tr>
</table></form></div>");

print("<div id='fragment-7' class='ui-tabs-panel'>
        <table border='1' align='center' width='81%'>");

print("<tr>
        <td class='colhead' align='center' colspan='2' style='height : 25px;'>{$lang['title_theme_preview']}<br /></td>
    </tr>");

$res = $db->query("SELECT *
                   FROM stylesheets
                   ORDER BY id ASC") or sqlerr(__FILE__, __LINE__);

     print("<tr>");

     print("<td class='colhead' align='center' style='height : 20px;'>{$lang['table_stylesheet']}</td>
            <td class='colhead' align='center' style='height : 20px;'>{$lang['table_preview']}</td>");

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

print("</table></div>");

print("</div></div>");

print("<br />");

?>

<script type="text/javascript" src="js/jquery-1.8.2.js" ></script>
<script type="text/javascript" src="js/jquery-ui-1.9.0.custom.min.js" ></script>

<script type="text/javascript">
    $(document).ready(function()
    {
        $("#featured").tabs({fx:{opacity: "toggle"}}).tabs("rotate", 5000, true);
    });
</script>

    <script type="text/javascript" src="js/password.js"></script>

<?php

site_footer();

?>