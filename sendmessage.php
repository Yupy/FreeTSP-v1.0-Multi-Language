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
require_once(FUNC_DIR . 'function_bbcode.php');
require_once(FUNC_DIR . 'function_page_verify.php');

db_connect(false);
logged_in();

$lang = array_merge(load_language('sendmessage'),
                    load_language('func_bbcode'),
                    load_language('global'));

parked();

$newpage = new page_verify();
$newpage->create('_sendmessage_');

$receiver = intval(0 + $_GET['receiver']);

if (!is_valid_id($receiver))
{
    //die;
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_inv_id']}");
}

$replyto = isset($_GET['replyto']) ? (int) $_GET['replyto'] : 0;

if ($replyto && !is_valid_id($replyto))
{
    //die;
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_inv_id']}");
}

$res = $db->query("SELECT *
                  FROM users
                  WHERE id = $receiver") or die($db->error);

$user = $res->fetch_assoc();

if (!$user)
{
    //die("No User with that ID.");
    error_message_center("error",
                         "{$lang['gbl_error']}",
                         "{$lang['err_no_user_id']}");
}

if ($replyto)
{
    $res = $db->query("SELECT *
                      FROM messages
                      WHERE id = $replyto") or sqlerr();

    $msga = $res->fetch_assoc();

    if ($msga['receiver'] != user::$current['id'])
    {
        //die;
        error_message_center("error",
                             "{$lang['gbl_error']}",
                             "{$lang['err_inv_id']}");
    }

    $res = $db->query("SELECT username
                      FROM users
                      WHERE id = " . (int)$msga['sender']) or sqlerr();

    $usra = $res->fetch_assoc();
    $body .= "\n\n\n--------&nbsp;{$usra['username']}&nbsp;{$lang['form_wrote']}&nbsp;--------\n{$msga['msg']}\n";
    $subject = "{$lang['form_re']}" . security::html_safe($msga['subject']);
}

site_header("{$lang['title_send_msg']}", false);
?>
<table class='main' border='0' width='100%' cellspacing='0' cellpadding='0'>
    <tr>
        <td class='embedded'>
            <div align='center'>
                <h1><?php echo $lang['text_msg_to']?><a href='userdetails.php?id=<?php echo $receiver?>'><?php echo security::html_safe($user['username'])?></a>
                </h1>

                <form name='compose' method='post' action='takemessage.php'>
                    <table border='1' cellspacing='0' cellpadding='5'>
                        <tr>
                            <td class='std' colspan='2'>
                                <span style='font-weight : bold;'>
                                    <label for='subject'><?php echo $lang['table_subject']?>&nbsp;&nbsp;</label>
                                </span>
                                <input type='text' name='subject' id='subject' size='76' value='<?php echo isset($subject) ? htmlentities($subject, ENT_QUOTES) : "" ?>' />
                            </td>
                        </tr>
                        <tr>
                            <td<?php echo $replyto ? " colspan='2' " : ""?>>
                                <?php echo("" . textbbcode("compose", "msg", "$body") . "");?>
                            </td>
                        </tr>

                        <?php if ($replyto)
                    { ?>

                        <tr>
                            <td class='std' align='center'>
                                <input type='checkbox' name='delete' value='yes' <?php echo user::$current['deletepms'] == 'yes' ? "checked='checked' " : ""?> /><?php echo $lang['form_del_msg']?>
                                <input type='hidden' name='origmsg' value='<?php echo $replyto?>' />
                            </td>
                        </tr>

                        <?php } ?>

                        <tr>
                            <td class='std' align='center'>
                                <input type='checkbox' name='save' value='yes' <?php echo user::$current['savepms'] == 'yes' ? "checked='checked' " : ""?> /><?php echo $lang['form_save_msg']?>
                            </td>
                        </tr>
                        <tr>
                            <td <?php echo $replyto ? " colspan='2' " : ""?> align='center'>
                                <input type='submit' class='btn' value='<?php echo $lang['btn_send']?>' />
                            </td>
                        </tr>
                    </table>
                    <input type='hidden' name='receiver' value='<?php echo $receiver?>' />
                </form>
            </div>
        </td>
    </tr>
</table>

<?php

site_footer();

?>