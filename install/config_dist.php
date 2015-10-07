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
** Based On The TBDev Installer
** Moddified To Work With FreeTSP By Fireknight
**/

##############
## DB Setup ##
##############
$mysql_host = "<#mysql_host#>"; # Your MySQL Host Name -- localhost is the Default
$mysql_user = "<#mysql_user#>"; # Your MySQL Username
$mysql_pass = "<#mysql_pass#>"; # Your MySQL Password
$mysql_db   = "<#mysql_db#>";   # Your MySQL Data Base Name

########################
## Define Directories ##
########################
define('FUNC_DIR',dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('ROOT_DIR',realpath(FUNC_DIR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
define('ADMIN_DIR', ROOT_DIR . 'admincp' . DIRECTORY_SEPARATOR);
define('CACHE_DIR', ROOT_DIR . 'cache' . DIRECTORY_SEPARATOR);
define('STYLES_DIR', ROOT_DIR . 'stylesheets' . DIRECTORY_SEPARATOR);
define('LANG_DIR', ROOT_DIR . 'lang' . DIRECTORY_SEPARATOR);
define('CLASS_DIR', FUNC_DIR . 'classes' . DIRECTORY_SEPARATOR);

##################
## Site Setings ##
##################
$site_url              = "<#site_url#>";          # Set This To Your Sites URL No Ending Slash
$site_online           = true;                    # Set To False To Turn Site Offline.  To Set Back Online Edit This File
$members_only          = true;                    # Set To False To Allow Non-members To Download
$site_email            = "<#site_email#>";        # Email For Sender/return Path.
$email_confirm         = false;                   # Allow Members To Signup Without Confirming Their Email.
$site_name             = "<#site_name#>";         # Name Of Your Site
$image_dir             = "/images/";              # Images Directory
$max_users             = 7500;                    # Max Users Before Registration Closes
$max_users_then_invite = 5000;                    # Max Users Before Invite Only
$invites               = 2500;                    # Max Number Of Invites Avalible
$signup_timeout        = 3 * 86400;               # Default 3 Days
$min_votes             = 1;                       # Min Votes
$autoclean_interval    = 900;                     # Default 15 Mins
$maxloginattempts      = 6;                       # Max Failed Logins Before Getting Banned
$dictbreaker           = "dictbreaker";           # Folder For Max Failed Logins
$site_reputation       = false;                   # Set To False To Turn Reputation System Off
$FREETSP['language']   = "english";

######################
## Torrents Setings ##
######################
$announce_urls         = array();
$announce_urls[]       = "<#announce_url#>";        # Set This To Your Sites URL + /announce.php
$torrent_dir           = "torrents";                # FOR UNIX ONLY - Must Be Writable For httpd User
//$torrent_dir         = "C:/AppServ/www/torrents"; # FOR WINDOWS ONLY - Must Be Writable For httpd User
$torrents_allfree      = "false";                   # Set To True To Make All Torrents Free To Download.
$peer_limit            = 50000;                     # Max Number Peers Allowed Before Torrents Start To Be Deleted To Make Room
$announce_interval     = 60 * 30;                   # Default 30 Mins
$max_torrent_size      = 1000000;                   # Max Torrent File Size Allowed
$max_dead_torrent_time = 3 * 3600;                  # Default 3 Hours
$oldtorrents           = 0;                         # Delete Old Torrents 0 = Disabled 1 = Enabled
$days                  = 28;                        # Amount Of Days before Dead Torrents Are Removed

########################
## Torrent Wait Times ##
########################
$waittime       = false;  # Activate Waiting Times For Downloading
$max_class_wait = 2;      # Class Selected And Classes Below Will Incure Wait Times
$ratio_1        = 0.5;    # Min Ratio Setting For 1st Wait Time
$gigs_1         = 5;      # Min Upload Setting For 1st Wait Time
$wait_1         = 48;     # Wait Time Setting For 1st Wait Time
$ratio_2        = 0.65;   # Min Ratio Setting For 2nd Wait Time
$gigs_2         = 6.5;    # Min Upload Setting For 2nd Wait Time
$wait_2         = 24;     # Wait Time Setting For 2nd Wait Time
$ratio_3        = 0.8;    # Min Ratio Setting For 3rd Wait Time
$gigs_3         = 8;      # Min Upload Setting For 3rd Wait Time
$wait_3         = 12;     # Wait Time Setting For 3rd Wait Time
$ratio_4        = 0.95;   # Min Ratio Setting For 4th Wait Time
$gigs_4         = 9.5;    # Min Upload Setting For 4th Wait Time
$wait_4         = 6;      # Wait Time Setting For 4th Wait Time

####################
## Forums Setings ##
####################
$maxfilesize        = 1024 * 1024;                      # The Max File Size Allowed To Be Uploaded - Default: 1024*1024 = 1MB
$attachment_dir     = ROOT_DIR . "forum_attachments";   # The Path To The Attachment Dir, No Slahses
$forum_width        = '100%';                           # The Width Of The Forum, In Percent, 100% Is The Full Width -- Note:
                                                        # The Width Is Also Set In The Function begin_main_frame()
$maxsubjectlength   = 80;                               # The Max Subject Length In The Topic Descriptions, Forum Name Etc...
$posts_read_expiry  = 14 * 86400;                       # Read Post Expiry Time For Forums

#$postsperpage       = (empty($CURUSER['postsperpage']) ? 25 : (int) $CURUSER['postsperpage']); # Get The Users Posts Per
                                                                                               # Page, No Need To Change

$use_attachment_mod = true;     # Set To True If You Want To Use The Attachment Mod
$use_poll_mod       = true;     # Set To True If You Want To Use The Forum Poll Mod
$forum_stats_mod    = true;     # Set To False To Disable The Forum Stats

$use_flood_mod      = true;     # Set To True If You Want To Use The Flood Mod
$limit              = 10;       # If There Are More Than $limit (default 10) Posts In The Last $minutes (default 5)
                                # Minutes, It Will Give Them A Error... -- Note: Requires The Flood Mod Set To True
$minutes            = 5;        # If There Are More Than $limit(default 10) Posts In The Last $minutes(default 5)
                                # Minutes, It Will Give Them A Error... -- Note: Requires The Flood Mod Set To True

######################
## Cleanup Settings ##
######################
$staff_log           = 7 * 86400;                 # Default Setting Is 7 Days
$site_log            = 7 * 86400;                 # Default Setting Is 7 Days
$parked_users        = 175 * 86400;               # Default Setting Is 175 Days
$inactive_users      = 40 * 86400;                # Default Setting Is 40 Days
$old_login_attempts  = 1 * 86400;                 # Default Setting Is 1 Day
$old_help_desk       = 7 * 86400;                 # Default Setting Is 7 Days
$promote_upload      = 25 * 1024 * 1024 * 1024;   # Default Setting Is 25 GB
$promote_ratio       = 1.05;                      # Default Setting Is 1.05
$promote_time_member = 28 * 86400;                # Default Setting Is 4 Weeks
$demote_ratio        = 0.95;                      # Default Setting Is 0.95

########################
## Define User Groups ##
########################
define ('UC_USER', 0);
define ('UC_POWER_USER', 1);
define ('UC_VIP', 2);
define ('UC_UPLOADER', 3);
define ('UC_MODERATOR', 4);
define ('UC_ADMINISTRATOR', 5);
define ('UC_SYSOP', 6);
define ('UC_MANAGER', 7);

define ('UC_TRACKER_MANAGER', 1); # Set The Id Number To Match The Member Who Will Have Access To The Tracker Manager

define ('FTSP', 'FreeTSP');
$curversion = 'v1.0';             # FreeTSP Version DO NOT ALTER - This Will Help Identify Code For Support Issues At freetsp.info

?>