<?php
$debug = false; 
$Version = "<!-- WU-History-inc.php - Version 3.4d - 23-May-2019  -->\r";
/*------------------------------------------------
//WU-History.php
//PHP script by Jim McMurry - jmcmurry@mwt.net - jcweather.us
//Current version maintained at http://jcweather.us/scripts.php
//Version 1.0  February 18, 2008
//        1.1  February 19, 2008 - Fixed a broken link to WU & added optional selection of other stations.
//        1.2  February 20, 2008 - Fixed an obscure link bug dealing with the new "other station" option.
//        1.3  February 22, 2008 - Added option to omit cloud conditions from tabular listing.  Fixed solar & rain issue in the summary section.
//        1.4  February 22, 2008 - Supressed Solar & UV graphs in monthly & longer modes. Supressed current conditions in the summary except when viewing current day.
//        1.5  February 23, 2008 - Fixed precip error when viewing previous days.  Added optional top link in header to return to "today" when off somewhere else.
//        1.6  February 27, 2008 - Fixed some missing </b> tags that were missing in the English mode that were preventing validation.
//        1.7  March 25, 2008    - Removed a superfluous <td> tag that caused a failed validation when using the option for multiple stations.
//        1.8  April, 05, 2008   - added Settings.php awareness to script (K True/saratoga-weather.org) for Carterlake/WD/AJAX/PHP templates.
//        1.9  April 07, 2008    - Made the tabular data listing optional.
//        2.0  December 23, 2008 - Fixes bugs in code to determine units output. (Thanks to Jozef (Pinto) for finding those.
//        2.1  December 23, 2008 - Fixes the modes other than Daily that were affected.  (Jozef is now officially a co-author)
//        2.2  January 31, 2009  - Fixes errors in average wind and some metric rain conversions.  (Thanks to Paul Gogan for finding these)
//        2.3  February 10, 2009 - A better fix to the average wind issue and a variable we can turn off when they fix the problem.
//        2.4  April 18, 2009    - Changed cm to mm in tabular section when showing units "both" in daily mode.  Added Solar to daily tabular data
//        2.5  April 15, 2010    - Fixed a bug found by Pelle where metric rain wasn't being converted to mm in the summary section
//        2.6  August 8, 2011    - Chenged the above back to get the rain right again due to problem reported by Oebel
//        2.7  February 3, 2012  - Put in a check to preclude selecting a future date which was causing problems for Wunderground
//        2.8  October 10, 2013   - Fixed the spacing on some of the units.  Found by Han
//        2.9  December 1, 2013   - Fixed first of the week/month or other times that there's no data in the csv file.  Found by Han
//        3.0  December 29, 2014  - WU doesn't remove deleted lines, but makes temps -999 so this removes those.  Found by Jerry Callahan
//        3.1  April 28, 2015     - Jachym found - If data is missing WU will usually report -999.9 or -573.3 (that is -999.9 F converted to C....)
//        3.2  January 28, 2016   - ereg_replace changed to str_replace for PHP 7.0.  Thanks Wim
//        3.3  March 4, 2016      - Changed code for getting WU data.  Thanks once again Wim
//        3.4  November 7, 2016   - Changed the 'getcsv' code for cURL+https Ken True
//        3.4a February 21, 2017  - fixed two Notice: errata Ken True
//        3.4b December 1, 2017 - more Notice: errata fixed - Ken True
//        3.4c October 3, 2018 - return with message if data not available, fix notice errata for no data
//        3.4d May, 23, 2019   - added support for local WXDailyHistory.php as WU discontinued WXDailyHistory.asp
//
//Portions of the code was borrowed from:
//Weather Underground - wunderground.com
//Ken True - saratoga-weather.org
//Tom Chaplin - carterlake.org
//Kevin Reed - tnetweather.com
//and probably several others.

//This script retrieves a weather station's raw data from Weather Underground 
//and displays it in a similar way to how they do it on their site.
//Weather Underground is very willing to share the graphics and data
//on their site with those who provide weather data to them.
//In a correspondence with Ken True, John Celenza, Director of Weather 
//Technology at Wunderground stated:

//"Please feel free to use Wunderground images and data on personal sites,
//as long as you link those images to Wunderground or give direct credit.
//Something like 'This image courtesy of Weather Underground' is appropriate."

//This script does NOT generate a complete HTML page and is intended for use
//ONLY by being included in an existing webpage on your site by:
//    include("./WU-History.php"); 
 
//You'll also have to place the following in the <head> section of your calling
//page:
//   <link rel="stylesheet" type="text/css" href="./WU-HistoryTan.css" />
//See the enclosed test file TestHistory.php to see how this is done.

//If your WU-history files are to be kept in a folder other than where your
//calling page is, you'll have to adjust the above.  As an example,
//if your calling page is in the root folder of your web site and the WU files
//have been placed in a folder named /abc/, the paths above would need to be
//                       ./abc/WU-History.xxx 
//If using the optional files below to place information to the right of the
//summary area, the same path will have to be used with those files as well.
//Just remember that the paths are always relative to the calling page.
   
//The supporting graphics are in the enclosed wuicons folder, and that folder 
//must be placed in the same folder with this script.  If you wish the icons 
//elsewhere, just make the appropriate changes in WU-History.css.

//Optional parameters are normally not necessary, but there are a few available:
//ID=xxxxxxx should you wish to call it with a different station ID for some reason
//day=xx for a day other than today
//month=xx for a month other than this month
//year=xxxx for a different year
//There must be a "?" to signal the beginning of the parameters and "&" between items
//So, if you wished to call it for my station and show last Christmas, you'd use
//WU-History.php?ID=KWIMAUST1&day=25&month=12&year=2007

//Folks with graphic talent may want to modify the css and create graphics in other
//colors in order to come up with different color schemes.  If you put something
//together that you'd like to share, please send me copies of the .css and the
//graphics and I'd be happy to add them to this package for others to use.

//This script Is Valid XHTML 1.0 Strict!

$WunderWrong

//settings ----------------------------- */
// Special Temporary Setting.  In week, month or year modes, Wundergrund is sending Average Wind in mph instead of km/h.  Only affects metric users.
// I'll get the word out if they fix it and you can turn it off.
$WunderWrong = true;
//
$timezone   = "America/Los_Angeles";                    // Change to your TZ.  Ken True has a list available at http://saratoga-weather.org/timezone.txt  
$WUID       = "KCASARAT1";                          // Your stations Wunderground ID
$units      = "E";                                  // Default units which are changeable at runtime.  "M" for Metric, "E" for English or "B" for Both
$birthday   = "07-02-2004";                         // Stations first day of operation format dd-mm-yyyy.  This will determine years on the date selector.
$birthday   = "01-01-2008"; // all that's left in wu API data
$gwidth     = "500";                                // Width of the graph - normally 500
$gtemp      = true;                                 // =true if you want the temperature graph =false if no
$gpress     = true;                                 // =true if baro graph =false if no
$gwind      = true;                                 // =true if wind velocity graph =false if no
$gwindir    = true;                                 // =true if wind direction graph =false if no
$grain      = true;                                 // =true if rain graph =false if no
$gsolar     = true;                                 // =true if solar graph =false if no
$guv        = true;                                 // =true if UV graph =false if no
$pwidth     = "620px";                               // The width of the summary and graph portion of the page (% or px). Normally 100%
// Optional header info
$header     = true;                                 // true if you wish to use it
$Langtitle  = "Saratoga-Weather Station Historical Data";            // The Bold text at the top
$LcurDay    = "Return to Current Day";              // The link back to the current day if off in one of the other modes.  "" to disable it.
// Optional footer bar
$footer     = true;                                 // true if you wish to have the colored footer bar at the bottom
$LangFtext  = "Juneau County Weather";              // Anything you wish or "" for a plain bar
// Optional content to be placed to the right of the Summary/Graph portion of the page
$inboxfile  = "";                          // A file of html to be placed in the right outlined box.  Make it "" if not using, or just don't have a file available.
$outboxfile = "";                       // Same but for the area below the right blue box.  Paths to the files must be relative to the calling page.
// Optional "Return to Top" link on the right side
$toTop      = false;                                // Most will want this, but some folks have alternative methods. 
$LtopPg     = "Return to Top";
// Optional selector for other PWS data
$selOthers  = false;                                // true if you wish to show other stations, false if not 
$otherIds   = array('KWIMAUST1', 'ISILKEBO2', 'IVLAAMSG7', 'IBOUCHES4');  // Only works for PWS - Not Airports!
$otherLocat = array('Mauston, WI', 'Silkeborg, DK', 'Kampenhout, BE', 'Cassis, FR'); 
// Option to not show sky conditions in the daily tabular listing
$skipSolar  = false;                                 // true to skip solar data, false to include them
$skipSky    = true;                                 // true to skip sky conditions, false to include them
// Option to not show the tabular data
$skipTab    = false;                                // true if you wish to suppress the tabular data
//
// Language changes follow.  If unsure about any of them, try it and see what happens.
$mnthname     = array('Nil', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
              // Next are for the Summary Table
$Langtabs     = array('Daily', 'Weekly', 'Monthly', 'Yearly', 'Custom');  // Tabs above the Summary Table
$LangSumHeads = array("Temperature", "Dew Point", "Humidity", "Wind Speed", "Wind Gust", "Wind", "Pressure", "Precipitation", "Solar");
              // Headings for the Summary Table
$LangSumCols  = array("Current", "High", "Low", "Average");
$LSumfor   = "Summary for";
$Lunits    = "Units";
$Lboth     = "Both";
$Lenglish  = "English";
$Lmetric   = "Metric";
$Lnext     = "Next";
$Lprev     = "Previous";
$Ltarget   = array("Day", "Week", "Month", "Year");
$Lview     = "View";
$LNoData   = "No Data Available for This Period";				 // added 12/1/13
$Ltab1     = "Tabular Data for";  // Next 5 for Blue bar above the Tabular listing
$Ltab2     = "Weeks Tabular Data";
$Ltab3     = "Months Tabular Data";
$Ltab4     = "Years Tabular Data";
$Ltab5     = "Custom Date Range Tabular Data";
          // Next are for the Tabular Table
$Lheadings = array('Time', 'Temperature', 'Dew Point', 'Pressure', 'Wind', 'Wind Speed', 'Wind Gust', 'Humidity', 'Rainfall Rate (Hourly)', 'solar', 'Conditions');
          // Headings when in weekly, monthly etc modes
$Lhdngs2   = array("Temp", "Dew Point", "Humidity", "Sea Level Pressure", "Wind", "Gust Speed", "Precip");
$Lcols2    = array("high", "ave", "low", "sum"); 
$Lcommafile= "Comma Delimited File";
$Lthanks   = "Compliments of";
//
// end of settings
//------------------------------------------------
// overrides from Settings.php if available
global $SITE;
if (isset($SITE['tz'])) 		{$timezone = $SITE['tz'];}
if (isset($SITE['WUID']))		{$WUID = $SITE['WUID'];}
if (isset($SITE['uomTemp']))	{
  $units = preg_match('|C|i',$SITE['uomTemp']) ? 'M':'E';
}
if (isset($SITE['WUunits']))	{$units = $SITE['WUunits'];}
if (isset($SITE['WUstationname'])) {$LangFtext = $SITE['WUstationname'];}
if (isset($SITE['WUbirthday'])) {$birthday = $SITE['WUbirthday'];}
if (isset($SITE['UV']))		{$guv = $SITE['UV'];}
if (isset($SITE['SOLAR']))	{$gsolar = $SITE['SOLAR'];}
if (isset($SITE['timeFormat'])) {$timeFormat = $SITE['timeFormat'];}
// end of overrides from Settings.php if available
if (!function_exists('date_default_timezone_set')) {
	putenv("TZ=" . $timezone);
} else {
	date_default_timezone_set($timezone);
}
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
//--self downloader --
   $filenameReal = __FILE__;
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;
}
echo $Version;
if(isset($_REQUEST['debug'])) {$debug = true; }
// Set some dates
$mo = date("m");
$da = date("d");
$yr = date("Y");
$FIRST_YEAR = substr($birthday,6,4);
$LAST_YEAR = $yr;

