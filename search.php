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
require_once(FUNC_DIR . 'function_vfunctions.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_bbcode.php');

db_connect();
logged_in();

$lang = array_merge(load_language('search'),
                    load_language('global'),
                    load_language('func_bbcode'));

site_header("{$lang['title_search']}");

?>

<table class='main' border='0' width='100%' cellspacing='0' cellpadding='0'>
    <tr>
        <td class='embedded'>
            <form method='get' action='browse.php'>
                <p align='center'>
                    <?php echo $lang['text_search']?>
                    <input type='text' name='search' size='40' value='<?php echo security::html_safe($searchstr) ?>' />
                    <?php echo $lang['text_in']?>
                    <select name='cat'>
                        <option value='0'><?php echo $lang['text_types']?></option>

                        <?php

                        $cats        = cached::genrelist();
                        $catdropdown = '';

                        foreach ($cats
                                AS
                                $cat)
                        {
                            $catdropdown .= "<option value='{$cat['id']}'";
                            $getcat      = (isset($_GET['cat']) ? $_GET['cat'] : '');

                            if ($cat['id'] == $getcat)

                            {
                                $catdropdown .= " selected='selected'";
                            }

                            $catdropdown .= ">" . security::html_safe($cat['name']) . "</option>\n";
                        }

                        $deadchkbox = "<input type='checkbox' name='incldead' value='1'";

                        if (isset($_GET['incldead']))
                        {
                            $deadchkbox .= " checked='checked'";
                        }

                        $deadchkbox .= " />{$lang['text_inc_dead']}\n";

                        echo $catdropdown;

                        ?>

                    </select>

                    <?php echo $deadchkbox; ?>

                    <input type='submit' class='btn' value='<?php echo $lang['btn_search']?>' />
                </p>
            </form>
        </td>
    </tr>
</table>

<?php

site_footer();

?>