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

//-- Geshi Highlighter By putyn --//
function source_highlighter($code)
{
    require_once(FUNC_DIR . 'function_geshi.php');

    global $lang;

    $source = str_replace(array("&#039;",
                                "&gt;",
                                "&lt;",
                                "&quot;",
                                "&amp;"), array("'",
                                                ">",
                                                "<",
                                                "\"",
                                                "&"), $code[1]);

    if (false !== stristr($code[0], "[php]"))
    {
        $lang2geshi = "php";
    }

    elseif (false !== stristr($code[0], "[sql]"))
    {
        $lang2geshi = "sql";
    }

    elseif (false !== stristr($code[0], "[html]"))
    {
        $lang2geshi = "html4strict";
    }
    else
    {
        $lang2geshi = "txt";
    }

    $geshi = new GeSHi($source, $lang2geshi);
    $geshi->set_header_type(GESHI_HEADER_PRE_VALID);
    $geshi->set_overall_style('font : normal normal 100% monospace; color : #000066;', false);
    $geshi->set_line_style('color: #003030;', 'font-weight : bold; color: #006060;', true);
    $geshi->set_code_style('color: #000020; font-family : monospace; font-size : 12px; line-height : 6px;', true);
    $geshi->enable_classes(false);
    $geshi->set_link_styles(GESHI_LINK, 'color : #000060;');
    $geshi->set_link_styles(GESHI_HOVER, 'background-color : #f0f000;');
    $return = "<div class='codetop'>{$lang['table_code']}</div><div class='codemain'>\n";
    $return .= $geshi->parse_code();
    $return .= "\n</div>\n";
    return $return;
}

$smilies = array(":)"                       => "happy.png",
                 ":("                       => "sad.png",
                 ":P"                       => "tongue.png",
                 ":wink:"                   => "wink.png",
                 ":x"                       => "angry.png",
                 ":|"                       => "expressionless.png",
                 ":D"                       => "laugh.png",
                 ":S"                       => "puzzled.png",
                 "8-)"                      => "cool.png",
                 ":O"                       => "surprised.png",
                 ":asleep:"                 => "asleep.png",
                 ":bashful:"                => "bashful.png",
                 ":bashfulcute:"            => "bashfulcute.png",
                 ":bigevilgrin:"            => "bigevilgrin.png",
                 ":bigsmile:"               => "bigsmile.png",
                 ":bigwink:"                => "bigwink.png",
                 ":chuckle:"                => "chuckle.png",
                 ":crying:"                 => "crying.png",
                 ":confused:"               => "confused.png",
                 ":confusedsad:"            => "confusedsad.png",
                 ":dead:"                   => "dead.png",
                 ":delicious:"              => "delicious.png",
                 ":depressed:"              => "depressed.png",
                 ":evil:"                   => "evil.png",
                 ":evilgrin:"               => "evilgrin.png",
                 ":grin:"                   => "grin.png",
                 ":impatient:"              => "impatient.png",
                 ":inlove:"                 => "inlove.png",
                 ":kiss:"                   => "kiss.png",
                 ":mad:"                    => "mad.png",
                 ":nerdy:"                  => "nerdy.png",
                 ":notfunny:"               => "notfunny.png",
                 ":ohrly:"                  => "ohrly.png",
                 ":reallyevil:"             => "reallyevil.png",
                 ":sarcasm:"                => "sarcasm.png",
                 ":shocked:"                => "shocked.png",
                 ":sick:"                   => "sick.png",
                 ":silly:"                  => "silly.png",
                 ":sing:"                   => "sing.png",
                 ":smitten:"                => "smitten.png",
                 ":smug:"                   => "smug.png",
                 ":stress:"                 => "stress.png",
                 ":sunglasses:"             => "sunglasses.png",
                 ":sunglasses2:"            => "sunglasses2.png",
                 ":superbashfulcute:"       => "superbashfulcute.png",
                 ":tired:"                  => "tired.png",
                 ":whistle:"                => "whistle.png",
                 ":winktongue:"             => "winktongue.png",
                 ":yawn:"                   => "yawn.png",
                 ":zipped:"                 => "zipped.png",);

