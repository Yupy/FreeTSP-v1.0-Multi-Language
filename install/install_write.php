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
**
** Based On The TBDev Installer
** Moddified To Work With FreeTSP By Fireknight
**/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

define('INSTALLER_ROOT_PATH', './');
define('FTSP_ROOT_PATH', '../');
define('CACHE_PATH', FTSP_ROOT_PATH);
define('REQ_PHP_VER', '4.3.0');
define('REQ_MYSQL_VER', '4.0.27');
define('FreeTSP_REV', 'FreeTSP v1.0');

$installer = new installer;

class installer
{
    var $htmlout = "";
    var $VARS = array();

    function installer ()
    {
        $this->VARS = array_merge($_GET, $_POST);

        if (file_exists(INSTALLER_ROOT_PATH.'install.lock'))
        {
			require_once (INSTALLER_ROOT_PATH.'lang/<#lang_file#>.php');
			$this->error_message_center("error", "{$lang['err_error']}", "{$lang['err_install_lock']}");
        }

        switch ($this->VARS['progress'])
        {
            case '1':
                $this->do_step_one();
                break;

            case '2':
                $this->do_step_two();
                break;

            case '3':
                $this->do_step_three();
                break;

            case '4':
                $this->do_step_four();
                break;

            case 'end':
                $this->do_end();
                break;

            default:
                $this->do_start();
                break;
        }
    }

    function do_start ()
    {
        require_once (INSTALLER_ROOT_PATH.'lang/<#lang_file#>.php');

        $this->stdhead($lang['std_welcome']);

        $this->htmlout .= "<h6>{$lang['title_welcome']}</h6><br />
                           <div class='box_content'>
                           <p>{$lang['text_check_files']}</p>

                           <br /><br />

                           <h3>".FreeTSP_REV." {$lang['text_check_software']}
                                 PhpMyAdmin - ".REQ_PHP_VER." {$lang['text_better']}
                                 MYSQL - ".REQ_MYSQL_VER." {$lang['text_db_better']}
                           </h3>

                           <br /><br />

                           {$lang['text_check_info']}

                           <ul>
                               <li>{$lang['text_mysql_db_name']}</li>
                               <li>{$lang['text_mysql_username']}</li>
                               <li>{$lang['text_mysql_password']}</li>
                               <li>{$lang['text_mysql_host']}&nbsp;{$lang['text_mysql_localhost']}</li>
                           </ul>

                           <br />

                           {$lang['text_server_details']}

                           <br /><br />

                           {$lang['text_take_notice']}";

        $warnings = array();

        $checkfiles = array(INSTALLER_ROOT_PATH."sql",
                            FTSP_ROOT_PATH."functions/function_config.php");

        $writeable = array(FTSP_ROOT_PATH."functions/function_config.php",
                           FTSP_ROOT_PATH."cache",
                           FTSP_ROOT_PATH."cache/last24",
                           FTSP_ROOT_PATH."forum_attachments",
                           FTSP_ROOT_PATH."torrents",);

        foreach ($checkfiles
                 AS
                 $cf)
        {
            if (!file_exists($cf))
            {
                $warnings[] = "{$lang['warn_locate_file']} '$cf'.";
            }
        }

        foreach ($writeable
                 AS
                 $cf)
        {
            if (!is_writeable($cf))
            {
                $warnings[] = "{$lang['warn_write_file']} '$cf'. {$lang['warn_chmod_file']}";
            }
        }

        $phpversion = phpversion();

        if ($phpversion < REQ_PHP_VER)
        {
            $warnings[] = "<strong>{$lang['warn_php_version']} ".REQ_PHP_VER." {$lang['text_better']}</strong>";
        }

        if (!function_exists('get_cfg_var'))
        {
            $warnings[] = "<strong>{$lang['warn_php_install']}</strong>";
        }

        if (function_exists('ini_get') AND @ini_get("safe_mode"))
        {
            $warnings[] = "<strong>{$lang['warn_safe_mode']}</strong>";
        }

        if (function_exists('gd_info'))
        {
            $gd   = gd_info();
            $fail = true;

            if ($gd["GD Version"])
            {
                preg_match("/.*?([\d\.]+).*?/", $gd["GD Version"], $matches);

                if ($matches[1])
                {
                    $gdversions = version_compare('2.0', $matches[1], '<=');

                    if (!$gdversions)
                    {
                        $fail = false;
                    }
                }
            }

            !$fail ? $warnings[] = "{$lang['warn_libary_version1']}'{$gd['GD Version']}'. {$lang['warn_libary_version2']}" : false;
        }

        $ext = get_loaded_extensions();

        if (!in_array('mysql', $ext))
        {
            $warnings[] = "<strong>{$lang['warn_mysql_libary']}</strong>";
        }

        if (count($warnings) > 0)
        {
            $err_string = implode("<br /><br />", $warnings);

            $this->htmlout .= "<br /><br />
                               <div class='error-box' style='width: 500px;'>
                                   <strong>{$lang['warn_rec_err']}</strong>
                                   <br /><br />
                                   $err_string
                               </div>";
        }
        else
        {
            $this->htmlout .= "<br /><br />
                               <div class='proceed-btn-div'>
                                   <a href='install.php?progress=1'><span class='btn'>{$lang['butt_continue']}</span></a>
                               </div>";
        }

        $this->htmlout .= "</div>";

        $this->htmlout();
    }

