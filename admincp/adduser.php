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

$lang = array_merge(load_language('adm_adduser'),
                    load_language('adm_global'));

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if ($_POST['username'] == '' || $_POST['password'] == '' || $_POST['email'] == '')
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_data']}");
    }

    if ($_POST['password'] != $_POST['password2'])
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_password']}");
    }

    if (!security::valid_email($_POST['email']))
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_email']}");
    }

    $username = sqlesc($_POST['username']);
    $password = $_POST['password'];
    $email    = sqlesc($_POST['email']);
    $secret   = mksecret();
    $passhash = sqlesc(md5($secret.$password.$secret));
    $secret   = sqlesc($secret);

    $db->query("INSERT INTO users (added, last_access, secret, username, passhash, status, email)
               VALUES('" . get_date_time() . "', '" . get_date_time() . "', $secret, $username, $passhash, 'confirmed', $email)") or
                error_message_center("error",
                                     "{$lang['gbl_adm_error']}",
                                     "{$lang['err_already_exists']}<br />
                                     <br />{$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=5'>{$lang['text_return_adduser']}</a>
                                     <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");

    $res = $db->query("SELECT id
                      FROM users
                      WHERE username = " . $username);

    $arr = $res->fetch_row();

    if (!$arr)
    {
        error_message("error",
                      "{$lang['gbl_adm_error']}",
                      "{$lang['err_already_exists']}");
    }

    $id = intval(0 + $arr['0']);

    error_message_center("success",
                         "{$lang['gbl_adm_success']}",
                         "<strong>{$lang['text_created']}</strong><br />
                         <br /> {$lang['text_view_member']}<a href='$site_url/userdetails.php?id={$arr['0']}'>{$lang['text_details']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=5'>{$lang['text_return_adduser']}</a>
                         <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
    die();
}

site_header("{$lang['title_adduser']}", false);

print("<h1>{$lang['title_adduser']}</h1>");
print("<br />");
print("<form method='post' action='controlpanel.php?fileaction=5'>");
print("<table border='1' align='center' cellspacing='0' cellpadding='5'>");

print("<tr>
        <td class='colhead'>
            <label for='username'>{$lang['form_username']}</label>
        </td>
        <td class='rowhead'>
            <input type='text' name='username' id='username' size='40' />
        </td>
    </tr>");

print("<tr>
        <td class='colhead'>
            <label for='password'>{$lang['form_password']}</label>
        </td>
        <td class='rowhead'>
            <input type='password' name='password' id='password' size='40' />
        </td>
    </tr>");

print("<tr>
        <td class='colhead'>
            <label for='password2'>{$lang['form_password2']}</label>
        </td>
        <td class='rowhead'>
            <input type='password' name='password2' id='password2' size='40' />
        </td>
    </tr>");

print("<tr>
        <td class='colhead'>
            <label for='email'>{$lang['form_email']}</label>
        </td>
        <td class='rowhead'>
            <input type='text' name='email' id='email' size='40' />
        </td>
    </tr>");

print("<tr>
        <td class='std' colspan='2' align='center'>
            <input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' />
        </td>
    </tr>");

print("</table>");
print("</form>");

print("<br />");

site_footer();

?>