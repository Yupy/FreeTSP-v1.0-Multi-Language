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

function portblacklisted($port) {
	$blackports = array (
		array(135, 139),	// Winodws
		array(445, 445),	// Netbios
		array(411, 413),	// DC++
		array(6881,6889),	// Bittorrent
		array(1214,1214),	// Kazaa
		array(6346,6347),	// Gnutella
		array(4662,4662),	// eMule
		array(6699,6699),	// WinMX
	);
	foreach ($blackports as $b) {
		if ($port >= $b[0] && $port <= $b[1])
			return true;
	}
	return false;
}

function err($msg) {
    benc_resp(array('failure reason' => array('type'  => 'string',
                                              'value' => $msg)));
    exit();
}

function benc_resp($d) {
    benc_resp_raw(benc(array('type'  => 'dictionary',
                             'value' => $d)));
}

function benc_resp_raw($x) {
    header("Content-Type: text/plain");
    header("Pragma: no-cache");
    print($x);
}

?>