/*$privatesmilies = array(
    ":)"            => "happy.png",
    ":wink:"        => "wink.gif",
    ":D"            => "grin.gif",
    ":P"            => "tongue.gif",
    ":("            => "sad.gif",
    ":'("           => "cry.gif",
    ":|"            => "noexpression.gif",
    ":Boozer:"      => "alcoholic.gif",
    ":deadhorse:"   => "deadhorse.gif",
    ":spank:"       => "spank.gif",
    ":yoji:"        => "yoji.gif",
    ":locked:"      => "locked.gif",
    ":grrr:"        => "angry.gif",     // legacy
    "O:-"           => "innocent.gif",  // legacy
    ":sleeping:"    => "sleeping.gif",  // legacy
    "-_-"           => "unsure.gif",        // legacy
    ":clown:"       => "clown.gif",
    ":mml:"         => "mml.gif",
    ":rtf:"         => "rtf.gif",
    ":morepics:"    => "morepics.gif",
    ":rb:"          => "rb.gif",
    ":rblocked:"    => "rblocked.gif",
    ":maxlocked:"   => "maxlocked.gif",
    ":hslocked:"    => "hslocked.gif",
);
*/

//-- Uncomment To Use the scale Function Instead Of Lightbox --//
/*
function scale($src)
{
    $max = 350;

    if (!isset($max, $src))
        return;

    $src      = str_replace("", "%20", $src[1]);
    $info     = @getimagesize($src);
    $sw       = $info[0];
    $sh       = $info[1];
    $addclass = false;
    $max_em   = 0.06 * $max;

    if ($max < max($sw, $sh))
    {
        if ($sw > $sh)

            $new = array($max_em . "em", "auto");


        if ($sw < $sh)

            $new      = array("auto", $max_em."em");
            $addclass = true;

    }
    else

        $new = array("auto", "auto");
        $id  = mt_rand(0000, 9999);


    if ($new[0] == "auto" && $new[1] == "auto")

        $img = "<img src=\"{$src}\" border=\"0\" alt=\"\" />";

    else

        $img = "<a href=\"{$src}\" onclick=\"return false;\"><img id=\"r{$id}\" border=\"0\" alt=\"\" src=\"{$src}\" " . ($addclass ? "class=\"resized\"" : "") . " style=\"width:{$new[0]};height:{$new[1]};\" /></a>";

        return $img;
}
*/

//-- Set This To The Line Break Character Sequence Of Your System --//
$linebreak = "\r\n";

//-- Comment Out If You Want To Use redirect.am OR anonym.to OR nullrefer.com --//
function format_urls($s)
{
    return preg_replace("/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i","\\1<a target='_blank' href='redir.php?url=\\2'>\\2</a>", $s);

}

//-- Comment Out If You Want To Use redir.php OR redirect.am OR nullrefer.com --//
/*function format_urls($s)
{
    return preg_replace("/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i","\\1<a target='_blank' href='http://www.anonym.to/?\\2'>\\2</a>", $s);
}*/

//-- Comment Out If You Want To Use anonym.to OR redir.php OR nullrefer.com --//
/*function format_urls($s)
{
    return preg_replace("/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i","\\1<a target='_blank' href='http://www.redirect.am/?\\2'>\\2</a>", $s);
}*/

//-- Comment Out If You Want To Use anonym.to OR redirect.am OR redir.php --//
/*function format_urls($s)
{
    return preg_replace("/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i","\\1<a target='_blank' href='http://www.nullrefer.com/?\\2'>\\2</a>", $s);
}*/

function format_quotes($s)
{
    global $lang;

    $old_s = '';

    while ($old_s != $s)
    {
        $old_s = $s;

        //-- Find First Occurrence Of [/quote]
        $close = utf8::strpos($s, "[/quote]");

        if ($close === false)
        {
            return $s;
        }

        //-- Find Last [quote] Before First [/quote] --//
        //-- Note That There Is No Check For Correct Syntax --//
		$open = bt_utf8::strripos(utf8::substr($s, 0, $close), "[quote");

        if ($open === false)
        {
            return $s;
        }

        $quote = utf8::substr($s, $open, $close - $open + 8);

        //-- [quote]Text[/quote] --//
        $quote = preg_replace("/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i", "<span class='sub'><strong>{$lang['table_quote']}:</strong></span><table class='main' border='1' cellspacing='0' cellpadding='10'><tr><td style='border : 1px black dotted'>\\1</td></tr></table><br />", $quote);

        //-- [quote=Author]Text[/quote] --//
        $quote = preg_replace("/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i", "<span class='sub'><strong>\\1{$lang['table_wrote']}:</strong></span><table class='main' border='1' cellspacing='0' cellpadding='10'><tr><td style='border : 1px black dotted'>\\2</td></tr></table><br />", $quote);

        $s = utf8::substr($s, 0, $open) . $quote . utf8::substr($s, $close + 8);
    }
    return $s;
}