if ($debug)	{
  print "<!-- initial units='$units' -->\n";
  print "<!-- initial request \n".print_r($_REQUEST,true)." \n-->\n";
}
// Defaults if called without parameters
$PHP_SELF = $_SERVER['PHP_SELF'];
if ( empty($_REQUEST['ID']) ) 
        $_REQUEST['ID']=$WUID;
if ( empty($_REQUEST['day']) ) 
        $_REQUEST['day']=$da;
if ( empty($_REQUEST['dayend']) ) 
        $_REQUEST['dayend']=$da;
if ( empty($_REQUEST['month']) ) 
        $_REQUEST['month']=$mo;
if ( empty($_REQUEST['monthend']) ) 
        $_REQUEST['monthend']=$mo;
if ( empty($_REQUEST['year']) ) 
        $_REQUEST['year']=$yr;
if ( empty($_REQUEST['yearend']) ) 
        $_REQUEST['yearend']=$yr;		
if ( empty($_REQUEST['units']) ) 
        $_REQUEST['units']=$units;
if ( empty($_REQUEST['mode']) ) 
        $_REQUEST['mode']=1;
if ($debug)	{print "<!-- final request \n".print_r($_REQUEST,true)." \n-->\n";}

//Pass into PHP variables
//------------------------------------------------
$WUID = $_REQUEST['ID'];
$da = $_REQUEST['day'];
$mo = $_REQUEST['month'];
$yr = $_REQUEST['year'];
$da2 = $_REQUEST['dayend'];
$mo2 = $_REQUEST['monthend'];
$yr2 = $_REQUEST['yearend'];
$units = $_REQUEST['units'];
$mode = $_REQUEST['mode'];
print "<!-- final units='$units' -->\n";

// Find out if it's today or in the past
$reqdate = $da . "-" . $mo . "-" . $yr;
$isToday = strtotime($reqdate) == strtotime(date("d-m-Y"));
if (time() < strtotime($mo . "/" . $da . "/" . $yr)) {       // preclude any dates in the future that were causing problems for Wunderground
	$mo = date("n");
	$da = date("j");
	$yr = date("Y");
}
// Gather the csv data
$WUgraphstr = "https://www.wunderground.com/cgi-bin/wxStationGraphAll";          
$WUdatastr = "https://www.wunderground.com/weatherstation/WXDailyHistory.asp";
$tPage = getCurrentPageURL(false);
$WUdatastr = str_replace(pathinfo($_SERVER['PHP_SELF'],PATHINFO_BASENAME),'WXDailyHistory.php',$tPage);   
if ($mode == 1) {
	$wunderstring = $WUdatastr . "?ID=" . $WUID . "&month=" . $mo . "&day=" . $da . "&year=" . $yr . "&format=1&graphspan=day";    // Day
} elseif ($mode == 2) {
	$wunderstring = $WUdatastr . "?ID=" . $WUID . "&month=" . $mo . "&day=" . $da . "&year=" . $yr . "&format=1&graphspan=week";   // Week
} elseif ($mode == 3) {
	$wunderstring = $WUdatastr . "?ID=" . $WUID . "&month=" . $mo . "&day=" . $da . "&year=" . $yr . "&format=1&graphspan=month";  // Month
} elseif ($mode == 4) {
	$wunderstring = $WUdatastr . "?ID=" . $WUID . "&month=" . $mo . "&day=" . $da . "&year=" . $yr . "&format=1&graphspan=year";   // Year
} elseif ($mode == 5) {
	$wunderstring = $WUdatastr . "?ID=" . $WUID . "&month=" . $mo . "&day=" . $da . "&year=" . $yr . "&monthend=" . $mo2 . "&dayend=" . $da2 . "&yearend=" . $yr2 . "&format=1&graphspan=custom";  // Custom
}
$rawstring=getcsvWithoutHanging($wunderstring);
$csvraw = array();
$rawstring = str_replace("<br>", "",$rawstring); // remove any embedded html newlines
foreach (explode("\n",$rawstring) as $i => $line) {
	$csvraw[] = explode(",", $line);
}
if(count($csvraw) < 20) {
	echo "<p>Sorry... the WU data for this date is not currently available.  Please try again later.</p>\n";
	return;
}
//echo $wunderstring; 
//print_r($csvraw);
//exit;
$csvdata = array_pure($csvraw);             //$csvdata has headings in row 0.  Saving a copy for no good reason
echo "<!-- Size of the array is " . sizeof($csvdata) . " -->\r";  // Thanks to Jerry Callahan for finding this issue
foreach($csvdata as $key => &$line) {       // See if there are any deleted entries and remove them
	if ($line[1] < -100 || $line[1] > 150) {               // If data is missing WU will usually report -999.9 or -573.3 (that is -999.9 F converted to C....)
//	if ($line[1] == -999.0) {               // Temp is this when the line's been deleted by the user
		echo "<!-- Removing line " . $key . " -->\r";	
		unset($csvdata[$key]);
	}
}
unset($line);                               // break the reference with the last element
echo "<!-- Size of the final array is " . sizeof($csvdata) . " -->\r";
$csvarray = $csvdata;
if ($mode == 1){
	array_shift($csvarray);     // Now $csvarray has nothing but 2D data.  The other modes don't need this treatment
	if ($csvarray[0][3] > 50) {                 // Use Baro  to determine whether raw data is metric or not
		$rawunits = "metric";                   // Depends on how the user's wunderground cookie is set
	} else {
		$rawunits = "english";
	}
	if ($debug)	{ echo "<!-- $rawunits - " . $csvarray[0][3] . " -->\n";	}	
} else {
	if ($csvarray[0][10] > 50) {                // Baro is in a different position in the other modes
		$rawunits = "metric";                   
	} else {
		$rawunits = "english";
	}
	if ($debug)	{echo "<!-- $rawunits - " . $csvarray[0][10] . " -->\n";}		
}
sizeof($csvarray) > 0 ? $DataGood = true : $DataGood = false;   // changed 12/1/13
$wunderCSVstring = str_replace("&","&amp;",$wunderstring);	// So the link to the csv output will validate
?>
<div id="wuwrap">
<table cellpadding="0" cellspacing="0" class="full" >
<tr>
  <td class="vaT" id="content" >
  <div style="margin-top: 15px;">
