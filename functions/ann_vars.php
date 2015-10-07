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

$agent      = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$sp_compact = isset($_GET['compact']) ? true : false;

foreach (array("passkey",
               "info_hash",
               "peer_id",
               "ip",
               "event")
            AS
            $x)
{
    $GLOBALS[$x] = ''.$_GET[$x];
}

foreach (array("port",
               "downloaded",
               "uploaded",
               "left")
            AS
            $x)
{
    $GLOBALS[$x] = 0 + $_GET[$x];
}

if (strpos($passkey, "?")) {
    $tmp      = substr($passkey, strpos($passkey, "?"));
    $passkey  = substr($passkey, 0, strpos($passkey, "?"));
    $tmpname  = substr($tmp, 1, strpos($tmp, "=") - 1);
    $tmpvalue = substr($tmp, strpos($tmp, "=") + 1);

    $GLOBALS[$tmpname] = $tmpvalue;
}

foreach (array("passkey",
               "info_hash",
               "peer_id",
               "port",
               "downloaded",
               "uploaded",
               "left")
            AS
            $x)
{
    if (!isset($x)) {
        err('Missing Key: $x');
    }
}

foreach (array("info_hash",
               "peer_id")
            AS
            $x)
{
    if (strlen($GLOBALS[$x]) != 20) {
        err("Invalid $x (" . strlen($GLOBALS[$x]) . " - " . urlencode($GLOBALS[$x]) . ")");
    }
}

if (strlen($passkey) != 32) {
    err("Invalid Passkey (" . strlen($passkey) . " - $passkey)");
}

$ip         = vars::$ip;
$port       = 0 + $port;
$downloaded = 0 + $downloaded;
$uploaded   = 0 + $uploaded;
$left       = 0 + $left;
$rsize      = 50;

foreach (array("num want",
               "numwant",
               "num_want")
            AS
            $k)
{
    if (isset($_GET[$k])) {
        $rsize = 0 + $_GET[$k];
        break;
    }
}

?>