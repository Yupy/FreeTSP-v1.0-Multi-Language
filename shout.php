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

//-- FreeTSP shout.php Spook --//

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'function_main.php');
require_once(FUNC_DIR . 'function_user.php');
require_once(FUNC_DIR . 'function_vfunctions.php');

db_connect();
logged_in();

?>

<iframe src='shoutbox.php' width='100%' height='200' frameborder='1' name='sbox' marginwidth='0' marginheight='0'></iframe>

<br /><br />

<form name='shbox' method='get' action='shoutbox.php' target='sbox' onsubmit='mySubmit()'>
    <center>
        <table width='600' cellspacing='0' cellpadding='1'>
            <tr>
                <td align='center' colspan='1'>
                    <table cellspacing='1' cellpadding='1'>
                        <tr>
                            <td class='embedded'>
                                <input type='button' class='codebuttons' name='b' value='B' title='<?php echo $lang['form_bold']?>' style='font-weight : bold; font-size : 9px;' onclick='javascript: simpletag("b")' />
                            </td>
                            <td class='embedded'>
                                <input type='button' class='codebuttons' name='i' value='I' title='<?php echo $lang['form_italic']?>' style='font-style: italic; font-size : 9px;' onclick='javascript: simpletag("i")' />
                            </td>
                            <td class='embedded'>
                                <input type='button' class='codebuttons' name='u' value='U' title='<?php echo $lang['form_underline']?>' style='text-decoration : underline; font-size : 9px;' onclick='javascript: simpletag("u")' />
                            </td>
                            <td class='embedded'>
                                <input type='button' class='codebuttons' name='url' value='URL' title='<?php echo $lang['form_url']?>' style='font-size : 9px;' onclick='tag_url()' />
                            </td>
                            <!-- <td class='embedded'>
                                <input type='button' class='codebuttons' name='IMG' value='IMG' title='<?php echo $lang['form_image']?>' style='font-size : 9px;' onclick='javascript: tag_image()' />
                            </td> -->
                            <td class='embedded'>
                                <input type='button' class='codebuttons'name='tagcount' value='Close Tags' title='<?php echo $lang['form_tags']?>' style='font-size : 9px;' onclick='javascript: closeall();'  />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table width='600' cellspacing='0' cellpadding='1'>
            <tr>
                <td align='center' colspan='1'>
                    <table cellspacing='1' cellpadding='1'>
                        <tr>
                            <td class='embedded'>
                                <select class='codebuttons' name='color'
                                        onchange='alterfont(this.options[this.selectedIndex].value, "color")'>
                                    <option value='0'>----------<?php echo $lang['form_opt_color']?>----------</option>
                                    <option style='background-color: black' value='Black'><?php echo $lang['form_opt_color1']?></option>
                                    <option style='background-color: sienna' value='Sienna'><?php echo $lang['form_opt_color2']?></option>
                                    <option style='background-color: darkolivegreen' value='DarkOliveGreen'><?php echo $lang['form_opt_color3']?></option>
                                    <option style='background-color: darkgreen' value='DarkGreen'><?php echo $lang['form_opt_color4']?></option>
                                    <option style='background-color: darkslateblue' value='DarkSlateBlue'><?php echo $lang['form_opt_color5']?></option>
                                    <option style='background-color: navy' value='Navy'><?php echo $lang['form_opt_color6']?></option>
                                    <option style='background-color: indigo' value='Indigo'><?php echo $lang['form_opt_color7']?></option>
                                    <option style='background-color: darkslategray' value='DarkSlateGray'><?php echo $lang['form_opt_color8']?></option>
                                    <option style='background-color: darkred' value='DarkRed'><?php echo $lang['form_opt_color9']?></option>
                                    <option style='background-color: darkorange' value='DarkOrange'><?php echo $lang['form_opt_color10']?></option>
                                    <option style='background-color: olive' value='Olive'><?php echo $lang['form_opt_color11']?></option>
                                    <option style='background-color: green' value='Green'><?php echo $lang['form_opt_color12']?></option>
                                    <option style='background-color: teal' value='Teal'><?php echo $lang['form_opt_color13']?></option>
                                    <option style='background-color: blue' value='Blue'><?php echo $lang['form_opt_color14']?></option>
                                    <option style='background-color: slategray' value='SlateGray'><?php echo $lang['form_opt_color15']?></option>
                                    <option style='background-color: dimgray' value='DimGray'><?php echo $lang['form_opt_color16']?></option>
                                    <option style='background-color: red' value='Red'><?php echo $lang['form_opt_color17']?></option>
                                    <option style='background-color: sandybrown' value='SandyBrown'><?php echo $lang['form_opt_color18']?></option>
                                    <option style='background-color: yellowgreen' value='YellowGreen'><?php echo $lang['form_opt_color19']?></option>
                                    <option style='background-color: seagreen' value='SeaGreen'><?php echo $lang['form_opt_color20']?></option>
                                    <option style='background-color: mediumturquoise' value='MediumTurquoise'><?php echo $lang['form_opt_color21']?></option>
                                    <option style='background-color: royalblue' value='RoyalBlue'><?php echo $lang['form_opt_color22']?></option>
                                    <option style='background-color: purple' value='Purple'><?php echo $lang['form_opt_color23']?></option>
                                    <option style='background-color: gray' value='Gray'><?php echo $lang['form_opt_color24']?></option>
                                    <option style='background-color: magenta' value='Magenta'><?php echo $lang['form_opt_color25']?></option>
                                    <option style='background-color: orange' value='Orange'><?php echo $lang['form_opt_color26']?></option>
                                    <option style='background-color: yellow' value='Yellow'><?php echo $lang['form_opt_color27']?></option>
                                    <option style='background-color: lime' value='Lime'><?php echo $lang['form_opt_color28']?></option>
                                    <option style='background-color: cyan' value='Cyan'><?php echo $lang['form_opt_color29']?></option>
                                    <option style='background-color: deepskyblue' value='DeepSkyBlue'><?php echo $lang['form_opt_color30']?></option>
                                    <option style='background-color: darkorchid' value='DarkOrchid'><?php echo $lang['form_opt_color31']?></option>
                                    <option style='background-color: silver' value='Silver'><?php echo $lang['form_opt_color32']?></option>
                                    <option style='background-color: pink' value='Pink'><?php echo $lang['form_opt_color33']?></option>
                                    <option style='background-color: wheat' value='Wheat'><?php echo $lang['form_opt_color34']?></option>
                                    <option style='background-color: lemonchiffon' value='LemonChiffon'><?php echo $lang['form_opt_color35']?></option>
                                    <option style='background-color: palegreen' value='PaleGreen'><?php echo $lang['form_opt_color36']?></option>
                                    <option style='background-color: paleturquoise' value='PaleTurquoise'><?php echo $lang['form_opt_color37']?></option>
                                    <option style='background-color: lightblue' value='LightBlue'><?php echo $lang['form_opt_color38']?></option>
                                    <option style='background-color: plum' value='Plum'><?php echo $lang['form_opt_color39']?></option>
                                    <option style='background-color: white' value='White'><?php echo $lang['form_opt_color40']?></option>
                                </select>

                                <select class='codebuttons' name='font'
                                        onchange='alterfont(this.options[this.selectedIndex].value, "font")'>
                                    <option value='0'>--------------<?php echo $lang['form_opt_font']?>--------------</option>
                                    <option value='Arial'><?php echo $lang['form_opt_font1']?></option>
                                    <option value='Arial Black'><?php echo $lang['form_opt_font2']?></option>
                                    <option value='Arial Narrow'><?php echo $lang['form_opt_font3']?></option>
                                    <option value='Book Antiqua'><?php echo $lang['form_opt_font4']?></option>
                                    <option value='Century Gothic'><?php echo $lang['form_opt_font5']?></option>
                                    <option value='Comic Sans MS'><?php echo $lang['form_opt_font6']?></option>
                                    <option value='Courier New'><?php echo $lang['form_opt_font7']?></option>
                                    <option value='Fixedsys'><?php echo $lang['form_opt_font8']?></option>
                                    <option value='Franklin Gothic Medium'><?php echo $lang['form_opt_font9']?></option>
                                    <option value='Garamond'><?php echo $lang['form_opt_font10']?></option>
                                    <option value='Georgia'><?php echo $lang['form_opt_font11']?></option>
                                    <option value='Impact'><?php echo $lang['form_opt_font12']?></option>
                                    <option value='Lucida Console'><?php echo $lang['form_opt_font13']?></option>
                                    <option value='Lucida Sans Unicode'><?php echo $lang['form_opt_font14']?></option>
                                    <option value='Microsoft Sans Serif'><?php echo $lang['form_opt_font15']?></option>
                                    <option value='Palatino Linotype'><?php echo $lang['form_opt_font16']?></option>
                                    <option value='System'><?php echo $lang['form_opt_font17']?></option>
                                    <option value='Tahoma'><?php echo $lang['form_opt_font18']?></option>
                                    <option value='Times New Roman'><?php echo $lang['form_opt_font19']?></option>
                                    <option value='Trebuchet MS'><?php echo $lang['form_opt_font20']?></option>
                                    <option value='Verdana'><?php echo $lang['form_opt_font21']?></option>
                                </select>

                                <select class='codebuttons' name='size'
                                        onchange='alterfont(this.options[this.selectedIndex].value, "size")'>
                                    <option value='0'>------<?php echo $lang['form_opt_size']?>------</option>
                                    <option value='1'>1</option>
                                    <option value='2'>2</option>
                                    <option value='3'>3</option>
                                    <option value='4'>4</option>
                                    <option value='5'>5</option>
                                    <option value='6'>6</option>
                                    <option value='7'>7</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td align='center'><br /><strong><?php echo $lang['table_msg']?></strong><br />
                    <input type='text' name='shbox_text' size='95' /><br /><br />
                    <input type='submit' class='btn' name='submit' value='<?php echo $lang['btn_shout']?>' style="width : 115" />
                    <input type='hidden' name='sent' value='yes' /><br />
                    <br /><br />
                    <span><a class='btn' href='shoutbox.php' target='sbox'><?php echo $lang['btn_refresh']?></a></span>

                    <?php
                    if (get_user_class() >= UC_MODERATOR)
                    {
                        ?>
                        <a class='btn' href="javascript:popUp('shoutbox_commands.php')"><?php echo $lang['btn_command']?></a><br /><br />
                        <?php
                    }
                    ?>
                    <a href="javascript:SmileIT(':)','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/happy.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_happy']?>' title='<?php echo $lang['img_alt_happy']?>' /></a>

                    <a href="javascript:SmileIT(':(','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/sad.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_sad']?>' title='<?php echo $lang['img_alt_sad']?>' /></a>

                    <a href="javascript:SmileIT(':P','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/tongue.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_tongue']?>' title='<?php echo $lang['img_alt_tongue']?>' /></a>

                    <a href="javascript:SmileIT(':wink:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/wink.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_wink']?>' title='<?php echo $lang['img_alt_wink']?>' /></a>

                    <a href="javascript:SmileIT(':x','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/angry.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_angry']?>' title='<?php echo $lang['img_alt_angry']?>' /></a>

                    <a href="javascript:SmileIT(':confused:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/confused.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_confused']?>' title='<?php echo $lang['img_alt_confused']?>' /></a>

                    <a href="javascript:SmileIT(':whistle:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/whistle.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_whistle']?>' title='<?php echo $lang['img_alt_whistle']?>' /></a>

                    <a href="javascript:SmileIT(':D','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/laugh.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_laugh']?>' title='<?php echo $lang['img_alt_laugh']?>' /></a>

                    <a href="javascript:SmileIT(':S','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/puzzled.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_puzzled']?>' title='<?php echo $lang['img_alt_puzzled']?>' /></a>

                    <a href="javascript:SmileIT('8-)','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/cool.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_cool']?>' title='<?php echo $lang['img_alt_cool']?>' /></a>

                    <a href="javascript:SmileIT(':O','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/surprised.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_surprised']?>' title='<?php echo $lang['img_alt_surprised']?>' /></a>

                    <a href="javascript:SmileIT(':asleep:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/asleep.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_asleep']?>' title='<?php echo $lang['img_alt_asleep']?>' /></a>

                    <a href="javascript:SmileIT(':bashful:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/bashful.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_bashful']?>' title='<?php echo $lang['img_alt_bashful']?>' /></a>

                    <a href="javascript:SmileIT(':reallyevil:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/reallyevil.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_reallyevil']?>' title='<?php echo $lang['img_alt_reallyevil']?>' /></a>

                    <a href="javascript:SmileIT(':inlove:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/inlove.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_inlove']?>' title='<?php echo $lang['img_alt_inlove']?>' /></a>

                    <a href="javascript:SmileIT(':bigwink:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/bigwink.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_bigwink']?>' title='<?php echo $lang['img_alt_bigwink']?>' /></a>

                    <a href="javascript:SmileIT(':crying:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/crying.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_crying']?>' title='<?php echo $lang['img_alt_crying']?>' /></a>

                    <a href="javascript:SmileIT(':confused:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/confused.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_confused']?>' title='<?php echo $lang['img_alt_confused']?>' /></a>

                    <a href="javascript:SmileIT(':zipped:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/zipped.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_zipped']?>' title='<?php echo $lang['img_alt_zipped']?>' /></a>

                    <a href="javascript:SmileIT(':evil:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/evil.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_evil']?>' title='<?php echo $lang['img_alt_evil']?>' /></a>

                    <a href="javascript:SmileIT(':sunglasses:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/sunglasses.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_sunglasses']?>' title='<?php echo $lang['img_alt_sunglasses']?>' /></a>

                    <a href="javascript:SmileIT(':kiss:','shbox','shbox_text')"><img src='<?php echo $image_dir?>smilies/kiss.png' width='16' height='16' border='0' alt='<?php echo $lang['img_alt_kiss']?>' title='<?php echo $lang['img_alt_kiss']?>' /></a>

                    <br /><br />
                </td>
            </tr>
        </table>
    </center>