<?php 
if ($header) {  // This provides an option whether to show the top heading
echo '<div class = "heading">' . $Langtitle . '</div>' . "\r";
// Display link back to current day if not currently showing "today"
	if (($mode > 1 or ! $isToday) && $LcurDay <> "") {
		$callstr = $PHP_SELF . '?ID=' . $WUID . '&amp;month=' . date("m") . '&amp;day=' . date("d") . '&amp;year=' . date("Y") . '&amp;mode=1&amp;units=' . $units;
		echo '<div class="titleBar"><a href="' . $callstr . '">' . $LcurDay . '</a></div>' . "\r";
	} else { 
		echo '	<div class="titleBar">&nbsp;</div>' . "\r"; 
	}
}
if ($mode == 1) {
	$bannerphrase = $Langtabs[0] . ' ' . $LSumfor . ' ' . $mnthname[intval($mo)] . ' ' . $da . ', ' . $yr;
} elseif ($mode == 2) {
	$bannerphrase = $Langtabs[1] . ' ' . $LSumfor . ' ' . $mnthname[intval($mo)] . ' ' . $da . ', ' . $yr;
} elseif ($mode == 3) {
	$bannerphrase = $Langtabs[2] . ' ' . $LSumfor . ' ' . $mnthname[intval($mo)] . ' ' . $yr;
} elseif ($mode == 4) {
	$bannerphrase = $Langtabs[3] . ' ' . $LSumfor . ' ' . $yr;
} else {
	$bannerphrase = $LSumfor . ' ' . $mnthname[intval($mo)] . ' ' . $da . ', ' . $yr . ' - ' . $mnthname[intval($mo2)] . ' ' . $da2 . ', ' . $yr2;
}
?>  
	<table cellspacing="0" cellpadding="0" style="width: <?php echo($pwidth); ?>;">
		<tr class="vaT">
		<td class="full">		
			<table cellspacing="0" cellpadding="0" class="colorTop">
			<tr>
			<td class="hLeft"></td>
			<td class="hCenter vaM" ><?php echo $bannerphrase; ?></td>
			<td id="unitmenu" class="hMenu taL nobr vaM  noprint myMenu" style="padding-top: 3px">
			<?php
			$callstr = $PHP_SELF . '?ID=' . $WUID . '&amp;month=' . $mo . '&amp;day=' . $da . '&amp;year=' . $yr . '&amp;mode=' . $mode . '&amp;units=';
			if ($units == "M") {
			echo $Lunits . ': &nbsp;<b>' . $Lmetric . '</b>&nbsp;&nbsp;&nbsp; <a href="' . $callstr . '&amp;units=E' . '">' . $Lenglish . '</a>&nbsp;&nbsp;&nbsp;<a href="' . $callstr . 'B">' . $Lboth . '</a>';
			} elseif ($units == "E") {
			echo $Lunits . ': &nbsp;<b>' . $Lenglish . '</b>&nbsp;&nbsp;&nbsp; <a href="' . $callstr . '&amp;units=M' . '"> ' . $Lmetric . '</a>&nbsp;&nbsp;&nbsp;<a href="' . $callstr . 'B">' . $Lboth . '</a>';
			} else {
			echo $Lunits . ': &nbsp;<b>' . $Lboth . '</b>&nbsp;&nbsp;&nbsp; <a href="' . $callstr . '&amp;units=E' . '">' . $Lenglish . '</a>&nbsp;&nbsp;&nbsp;<a href="' . $callstr . 'M">' . $Lmetric . '</a>';
			}
			?>
			</td>
			<td class="hRight"></td>
			</tr>
			<tr>
			<td class="sLeft"></td>
			<td class="sCenter"></td>
			<td class="sCenter"></td>		
			<td class="sRight"></td>
			</tr>
			</table>
		<div class="colorBox">
		<div class="selectorBox noPrint">
		<table cellspacing="0" cellpadding="0" class="full dateTable">
		<tr>
<?php		
if ($mode == 1) {
	$ndate = AddDate($mo, $da, $yr, -1);
	echo '<td class="nobr noprint"><!-- a href="' . $_SERVER['PHP_SELF'] . '?ID=' . $WUID . '&amp;month=' . $ndate['mon'] . '&amp;day=' . $ndate['mday'] . '&amp;year=' . $ndate['year'] . '&amp;units=' . $units . '&amp;mode=' . $mode . '">&laquo; ' . $Lprev . ' ' . $Ltarget[0] . '</a --></td>';
} elseif ($mode == 2) {
	$ndate = AddDate($mo, $da, $yr, -7);
	echo '<td class="nobr noprint"><!-- a href="' . $_SERVER['PHP_SELF'] . '?ID=' . $WUID . '&amp;month=' . $ndate['mon'] . '&amp;day=' . $ndate['mday'] . '&amp;year=' . $ndate['year'] . '&amp;units=' . $units . '&amp;mode=' . $mode  . '">&laquo; ' . $Lprev . ' ' . $Ltarget[1] . '</a --></td>';
} elseif ($mode == 3) {
	$ndate = AddDate($mo, $da, $yr, -30);
	echo '<td class="nobr noprint"><!-- a href="' . $_SERVER['PHP_SELF'] . '?ID=' . $WUID . '&amp;month=' . $ndate['mon'] . '&amp;day=' . $ndate['mday'] . '&amp;year=' . $ndate['year'] . '&amp;units=' . $units . '&amp;mode=' . $mode  . '">&laquo; ' . $Lprev . ' ' . $Ltarget[2] . '</a --></td>';
} elseif ($mode == 4) {
	$ndate = AddDate($mo, $da, $yr, -365);
	echo '<td class="nobr noprint"><!-- a href="' . $_SERVER['PHP_SELF'] . '?ID=' . $WUID . '&amp;month=' . $ndate['mon'] . '&amp;day=' . $ndate['mday'] . '&amp;year=' . $ndate['year'] . '&amp;units=' . $units . '&amp;mode=' . $mode  . '">&laquo; ' . $Lprev . ' ' . $Ltarget[3] . '</a --></td>';
}		
?>		
		<td class="taC full noprint">
<?php  // These are the date selectors
    echo '<form method="post" action="' . $SITE['PHP_SELF'] . '" />';
    echo '<table border="0" cellpadding="0" cellspacing="0" style="margin-left: auto; margin-right: auto;">  
        <tr>'; 
	if ($selOthers) {
		echo '<td>
			<select name="ID">' . "\n";
		for ( $i = 0; $i < sizeof($otherLocat); $i ++ ) {
			echo '<option value="' . $otherIds[$i] . '"';
			if ($otherIds[$i]==$WUID) echo ' selected="selected"'; 
				echo '>' . $otherLocat[ $i ] . '</option>' . "\n";
		}
		echo '</select>
			</td>';
	}	
	echo '<td>
        <select name="month">' . "\n";
    for ( $mon = 1; $mon < 13; $mon ++ ) {
        echo '<option value="' . $mon . '"';
		if ($mon==$mo) echo ' selected="selected"'; 
			echo '>' . $mnthname[ $mon ] . '</option>' . "\n";
    }
    echo '</select>
        </td><td>
        <select name="day">' . "\n";
    for ( $dd = 1 ; $dd < 32 ; $dd ++ ) {
        echo '<option value="' . $dd . '"'; 
		if ($dd==$da) echo ' selected="selected"';  
		echo '>' . $dd . '</option>' . "\n";
    }
    echo '</select>
        </td><td>
        <select name="year">' . "\n";
    for ( $yy = $FIRST_YEAR ; $yy < $LAST_YEAR +1 ; $yy++ ) {
        echo '<option value="' . $yy . '"';
		if ($yy==$yr) echo ' selected="selected"';  
		echo '>' . $yy . '</option>' . "\n";
    }
    echo '</select>
        </td>';
if ($mode == 5) {   //Add second set here	
		echo '<td class="nobr" style="vertical-align: middle;">- TO -</td><td>
        <select name="monthend">' . "\n";
    for ( $mon2 = 1; $mon2 < 13; $mon2 ++ ) {
        echo '<option value="' . $mon2 . '"';
		if ($mon2==$mo2) echo ' selected="selected"'; 
		echo '>' . $mnthname[ $mon2 ] . '</option>' . "\n";
    }
    echo '</select>
        </td><td>
        <select name="dayend">' . "\n";
    for ( $dd2 = 1 ; $dd2 < 32 ; $dd2 ++ ) {
        echo '<option value="' . $dd2 . '"'; 
		if ($dd2==$da2) echo ' selected="selected"';  
		echo '>' . $dd2 . '</option>' . "\n";
    }
    echo '</select>
        </td><td>
        <select name="yearend">' . "\n";
    for ( $yy2 = $FIRST_YEAR ; $yy2 < $LAST_YEAR +1 ; $yy2++ ) {
        echo '<option value="' . $yy2 . '"';
		if ($yy2==$yr2) echo ' selected="selected"';  
		echo '>' . $yy2 . '</option>' . "\n";
    }
    echo '</select>
        </td>';
}
		echo '<td><input type="submit" value="' . $Lview . '" /></td>
        </tr>
        </table>' . "\n";
?>		
		</td>
<?php		
if ($mode == 1) {
	$ndate = AddDate($mo, $da, $yr, +1);
	echo '<td class="nobr noprint"><!-- a href="' . $_SERVER['PHP_SELF'] . '?ID=' . $WUID . '&amp;month=' . $ndate['mon'] . '&amp;day=' . $ndate['mday'] . '&amp;year=' . $ndate['year'] . '&amp;units=' . $units . '&amp;mode=' . $mode . '">' . $Lnext . ' ' . $Ltarget[0] . ' &raquo;</a --></td>';
} elseif ($mode == 2) {
	$ndate = AddDate($mo, $da, $yr, +7);
	echo '<td class="nobr noprint"><!-- a href="' . $_SERVER['PHP_SELF'] . '?ID=' . $WUID . '&amp;month=' . $ndate['mon'] . '&amp;day=' . $ndate['mday'] . '&amp;year=' . $ndate['year'] . '&amp;units=' . $units . '&amp;mode=' . $mode  . '">' . $Lnext . ' ' . $Ltarget[1] . ' &raquo;</a --></td>';
} elseif ($mode == 3) {
	$ndate = AddDate($mo, $da, $yr, +30);
	echo '<td class="nobr noprint"><!-- a href="' . $_SERVER['PHP_SELF'] . '?ID=' . $WUID . '&amp;month=' . $ndate['mon'] . '&amp;day=' . $ndate['mday'] . '&amp;year=' . $ndate['year'] . '&amp;units=' . $units . '&amp;mode=' . $mode  . '">' . $Lnext . ' ' . $Ltarget[2] . ' &raquo;</a --></td>';
} elseif ($mode == 4) {
	$ndate = AddDate($mo, $da, $yr, +365);
	echo '<td class="nobr noprint"><!-- a href="' . $_SERVER['PHP_SELF'] . '?ID=' . $WUID . '&amp;month=' . $ndate['mon'] . '&amp;day=' . $ndate['mday'] . '&amp;year=' . $ndate['year'] . '&amp;units=' . $units . '&amp;mode=' . $mode  . '">' . $Lnext . ' ' . $Ltarget[3] . ' &raquo;</a --></td>';
}		
?>				
		</tr>
	</table>
	<table cellspacing="0" cellpadding="0" class="full" id="typeTable">
		<tr>
