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

const REQUIRED_PHP = 50300, REQUIRED_PHP_VERSION = '5.3.0';

if (PHP_VERSION_ID < REQUIRED_PHP)
	die('PHP '.REQUIRED_PHP_VERSION.' or higher is required.');

if (get_magic_quotes_gpc() || get_magic_quotes_runtime() || ini_get('magic_quotes_sybase'))
	die('PHP is configured incorrectly. Turn off magic quotes.');

if (ini_get('register_long_arrays') || ini_get('register_globals') || ini_get('safe_mode'))
	die('PHP is configured incorrectly. Turn off safe_mode, register_globals and register_long_arrays.');

if (ini_get('mbstring.func_overload') || ini_get('mbstring.encoding_translation'))
    die('PHP is configured incorrectly. Turn off mbstring.func_overload and mbstring.encoding_translation, mult-byte function overloading, FreeTSP is fully multi-byte aware.');

if (!extension_loaded('zlib'))
    die('zlib Extension has not been loaded or not installed !');

if (!extension_loaded('memcache'))
    die('Memcache Extension has not been loaded or not installed !');

header('X-Frame-Options: DENY');

if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_SERVER)))
    die('Forbidden');
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_GET)))
    die('Forbidden');
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_POST)))
    die('Forbidden');
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_COOKIE)))
    die('Forbidden');
    
?>