function format_comment($text, $strip_html = true)
{
    global $smilies, $image_dir, $lang;

    $s = $text;

    unset($text);
    /*
        This Fixes The Extraneous ;) Smilies Problem. When There Was An Html Escaped
        Char Before A Closing Bracket - Like >), "), ... - This Would Be Encoded
        To &xxx;), Hence All The Extra Smilies. I Created A New :wink: Label, Removed
        The ;) One, And Replace All Genuine ;) By :wink: Before Escaping The Body.
        (what Took Us So Long? :blush:)- wyz
    */

    $s = str_replace(";)", ":wink:", $s);

    if ($strip_html)
    {
        $s = htmlentities($s, ENT_QUOTES, 'UTF-8');
    }

    if (preg_match("#function\s*\((.*?)\|\|#is", $s))
    {
        $s = str_replace(":", "&#58;", $s);
        $s = str_replace("[", "&#91;", $s);
        $s = str_replace("]", "&#93;", $s);
        $s = str_replace(")", "&#41;", $s);
        $s = str_replace("(", "&#40;", $s);
        $s = str_replace("{", "&#123;", $s);
        $s = str_replace("}", "&#125;", $s);
        $s = str_replace("$", "&#36;", $s);
    }

    //-- [*] --//
    if (utf8::stripos($s, '[*]') !== false)
    {
        $s = preg_replace("/\[\*\]/", "<img class='listitem' src='{$image_dir}list.gif' alt='{$lang['img_alt_list']}' title='{$lang['img_alt_list']}' />", $s);
    }

    //-- [b]Bold[/b] --//
    if (utf8::stripos($s, '[b]') !== false)
    {
        $s = preg_replace('/\[b\](.+?)\[\/b\]/is', "<span style='font-weight : bold;'>\\1</span>", $s);
    }

    //-- [i]Italic[/i] --//
    if (utf8::stripos($s, '[i]') !== false)
    {
        $s = preg_replace('/\[i\](.+?)\[\/i\]/is', "<span style='font-style : italic;'>\\1</span>", $s);
    }

    //-- [u]Underline[/u] --//
    if (utf8::stripos($s, '[u]') !== false)
    {
        $s = preg_replace('/\[u\](.+?)\[\/u\]/is', "<span style='text-decoration : underline;'>\\1</span>", $s);
    }

    //-- [color=blue]Text[/color] --//
    if (utf8::stripos($s, '[color=') !== false)
    {
        $s = preg_replace('/\[color=([a-zA-Z]+)\](.+?)\[\/color\]/is', '<span style="color : \\1">\\2</span>', $s);

        //-- [color=#ffcc99]Text[/color] --//
        $s = preg_replace('/\[color=(#[a-f0-9]{6})\](.+?)\[\/color\]/is', '<span style="color : \\1">\\2</span>', $s);
    }

    //-- Media Tag --//
    if (utf8::stripos($s, '[media=') !== false)
    {
        $s = preg_replace("#\[media=(youtube|liveleak|GameTrailers|imdb)\](.+?)\[/media\]#ies", "_MediaTag('\\2','\\1')", $s);
        $s = preg_replace("#\[media=(youtube|liveleak|GameTrailers|vimeo)\](.+?)\[/media\]#ies", "_MediaTag('\\2','\\1')", $s);
    }

    //-- Uncomment To Use The Scale Function Instead Of Lightbox --//
    //-- Img Using Image-Resize And Function Scale  --//
    //-- [img=http://www/image.gif]  --//
    /*
        if (utf8::stripos($s, '[img') !== false) {
        $s = preg_replace_callback("/\[img\](http:\/\/[^\s'\"<>]+(\.(jpg|gif|png)))\[\/img\]/i", "scale", $s);

        // [img=http://www/image.gif]
        $s = preg_replace_callback("/\[img=(http:\/\/[^\s'\"<>]+(\.(gif|jpg|png)))alt=\"\"\]/i", "scale", $s);
        }
    */

    //-- Img Using Lightbox --//
    //-- [img=http://www/image.gif] --//
    if (utf8::stripos($s, '[img') !== false)
    {
        $s = preg_replace("/\[img\]((http|https):\/\/[^\s'\"<>]+(\.(jpg|gif|png|bmp|jpeg)))\[\/img\]/i", "<a href=\"\\1\" rel=\"lightbox\"><img src=\"\\1\" alt=\"\" class=\"image-resize\" /></a>", $s);

        $s = preg_replace("/\[img=((http|https):\/\/[^\s'\"<>]+(\.(gif|jpg|png|bmp|jpeg)))\]/i", "<a href=\"\\1\" rel=\"lightbox\"><img src=\"\\1\" alt=\"\"  class=\"image-resize\" /></a>", $s);
    }

    //-- [size=4]Text[/size] --//
    if (utf8::stripos($s, '[size=') !== false)
    {
        $s = preg_replace('/\[size=([1-7])\](.+?)\[\/size\]/is', '<span class="font_size_\1">\2</span>', $s);
    }

    //-- [font=Arial]Text[/font] --//
    if (utf8::stripos($s, '[font=') !== false)
    {
        $s = preg_replace('/\[font=([a-zA-Z ,]+)\](.+?)\[\/font\]/is', '<span style="font-family : \\1">\\2</span>', $s);
    }

    //-- [s]Stroke[/s] --//
    if (utf8::stripos($s, '[s]') !== false)
    {
        $s = preg_replace("/\[s\](.+?)\[\/s\]/is", "<s>\\1</s>", $s);
    }

     //-- Dynamic Vars --//

    //-- [Spoiler]TEXT[/Spoiler] --//
    if (utf8::stripos($s, '[spoiler]') !== false)
    {
        $s = preg_replace("/\[spoiler\](.+?)\[\/spoiler\]/is", "<div class=\"smallfont\" align=\"left\"><input type=\"button\" class=\"btn\" value=\"{$lang['btn_show']}\" style=\"width : 75px; font-size : 10px; margin : 0px; padding : 0px;\" onclick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') {this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = '';this.innerText = ''; this.value = '{$lang['btn_hide']}'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerText = ''; this.value = '{$lang['btn_show']}'; }\" /><div style=\"margin : 10px; padding : 10px; border : 1px inset;\" align=\"left\"><div style=\"display: none;\">\\1</div></div></div>", $s);
    }

    //-- [mcom]Text[/mcom] --//
    if (utf8::stripos($s, '[mcom]') !== false)
    {
        $s = preg_replace("/\[mcom\](.+?)\[\/mcom\]/is", "<div style=\"font-size : 18pt; line-height : 50%;\"><div style=\"border-color : red; background-color : red; color : white; text-align : center; font-weight : bold; font-size : large;\"><strong>\\1</strong></div></div>", $s);
    }

    //-- The [you] Tag --//
    if (utf8::stripos($s, '[you]') !== false)
    {
        $s = preg_replace("/\[you\]/i", user::$current['username'], $s);
    }

    //-- [php]PHP Code[/php] --//
    if (utf8::stripos($s, '[php]') !== false)
    {
        $s = preg_replace_callback("/\[php\](.+?)\[\/php\]/ims", "source_highlighter", $s);
    }

    //-- [sql]SQL Code[/sql] --//
    if (utf8::stripos($s, '[sql]') !== false)
    {
        $s = preg_replace_callback("/\[sql\](.+?)\[\/sql\]/ims", "source_highlighter", $s);
    }

    //-- [html]HTML Code[/html] --//
    if (utf8::stripos($s, '[html]') !== false)
    {
        $s = preg_replace_callback("/\[html\](.+?)\[\/html\]/ims", "source_highlighter", $s);
    }

    //-- [mail]Mail[/mail] --//
    if (utf8::stripos($s, '[mail]') !== false)
    {
        $s = preg_replace("/\[mail\](.+?)\[\/mail\]/is", "<a href=\"mailto:\\1\" target=\"_blank\">\\1</a>", $s);
    }

    //--[Align=(center|left|right|justify)]Text[/align] --//
    if (utf8::stripos($s, '[align=') !== false)
    {
        $s = preg_replace("/\[align=([a-zA-Z]+)\](.+?)\[\/align\]/is", "<div style=\"text-align : \\1\">\\2</div>", $s);
    }

    //-- Quotes --//
    $s = format_quotes($s);

    //-- URLs --//
    $s = format_urls($s);

    if (utf8::stripos($s, '[url') !== false)
    {
        //-- [url=http://www.example.com]Text[/url] --//
        $s = preg_replace("/\[url=([^()<>\s]+?)\]((\s|.)+?)\[\/url\]/i","<a target=_blank href=redir.php?url=\\1>\\2</a>", $s);


        //-- [url]http://www.example.com[/url] --//
        $s = preg_replace("/\[url\]([^()<>\s]+?)\[\/url\]/i","<a target=_blank href=redir.php?url=\\1>\\1</a>", $s);
    }

    //-- Linebreaks --//
    $s = nl2br($s);

    //-- [pre]Preformatted[/pre] --//
    if (utf8::stripos($s, '[pre]') !== false)
    {
        $s = preg_replace("/\[pre\](.+?)\[\/pre\]/is", "<tt><span style=\"white-space : nowrap;\">\\1</span></tt>", $s);
    }

    //-- [nfo]NFO-preformatted[/nfo] --//
    if (utf8::stripos($s, '[nfo]') !== false)
    {
        $s = preg_replace("/\[nfo\](.+?)\[\/nfo\]/i", "<tt><span style=\"white-space : nowrap;\"><font face='MS Linedraw' size='2' style='font-size : 10pt; line-height : " . "10pt'>\\1</font></span></tt>", $s);
    }

    //-- Maintain Spacing --//
	$s = str_replace(utf8::NBSP, ' ', $s);
	$s = str_replace('  ', ' ' . utf8::NBSP, $s);

    reset($smilies);
    while (list($code, $url) = each($smilies))
    {
        $s = str_replace($code, "<img src='{$image_dir}smilies/{$url}' width='16' height='16' border='0' alt='" . security::html_safe($code) . "' title='" . security::html_safe($code) . "' />", $s);
    }

    /*
        reset($privatesmilies);
        while (list($code, $url) = each($privatesmilies))
        $s = str_replace($code, "<img src='{$image_dir}smilies/{$url}' width='16' height='16' border='0' alt='' title='' />", $s);
    */
    return $s;
}

