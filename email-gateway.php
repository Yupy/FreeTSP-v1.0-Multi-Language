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
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');

db_connect();
logged_in();

$lang = array_merge(load_language('email_gateway'),
                    load_language('global'));

$id = intval(0 + $_GET['id']);

if (!$id)
{
    error_message("error",
                  "{$lang['gbl_error']}",
                  "{$lang['err_inv_id']}");
}

$res = $db->query("SELECT username, class, email
                    FROM users
                    WHERE id = $id");

$arr = $res->fetch_assoc() or error_message("error",
                                                "{$lang['gbl_error']}",
                                                "{$lang['err_inv_user']}");

$username = security::html_safe($arr['username']);

if ($arr['class'] < UC_MODERATOR)
{
    error_message("error",
                  "{$lang['gbl_error']}",
                  "{$lang['err_staff_only']}");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $to = unesc($arr['email']);

    $from = substr(trim($_POST['from']), 0, 80);

    if ($from == "")
    {
        $from = "{$lang['text_anon']}";
    }

    $from_email = substr(trim($_POST['from_email']), 0, 80);

    if ($from_email == '')
    {
        $from_email = "{$site_email}";
    }

    if (!strpos($from_email, "@"))
    {
        error_message("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_email']}");
    }

    $from = "$from <$from_email>";

    $subject = substr(trim($_POST['subject']), 0, 80);

    if ($subject == '')
    {
        $subject = "{$lang['text_subject']}";
    }

    $subject = "{$lang['text_forward']} $subject";

    $message = trim($_POST['message']);

    if ($message == '')
    {
        error_message("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_no_msg']}");
    }

    $message = "{$langv['text_sent_from']}" . vars::$realip . "{$lang['text_at']}".gmdate("Y-m-d H:i:s") . "{$lang['text_gmt']}\n" . "{$lang['text_message']}\n" . "---------------------------------------------------------------------\n\n" . $message . "\n\n" . "---------------------------------------------------------------------\n$site_name {$lang['title_email']}\n";

    $success = mail($to, $subject, $message, "{$lang['text_from']} $from", "-f$site_email");

    if ($success)
    {
        error_message("success",
                      "{$lang['gbl_success']}",
                      "{$lang['text_email_queued']}");
    }
    else
    {
        error_message("error",
                      "{$lang['gbl_error']}",
                      "{$lang['text_email_failed']}");
    }
}

site_header("{$lang['title_email']}");
?>
<table class='main' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td class='embedded'>
            <img src='<?php echo $image_dir?>/email.gif' width='32' height='32' border='0' alt='<?php echo $lang['img_alt_send']?>' title='<?php echo $lang['img_alt_send']?>' />
        </td>
        <td class='embedded' style='padding-left: 10px'>
            <span style='font-size : small; font-weight : bold;'><?php echo $lang['table_send_to'], $username ?></span>
        </td>
    </tr>
</table><br />

<form method='post' action='email-gateway.php?id=<?php echo $id?>'>
    <table border='1' cellspacing='0' cellpadding='5'>
        <tr>
            <td class='rowhead'>
                <label for='name'><?php echo $lang['table_name']?></label>
            </td>
            <td>
                <input type='text' name='from' id='name' size='80' />
            </td>
        </tr>
        <tr>
            <td class='rowhead'>
                <label for='email'><?php echo $lang['table_email']?></label>
            </td>
            <td>
                <input type='text' name='from_email' id='email' size='80' />
            </td>
        </tr>
        <tr>
            <td class='rowhead'>
                <label for='subject'><?php echo $lang['table_subject']?></label>
            </td>
            <td>
                <input type='text' name='subject' id='subject' size='80' />
            </td>
        </tr>
        <tr>
            <td class='rowhead'>
                <label for='textarea'><?php echo $lang['table_message']?></label>
            </td>
            <td>
                <textarea name='message' id='textarea' cols='80' rows='20'></textarea>
            </td>
        </tr>
        <tr>
            <td colspan='2' align='center'>
                <input type='submit' class='btn' id='send' value='<?php echo $lang['gbl_btn_submit']?>' />
            </td>
        </tr>
    </table>
</form>

<p>
    <span style='font-size : small; font-weight : bold;'><?php echo $lang['footer_note']?></span>
</p>

<?php

site_footer();

?>