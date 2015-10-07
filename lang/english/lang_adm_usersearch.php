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
** Language File:- admincp/usersearch.php
**/

$lang = array(
    #Buttons
    'btn_instruct'             => 'Instructions',
    'btn_reset'                => 'Reset',
    'btn_new_announcement'     => 'Create New Announcement',

    #Errors
    'err_bad_email'            => 'Bad Email',
    'err_bad_ip'               => 'Bad IP',
    'err_bad_subnet'           => 'Bad Subnet Mask',
    'err_bad_ratio'            => 'Bad Ratio',
    'err_two_ratio'            => 'Two Ratios are Needed for this type of Search',
    'err_second_ratio'         => 'Bad Second Ratio',
    'err_bad_upload'           => 'Bad Uploaded Amount.',
    'err_two_upload'           => 'Two Uploaded Amounts Needed for this type of Search',
    'err_second_upload'        => 'Bad Second Uploaded Amount',
    'err_bad_download'         => 'Bad Downloaded Amount',
    'err_two_download'         => 'Two Downloaded Amounts Needed for this type of Search',
    'err_second_download'      => 'Bad Second Downloaded Amount',
    'err_inv_date'             => 'Invalid Date',
    'err_two_dates'            => 'Two Dates Needed for this Type of Search',
    'err_bad_date'             => 'The Second Date is Not Valid',

    #Forms
    'forms_name'               => 'Name:',
    'forms_ratio'              => 'Ratio:',
    'forms_dropdown_equal'     => 'Equal',
    'forms_dropdown_above'     => 'Above',
    'forms_dropdown_below'     => 'Below',
    'forms_dropdown_between'   => 'Between',
    'forms_member_status'      => 'Member Status:',
    'forms_dropdown_any'       => '(Any)',
    'forms_dropdown_confirmed' => 'Confirmed',
    'forms_dropdown_pending'   => 'Pending',
    'forms_email'              => 'Email:',
    'forms_ip'                 => 'IP:',
    'forms_account_status'     => 'Account Status:',
    'forms_dropdown_enabled'   => 'Enabled',
    'forms_dropdown_disabled'  => 'Disabled',
    'forms_comment'            => 'Comment:',
    'forms_mask'               => 'Mask:',
    'forms_class'              => 'Class:',
    'forms_joined'             => 'Joined:',
    'forms_uploaded'           => 'Uploaded:',
    'forms_dropdown_on'        => 'On',
    'forms_dropdown_before'    => 'Before',
    'forms_dropdown_after'     => 'After',
    'forms_donor'              => 'Donor:',
    'forms_dropdown_yes'       => 'Yes',
    'forms_dropdown_no'        => 'No',
    'forms_last_seen'          => 'Last Seen:',
    'forms_downloaded'         => 'Downloaded:',
    'forms_warned'             => 'Warned:',
    'forms_active_only'        => 'Active Only:',
    'forms_disabled_ip'        => 'Disabled IP:',

    #Misc
    'title_user_search'        => 'Administrative User Search',

    #Tables
    'table_info_1'             => 'Fields left blank will be Ignored.',
    'table_info_2'             => 'Wildcards * and ? may be used in Name, Email and Comments, as well as multiple values separated
                                   by spaces (e.g.\'wyz Max*\' in Name will list both users named \'wyz\' and those whose names start
                                   by \'Max\'. Similarly \'~\' can be used for negation, e.g. \'~alfiest\' in Comments will restrict
                                   the search to users that DO NOT have \'alfiest\' in their Comments).',
    'table_info_3'             => 'The Ratio field accepts \'Inf\' and \'---\' besides the usual numeric values.',
    'table_info_4'             => 'The Subnet Mask may be entered either in dotted decimal or CIDR notation (e.g. 255.255.255.0
                                   is the same as /24).',
    'table_info_5'             => 'Uploaded and Downloaded should be entered in GB.',
    'table_info_6'             => 'For search parameters with multiple text fields the second will be Ignored unless relevant for the
                                   type of search chosen. ',
    'table_info_7'             => '\'Active Only\' restricts the search to users currently Leeching or Seeding,  \'Disabled IPs\' to
                                   those whose IPs also show up in Disabled Accounts.',
    'table_info_8'             => 'The \'p\' columns in the results show Partial Stats, that is, those of the Torrents in Progress. ',
    'table_info_9'             => 'The History column lists the number of Forum Posts and Torrent Comments,respectively, as well as
                                   linking to the History page.',

    #Texts
    'text_count'               => 'Count Query',
    'text_search'              => 'Search Query',
    'text_url'                 => 'URL',
    'text_announce'            => 'Announce Query',
    'text_no_user'             => 'No User was Found!',
    'text_name'                => 'Name:',
    'text_ratio'               => 'Ratio:',
    'text_ip'                  => 'IP:',
    'text_email'               => 'Email:',
    'text_joined'              => 'Joined:',
    'text_last_seen'           => 'Last Seen:',
    'text_status'              => 'Status:',
    'text_enabled'             => 'Enabled:',
    'text_pr'                  => 'pR:',
    'text_pul'                 => 'pUL:',
    'text_pdl'                 => 'pDL:',
    'text_history'             => 'History:'
);

?>