function _MediaTag($content, $type)
{
    if ($content == '' or $type == '')
    {
        return;
    }

    $return = '';

    switch ($type)
    {
        case 'youtube':
            $return = preg_replace("#^http://(?:|www\.)youtube\.com/watch\?v=([\-_a-zA-Z0-9]+)+?$#i", "<object type='application/x-shockwave-flash' height='355' width='425' data='http://www.youtube.com/v/\\1'><param name='movie' value='http://www.youtube.com/v/\\1' /><param name='allowScriptAccess' value='sameDomain' /><param name='quality' value='best' /><param name='bgcolor' value='#FFFFFF' /><param name='scale' value='noScale' /><param name='salign' value='TL' /><param name='FlashVars' value='playerMode=embedded' /><param name='wmode' value='transparent' /></object>", $content);
            break;

        case 'liveleak':
            $return = preg_replace("#^http://(?:|www\.)liveleak\.com/view\?i=([_a-zA-Z0-9]+)+?$#i", "<object type='application/x-shockwave-flash' height='355' width='425' data='http://www.liveleak.com/e/\\1'><param name='movie' value='http://www.liveleak.com/e/\\1' /><param name='allowScriptAccess' value='sameDomain' /><param name='quality' value='best' /><param name='bgcolor' value='#FFFFFF' /><param name='scale' value='noScale' /><param name='salign' value='TL' /><param name='FlashVars' value='playerMode=embedded' /><param name='wmode' value='transparent' /></object>", $content);
            break;

        case 'GameTrailers':
            $return = preg_replace("#^http://(?:|www\.)gametrailers\.com/video/([\-_a-zA-Z0-9]+)+?/([0-9]+)+?$#i", "<object type='application/x-shockwave-flash' height='355' width='425' data='http://www.gametrailers.com/remote_wrap.php?mid=\\2'><param name='movie' value='http://www.gametrailers.com/remote_wrap.php?mid=\\2' /><param name='allowScriptAccess' value='sameDomain' /> <param name='allowFullScreen' value='true' /><param name='quality' value='high' /></object>", $content);
            break;

        case 'imdb':
            $return = preg_replace("#^http://(?:|www\.)imdb\.com/video/screenplay/([_a-zA-Z0-9]+)+?$#i", "<div class='\\1'><div style=\"padding : 3px; background-color : transparent; border : none; width : 690px;\"><div style=\"text-transform :  uppercase; border-bottom : 1px solid #CCCCCC; margin-bottom : 3px; font-size : 0.8em; font-weight : bold; display : block;\"><span onclick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerHTML = '<strong>{$lang['table_imdb']}: </strong><a href=\'#\' onclick=\'return false;\'>hide</a>'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerHTML = '<b>{$lang['table_imdb']}: </b><a href=\'#\' onclick=\'return false;\'>show</a>'; }\" ><b>{$lang['table_imdb']}: </b><a href=\"#\" onclick=\"return false;\">show</a></span></div><div class=\"quotecontent\"><div style=\"display : none;\"><iframe style='vertical-align : middle;' src='http://www.imdb.com/video/screenplay/\\1/player' scrolling='no' width='660' height='490' frameborder='0'></iframe></div></div></div></div>", $content);
            break;

        case 'vimeo':
            $return = preg_replace("#^http://(?:|www\.)vimeo\.com/([0-9]+)+?$#i", "<object type='application/x-shockwave-flash' width='425' height='355' data='http://vimeo.com/moogaloop.swf?clip_id=\\1&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1'>
            <param name='allowFullScreen' value='true' />
            <param name='allowScriptAccess' value='sameDomain' />
            <param name='movie' value='http://vimeo.com/moogaloop.swf?clip_id=\\1&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1' />
            <param name='quality' value='high' />
            </object>", $content);
            break;

        default:

            $return = "{$lang['table_not_found']}";
    }

    return $return;
}

//-- Credits To putyn --//
function textbbcode($form, $text, $content = "")
{
    global $image_dir, $lang;

    $custombutton = '';

    $bbcodebody = <<<HTML
    <script type="text/javascript">
        var textBBcode = "{$text}";
    </script>

    <script type="text/javascript" src="./js/textbbcode.js"></script>

    <div id="hover_pick" style="width : 25px; height : 25px; position : absolute; border : 1px solid #333333; display : none; z-index : 20;"></div>

    <div id="pickerholder"></div>

    <table border="1" align="center" cellpadding="5" cellspacing="0">
        <tr>
            <td width="100%" style="padding : 0" colspan="2">
                <div style="float : left; padding : 4px 0px 0px 2px;">
                    <img src="{$image_dir}bbcode/bold.png" width="16" height="16" border="0" alt="{$lang['img_alt_bold']}" title="{$lang['img_alt_bold']}" onclick="tag('b')" />
                    <img src="{$image_dir}bbcode/italic.png" width="16" height="16" border="0" alt="{$lang['img_alt_italic']}" title="{$lang['img_alt_italic']}" onclick="tag('i')" />
                    <img src="{$image_dir}bbcode/underline.png" width="16" height="16" border="0" alt="{$lang['img_alt_underline']}" title="{$lang['img_alt_underline']}" onclick="tag('u')" />
                    <img src="{$image_dir}bbcode/strike.png" width="16" height="16" border="0" alt="{$lang['img_alt_strike']}" title="{$lang['img_alt_strike']}" onclick="tag('s')" />
                    <img src="{$image_dir}bbcode/link.png" width="16" height="16" border="0" alt="{$lang['img_alt_link']}" title="{$lang['img_alt_link']}" onclick="clink()" />
                    <img src="{$image_dir}bbcode/picture.png" width="16" height="16" border="0" alt="{$lang['img_alt_image']}" title="{$lang['img_alt_image']}" onclick="cimage()" />
                    <img src="{$image_dir}bbcode/email.png" width="16" height="16" border="0" alt="{$lang['img_alt_email']}" title="{$lang['img_alt_email']}" onclick="mail()" />
HTML;

    if (user::$current['class'] >= UC_MODERATOR)
    {
        $bbcodebody .= <<<HTML
    <img src="{$image_dir}bbcode/php.png" width="16" height="16" border="0" alt="{$lang['img_alt_php']}" title="{$lang['img_alt_php']}" onclick="tag('php')" />
    <img src="{$image_dir}bbcode/sql.png" width="16" height="16" border="0" alt="{$lang['img_alt_sql']}" title="{$lang['img_alt_sql']}" onclick="tag('sql')" />
    <img src="{$image_dir}bbcode/script.png" width="16" height="16" border="0" alt="{$lang['img_alt_html']}" title="{$lang['img_alt_html']}" onclick="tag('html')" />
    <img src="{$image_dir}bbcode/modcom.png" width="16" height="16" border="0" alt="{$lang['img_alt_modcom']}" title="{$lang['img_alt_modcom']}" onclick="tag('mcom')" />
HTML;
    }

    $bbcodebody .= <<<HTML
                </div>
                <div style="float : right; padding : 4px 2px 0px 0px;"> <img src="{$image_dir}bbcode/align_left.png" width="16" height="16" border="0" alt="{$lang['img_alt_align_left']}" title="{$lang['img_alt_align_left']}" onclick="wrap('align', '', 'Left')" /> <img src="{$image_dir}bbcode/align_center.png" width="16" height="16" border="0" alt="{$lang['img_alt_align_center']}" title="{$lang['img_alt_align_center']}" onclick="wrap('align', '', 'center')" /> <img src="{$image_dir}bbcode/align_justify.png" width="16" height="16" border="0" alt="{$lang['img_alt_align_justify']}" title="{$lang['img_alt_align_justify']}" onclick="wrap('align', '', 'justify')" /> <img src="{$image_dir}bbcode/align_right.png" width="16" height="16" border="0" alt="{$lang['img_alt_align_right']}" title="{$lang['img_alt_align_right']}" onclick="wrap('align', '' ,'right')" /> </div>
            </td>
        </tr>
        <tr>
            <td width="100%" style="padding : 0;" colspan="2">
                <div style="float : left; padding : 4px 0px 0px 2px;">
                    <select name="fontfont" id="fontfont" onchange="font('font',this.value);">
                        <option value="0">{$lang['form_opt_select_font']}</option>
                        <option value="Arial" style="font-family : Arial;">{$lang['form_opt_arial']}</option>
                        <option value="Arial Black" style="font-family : Arial Black;">{$lang['form_opt_arial_blk']}</option>
                        <option value="Comic Sans MS" style="font-family : Comic Sans MS;">{$lang['form_opt_comic']}</option>
                        <option value="Courier New" style="font-family : Courier New;">{$lang['form_opt_courier']}</option>
                        <option value="Franklin Gothic Medium" style="font-family : Franklin Gothic Medium;">{$lang['form_opt_franklin']}</option>
                        <option value="Georgia" style="font-family : Georgia;">{$lang['form_opt_georgia']}</option>
                        <option value="Helvetica" style="font-family : Helvetica;">{$lang['form_opt_helvetica']}</option>
                        <option value="Impact" style="font-family : Impact;">{$lang['form_opt_impact']}</option>
                        <option value="Lucida Console" style="font-family : Lucida Console;">{$lang['form_opt_lucida_con']}</option>
                        <option value="Lucida Sans Unicode" style="font-family : Lucida Sans Unicode;">{$lang['form_opt_lucida_san']}</option>
                        <option value="Microsoft Sans Serif" style="font-family : Microsoft Sans Serif;">{$lang['form_opt_microsoft']}</option>
                        <option value="Palatino Linotype" style="font-family : Palatino Linotype;">{$lang['form_opt_palatino']}</option>
                        <option value="Tahoma" style="font-family : Tahoma;">{$lang['form_opt_tahoma']}</option>
                        <option value="Times New Roman" style="font-family : Times New Roman;">{$lang['form_opt_times']}</option>
                        <option value="Trebuchet MS" style="font-family : Trebuchet MS;">{$lang['form_opt_trebuchet']}</option>
                        <option value="Verdana" style="font-family : Verdana;">{$lang['form_opt_verdana']}</option>
                        <option value="Symbol" style="font-family : Symbol;">{$lang['form_opt_symbol']}</option>
                    </select>
                    <select name="fontsize" id="fontsize" style="padding-bottom : 3px;" onchange="font('size',this.value);">
                        <option value="0">{$lang['form_opt_select_size']}</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                    </select>
                    <select name="fontcolor" id="fontcolor" style="padding-bottom : 3px;" onchange="font('color',this.value);">
                        <option value="0">{$lang['form_opt_select_color']}</option>
                        <option value="FF0000" style="color:#FF0000">{$lang['form_opt_red']}</option>
                        <option value="00FFFF" style="color:#00FFFF">{$lang['form_opt_turquoise']}</option>
                        <option value="0000FF" style="color:#0000FF">{$lang['form_opt_lightblue']}</option>
                        <option value="0000A0" style="color:#0000A0">{$lang['form_opt_darkblue']}</option>
                        <option value="FF0080" style="color:#FF0080">{$lang['form_opt_lightpurple']}</option>
                        <option value="800080" style="color:#800080">{$lang['form_opt_darkpurple']}</option>
                        <option value="FFFF00" style="color:#FFFF00">{$lang['form_opt_yellow']}</option>
                        <option value="00FF00" style="color:#00FF00">{$lang['form_opt_green']}</option>
                        <option value="C0C0C0" style="color:#C0C0C0">{$lang['form_opt_grey']}</option>
                        <option value="FF8040" style="color:#FF8040">{$lang['form_opt_orange']}</option>
                        <option value="808000" style="color:#808000">{$lang['form_opt_forest_green']}</option>
                    </select>
                </div>
                <div style="float : right; padding : 4px 2px 0px 0px;"><img src="{$image_dir}bbcode/text_uppercase.png" width="16" height="16" border="0" alt="{$lang['img_alt_uppercase']}" title="{$lang['img_alt_uppercase']}" onclick="text('up')" /> <img src="{$image_dir}bbcode/text_lowercase.png" width="16" height="16" border="0" alt="{$lang['img_alt_lowercase']}" title="{$lang['img_alt_lowercase']}" onclick="text('low')" /> <img src="{$image_dir}bbcode/zoom_in.png" width="16" height="16" border="0" alt="{$lang['img_alt_inc_font']}" title="{$lang['img_alt_inc_font']}" onclick="fonts('up')" /> <img src="{$image_dir}bbcode/zoom_out.png" width="16" height="16" border="0" alt="{$lang['img_alt_dec_font']}" title="{$lang['img_alt_dec_font']}" onclick="fonts('down')" /></div>
            </td>
        </tr>
        <tr>
            <td><textarea id="{$text}" name="{$text}" rows="2" cols="2" style="width:450px; height : 250px; font-size : 12px;">{$content}</textarea></td>
            <td align="center" valign="top">
                <table class="em_holder" border="1" width="0" cellpadding="2" cellspacing="2">
                    <tr>
                        <td align="center"><a href="javascript:em(':)');"><img src="{$image_dir}smilies/happy.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':(');"><img src="{$image_dir}smilies/sad.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':P');"><img src="{$image_dir}smilies/tongue.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(';)');"><img src="{$image_dir}smilies/wink.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                    <tr>
                        <td align="center"><a href="javascript:em(':x');"><img src="{$image_dir}smilies/angry.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':|');"><img src="{$image_dir}smilies/expressionless.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':D');"><img src="{$image_dir}smilies/laugh.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':S');"><img src="{$image_dir}smilies/puzzled.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                    <tr>
                        <td align="center"><a href="javascript:em('8-)');"><img src="{$image_dir}smilies/cool.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':O');"><img src="{$image_dir}smilies/surprised.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':asleep:');"><img src="{$image_dir}smilies/asleep.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':bashful:');"><img src="{$image_dir}smilies/bashful.png" width="16" height="16"border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                    <tr>
                        <td align="center"><a href="javascript:em(':bashfulcute:');"><img src="{$image_dir}smilies/bashfulcute.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':bigevilgrin:');"><img src="{$image_dir}smilies/bigevilgrin.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':bigsmile:');"><img src="{$image_dir}smilies/bigsmile.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':bigwink:');"><img src="{$image_dir}smilies/bigwink.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                    <tr>
                        <td align="center"><a href="javascript:em(':chuckle:');" ><img src="{$image_dir}smilies/chuckle.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':crying:');" ><img src="{$image_dir}smilies/crying.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':confused:');"><img src="{$image_dir}smilies/confused.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':confusedsad:');" ><img src="{$image_dir}smilies/confusedsad.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                    <tr>
                        <td align="center"><a href="javascript:em(':dead:');" ><img src="{$image_dir}smilies/dead.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':delicious:');" ><img src="{$image_dir}smilies/delicious.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':depressed:');" ><img src="{$image_dir}smilies/depressed.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':evil:');" ><img src="{$image_dir}smilies/evil.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                    <tr>
                        <td align="center"><a href="javascript:em(':grin:');" ><img src="{$image_dir}smilies/grin.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':impatient:');" ><img src="{$image_dir}smilies/impatient.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':inlove:');" ><img src="{$image_dir}smilies/inlove.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':kiss:');" ><img src="{$image_dir}smilies/kiss.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                    <tr>
                        <td align="center"><a href="javascript:em(':mad:');" ><img src="{$image_dir}smilies/mad.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':nerdy:');" ><img src="{$image_dir}smilies/nerdy.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':notfunny:');" ><img src="{$image_dir}smilies/notfunny.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':ohrly:');" ><img src="{$image_dir}smilies/ohrly.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                        <tr>
                        <td align="center"><a href="javascript:em(':reallyevil:');" ><img src="{$image_dir}smilies/reallyevil.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':sarcasm:');" ><img src="{$image_dir}smilies/sarcasm.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':shocked:');" ><img src="{$image_dir}smilies/shocked.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':sick:');" ><img src="{$image_dir}smilies/sick.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                        <tr>
                        <td align="center"><a href="javascript:em(':silly:');" ><img src="{$image_dir}smilies/silly.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':sing:');" ><img src="{$image_dir}smilies/sing.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':smitten:');" ><img src="{$image_dir}smilies/smitten.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':smug:');" ><img src="{$image_dir}smilies/smug.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                        <tr>
                        <td align="center"><a href="javascript:em(':stress:');" ><img src="{$image_dir}smilies/stress.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':sunglasses:');" ><img src="{$image_dir}smilies/sunglasses.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':sunglasses2:');" ><img src="{$image_dir}smilies/sunglasses2.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':superbashfulcute:');" ><img src="{$image_dir}smilies/superbashfulcute.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                        <tr>
                        <td align="center"><a href="javascript:em(':tired:');" ><img src="{$image_dir}smilies/tired.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':whistle:');" ><img src="{$image_dir}smilies/whistle.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':winktongue:');" ><img src="{$image_dir}smilies/winktongue.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                        <td align="center"><a href="javascript:em(':yawn:');" ><img src="{$image_dir}smilies/yawn.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                        <tr>
                        <td align="center"><a href="javascript:em(':zipped:');" ><img src="{$image_dir}smilies/zipped.png" width="16" height="16" border="0" alt="{$lang['img_alt_smilies']}" title="" /></a></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
HTML;
    return $bbcodebody;
}

?>