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

$lang = array_merge(load_language('upload'),
                    load_language('global'),
                    load_language('func_bbcode'));

parked();

$newpage = new page_verify();
$newpage->create('_upload_');

site_header("{$lang['title_upload']}", false);

if (user::$current['uploadpos'] == 'no')
{
    error_message_center("warn",
                         "{$lang['err_sorry']}",
                         "{$lang['err_upload_denied']}<br />
                         {$lang['err_contact_staff']}");

    site_footer();

    exit;
}

if (get_user_class() < UC_USER)
{
    error_message_center("warn",
                         "{$lang['err_sorry']}",
                         "{$lang['err_unauth_upload']}<a href='faq.php#up'>{$lang['text_uploading']}</a>{$lang['text_faq']}");

    site_footer();

    exit;
}

//-- Start Offer List, If The Uploading Member Has Made Any Offers --//
$res_offer = $db->query("SELECT id, offer_name
                        FROM offers
                        WHERE offered_by_user_id = " . user::$current['id'] . "
                        AND status = 'approved'
                        AND filled_torrent_id = 0
                        ORDER BY offer_name ASC");

if ($res_offer->num_rows >= 0)
{
    $offers = "
                <tr>
                    <td class='rowhead'>{$lang['table_my_offers']}</td>
                    <td class='rowhead'>
                        <select name='offer'>
                            <option value='0'>{$lang['form_opt_myoffers']}</option>";

                            $message = "<option value='0'>{$lang['form_opt_no_offers']}</option>";

                            while($arr_offer = $res_offer->fetch_assoc())
                            {
                                $offers .= "<option value='{$arr_offer['id']}'>" . security::html_safe($arr_offer['offer_name']) . "</option>";
                            }

                            $offers .= "</select>&nbsp;&nbsp;{$lang['form_info_offers']}
                    </td>
                </tr>";
}
//-- Finish Offer List, If The Uploading Member Has Made Any Offers --//

//-- Start Request Section Dropdown --//
$res_request = $db->query("SELECT id, request_name
                          FROM requests
                          WHERE filled_by_user_id = 0
                          ORDER BY request_name ASC");
$request = "
            <tr>
                <td class='rowhead'>{$lang['table_requests']}</td>
                <td class='rowhead'>
                    <select name='request'>
                        <option value='0'>{$lang['form_opt_requests']}</option>";

                        if ($res_request)
                        {
                             while($arr_request = $res_request->fetch_assoc())
                            {
                                $request .= "<option value='{$arr_request['id']}'>" . security::html_safe($arr_request['request_name']) . "</option>";
                            }
                        }
                        else
                        {
                            $request .= "<option value='0'>{$lang['form_opt_no_requests']}</option>";
                        }

                        $request .= "</select>&nbsp;&nbsp;{$lang['form_info_requests']}
                </td>
            </tr>";
//-- Finish Request Section Dropdown --//

echo ("
    <div align='center'>
        <form name='upload' method='post' action='takeupload.php' enctype='multipart/form-data'>
            <input type='hidden' name='MAX_FILE_SIZE' value='$max_torrent_size' />

            <p>{$lang['form_info_announce']}<span style='font-weight : bold;'>$announce_urls[0]</span></p>

            <table border='1' width='100%' cellspacing='0' cellpadding='10'>
                <tr>
                    <td class='rowhead'>
                        <label for='file'>{$lang['table_file']}</label>
                    </td>
                    <td class='rowhead'>
                        <input type='file' name='file' id='file' size='80' />\n
                    </td>
                </tr>\n

                <tr>
                    <td class='rowhead'>
                        <label for='name'>{$lang['table_name']}</label>
                    </td>
                    <td class='rowhead'>
                        <input type='text' name='name' id='name' size='80' /><br />
                        {$lang['form_info_name']}<strong>{$lang['form_info_desc_names']}</strong>)\n
                    </td>
                </tr>\n

                <tr>
                    <td class='rowhead'>
                        <label for='poster'>{$lang['table_poster']}</label>
                    </td>
                    <td class='rowhead'>
                        <input type='text' name='poster' id='poster' size='80' /><br />
                        {$lang['form_info_poster']}\n</td>
                </tr>\n

                <tr>
                    <td class='rowhead'>
                        <label for='nfo'>{$lang['table_nfo']}</label>
                    </td>
                    <td class='rowhead'>
                        <input type='file' name='nfo' id='nfo' size='80' /><br />
                        (<strong>{$lang['form_info_reguired']}</strong>{$lang['form_info_pu']}\n
                    </td>
                </tr>\n");

            echo $offers;
            echo $request;

            echo ("<tr>
                    <td class='rowhead' style='padding : 10px'>{$lang['table_desc']}</td>
                    <td class='rowhead' align='center' style='padding : 3px'>" . textbbcode("upload", "descr", security::html_safe($row['ori_descr'])) . "</td>
                </tr>\n");

            $s = "<select name='type'>\n<option value='0'>{$lang['form_opt_choose']}</option>\n";

            $cats = cached::genrelist();

            foreach ($cats
                     AS
                     $row)
            {
                $s .= "<option value='{$row['id']}'>" . security::html_safe($row['name']) . "</option>\n";
            }

            $s .= "</select>\n";

            echo("<tr>
                    <td class='rowhead'>{$lang['table_type']}</td>
                    <td class='rowhead'>" . $s . "</td>
                </tr>

                <tr>
                    <td class='rowhead'>{$lang['table_show_uploader']}</td>
                    <td class='rowhead'>
                        <input type='checkbox' name='uplver' value='yes' />{$lang['form_info_show_name']}
                    </td>
                </tr>

                <tr>
                    <td class='rowhead'>{$lang['table_freeleech']}</td>
                    <td class='rowhead'>
                        <input type='checkbox' name='freeleech' value='yes' />{$lang['form_info_freeleech']}
                    </td>
                </tr>

                <tr>
                    <td class='std' align='center' colspan='2'>
                        <input type='submit' class='btn' value='{$lang['btn_upload']}' />
                    </td>
                </tr>
            </table>
        </form>
    </div>
<br />");

site_footer();

?>