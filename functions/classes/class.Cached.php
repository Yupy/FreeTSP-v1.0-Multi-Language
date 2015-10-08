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
require_once(CLASS_DIR . 'class.Template.php');

class cached {
	const TTL_TIME = 21600;
	const BAD_TTL_TIME = 86400;
	const FORUM_STATS = 120;


    public static function bans($ip, &$reason = '') {
        global $Memcache, $db;
 
        $ip  = vars::$ip;
        $nip = ip2long($ip);

	    $key = 'banned::' . $ip;
		if (($banned = $Memcache->get_value($key)) === false) {
            $res = $db->query("SELECT comment FROM bans WHERE '" . $nip . "' >= first AND '" . $nip . "' <= last");
            if ($res->num_rows) {
                $comment = $res->fetch_row();
                $ban_reason = $comment[0];
                     
                $Memcache->add_value($key, $ban_reason, self::TTL_TIME);
                return true;
            }
                
            $res->free();
            $Memcache->add_value($key, 0, self::BAD_TTL_TIME);
            return false;
        }
        elseif (!$banned)
            return false;
        else {
            $reason = $banned;
            return true;
        }
    }

    public static function get_torrent_from_hash($info_hash) {
        global $Memcache, $db;

        $key = 'torrent::hash::' . md5($info_hash);

        $torrent = $Memcache->get_value($key);
        if ($torrent === false) {
            $res = $db->query('SELECT id, banned, seeders, leechers, times_completed, seeders + leechers AS numpeers, UNIX_TIMESTAMP(added) AS ts, freeleech FROM torrents WHERE info_hash = ' . sqlesc($info_hash)) or sqlerr(__FILE__, __LINE__);
            if ($res->num_rows) {
                $torrent = $res->fetch_assoc();
				
                $torrent['id'] = (int)$torrent['id'];
                $torrent['seeders'] = (int)$torrent['seeders'];
                $torrent['leechers'] = (int)$torrent['leechers'];
                $torrent['times_completed'] = (int)$torrent['times_completed'];
			    $torrent['numpeers'] = (int)$torrent['numpeers'];
                $Memcache->cache_value($key, $torrent, self::TTL_TIME);

                $seed_key = 'torrents::seeds::' . $torrent['id'];
                $leech_key = 'torrents::leechs::' . $torrent['id'];
                $comp_key = 'torrents::comps::' . $torrent['id'];
                $Memcache->add_value($seed_key, $torrent['seeders'], self::TTL_TIME);
                $Memcache->add_value($leech_key, $torrent['leechers'], self::TTL_TIME);
                $Memcache->add_value($comp_key, $torrent['times_completed'], self::TTL_TIME);
            } else {
                $Memcache->cache_value($key, 0, self::BAD_TTL_TIME);
                return false;
            }
        } elseif (!$torrent)
		    return false;
        else {
            $seed_key = 'torrents::seeds::' . $torrent['id'];
            $leech_key = 'torrents::leechs::' . $torrent['id'];
            $comp_key = 'torrents::comps::' . $torrent['id'];
            $torrent['seeders'] = $Memcache->get_value($seed_key);
            $torrent['leechers'] = $Memcache->get_value($leech_key);
            $torrent['times_completed'] = $Memcache->get_value($comp_key);
		    
            if ($torrent['seeders'] === false || $torrent['leechers'] === false || $torrent['times_completed'] === false) {
                $res = $db->query('SELECT seeders, leechers, times_completed FROM torrents WHERE id = ' . sqlesc($torrent['id'])) or sqlerr(__FILE__, __LINE__);
                if ($res->num_rows) {
                    $torrentq = $res->fetch_assoc();
				    
                    $torrent['seeders'] = (int)$torrentq['seeders'];
                    $torrent['leechers'] = (int)$torrentq['leechers'];
                    $torrent['times_completed'] = (int)$torrentq['times_completed'];
                    $Memcache->add_value($seed_key, $torrent['seeders'], self::TTL_TIME);
                    $Memcache->add_value($leech_key, $torrent['leechers'], self::TTL_TIME);
                    $Memcache->add_value($comp_key, $torrent['times_completed'], self::TTL_TIME);
                } else {
                    $Memcache->delete_value($key);
                    return false;
                }
            }
        }
        return $torrent;
    }
	
    public static function adjust_torrent_peers($id, $seeds = 0, $leechers = 0, $completed = 0) {
        global $Memcache;

        if (!is_int($id) || $id < 1)
			return false;
		
        if (!$seeds && !$leechers && !$completed)
			return false;
		
        $adjust = 0;
		
        $seed_key = 'torrents::seeds::' . $id;
        $leech_key = 'torrents::leechs::' . $id;
        $comp_key = 'torrents::comps::' . $id;
		
        if ($seeds > 0)
			$adjust+= (bool)$Memcache->increment($seed_key, $seeds);
        elseif ($seeds < 0)
		    $adjust+= (bool)$Memcache->decrement($seed_key, -$seeds);
		
        if ($leechers > 0)
			$adjust+= (bool)$Memcache->increment($leech_key, $leechers);
        elseif ($leechers < 0)
		    $adjust+= (bool)$Memcache->decrement($leech_key, -$leechers);
		
        if ($completed > 0)
			$adjust+= (bool)$Memcache->increment($comp_key, $completed);
		
        return (bool)$adjust;
    }

