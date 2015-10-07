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
require_once(FUNC_DIR . 'define_bits.php');

class misc {
        const MB = 1024;
	    const MINUTE = 60;
	    const HOUR = 3600;
	    const DAY = 86400;
	    const WEEK = 604800;
	    const MONTH = 2629744;
	    const YEAR = 31556926;
		const STATS_EXPIRE = 30;
	
	    const PAGER_SHOW_PAGES = BIT_1;
	    const PAGER_NO_SEPARATOR = BIT_2;
	    const PAGER_LAST_PAGE_DEFAULT = BIT_3;
	    const PAGER_NO_NAV = BIT_4;
	    const PAGER_ONLY_PAGES = BIT_5;
		
		#mysqli_result function... This does not exist in mysqli so we use this
        public static function mysqli_result($res, $row, $field = 0) {
            $res->data_seek($row);
            $datarow = $res->fetch_array();
            return $datarow[$field];
        }

        public static function debug() {
            global $time_start, $Memcache, $tpl;

		    raintpl::configure('base_url', null);
            raintpl::configure('tpl_dir', 'stylesheets/tpl/');
            raintpl::configure('cache_dir', 'cache/');

            $tpl = new RainTPL;
            $max_mem = memory_get_peak_usage();
	        $cachetime = ($Memcache->Time / 1000);
	        $seconds = microtime(true) - $time_start;
	        $per_memcache = number_format(($cachetime / $seconds) * 100, 2);

            if (($memcache_stats = $Memcache->get_value('memcache::hits')) === false) {
                $memcache_stats = $Memcache->getStats();
                $memcache_stats['Hits'] = (($memcache_stats['get_hits'] / $memcache_stats['cmd_get'] < 0.7) ? '' : number_format(($memcache_stats['get_hits'] / $memcache_stats['cmd_get']) * 100, 3));
                $Memcache->cache_value('memcache::hits', $memcache_stats, self::STATS_EXPIRE);
            }

			$var_cache_time = number_format($cachetime, 5);
	        $tpl->assign('cache_time', $var_cache_time);

			$var_hits = $memcache_stats['Hits'];
	        $tpl->assign('hits', $var_hits);

			$var_misses = (100 - $memcache_stats['Hits']);
	        $tpl->assign('misses', $var_misses);

			$var_items = number_format($memcache_stats['curr_items']);
	        $tpl->assign('items', $var_items);

			$var_memory_usage = misc::mksize($max_mem);
	        $tpl->assign('memory_usage', $var_memory_usage);

	        $debug = $tpl->draw('debug', $return_string = true );
            echo $debug;
        }

	    public static function mksize($Size, $Levels = 2) {
		    $Units = array(' BiT', ' KiB', ' MiB', ' GiB', ' TiB', ' PiB', ' EiB', ' ZiB', ' YiB');
		    $Size = (double)$Size;
		    for ($Steps = 0; abs($Size) >= self::MB; $Size /= self::MB, $Steps++) {
		    }
		    if (func_num_args() == 1 && $Steps >= 4) {
			    $Levels++;
		    }
		    return number_format($Size, $Levels) . $Units[$Steps];
	    }
	
        public static function time_ago($timestamp) {
            $timestamp = (int)$timestamp;
            $current_time = vars::$timestamp;
            $diff = $current_time - $timestamp;

            //intervals in seconds
            $intervals = array (
                'year' => self::YEAR, 'month' => self::MONTH, 'week' => self::WEEK, 'day' => self::DAY, 'hour' => self::HOUR, 'minute'=> self::MINUTE
            );

            //now we just find the difference
            if ($diff == 0) {
                return 'just now';
            }

            if ($diff < self::MINUTE) {
                return $diff == 1 ? $diff . ' second ago' : $diff . ' seconds ago';
            }

            if ($diff >= self::MINUTE && $diff < $intervals['hour']) {
                $diff = floor($diff / $intervals['minute']);
                return $diff == 1 ? $diff . ' minute ago' : $diff . ' minutes ago';
            }

            if ($diff >= $intervals['hour'] && $diff < $intervals['day']) {
                $diff = floor($diff / $intervals['hour']);
                return $diff == 1 ? $diff . ' hour ago' : $diff . ' hours ago';
            }

            if ($diff >= $intervals['day'] && $diff < $intervals['week']) {
                $diff = floor($diff / $intervals['day']);
                return $diff == 1 ? $diff . ' day ago' : $diff . ' days ago';
            }

            if ($diff >= $intervals['week'] && $diff < $intervals['month']) {
                $diff = floor($diff / $intervals['week']);
                return $diff == 1 ? $diff . ' week ago' : $diff . ' weeks ago';
            }

            if ($diff >= $intervals['month'] && $diff < $intervals['year']) {
                $diff = floor($diff / $intervals['month']);
                return $diff == 1 ? $diff . ' month ago' : $diff . ' months ago';
            }

            if ($diff >= $intervals['year']) {
                $diff = floor($diff / $intervals['year']);
                return $diff == 1 ? $diff . ' year ago' : $diff . ' years ago';
            }
        }
	
        public static function make_utf8($Str) {
	        if ($Str != '') {
		        if (self::is_utf8($Str)) {
			        $Encoding = 'UTF-8';
		        }
		        if (empty($Encoding)) {
			        $Encoding = mb_detect_encoding($Str, 'UTF-8, ISO-8859-1');
		        }
		        if (empty($Encoding)) {
			        $Encoding = 'ISO-8859-1';
		        }
		        if ($Encoding == 'UTF-8') {
			        return $Str;
		        } else {
			       return @mb_convert_encoding($Str, 'UTF-8', $Encoding);
		        }
	        }
        }
	
        public static function is_utf8($Str) {
	        return preg_match('%^(?:
		    [\x09\x0A\x0D\x20-\x7E]			 // ASCII
		    | [\xC2-\xDF][\x80-\xBF]			// non-overlong 2-byte
		    | \xE0[\xA0-\xBF][\x80-\xBF]		// excluding overlongs
		    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} // straight 3-byte
		    | \xED[\x80-\x9F][\x80-\xBF]		// excluding surrogates
		    | \xF0[\x90-\xBF][\x80-\xBF]{2}	 // planes 1-3
		    | [\xF1-\xF3][\x80-\xBF]{3}		 // planes 4-15
		    | \xF4[\x80-\x8F][\x80-\xBF]{2}	 // plane 16
		    )*$%xs', $Str
	       );
        }

}

?>