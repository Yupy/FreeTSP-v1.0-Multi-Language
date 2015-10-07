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
**
** Credits To Nicky
**/

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');

db_connect();

$lang = array_merge(load_language('loginhelp'),
                    load_language('global'));

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $to         = $arr['email'];
    $from_email = substr(trim($_POST['from_email']), 0, 80);

    if (!validemail($from_email))
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['text_email_valid']}");
    }

    $subject = substr(trim($_POST['subject']), 0, 80);
    $subject = ($subject == "" ? "{$lang['text_no_subject']}" : "$subject");
    $message = trim($_POST['message']);

    if ($message == '')
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['text_no_msg_text']}");
    }

    $message = "{$lang['text_msg_sent']} $site_name $site_url.\n" .
                "---------------------------------------------------------------------\n\n" . $message .
                "\n\n" . "---------------------------------------------------------------------\n\n" .
                "$site_name $site_url\n";

    $success = mail($site_email, $subject, $message, "From: $from_email");

    if ($success)
    {
        error_message_center("success",
                             "{$lang['gbl_success']}",
                             "{$lang['text_queued']}");
    }
    else
    {
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['text_try_later']}");
    }
}

site_header("{$lang['title_send_to']}");
?>
<table class='main' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td class='embedded' style='padding-left: 10px'>
            <h1>
                <img src='<?php echo $image_dir?>email.png' width='48' height='48' alt='<?php echo $lang['img_alt_email']?>' title='<?php echo $lang['img_alt_email']?>' style='vertical-align : middle;' />
                &nbsp;<?php echo $lang['text_send_to'], $site_name?>
            </h1>
        </td>
    </tr>
</table>
<form method='post' action='loginhelp.php'>
    <table border='1' cellspacing='0' cellpadding='5'>
        <tr>
            <td class='rowhead'><?php echo $lang['text_email']?></td>
            <td>
                <input type='text' name='from_email' size='80' />
            </td>
        </tr>
        <tr>
            <td class='rowhead'><?php echo $lang['text_subject']?></td>
            <td>
                <input type='text' name='subject' size='80' />
            </td>
        </tr>
        <tr>
            <td class='rowhead'><?php echo $lang['text_msg']?></td>
            <td>
                <textarea name='message' cols='80' rows='20'></textarea>
            </td>
        </tr>
        <tr>
            <td colspan='2' align='center'>
                <input type='submit' class='btn' value='<?php echo $lang['gbl_btn_submit']?>' />
            </td>
        </tr>
    </table>
</form>

<?php

site_footer();

?>