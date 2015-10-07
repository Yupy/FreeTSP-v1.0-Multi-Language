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

//-- New Pager Count Is Total Number, Perpage Is Duh!, URL Is Whatever It's Going Too \o <=== And That's Me Waving To Pdq, Just Saying "hi There" --//

function pager_new($count, $perpage, $page, $url, $page_link = false)
{
    $lang = array_merge(load_language('func_pager_new'));

    $pages = floor($count / $perpage);

    if ($pages * $perpage < $count)
        ++$pages;

    //-- Lets Make Php Happy --//
    $page_num = '';
    $page     = ($page < 1 ? 1 : $page);
    $page     = ($page > $pages ? $pages : $page);

    //-- Lets Add The ... If Too Many Pages --//
    switch (true)
    {
        case ($pages < 11):
            for ($i = 1; $i <= $pages; ++$i)
            {
                $page_num .= ($i == $page ? ' ' . $i . ' ' : ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ');
            }
        break;

        case ($page < 5 || $page > ($pages - 3)):
            for ($i = 1; $i < 5; ++$i)
            {
                $page_num .= ($i == $page ? ' ' . $i . ' ' : ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ');
            }

            $page_num .= ' ... ';
            $math     = round($pages / 2);

            for ($i = ($math - 1); $i <= ($math + 1); ++$i)
            {
                $page_num .= ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ';
            }

            $page_num .= ' ... ';

            for ($i = ($pages - 2); $i <= $pages; ++$i)
            {
                $page_num .= ($i == $page ? ' ' . $i . ' ' : ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ');
            }
        break;

        case ($page > 4 && $page < ($pages - 2)):
            for ($i = 1; $i < 5; ++$i)
            {
                $page_num .= ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ';
            }

            $page_num .= ' ... ';
                for ($i = ($page - 1); $i <= ($page + 1); ++$i)
                {
                    $page_num .= ($i == $page ? ' ' . $i . ' ' : ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ');
                }

                $page_num .= ' ... ';

                for ($i = ($pages - 2); $i <= $pages; ++$i)
                {
                    $page_num .= ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ';
                }
        break;
    }

    $menu = ($page == 1 ? " <br /><div style='text-align : center; font-weight : bold;'>{$lang['text_prev']}" : "<br /><div style='text-align : center; font-weight : bold;'><a class='altlink' href='" . $url . '&amp;page=' . ($page - 1) . $page_link . "'>{$lang['text_prev']}</a>") . "&nbsp;&nbsp;&nbsp;" . $page_num . "&nbsp;&nbsp;&nbsp;" . ($page == $pages ? "{$lang['text_next']}</div><br /> " : " <a class='altlink' href='" . $url . '&amp;page=' . ($page + 1) . $page_link . "'>{$lang['text_next']}</a></div><br />");

    /*
    $menu = ($page == 1 ? " <br /><div style='text-align : center; font-weight : bold;'><img src='images/arrow_prev.gif' alt='&lt;&lt;' />{$lang['text_prev']}" : "<br /><div style='text-align : center; font-weight : bold;'><img src='images/arrow_prev.gif' alt='&lt;&lt;' /><a class='altlink' href='" . $url . '&amp;page=' . ($page - 1) . $page_link . "'>{$lang['text_prev']}</a>") . "&nbsp;&nbsp;&nbsp;" . $page_num . "&nbsp;&nbsp;&nbsp;" . ($page == $pages ? "{$lang['text_next']}<img src='images/arrow_next.gif' alt='&gt;&gt;' /></div><br /> " : " <a class='altlink' href='" . $url . '&amp;page=' . ($page + 1) . $page_link . "'>{$lang['text_next']}<img src='images/arrow_next.gif' alt='&gt;&gt;' /></a></div><br />");
    */

    $offset = ($page * $perpage) - $perpage;
    $LIMIT  =  ($count > 0 ? "LIMIT $offset,$perpage" : '');

    return array($menu, $LIMIT);
   } //-- End Pager Function --//

?>