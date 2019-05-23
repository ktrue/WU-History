This is the README file for installation of Jim McMurray's (http://www.jcweather.us/)
WeatherUnderground History page.

Adapted for Carterlake/WD/AJAX/PHP template set by
Ken True - http://saratoga-weather.org/template/index.php  (Template integration)
Mike Challis - http://www.carmosaic.com/weather/scripts.php (dynamic CSS generation + theme integration)

Based on V2.07 of WU-History.php by Jim McMurray .. included with permission (thanks Jim!)

Zip file contents:

  wuicons/*          - helper files for the page display
  wxwuhistory.php    - main page in Carterlake/WD/AJAX/PHP template set
  WU-History-inc.php - Jim's modified WU-History.php page (does the real work)
  wxwuhistory-README.txt - this file.

  You may already have these two files (if you have a later version of wxhistory.php)

  floatTop.js        - JavaScript for floating 'Top' link
  ajax-images/toparrow.gif - image file for floating 'Top' link

Installation:

1) Unzip the wuwxhistory.zip using folder names to the same directory as your Settings.php file.


2) Add the following entries to your Settings.php file:

###########################################################################
# WU-History settings
$SITE['WUID']		= 'KCASARAT1'; // set to Wunderground ID (upper case)
//$SITE['WUunits']	= 'E';		// units to display 'E'=english, 'M'=metric, 'B'=both
//             comment $SITE['WUunits'] above out to use uomTemp to select English or Metric
$SITE['WUstationname'] = 'Saratoga, California, USA'; // for legend at bottom of page
$SITE['WUbirthday']	= '02-04-2004'; //Stations first day of operation format dd-mm-yyyy
###########################################################################



3) upload wuicons/* (Binary), and
   (ASCII) Settings.php, wxwuhistory.php, WU-History-inc.php to your website.

   If you don't have the wxhistory.php with the floating top arrow, then
   upload ajax-images/toparrow.gif (Binary) and floatTop.js to your website.

4) add wxwuhistory.php link to your menu system (menubar.php for classic menu, flyout-menu.xml for flyout menu)


Readme for WU-History.php
By Jim McMurry - jcweather.us - jmcmurry@mwt.net 3-Feb-2012


All instructions for the script are in the comments at the beginning of the script.

One thing to note though is that search spiders can spend a lot of time going through this script because it calls itself with various different parameters.  The spiders just see those as different links and blindly follow them.  

It is wise to have a robots.txt file in the root of your web site to tell the spiders to not go there.  A couple ways to do this are:


User-agent: *
Disallow: /WUHistory/

or

User-agent: *
Disallow: /history.php


If that doesn't deter a "bad" bot, you can block them in .htaccess if you're on a linux server.
Google that because there are several ways to go about doing that.