<?php   // These are the daily, weekly etc tabs
$ActTab = 'class="activeTab"';		
$InacTab = 'class="inactiveTab"';		
$callstr = $PHP_SELF . '?ID=' . $WUID . '&amp;month=' . $mo . '&amp;day=' . $da . '&amp;year=' . $yr . '&amp;units=' . $units;
if ($mode == 1) { echo '<td ' . $ActTab . '>' . $Langtabs[0] . '</td>' . "\n"; } else { echo '<td ' . $InacTab . '><a href="' . $callstr . '&amp;mode=1">' . $Langtabs[0] . '</a></td>' . "\n"; }		
if ($mode == 2) { echo '<td ' . $ActTab . '>' . $Langtabs[1] . '</td>' . "\n"; } else { echo '<td ' . $InacTab . '><a href="' . $callstr . '&amp;mode=2">' . $Langtabs[1] . '</a></td>' . "\n"; }		
if ($mode == 3) { echo '<td ' . $ActTab . '>' . $Langtabs[2] . '</td>' . "\n"; } else { echo '<td ' . $InacTab . '><a href="' . $callstr . '&amp;mode=3">' . $Langtabs[2] . '</a></td>' . "\n"; }		
if ($mode == 4) { echo '<td ' . $ActTab . '>' . $Langtabs[3] . '</td>' . "\n"; } else { echo '<td ' . $InacTab . '><a href="' . $callstr . '&amp;mode=4">' . $Langtabs[3] . '</a></td>' . "\n"; }		
if ($mode == 5) { echo '<td ' . $ActTab . '>' . $Langtabs[4] . '</td>' . "\n"; } else { echo '<td ' . $InacTab . '><a href="' . $callstr . '&amp;mode=5">' . $Langtabs[4] . '</a></td>' . "\n"; }		
?>		
		</tr>
	</table>
	</div>  <!-- div class="selectorBox noPrint" -->
