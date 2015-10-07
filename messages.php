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

//-- Connect To DB & Check Login --//
db_connect();
logged_in();

$lang = array_merge(load_language('messages'),
                    load_language('func_bbcode'),
                    load_language('global'));

//-- Check To See If Account Is Parked --//
parked();

//-- Define Constants --//
define('PM_DELETED', 0); //-- Message Was Deleted --//
define('PM_INBOX', 1); //-- Message Located In Inbox For Reciever --//
define('PM_SENTBOX', -1); //-- GET Value For Sent Box --//

//-- Determine Action --//
$action = isset($_GET['action']) ? (string) $_GET['action'] : false;

if (!$action)
{
    $action = isset($_POST['action']) ? (string) $_POST['action'] : 'viewmailbox';
}

//-- View Listing Of Messages In Mail Box --//
if ($action == "viewmailbox")
{
    //-- Get Mailbox Number --//
    $mailbox = isset($_GET['box']) ? (int) $_GET['box'] : PM_INBOX;

    //-- Get Mailbox Name --//
    if ($mailbox != PM_INBOX && $mailbox != PM_SENTBOX)
    {
        $res = $db->query('SELECT name
                          FROM pmboxes
                          WHERE userid = ' . sqlesc(user::$current['id']) . '
                          AND boxnumber = ' . sqlesc($mailbox) . '
                          LIMIT 1') or sqlerr(__FILE__, __LINE__);

        if ($res->num_rows == 0)
        {
            error_message("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_inv_mailbox']}");
        }

        $mailbox_name = $res->fetch_array(MYSQLI_BOTH);
        $mailbox_name = security::html_safe($mailbox_name[0]);
    }
    else
    {
        if ($mailbox == PM_INBOX)
        {
            $mailbox_name = "{$lang['text_inbox']}";
        }
        else
        {
            $mailbox_name = "{$lang['text_sentbox']}";
        }
    }
    $pmcount = $db->query("SELECT COUNT(id)
                           FROM messages
                           WHERE receiver = " . user::$current['id'] . "
                           AND location >= '1' || sender = " . user::$current['id'] . "
                           AND saved = 'yes' ") or sqlerr(__FILE__, __LINE__);
	$pmcount = $pmcount->fetch_row();

    $pm_perc     = $pmcount[0] ? ($pmcount[0] / 50 * 100) : 0;
    $perc_image  = ($pm_perc > 66) ? 'loadbarred.gif' : (($pm_perc > 33) ? 'loadbaryellow.gif' : 'loadbargreen.gif');
    $image_width = $pm_perc > 0 ? round($pm_perc * 2.5) : 1;

    if ($image_width > 250)
    {
        $image_width = 250;
    }

    //-- Start Page --//
    site_header($mailbox_name, false);
    ?>

<!-- Check All -->
<script type='text/javascript'>

    function checkAll(field)
    {
        if (field.CheckAll.checked == true)
        {
            for (i = 0; i < field.length; i++)
            {
                field[i].checked = true;
            }
        }
        else
        {
            for (i = 0; i < field.length; i++)
            {
                field[i].checked = false;
            }
        }
    }

</script>
<!-- Check All -->

<table align='center' width='30%' cellspacing='1'>
    <tbody>
    <tr>
        <td class='rowhead' align='left' colspan='3'><?php echo $lang['text_folder_pc'], $pm_perc, $lang['text_folder_full']?></td>
    </tr>
    <tr>
        <td class='rowhead' align='center' nowrap='nowrap' colspan='3'>
            <img src='<?php echo $image_dir?><?php echo $perc_image?>' width='<?php echo $image_width?>' height='10' align='middle' alt='<?php echo $lang['img_alt_full']?>' title='<?php echo $lang['img_alt_full']?>' />
        </td>
    </tr>
    <tr>
        <td class='rowhead' align='center' width='33%'>0%</td>
        <td class='rowhead' align='center' width='33%'>50%</td>
        <td class='rowhead' align='center' width='33%'>100%</td>
    </tr>
    </tbody>
</table>
<br />

<table border='0' width='100%' cellpadding='4' cellspacing='0'>
    <tr>
        <td class='rowhead' align='right'><?php echo insertJumpTo($mailbox);?></td>
    </tr>
</table>

<form name='mutliact' method='post' action='messages.php'>
    <input type='hidden' name='action' value='moveordel' />
    <table border='0' width='100%' cellpadding='4' cellspacing='0'>
        <tr>
            <td class='colhead' width='1%'><?php echo $lang['table_status']?></td>
            <td class='colhead'><?php echo $lang['table_subject']?></td>

            <?php

            if ($mailbox != PM_SENTBOX)
            {
                ?>

                <td class='colhead' width='35%'><?php echo $lang['table_sender']?></td>

                <?php
            }
            else
            {
                ?>

                <td class='colhead' width='35%'><?php echo $lang['table_receiver']?></td>

                <?php
            }
            ?>

            <td class='colhead' width='1%'><?php echo $lang['table_date']?></td>
            <td class='colhead' width='1%'>
                <input type='checkbox' name='CheckAll' id='CheckAll' class='checkbox' value='1' onclick='checkAll(mutliact)' title='Check All' />
            </td>
        </tr>

        <?php

        if ($mailbox != PM_SENTBOX)
        {
            $res = $db->query("SELECT *
                              FROM messages
                              WHERE receiver = " . sqlesc(user::$current['id']) . "
                              AND location = " . sqlesc($mailbox) . "
                              ORDER BY id DESC") or sqlerr(__FILE__, __LINE__);
        }
        else
        {
            $res = $db->query("SELECT *
                              FROM messages
                              WHERE sender = " . sqlesc(user::$current['id']) . "
                              AND saved = 'yes'
                              ORDER BY id DESC") or sqlerr(__FILE__, __LINE__);
        }

        if ($res->num_rows == 0)
        {
            echo("<tr><td class='colhead' align='center' colspan='5'>{$lang['table_no_msg']}</td></tr>\n");
        }
        else
        {
            while ($row = $res->fetch_assoc())
            {
                //-- Get Sender Username --//
                if ($row['sender'] != 0 && $row['sender'] != user::$current['id'])
                {
                    $res2 = $db->query("SELECT username
                                       FROM users
                                       WHERE id = " . sqlesc($row['sender']));

                    $username = $res2->fetch_array(MYSQLI_BOTH);
                    $username = "<a href='userdetails.php?id={$row['sender']}'>" . security::html_safe($username[0]) . "</a>";
                    $id       = intval(0 + $row['sender']);

                    $r = $db->query("SELECT id
                                    FROM friends
                                    WHERE userid = " . user::$current['id'] . "
                                    AND friendid = " . $id) or sqlerr(__FILE__, __LINE__);

                    $friend = $r->num_rows;

                    if ($friend)
                    {
                        $username .= "&nbsp;<a href='friends.php?action=delete&amp;type=friend&amp;targetid=$id'>[{$lang['text_del_friend']}]</a>";
                    }
                    else
                    {
                        $username .= "&nbsp;<a href='friends.php?action=add&amp;type=friend&amp;targetid=$id'>[{$lang['text_add_friend']}]</a>";
                    }
                }
                elseif ($row['sender'] == user::$current['id'])
                {
                    $res2 = $db->query("SELECT username
                                       FROM users
                                       WHERE id = " . sqlesc($row['receiver']));

                    $username = $res2->fetch_array(MYSQLI_BOTH);
                    $username = "<a href='userdetails.php?id={$row['receiver']}'>" . security::html_safe($username[0]) . "</a>";
                    $id       = intval(0 + $row['receiver']);
                }
                else
                {
                    $username = "{$lang['text_system']}";
                }
                $subject = security::html_safe($row['subject']);

                if (strlen($subject) <= 0)
                {
                    $subject = "{$lang['text_no_subject']}";
                }

                if ($row['unread'] == 'yes')
                {
                    echo("<tr>\n<td class='rowhead' align='center'><img src='{$image_dir}unreadpm.gif' width='19' height='15' border='0' alt='{$lang['img_alt_unread']}' title='{$lang['img_alt_unread']}' /></td>\n");
                }
                else
                {
                    echo("<tr>\n<td class='rowhead' align='center'><img src='{$image_dir}readpm.gif' width='19' height='15' border='0' alt='{$lang['img_alt_read']}' title='{$lang['img_alt_read']}' /></td>\n");
                }
                echo("<td class='rowhead' align='left'><a href='messages.php?action=viewmessage&amp;id={$row['id']}'>$subject</a></td>\n");
                echo("<td class='rowhead' align='left'>$username</td>\n");
                echo("<td class='rowhead' nowrap='nowrap'>{$row['added']}</td>\n");
                echo("<td class='rowhead'><input type='checkbox' name='messages[]' value='" . (int)$row['id'] . "' /></td>\n</tr>\n");
            }
        }
        ?>
        <tr class='colhead'>
            <td class='colhead' align='right' colspan='5'>
                <input type='submit' class='btn' name='move' value='<?php echo $lang['btn_move_to']?>' title='<?php echo $lang['btn_move_to']?>' />
                <select name='box'>
                    <option value='1'><?php echo $lang['form_inbox']?></option>

                    <?php

                    $res = $db->query("SELECT *
                                      FROM pmboxes
                                      WHERE userid = " . sqlesc(user::$current['id']) . "
                                      ORDER BY boxnumber") or sqlerr(__FILE__, __LINE__);

                    while ($row = $res->fetch_assoc())
                    {
                        echo("<option value='{$row['boxnumber']}'>" . security::html_safe($row['name']) . "</option>\n");
                    }

                    ?>

                </select>
                <input type='submit' class='btn' name='delete' value='<?php echo $lang['btn_delete']?>' title='<?php echo $lang['btn_delete']?>' />
            </td>
        </tr>
    </table>
</form>
<table border='0' width='100%' cellpadding='4' cellspacing='0'>
    <tr>
        <td colspan='5'>
            <div align='left'><img src='<?php echo $image_dir?>unreadpm.gif' width='19' height='15' border='0' alt='<?php echo $lang['img_alt_unread']?>' title='<?php echo $lang['img_alt_unread']?>' />&nbsp;<?php echo $lang['table_unread']?><br /> <img src='<?php echo $image_dir?>readpm.gif' width='19' height='15' border='0' alt='<?php echo $lang['img_alt_read']?>' title='<?php echo $lang['img_alt_read']?>' />&nbsp;<?php echo $lang['table_read']?>
            </div>
            <div align='right'>
                <a href='messages.php?action=editmailboxes'>
                    <input type='submit' class='btn' name='Edit' value='<?php echo $lang['btn_add_edit']?>' title='<?php echo $lang['btn_add_edit']?>' />
                </a>
                <a href='messages.php'>
                    <input type='submit' class='btn' name='return' value='<?php echo $lang['btn_return']?>' title='<?php echo $lang['btn_return']?>' />
                </a>
            </div>
        </td>
    </tr>
</table>

<?php

    site_footer();

}

if ($action == 'viewmessage')
{
    $pm_id = (int) $_GET['id'];

    if (!$pm_id)
    {
        error_message("warn",
                      "{$lang['gbl_warn']}",
                      "{$lang['err_perm_denied']}");
    }

    //-- Get The Message --//
    $res = $db->query("SELECT *
                      FROM messages
                      WHERE id = " . sqlesc($pm_id) . "
                      AND (receiver = " . sqlesc(user::$current['id']) . "
                      OR (sender = " . sqlesc(user::$current['id']) . "
                      AND saved = 'yes'))
                      LIMIT 1") or sqlerr(__FILE__, __LINE__);

    if (!$res)
    {
        error_message("warn",
                      "{$lang['gbl_warn']}",
                      "{$lang['err_perm_denied']}");
    }

    //-- Prepare For Displaying Message --//
    $message = $res->fetch_assoc() or header("Location: messages.php");

    if ($message['sender'] == user::$current['id'])
    {
        //-- Display To --//
        $res2 = $db->query("SELECT username
                           FROM users
                           WHERE id = " . sqlesc($message['receiver'])) or sqlerr(__FILE__, __LINE__);

        $sender = $res2->fetch_array(MYSQLI_BOTH);
        $sender = "<a href='userdetails.php?id={$message['receiver']}'>" . security::html_safe($sender[0]) . "</a>";
        $reply  = "";
        $from   = "{$lang['text_to']}";
    }
    else
    {
        $from = "{$lang['text_from']}";

        if ($message['sender'] == 0)
        {
            $sender = "{$lang['text_system']}";
            $reply  = "";
        }
        else
        {
            $res2 = $db->query("SELECT username
                               FROM users
                               WHERE id = " . sqlesc($message['sender'])) or sqlerr(__FILE__, __LINE__);

            $sender = $res2->fetch_array(MYSQLI_BOTH);
            $sender = "<a href='userdetails.php?id={$message['sender']}'>" . security::html_safe($sender[0]) . "</a>";
            //$reply  = " [ <a href='sendmessage.php?receiver={$message['sender']}&amp;replyto={$pm_id}'>Reply</a> ]";
        }
    }
    $body  = format_comment($message['msg']);
    $added = $message['added'];

    if (user::$current['class'] >= UC_MODERATOR && $message['sender'] == user::$current['id'])
    {
        $unread = ($message['unread'] == 'yes' ? "<span class='new_message'>({$lang['text_new']})</span>" : '');
    }
    else
    {
        $unread = '';
    }
    $subject = security::html_safe($message['subject']);

    if (strlen($subject) <= 0)
    {
        $subject = "{$lang['text_no_subject']}";
    }

    if ($message['unread'] === 'yes')
    {
        //-- Mark Message Unread --//
        $db->query("UPDATE messages
                   SET unread = 'no'
                   WHERE id = " . sqlesc($pm_id) . "
                   AND receiver = " . sqlesc(user::$current['id']) . "
                   LIMIT 1");
	    
		$Memcache->delete_value('statusbar::pm::count::' . user::$current['id']);
    }

    //-- Display Message --//
    site_header("{$lang['title_pm']}($subject)", false); ?>

<h1><?php echo $subject?></h1>

<table border='0' width='100%' cellpadding='4' cellspacing='0'>
    <tr>
        <td class='colhead' width='50%'><?php echo $from?></td>
        <td class='colhead' width='50%'><?php echo $lang['table_date']?></td>
    </tr>
    <tr>
        <td class='rowhead'><?php echo $sender?></td>
        <td class='rowhead'><?php echo $added?>&nbsp;&nbsp;<?php echo $unread?></td>
    </tr>
    <tr>
        <td class='rowhead' align='left' colspan='2'><?php echo $body?></td>
    </tr>
    <tr>
        <td class='rowhead' align='left'>
            <form method='post' action='messages.php'>
                <input type='hidden' name='action' value='moveordel' />
                <input type='hidden' name='id' value='<?php echo $pm_id?>' />
                <select name='box'>
                    <option value='1'><?php echo $lang['form_inbox']?></option>
                    <?php
                    $res = $db->query("SELECT *
                                      FROM pmboxes
                                      WHERE userid = " . sqlesc(user::$current['id']) . "
                                      ORDER BY boxnumber") or sqlerr(__FILE__, __LINE__);

                    while ($row = $res->fetch_assoc())
                    {
                        echo("<option value='{$row['boxnumber']}'>" . security::html_safe($row['name']) . "</option>\n");
                    }?>
                </select>
                <input type='submit' class='btn' name='move' value='<?php echo $lang['btn_move']?>' title='<?php echo $lang['btn_move']?>'  />
            </form>
        </td>
        <td class='rowhead' align='right'>
            <a href='messages.php'>
                <input type='submit' class='btn' name='return' value='<?php echo $lang['btn_return']?>' title='<?php echo $lang['btn_return']?>' />
            </a>
            <a href='messages.php?action=deletemessage&amp;id=<?php echo $pm_id?>'>
                <input type='submit' class='btn' name='delete' value='<?php echo $lang['btn_delete']?>' title='<?php echo $lang['btn_delete']?>' />
            </a>
            <a href='sendmessage.php?receiver=<?php echo $message['sender']?>&amp;replyto=<?php echo $pm_id?>'>
                <input type='submit' class='btn' name='delete' value='<?php echo $lang['btn_reply']?>' title='<?php echo $lang['btn_reply']?>' />
            </a>
            <a href='messages.php?action=forward&amp;id=<?php echo $pm_id?>'>
                <input type='submit' class='btn' name='forward' value='<?php echo $lang['btn_forward']?>' title='<?php echo $lang['btn_forward']?>' />
            </a>
        </td>
    </tr>
</table>

<?php

    site_footer();

}

if ($action == 'moveordel')
{
    $pm_id       = (int) $_POST['id'];
    $pm_box      = (int) $_POST['box'];
    $pm_messages = $_POST['messages'];

    if ($_POST['move'])
    {
        if ($pm_id)
        {
            //-- Move A Single Message --//
            @$db->query("UPDATE messages
                        SET location = " . sqlesc($pm_box) . "
                        WHERE id = " . sqlesc($pm_id) . "
                        AND receiver = " . user::$current['id'] . "
                        LIMIT 1");
        }
        else
        {
            //-- Move Multiple Messages --//
            @$db->query("UPDATE messages
                        SET location = " . sqlesc($pm_box) . "
                        WHERE id IN (" . implode(", ", array_map("sqlesc", $pm_messages)) . ")
                        AND receiver = " . user::$current['id']);
        }
        //-- Check If Messages Were Moved --//
        if (@$db->affected_rows == 0)
        {
            error_message("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_move_msg']}");
        }

        header("Location: messages.php?action=viewmailbox&box=" . $pm_box);
        exit();
    }
    elseif ($_POST['delete'])
    {
        if ($pm_id)
        {
            //-- Delete A Single Message --//
            $res = $db->query("SELECT *
                              FROM messages
                              WHERE id = " . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);

            $message = $res->fetch_assoc();

            if ($message['receiver'] == user::$current['id'] && $message['saved'] == 'no' || $message['sender'] == user::$current['id'] && $message['location'] == PM_DELETED)
            {
                $db->query("DELETE FROM messages
                           WHERE id = $id") or sqlerr(__FILE__, __LINE__);
				
				$Memcache->delete_value('statusbar::pm::count::' . user::$current['id']);
            }
            elseif ($message['receiver'] == user::$current['id'] && $message['saved'] == 'yes')
            {
                $db->query("UPDATE messages
                           SET location = 0, unread = 'no'
                           WHERE id = $id") or sqlerr(__FILE__, __LINE__);
				
				$Memcache->delete_value('statusbar::pm::count::' . user::$current['id']);
            }
            elseif ($message['sender'] == user::$current['id'] && $message['location'] != PM_DELETED)
            {
                $db->query("UPDATE messages
                           SET saved = 'no'
                           WHERE id = $id") or sqlerr(__FILE__, __LINE__);
            }
        }
        else
        {
            //-- Delete Multiple Messages --//
            foreach ($pm_messages
                     AS
                     $id)
            {
                $res = $db->query("SELECT *
                                  FROM messages
                                  WHERE id = " . sqlesc((int) $id));

                $message = $res->fetch_assoc();

                if ($message['receiver'] == user::$current['id'] && $message['saved'] == 'no' || $message['sender'] == user::$current['id'] && $message['location'] == PM_DELETED)
                {
                    $db->query("DELETE FROM messages
                               WHERE id = $id") or sqlerr(__FILE__, __LINE__);
					
					$Memcache->delete_value('statusbar::pm::count::' . user::$current['id']);
                }
                elseif ($message['receiver'] == user::$current['id'] && $message['saved'] == 'yes')
                {
                    $db->query("UPDATE messages
                               SET location = 0, unread = 'no'
                               WHERE id = $id") or sqlerr(__FILE__, __LINE__);
					
					$Memcache->delete_value('statusbar::pm::count::' . user::$current['id']);
                }
                elseif ($message['sender'] == user::$current['id'] && $message['location'] != PM_DELETED)
                {
                    $db->query("UPDATE messages
                               SET saved = 'no'
                               WHERE id = $id") or sqlerr(__FILE__, __LINE__);
                }
            }
        }
        //-- Check If Messages Were Moved --//
        if (@$db->affected_rows == 0)
        {
            error_message("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_del_msg']}");
        }
        else
        {
            header("Location: messages.php?action=viewmailbox");
            exit();
        }
    }

    error_message("error",
                  "{$lang['gbl_error']}",
                  "{$lang['err_no_action']}");
}

if ($action == 'forward')
{
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        //-- Display Form --//
        $pm_id = (int) $_GET['id'];

        //-- Get The Message --//
        $res = $db->query("SELECT *
                          FROM messages
                          WHERE id = " . sqlesc($pm_id) . "
                          AND (receiver = " . sqlesc(user::$current['id']) . "
                          OR sender = " . sqlesc(user::$current['id']) . ")
                          LIMIT 1") or sqlerr(__FILE__, __LINE__);

        if (!$res)
        {
            error_message("warn",
                          "{$lang['gbl_warn']}",
                          "{$lang['err_fwd_msg']}");
        }

        if ($res->num_rows == 0)
        {
            error_message("warn",
                          "{$lang['gbl_warn']}",
                          "{$lang['err_fwd_msg']}");
        }

        $message = $res->fetch_assoc();

        //-- Prepare Variables --//
        $subject = "Fwd: " . security::html_safe($message['subject']);
        $from    = $message['sender'];
        $orig    = $message['receiver'];

        $res = $db->query("SELECT username
                          FROM users
                          WHERE id = " . sqlesc($orig) . "
                          OR id = " . sqlesc($from)) or sqlerr(__FILE__, __LINE__);

        $orig2 = $res->fetch_assoc();

        $orig_name = "<a href='userdetails.php?id={$from}'>" . security::html_safe($orig2['username']) . "</a>";

        if ($from == 0)
        {
            $from_name         = "{$lang['text_system']}";
            $from2['username'] = "{$lang['text_system']}";
        }
        else
        {
            $from2     = $res->fetch_array(MYSQLI_BOTH);
            $from_name = "<a href='userdetails.php?id={$from}'>" . security::html_safe($from2['username']) . "</a>";
        }

        $body = "-------- {$lang['text_original']}{$from2['username']}: --------<br />" . format_comment($message['msg']);

        site_header($subject, false);?>

    <h1><?php echo $subject?></h1>

    <form method='post' action='messages.php'>
        <input type='hidden' name='action' value='forward' />
        <input type='hidden' name='id' value='<?php echo $pm_id?>' />
        <table border='0' width='100%' cellpadding='4' cellspacing='0'>
            <tr>
                <td class='colhead'><?php echo $lang['text_to']?></td>
                <td align='left'>
                    <input type='text' name='to' size='83' value='<?php echo $lang['form_username']?>' />
                </td>
            </tr>
            <tr>
                <td class='colhead'><?php echo $lang['table_orig_rcv']?></td>
                <td class='rowhead' align='left'><?php echo $orig_name?></td>
            </tr>
            <tr>
                <td class='colhead'><?php echo $lang['table_from']?></td>
                <td class='rowhead' align='left'><?php echo $from_name?></td>
            </tr>
            <tr>
                <td class='colhead'><?php echo $lang['table_subject']?></td>
                <td class='rowhead' align='left'>
                    <input type='text' name='subject' size='83' value='<?php echo $subject?>' />
                </td>
            </tr>
            <tr>
                <td class='colhead'><?php echo $lang['table_message']?></td>
                <td class='rowhead' align='left'>
                    <textarea name='msg' cols='80' rows='8'></textarea><br /><?php echo $body?>
                </td>
            </tr>
            <tr>
                <td class='rowhead' align='left' colspan='2'><?php echo $lang['table_save_msg']?>
                    <input type='checkbox' name='save' value='1'<?php echo user::$current['savepms'] == 'yes' ? " checked='checked'" : ""?> />&nbsp;
                    <input type='submit' class='btn' value='<?php echo $lang['btn_forward']?>' title='<?php echo $lang['btn_forward']?>' />
                </td>
            </tr>
        </table>
    </form>

    <?php
        site_footer();
    }
    else
    {
        //-- Forward The Message --//
        $pm_id = (int) $_POST['id'];

        //-- Get The Message --//
        $res = $db->query("SELECT *
                          FROM messages
                          WHERE id = " . sqlesc($pm_id) . "
                          AND (receiver = " . sqlesc(user::$current['id']) . "
                          OR sender = " . sqlesc(user::$current['id']) . ")
                          LIMIT 1") or sqlerr(__FILE__, __LINE__);

        if (!$res)
        {
            error_message("warn",
                          "{$lang['gbl_warn']}",
                          "{$lang['err_fwd_msg']}");
        }

        if ($res->num_rows == 0)
        {
            error_message("warn",
                          "{$lang['gbl_warn']}",
                          "{$lang['err_fwd_msg']}");
        }

        $message  = $res->fetch_assoc();
        $subject  = (string) $_POST['subject'];
        $username = strip_tags($_POST['to']);

        //-- Try Finding A User With Specified Name --//
        $res = $db->query("SELECT id
                          FROM users
                          WHERE LOWER(username) = LOWER(" . sqlesc($username) . ")
                          LIMIT 1");

        if (!$res)
        {
            error_message("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_inv_user']}");
        }

        if ($res->num_rows == 0)
        {
            error_message("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_inv_user']}");
        }

        $to = $res->fetch_array(MYSQLI_BOTH);
        $to = (int)$to[0];

        //-- Get Orignal Sender's Username --//
        if ($message['sender'] == 0)
        {
            $from = "{$lang['text_system']}";
        }
        else
        {
            $res = $db->query("SELECT *
                              FROM users
                              WHERE id = " . sqlesc($message['sender'])) or sqlerr(__FILE__, __LINE__);

            $from = $res->fetch_assoc();
            $from = $from['username'];
        }

        $body = (isset($_POST['msg']) ? (string) $_POST['msg'] : '');
        $body .= "\n--------{$lang['text_original']}{$from}: --------\n{$message['msg']}";
        $save = (isset($_POST['save']) ? (int) $_POST['save'] : '');

        if ($save)
        {
            $save = 'yes';
        }
        else
        {
            $save = 'no';
        }

        //-- Make Sure Recipient Wants This Message --//
        if (user::$current['class'] < UC_MODERATOR)
        {
            if ($from['acceptpms'] == 'yes')
            {
                $res2 = $db->query("SELECT *
                                   FROM blocks
                                   WHERE userid = $to
                                   AND blockid = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

                if ($res2->num_rows == 1)
                {
                    error_message("info",
                                  "{$lang['gbl_sorry']}",
                                  "{$lang['err_blocked']}");
                }
            }

            elseif ($from['acceptpms'] == 'friends')
            {
                $res2 = $db->query("SELECT *
                                   FROM friends
                                   WHERE userid = $to
                                   AND friendid = " . user::$current['id']) or sqlerr(__FILE__, __LINE__);

                if ($res2->num_rows != 1)
                {
                    error_message("info",
                                  "{$lang['gbl_sorry']}",
                                  "{$lang['err_only_friends']}");
                }
            }

            elseif ($from['acceptpms'] == 'no')
            {
                error_message("info",
                              "{$lang['gbl_sorry']}",
                              "{$lang['err_deny_pm']}");
            }
        }

        $db->query("INSERT INTO messages (poster, sender, receiver, added, subject, msg, location, saved)
                   VALUES(" . user::$current['id'] . ", " . user::$current['id'] . ", $to, '" . get_date_time() . "', " . sqlesc($subject) . "," . sqlesc($body) . ", " . sqlesc(PM_INBOX) . ", " . sqlesc($save) . ")") or sqlerr(__FILE__, __LINE__);

        error_message("success",
                      "{$lang['gbl_success']}",
                      "{$lang['err_pm_fwd']}");
    }
}

if ($action == 'editmailboxes')
{
    $res = $db->query("SELECT *
                      FROM pmboxes
                      WHERE userid = " . sqlesc(user::$current['id'])) or sqlerr(__FILE__, __LINE__);

site_header("{$lang['title_edit_mailbox']}", false); ?>

<h1><?php echo $lang['title_edit_mailbox']?></h1>
<table  border='0' width='100%'cellpadding='4' cellspacing='0'>
    <tr>
        <td class='colhead' align='left'><?php echo $lang['table_add_mailbox']?></td>
    </tr>
    <tr>
        <td class='rowhead' align='left'><?php echo $lang['table_add_mailbox_info']?><br />

            <form method='get' action='messages.php'>
                <input type='hidden' name='action' value='editmailboxes2' />
                <input type='hidden' name='action2' value='add' />
                <input type='text' name='new1' size='40' maxlength='14' /><br />
                <input type='text' name='new2' size='40' maxlength='14' /><br />
                <input type='text' name='new3' size='40' maxlength='14' /><br />
                <input type='submit' class='btn' value='<?php echo $lang['btn_add']?>' />
            </form>
        </td>
    </tr>
    <tr>
        <td class='colhead' align='left'><?php echo $lang['table_edit_mailbox']?></td>
    </tr>
    <tr>
        <td class='rowhead' align='left'><?php echo $lang['table_edit_mailbox_info']?>
            <form method='get' action='messages.php'>
                <input type='hidden' name='action' value='editmailboxes2' />
                <input type='hidden' name='action2' value='edit' />

                <?php

                if (!$res)
                {
                    echo ("<span style='text-align : center; font-weight : bold;'>{$lang['text_no_mailboxes']}</span>");
                }

                if ($res->num_rows == 0)
                {
                    echo ("<span style='text-align : center; font-weight : bold;'>{$lang['text_no_mailboxes']}</span>");
                }
                else
                {
                    while ($row = $res->fetch_assoc())
                    {
                        $id   = (int)$row['id'];
                        $name = security::html_safe($row['name']);

                        echo("<input type='text' name='edit$id' size='40' maxlength='14' value='$name' /><br />\n");
                    }

                    echo("<input type='submit' class='btn' value='{$lang['btn_edit']}' />");
                }
                ?>

            </form>
        </td>
    </tr>
</table>

<?php

    site_footer();
}

if ($action == 'editmailboxes2')
{
    $action2 = (string) $_GET['action2'];

    if (!$action2)
    {
        error_message("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_no_action']}");
    }

    if ($action2 == 'add')
    {
        $name1 = $_GET['new1'];
        $name2 = $_GET['new2'];
        $name3 = $_GET['new3'];

        //-- Get Current Max Box Number --//
        $res = $db->query("SELECT MAX(boxnumber)
                          FROM pmboxes
                          WHERE userid = " . sqlesc(user::$current['id']));

        $box = $res->fetch_array(MYSQLI_BOTH);
        $box = (int) $box[0];

        if ($box < 2)
        {
            $box = 1;
        }

        if (strlen($name1) > 0)
        {
            ++$box;
            $db->query("INSERT INTO pmboxes (userid, name, boxnumber)
                       VALUES (" . sqlesc(user::$current['id']) . ", " . sqlesc($name1) . ", $box)") or sqlerr(__FILE__, __LINE__);
        }

        if (strlen($name2) > 0)
        {
            ++$box;
            $db->query("INSERT INTO pmboxes (userid, name, boxnumber)
                       VALUES (" . sqlesc(user::$current['id']) . ", " . sqlesc($name2) . ", $box)") or sqlerr(__FILE__, __LINE__);
        }

        if (strlen($name3) > 0)
        {
            ++$box;
            $db->query("INSERT INTO pmboxes (userid, name, boxnumber)
                       VALUES (" . sqlesc(user::$current['id']) . ", " . sqlesc($name3) . ", $box)") or sqlerr(__FILE__, __LINE__);
        }

        header("Location: messages.php?action=editmailboxes");
        exit();
    }

    if ($action2 == 'edit')
    {
        $res = $db->query("SELECT *
                          FROM pmboxes
                          WHERE userid = " . sqlesc(user::$current['id']));

        if (!$res)
        {
            error_message("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_edit_mailbox']}");
        }

        if ($res->num_rows == 0)
        {
            error_message("error",
                          "{$lang['gbl_error']}",
                          "{$lang['err_edit_mailbox']}");
        }
        else
        {
            while ($row = $res->fetch_assoc())
            {
                if (isset($_GET['edit' . $row['id']]))
                {
                    if ($_GET['edit' . $row['id']] != $row['name'])
                    {
                        //-- Do Something --//
                        if (strlen($_GET['edit' . $row['id']]) > 0)
                        {
                            //-- Edit Name --//
                            $db->query("UPDATE pmboxes
                                       SET name = " . sqlesc($_GET['edit' . $row['id']]) . "
                                       WHERE id = " . sqlesc($row['id']) . "
                                       LIMIT 1");
                        }
                        else
                        {
                            //-- Delete --//
                            $db->query("DELETE
                                       FROM pmboxes
                                       WHERE id = " . sqlesc($row['id']) . "
                                       LIMIT 1");

                            //-- Delete All Messages From This Folder (uses Multiple Queries Because We Can Only Perform Security Checks In WHERE Clauses) --//
                            $db->query("UPDATE messages
                                       SET location = 0
                                       WHERE saved = 'yes'
                                       AND location = " . sqlesc($row['boxnumber']) . "
                                       AND receiver = " . sqlesc(user::$current['id']));

                            $db->query("UPDATE messages
                                       SET saved = 'no'
                                       WHERE saved = 'yes'
                                       AND sender = " . sqlesc(user::$current['id']));

                            $db->query("DELETE
                                       FROM messages
                                       WHERE saved = 'no'
                                       AND location = " . sqlesc($row['boxnumber']) . "
                                       AND receiver = " . sqlesc(user::$current['id']));

                            $db->query("DELETE
                                       FROM messages
                                       WHERE location = 0
                                       AND saved = 'yes'
                                       AND sender = " . sqlesc(user::$current['id']));
                        }
                    }
                }
            }
            header("Location: messages.php?action=editmailboxes");
            exit();
        }
    }
}

if ($action == 'deletemessage')
{
    $pm_id = (int) $_GET['id'];

    //-- Delete Message --//
    $res = $db->query("SELECT *
                      FROM messages
                      WHERE id = " . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);

    if (!$res)
    {
        error_message("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_msg_id']}");
    }

    if ($res->num_rows == 0)
    {
        error_message("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_inv_msg_id']}");
    }

    $message = $res->fetch_assoc();

    if ($message['receiver'] == user::$current['id'] && $message['saved'] == 'no')
    {
        $res2 = $db->query("DELETE
                           FROM messages
                           WHERE id = " . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
		
		$Memcache->delete_value('statusbar::pm::count::' . user::$current['id']);
    }

    elseif ($message['sender'] == user::$current['id'] && $message['location'] == PM_DELETED)
    {
        $res2 = $db->query("DELETE
                           FROM messages
                           WHERE id = " . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
		
		$Memcache->delete_value('statusbar::pm::count::' . user::$current['id']);
    }

    elseif ($message['receiver'] == user::$current['id'] && $message['saved'] == 'yes')
    {
        $res2 = $db->query("UPDATE messages
                           SET location = 0
                           WHERE id = " . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    }

    elseif ($message['sender'] == user::$current['id'] && $message['location'] != PM_DELETED)
    {
        $res2 = $db->query("UPDATE messages
                           SET saved = 'no'
                           WHERE id = " . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    }

    if (!$res2)
    {
        error_message("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_del_msg1']}");
    }

    if ($db->affected_rows == 0)
    {
        error_message("error",
                      "{$lang['gbl_error']}",
                      "{$lang['err_del_msg1']}");
    }
    else
    {
        header("Location: messages.php?action=viewmailbox&id={$message['location']}");
        exit();
    }
}

//-- Functions --//
function insertJumpTo($selected = 0)
{
    global $lang, $db;

    $res = $db->query("SELECT *
                      FROM pmboxes
                      WHERE userid = " . sqlesc(user::$current['id']) . "
                      ORDER BY boxnumber"); ?>

<form method='get' action='messages.php'>
    <input type='submit' class='btn' value='<?php echo $lang['btn_view']?>' title='<?php echo $lang['btn_view']?>' />
    <input type='hidden' name='action' value='viewmailbox' />
    <select name='box'>
        <option value='1'<?php echo ($selected == PM_INBOX ? " selected='selected' " : '')?>><?php echo $lang['form_inbox']?></option>
        <option value='-1'<?php echo ($selected == PM_SENTBOX ? " selected='selected' " : '')?>><?php echo $lang['form_sentbox']?></option>
        <?php
        while ($row = $res->fetch_assoc())
        {
            if ($row['boxnumber'] == $selected)
            {
                echo("<option value='{$row['boxnumber']}' selected='selected'>" . security::html_safe($row['name']) . "</option>\n");
            }
            else
            {
                echo("<option value='{$row['boxnumber']}'>" . security::html_safe($row['name']) . "</option>\n");
            }
        }
        ?>
    </select>

</form>

<?php

}

?>