    function do_step_one ()
    {
        require_once (INSTALLER_ROOT_PATH.'lang/<#lang_file#>.php');

        $this->stdhead($lang['std_set_up']);

        $this->htmlout .= "<h6>{$lang['title_welcome']}</h6><br />
                           <div class='box_content'>
                               <form method='post' action='install.php'>
                           <div>
                               <input type='hidden' name='progress' value='2' />
                           </div>
                           <h2>{$lang['title_envo']}</h2>";

        $this->htmlout .= "<p>{$lang['text_sql_info']}</p>

                           <fieldset>
                           <legend><strong>{$lang['title_mysql']}</strong></legend>

                           <fieldset>
                               <legend><strong>{$lang['text_mysql_host']}</strong></legend>
                               <input type='text' name='mysql_host' value='' />
                               {$lang['text_mysql_localhost']}
                           </fieldset>

                           <fieldset>
                               <legend><strong>{$lang['text_mysql_db_name']}</strong></legend>
                               <input type='text' name='mysql_db' value='' />
                           </fieldset>

                           <fieldset>
                               <legend><strong>{$lang['text_mysql_username']}</strong></legend>
                               <input type='text' name='mysql_user' value='' />
                           </fieldset>

                           <fieldset>
                               <legend><strong>{$lang['text_mysql_password']}</strong></legend>
                               <input type='text' name='mysql_pass' value='' />
                           </fieldset>

                           </fieldset>

                           <fieldset>
                           <legend><strong>{$lang['title_general']}</strong></legend>

                           <fieldset>
                               <legend><strong>{$lang['text_site_url']}</strong></legend>
                               <input type='text' name='site_url' value='http://' />
                               {$lang['text_ex_site_url']}
                           </fieldset>

                           <fieldset>
                               <legend><strong>{$lang['text_anno_url']}</strong></legend>
                               <input type='text' name='announce_url' value='http://' />
                               {$lang['text_ex_anno_url']}
                           </fieldset>

                           <fieldset>
                               <legend><strong>{$lang['text_site_name']}</strong></legend>
                               <input type='text' name='site_name' value='' />
                           </fieldset>

                           </fieldset>

                           <div class='proceed-btn-div'>
                               <input class='btn' type='submit' value='{$lang['butt_continue']}' />
                           </div>
                           </form>
                           </div>";
        $this->htmlout();
    }

