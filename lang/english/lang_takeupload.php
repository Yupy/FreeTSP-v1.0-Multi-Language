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
** Language File:- takeupload.php
**/

$lang = array(
    #Errors
    'err_upload_fail'      => 'Upload Failed!',
    'err_missing_data'     => 'Missing Form Data',
    'err_empty_filename'   => 'Empty filename!',
    'err_no_nfo'           => 'No NFO!',
    'err_zero_byte'        => '0-byte NFO',
    'err_large_nfo'        => 'NFO is too big! Max 65,535 bytes.',
    'err_nfo_fail'         => 'NFO Upload Failed',
    'err_enter_desc'       => 'You MUST Enter a Description!',
    'err_select_cat'       => 'You MUST Select a Category to put the torrent in!',
    'err_inv_filename'     => 'Invalid Filename!',
    'err_inv_filename_tor' => 'Invalid Filename (Not a .torrent).',
    'err_eek'              => 'EEK',
    'err_empty_file'       => 'Empty File!',
    'err_bencoded'         => 'What the hell did you upload? This is NOT a Bencoded File!',
    'err_dict'             => 'NOT a Dictionary',
    'err_dict_key'         => 'Dictionary is missing Key(s)',
    'err_inv_dict'         => 'Invalid entry in Dictionary',
    'err_inv_dict_type'    => 'Invalid Dictionary entry type',
    'err_inv_announce'     => 'Invalid Announce URL! Must be ',
    'err_inv_pieces'       => 'Invalid Pieces',
    'err_miss_files'       => 'Missing both Length and Files',
    'err_no_files'         => 'No Files',
    'err_filename_err'     => 'Filename Error',
    'err_already_upload'   => 'Torrent Already Uploaded!',

    #Misc
    'msg_subject_voted'    => 'An Offer you Voted for!',
    'msg_hi'               => 'Hi, ',
    'msg_offer_uploaded'   => ' An Offer you were interested in has just been Uploaded! ',
    'msg_subject_request'  => 'A Request you were interested in!',
    'msg_request_uploaded' => ' A Request you were interested in has just been Uploaded! ',
    'msg_subject_req_made' => 'A Request you made!',
    'msg_request_made'     => ' A Request you made has just been Uploaded! ',
    'writelog_offered'     => 'Offered Torrent ',
    'writelog_uploaded'    => ' was Uploaded by ',
    'writelog_request'     => 'Request for torrent ',
    'writelog_filled'      => ' was Filled by ',
    'writelog_torrent'     => 'Torrent ',
    'email_uploaded'       => 'A new torrent has been uploaded.',
    'email_name'           => 'Name: ',
    'email_size'           => 'Size: ',
    'email_cat'            => 'Category: ',
    'email_upload_by'      => 'Uploaded by: ',
    'email_desc'           => 'Description',
    'email_url'            => 'You can use the URL below to download the torrent (you may have to login).',
    'email_recipients'     => 'Multiple recipients ',
    'email_new_torr'       => 'New torrent - ',
    'email_from'           => 'From: ',
    'email_bcc'            => 'Bcc: ',
    'email_'               => '',

    #Texts
    'text_anon'            => 'Anonymous',
    'text_notif_err'       => 'Your torrent has been been Uploaded. DO NOT RELOAD THE PAGE! There was however a problem delivering the e-mail notifcations. Please let an Administrator know about this Error!'
);

?>