	public static function remove_torrent_peers($id) {
		global $Memcache;
		
		if (!is_int($id) || $id < 1)
			return false;
		
		$delete = 0;
		$seed_key = 'torrents::seeds::' . $id; $leech_key = 'torrents::leechs::' . $id; $comp_key = 'torrents::comps::' . $id;
		bt_memcache::connect();
		$delete += $Memcached->delete_value($seed_key, 5); $delete += $Memcache->delete_value($leech_key, 5); $delete += $Memcache->delete_value($comp_key, 5);
		return (bool)$delete;
	}

    public static function forum_stats() {
        global $forum_width, $image_dir, $site_url, $lang, $Memcache, $db, $tpl;

		raintpl::configure('base_url', null);
        raintpl::configure('tpl_dir', 'stylesheets/tpl/');
        raintpl::configure('cache_dir', 'cache/');
        $tpl = new RainTPL;

        $forum3 = '';

		$key = 'users::on::forum';
		if (($forum3 = $Memcache->get_value($key)) === false) {
            $dt = sqlesc(get_date_time(gmtime() - 180));

            $forum1 = $db->query("SELECT id, username, class, warned, donor
                                  FROM users
                                  WHERE forum_access >= " . $dt . "
                                  ORDER BY class DESC") or sqlerr(__FILE__, __LINE__);

            while ($forum2 = $forum1->fetch_assoc()) {
                $forum3[] = $forum2;
            }
            $Memcache->cache_value($key, $forum3, self::FORUM_STATS);
        }

        $forumusers = '';

        if (is_array($forum3)) {
            foreach ($forum3 AS $arr) {
                if ($forumusers) {
                    $forumusers .= ',\n';
                }
                $forumusers .= format_username($arr);
            }
        }

        if (!$forumusers) {
            $forumusers = $lang['table_no_active'];
        }

        $topic_post_res = $db->query("SELECT SUM(topiccount) AS topics, SUM(postcount) AS posts
                                      FROM forums");
        $topic_post_arr = $topic_post_res->fetch_assoc();

		#Lang...
		$lang_now_active = $lang['table_now_active'];
	    $tpl->assign('lang_active', $lang_now_active);

		$lang_member_wrote = $lang['table_member_wrote'];
	    $tpl->assign('lang_member_wrote', $lang_member_wrote);

		$lang_threads = $lang['table_threads'];
	    $tpl->assign('lang_threads', $lang_threads);

		$lang_posts_in = $lang['table_posts_in'];
	    $tpl->assign('lang_posts_in', $lang_posts_in);

		#Vars...
		$var_width = $forum_width;
	    $tpl->assign('forum_width', $var_width);

		$var_users = $forumusers;
	    $tpl->assign('forum_users', $var_users);

		$var_posts = number_format($topic_post_arr['posts']);
	    $tpl->assign('posts', $var_posts);

		$var_topics = number_format($topic_post_arr['topics']);
	    $tpl->assign('topics', $var_topics);

        #Let's draw it...
	    $forum_users = $tpl->draw('forum_users', $return_string = true );
        echo $forum_users;
    }

    public static function get_forum_access_levels($forumid) {
        global $site_url, $image_dir, $Memcache, $db;

		$key = 'forum::access::levels::' . $forumid;
		if (($arr = $Memcache->get_value($key)) === false) {
            $res = $db->query("SELECT minclassread, minclasswrite, minclasscreate
                               FROM forums
                               WHERE id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

            if ($res->num_rows != 1) {
                return false;
            }

            $arr = $res->fetch_assoc();
            $Memcache->cache_value($key, $arr, self::TTL_TIME);
        }

        return array('read'   => (int)$arr['minclassread'],
                     'write'  => (int)$arr['minclasswrite'],
                     'create' => (int)$arr['minclasscreate']);
    }

    public static function get_topic_forum($topicid) {
        global $image_dir, $posts_read_expiry, $site_url, $Memcache, $db;

		$key = 'get::topic::forum::' . $topicid;
		if (($arr = $Memcache->get_value($key)) === false) {
            $res = $db->query("SELECT forumid
                               FROM topics
                               WHERE id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

            if ($res->num_rows != 1) {
                return false;
            }

            $arr = $res->fetch_assoc();
            $Memcache->cache_value($key, $arr, self::TTL_TIME);
        }
        return (int)$arr['forumid'];
    }

    public static function get_forum_last_post($forumid) {
        global $Memcache, $db;

		$key = 'get::forum::last::post::' . $forumid;
		if (($postid = $Memcache->get_value($key)) === false) {
            $res = $db->query("SELECT MAX(lastpost) AS lastpost
                               FROM topics
                               WHERE forumid = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

            $arr = $res->fetch_assoc();

			$postid = (int)$arr['lastpost'];
            $Memcache->cache_value($key, $postid, self::TTL_TIME);
        }
        return (is_valid_id($postid) ? $postid : 0);
    }

    public static function genrelist() {
	    global $Memcache, $db;

		$key = 'genre::list';
		if (($ret = $Memcache->get_value($key)) === false) {
            $ret = array();
            $res = $db->query("SELECT id, name, image
                               FROM categories
                               ORDER BY name");

            while ($row = $res->fetch_array(MYSQLI_BOTH)) {
                $ret[] = $row;
            }
            $Memcache->cache_value($key, $ret, self::TTL_TIME);
        }
        return $ret;
    }

} #End Class...
?>
