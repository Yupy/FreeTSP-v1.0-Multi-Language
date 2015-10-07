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
** Modified To Work With FreeTSP By Fireknight
**/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

define('INSTALLER_ROOT_PATH', './');
define('FTSP_ROOT_PATH', '../');
define('CACHE_PATH', FTSP_ROOT_PATH);
define('REQ_PHP_VER', '4.3.0');
define('REQ_MYSQL_VER', '4.0.27');
define('FreeTSP_REV', 'FreeTSP v1.0');

$posted_action   = strip_tags((isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '')));

//-- Add All Possible Actions Here, Separated By A Comma And Check Them To Be Sure They Are OK --//
$valid_actions = array('english',
                       'french',
                       'romanian',
                       'spanish',
                       'swedish');

//-- Check Posted Action, And If No Action Was Posted, Show The Default Page --//
$action = (in_array($posted_action, $valid_actions) ? $posted_action : 'default');


switch ($action)
{

	case 'default':
	do_language();
	break;

	case 'english':
	do_english();
	break;

	case 'french':
	do_french();
	break;

	case 'romanian':
	do_romanian();
	break;

	case 'spanish':
	do_spanish();
	break;

	case 'swedish':
	do_swedish();
	break;

}

function do_language()
{
	site_header();

		print("<div class='box_content'>");

		print("<p align='center'>Please choose your prefered language<br /></p>");

		print("<div align='center'>
				   <form method='post' action='index.php'>

					   <select name='action'>
						   <option name='action' value='0' />----Select----</option>
						   <option name='action' value='english' />English</option>
						   <option name='action' value='french' />French</option>
						   <option name='action' value='romanian' />Romanian</option>
						   <option name='action' value='spanish' />Spanish</option>
						   <option name='action' value='swedish' />Swedish</option>
					   </select>

					   <div class='proceed-btn-div' align='center'><input type='submit' class='btn' value='Submit' /></div>

				   </form>
				</div>");

		print("</div>");

	site_footer();
}

function do_english()
{
	site_header();

		//-- Open install_write.php --//
		$conf_string = file_get_contents(INSTALLER_ROOT_PATH.'install_write.php');

		$placeholders = '<#lang_file#>';
		$replacements = 'english';

		$conf_string = str_replace($placeholders, $replacements, $conf_string);

		if ($fh = fopen(INSTALLER_ROOT_PATH.'install.php', 'w'))
		{
			fputs($fh, $conf_string, strlen($conf_string));
			fclose($fh);
		}

		print("<div class='box_content'>
				   <p align='center'>You Have Chosen To Use The English Language</p>");

		print("<div align='center'>
				   <div class='proceed-btn-div' align='center'>
					   <a href='install.php'><input type='submit' class='btn' value='Continue' /></a>
				   </div>
			   </div>");

		print("<p align='center'>Wrong Language -- Then Press Return And Start Again</p>");

		print("<div align='center'>
				  <div class='proceed-btn-div' align='center'>
					  <a href='index.php'><input type='submit' class='btn' value='Return' /></a>
				  </div>
			  </div>");

		print("</div>");

	site_footer();
}

function do_french()
{
	site_header();
			//-- Open install_write.php --//
			$conf_string = file_get_contents(INSTALLER_ROOT_PATH.'install_write.php');


			$placeholders = '<#lang_file#>';
			$replacements = 'french';

			$conf_string = str_replace($placeholders, $replacements, $conf_string);

			if ($fh = fopen(INSTALLER_ROOT_PATH.'install.php', 'w'))
			{
				fputs($fh, $conf_string, strlen($conf_string));
				fclose($fh);
			}

		print("<div class='box_content'>
				   <p align='center'>Vous avez choisi d'utiliser la langue française</p>");

		print("<div align='center'>
				   <div class='proceed-btn-div' align='center'>
					   <a href='install.php'><input type='submit' class='btn' value='Continuer' /></a>
				   </div>
			   </div>");

		print("<p align='center'>Wrong Language -- Then Press Return And Start Again</p>");

		print("<div align='center'>
				  <div class='proceed-btn-div' align='center'>
					  <a href='index.php'><input type='submit' class='btn' value='Return' /></a>
				  </div>
			  </div>");

		print("</div>");

	site_footer();
}

function do_romanian()
{
	site_header();
		//-- Open install_write.php --//
		$conf_string = file_get_contents(INSTALLER_ROOT_PATH.'install_write.php');


		$placeholders = '<#lang_file#>';
		$replacements = 'romanian';

		$conf_string = str_replace($placeholders, $replacements, $conf_string);

		if ($fh = fopen(INSTALLER_ROOT_PATH.'install.php', 'w'))
		{
			fputs($fh, $conf_string, strlen($conf_string));
			fclose($fh);
		}

		print("<div class='box_content'>
				   <p align='center'>Ai ales sa folosesti limba Romana</p>");

		print("<div align='center'>
				   <div class='proceed-btn-div' align='center'>
					   <a href='install.php'><input type='submit' class='btn' value='Contiuna' /></a>
				   </div>
			   </div>");

		print("<p align='center'>Wrong Language -- Then Press Return And Start Again</p>");

		print("<div align='center'>
				  <div class='proceed-btn-div' align='center'>
					  <a href='index.php'><input type='submit' class='btn' value='Return' /></a>
				  </div>
			  </div>");

		print("</div>");

	site_footer();
}

function do_spanish()
{
	site_header();
		//-- Open install_write.php --//
		$conf_string = file_get_contents(INSTALLER_ROOT_PATH.'install_write.php');


		$placeholders = '<#lang_file#>';
		$replacements = 'spanish';

		$conf_string = str_replace($placeholders, $replacements, $conf_string);

		if ($fh = fopen(INSTALLER_ROOT_PATH.'install.php', 'w'))
		{
			fputs($fh, $conf_string, strlen($conf_string));
			fclose($fh);
		}

		print("<div class='box_content'>
				   <p align='center'>Usted esta usando el idioma Española</p>");

		print("<div align='center'>
				   <div class='proceed-btn-div' align='center'>
					   <a href='install.php'><input type='submit' class='btn' value='Continuar' /></a>
				   </div>
			   </div>");

		print("<p align='center'>Wrong Language -- Then Press Return And Start Again</p>");

		print("<div align='center'>
				  <div class='proceed-btn-div' align='center'>
					  <a href='index.php'><input type='submit' class='btn' value='Return' /></a>
				  </div>
			  </div>");

		print("</div>");

	site_footer();
}

function do_swedish()
{
	site_header();
		//-- Open install_write.php --//
		$conf_string = file_get_contents(INSTALLER_ROOT_PATH.'install_write.php');


		$placeholders = '<#lang_file#>';
		$replacements = 'swedish';

		$conf_string = str_replace($placeholders, $replacements, $conf_string);

		if ($fh = fopen(INSTALLER_ROOT_PATH.'install.php', 'w'))
		{
			fputs($fh, $conf_string, strlen($conf_string));
			fclose($fh);
		}

		print("<div class='box_content'>
				   <p align='center'>Du har valt att använda Svenska som språk.</p>");

		print("<div align='center'>
				   <div class='proceed-btn-div' align='center'>
					   <a href='install.php'><input type='submit' class='btn' value='Fortsätt' /></a>
				   </div>
			   </div>");

		print("<p align='center'>Wrong Language -- Then Press Return And Start Again</p>");

		print("<div align='center'>
				  <div class='proceed-btn-div' align='center'>
					  <a href='index.php'><input type='submit' class='btn' value='Return' /></a>
				  </div>
			  </div>");

		print("</div>");

	site_footer();
}


##======================##
##== Worker Functions ==##
##======================##


function site_header($title = "")
{
?>
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
	<html xmlns=\"http://www.w3.org/1999/xhtml\">

	<head>
		<meta name='generator' content='FreeTSP' />
		<meta http-equiv='Content-Language' content='en-us' />
		<meta http-equiv='Content-Type' content='text/html' />

		<title>FTSP.INFO :: Installer</title>
		<link rel='stylesheet' href='1.css' type='text/css' />
	</head>

	<body>
		<div class='text-header' style='text-align:center;'><img src='/images/logo.png' alt='' /><br /><h6>Welcome to the FreeTSP Tracker Installer</h6></div>
		<div>
<?php
}

function site_footer()
{
?>
		</div>
		<div id='siteInfo'><p class='center'>
		<a href='http://www.freetsp.info'><img src='/images/button.png' alt='Powered By FreeTSP v1.0 &copy;&nbsp;2010 - 2012' title='Powered By FreeTSP v1.0 &copy;&nbsp;2010 - 2012' /></a>
		</div>

	</body>
	</html>
<?php
}
?>