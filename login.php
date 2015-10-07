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
require_once(FUNC_DIR . 'function_page_verify.php');

$newpage = new page_verify();
$newpage->create('_login_');

db_connect();

$lang = array_merge(load_language('login'),
                    load_language('global'));

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <title><?php echo $site_name ?></title>

    <!-- Style Sheet General -->
    <link href='css/reset.css' rel='stylesheet' type='text/css' />
    <link href='css/960.css' rel='stylesheet' type='text/css' />
    <link href='css/style.css' rel='stylesheet' type='text/css' />

</head>

<body>
<table width='100%' cellspacing='0' cellpadding='0' style='background : transparent'>
    <tr>
        <td>
            <div align='center'>
                <a href='index.php'><img src='<?php echo $image_dir?>logo.png' width='486' height='100' border='0' alt='<?php echo $site_name?>' title='<?php echo $site_name?>' style='vertical-align : middle;' /></a>
            </div>
        </td>
    </tr>
</table>

<!-- Start Container -->
<div class='container_12'>

    <div class='loading radius-left'>
        <span><?php echo $lang['text_loading']?></span>
    </div>

    <!-- Start Forms -->
    <div class='grid_4 push_4'>

        <div class='box radius'>

            <form class='active' id='login' action='takelogin.php'>
                <?php
                    $value = array('...', '...', '...', '...', '...', '...');
                    $value [mt_rand(1, count($value)-1)] = 'X';
                ?>

                <h1>Log in</h1>

                <fieldset class='radius'>

                    <p><?php echo $maxloginattempts, $lang['text_failed_login']?><br /><br /><?php echo $lang['text_you_have']?>&nbsp;<?php echo remaining(), $lang['text_attempts'];?>
                    </p>

                    <p>
                        <label class='required' for='username'><?php echo $lang['form_username']?></label>
                        <br />
                        <input type='text' name='username' id='username' />
                    </p>

                    <p>
                        <label class='required' for='password'><?php echo $lang['form_password']?></label>
                        <br />
                        <input type='password' name='password' id='password' />
                    </p>

                    <p>
                        <?php echo $lang['form_click_x']?><strong>X</strong>
                    </p>

                    <p>
                        <?php
                        for ($i=0; $i < count($value); $i++)
                        {
                            echo("<input type='submit' class='btn' name='submitme' value='{$value[$i]}' />");
                        }
                        ?>
                    </p>

                    <br />
                    <p><a href='#' class='link' rel='registration'><?php echo $lang['form_create']?></a></p>
                    <p><a href='#' class='link' rel='lost_password'><?php echo $lang['form_forgot']?></a></p>
                    <p><a href='loginhelp.php'><?php echo $lang['form_help']?></a></p>

                </fieldset>

            </form>

            <form method='post' action='recover.php' id='lost_password'>

                <h1><?php echo $lang['form_forgot_1']?></h1>

                <fieldset class='radius'>

                    <p>
                        <label class='required' for='email'><?php echo $lang['form_email']?></label>
                        <br />
                        <input type='text' name='email' id='email' />
                    </p>

                    <input type='submit' class='button button-orange float_right' value='<?php echo $lang['gbl_btn_submit']?>' />
                    <br />
                    <p><a href='#' class='link' rel='login'><?php echo $lang['form_login']?></a></p>
                    <p><a href='#' class='link' rel='registration'><?php echo $lang['form_create']?></a></p>

                </fieldset>

            </form>

            <form action='takesignup.php' id='registration'>



                <?php

                $res = $db->query("SELECT COUNT(*)
                                  FROM users") or sqlerr(__FILE__, __LINE__);

                $arr = $res->fetch_row();

                if ($arr[0] >= $max_users_then_invite)
                {
                    ?><h1><?php echo $lang['form_register_closed']?></h1><?php

                    display_message("info",
                                    "<span class='login_sorry'>{$lang['gbl_sorry']}</span>",
                                    "<span class='login_limit'>{$lang['text_limit_reached']}(" . number_format($max_users) . "){$lang['text_call_back']}</span>");

                    echo("<p><a href='#' class='link' rel='invited_user'><font class='login_invited'>{$lang['text_have_invite']}</font></a></p>");
                }

                else

                { ?>

                <h1><?php echo $lang['form_register']?></h1>

                <fieldset class='radius'>

                    <p>
                        <label class='required' for='registration_username'><?php echo $lang['form_register_name']?></label>
                        <br />
                        <input type='text' name='wantusername' id='registration_username' />
                    </p>

                    <p>
                        <label class='required' for='registration_password'><?php echo $lang['form_register_pass']?></label>
                        <br /><img src='<?php echo $image_dir?>password/tooshort.gif' width='240' height='27' border='0' id='strength' alt='' title='' /><br />
                        <input type='password' name='wantpassword' id='registration_password' maxlength='15' onkeyup="updatestrength( this.value );" />
                    </p>

                    <p>
                        <label class='required' for='registration_password_repeat'><?php echo $lang['form_register_pass_1']?></label>
                        <br />
                        <input type='password' name='passagain' id='registration_password_repeat' />
                    </p>

                    <p>
                        <label class='required' for='registration_email'><?php echo $lang['form_register_email']?></label>
                        <br />
                        <input type='text' name='email' id='registration_email' />
                    </p>

                    <p>
                        <input type='checkbox' name='rulesverify' value='yes' />&nbsp;<?php echo $lang['form_agree_rules']?><br />
                        <input type='checkbox' name='faqverify' value='yes' />&nbsp;<?php echo $lang['form_agree_faq']?><br />
                        <input type='checkbox' name='ageverify' value='yes' />&nbsp;<?php echo $lang['form_agree_age']?><br /><br />

                        <input type='submit' class='button button-orange float_right' value='<?php echo $lang['btn_create']?>' /><br />
                    </p>

                    <p><a href='#' class='link' rel='login'><?php echo $lang['form_login_here']?></a></p>

                </fieldset>



            <?php } ?>
            </form>


            <form method='post' action='take_invite_signup.php' id='invited_user'>

                <h1><?php echo $lang['form_invited']?></h1>

                <fieldset class='radius'>

                    <p>
                        <label class='required' for='invited_user_username'><?php echo $lang['form_register_name']?></label>
                        <br />
                        <input type='text' name='wantusername' id='invited_user_username' />
                    </p>

                    <p>
                        <label class='required' for='invited_user_password'><?php echo $lang['form_register_pass']?></label>
                        <br /><img src='<?php echo $image_dir?>password/tooshort.gif' width='240' height='27' border='0'  id='strength1' alt='' title='' /><br />
                        <input type='password' name='wantpassword' id='invited_user_password' maxlength='15' onkeyup="updatestrength( this.value );" />
                    </p>

                    <p>
                        <label class='required' for='invited_user_password_repeat'><?php echo $lang['form_register_pass_1']?></label>
                        <br />
                        <input type='password' name='passagain' id='invited_user_password_repeat' />
                    </p>

                    <p>
                        <label class='required' for='invited_user_invited_user'><?php echo $lang['form_invite_code']?></label>
                        <br />
                        <input type='password' name='invite' id='invited_user_invited_user' />
                    </p>

                    <p>
                        <label class='required' for='invited_user_email'><?php echo $lang['form_register_email']?></label>
                        <br />
                        <input type='text' name='email' id='invited_user_email' />
                    </p>

                    <p>
                        <input type='checkbox' name='rulesverify' value='yes' />&nbsp;<?php echo $lang['form_agree_rules']?><br />
                        <input type='checkbox' name='faqverify' value='yes' />&nbsp;<?php echo $lang['form_agree_faq']?><br />
                        <input type='checkbox' name='ageverify' value='yes' />&nbsp;<?php echo $lang['form_agree_age']?><br /><br />

                        <input type='submit' class='button button-orange float_right' value='<?php echo $lang['btn_signup']?>' /><br />
                    </p>

                </fieldset>

            </form>

        </div>

    </div>
    <!-- End Forms -->

</div>
<!-- End Container -->

<!-- jQuery -->
    <script type='text/javascript' src='js/jquery.min.js'></script>
    <script type='text/javascript' src='js/password.js'></script>

    <!-- Custom js -->
    <script type='text/javascript' src='js/custom.min.js'></script>

</body>
</html>