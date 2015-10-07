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

class security {
	const REPLACE_COMPAT = ENT_COMPAT;
	const REPLACE_XHTML = ENT_XHTML;
	const CHARSET = 'UTF-8';
    const URL_REPLACE = '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i';
	
	private static $valid_tlds = array(
		'ac','ad','ae','af','ag','ai','al','am','an','ao','aq',
		'ar','as','at','au','aw','az','ax','ba','bb','bd','be',
		'bf','bg','bh','bi','bj','bm','bn','bo','br','bs','bt',
		'bv','bw','by','bz','ca','cc','cd','cf','cg','ch','ci',
		'ck','cl','cm','cn','co','cr','cs','cu','cv','cx','cy',
		'cz','de','dj','dk','dm','do','dz','ec','ee','eg','eh',
		'er','es','et','eu','fi','fj','fk','fm','fo','fr','ga',
		'gb','gd','ge','gf','gg','gh','gi','gl','gm','gn','gp',
		'gq','gr','gs','gt','gu','gw','gy','hk','hm','hn','hr',
		'ht','hu','id','ie','il','im','in','io','iq','ir','is',
		'it','je','jm','jo','jp','ke','kg','kh','ki','km','kn',
		'kp','kr','kw','ky','kz','la','lb','lc','li','lk','lr',
		'ls','lt','lu','lv','ly','ma','mc','md','mg','mh','mk',
		'ml','mm','mn','mo','mp','mq','mr','ms','mt','mu','mv',
		'mw','mx','my','mz','na','nc','ne','nf','ng','ni','nl',
		'no','np','nr','nu','nz','om','pa','pe','pf','pg','ph',
		'pk','pl','pm','pn','pr','ps','pt','pw','py','qa','re',
		'ro','ru','rw','sa','sb','sc','sd','se','sg','sh','si',
		'sj','sk','sl','sm','sn','so','sr','st','sv','sy','sz',
		'tc','td','tf','tg','th','tj','tk','tl','tm','tn','to',
		'tp','tr','tt','tv','tw','tz','ua','ug','uk','um','us',
		'uy','uz','va','vc','ve','vg','vi','vn','vu','wf','ws',
		'ye','yt','yu','za','zm','zw','biz','com','info','name',
		'net','org','edu','xxx','me',

		#'aero','gov','travel','pro','int','mil','jobs','mobi' #Example of Domains we don't want to see on our Tracker =]
	);

        public static function html_safe($string) {
               return htmlspecialchars($string, self::REPLACE_COMPAT | self::REPLACE_XHTML, self::CHARSET);
        }

        public static function esc_url($url) {
                if ('' == $url) {
                    return $url;
                }
 
                $url = preg_replace(self::URL_REPLACE, '', $url);
 
                $strip = array('%0d', '%0a', '%0D', '%0A');
                $url = (string) $url;
 
                $count = 1;
                while ($count) {
                       $url = str_replace($strip, '', $url, $count);
                }
 
                $url = str_replace(';//', '://', $url);
                $url = htmlentities($url);
                $url = str_replace('&amp;', '&#038;', $url);
                $url = str_replace("'", '&#039;', $url);
 
                if ($url[0] !== '/') {
                    // We're only interested in relative links from $_SERVER['PHP_SELF']
                    return '';
                } else {
                    return $url;
                }
        }

	public static function valid_email($email) {
		if (preg_match('/^[\w.+-]+@(?:[\w.-]+\.)+([a-z]{2,6})$/isD', $email, $m)) {
			if (self::valid_tld($m[1]))
				return true;
		}
		return false;
	}

	private static function valid_tld($tld) {
		$tld = strtolower($tld);
		if (in_array($tld, self::$valid_tlds, true))
			return true;
		else
			return false;
	}

}

?>