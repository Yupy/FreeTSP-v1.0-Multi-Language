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

// Based on Hanne's Shoutbox With added staff functions-putyn shout and reply added Spook

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_bbcode.php');

db_connect(false);
logged_in();

//-- Start link To Customise Shoutbox Per Individual Theme --//
if (user::$current)
{
    $ss_a = $db->query("SELECT uri
                        FROM stylesheets
                        WHERE id = " . user::$current['stylesheet']);
	$ss_a = $ss_a->fetch_array(MYSQLI_BOTH);

    if ($ss_a)
    {
        $ss_uri = $ss_a['uri'];
    }
}

if (!$ss_uri)
{
    ($r = $db->query("SELECT uri
                     FROM stylesheets
                     WHERE id = 1")) or die($db->error);

    ($a = $r->fetch_array(MYSQLI_BOTH)) or die($db->error);

    $ss_uri = $a['uri'];
}

require_once(STYLES_DIR . $ss_uri . DIRECTORY_SEPARATOR . 'theme_function.php');

//-- Finish link To Customise Shoutbox Per Individual Theme --//

function autoshout($msg = '')
{
	global $db;

    $message = $msg;

    $db->query("INSERT INTO shoutbox (date, text)
               VALUES (" . implode(", ", array_map("sqlesc", array(time(), $message))) . ")") or sqlerr(__FILE__, __LINE__);
}

/*
    Get current datetime
    $dt = gmtime() - 60;
    $dt = sqlesc(get_date_time($dt));
*/

unset ($insert);

$insert = false;
$query  = "";

//-- Delete Shout --//
if (isset($_GET['del']) && get_user_class() >= UC_MODERATOR && is_valid_id($_GET['del']))
{
    $db->query("DELETE
               FROM shoutbox
               WHERE id = " . sqlesc($_GET['del']));
}

//-- Empty Shout - Coder/Owner --//
if (isset($_GET['delall']) && get_user_class() >= UC_SYSOP)
{
    $query = "TRUNCATE
              TABLE shoutbox";
}

$db->query($query);
unset($query);

//-- Edit Shout --//
if (isset($_GET['edit']) && get_user_class() >= UC_MODERATOR && is_valid_id($_GET['edit']))
{
    $sql = $db->query('SELECT id, text
                      FROM shoutbox
                      WHERE id = ' . sqlesc($_GET['edit']));

    $res = $sql->fetch_assoc();
    unset($sql);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <meta http-equiv='Pragma' content='no-cache' />
        <meta http-equiv='expires' content='-1' />

        <?php sb_style(); ?>

    </head>

<body>

<?php

    $lang = (load_language('shoutbox'));

    echo "<form method='post' action='shoutbox.php'>
          <input type='hidden' name='id' value='" . (int) $res['id'] . "' />
          <textarea name='text' id='staff_specialbox' rows='3'>'" . security::html_safe($res['text']) . "'</textarea>
          <input type='submit' class='btn' name='save' value='{$lang['btn_save']}' />
          </form></body></html>";
    die();
}

//-- Power Users+ Can Edit Anyones Single Shouts - pdq --//
if (isset($_GET['edit']) && ($_GET['user'] == user::$current['id']) && (user::$current['class'] >= UC_POWER_USER && user::$current['class'] <= UC_MODERATOR) && is_valid_id($_GET['edit']))
{
    $sql = $db->query("SELECT id, text, userid
                      FROM shoutbox
                      WHERE userid = " . sqlesc($_GET['user']) . "
                      AND id = " . sqlesc($_GET['edit']));

    $res = $sql->fetch_array(MYSQLI_BOTH);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <meta http-equiv='Pragma' content='no-cache' />
        <meta http-equiv='expires' content='-1' />

        <?php sb_style(); ?>

    </head>

<body>

<?php

    $lang = (load_language('shoutbox'));

    echo "<form method='post' action='shoutbox.php'>
            <input type='hidden' name='id' value='" . (int) $res['id'] . "' />
            <input type='hidden' name='user' value='" . (int) $res['userid'] . "' />
            <textarea name='text' id='member_specialbox' rows='3'>" . security::html_safe($res['text']) . "</textarea>
            <input type='submit' name='save' value='{$lang['btn_save']}' />
            </form></body></html>";
    die;
}

//-- Staff Shout Edit --//
if (isset($_POST['text']) && user::$current['class'] >= UC_MODERATOR && is_valid_id($_POST['id']))
{
    $text        = trim($_POST['text']);
    $text_parsed = format_comment($text);

    $db->query("UPDATE shoutbox
               SET text = " . sqlesc($text) . ", text_parsed = " . sqlesc($text_parsed) . "
               WHERE id = " . sqlesc($_POST['id']));

    unset ($text, $text_parsed);
}
// Power User+ Shout Edit --//
//-- Correction By Fireknight Added In theme_function.php --//
if (isset($_POST['text']) && (isset($_POST['user']) == user::$current['id']) && (user::$current['class'] >= UC_POWER_USER && user::$current['class'] < UC_MODERATOR) && is_valid_id($_POST['id']))
{
    $text        = trim($_POST['text']);
    $text_parsed = format_comment($text);

    $db->query("UPDATE shoutbox
               SET text = " . sqlesc($text) . ", text_parsed = " . sqlesc($text_parsed) . "
               WHERE userid = " . sqlesc($_POST['user']) . "
               AND id = " . sqlesc($_POST['id']));

    unset ($text, $text_parsed);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <meta http-equiv='Pragma' content='no-cache' />
        <meta http-equiv='expires' content='0' />
    <title><?php echo $lang['title_shoutbox']?></title>
    <meta http-equiv='REFRESH' content='60; URL=shoutbox.php' />

    <?php sb_style(); ?>

</head>

<?php

$lang = (load_language('shoutbox'));

//-- Start Defining Background Color And Font Color To Match Theme Color --//
echo "<body>";
//-- Finish Defining Background Color And Font Color To Match Theme Color --//

if (user::$current['shoutboxpos'] == 'no')
{
    echo("<div class='error' align='center'><br /><span class='shoutbox_disabled'>{$lang['err_denied']}</span> (<span class='shout_disabled'>{$lang['err_check_pm']}</span>)<br /><br /></div></body></html>");
    exit;
}

if (isset($_GET['sent']) && ($_GET['sent'] == 'yes'))
{
    $limit       = 1;
    $userid      = user::$current['id'];
    $date        = sqlesc(vars::$timestamp);
    $text        = (trim($_GET['shbox_text']));
    $text_parsed = format_comment($text);
/*
    // quiz bot
    if (stristr($text, "/quiz") && user::$current['class'] >= UC_MODERATOR)

    {
        $userid = 13767;
    }
    $text = str_replace(array("/quiz",
                              "/QUIZ [color=red]"), "", $text);
    $text_parsed = format_comment($text);

    //  radio bot
    if (stristr($text, "/scast") && user::$current['class'] >= UC_MODERATOR)

    {
        $userid = 13626;
    }
    $text = str_replace(array("/scast",
                              "/SCAST"), "", $text);
    $text_parsed = format_comment($text);

    //Notice By Subzero
    if (stristr($text, "/notice") && user::$current['class'] >= UC_MODERATOR)
    {
        $userid = 2;
    }
    $text = str_replace(array("/NOTICE",
                              "/notice"), "", $text);
    $text_parsed = format_comment($text);

    if (stristr($text, "/system") && user::$current['class'] >= UC_MODERATOR)
    {
        $userid = 2;
        $text   = str_replace(array("/SYSTEM",
                                    "/system"), "", $text);
        //$text_parsed = format_comment($text);
    }
*/
    //-- Shoutbox Command System By Putyn & pdq --//
    $commands = array("\/EMPTY",
                      "\/GAG",
                      "\/UNGAG",
                      "\/WARN",
                      "\/UNWARN",
                      "\/DISABLE",
                      "\/ENABLE",
                      "\/"); //-- This / Was Replaced With \/ To Work With The Regex --//

    $pattern  = "/(" . implode("|", $commands) . "\w+)\s([a-zA-Z0-9_:\s(?i)]+)/";

    //-- $private_pattern = "/(^\/private)\s([a-zA-Z0-9]+)\s([\w\W\s]+)/";  --//

    if (preg_match($pattern, $text, $vars) && user::$current['class'] >= UC_MODERATOR)
    {
        $command = $vars[1];
        $user    = $vars[2];

        $c = $db->query("SELECT id, class, modcomment
                        FROM users
                        WHERE username = " . sqlesc($user)) or sqlerr();

        $a = $c->fetch_row();

        if ($c->num_rows == 1 && user::$current['class'] > $a[1])
        {
            switch ($command)
            {
                case "/EMPTY" :
                    $what = "{$lang['text_del_all']}";
                    $msg  = " - [b]" . $user . "'s[/b] - {$lang['text_shouts_del']}";

                    $query = "DELETE
                              FROM shoutbox
                              WHERE userid = " . (int)$a[0];
                    break;

                case "/GAG"    :
                    $what       = 'Gagged';
                    $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_gag']}" . user::$current['username'] . "\n\n{$a['modcomment']}";
                    $msg        = " - [b]" . $user . "[/b] - {$lang['text_gagged']}" . user::$current['username'];

                    $query = "UPDATE users
                              SET shoutboxpos = 'no', modcomment = concat(" . sqlesc($modcomment) . ", modcomment)
                              WHERE id = " . (int)$a[0];
                    break;

                case "/UNGAG" :
                    $what       = 'Un-Gagged';
                    $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_ungag']}" . user::$current['username'] . "\n\n" . $a[2];
                    $msg        = " - [b]" . $user . "[/b] - {$lang['text_ungag']}" . user::$current['username'];

                    $query = "UPDATE users
                              SET shoutboxpos = 'yes', modcomment = concat(" . sqlesc($modcomment) . ", modcomment)
                              WHERE id = " . (int)$a[0];

                    break;

                case "/WARN" :
                    $what       = 'Warned';
                    $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_warn']}" . user::$current['username'] . "\n\n" . $a[2];
                    $msg        = " - [b]" . $user . "[/b] - {$lang['text_warn']}" . user::$current['username'];

                    $query = "UPDATE users
                                SET warned = 'yes', modcomment = concat(" . sqlesc($modcomment) . ", modcomment)
                                WHERE id = " . (int)$a[0];
                    break;

                case "/UNWARN" :
                    $what       = 'Un-Warned';
                    $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_unwarn']}" . user::$current['username'] . "\n\n" . $a[2];
                    $msg        = " - [b]" . $user . "[/b] - {$lang['text_unwarn']}" . user::$current['username'];

                    $query = "UPDATE users
                              SET warned = 'no', modcomment = concat(" . sqlesc($modcomment) . ", modcomment)
                              WHERE id = " . (int)$a[0];
                    break;

                case "/DISABLE"    :
                    $what       = 'Disabled';
                    $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_disable']}" . user::$current['username'] . "\n\n" . $a[2];
                    $msg        = " - [b]" . $user . "[/b] - {$lang['text_disable']}" . user::$current['username'];

                    $query = "UPDATE users
                              SET enabled = 'no', modcomment = concat(" . sqlesc($modcomment) . ", modcomment)
                              WHERE id = " . (int)$a[0];
                    break;

                case "/ENABLE" :
                    $what       = 'Enabled';
                    $modcomment = gmdate("Y-m-d") . " - {$lang['text_modcom_enable']}" . user::$current['username'] . "\n\n" . $a[2];
                    $msg        = " - [b]" . $user . "[/b] - {$lang['text_enable']}" . user::$current['username'];

                    $query = "UPDATE users
                              SET enabled = 'yes', modcomment = concat(" . sqlesc($modcomment) . ", modcomment)
                              WHERE id = " . (int)$a[0];
                    break;
            }
            if ($db->query($query))
            {
                autoshout($msg);
            }

            print "<script type=\"text/javascript\">parent.document.forms[0].shbox_text.value='';</script>";

            write_log("<strong>{$lang['log_shoutbox']}</strong> " . $user . "{$lang['log_has_been']}" . $what . "{$lang['log_by']}" . user::$current['username']);

            unset ($text, $text_parsed, $query, $date, $modcomment, $what, $msg, $commands);
        }
    }
    else
    {
        $a = $db->query("SELECT userid, date
                         FROM shoutbox
                         ORDER by id DESC
                         LIMIT 1 ") or print ("{$lang['text_first_shout']}");
		$a = $a->fetch_row();

        if (empty($text) || strlen($text) == 1)
        {
            print ("<span class='shoutbox_empty'>{$lang['text_shout_empty']}</span>");
        }

        else
        {
            $db->query("INSERT INTO shoutbox (id, userid, date, text)
                       VALUES ('id'," . sqlesc($userid) . ", $date, " . sqlesc($text) . ")") or sqlerr(__FILE__, __LINE__);

            print "<script type=\"text/javascript\">parent.document.forms[0].shbox_text.value='';</script>";
        }
    }
}

sb_images();

?>
<script type='text/javascript' src='js/shout.js'></script>
</body>
</html>