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

function linkcolor($num)
{
    if (!$num)
    {
        return "linkcolor_leech";
    }

    return "linkcolor_seed";
}

function torrenttable($res, $variant = "index")
{
    $lang = array_merge(load_language('func_torrenttable'));

    global $Memcache, $db, $image_dir, $torrents_allfree, $added, $waittime, $max_class_wait, $ratio_1, $ratio_2, $ratio_3, $ratio_4, $gigs_1, $gigs_2, $gigs_3, $gigs_4, $wait_1, $wait_2, $wait_3, $wait_4;

	if (($last_browse = $Memcache->get_value('user::last::browse::' . user::$current['id'])) === false) {
        $browse_res = $db->query("SELECT last_browse
                              FROM users
                              WHERE id = " . user::$current['id']);
        $browse_arr = $browse_res->fetch_row();
        $last_browse = (int)$browse_arr[0];
        $Memcache->add_value('user::last::browse::' . user::$current['id'], $last_browse, 25200);
    }

    $time_now = gmtime();

    if ($last_browse > $time_now)
    {
      $last_browse = $time_now;
    }

    ?>
    <table border='1' align='center' cellspacing='0' cellpadding='5'>
        <tr>
            <td class='colhead' align='center'><?php echo $lang['table_type']?></td>
            <td class='colhead' align='left'><?php echo $lang['table_name']?></td>
            <td class='colhead' align='center'><?php echo $lang['table_download']?></td>
            <td class='colhead' align='right'><?php echo $lang['table_files']?></td>
            <td class='colhead' align='right'><?php echo $lang['table_comments']?></td>

            <!--<td class='colhead' align='center'><?php echo $lang['table_rating']?></td>-->
            <!--<td class='colhead' align='center'><?php echo $lang['table_added']?></td>-->

            <td class='colhead' align='center'><?php echo $lang['table_size']?></td>

            <!--<td class='colhead' align='right'><?php echo $lang['table_views']?></td>-->
            <!--<td class='colhead' align='right'><?php echo $lang['table_hits']?></td>-->

            <td class='colhead' align='center'><?php echo $lang['table_snatched']?></td>
            <td class='colhead' align='right'><?php echo $lang['table_seeders']?></td>
            <td class='colhead' align='right'><?php echo $lang['table_leechers']?></td>
    <?php

    if ($variant == 'index')
    {
        print("<td class='colhead' align='center'>{$lang['table_up_by']}</td>\n");
    }

    print("</tr>\n");

    while ($row = $res->fetch_assoc())
    {
        $id = (int)$row['id'];

        if ($row['sticky'] == 'yes')
        {
            echo("<tr class='sticky'>\n");
        }
        else
        {
            echo("<tr>\n");
        }

        print("<td class='rowhead' align='center' style='padding : 0px'>");

        if (isset($row['cat_name']))
        {
            print("<a href='/browse.php?cat=" . (int)$row['category'] . "'>");

            if (isset($row['cat_pic']) && $row['cat_pic'] != "")
            {
                print("<img src='{$image_dir}caticons/{$row['cat_pic']}' width='60' height='54' border='0' alt='{$row['cat_name']}' title='{$row['cat_name']}' />");
            }
            else
            {
                print security::html_safe($row['cat_name']);
            }
            echo("</a>");
        }
        else
        {
            print("-");
        }

        print("</td>\n");

        $freeleech = ($row['freeleech']=='yes' ? "&nbsp;<img src='{$image_dir}free.png' width='32' height='15' border='0' align='right' alt='{$lang['img_alt_free']}' title='{$lang['img_alt_free']}' />" : "");

        $dispname = security::html_safe($row['name']);

        $added    = sqlesc(get_date_time());

        $sticky = ($row['sticky']=='yes' ? "<img align='right' src='{$image_dir}sticky.png' width='40' height='15' border='0' alt='{$lang['img_alt_sticky']}' title='{$lang['img_alt_sticky']}' />" : "");

        $allfree  = ("<img align='right' src='{$image_dir}free.png' width='32' height='15' border='0' alt='{$lang['img_alt_free']}' title='{$lang['img_alt_free']}' />");

        print("<td class='rowhead' align='left'><a href='details.php?");

        if ($variant == 'mytorrents')
        {
            print("returnto=" . urlencode($_SERVER['REQUEST_URI']) . "&amp;");
        }

        print("id=$id");

        if ($variant == 'index')
        {
            print("&amp;hit=1");
        }

        if ($torrents_allfree =='true')
        {
            if (sql_timestamp_to_unix_timestamp($row['added']) >= $last_browse)
            {
                print("'><span style='font-weight : bold;'>$dispname&nbsp;</span></a><img src='{$image_dir}new.png' width='30' height='15' border='0' align='right' alt='{$lang['img_alt_new']}' title='{$lang['img_alt_new']}' />&nbsp;&nbsp;$sticky$allfree<br />{$row['added']}</td>\n");
            }
            else
            {
                print("'><span style='font-weight : bold;'>$dispname&nbsp;&nbsp;$sticky$allfree</span></a><br />{$row['added']}</td>\n");
            }
        }
        else
        {
            if (sql_timestamp_to_unix_timestamp($row['added']) >= $last_browse)
            {
                print("'><span style='font-weight : bold;'>$dispname&nbsp;</span></a><img src='{$image_dir}new.png' width='30' height='15' border='0' align='right' alt='{$lang['img_alt_new']}' title='{$lang['img_alt_new']}' />&nbsp;&nbsp;$sticky$freeleech<br />{$row['added']}</td>\n");
            }
            else
            {
                print("'><span style='font-weight : bold;'>$dispname&nbsp;&nbsp;$sticky$freeleech</span></a><br />{$row['added']}</td>\n");
            }
        }

        if (user::$current['class'] <= $max_class_wait)
        {
            $gigs = user::$current['uploaded'] / (1024*1024*1024);
            $ratio = ((user::$current['downloaded'] > 0) ? (user::$current['uploaded'] / user::$current['downloaded']) : 0);

            if ($ratio < $ratio_1 || $gigs < $gigs_1)
            {
                $wait = $wait_1;
            }
            elseif ($ratio < $ratio_2 || $gigs < $gigs_2)
            {
                $wait = $wait_2;
            }
            elseif ($ratio < $ratio_3 || $gigs < $gigs_3)
            {
                $wait = $wait_3;
            }
            elseif ($ratio < $ratio_4 || $gigs < $gigs_4)
            {
                $wait = $wait_4;
            }
            else
            {
                $wait = 0;
            }
        }

        $elapsed = floor((gmtime() - strtotime($row['added'])) / 3600);

        if (($elapsed < $wait) && ($waittime == 'true'))
        {
            print("<td align='center'><span class='waittime'>{$lang['text_wait']}<br />" . number_format($wait - $elapsed) . "{$lang['text_hours']}<br />{$lang['text_remain']}</span></td>\n");
        }
        elseif (user::$current['downloadpos'] == 'no' || ($row['banned'] == 'yes'))
        {
            print("<td align='center'><span class='download_pos'>{$lang['text_download']}<br />{$lang['text_disabled']}</span></td>\n");
        }
        else
        {
            print("<td class='rowhead' align='center'><a href='/download.php/$id/" . rawurlencode($row['filename']) . "'><img src='{$image_dir}download.png' width='16' height='16' border='0' alt='{$lang['img_alt_download']}' title='{$lang['img_alt_download']}' /></a></td>\n");
        }

        if ($row['type'] == 'single')
        {
            print("<td align='center'>" . (int)$row['numfiles'] . "</td>\n");
        }
        else
        {
            if ($variant == 'index')
            {
                print("<td class='rowhead' align='center'><span style='font-weight : bold;'><a href='/details.php?id=$id&amp;hit=1&amp;filelist=1'>" . (int)$row['numfiles'] . "</a></span></td>\n");
            }
            else
            {
                print("<td class='rowhead' align='center'><span style='font-weight : bold;'><a href='/details.php?id=$id&amp;filelist=1#filelist'>" . (int)$row['numfiles'] . "</a></span></td>\n");
            }
        }

        if (!$row['comments'])
        {
            print("<td class='rowhead' align='center'>" . (int)$row['comments'] . "</td>\n");
        }
        else
        {
            if ($variant == 'index')
            {
                print("<td class='rowhead' align='center'><span style='font-weight : bold;'><a href='/details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . (int)$row['comments'] . "</a></span></td>\n");
            }
            else
            {
                print("<td class='rowhead' align='center'><span style='font-weight : bold;'><a href='/details.php?id=$id&amp;page=0#startcomments'>" . (int)$row['comments'] . "</a></span></td>\n");
            }
        }

    /*
        print("<td class='rowhead' align='center'>");
        if (!isset($row['rating']))
            print("---");
        else {
            $rating = round($row['rating'] * 2) / 2;
            $rating = ratingpic($row['rating']);
            if (!isset($rating))
                print("---");
            else
                print($rating);
        }
        print("</td>\n");

        print("<td class='rowhead' align='center'><table border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>".str_replace(" ", "<br />")."</td></tr></table></td>\n");
    */

        print("<td class='rowhead' align='center'>" . str_replace(" ", "<br />", misc::mksize($row['size'])) . "</td>\n");

        // print("<td class='rowhead' align='right'>{$row['views']}</td>\n");
        // print("<td class='rowhead' align='right'>{$row['hits']}</td>\n");

        $_s = "";

        if ($row['times_completed'] != 1)
        {
            $_s = "s";
        }

        print("<td class='rowhead' align='center'>" . ($row['times_completed'] > 0 ? "<a href='/snatches.php?id=$id'>" . number_format($row['times_completed']) . "<br />{$lang['text_time']}$_s</a>" : "0 {$lang['text_times']}") . "</td>\n");

        if ($row['seeders'])
        {
            if ($variant == 'index')
            {
                if ($row['leechers'])
                {
                    $ratio = $row['seeders'] / $row['leechers'];
                }
                else
                {
                    $ratio = 1;
                }

                print("<td class='rowhead' align='right'><span style='font-weight : bold;'><a href='details.php?id=$id&amp;dllist=1#seeders'><span style='color :" . get_slr_color($ratio) . "'>" . (int)$row['seeders'] . "</span></a></span></td>\n");
            }
            else
            {
                print("<td class='rowhead' align='right'><span style='font-weight : bold;'><a class='" . linkcolor($row['seeders']) . "' href='details.php?id=$id&amp;dllist=1#seeders'>" . (int)$row['seeders'] . "</a></span></td>\n");
            }
        }
        else
        {
            print("<td class='rowhead' align='right'><span class='" . linkcolor($row['seeders']) . "'>" . (int)$row['seeders'] . "</span></td>\n");
        }

        if ($row['leechers'])
        {
            if ($variant == 'index')
            {
                print("<td class='rowhead' align='right'><span style='font-weight : bold;'><a href='/details.php?id=$id&amp;dllist=1#leechers'>" . number_format($row['leechers']) . "</a></span></td>\n");
            }
            else
            {
                print("<td class='rowhead' align='right'><span style='font-weight : bold;'><a class='" . linkcolor($row['leechers']) . "' href='/details.php?id=$id&amp;dllist=1#leechers'>" . (int)$row['leechers'] . "</a></span></td>\n");
            }
        }
        else
        {
            print("<td class='rowhead' align='right'>0</td>\n");
        }

        if ($variant == 'index')
        {
            if ($row['anonymous'] == 'yes')
            {
                print("<td align='center'><em>{$lang['text_anon']}</em></td>\n");
            }
            else
            {
                print("<td align='center'>" . (isset($row['username']) ? ("<a href='userdetails.php?id=" . (int)$row['owner'] . "'><strong>" . security::html_safe($row['username']) . "</strong></a>") : "<em>({$lang['text_unknown']})</em>") . "</td>\n");
            }
        }

        print("</tr>\n");
    }

    print("</table>\n");

    return $rows;
}