    function do_step_two ()
    {
        global $db;
        require_once (INSTALLER_ROOT_PATH.'lang/<#lang_file#>.php');

        $in = array('mysql_host',
                    'mysql_db',
                    'mysql_user',
                    'mysql_pass',
                    'site_url',
                    'announce_url',
                    'site_name');

        foreach ($in
                 AS
                 $out)
        {
            if ($this->VARS[$out] == "")
            {
                $this->error_message_center("error", "{$lang['err_error']}", "{$lang['err_complete_form']}");
            }
        }

        if (!@$db = new mysqli($this->VARS['mysql_host'], $this->VARS['mysql_user'], $this->VARS['mysql_pass']))
        {
            $this->error_message_center("error", "{$lang['err_error']}", "{$lang['err_conection']}<br /><br />
                                                  [".$db->errno."] dbconn: mysql_connect: <br /><br />".$db->error);
        }
        //mysql_select_db($FTSP['mysql_db']) or die('dbconn: mysql_select_db: '.mysql_error());
        //mysql_set_charset('utf8');

        if (!$db->select_db($this->VARS['mysql_db']))
        {
            if (!$db->query("CREATE DATABASE {$this->VARS['mysql_db']} DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci"))
            {
                $this->error_message_center("error", "{$lang['err_error']}", "{$lang['err_create_db']}");
            }
            $db->select_db($this->VARS['mysql_db']);
        }
        else
        {
            $db->select_db($this->VARS['mysql_db']);
        }

        require_once(INSTALLER_ROOT_PATH.'sql/mysql_tables.php');
        require_once(INSTALLER_ROOT_PATH.'sql/mysql_inserts.php');

        foreach ($TABLE
                 AS
                 $q)
        {
            preg_match("/CREATE TABLE (\S+) \(/", $q, $match);

            if ($match[1])
            {
                $db->query("DROP TABLE {$match[1]}");
            }

            if (!$db->query($q))
            {
                $this->error_message_center("error", "{$lang['err_error']}", "$q<br /><br />".$db->error);
            }
        }

        foreach ($INSERT
                 AS
                 $q)
        {
            if (!$db->query($q))
            {
               $this->error_message_center("error", "{$lang['err_error']}", "$q<br /><br />".$db->error);
            }
        }

        $db->query("UPDATE config SET mysql_host=('".$_POST['mysql_host']."'),mysql_db=('".$_POST['mysql_db']."'),mysql_user=('".$_POST['mysql_user']."'),mysql_pass=('".$_POST['mysql_pass']."'),site_url=('".$_POST['site_url']."'),announce_url=('".$_POST['announce_url']."'),site_name=('".$_POST['site_name']."')");

        $this->stdhead($lang['std_db_success']);

        $this->htmlout .= "<h6>{$lang['title_welcome']}</h6><br />
                           <div class='box_content'>
                               <h2>{$lang['title_db_success']}</h2>
                               <br /><br />

                               <div align='center'>
                                   <strong>{$lang['title_db_installed']}</strong>
                                   <br /><br />
                                   {$lang['text_almost_complete']}
                               </div>

                               <br /><br />
                               <form method='post' action='install.php'>

							   <div>
								   <input type='hidden' name='progress' value='3' />
								   <input type='hidden' name='mysql_host' value='{$this->VARS['mysql_host']}' />
								   <input type='hidden' name='mysql_db' value='{$this->VARS['mysql_db']}' />
								   <input type='hidden' name='mysql_user' value='{$this->VARS['mysql_user']}' />
								   <input type='hidden' name='mysql_pass' value='{$this->VARS['mysql_pass']}' />
								   <input type='hidden' name='site_url' value='{$this->VARS['site_url']}' />
								   <input type='hidden' name='announce_url' value='{$this->VARS['announce_url']}' />
								   <input type='hidden' name='site_name' value='{$this->VARS['site_name']}' />
							   </div>

							   <div class='proceed-btn-div'>
							       <input class='btn' type='submit' value='{$lang['butt_continue']}' />
							   </div>

                               </form>
                           </div>";

        $this->htmlout();
    }

    function do_step_three ()
    {
		require_once (INSTALLER_ROOT_PATH.'lang/<#lang_file#>.php');

		$this->stdhead($lang['std_config_sup']);

		$this->htmlout .= "<h6>{$lang['title_welcome']}</h6><br />
		                   <div class='box_content'>
						   <form method='post' action='install.php'>

						   <div>
							   <input type='hidden' name='progress' value='4' />
						   </div>

						   <h2>{$lang['title_config_file']}</h2>";

		$this->htmlout .= "<p>{$lang['text_enter_info']}</p>

						   <fieldset>
						   <legend><strong>{$lang['title_mysql']}</strong></legend>

						   <div class='form-field'>
							   <label>{$lang['text_mysql_host']}</label>
							   <input type='text' name='mysql_host' value='{$this->VARS['mysql_host']}' /><br />
						   </div>

						   <div class='form-field'>
							   <label>{$lang['text_mysql_db_name']}</label>
							   <input type='text' name='mysql_db' value='{$this->VARS['mysql_db']}' /><br />
						   </div>

						   <div class='form-field'>
							   <label>{$lang['text_mysql_username']}</label>
							   <input type='text' name='mysql_user' value='{$this->VARS['mysql_user']}' /><br />
						   </div>

						   <div class='form-field'>
							   <label>{$lang['text_mysql_password']}</label>
							   <input type='text' name='mysql_pass' value='{$this->VARS['mysql_pass']}' /><br />
						   </div>

						   </fieldset>

						   <fieldset>
						   <legend><strong>{$lang['title_tracker_settings']}</strong></legend>

						   <div class='form-field'>
							   <label>{$lang['text_site_url']}</label>
							   <input type='text' name='site_url' value='{$this->VARS['site_url']}' />
							   <br /><span class='form-field-info'>{$lang['text_auto_setting']}</span>
						   </div>

						   <div class='form-field'>
							   <label>{$lang['text_anno_url']}</label>
							   <input type='text' name='announce_url' value='{$this->VARS['announce_url']}' />
							   <br /><span class='form-field-info'>{$lang['text_auto_setting']}</span>
						  </div>

						  <div class='form-field'>
							  <label>{$lang['text_site_name']}</label>
							  <input type='text' name='site_name' value='{$this->VARS['site_name']}' />
						  </div>
						  </fieldset>

						  <div class='proceed-btn-div'>
							  <input class='btn' type='submit' value='{$lang['butt_continue']}' />
						  </div>

						  </form>
						  </div>";

        $this->htmlout();
    }

    function do_step_four ()
    {
        require_once (INSTALLER_ROOT_PATH.'lang/<#lang_file#>.php');

        $DB = "";

        $NEW_INFO = array();

        $in = array('mysql_host',
                    'mysql_db',
                    'mysql_user',
                    'mysql_pass',
                    'site_url',
                    'announce_url',
                    'site_name');
        //print_r($this->VARS); exit;
        foreach ($in
                 AS
                 $out)
        {
            if ($this->VARS[$out] == "")
            {
                require_once (INSTALLER_ROOT_PATH.'lang/<#lang_file#>.php');
			    $this->error_message_center("error", "{$lang['err_error']}", "{$lang['err_complete_form']}");
            }
        }

        // open config_dist.php
        $conf_string = file_get_contents(INSTALLER_ROOT_PATH.'config_dist.php');

        $placeholders = array('<#mysql_host#>',
                              '<#mysql_db#>',
                              '<#mysql_user#>',
                              '<#mysql_pass#>',
                              '<#announce_url#>',
                              '<#site_url#>',
                              '<#site_name#>');

        $replacements = array($this->VARS['mysql_host'],
                              $this->VARS['mysql_db'],
                              $this->VARS['mysql_user'],
                              $this->VARS['mysql_pass'],
                              $this->VARS['announce_url'],
                              $this->VARS['site_url'],
                              $this->VARS['site_name']);

        $conf_string = str_replace($placeholders, $replacements, $conf_string);

        if ($fh = fopen(FTSP_ROOT_PATH.'functions/function_config.php', 'w'))
        {
            fputs($fh, $conf_string, strlen($conf_string));
            fclose($fh);
        }
        else
        {
            require_once (INSTALLER_ROOT_PATH.'lang/<#lang_file#>.php');
		    $this->error_message_center("error", "{$lang['err_error']}", "{$lang['err_write_config']}");
        }

        $this->stdhead($lang['std_wrote_config']);

        $this->htmlout .= "<h6>{$lang['title_welcome']}</h6><br />
                           <div class='box_content'>
                               <h2>{$lang['title_config_write']}</h2>
                               <br /><br />

                               <div class='proceed-btn-div'>
                                   <a href='install.php?progress=end'><span class='btn'>{$lang['butt_continue']}</span></a>
                               </div>

                           </div>";

        $this->htmlout();
    }

    function do_end ()
    {
        require_once (INSTALLER_ROOT_PATH.'lang/<#lang_file#>.php');

        if ($FH = @fopen(INSTALLER_ROOT_PATH.'install.lock', 'w'))
        {
            @fwrite($FH, date(DATE_RFC822), 40);
            @fclose($FH);

            @chmod(INSTALLER_ROOT_PATH.'install.lock', 0666);

            $this->stdhead($lang['std_install_complete']);

            $txt = "{$lang['text_install_locked']}
					<br /><br /><br />
					<div style='text-align: center;'>
					    <a href='../login.php'><span class='btn'>{$lang['butt_tm_account']}</span></a>
					</div>";
        }
        else
        {
            $this->stdhead($lang['std_install_complete']);

            $txt = "{$lang['text_install_remove']}
                    <br /><br />
                    <div style='text-align: center;'>
                        <a href='../login.php'><span class='btn'>{$lang['butt_tm_account']}</span></a>
                    </div>";
        }

        $warn = '';

        if (!@chmod(FTSP_ROOT_PATH.'functions/function_config.php', 0644))
        {
            $warn .= "<br />{$lang['warn_chmod_config']}";
        }

        $this->htmlout .= "
            <div class='box_content'>
                <h2>{$lang['title_install_complete']}</h2>
                <br />
                <strong>{$lang['text_install_complete']}</strong>
                {$warn}
                <br /><br />
                {$txt}
            </div>";

        $this->htmlout();
    }

    //-- Worker Functions --//

    function error_message_center ($type, $heading, $message)
	{

	    $this->stdhead($lang['std_warning']);

	    $this->htmlout .= "<table align='center' width='50%'>
	                       <tr>
	                       <td class='embedded'>";

	    if ($heading)
	    {
	        $this->htmlout .= "<div class='notice notice-$type' align='center'>
	                           <h2>$heading</h2>";
	    }

	    $this->htmlout .= "<p>$message</p><span></span></div>
	                       </td></tr></table>";


        $this->htmlout();
}


    function htmlout ()
    {
        echo $this->htmlout;
        echo "</div>
            <div id='siteInfo'><p class='center'>
                <a href='http://www.freetsp.info'><img src='/images/button.png' alt='Powered By FreeTSP v1.0 &copy;&nbsp;2010 - 2012' title='Powered By FreeTSP v1.0 &copy;&nbsp;2010 - 2012' /></a></p>
            </div>

        </body></html>";
        exit();
    }

    function stdhead ($title = "")
    {
        $this->htmlout = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
            \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
        <html xmlns=\"http://www.w3.org/1999/xhtml\">

            <head>

                <meta name='generator' content='FreeTSP' />
                <meta http-equiv='Content-Language' content='en-us' />
                <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />

                <title>FTSP.INFO :: {$title}</title>
                <link rel='stylesheet' href='1.css' type='text/css' />

            </head>

        <body>

          <div class='text-header' style='text-align:center;'><img src='/images/logo.png' alt='' /><br /><h6>{$lang['title_welcome']}</h6></div>

        <div>";
    }

    function mksecret ($len = 5)
    {
        $salt = '';

        for ($i = 0;
             $i < $len;
             $i++)
        {
            $num = mt_rand(33, 126);

            if ($num == '92')
            {
                $num = 93;
            }

            $salt .= chr($num);
        }

        return $salt;
    }

    function make_passhash ($salt, $md5_once_password)
    {
        return md5(md5($salt).$md5_once_password);
    }

} //-- End Class --//

?>