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

//-- Start Execution Time --//
$qtme['start'] = microtime(true);
//-- End --//

//-- Strip Slashes By System --//
function cleanquotes(&$in) {
    if (is_array($in)) {
        return array_walk($in, 'cleanquotes');
    }
    return $in = stripslashes($in);
}

if (get_magic_quotes_gpc()) {
    array_walk($_GET, 'cleanquotes');
    array_walk($_POST, 'cleanquotes');
    array_walk($_COOKIE, 'cleanquotes');
    array_walk($_REQUEST, 'cleanquotes');
}

function local_user() {
    return $_SERVER['SERVER_ADDR'] == vars::$realip;
}

function illegal_access($page, $class) {
    global $lang, $db;

    $page =  security::esc_url($_SERVER['PHP_SELF']);

    if (get_user_class() < $class) {
        $added    = sqlesc(get_date_time());
        $subject  = sqlesc("{$lang['gbl_msg_sub_illegal_access']}");
        $username = security::html_safe(user::$current['username']);
        $userid   = user::$current['id'];
        $msg      = sqlesc("{$lang['gbl_msg_illegal_access1']}\n\n{$lang['gbl_msg_illegal_access2']}\n\n{$lang['gbl_msg_illegal_access3']}");

        $db->query("INSERT INTO messages (sender, receiver, added, subject, msg)
                    VALUES (0, $userid, $added, $subject, $msg)") or sqlerr(__FILE__, __LINE__);

        write_stafflog ("<strong><a href='userdetails.php?id=$userid'>$username.</a></strong> -- {$lang['gbl_stafflog_illegal_access']}$page");

        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "<strong>$username</strong>{$lang['gbl_illegal_access']}");
    }
}

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'function_config.php');
require_once(FUNC_DIR . 'function_cleanup.php');
require_once(FUNC_DIR . 'defines.php');
require_once(CLASS_DIR . 'class.Options.php');
require_once(CLASS_DIR . 'class.Misc.php');
require_once(CLASS_DIR . 'class.Memcache.php');
require_once(CLASS_DIR . 'class.Vars.php');
require_once(CLASS_DIR . 'class.IP.php');
require_once(CLASS_DIR . 'class.String.php');
require_once(CLASS_DIR . 'class.UTF8.php');
require_once(CLASS_DIR . 'class.Security.php');
require_once(CLASS_DIR . 'class.User.php');
require_once(CLASS_DIR . 'class.Cached.php');
require_once(CLASS_DIR . 'class.Cookie.php');

$Memcache = new Cache();


//-- Do Not Modify -- Versioning System --//
//-- This Will Help Identify Code For Support Issues At freetsp.info --//
function copyright() {
    global $curversion, $lang;

    echo("{$lang['gbl_text_powered_by']}by <a href='http://www.freetsp.info'>" . FTSP . "{$lang['gbl_text_version']}$curversion</a> &copy; <a href='http://www.freetsp.info'>" . FTSP . "</a> " . (date("Y") > 2010 ? "2010-" : "") . date("Y"));
}

function db_connect($autoclean = false) {
    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db, $lang, $db;
	
    /*
     * Connect to Database.
    */
    if (!@$db = new mysqli($mysql_host, $mysql_user, $mysql_pass)) {
        switch ($db->errno) {
            case 1040:
            case 2002:

            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                die("<html><head><meta http-equiv='refresh' content='5 {$_SERVER['REQUEST_URI']}'></head><body><table width='100%' height='100%' border='0'><tr><td><h3 align='center'>{$lang['gbl_text_serverload']}</h3></td></tr></table></body></html>");
            } else {
                die($lang['gbl_text_high_users']);
            }
            default:

            die("[" . $db->errno . "] db_connect: mysqli_connect: " . $db->error);
        }
    }
    $db->select_db($mysql_db)
        or die('db_connect: mysqli_select_db: ' + $db->error);

    user::login();

    if ($autoclean) {
        register_shutdown_function('autoclean');
    }
}