<?php if($DataGood) { ?>                    <!-- added 12/1/13 -->
<!--  Begin Summary Area -->
	<table cellspacing="0" cellpadding="0" class="summaryTable tm10">
		<thead>
		<tr style="width:100%">
		<td style="width: 25%;">&nbsp;</td>		
<?php	if ($mode==1 && $isToday) echo '<td>' . $LangSumCols[0] . ':</td>'; ?>
		<td><?php echo $LangSumCols[1]; ?>:</td>
		<td><?php echo $LangSumCols[2]; ?>:</td>
		<td><?php echo $LangSumCols[3]; ?>:</td>
		</tr>
		</thead>
		<tbody>
		<tr>
		<td><?php echo $LangSumHeads[0] . ":"; ?></td>
<?php
print "<!-- units='$units' mode='$mode' -->\n";
if ($mode == 1) {
	$result=temp_stats($csvarray, 1); // Temp
	$current=convertTemps($csvarray[count($csvarray)-1][1]);
} else {
	$t = temp_stats($csvarray, 1); // Process column 1 of the raw data
	$result[0] = $t[0];            // Store just the Hi (of the Hi's)
	$t = temp_stats($csvarray, 3); // Then column 2 which are the Lo's
	$result[1] = $t[1];            // Store the Lo (of the Lo's)
	$t = temp_stats($csvarray, 2); // Lastly proces  column 3 which are the ave's
	$result[2] = $t[2];            // Store the Ave (of the Ave's)
}	
if ($units == "M") {
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[1][0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[2][0] . '</b> &deg;C</td>' . "\n";
} elseif ($units == "E"){
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[1] . '</b> &deg;F</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][1] . '</b> &deg;F</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[1][1] . '</b> &deg;F</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[2][1] . '</b> &deg;F</td>' . "\n";
} else {
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[1] . '</b> &deg;F  /  &nbsp;<b>' . $current[0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][1] . '</b> &deg;F /  &nbsp;<b>' . $result[0][0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[1][1] . '</b> &deg;F /  &nbsp;<b>' . $result[1][0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[2][1] . '</b> &deg;F /  &nbsp;<b>' . $result[2][0] . '</b> &deg;C</td>' . "\n";
}
?>				
		</tr>
		<tr>
		<td><?php echo $LangSumHeads[1] . ":"; ?></td>
<?php
if ($mode == 1) {
	$result=temp_stats($csvarray, 2); // Dew Point
	$current=convertTemps($csvarray[count($csvarray)-1][2]);
} else {
	$t = temp_stats($csvarray, 4); 
	$result[0] = $t[0];            
	$t = temp_stats($csvarray, 6); 
	$result[1] = $t[1];            
	$t = temp_stats($csvarray, 5); 
	$result[2] = $t[2];            
}	
if ($units == "M") {
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[1][0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[2][0] . '</b> &deg;C</td>' . "\n";
} elseif ($units == "E"){
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[1] . '</b> &deg;F</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][1] . '</b> &deg;F</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[1][1] . '</b> &deg;F</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[2][1] . '</b> &deg;F</td>' . "\n";
} else {
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[1] . '</b> &deg;F  /  &nbsp;<b>' . $current[0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][1] . '</b> &deg;F /  &nbsp;<b>' . $result[0][0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[1][1] . '</b> &deg;F /  &nbsp;<b>' . $result[1][0] . '</b> &deg;C</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[2][1] . '</b> &deg;F /  &nbsp;<b>' . $result[2][0] . '</b> &deg;C</td>' . "\n";
}
?>		
		</tr>
		<tr>
		<td><?php echo $LangSumHeads[2] . ":"; ?></td>
<?php
if ($mode == 1) {
	$result=col_stats($csvarray, 8); // Humidity
} else {
	$t = col_stats($csvarray, 7); 
	$result[0] = $t[0];            
	$t = col_stats($csvarray, 9); 
	$result[1] = $t[1];            
	$t = col_stats($csvarray, 8); 
	$result[2] = $t[2];            
}
if ($mode == 1 && $isToday) echo '<td>' . $csvarray[count($csvarray)-1][8] . '%</td>' . "\n";
echo '<td>' . $result[0] . '%</td>' . "\n";
echo '<td>' . $result[1] . '%</td>' . "\n";
echo '<td>' . round($result[2],0) . '%</td>' . "\n";
?>		
		</tr>
		<tr>
		<td><?php echo $LangSumHeads[3] . ":"; ?></td>
<?php
if ($mode == 1) {
	$result=wind_stats($csvarray, 6); // Wind Speed
	$current=convertWind($csvarray[count($csvarray)-1][6]);
} else {
	$t = wind_stats($csvarray, 12); 
	$result[0] = $t[0];            
	$result[1] = 0;            
	$t = wind_stats($csvarray, 13); 
	$result[2] = $t[2];  
	
	if ($WunderWrong) {					
		if ($rawunits=="metric") $result[2][0] = miTokm($result[2][0],1); //PG 28/01/2009 (WU send mi/h instead of km/h for average wind speed)          
	}					
	
	
/*	
	if ($rawunits=="metric") $result[2][0] = miTokm($result[2][0],1); //PG 28/01/2009 (WU send mi/h instead of km/h for average wind speed)          
*/          
}

if ($units == "M") {
	if ($mode == 1 && $isToday) 	echo '<td class="nobr"><b>' . $current[0] . '</b>km/h</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][0] . '</b>km/h</td>' . "\n";
	if ($result[1] == 0){ echo '<td>-</td>' . "\n";} else{	echo '<td class="nobr"><b>' . $result[1][0] . '</b>km/h</td>' . "\n";}
	echo '<td class="nobr"><b>' . $result[2][0] . '</b>km/h</td>' . "\n";
} elseif ($units == "E"){
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[1] . '</b>mph</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][1] . '</b>mph</td>' . "\n";
	if ($result[1] == 0){ echo '<td>-</td>' . "\n";} else{	echo '<td class="nobr"><b>' . $result[1][1] . '</b>mph</td>' . "\n";}
	echo '<td class="nobr"><b>' . $result[2][1] . '</b>mph</td>' . "\n";
} else {
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[1] . '</b>mph  /  &nbsp;<b>' . $current[0] . '</b>km/h</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][1] . '</b>mph /  &nbsp;<b>' . $result[0][0] . '</b>km/h</td>' . "\n";
	echo '<td>-</td>' . "\n";	
	echo '<td class="nobr"><b>' . $result[2][1] . '</b>mph /  &nbsp;<b>' . $result[2][0] . '</b>km/h</td>' . "\n";
}
?>				
		</tr>
		<tr>
		<td><?php echo $LangSumHeads[4] . ":"; ?></td>
<?php	

if ($mode == 1) {
	$result=wind_stats($csvarray, 7); // Wind Gust
	$current=convertWind($csvarray[count($csvarray)-1][7]);
} else {
	$t = wind_stats($csvarray, 14); 
	$result[0] = $t[0];            
}

if ($units == "M") {
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[0] . '</b>km/h</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][0] . '</b>km/h</td>' . "\n";
	echo '<td>-</td>' . "\n";
	echo '<td>-</td>' . "\n";
} elseif ($units == "E"){
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[1] . '</b>mph</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][1] . '</b>mph</td>' . "\n";
	echo '<td>-</td>' . "\n";
	echo '<td>-</td>' . "\n";
} else {
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[1] . '</b>mph  /  &nbsp;<b>' . $current[0] . '</b>km/h</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][1] . '</b>mph /  &nbsp;<b>' . $result[0][0] . '</b>km/h</td>' . "\n";
	echo '<td>-</td>' . "\n";
	echo '<td>-</td>' . "\n";
}
?>						
		</tr>
<?php
if ($mode == 1) {
	echo '<tr>';
	echo '<td>' . $LangSumHeads[5] . '</td>';
	$result=wDirAvg($csvarray); // Wind Direction
	if ($csvarray[count($csvarray)-1][6]==0) { $current = "Calm"; } else { $current = $csvarray[count($csvarray)-1][4]; }
	if ($mode == 1 && $isToday) echo '<td>' . $current . '</td>' . "\n";
	echo '<td>-</td>' . "\n";
	echo '<td>-</td>' . "\n";
	echo '<td>' . degTotxt($result) . '</td>' . "\n";
	echo '</tr>' . "\n";
}
?>				
		<tr>
		<td><?php echo $LangSumHeads[6] . ":"; ?></td>
<?php
if ($mode == 1) {
	$result=baro_stats($csvarray, 3); // Pressure
	$current=convertBaro($csvarray[count($csvarray)-1][3]);
} else {
	$t = baro_stats($csvarray, 10); 
	$result[0] = $t[0];            
	$t = baro_stats($csvarray, 11); 
	$result[1] = $t[1];            
}
if ($units == "M") {
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[0] . '</b> hPa</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][0] . '</b> hPa</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[1][0] . '</b> hPa</td>' . "\n";
	echo '<td>-</td>' . "\n";
} elseif ($units == "E"){
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[1] . '</b> in</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][1] . '</b> in</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[1][1] . '</b> in</td>' . "\n";
	echo '<td>-</td>' . "\n";
} else {
	if ($mode == 1 && $isToday) echo '<td class="nobr"><b>' . $current[1] . '</b> in  /  &nbsp;<b>' . $current[0] . '</b> hPa</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[0][1] . '</b> in /  &nbsp;<b>' . $result[0][0] . '</b> hPa</td>' . "\n";
	echo '<td class="nobr"><b>' . $result[1][1] . '</b> in /  &nbsp;<b>' . $result[1][0] . '</b> hPa</td>' . "\n";
	echo '<td>-</td>';
}
?>						
		</tr>
		<tr>
		<td><?php echo $LangSumHeads[7] . ":"; ?></td>
<?php
if ($mode == 1) {
	$current=convertRainMM($csvarray[count($csvarray)-1][12]);   
} else {
	$current=total_rain($csvarray, 15);	
}	
	if ($units == "M") {           // Precip
//		echo '<td class="nobr"><b>' . $current[0] * 10 . '</b> mm</td>';        // To fix the cm to mm conversion problem found by Pelle
		echo '<td class="nobr"><b>' . $current[0] . '</b> mm</td>';             // Changed back 8/2011
	} elseif ($units == "E"){
		echo '<td class="nobr"><b>' . $current[1] . '</b> in</td>';
	} else {
//		echo '<td class="nobr"><b>' . $current[1] . '</b> in  /  &nbsp;<b>' . $current[0] * 10 . '</b> mm</td>';        // Same
		echo '<td class="nobr"><b>' . $current[1] . '</b> in  /  &nbsp;<b>' . $current[0] . '</b> mm</td>';        		
	}
?>					
		<td>&nbsp;</td>
		<td>&nbsp;</td>
<?php
if ($mode == 1 && $isToday) {		
		echo '		<td>&nbsp;</td>';
}
?>		
		</tr>
		</tbody>
	</table>
<!--  End Summary Area & Show the Graph -->
<?php } else {                                         // added 12/1/13
	echo '<p>&nbsp;</p><div style="text-align:center;"><h1 style="color:black;">' . $LNoData . '</h1></div><p>&nbsp;</p>';
}
?>
<?php	
if ($mode == 1) {
	$gphrase='type=3';  // Day
} elseif ($mode == 2) {
	$gphrase='type=2';  // Week
} elseif ($mode == 3) {
	$gphrase='type=1';  // Month
} elseif ($mode == 4) {
	$gphrase='type=0';  // Year
} elseif ($mode == 5) {
	$gphrase='type=6&amp;yearend=' . $yr2 . '&amp;monthend=' . $mo2 . '&amp;dayend=' . $da2;  // Custom
}	
?>	
	<div class="taC tm10">
	<img src="<?php echo($WUgraphstr); ?>?day=<?php echo($da); ?>&amp;year=<?php echo($yr); ?>&amp;month=<?php echo($mo); ?>&amp;ID=<?php echo($WUID); ?>&amp;width=<?php echo($gwidth); ?>&amp;showsolarradiation=<?php if ($mode < 3) echo $gsolar?'1':'0';?>&amp;showuv=<?php if ($mode < 3) echo $guv?'1':'0'; ?>&amp;showtemp=<?php echo $gtemp?'1':'0'; ?>&amp;showpressure=<?php echo $gpress?'1':'0'; ?>&amp;showwind=<?php echo $gwind?'1':'0'; ?>&amp;showwinddir=<?php echo $gwindir?'1':'0'; ?>&amp;showrain=<?php echo $grain?'1':'0'; ?>&amp;type=<?php echo($gphrase); ?>" alt="Historical Graphs" id="wxHistoryImage" />
	<div class="noGap">&nbsp;</div>
	</div>
	</div>   <!--div class="colorBox" -->
	<table cellspacing="0" cellpadding="0" class="colorBottom">
		<tr>
		<td class= "bLeft"><div></div></td>
		<td class= "bCenter full"><div></div></td>
		<td class= "bRight"><div></div></td>
		</tr>
	</table>
	<div>&nbsp;</div>
	</td>
<!--  This is the area for the blue box right of the graph -->
<?php
if (file_exists($inboxfile)) {
	echo '<td class="noPrint">';
	echo '  <div class="rightCol">';
	
	echo '  <div class="rtTop"></div>';
	
	echo '    <div class="contentBox taC">';
	include ($inboxfile);
	echo '    </div>';
	
	echo '  <div class="rtBottom"></div>';
	
	if (file_exists($outboxfile)) {
		include ($outboxfile);
	}
	echo '  </div>';
	echo '</td>';
}
?>
		</tr>
	</table>
<?php	
if ($toTop == 1) {       // Optional link to top of the page
	echo '<div class="pageTop noprint"><a href="#">' . $LtopPg . '</a></div>';
}
if (! $skipTab) {       // Begin tabular section with option to suppress
	if ($mode == 1) {
		$bannerphrase = $Ltab1 . ' ' . $mnthname[intval($mo)] . ' ' . $da . ', ' . $yr;
	} elseif ($mode == 2) {
		$bannerphrase = $Ltab2;
	} elseif ($mode == 3) {
		$bannerphrase = $Ltab3;
	} elseif ($mode == 4) {
		$bannerphrase = $Ltab4;
	} else {
		$bannerphrase = $Ltab5;
	}	
		echo '<table cellspacing="0" cellpadding="0" class="colorTop">' . "\n";
			echo '<tr>' . "\n";
				echo '<td class="hLeft"></td>' . "\n";
				echo '<td class="hCenter">' .  $bannerphrase . '</td>' . "\n";
				echo '<td class="hMenu"></td>' . "\n";
				echo '<td class="hRight"></td>' . "\n";
			echo '</tr>' . "\n";
			echo '<tr>' . "\n";
				echo '<td class="sLeft"></td>' . "\n";
				echo '<td class="sCenter"></td>' . "\n";
				echo '<td></td>' . "\n";
				echo '<td class="sRight"></td>' . "\n";
			echo '</tr>' . "\n";
		echo '</table>' . "\n";
		echo '<div class="colorBox">' . "\n";
	   // This is the lower data table when in Daily mode
	if ($mode == 1) {
		echo '<table cellspacing="0" cellpadding="0" class="dailyTable">' . "\n";
		echo '<thead>' . "\n";
		echo '<tr>' . "\n";
		echo '<td>' . $Lheadings[0] . '</td>' . "\n";
		echo '<td>' . $Lheadings[1] . '</td>' . "\n";
		echo '<td>' . $Lheadings[2] . '</td>' . "\n";
		echo '<td>' . $Lheadings[3] . '</td>' . "\n";
		echo '<td>' . $Lheadings[4] . '</td>' . "\n";
		echo '<td>' . $Lheadings[5] . '</td>' . "\n";
		echo '<td>' . $Lheadings[6] . '</td>' . "\n";
		echo '<td>' . $Lheadings[7] . '</td>' . "\n";
		echo '<td>' . $Lheadings[8] . '</td>' . "\n";
		$columns = 10;
		if (! $skipSolar) {
			echo '<td>' . $Lheadings[9] . '</td>' . "\n";
			$columns++;
		}
		if (! $skipSky) {
			echo '<td>' . $Lheadings[10] . '</td>' . "\n";
			$columns++;
		}
		echo '</tr>' . "\n";
		echo '</thead>' . "\n";
		echo '<tbody>' . "\n";
		for ($row=0; $row<count($csvarray); $row++) {
			echo "<tr>" . "\n";
			for ($col=0; $col<$columns; $col++) {
				$data = $csvarray[$row][$col];      
				if ($col == 0) {print "<!-- data='$data' -->"; $data = substr($data,11,5);}  //Date/Time 
				if ($col == 1 || $col == 2){               // Temp or DP
					$convarray = convertTemps($data);
					if ($units == "M") {
						$data = "<b>" . $convarray[0] . "</b> &deg;C";
					} elseif ($units == "E"){
						$data = "<b>" . $convarray[1] . "</b> &deg;F";				
					} else {
						$data = "<b>" . $convarray[1] . "</b> &deg;F / &nbsp;" . "<b>" . $convarray[0] . "</b> &deg;C";
					}
				}
				if ($col == 3) {  // Baro
					$convarray = convertBaro($data);		
					if ($units == "M") {
						$data = "<b>" . $convarray[0] . "</b> hPa";
					} elseif ($units == "E"){
						$data = "<b>" . $convarray[1] . "</b> in";				
					} else {
						$data = "<b>" . $convarray[1] . "</b>in / &nbsp;" . "<b>" . $convarray[0] . "</b>hPa";
					}
				}
				if ($col == 4) { // Wind direction
					if ($csvarray[$row][$col+2]==0) $data = "Calm";
					$col++; // Skip the wind "degrees" column		
				}
				if ($col == 6 || $col == 7){  // Wind or Gust
					$convarray = convertWind($data);		
					if ($data == 0) {
						$data = "&nbsp;";
					} elseif ($units == "M") {
						$data = "<b>" . $convarray[0] . "</b> km/h";
					} elseif ($units == "E"){
						$data = "<b>" . $convarray[1] . "</b> mph";				
					} else {
						$data = "<b>" . $convarray[1] . "</b>mph / &nbsp;" . "<b>" . $convarray[0] . "</b>km/h";
					}
				}
				if ($col == 8) $data = $data . "%";  //Humidity
				if ($col == 9) {  // Rain
					$convarray = convertRainMM($data);		
					if ($units == "M") {
						$data = "<b>" . $convarray[0] . "</b> mm";
					} elseif ($units == "E"){
						$data = "<b>" . $convarray[1] . "</b> in";				
					} else {
						$data = "<b>" . $convarray[1] . "</b>in / &nbsp;" . "<b>" . $convarray[0] . "</b>mm";
					}
/*					
					if ($skipSolar) {
						$col = $col + 1;      // Skip the solar column
					}
					if ($skipSky) {
						$col = $col + 1;      // Skip the condition column
					}
*/					
				}
				if ($col == 10 && ! $skipSolar) {    // Solar
					$data = $csvarray[$row][$col+3]; 
					$data = "<b>" . $data . "</b> W/m<sup>2</sup>";
				}  
				if ($col == 11 && ! $skipSky) {    // Conditions
					$data = $csvarray[$row][$col-1];
				}  
				echo '<td>' . $data . "</td>" . "\n";
			}  // for $col
			echo "</tr>";
		} // for $row
		echo '</tbody>';
		echo '</table>';
	} else {                   // Its weekly, monthly, yearly or custom
		if ($units == "M") {
			$tsym = "&deg;C";
			$bsym = "hPa";
			$dsym = "km/h";
			$vsym = "km";
			$rsym = "cm";
//			$rsym = "mm";                        // Change to mm for Pelle

		} elseif ($units == "E") {
			$tsym = "&deg;F";
			$bsym = "in";
			$dsym = "mph";
			$vsym = "mi";
			$rsym = "in";
		} else {
			$tsym = "&deg;F / &deg;C";
			$bsym = "in / hPa";
			$dsym = "mph / km/h";
			$vsym = "mi / km";
			$rsym = "in / cm";	                  
//			$rsym = "in / mm";	                  // Change to mm for Pelle

		}
		$pmo = substr($csvarray[0][0], 5, 2);
		$pda = substr($csvarray[0][0], strrpos($csvarray[0][0],"-")+1, 2);
		$pyr = substr($csvarray[0][0], 0, 4);
		$columns = 16;
		$needheading = true;
		for ($row=0; $row<count($csvarray); $row++) {
			if ($needheading) {
				$pmo = substr($csvarray[$row][0], 5, 2);
				$pyr = substr($csvarray[$row][0], 0, 4);
				$col1title = $mnthname[intval($pmo)];
				echo '<table cellspacing="0" cellpadding="0" class="obsTable" style="width:100%;">' . "\n";
				echo '<thead>' . "\n";
				echo '<tr>' . "\n";
				echo '<td colspan="2">' . $pyr . '</td>' . "\n";
				echo '<td colspan="3">' . $Lhdngs2[0] . ' (' . $tsym . ')</td>' . "\n";
				echo '<td colspan="3">' . $Lhdngs2[1] . ' (' . $tsym . ')</td>' . "\n";
				echo '<td colspan="3">' . $Lhdngs2[2] . ' (%)</td>' . "\n";
				echo '<td colspan="2">' . $Lhdngs2[3] . ' (' . $bsym . ')</td>' . "\n";
				echo '<td colspan="2">' . $Lhdngs2[4] . ' (' . $dsym . ')</td>' . "\n";
				echo '<td>' . $Lhdngs2[5] . ' (' . $dsym . ')</td>' . "\n";
				echo '<td>' . $Lhdngs2[6] . ' (' . $rsym . ')</td>' . "\n";
				echo '</tr>' . "\n";
				echo '</thead>' . "\n";
				echo '<tbody>' . "\n";
				echo '<tr>' . "\n";
				echo '<td class="b HdgLt" colspan="2">' . $col1title . '</td>' . "\n";
				echo '<td class="HdgDk Left">' . $Lcols2[0] . '</td>' . "\n";
				echo '<td class="HdgDk">' . $Lcols2[1] . '</td>' . "\n";
				echo '<td class="HdgDk Right">' . $Lcols2[2] . '</td>' . "\n";
				echo '<td class="HdgLt">' . $Lcols2[0] . '</td>' . "\n";
				echo '<td class="HdgLt">' . $Lcols2[1] . '</td>' . "\n";
				echo '<td class="HdgLt">' . $Lcols2[2] . '</td>' . "\n";
				echo '<td class="HdgDk Left">' . $Lcols2[0] . '</td>' . "\n";
				echo '<td class="HdgDk">' . $Lcols2[1] . '</td>' . "\n";
				echo '<td class="HdgDk Right">' . $Lcols2[2] . '</td>' . "\n";
				echo '<td class="HdgLt">' . $Lcols2[0] . '</td>' . "\n";
				echo '<td class="taC HdgLt">' . $Lcols2[2] . '</td>' . "\n";
				echo '<td class="HdgDk Left">' . $Lcols2[0] . '</td>' . "\n";
				echo '<td class="HdgDk Right">' . $Lcols2[1] . '</td>' . "\n";
				echo '<td class="HdgLt">' . $Lcols2[0] . '</td>' . "\n";
				echo '<td class="HdgDk Left Right">' . $Lcols2[3] . '</td>' . "\n";
				echo '</tr>' . "\n";
				echo '</tbody>' . "\n";
				$needheading = false;
				echo '<tbody>' . "\n";
			}
			echo '<tr class = "taC">' . "\n";
			for ($col=0; $col<$columns; $col++) {
				$data = $csvarray[$row][$col];      
				if ($col == 0) {
					$pmo = substr($data, 5, 2);
					$pda = substr($data, strrpos($data,"-")+1, 2);
					$pyr = substr($data, 0, 4);
					$tdate = AddDate($pmo, $pda, $pyr, 0); // just to get the name of the day
					if ($row !== count($csvarray) - 1) {  // ie not the last row
						if (substr($data, 0, 7) !== substr($csvarray[$row+1][0], 0, 7)) $needheading = true;
					}
					$data = '<td class = "date">' . substr($tdate['weekday'], 0, 3) . '</td><td class = "date"><a href="' . $PHP_SELF . '?ID=' . $WUID . '&amp;month=' . $pmo . '&amp;day=' .$pda . '&amp;year=' . $pyr . '&amp;mode=1&amp;units=' . $units . '">' . $pda . '</a></td>';  //Date/Time
				}
				if ($col == 1) {
					$convarray = convertTemps($data);
					$data = merge_data($convarray);        // Figures out which measurement is desired & returns it
					$data =  '<td class="BodyDk Left">' . $data . '</td>';  // Temp
				}
				if ($col == 2) {
					$convarray = convertTemps($data);
					$data = merge_data($convarray);        	
					$data =  '<td class="BodyDk">' . $data . '</td>';
				}
				if ($col == 3) {
					$convarray = convertTemps($data);
					$data = merge_data($convarray);        		
					$data =  '<td class="BodyDk Right">' . $data . '</td>';
				}
				if ($col == 4 || $col == 5 || $col == 6) {      // DP
					$convarray = convertTemps($data);
					$data = merge_data($convarray);        		
					$data =  '<td>' . $data . '</td>';  
				}
				if ($col == 7) $data =  '<td class="BodyDk Left">' . $data . '</td>';        // Humidity
				if ($col == 8) $data =  '<td class="BodyDk">' . $data . '</td>';
				if ($col == 9) $data =  '<td class="BodyDk Right">' . $data . '</td>';
				if ($col == 10 || $col == 11) {                                               // Baro
					$convarray = convertBaro($data);
					$data = merge_data($convarray);        		
					$data =  '<td>' . $data . '</td>'; 
				}                          
				if ($col == 12) {                   // Wind
if ($debug)	echo $data . "  ";
					$convarray = convertWind($data);
					$data = merge_data($convarray);        		
					$data =  '<td class="BodyDk Left">' . $data . '</td>';  
				}
				      
				if ($col == 13) {
if ($debug)	echo $data . "  ";
				
					$convarray = convertWind($data);  // Returns an array with km in [0] and mi in [1]
if ($WunderWrong) {					
					if ($rawunits == "metric") $convarray[0] = miTokm($convarray[0],1);  // Wunderground is sending in mph vs km/h
}					
					$data = merge_data($convarray);     
/*					
					if ($rawunits == "metric") $data = miTokm($data,1); // PG 28/01/2009 (WU send mi/h instead of km/h for average wind speed)      					
*/					   					
					$data =  '<td class="BodyDk Right">' . $data . '</td>';
				}
			
				
				if ($col == 14) {                       // Gust
if ($debug)	echo $data . " / ";
				
					$convarray = convertWind($data);
					$data = merge_data($convarray);        								
					$data =  '<td>' . $data . '</td>';  
				}                         
				if ($col == 15) {                        // Rain
//					$convarray = convertRainCM($data);                  // changed 8/2010
					$convarray = convertRainMM($data);
					$data = merge_data($convarray);        											
					$data =  '<td class="BodyDk Left Right">' . $data . '</td>';  
				}
				echo $data;
			} // Columns
			echo '</tr>';
			if ($needheading) {
				echo '</tbody>';
				echo '</table>';			
			}
		} // Rows
		echo '</tbody>';
		echo '</table>';
	}
		echo '<table cellspacing="0" cellpadding="10" class="center noprint">' . "\n";
			echo '<tr>' . "\n";
			echo '<td id="csvLink" class="taC" style="width: 50%;"><a href="javascript:void(window.open(\'' . $wunderCSVstring . '\'))" class="subLink">' . $Lcommafile . '</a></td>' . "\n";
			echo '</tr>' . "\n";
		echo '</table>' . "\n";
		echo '</div>' . "\n";  //<!-- class="colorBox" -->
		echo '<table cellspacing="0" cellpadding="0" class="colorBottom">' . "\n";
			echo '<tr>' . "\n";
			echo '<td class= "bLeft"><div></div></td>' . "\n";
			echo '<td class= "bCenter full"><div></div></td>' . "\n";
			echo '<td class= "bRight"><div></div></td>' . "\n";
			echo '</tr>' . "\n";
		echo '</table>' . "\n";
 } // End of skipping tabular data 
	  echo '</div>' . "\n";  //<!-- class="colorBox" -->
// <!-- blue credits bar -->
if ($toTop == 1) {       // Optional link to top of the page
	echo '<div class="pageTop noprint"><a href="#">' . $LtopPg . '</a></div>';
}
if ($footer and $LangFtext <> '') {  
	echo '<div class="bottomBar">' . $LangFtext . '</div>';
}
?>  
	<table class="center tm20 noprint">
		<tr>
			<td class = "logo"></td>
			<td><?php echo $Lthanks . ' '; ?><a href="//www.wunderground.com"> Weather Underground</a> - <?php echo convert(memory_get_usage(true)); ?></td>
		</tr>
	</table>
  </td>
</tr>
</table>
</div>  <!-- id="wuwrap" -->
<?php
// End of the script all functions follow
function convert($size)
 {
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
 }
function merge_data($arr) { // Takes an array and returns data in the proper units
global $units;
	if ($units == "M") {
		$data = $arr[0];
	} elseif ($units == "E"){
		$data =$arr[1];				
	} else {
		$data = $arr[1] . " / &nbsp;" . $arr[0];
	}
return $data;	
}

function col_stats($arr, $col) {
//Returns array $return with Hi Lo & Ave in [0],[1]&[2]
	$hi=$arr[0][$col];
	$lo=$arr[0][$col];
	$tot=0;
	for ($row=0; $row<count($arr); $row++) {
		$tot = $tot + $arr[$row][$col];
		if ($arr[$row][$col] > $hi) $hi=$arr[$row][$col];
		if ($arr[$row][$col] < $lo) $lo=$arr[$row][$col];		
	}
	$return[0] = $hi;
	$return[1] = $lo;
	$return[2] = round($tot / count($arr),1);
	return $return;
}

function temp_stats($arr, $col) {
//Returns array $return with Hi Lo & Ave in [0],[1]&[2]
//Each of those is an array with C in 0 and F in 1
	$hi=$arr[0][$col];
	$lo=$arr[0][$col];
	$tot=0;
	for ($row=0; $row<count($arr); $row++) {
		$tot = $tot + $arr[$row][$col];
		if ($arr[$row][$col] > $hi) $hi=$arr[$row][$col];
		if ($arr[$row][$col] < $lo) $lo=$arr[$row][$col];		
	}
	$return[0] = convertTemps($hi);
	$return[1] = convertTemps($lo);
	$return[2] = convertTemps(round($tot / count($arr),1));
	return $return;
}
   
function convertTemps($raw) {
// Returns an array with C in [0] and F in [1]
global $rawunits;
if ($rawunits == "english") {
	$return[0] = FtoC($raw,1);
	$return[1] = $raw;  
} else {
	$return[0] = $raw;
	$return[1] = CtoF($raw,1);
}
return $return;
}

function CtoF ($cTemp, $prec=0) {
	$prec = (integer)$prec;
	$fTemp = (float)(1.8 * $cTemp) + 32;
	return round($fTemp, $prec);
}

function FtoC ($fTemp, $prec=0) {
  $prec = (integer)$prec;
  $cTemp = (float)(($fTemp - 32) / 1.8 );
  return round($cTemp, $prec);
}

function baro_stats($arr, $col) {
//Returns array $return with Hi Lo & Ave in [0],[1]&[2]
//Each of those is an array with mb in 0 and inches in 1
	$hi=$arr[0][$col];
	$lo=$arr[0][$col];
	$tot=0;
	for ($row=0; $row<count($arr); $row++) {
		$tot = $tot + $arr[$row][$col];
		if ($arr[$row][$col] > $hi) $hi=$arr[$row][$col];
		if ($arr[$row][$col] < $lo) $lo=$arr[$row][$col];		
	}
	$return[0] = convertBaro($hi);
	$return[1] = convertBaro($lo);
	$return[2] = convertBaro(round($tot / count($arr),1));
	return $return;
}

function convertBaro($raw) {
// Returns an array with mb in [0] and inches in [1]
global $rawunits;
if ($rawunits == "english") {
	$return[0] = inTomb($raw,1);
	$return[1] = $raw;  
} else {
	$return[0] = $raw;
	$return[1] = mbToin($raw,1);
}
return $return;
}

function inTomb ($inch, $prec=0) {
  $prec = (integer)$prec;
  $mb = (float)$inch * 33.86;
  return round($mb, $prec);
}

function mbToin ($mb, $prec=0) {
  $prec = (integer)$prec;
  $in = (float)$mb / 33.86;
  return round($in, $prec);
}

function wind_stats ($arr, $col) {
//Returns array $return with Hi Lo & Ave in [0],[1]&[2]
//Disregards 0 or "calm" entries
	$hi=$arr[0][$col];
	$lo=$arr[0][$col];
	$tot=0;
	$num=0;
	for ($row=0; $row<count($arr); $row++) {
		//if ($arr[$row][$col] != 0) {   // Suggestion by Paul Gogan
			$num++;
			$tot = $tot + $arr[$row][$col];
			if ($arr[$row][$col] > $hi) $hi=$arr[$row][$col];
			if ($arr[$row][$col] < $lo) $lo=$arr[$row][$col];	
		//}
	}
	$return[0] = convertWind($hi);
	$return[1] = convertWind($lo);
	$return[2] = convertWind(round($tot / $num,1));
	return $return;
}

function convertWind($raw) {
// Returns an array with km in [0] and mi in [1]
global $rawunits;
if ($rawunits == "english") {
	$return[0] = miTokm($raw,1);
	$return[1] = $raw;  
} else {
	$return[0] = $raw;
	$return[1] = kmTomi($raw,1);
}
return $return;
}

function wDirAvg($arr) {
//Returns average wind direction
//Disregards 0 or "calm" entries
	$tot=0;
	$num=0;
	for ($row=0; $row<count($arr); $row++) {
		if ($arr[$row][6] != 0) {
			$num++;
			$tot = $tot + $arr[$row][5];
		}
	}
	return round($tot / $num,0);
}

function degTotxt ( $wind ) { 
  if ($wind >= 0 ) $return = "North"; 
  if ($wind > 10 ) $return = "NNE"; 
  if ($wind > 33 ) $return = "NE"; 
  if ($wind > 55 ) $return = "ENE"; 
  if ($wind > 78 ) $return = "East"; 
  if ($wind > 100 ) $return = "ESE"; 
  if ($wind > 123 ) $return = "SE"; 
  if ($wind > 145 ) $return = "SSE"; 
  if ($wind > 168 ) $return = "South"; 
  if ($wind > 190 ) $return = "SSW"; 
  if ($wind > 213 ) $return = "SW"; 
  if ($wind > 235 ) $return = "WSW"; 
  if ($wind > 258 ) $return = "West"; 
  if ($wind > 280 ) $return = "WNW"; 
  if ($wind > 303 ) $return = "NW"; 
  if ($wind > 325 ) $return = "NNW"; 
  if ($wind > 348 ) $return = "North"; 
  return $return;
}

function miTokm ($mile, $prec=0) {
  $prec = (integer)$prec;
  $km = (float)$mile * 1.6093;
  return round($km, $prec);
}

function kmTomi ($km, $prec=0) {
  $prec = (integer)$prec;
  $mi = (float)$km * .6213;
  return round($mi, $prec);
}

function total_rain($arr, $col) {
//Returns the total rain in a column in an array
//When in metric mode, wunderground gives us rain in cm
global $rawunits;
$tot=0;
for ($row=0; $row<count($arr); $row++) {
	$tot = $tot + $arr[$row][$col];  // Total them up.  Might be in or cm
}
$return = convertRainCM($tot);  // Get array with cm & in
$return[0] = $return[0] * 10;   //Change to mm for summary  
return $return;
}

function convertRainCM($raw) {
// Returns an array with cm in [0] and inches in [1]
global $rawunits;
if ($rawunits == "english") {
	$return[0] = inTocm($raw, 2);
	$return[1] = $raw;  
} else {
	$return[0] = $raw;
	$return[1] = cmToin($raw, 2);
}
if ($return[0] < 0) {  // For Steve (Stumpy's) -990.00 data problem)
	$return[0] = 0;
	$return[1] = 0;	
}
return $return;
}

function convertRainMM($raw) {
// Returns an array with mm in [0] and inches in [1]
global $rawunits;
if ($rawunits == "english") {
	$return[0] = inTomm($raw, 2);
	$return[1] = $raw;          
} else {
	$return[0] = $raw;                        
	$return[1] = mmToin($raw, 2);
}
if ($return[0] < 0) {  // For Steve (Stumpy's) -990.00 data problem)
	$return[0] = 0;
	$return[1] = 0;	
}
return $return;
}

function inTocm ($inch, $prec)
{
  $prec = (integer)$prec;
  $cm = (float)$inch * 2.54;
  return round($cm, $prec);
}

function cmToin ($cm, $prec)
{
  $prec = (integer)$prec;
  $inch = (float)$cm * 0.39;
  return round($inch, $prec);
}


function inTomm ($inch, $prec=1) {
  $prec = (integer)$prec;
  $mm = (float)$inch * 25.4;
  return round($mm, $prec);
}

function mmToin ($mm, $prec=1) {
  $prec = (integer)$prec;
  $in = (float)$mm * .0394;
  return round($in, $prec);
}

function AddDate ( $month, $day, $year, $numdays) {
	$nday = $day + $numdays;
	$newdate = mktime (0,0,0,$month,$nday,$year);
	return getdate($newdate);
}	
/*	Returns Array (
    [seconds] => 40
    [minutes] => 58
    [hours]   => 21
    [mday]    => 17
    [wday]    => 2
    [mon]     => 6
    [year]    => 2003
    [yday]    => 167
    [weekday] => Tuesday
    [month]   => June
    [0]       => 1055901520*/
	
function array_pure ($input) {  // Bad hack to pick out the lines w/o "\r\n" by picking known content
	$i = 0;
	while($i < count($input)) {
		if( isset($input[$i][0][0]) and ( $input[$i][0][0] == "2" || $input[$i][0][0] == "T") ) {
			$return[] = $input[$i];
		}
	$i++;
	}
return $return;
} 

function getcsvWithoutHanging($url)   {
  global $needCookie;
  $overall_start = time();
  $Status = '';
  if (true) {
   // Set maximum number of seconds (can have floating-point) to wait for feed before displaying page without feed
   $numberOfSeconds=4;   

// Thanks to Curly from ricksturf.com for the cURL fetch functions

  $data = '';
  $domain = parse_url($url,PHP_URL_HOST);
  $theURL = str_replace('nocache','?'.$overall_start,$url);        // add cache-buster to URL if needed
  $Status .= "<!-- curl fetching '$theURL' -->\n";
  $ch = curl_init();                                           // initialize a cURL session
  curl_setopt($ch, CURLOPT_URL, $theURL);                         // connect to provided URL
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);                 // don't verify peer certificate
  curl_setopt($ch, CURLOPT_USERAGENT, 
    'Mozilla/5.0 (ec-forecast.php - saratoga-weather.org)');
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $numberOfSeconds);  //  connection timeout
  curl_setopt($ch, CURLOPT_TIMEOUT, $numberOfSeconds);         //  data timeout
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);              // return the data transfer
  curl_setopt($ch, CURLOPT_NOBODY, false);                     // set nobody
  curl_setopt($ch, CURLOPT_HEADER, true);                      // include header information
  if (isset($needCookie[$domain])) {
    curl_setopt($ch, $needCookie[$domain]);                    // set the cookie for this request
    curl_setopt($ch, CURLOPT_COOKIESESSION, true);             // and ignore prior cookies
    $Status .=  "<!-- cookie used '" . $needCookie[$domain] . "' for GET to $domain -->\n";
  }

  $data = curl_exec($ch);                                      // execute session

  if(curl_error($ch) <> '') {                                  // IF there is an error
   $Status .= "<!-- Error: ". curl_error($ch) ." -->\n";        //  display error notice
  }
  $cinfo = curl_getinfo($ch);                                  // get info on curl exec.
/*
curl info sample
Array
(
[url] => http://saratoga-weather.net/clientraw.txt
[content_type] => text/plain
[http_code] => 200
[header_size] => 266
[request_size] => 141
[filetime] => -1
[ssl_verify_result] => 0
[redirect_count] => 0
  [total_time] => 0.125
  [namelookup_time] => 0.016
  [connect_time] => 0.063
[pretransfer_time] => 0.063
[size_upload] => 0
[size_download] => 758
[speed_download] => 6064
[speed_upload] => 0
[download_content_length] => 758
[upload_content_length] => -1
  [starttransfer_time] => 0.125
[redirect_time] => 0
[redirect_url] =>
[primary_ip] => 74.208.149.102
[certinfo] => Array
(
)

[primary_port] => 80
[local_ip] => 192.168.1.104
[local_port] => 54156
)
*/
  $Status .= "<!-- HTTP stats: " .
    " RC=".$cinfo['http_code'] .
    " dest=".$cinfo['primary_ip'];
	if(isset($cinfo['primary_port'])) {
	  $Status .= " port=".$cinfo['primary_port'] ;
	}
	if(isset($cinfo['local_ip'])) {
	  $Status .= " (from sce=" . $cinfo['local_ip'] . ")";
	}
	$Status .= 
	"\n      Times:" .
    " dns=".sprintf("%01.3f",round($cinfo['namelookup_time'],3)).
    " conn=".sprintf("%01.3f",round($cinfo['connect_time'],3)).
    " pxfer=".sprintf("%01.3f",round($cinfo['pretransfer_time'],3));
	if($cinfo['total_time'] - $cinfo['pretransfer_time'] > 0.0000) {
	  $Status .=
	  " get=". sprintf("%01.3f",round($cinfo['total_time'] - $cinfo['pretransfer_time'],3));
	}
    $Status .= " total=".sprintf("%01.3f",round($cinfo['total_time'],3)) .
    " secs -->\n";

  //$Status .= "<!-- curl info\n".print_r($cinfo,true)." -->\n";
  curl_close($ch);                                              // close the cURL session
  //$Status .= "<!-- raw data\n".$data."\n -->\n"; 
  $i = strpos($data,"\r\n\r\n");
  $headers = substr($data,0,$i);
  $content = substr($data,$i+4);
  $Status .= "<!-- headers:\n".$headers."\n -->\n";
  print $Status;  
  return $content;                                                 // return contents

 } else {
//   print "<!-- using file_get_contents function -->\n";
   $STRopts = array(
	  'http'=>array(
	  'method'=>"GET",
	  'protocol_version' => 1.1,
	  'header'=>"Cache-Control: no-cache, must-revalidate\r\n" .
				"Cache-control: max-age=0\r\n" .
				"Connection: close\r\n" .
				"User-agent: Mozilla/5.0 (ec-forecast.php - saratoga-weather.org)\r\n" .
				"Accept: text/plain,text/html\r\n"
	  ),
	  'https'=>array(
	  'method'=>"GET",
	  'protocol_version' => 1.1,
	  'header'=>"Cache-Control: no-cache, must-revalidate\r\n" .
				"Cache-control: max-age=0\r\n" .
				"Connection: close\r\n" .
				"User-agent: Mozilla/5.0 (ec-forecast.php - saratoga-weather.org)\r\n" .
				"Accept: text/plain,text/html\r\n"
	  )
	);
	
   $STRcontext = stream_context_create($STRopts);

   $T_start = WU_fetch_microtime();
   $xml = file_get_contents($url,false,$STRcontext);
   $T_close = WU_fetch_microtime();
   $headerarray = get_headers($url,0);
   $theaders = join("\r\n",$headerarray);
//   $xml = $theaders . "\r\n\r\n" . $xml;

   $ms_total = sprintf("%01.3f",round($T_close - $T_start,3)); 
   $Status .= "<!-- file_get_contents() stats: total=$ms_total secs -->\n";
   $Status .= "<-- get_headers returns\n".$theaders."\n -->\n";
//   print " file() stats: total=$ms_total secs.\n";
   $overall_end = time();
   $overall_elapsed =   $overall_end - $overall_start;
   $Status .= "<!-- fetch function elapsed= $overall_elapsed secs. -->\n"; 
//   print "fetch function elapsed= $overall_elapsed secs.\n"; 
   print $Status;
   return($xml);
 }

}    // end ECF_fetch_URL

// ------------------------------------------------------------------

function WU_fetch_microtime()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

function getCurrentPageURL($queryString = false) {
	// Code from: https://www.skptricks.com/2018/03/how-to-get-current-page-url-using-php.html
   $port = $_SERVER['SERVER_PORT'];
            
   $url  = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $port == 443) ? "https://" : "http://";
   $url .= $_SERVER['HTTP_HOST'];
   $url .= ($port == '80' || $port == '443' ? '' :  ':' . $port);
   $url .= $_SERVER['PHP_SELF'];
   $url .= ($queryString == true ? '?' . $_SERVER['QUERY_STRING'] : '');
            return $url;
}
?>
