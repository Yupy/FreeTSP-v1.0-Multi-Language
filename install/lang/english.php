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
** Language File:- install/lang/english.php
**/

$lang = array
(

	##################
	##    Errors    ##
	##################

	'err_install_lock'       => 'This Installer is Locked!<br />You cannot Install unless you Delete the <strong>install.lock</strong> file.',
	'err_complete_form'      => 'You must complete all of the form',
	'err_conection'          => 'Connection error:<br /><br /><a href="javascript:history.back()"><span class="btn">Go Back</span></a><br /><br />And Try Again',
	'err_create_db'          => 'Unable to create database<br /><br /><a href="javascript:history.back()"><span class="btn">Go Back</span></a><br /><br />And Try Again',
	'err_write_config'       => 'Could not write to the <strong>function_config.php</strong>',
	'err_error'              => 'ERROR',


	##################
	##   Warnings   ##
	##################

	'warn_locate_file'       => 'Cannot locate the file',
	'warn_write_file'        => 'Cannot write to the file',
	'warn_chmod_file'        => 'Please CHMOD to 0777.',
	'warn_php_version'       => 'FreeTSP Tracker requires PHP Version',
	'warn_php_install'       => 'Your PHP installation is not sufficient to run FreeTSP Tracker.',
	'warn_safe_mode'         => 'FreeTSP Tracker will not run when safe_mode is on.',
	'warn_libary_version1'   => 'FreeTSP requires GD library version 2. The version on your server is',
	'warn_libary_version2'   => 'Find the upgrade here <a href="http://us.php.net/manual/en/image.setup.php">libgd library</a>.',
	'warn_mysql_libary'      => 'Your server does not appear to have a MySQL library, you will need this before you can continue.',
	'warn_rec_err'           => 'Warning!  The following errors must be rectified before continuing!',
	'warn_chmod_config'      => 'Warning, please chmod functions/function_config.php to 0777 via ftp or shell.',


	##################
	##   Buttons    ##
	##################

	'butt_continue'          => 'CONTINUE',
	'butt_go_back'           => 'Go Back',
	'butt_tm_account'        => 'Create Tracker Manager Account',


	##################
	##   stdhead    ##
	##################

	'std_welcome'            => 'Welcome',
	'std_set_up'             => 'Set Up form',
	'std_db_success'         => 'Database Success!',
	'std_config_sup'         => 'Config Set Up form',
	'std_wrote_config'       => 'Wrote Config Success!',
	'std_install_complete'   => 'Install Complete!',
	'std_warning'            => 'Warning!',


	##################
	##    Titles    ##
	##################

	'title_envo'             => 'Your Server Environment',
	'title_mysql'            => 'MySQL Settings',
	'title_general'          => 'General Settings',
	'title_tracker_settings' => 'General Tracker Settings',
	'title_db_success'       => 'Database Success',
	'title_db_installed'     => 'Your Database has been Installed!',
	'title_config_file'      => 'Setting up your Config file',
	'title_config_write'     => 'Success! Your configuration file was written to successfully!',
	'title_install_complete' => 'Installation Successfully Completed!',
	'title_warning'          => 'Warning!',
	'title_welcome'          => 'Welcome to the FreeTSP Tracker Installer',


	##################
	##     Text     ##
	##################

	'text_check_software'    => ' requires the following software installed to function at maximum quality.<br />',
	'text_better'            => ' or better.<br />',
	'text_db_better'         => ' Data Base or better.<br />',
	'text_check_info'        => 'You will also need the following information:',
	'text_mysql_db_name'     => 'Your mySQL database',
	'text_mysql_username'    => 'Your mySQL username',
	'text_mysql_password'    => 'Your mySQL password',
	'text_mysql_host'        => 'Your mySQL host address',
	'text_mysql_localhost'   => '(localhost is usually sufficient)',
	'text_site_url'          => 'Site URL',
	'text_ex_site_url'       => '( Example - http://www.yoursite.com )',
	'text_anno_url'          => 'Announce URL',
	'text_ex_anno_url'       => '( No ending slash - Example - http://www.yoursite.com/announce.php )',
	'text_site_name'         => 'Site Name',
	'text_almost_complete'   => 'The Installation process is almost complete.<br /> The next step will configure the tracker settings.',
	'text_install_complete'  => 'The Installation is now Complete!',
	'text_correct_errors'    => 'The following errors must be rectified before continuing!',
	'text_please'            => 'Please ',
	'text_try_again'         => ' And try again',
	'text_auto_setting'      => 'Check that this setting is correct, as it was automatic!',
	'text_tm_account'        => 'Create Tracker Manager Account',


	##################
	##   Long Text  ##
	##################

	'text_check_files'       => 'Before we go any further, please ensure that all the files have been uploaded, and that the file <b>function_config.php</b> <br />
								 ( You will find this in the functions folder.) <br />
								 Has suitable Permissions to allow this script to write to it ( 0777 is sufficient for all servers ).',

	'text_server_details'    => 'Once you have clicked on the proceed button,
								 you will be taken to the next page.<br />
								 Where you will be required to enter information regarding your server details.<br />
								 The Installer needs this infomation to Install your tracker.',

	'text_take_notice'       => '<strong>TAKE NOTICE:- Using this Installer WILL DELETE any current FREETSP database
	                             and overwrite any function_config.php file.</strong>',

	'text_sql_info'          => 'This section requires you to enter your SQL information.<br />
								 If in doubt, please check with your webhost before asking for support.<br />
								 You may choose to enter an existing database name.<br />
								 If you do not have an existing database, you can not create one from here.',

	'text_enter_info'        => 'This section requires you to enter your all information. If in doubt, please check with your webhost before asking for support.
								 Please note: Any settings you enter here will overwrite any settings in your function_config.php file!',

	'text_install_locked'    => 'Although the installer is now locked (to re-install, remove the file <strong>install.lock</strong>),
	                             for added security, please rename the install folder after installation is complete.',

	'text_install_remove'    => 'PLEASE REMOVE THE INSTALLER <strong>install.php</strong BEFORE CONTINUING!<br />
	                             Not doing this will open you up to a situation where anyone could delete your tracker data!',
);

?>