</form>

<script type='text/javascript'>

    function SmileIT(smile, form, text)
    {
        document.forms[form].elements[text].value = document.forms[form].elements[text].value + " " + smile + " ";
        document.forms[form].elements[text].focus();
    }
</script>

<script type='text/javascript'>
<!--
    function mySubmit()
    {
        setTimeout('document.shbox.reset()', 100);
    }
//-->
</script>

<script type='text/javascript'>
    var b_open     = 0;
    var i_open     = 0;
    var u_open     = 0;
    var color_open = 0;
    var html_open  = 0;
    var myAgent    = navigator.userAgent.toLowerCase();
    var myVersion  = parseInt(navigator.appVersion);
    var is_ie      = ((myAgent.indexOf('msie') != -1) && (myAgent.indexOf('opera') == -1));
    var is_nav     = ((myAgent.indexOf('mozilla') != -1) && (myAgent.indexOf('spoofer') == -1) && (myAgent.indexOf('compatible') == -1) && (myAgent.indexOf('opera') == -1) && (myAgent.indexOf('webtv') == -1) && (myAgent.indexOf('hotjava') == -1));
    var is_win     = ((myAgent.indexOf('win') != -1) || (myAgent.indexOf('16bit') != -1));
    var is_mac     = (myAgent.indexOf('mac') != -1);
    var bbtags     = new Array();

    function cstat()
    {
         var c = stacksize(bbtags);

        if ((c < 1) || (c == null))
        {
            c = 0;
        }

        if (!bbtags[0])
        {
            c = 0;
        }

        document.shbox.tagcount.value = "Tags " + c;
    }

    function stacksize(thearray)
    {
        for (i = 0; i < thearray.length; i++)
        {
            if ((thearray[i] == "") || (thearray[i] == null) || (thearray == 'undefined'))
            {
                return i;
            }
        }
        return thearray.length;
    }

    function pushstack(thearray, newval)
    {
        arraysize           = stacksize(thearray);
        thearray[arraysize] = newval;
    }

    function popstackd(thearray)
    {
        arraysize = stacksize(thearray);
        theval    = thearray[arraysize - 1];
        return theval;
    }

    function popstack(thearray)
    {
        arraysize = stacksize(thearray);
        theval    = thearray[arraysize - 1];

        delete thearray[arraysize - 1];
        return theval;
    }

    function closeall()
    {
        if (bbtags[0])
        {
            while (bbtags[0])
            {
                tagRemove = popstack(bbtags)

                if ((tagRemove != 'color'))
                {
                    doInsert("[/" + tagRemove + "]", "", false);
                    eval("document.shbox." + tagRemove + ".value = ' " + tagRemove + " '");
                    eval(tagRemove + "_open = 0");
                }
                else
                {
                    doInsert("[/" + tagRemove + "]", "", false);
                }

                cstat();
                return;
            }
        }
        document.shbox.tagcount.value = "Tags 0";
        bbtags = new Array();
        document.shbox.shbox_text.focus();
    }

    function add_code(NewCode)
    {
        document.shbox.shbox_text.value += NewCode;
        document.shbox.shbox_text.focus();
    }

    function alterfont(theval, thetag)
    {
        if (theval == 0) return;

        if (doInsert("[" + thetag + "=" + theval + "]", "[/" + thetag + "]", true)) pushstack(bbtags, thetag);

        document.shbox.color.selectedIndex = 0;
        cstat();
    }

    function tag_url()
    {
        var FoundErrors = "";
        var enterURL    = prompt("<?php echo $lang['err_reg_url']?>", "http://");
        var enterTITLE  = prompt("<?php echo $lang['err_reg_title']?>", "");

        if (!enterURL || enterURL == "")
        {
            FoundErrors += " " + "<?php echo $lang['err_ind_url']?>";
        }

        if (!enterTITLE)
        {
            FoundErrors += " " + "<?php echo $lang['err_ind_title']?>";
        }

        if (FoundErrors)
        {
            alert("<?php echo $lang['err_error']?>" + FoundErrors);
            return;
        }
        doInsert("[url=" + enterURL + "]" + enterTITLE + "[/url]", "", false);
    }

    function tag_image()
    {
        var FoundErrors = "";
        var enterURL    = prompt("<?php echo $lang['err_reg_url']?>", "http://");

        if (!enterURL || enterURL == "http://")
        {
            alert("<?php echo $lang['err_error']?>" + "<?php echo $lang['err_reg_url']?>");
            return;
        }
        doInsert("[img]" + enterURL + "[/img]", "", false);
    }

    function tag_email()
    {
        var emailAddress = prompt("<?php echo $lang['err_ind_email']?>", "");

        if (!emailAddress)
        {
            alert("<?php echo $lang['err_error']?>" + "<?php echo $lang['err_ind_email']?>");
            return;
        }
        doInsert("[email]" + emailAddress + "[/email]", "", false);
    }

    function doInsert(ibTag, ibClsTag, isSingle)
    {
        var isClose = false;
        var obj_ta = document.shbox.shbox_text;

        if ((myVersion >= 4) && is_ie && is_win)
        {
            if (obj_ta.isTextEdit)
            {
                obj_ta.focus();

                var sel = document.selection;
                var rng = sel.createRange();
                rng.colapse;

                if ((sel.type == "Text" || sel.type == "None") && rng != null)
                {
                    if (ibClsTag != "" && rng.text.length > 0)
                    {
                        ibTag += rng.text + ibClsTag;
                    }
                    else if (isSingle) isClose = true;
                    rng.text = ibTag;
                }
            }
            else
            {
                if (isSingle) isClose = true;

                obj_ta.value += ibTag;
            }
        }
        else
        {
            if (isSingle) isClose = true;

            obj_ta.value += ibTag;
        }
        obj_ta.focus();
        // obj_ta.value = obj_ta.value.replace(/ /, " ");
        return isClose;
    }

    function em(theSmilie)
    {
        doInsert(" " + theSmilie + " ", "", false);
    }

    function ShowSmilies()
    {
        var SmiliesWindow = window.open("/smilies.php", "<?php echo $lang['err_smilies']?>", "")
    }

    function ShowTags()
    {
        var TagsWindow = window.open("/tags.php", "<?php echo $lang['err_tags']?>", "")
    }

    function winop()
    {
        windop = window.open("smilies.php", "<?php echo $lang['err_mywin']?>", "");
    }

    function addText(theTag, theClsTag, isSingle, theForm)
    {
        var isClose  = false;
        var message  = theForm.shbox_text;
        var set      = false;
        var old      = false;
        var selected = "";

        if (navigator.appName == "Netscape" && message.textLength >= 0)
        { //-- Mozilla, Firebird, Netscape --//
            if (theClsTag != "" && message.selectionStart != message.selectionEnd)
            {
                selected = message.value.substring(message.selectionStart, message.selectionEnd);
                str = theTag + selected + theClsTag;
                old = true;
                isClose = true;
            }
            else
            {
                str = theTag;
            }

            message.focus();

            start                  = message.selectionStart;
            end                    = message.textLength;
            endtext                = message.value.substring(message.selectionEnd, end);
            starttext              = message.value.substring(0, start);
            message.value          = starttext + str + endtext;
            message.selectionStart = start;
            message.selectionEnd   = start;
            message.selectionStart = message.selectionStart + str.length;

            if (old)
            {
                return false;
            }

            set = true;

            if (isSingle)
            {
                isClose = false;
            }
        }

        if ((myVersion >= 4) && is_ie && is_win)
        { //-- Internet Explorer --//
            if (message.isTextEdit)
            {
                message.focus();

                var sel = document.selection;
                var rng = sel.createRange();

                rng.colapse;

                if ((sel.type == "Text" || sel.type == "None") && rng != null)
                {
                    if (theClsTag != "" && rng.text.length > 0)
                    {
                        theTag += rng.text + theClsTag;
                    }
                    else if (isSingle)
                    {
                        isClose = true;
                    }
                    rng.text = theTag;
                }
            }
            else
            {
                if (isSingle) isClose = true;

                if (!set)
                {
                     message.value += theTag;
                }
            }
        }
        else
        {
                if (isSingle) isClose = true;

                if (!set)
                {
                    message.value += theTag;
                }
            }
            message.focus();

            return isClose;
        }

    function smilie(theSmilie)
    {
        addText(" " + theSmilie, "", false, document.shbox);
    }

    function simpletag(thetag)
    {
        var tagOpen = eval(thetag + "_open");

        if (tagOpen == 0)
        {
            if (doInsert("[" + thetag + "]", "[/" + thetag + "]", true))
            {
                eval(thetag + "_open = 1");
                eval("document.shbox." + thetag + ".value += '*'");

                pushstack(bbtags, thetag);
                cstat();
            }
        }
        else
        {
            lastindex = 0;

            for (i = 0;
                i < bbtags.length;
                i++)
            {
                if (bbtags[i] == thetag)
                {
                        lastindex = i;
                }
            }

            while (bbtags[lastindex])
            {
                tagRemove = popstack(bbtags);
                doInsert("[/" + tagRemove + "]", "", false)

                if ((tagRemove != 'COLOR'))
                {
                        eval("document.shbox." + tagRemove + ".value = ' " + tagRemove + " '");
                        eval(tagRemove + "_open = 0");
                }
            }
            cstat();
        }
    }
</script>