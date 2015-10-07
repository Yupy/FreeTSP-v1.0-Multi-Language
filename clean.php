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
require_once(FUNC_DIR . 'function_vfunctions.php');

if (!local_user())
{
    die;
}

db_connect();

$res = $db->query("SELECT id, torrent
                  FROM peers") or sqlerr();

$n = 0;

while ($arr = $res->fetch_assoc())
{
    $res2 = $db->query("SELECT id
                       FROM torrents
                       WHERE id = " . (int)$arr['torrent']) or sqlerr();

    if ($res2->num_rows == 0)
    {
        ++$n;
    }
}

echo $n;

?>