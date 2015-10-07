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

$lang = array_merge(load_language('adm_update_sql'),
                    load_language('adm_global'));

###############################################################
##                                                           ##
##     Password Default Setting - Is Your Mysql Password.    ##
##  Change The $password Below To One Of Your Own Choosing.  ##
##         If You Feel It Would Be More Secure.              ##
##                                                           ##
###############################################################

global $mysql_pass;

$password = "$mysql_pass";

$action = ($_POST['action'] ? security::html_safe($_POST['action']) : ($_GET['action'] ? security::html_safe($_GET['action']) :''));

if ($action == 'update')
{
    site_header();

	$db_sql = trim((isset($_POST['db_sql']) ? $_POST['db_sql'] : ''));

	if (!$_POST['db_sql'])
	{
	    error_message_center("warn",
                             "{$lang['gbl_adm_warn']}",
                             "{$lang['err_empty']}");
	}

	//-- Execute Query & Update Database --//
	if ($db->query($db_sql))
	{
	    error_message_center("success",
                             "{$lang['gbl_adm_success']}",
                             "<strong>{$lang['err_updated']}</strong><br />
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php?fileaction=31'>{$lang['text_return_dbsql']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='controlpanel.php'>{$lang['gbl_adm_return_admin']}</a>
                             <br /> {$lang['gbl_adm_return_to']}<a href='index.php'>{$lang['gbl_adm_main_page']}</a>");
	}
	else
	{
	    error_message_center("error",
                             "{$lang['gbl_adm_error']}",
                             "{$lang['err_problem']}<br /><br />" . $db->error);
	}

    site_footer();
}

if (isset($_POST['password']) && ($_POST['password'] == "$password"))
{
    site_header("{$lang['title_update_sql']}", false);

	print("<form method='post' action='controlpanel.php?fileaction=31&amp;action=update'>");
	print("<table border='1' align='center' width='81%' cellspacing='0' cellpadding='5'>");

	print("<tr>
	            <td class='colhead' align='center'>{$lang['text_create']}</td>
	       </tr>");

 	print("<tr>
 	           <td class='rowhead' align='center'>
 	                <label for='db_sql'>{$lang['text_enter_sql']}</label>
 	           </td>
	       </tr>");

	print("<tr>
	           <td class='rowhead' align='center'>
	                <textarea name='db_sql' id='db_sql' cols='80' rows='20'></textarea>
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
}
else
{
    site_header("{$lang['title_update_sql']}", false);

    if (isset($_POST['password']) || $password == '')
    {
        print("<div align='center'>
                    <span class='inc_pass_update_sql'>{$lang['err_inc_pass']}</span><br />{$lang['err_cor_pass']}
               </div><br />");
    }

	print("<form method='post' action='controlpanel.php?fileaction=31'>");
	print("<table border='1' align='center' width='40%' cellspacing='0' cellpadding='5'>");

	print("<tr>
	            <td class='colhead' align='center' colspan='2'>{$lang['text_restrict']}<br /><br />{$lang['text_pro_pass']}</td>
	       </tr>");

	print("<tr>
	            <td class='rowhead'>
                    <label for='password'>{$lang['form_pass']}</label>
                </td>

	            <td class='rowhead'>
	                <input type='password' name='password' id='password' size='40' />
	            </td>
	       </tr>");

	print("<tr>
	           <td class='std' align='center' colspan='2'>
	                <input type='submit' class='btn' value='{$lang['gbl_adm_btn_submit']}' />
	           </td>
	       </tr>");

	print("</table>");
	print("</form>");

	print("<br />");

    site_footer();
}

?>