function autoclean() {
    global $autoclean_interval, $db;

    $now       = vars::$timestamp;
    $docleanup = 0;

    $res = $db->query("SELECT value_u
                      FROM avps
                      WHERE arg = 'lastcleantime'");

    $row = $res->fetch_array(MYSQLI_BOTH);

    if (!$row) {
        $db->query("INSERT INTO avps (arg, value_u)
                   VALUES ('lastcleantime', " . $now . ")");
        return;
    }

    $ts = $row[0];

    if ($ts + $autoclean_interval > $now) {
        return;
    }

    $db->query("UPDATE avps
                SET value_u = " . $now . "
                WHERE arg = 'lastcleantime'
                AND value_u = " . $ts);

    if (!$db->affected_rows) {
        return;
    }

    docleanup();
}

function unesc($x) {
    if (get_magic_quotes_gpc()) {
        return stripslashes($x);
    }
    return $x;
}

function mksizeint($bytes) {
    $bytes = max(0, $bytes);

    if ($bytes < 1000) {
        return floor($bytes) . " B";
    }
    elseif ($bytes < 1000 * 1024) {
        return floor($bytes / 1024) . " kB";
    }
    elseif ($bytes < 1000 * 1048576) {
        return floor($bytes / 1048576) . " MB";
    }
    elseif ($bytes < 1000 * 1073741824) {
        return floor($bytes / 1073741824) . " GB";
    } else {
        return floor($bytes / 1099511627776) . " TB";
    }
}

function deadtime() {
    global $announce_interval;

    return vars::$timestamp - floor($announce_interval * 1.3);
}

function mkprettytime($s) {
    if ($s < 0) {
        $s = 0;
    }

    $t = array();

    foreach (array("60:sec",
                   "60:min",
                   "24:hour",
                   "0:day")
            AS
            $x)
    {
        $y = explode(":", $x);

        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        } else {
            $v = $s;
        }

        $t[$y[1]] = $v;
    }

    if ($t['day']) {
        return $t['day'] . "d " . sprintf("%02d:%02d:%02d", $t['hour'], $t['min'], $t['sec']);
    }

    if ($t['hour']) {
        return sprintf("%d:%02d:%02d", $t['hour'], $t['min'], $t['sec']);
    }

    return sprintf("%d:%02d", $t['min'], $t['sec']);
}

function mkglobal($vars) {
    if (!is_array($vars)) {
        $vars = explode(":", $vars);
    }

    foreach ($vars
             AS
             $v)
    {
        if (isset($_GET[$v])) {
            $GLOBALS[$v] = unesc($_GET[$v]);
        }
        elseif (isset($_POST[$v])) {
            $GLOBALS[$v] = unesc($_POST[$v]);
        } else {
            return 0;
        }
    }

    return 1;
}

function validfilename($name) {
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function sqlesc($x) {
	global $db;
	
    return "'" . $db->real_escape_string($x) . "'";
}

function sqlwildcardesc($x)
{
	global $db;
	
    return str_replace(array("%",
                             "_"), array("\\%",
                                         "\\_"), $db->real_escape_string($x));
}

function urlparse($m) {
    $t = $m[0];

    if (preg_match(',^\w+://,', $t)) {
        return "<a href='$t'>$t</a>";
    }

    return "<a href='http://$t'>$t</a>";
}

function parsedescr($d, $html) {
    if (!$html)
    {
        $d = security::html_safe($d);
        $d = str_replace("\n", "\n<br />", $d);
    }
    return $d;
}

function site_header ($title = "", $msgalert = true) {
    global $site_online, $site_name, $image_dir, $FREETSP, $db;

    //$lang = array_merge(load_language('func_main'));
    $lang = load_language('func_main');

    if (!$site_online) {
        die("{$lang['err_site_down']}<br />");
    }

    if ($title == "") {
        $title = $site_name . (isset($_GET['ftsp']) ? " (" . FTSP . " $curversion)" : '');
    } else {
        $title = $site_name . (isset($_GET['ftsp']) ? " (" . FTSP . " $curversion)" : '') . " :: " . security::html_safe($title);
    }

    if (user::$current) {
        $ss_a = $db->query("SELECT uri
                            FROM stylesheets
                            WHERE id = " . user::$current['stylesheet'] . "
                            AND active = 'yes'");
		$ss_a = @$ss_a->fetch_array(MYSQLI_BOTH);

        if ($ss_a) {
            $ss_uri = $ss_a['uri'];
        }
    }

    if (!$ss_uri) {
        ($r = $db->query("SELECT uri
                          FROM stylesheets
                          WHERE id = 1")) or die($db->error);

        ($a = $r->fetch_array(MYSQLI_BOTH)) or die($db->error);

        $ss_uri = $a['uri'];
    }

    if ($msgalert && user::$current) {
        $res = $db->query("SELECT COUNT(id)
                           FROM messages
                           WHERE receiver = " . user::$current['id'] . " && unread = 'yes'") or die("{$lang['err_oops']}");
        $arr = $res->fetch_row();
        $unread = (int)$arr[0];
    }
	
    if (user::$current) {
        $FREETSP['language'] = isset(user::$current['language']) ? "" . user::$current['language'] . "" : $FREETSP['language'];
    }

    require_once(STYLES_DIR . $ss_uri . DIRECTORY_SEPARATOR . 'theme_function.php');
    require_once(STYLES_DIR . $ss_uri . DIRECTORY_SEPARATOR . 'site_header.php');

    global $lang;

    //-- Start Temp Demote By Retro 2 of 3 --//
    if (user::$current['override_class'] != 255 && user::$current) { //-- Second Condition Needed So That This Box Is Not Displayed For Non Members/logged Out Members --//
        display_message_center("warn",
                               "{$lang['gbl_warning']}",
                               "{$lang['gbl_text_run_low']}<a href='$site_url/restoreclass.php'><strong>{$lang['gbl_text_here']}</strong></a>{$lang['gbl_text_restore']}");
    }
    //-- Finish Temp Demote By Retro 2 of 3 --//

    if (isset($unread) && !empty($unread)) {
        /*print("<table border='0' cellspacing='0' cellpadding='10'><tr><td class='old_pm_bg'>\n");
        print("<a href='messages.php'><span class='old_pm_text'>{$lang['gbl_text_you_have']}$unread{$lang['gbl_text_msg']}" . ($unread > 1 ? "{$lang['gbl_text_msg_1']}" : "") . "!</span></a>");
        print("</td></tr></table>\n");*/

        //-- To Change the Color of class='emphasis' in css/notifications.css --//
        print("<div align='center'>");
        print("<div class='silver mail round small inset'>");
        print("<p><strong>{$lang['gbl_text_mail']}</strong>");
        print("<br /><a href='messages.php'>&nbsp;&nbsp;&nbsp;&nbsp;<span class='emphasis'>{$lang['gbl_text_you_have']}$unread{$lang['gbl_text_msg']}" . ($unread > 1 ? "{$lang['gbl_text_msg_1']}" : "") . "</span></a></p>");
        print("<div class='shadow-out'></div>");
        print("</div>");
        print("</div><br />");
    }

    //-- Start Announcement Message Display --//
    $res = $db->query("SELECT created
                       FROM announcement_main
                       WHERE 1 = 1");

    while ($arr = $res->fetch_assoc())

    if ($arr['created'] >= user::$current['added']) {
        $ann_subject = trim(user::$current['curr_ann_subject']);
        $ann_body    = trim(user::$current['curr_ann_body']);
        $ann_expires = trim(user::$current['curr_ann_expires']);

        if ((!empty($ann_subject)) AND (!empty($ann_body))) {
            print("<div align='center'>");
            print("<div class='silver box tip inset'>");
            print("<p><strong><span class='olive round inset'>{$lang['gbl_table_announcement']} :- $ann_subject</span></strong>");
            print("<br /><strong>".format_comment($ann_body)."</strong></p><hr />");
            print("<span class='emphasis'>{$lang['gbl_table_expire']}:-&nbsp;$ann_expires :-&nbsp;");
            print(" (" . mkprettytime(strtotime($ann_expires) - gmtime()) . "{$lang['gbl_table_to_go']})</span>");
            print("<br /><hr />");
            print("<div class='medium'><a class='btn' href='clear_announcement.php'>{$lang['gbl_table_click']}{$lang['gbl_table_here']}{$lang['gbl_table_clear']}</a></div>");
            print("<div class='shadow-out'></div>");
            print("</div>");
            print("</div><br />");

            site_footer();
            die();
        }
    }
//-- Finish Announcement Message Display --//
}

function site_footer() {
    global $FREETSP, $db;

    if (user::$current) {
        $ss_a = $db->query("SELECT uri
                            FROM stylesheets
                            WHERE id = " . user::$current['stylesheet']);
	    $ss_a = @$ss_a->fetch_array(MYSQLI_BOTH);

        if ($ss_a) {
            $ss_uri = $ss_a['uri'];
        }
    }

    if (!$ss_uri) {
        ($r = $db->query("SELECT uri
                          FROM stylesheets
                          WHERE id = 1")) or die($db->error);

        ($a = $r->fetch_array(MYSQLI_BOTH)) or die($db->error);

        $ss_uri = $a['uri'];
    }

    require_once(STYLES_DIR . $ss_uri . DIRECTORY_SEPARATOR . 'theme_function.php');
    require_once(STYLES_DIR . $ss_uri . DIRECTORY_SEPARATOR . 'site_footer.php');
}

function mksecret($len = 20) {
    return _string::random($len);
}


function httperr($code = 404) {
    global $lang;

    header("{$lang['gbl_404']}");

    print("<h1>{$lang['gbl_not_found']}</h1>\n");
    print("<p>{$lang['gbl_sorry']}</p>\n");

    exit();
}

function gmtime() {
    return strtotime(get_date_time());
}

function logincookie($id, $passhash, $updatedb = 1, $expires = 0x7fffffff) {
	global $db;
	
    cookie::set('uid', $id, $expires, '/');
    cookie::set('pass', $passhash, $expires, '/');

    if ($updatedb) {
        $db->query("UPDATE users
                    SET last_login = NOW()
                    WHERE id = " . $id);
    }
}

function logoutcookie() {
    cookie::set('uid', '', 0x7fffffff, '/');
    cookie::set('pass', '', 0x7fffffff, '/');
}

function logged_in() {
    global $site_url;

    if (!user::$current) {
        header("Location: $site_url/login.php?returnto=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}

function status_change($id) {
	global $db;
	
    $db->query("UPDATE announcement_process
                SET status = 0
                WHERE user_id = " . sqlesc($id) . "
                AND status = 1");
}

function hashit($var, $addtext = "") {
//-- I Would Suggest That You Change The Literal Text To Something That Only You Know (unique For Each Community Installing This Function). --//
    return md5("This Text " . $addtext . $var . $addtext . " is added to muddy the water...");
}

//-- Returns The Current Time In GMT In MySQL Compatible Format. --//
function get_date_time($timestamp = 0) {
    if ($timestamp) {
        return date("Y-m-d H:i:s", $timestamp);
    } else {
        return gmdate("Y-m-d H:i:s");
    }
}

function sqlerr($file = '', $line = '') {
	global $db;
	
    error_message("error",
                  "{$lang['gbl_sql_error']}",
                  "" . $db->error . ($file != '' && $line != '' ? "in $file, line $line" : "") . "");
}


function load_language($file = '') {
    global $FREETSP;
	
    if (!isset($GLOBALS['CURUSER']) OR empty($GLOBALS['CURUSER']['language'])) {
        if (!file_exists(LANG_DIR . "{$FREETSP['language']}/lang_{$file}.php")) {
            error_message_center("error",
                                 "Error",
                                 "Can\'t find language files");
        }

        require_once (LANG_DIR . "{$FREETSP['language']}/lang_{$file}.php");

        return $lang;
    }

    if (!file_exists(LANG_DIR . "" . user::$current['language'] . "/lang_{$file}.php")) {
        error_message_center("error",
                             "Error",
                             "Can\'t find language files");
    } else {
        require_once LANG_DIR . "" . user::$current['language'] . "/lang_{$file}.php";
    }
    return $lang;
}

//==  Coldfusion Tbdev
function htmlsafechars($txt = '') {
    $txt = preg_replace("/&(?!#[0-9]+;)(?:amp;)?/s", '&amp;', $txt);
    $txt = str_replace(array("<",
                             ">",
                             '"',
                             "'"
                            ) , array("&lt;",
                                      "&gt;",
                                      "&quot;",
                                      '&#039;'
                            ) ,
                    $txt);
    return $txt;
}

//-- SQL Query Count --//
$qtme['querytime'] = 0;

function sql_query($querytme) {
    global $queries, $qtme, $querytime, $query_stat;

    $qtme               = isset($qtme) && is_array($qtme) ? $qtme : array();
    $qtme['query_stat'] = isset($qtme['query_stat']) && is_array($qtme['query_stat']) ? $qtme['query_stat'] : array();

    $queries++;
    $query_start_time     = microtime(true); //-- Start Time --//
    $result               = mysql_query($querytme);
    $query_end_time       = microtime(true); //-- End Time --//
    $query_time           = ($query_end_time - $query_start_time);
    $querytime            = $querytime + $query_time;
    $qtme['querytime']    = (isset($qtme['querytime']) ? $qtme['querytime'] : 0) + $query_time;
    $query_time           = substr($query_time, 0, 8);
    $qtme['query_stat'][] = array('seconds' => $query_time,
                                  'query'   => $querytme);
    return $result;
}

if (file_exists(ROOT_DIR . "install/index.php")) {
    echo("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
        <html xmlns='http://www.w3.org/1999/xhtml'>
        <head>
            <title>Warning</title>
        </head>
        <body>
            <div style='font-size : 33px; color : white; background-color : red; text-align : center;'>Delete the Install Directory, then Refresh your Browser.</div>
        </body>
    </html>");
    exit();
}

?>