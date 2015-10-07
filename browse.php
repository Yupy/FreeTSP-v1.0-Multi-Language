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
require_once(FUNC_DIR . 'function_torrenttable.php');
require_once(FUNC_DIR . 'function_bbcode.php');

db_connect(false);
logged_in();

$lang = array_merge(load_language('browse'),
                    load_language('func_bbcode'),
                    load_language('func_vfunctions'),
                    load_language('global'));

parked();

if (isset($_GET['clear_new']) && $_GET['clear_new'] == '1')
{
    $db->query("UPDATE users
               SET last_browse = " . gmtime() . " +3600
               WHERE id = " . user::$current['id']);
	
	$Memcache->delete_value('user::last::browse::' . user::$current['id']);

    header("Location: {$site_url}/browse.php");
}

$cats = cached::genrelist();

if (isset($_GET['search']))
{
    $searchstr      = unesc($_GET['search']);
    $cleansearchstr = searchfield($searchstr);

    if (empty($cleansearchstr))
    {
        unset($cleansearchstr);
    }
}

$orderby     = "ORDER BY torrents.sticky ASC, torrents.id DESC";
$addparam    = "";
$wherea      = array();
$wherecatina = array();

if (isset($_GET['incldead']) && $_GET['incldead'] == 1)
{
    $addparam .= "incldead=1&amp;";

    if (!isset(user::$current) || get_user_class() < UC_ADMINISTRATOR)
    {
        $wherea[] = "banned != 'yes'";
    }
}
else
{
    if (isset($_GET['incldead']) && $_GET['incldead'] == 2)
    {
        $addparam .= "incldead=2&amp;";
        $wherea[] = "visible = 'no'";
    }
    else
    {
        $wherea[] = "visible = 'yes'";
    }
}

$category = (isset($_GET['cat'])) ? (int) $_GET['cat'] : false;
$all      = isset($_GET['all']) ? $_GET['all'] : false;

if (!$all)
{
    if (!$_GET && user::$current['notifs'])
    {
        $all = true;

        foreach ($cats
                 AS
                 $cat)
        {
            $all &= $cat['id'];

            if (strpos(user::$current['notifs'], "[cat" . $cat['id'] . "]") !== false)
            {
                $wherecatina[] = $cat['id'];
                $addparam .= "c{$cat['id']}=1&amp;";
            }
        }
    }
    elseif ($category)
    {
        if (!is_valid_id($category))
        {
            error_message_center("error",
                                 "{$lang['gbl_error']}",
                                 "{$lang['err_inv_id']}");
        }

        $wherecatina[] = $category;
        $addparam .= "cat = $category&amp;";
    }
    else
    {
        $all = true;

        foreach ($cats
                 AS
                 $cat)
        {
            $all &= isset($_GET["c{$cat['id']}"]);

            if (isset($_GET["c{$cat['id']}"]))
            {
                $wherecatina[] = $cat['id'];
                $addparam .= "c{$cat['id']}=1&amp;";
            }
        }
    }
}

if ($all)
{
    $wherecatina = array();
    $addparam    = "";
}

if (count($wherecatina) > 1)
{
    $wherecatin = implode(",", $wherecatina);
}

elseif (count($wherecatina) == 1)
{
    $wherea[] = "category = $wherecatina[0]";
}

$wherebase = $wherea;

if (isset($cleansearchstr))
{
    $wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")";
    $addparam .= "search = " . urlencode($searchstr) . "&amp;";
    $orderby  = "";
}

$where = implode(" AND ", $wherea);

if (isset($wherecatin))
{
    $where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";
}

if ($where != "")
{
    $where = "WHERE " . $where;
}

if (($count = $Memcache->get_value('torrents::where::' . sha1($where))) === false) {
    $res = $db->query("SELECT COUNT(id)
                       FROM torrents " . $where) or die($db->error);
    $row = $res->fetch_array(MYSQLI_BOTH);
    $count = (int)$row[0];
    $Memcache->add_value('torrents::where::' . sha1($where), $count, 120);
}

if (!$count && isset($cleansearchstr))
{
    $wherea  = $wherebase;
    $orderby = "ORDER BY torrents.sticky ASC, torrents.id DESC";
    $searcha = explode(" ", $cleansearchstr);
    $sc      = 0;

    foreach ($searcha
            AS
            $searchss)
    {
        if (strlen($searchss) <= 1)
        {
            continue;
        }

        $sc++;

        if ($sc > 5)
        {
            break;
        }

        $ssa = array();

        foreach (array("search_text",
                       "ori_descr")
                        AS
                        $sss)

        {
            $ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
        }

        $wherea[] = "(" . implode(" or ", $ssa) . ")";
    }

    if ($sc)
    {
        $where = implode(" AND ", $wherea);
        if ($where != "")
        {
            $where = "WHERE $where";
        }

        if (($count = $Memcache->get_value('torrents::where::' . sha1($where))) === false) {
            $res = $db->query("SELECT COUNT(id)
                               FROM torrents " . $where) or die($db->error);
            $row = $res->fetch_array(MYSQLI_BOTH);
            $count = (int)$row[0];
            $Memcache->add_value('torrents::where::' . sha1($where), $count, 120);
        }
    }
}

$torrentsperpage = user::$current['torrentsperpage'];

if (!$torrentsperpage)
{
    $torrentsperpage = 15;
}

if ($count)
{
    list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "browse.php?" . $addparam);

    $query = "SELECT torrents.id, torrents.sticky, torrents.category, torrents.leechers, torrents.seeders, torrents.freeleech, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments, torrents.numfiles, torrents.filename, torrents.sticky, torrents.anonymous, torrents.banned,
torrents.owner,IF(torrents.nfo <> '', 1, 0) AS nfoav," .
            "categories.name AS cat_name, categories.image AS cat_pic, users.username
            FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $orderby $limit";

    $res = $db->query($query) or die($db->error);
}
else
{
    unset($res);
}

if (isset($cleansearchstr))
{
    site_header("{$lang['title_results']} $searchstr", false);
}
else
{
    site_header('', false);
}

?>

<table class='bottom' width='100%'>
    <tr>
        <td class='embedded'>
            <form method='get' action='browse.php'>
                <p align='center'>
                    <label for='search'>
                        <span style='font-weight : bold;'><?php echo $lang['form_search']?>&nbsp;</span>
                    </label>
                    <input type='text' name='search' id='search' size='40' value='<?php security::html_safe($searchstr)?>' />
                    <input type='submit' class='btn' value='<?php echo $lang['gbl_btn_submit']?>' />
                </p>
            </form>
        </td>
    </tr>
</table>

<form method='get' action='browse.php'>
    <table class='bottom' width='100%'>
        <tr>
            <td class='bottom'>
                <table class='bottom' align='right' width='75%'>
                    <tr>
                        <?php

                        $i = 0;

                        //-- Comment Out To Show The Categories As Images --//
                        foreach ($cats
                                 AS
                                 $cat)
                        {
                            $catsperrow = 6;

                            print(($i && $i % $catsperrow == 0) ? "</tr><tr>" : "");

                            print("<td class='bottom' style='padding-bottom : 2px; padding-left : 7px'>
                                    <input name='c{$cat['id']}' type='checkbox' " . (in_array($cat['id'], $wherecatina) ? "checked='checked' " : "") . "value='1' /><a class='catlink' href='browse.php?cat={$cat['id']}'>" . security::html_safe($cat['name']) . "</a>
                                    </td>\n");

                            $i++;
                        }

                        //-- Comment Out To Show The Categories As Text --//
                        /*foreach ($cats
                                 AS
                                 $cat)
                        {
                            $catsperrow = 6;

                            print(($i && $i % $catsperrow == 0) ? "</tr><tr>" : "");

                            print("<td class='bottom' style='padding-bottom : 2px; padding-left : 7px'>
                            <input name='c{$cat['id']}' type='hidden' " . (in_array($cat['id'], $wherecatina) ? " checked='checked' " : "") . "value='1' />
                            <a class='catlink' href='browse.php?cat={$cat['id']}'><img src='{$image_dir}caticons/" . security::html_safe($cat['image']) . "' alt='' title='' /></a></td>\n");
                            $i++;
                        }*/

                        $alllink     = "<div align='left'>(<a href='browse.php?all=1'><span style='font-weight : bold;'>{$lang['text_show_all']}</span></a>)</div>";
                        $ncats       = count($cats);
                        $nrows       = ceil($ncats / $catsperrow);
                        $lastrowcols = $ncats % $catsperrow;

                        if ($lastrowcols != 0)
                        {
                            if ($catsperrow - $lastrowcols != 1)
                            {
                                print("<td class='bottom' rowspan='" . ($catsperrow - $lastrowcols - 1) . "'>&nbsp;</td>");
                            }
                            print("<td class='bottom' style='padding-left : 5px'>$alllink</td>\n");
                        }

                        ?>
                    </tr>
                </table>
            </td>
            <td class='bottom'>
                <table class='bottom' width='50'>
                    <tr>
                        <td class='bottom' style='padding : 1px; padding-left : 10px'>
                            <label><select name='incldead'>
                                <option value='0'><?php echo $lang['form_opt_active']?></option>
                                <option value='1'<?php echo($_GET['incldead'] == 1 ? " selected='selected' " : ""); ?>>
                                    <?php echo $lang['form_opt_inc_dead']?>
                                </option>
                                <option value='2'<?php echo($_GET['incldead'] == 2 ? " selected='selected' " : ""); ?>>
                                    <?php echo $lang['form_opt_dead']?>
                                </option>
                            </select></label>
                        </td>

                        <?php

                        if ($ncats % $catsperrow == 0)
                        {
                            print("<td class='bottom' align='right' valign='center' rowspan='$nrows' style='padding-left : 15px'>$alllink</td>\n");
                        }

                        ?>

                    </tr>
                    <tr>
                        <td class='bottom' style='padding : 1px 1px 1px 10px; padding-left : 10px'>
                            <div align='center'>
                                <input type='submit' class='btn' value='<?php echo $lang['gbl_btn_submit']?>' />
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>

<?php

if (isset($cleansearchstr))
{
    print("<h2>{$lang['test_results']}'" . security::html_safe($searchstr) . "'</h2>\n");
}

//-- If you want a Button --//
//echo ("<a href='?clear_new=1'><input type='submit' class='btn' value='{$lang['btn_clear_tag']}' /></a><br />");
//-- If you want a Link --//


    echo ("<a class='altlink' href='?clear_new=1'><span class='browse'>{$lang['btn_clear_tag']}</span></a>");


if ($count)
{
    print($pagertop);

    torrenttable($res);

    print($pagerbottom);
}
else
{
    if (isset($cleansearchstr))
    {
        error_message_center("info",
                             "{$lang['err_nothing']}",
                             "{$lang['err_refine']}");
    }
    else
    {
        echo ("<br />");
        display_message_center("info",
                               "{$lang['gbl_sorry']}",
                               "{$lang['err_nothing1']}");
    }
}
//
site_footer();

?>