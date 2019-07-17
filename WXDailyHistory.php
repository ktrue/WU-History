<?php
#--------------------------------------------------------------------------------------
# this is a shim program to replace the https://www.wunderground.com/weatherstation/WXDailyHistory.asp
# program what stopped working 18-May-2019 with queries to the PWS WU/TWC API JSON data and to return
# the data as the original program did (in a poorly formatted Text/CSV file).
# To speed future accesses, a cache file of the JSON is maintained as
# ./cache/wuYYYYMM-{WUID}-{WUUNITS}.json  like:
# ./cache/wu201905-KCASARAT1-e.json (for my April, 2019 month data
#
# 'day','week' data is not cached
# 'month' data is cached, and current month data is always fetched and cached as today's data changes
# 'year' data is cached (and current month data is always fetched and cached)
# additional logic is included to automatically refresh the prior month's data if that file was not captured
# during the current month.
#
# NOTE: the daily JSON does not include the former Conditions nor Clouds variables, so those are set to null ('')
# Also, the PWS API has a hard limit of 1500 calls/day and rate of 30 calls/minute.
# 
# Author: Ken True  webmaster@saratoga-weather.org 23-May-2019
# 
# Version 1.00 - 23-May-2019 - initial release
# Version 1.10 - 30-May-2019 - added cache files for day/week/month to rate-limit API calls
# Version 1.20 - 03-Jun-2019 - change to cache day files wu-YYYYMMDD-<WUID>-<WUunits>.json update today/yesterday only
# Version 1.21 - 07-Jun-2019 - added API bug bypass for today date != today UTC date day data calls
# Version 1.22 - 17-Jul-2019 - fix for bad local/epoch dates in API prior to 2018-07-01
#
#--------------------------------------------------------------------------------------
$Version = "WXDailyHistory.php Version 1.21 - 17-Jul-2019";
#
# ------------------------ settings -----------------------
$WUID = 'KCASARAT1';   // your Wunderground PWS ID
$WCAPIkey = 'specify-for-standalone-use-here'; // use this only for standalone / non-template use
$WCunits  = 'e';  // 'e'= US units F,mph,inHg,in,in
//$WCunits  = 'm';  // 'm'= metric   C,km/h,hPa,mm,cm
//$WCunits  = 'h';  // 'h'= UK units C,mph,mb,mm,cm
//$WCunits  = 's';  // 's'= SI units C,m/s,hPa,mm,cm
$ourTZ = 'America/Los_Angeles'; // our timezone
$cacheFileDir = './cache/';  // use './' to store in current directory
$refreshSecondsDay = 150;  // limit API calls to every 300 seconds (2.5 minutes) for day
$refreshSeconds = 1800; // limit API calls for week/month/year data to every 1/2 hour
# ------------------- end of settings ----------------------
// overrides from Settings.php if available
if(file_exists("Settings.php")) {include_once("Settings.php"); }
global $SITE;
if (isset($SITE['WUID']))     {$WUID = $SITE['WUID'];}
if (isset($SITE['WCAPIkey'])) {$WCAPIkey = $SITE['WCAPIkey']; } 
if (isset($SITE['WCunits']))  {$WCunits = $SITE['WCunits']; } 
if (isset($SITE['tz']))       {$ourTZ = $SITE['tz'];}
if (isset($SITE['cacheFileDir'])) {$cacheFileDir = $SITE['cacheFileDir']; }

/*
Old queries  to the WU page providing the data looked like
$WUdatastr = "https://www.wunderground.com/weatherstation/WXDailyHistory.asp";   
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

where 

	$mo = date("m");
	$da = date("d");
	$yr = date("Y");

*/
// -------------------begin code ------------------------------------------


if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
   //--self downloader --
   $filenameReal = __FILE__;
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain;charset=ISO-8859-1");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   
   readfile($filenameReal);
   exit;
}

$Status = "<!-- $Version on PHP ".phpversion()." -->\n";
//------------------------------------------------

if(preg_match('|specify|i',$WCAPIkey)) {
	print "<p>Note: the WXDailyHistory script requires an API key from WeatherUnderground to operate.<br/>";
	print "Visit <a href=\"https://www.wunderground.com/member/api-keys\">Weather Underground</a> to ";
	print "register for an API key. You must have a PWS submitting data to WU to acquire an API key.</p>\n";
	if( isset($SITE['fcsturlWC']) ) {
		print "<p>Insert in Settings.php an entry for:<br/><br/>\n";
		print "\$SITE['WCAPIkey'] = '<i>your-key-here</i>';<br/><br/>\n";
		print "replacing <i>your-key-here</i> with your WU/TWC API key.</p>\n";
	}
	return;
}

date_default_timezone_set($ourTZ);
$Status = '';
global $Status, $doDebug;

$doDebug = isset($_REQUEST['debug'])?true:false;

if(isset($_REQUEST['ID'])) {
	$t = $_REQUEST['ID'];
	if(preg_match('!^([A..Z0..9]+)$!',$t,$m)) {
		$WUID = $m[1];
	}
}

$reqtype = 'day';

$reqtypeValid = array(
 'day' => true,
 'week' => true,
 'month' => true,
 'year' => true, // not yet implemented
 'custom' => false // not yet implemented
 );
 
$Jtypes = array( // the JSON detail data is returned under one of the following section names in the JSON
  'e' => 'imperial',
	'm' => 'metric',
	's' => 'metric_si',
	'h' => 'uk_hybrid'
);

$JSONsection = $Jtypes[$WCunits];
 
if( isset($_REQUEST['graphspan']) and 
    isset($reqtypeValid[$_REQUEST['graphspan']]) ) {
		$reqtype = $_REQUEST['graphspan'];
}
if (empty($_REQUEST['force'])) $_REQUEST['force'] = 0;
$Force = $_REQUEST['force'];
if($Force > 0) {$forceUpdate = true;} else {$forceUpdate = false; }

if(!$reqtypeValid[$reqtype]) { // oops, a non implemented one selected
  header('Content-type: text/plain;charset=ISO-8859-1');
  print '';
	return;
}
	$mo = date("m");
	$da = date("d");
	$yr = date("Y");
	$todayYMD = "$yr$mo$da";
  
if(isset($_REQUEST['month']) and is_numeric($_REQUEST['month'])) {
	if(preg_match('!^(\d{1,2})$!',$_REQUEST['month'],$m)) {
		$mo = $m[1];
		if(strlen($mo) < 2) {$mo = '0'.$mo;}
	}
}
if(isset($_REQUEST['day'])) {
	if(preg_match('!^(\d{1,2})$!',$_REQUEST['day'],$m)) {
		$da = $m[1];
		if(strlen($da) < 2) {$da = '0'.$da;}
	}
}

if(isset($_REQUEST['year'])) {
	if(preg_match('!^(\d{4})$!',$_REQUEST['year'],$m)) {
		$yr = $m[1];
	}
}

$ymd = "$yr$mo$da";

if($reqtype == 'day') {
 //day
   $todayYMDUTC = gmdate('Ymd'); // Bug bypass for when today date !== UTC date and query is for today
	 $lookFor = $ymd;
	 if($ymd == $todayYMD and $todayYMD !== $todayYMDUTC) {
		$Status .= "<!-- using $todayYMDUTC for query to bypass bug -->\n";
		$lookFor = $todayYMDUTC;
	 }
		// end API bug handling code for today's data
		$url = 'https://api.weather.com/v2/pws/history/all?stationId='.$WUID.'&format=json&units='.$WCunits.'&date='.$lookFor.'&apiKey='.$WCAPIkey;
		$priorYMD = date('Ymd',strtotime('yesterday'));
		$cacheFileName = $cacheFileDir."wuday-$ymd-$WUID-$WCunits.json";
		$saveCache = false;
		$doFetch   = false;
		if($forceUpdate or                  // always fetch if force=1
		   !file_exists($cacheFileName) or
			 ($ymd == $priorYMD and           // fetch if yesterday file not complete
			  file_exists($cacheFileName) and 
				filemtime($cacheFileName) < strtotime('yesterday 23:59:59')
			 ) or 
			 (file_exists($cacheFileName) and // fetch if today file cache lifetime expires
			  $ymd == date('Ymd') and
				filemtime($cacheFileName) + $refreshSecondsDay < time()
				) 
		  ) {
			$doFetch = true;
		}
		
		if(!$doFetch) {
			$data = file_get_contents($cacheFileName);
			$Status .= "<!-- day cache loaded from $cacheFileName -->\n";
		} else {
		  $data = WUJCSV_fetchUrlWithoutHanging($url);
			$saveCache = true;
		}
		$outdata = WUJSON_decode('day',$data,$WCunits);
		if($saveCache and strlen($data) > 100) {
			file_put_contents($cacheFileName,$data);
			$Status .= "<!-- day cache $cacheFileName updated. ".strlen($data)." bytes saved. -->\n";
		} 
		if(strlen($outdata) > 0) {
			header('Content-type: text/plain;charset=ISO-8859-1');
			print $outdata;
		}
}

if($reqtype == 'week') {
 //week
		$url = 'https://api.weather.com/v2/pws/observations/hourly/7day?stationId='.$WUID.'&format=json&units='.$WCunits .
           '&apiKey='.$WCAPIkey;
		$cacheFileName = $cacheFileDir."wuweek-$WUID-$WCunits.json";
		$saveCache = false;
		if(!$forceUpdate and file_exists($cacheFileName) and filemtime($cacheFileName) + $refreshSeconds > time()) {
			$data = file_get_contents($cacheFileName);
			$Status .= "<!-- week cache loaded from $cacheFileName -->\n";
		} else {
		    $data = WUJCSV_fetchUrlWithoutHanging($url);
			$saveCache = true;
		}
		 
		$outdata = WUJSON_decode('week',$data,$WCunits);
		if($saveCache and strlen($data) > 100) {
			file_put_contents($cacheFileName,$data);
			$Status .= "<!-- week cache $cacheFileName updated. ".strlen($data)." bytes saved. -->\n";
		} 
		if(strlen($outdata) > 0) {
			header('Content-type: text/plain;charset=ISO-8859-1');
			print $outdata;
		}
}


if($reqtype == 'month') {
	// Month processing is a bit complicated .. need to cache the month files
	  $tDate = date('F Y',strtotime("$yr-$mo-$da"));
	  $sDate = date('Ymd',strtotime('first day of '.$tDate));
	  $eDate = date('Ymd',strtotime('last day of '.$tDate));
		$tYM = substr($sDate,0,6);
	  $nowYM = date('Ym');
		$priorMonthTS = strtotime('last day of previous month 23:59:59');
		$priorYM = date('Ym',$priorMonthTS);
		$fetchPriorMonth = false;
		$cacheFileName = $cacheFileDir."wu$tYM-$WUID-$WCunits.json";
		$priorCacheFileName = $cacheFileDir."wu$priorYM-$WUID-$WCunits.json";
		if(file_exists($priorCacheFileName) and 
		   filemtime($priorCacheFileName) <= $priorMonthTS) {
				 // yes, prior month doesn't have full file contents
				 $fetchPriorMonth = true;
				 if($doDebug) {
					 $Status .= "<!-- note: $priorCacheFileName is stale -- updating -->\n";
				 }
			 }
		if($doDebug) {
	    $Status .= "<!-- yr='$yr' mo='$mo' da='$da' tDate = '$tDate' sDate='$sDate' eDate='$eDate' -->\n";
		  $Status .= "<!-- priorMonthTS='$priorMonthTS' ='".date('Y-m-d H:i:s T',$priorMonthTS)."' priorYM='$priorYM' -->\n";
		}
		$url = 'https://api.weather.com/v2/pws/history/daily?stationId='.$WUID.'&format=json&units='.$WCunits.  
		'&startDate='.$sDate.'&endDate='.$eDate. '&apiKey='.$WCAPIkey;
		if($forceUpdate or !file_exists($cacheFileName) or 
		  ($tYM == $nowYM and filemtime($cacheFileName)+$refreshSeconds < time()) or 
			($tYM == $priorYM and $fetchPriorMonth )) {
		    $data = WUJCSV_fetchUrlWithoutHanging($url);
			  if(preg_match('|observations|s',$data)) {
				  file_put_contents($cacheFileName,$data);
				  $Status .= "<!-- saved $tYM data into $cacheFileName -->\n";
			  }
		} else {
			$data = file_get_contents($cacheFileName);
			$Status .= "<!-- fetched $tYM data from $cacheFileName -->\n";
		}
		$outdata = WUJSON_decode('month',$data,$WCunits);
		
		if(strlen($outdata) > 0) {
			header('Content-type: text/plain;charset=ISO-8859-1');
			print $outdata;
		}
}

if($reqtype == 'year') {
	set_time_limit(45);
  // this is complicated.. have to do month queries and merge results
	$Months = array('January','February','March','April','May','June',
     'July','August','September','October','November','December');
	$firstDate = array();
	$lastDate = array();
	$nowYMD = date('Ymd');
	$nowYM = substr($nowYMD,0,6);
	$priorMonthTS = strtotime('last day of previous month 23:59:59');
	$priorYM = date('Ym',$priorMonthTS);
	$fetchPriorMonth = false;
	$priorCacheFileName = $cacheFileDir."wu$priorYM-$WUID-$WCunits.json";
	if($doDebug) {
	  $Status .= "<!-- priorMonthTS='$priorMonthTS' ='".date('Y-m-d H:i:s T',$priorMonthTS)."' priorYM='$priorYM' -->\n";
	}
	if(file_exists($priorCacheFileName) and 
		 filemtime($priorCacheFileName) <= $priorMonthTS) {
			 // yes, prior month doesn't have full file contents
			 $fetchPriorMonth = true;
			 if($doDebug) {
				 $Status .= "<!-- note: $priorCacheFileName is stale -- updating -->\n";
			 }
		 }
	
	$outdata = '';
	header('Content-type: text/plain;charset=ISO-8859-1');
	foreach ($Months as $n => $moName) {
		$tFirst = sprintf('%04d%02d%02d',$yr,$n+1,1);
		$tYM = substr($tFirst,0,6);
		
		if($tFirst > $nowYMD) { break; }
		$firstDate[$n] = $tFirst;
		$lastDate[$n] = date('Ymd',strtotime('last day of '.$moName.' '.$yr));
//	  if($doDebug) { print "$moName from $firstDate[$n] to $lastDate[$n]<br/>\n";}
    $sDate = $firstDate[$n];
		$eDate = $lastDate[$n];
		$url = 'https://api.weather.com/v2/pws/history/daily?stationId='.$WUID.'&format=json&units='.$WCunits.  
		'&startDate='.$sDate.'&endDate='.$eDate. '&apiKey='.$WCAPIkey;

		$cacheFileName = $cacheFileDir."wu$tYM-$WUID-$WCunits.json";
		if($doDebug) {
			$Status .= "<!-- nowYM='$nowYM' tYM='$tYM' priorYM='$priorYM' fetch='$fetchPriorMonth' -->\n";
		}
		if($forceUpdate or !file_exists($cacheFileName) or 
		  ($tYM == $nowYM and filemtime($cacheFileName)+$refreshSeconds < time() ) or 
			($tYM == $priorYM and $fetchPriorMonth )) {
		    $data = WUJCSV_fetchUrlWithoutHanging($url);
		    sleep(1); // wait a bit to not pound the api the first time
			  if(preg_match('|observations|s',$data)) {
				  file_put_contents($cacheFileName,$data);
				  $Status .= "<!-- saved $tYM data into $cacheFileName -->\n";
			  }
		} else {
			$data = file_get_contents($cacheFileName);
			$Status .= "<!-- fetched $tYM data from $cacheFileName -->\n";
		}
		
		$doHeader = ($moName == 'January')?true:false;
		$outdata .= WUJSON_decode('month',$data,$WCunits,$doHeader);
	}
	if(strlen($outdata) > 0) {
		print $outdata;
	}
	
	
}

if($doDebug) { print $Status; }

function WUJSON_decode($type,$json,$unit,$doHeader=true) {
	// try to make a CVS-style data file from the WU/TWC API return
	global $JSONsection;
	$J = json_decode($json,true);
	$compass = array('N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW');

// ----------------------------------------------------------------------
	if($type == 'day') { // create a daily-formatted file 
	  if(!isset($J['observations'])) {
			return ('');
		}
/* make it look like:

Time,TemperatureF,DewpointF,PressureIn,WindDirection,WindDirectionDegrees,WindSpeedMPH,WindSpeedGustMPH,Humidity,HourlyPrecipIn,Conditions,Clouds,dailyrainin,SolarRadiationWatts/m^2,SoftwareType,DateUTC<br>
2019-03-05 00:04:48,50.4,47.0,30.01,NNW,330,0.0,0.0,88,0.00,OVC,,0.00,0.00,WeatherDisplay:10.37,2019-03-05 08:04:48,
<br>
2019-03-05 00:09:49,50.4,47.0,30.01,NNW,330,0.0,0.0,88,0.00,OVC,,0.00,0.00,WeatherDisplay:10.37,2019-03-05 08:09:49,
<br>

from entries like:

{
  "observations": [{
      "stationID": "KCASARAT1",
      "tz": "America/Los_Angeles",
      "obsTimeUtc": "2019-05-20T07:04:57Z",
      "obsTimeLocal": "2019-05-20 00:04:57",
      "epoch": 1558335897,
      "lat": 37.27470016,
      "lon": -122.02295685,
      "solarRadiationHigh": 0.0,
      "uvHigh": 0.0,
      "winddirAvg": 233,
      "humidityHigh": 93,
      "humidityLow": 92,
      "humidityAvg": 92,
      "qcStatus": 1,
      "imperial": {
        "tempHigh": 52,
        "tempLow": 52,
        "tempAvg": 52,
        "windspeedHigh": 0,
        "windspeedLow": 0,
        "windspeedAvg": 0,
        "windgustHigh": 0,
        "windgustLow": 0,
        "windgustAvg": 0,
        "dewptHigh": 50,
        "dewptLow": 50,
        "dewptAvg": 50,
        "windchillHigh": 52,
        "windchillLow": 52,
        "windchillAvg": 52,
        "heatindexHigh": 52,
        "heatindexLow": 52,
        "heatindexAvg": 52,
        "pressureMax": 29.89,
        "pressureMin": 29.89,
        "pressureTrend": -0.01,
        "precipRate": 0.00,
        "precipTotal": 0.00
      }
    }, {

*/

    if($unit == 'e') {
		  $headingDaily = 'Time,TemperatureF,DewpointF,PressureIn,WindDirection,WindDirectionDegrees,WindSpeedMPH,WindSpeedGustMPH,Humidity,HourlyPrecipIn,Conditions,Clouds,dailyrainin,SolarRadiationWatts/m^2,UVIndex,SoftwareType,DateUTC<br>
';  
	    $U = 'imperial';
		} elseif ($unit == 'm') {
			$headingDaily = 
'Time,TemperatureC,DewpointC,PressurehPa,WindDirection,WindDirectionDegrees,WindSpeedKMH,WindSpeedGustKMH,Humidity,HourlyPrecipMM,Conditions,Clouds,dailyrainMM,SolarRadiationWatts/m^2,UVIndex,SoftwareType,DateUTC<br>
';
      $U = 'metric';
		} elseif ($unit == 's') {
			$headingDaily = 
'Time,TemperatureC,DewpointC,PressurehPa,WindDirection,WindDirectionDegrees,WindSpeedMPS,WindSpeedGustMPS,Humidity,HourlyPrecipMM,Conditions,Clouds,dailyrainMM,SolarRadiationWatts/m^2,UVIndex,SoftwareType,DateUTC<br>
';
      $U = 'metric_SI';
			
		} elseif ($unit == 'h') {
			$headingDaily = 
'Time,TemperatureC,DewpointC,PressureMB,WindDirection,WindDirectionDegrees,WindSpeedMPH,WindSpeedGustMPH,Humidity,HourlyPrecipMM,Conditions,Clouds,dailyrainMM,SolarRadiationWatts/m^2,UVIndex,SoftwareType,DateUTC<br>
';
      $U = 'uk_hybrid';
		}
	
    $doneHeader = false;
		if(!$doHeader) {$doneHeader = true;}
		$outrecs = '';
		if(isset($J['observations']['tz'])) {
			date_default_timezone_set($J['observations']['tz']);
		}
		foreach ($J['observations'] as $i => $obs) {
		  $rec = array();
//			$rec[] = $obs['obsTimeLocal'];
      $epoch = $obs['epoch'];
			if(strlen($epoch > 10)) {$epoch = substr($epoch,0,10);}
			$rec[] = date('Y-m-d H:i:s',$epoch);
			$rec[] = $obs[$U]['tempAvg'];
			$rec[] = $obs[$U]['dewptAvg'];
			$rec[] = $obs[$U]['pressureMax']; // yes, no pressureAvg to use
			
			$rec[] = $compass[round($obs['winddirAvg'] / 22.5) % 16];
			$rec[] = $obs['winddirAvg'];
			$rec[] = $obs[$U]['windspeedAvg'];
			$rec[] = $obs[$U]['windgustHigh'];
			$rec[] = $obs['humidityAvg'];
			$rec[] = $obs[$U]['precipRate']; // this may not be correct...
			$rec[] = ''; // no conditions available
			$rec[] = ''; // no Clouds available
			$rec[] = $obs[$U]['precipTotal'];
			$rec[] = isset($obs['solarRadiationHigh'])?$obs['solarRadiationHigh']:'';
			$rec[] = isset($obs['uvHigh'])?round($obs['uvHigh'],1):'';
			$rec[] = 'n/a';
			$rec[] = $obs['obsTimeUtc'];
			if(!$doneHeader) {
			  $outrecs .= $headingDaily;
				$doneHeader = true;
			}
			$outrecs .= join(',',$rec)."\n<br>\n";
		} // end observations loop
	   return($outrecs);
	} // end daily processing

// ----------------------------------------------------------------------
	if($type == 'week') { // create a daily-formatted file for WEEK 
	  if(!isset($J['observations'])) {
			return ('');
		}
    if($unit == 'e') {
		  $heading = 'Date,TemperatureHighF,TemperatureAvgF,TemperatureLowF,DewpointHighF,DewpointAvgF,DewpointLowF,HumidityHigh,HumidityAvg,HumidityLow,PressureMaxIn,PressureMinIn,WindSpeedMaxMPH,WindSpeedAvgMPH,GustSpeedMaxMPH,PrecipitationSumIn<br>
';  
	    $U = 'imperial';
		} elseif ($unit == 'm') {
			$heading = 
'Date,TemperatureHighC,TemperatureAvgC,TemperatureLowC,DewpointHighC,DewpointAvgC,DewpointLowC,HumidityHigh,HumidityAvg,HumidityLow,PressureMaxhPa,PressureMinhPa,WindSpeedMaxKMH,WindSpeedAvgKMH,GustSpeedMaxKMH,PrecipitationSumCM<br>
<br>
';
      $U = 'metric';
		} elseif ($unit == 's') {
			$heading = 
'Date,TemperatureHighC,TemperatureAvgC,TemperatureLowC,DewpointHighC,DewpointAvgC,DewpointLowC,HumidityHigh,HumidityAvg,HumidityLow,PressureMaxhPa,PressureMinhPa,WindSpeedMaxMPS,WindSpeedAvgMPS,GustSpeedMaxMPS,PrecipitationSumCM<br>
<br>
';
      $U = 'metric_si';
			
		} elseif ($unit == 'h') {
			$heading = 
'Date,TemperatureHighC,TemperatureAvgC,TemperatureLowC,DewpointHighC,DewpointAvgC,DewpointLowC,HumidityHigh,HumidityAvg,HumidityLow,PressureMaxMB,PressureMinMB,WindSpeedMaxMPH,WindSpeedAvgMPH,GustSpeedMaxMPH,PrecipitationSumCM<br>
<br>
';
      $U = 'uk_hybrid';
			
		}
/*
{
  "observations": [
    {
      "stationID": "KCASARAT1",
      "tz": "America/Los_Angeles",
      "obsTimeUtc": "2019-05-14T07:59:58Z",
      "obsTimeLocal": "2019-05-14 00:59:58",
      "epoch": 1557820798,
      "lat": 37.27470016,
      "lon": -122.02295685,
      "solarRadiationHigh": 0,
      "uvHigh": 0,
      "winddirAvg": 101,
      "humidityHigh": 80,
      "humidityLow": 79,
      "humidityAvg": 79,
      "qcStatus": 1,
      "imperial": {
        "tempHigh": 58,
        "tempLow": 58,
        "tempAvg": 58,
        "windspeedHigh": 0,
        "windspeedLow": 0,
        "windspeedAvg": 0,
        "windgustHigh": 2,
        "windgustLow": 0,
        "windgustAvg": 0,
        "dewptHigh": 52,
        "dewptLow": 52,
        "dewptAvg": 52,
        "windchillHigh": 58,
        "windchillLow": 58,
        "windchillAvg": 58,
        "heatindexHigh": 58,
        "heatindexLow": 58,
        "heatindexAvg": 58,
        "pressureMax": 30.04,
        "pressureMin": 30.04,
        "pressureTrend": 0,
        "precipRate": 0,
        "precipTotal": 0
      }
    },
to
'Date,TemperatureHighF,TemperatureAvgF,TemperatureLowF,DewpointHighF,DewpointAvgF,DewpointLowF,HumidityHigh,HumidityAvg,HumidityLow,PressureMaxIn,PressureMinIn,WindSpeedMaxMPH,WindSpeedAvgMPH,GustSpeedMaxMPH,PrecipitationSumIn<br>
';  

*/	
    $doneHeader = false;
		if(!$doHeader) {$doneHeader = true;}
		$outrecs = '';
		if(isset($J['observations']['tz'])) {
			date_default_timezone_set($J['observations']['tz']);
		}
		foreach ($J['observations'] as $i => $obs) {
		  $rec = array();
//			$rec[] = $obs['obsTimeLocal'];
      $epoch = $obs['epoch'];
			if(strlen($epoch > 10)) {$epoch = substr($epoch,0,10);}
			$rec[] = date('Y-m-d H:i:s',$epoch);
			$rec[] = $obs[$U]['tempHigh'];
			$rec[] = $obs[$U]['tempAvg'];
			$rec[] = $obs[$U]['tempLow'];
			$rec[] = $obs[$U]['dewptHigh'];
			$rec[] = $obs[$U]['dewptAvg'];
			$rec[] = $obs[$U]['dewptLow'];
			$rec[] = $obs['humidityHigh'];
			$rec[] = $obs['humidityAvg'];
			$rec[] = $obs['humidityLow'];
			$rec[] = $obs[$U]['pressureMax']; 
			$rec[] = $obs[$U]['pressureMin'];
			$rec[] = $obs[$U]['windspeedHigh'];
			$rec[] = $obs[$U]['windspeedAvg'];
			$rec[] = $obs[$U]['windgustHigh'];
//			$rec[] = $compass[round($obs['winddirAvg'] / 22.5) % 16];
//			$rec[] = $obs['winddirAvg'];
//			$rec[] = $obs[$U]['precipRate']; // this may not be correct...
//			$rec[] = 'n/a'; // no conditions available
//			$rec[] = 'n/a'; // no Clouds available
			$rec[] = $obs[$U]['precipTotal'];
			$rec[] = isset($obs['solarRadiationHigh'])?$obs['solarRadiationHigh']:'';
			$rec[] = 'n/a';
			$rec[] = $obs['obsTimeUtc'];
			if(!$doneHeader) {
			  $outrecs .= $heading;
				$doneHeader = true;
			}
			$outrecs .= join(',',$rec)."\n<br>\n";
		} // end observations loop
	   return($outrecs);
	} // end 7-day processing

// ----------------------------------------------------------------------
	if($type == 'month' or $type=='year') { // create a monthly-formatted file 
	  if(!isset($J['observations'])) {
			return ('');
		}
    if($unit == 'e') {
		  $heading = 'Date,TemperatureHighF,TemperatureAvgF,TemperatureLowF,DewpointHighF,DewpointAvgF,DewpointLowF,HumidityHigh,HumidityAvg,HumidityLow,PressureMaxIn,PressureMinIn,WindSpeedMaxMPH,WindSpeedAvgMPH,GustSpeedMaxMPH,PrecipitationSumIn<br>
';  
	    $U = 'imperial';
		} elseif ($unit == 'm') {
			$heading = 
'Date,TemperatureHighC,TemperatureAvgC,TemperatureLowC,DewpointHighC,DewpointAvgC,DewpointLowC,HumidityHigh,HumidityAvg,HumidityLow,PressureMaxhPa,PressureMinhPa,WindSpeedMaxKMH,WindSpeedAvgKMH,GustSpeedMaxKMH,PrecipitationSumCM<br>
<br>
';
      $U = 'metric';
		} elseif ($unit == 's') {
			$heading = 
'Date,TemperatureHighC,TemperatureAvgC,TemperatureLowC,DewpointHighC,DewpointAvgC,DewpointLowC,HumidityHigh,HumidityAvg,HumidityLow,PressureMaxhPa,PressureMinhPa,WindSpeedMaxMPS,WindSpeedAvgMPS,GustSpeedMaxMPS,PrecipitationSumCM<br>
<br>
';
      $U = 'metric_si';
			
		} elseif ($unit == 'h') {
			$heading = 
'Date,TemperatureHighC,TemperatureAvgC,TemperatureLowC,DewpointHighC,DewpointAvgC,DewpointLowC,HumidityHigh,HumidityAvg,HumidityLow,PressureMaxMB,PressureMinMB,WindSpeedMaxMPH,WindSpeedAvgMPH,GustSpeedMaxMPH,PrecipitationSumCM<br>
<br>
';
      $U = 'uk_hybrid';
			
		}
/*
{
  "observations": [
    {
      "stationID": "KCASARAT1",
      "tz": "America/Los_Angeles",
      "obsTimeUtc": "2019-05-14T07:59:58Z",
      "obsTimeLocal": "2019-05-14 00:59:58",
      "epoch": 1557820798,
      "lat": 37.27470016,
      "lon": -122.02295685,
      "solarRadiationHigh": 0,
      "uvHigh": 0,
      "winddirAvg": 101,
      "humidityHigh": 80,
      "humidityLow": 79,
      "humidityAvg": 79,
      "qcStatus": 1,
      "imperial": {
        "tempHigh": 58,
        "tempLow": 58,
        "tempAvg": 58,
        "windspeedHigh": 0,
        "windspeedLow": 0,
        "windspeedAvg": 0,
        "windgustHigh": 2,
        "windgustLow": 0,
        "windgustAvg": 0,
        "dewptHigh": 52,
        "dewptLow": 52,
        "dewptAvg": 52,
        "windchillHigh": 58,
        "windchillLow": 58,
        "windchillAvg": 58,
        "heatindexHigh": 58,
        "heatindexLow": 58,
        "heatindexAvg": 58,
        "pressureMax": 30.04,
        "pressureMin": 30.04,
        "pressureTrend": 0,
        "precipRate": 0,
        "precipTotal": 0
      }
    },
to
'Date,TemperatureHighF,TemperatureAvgF,TemperatureLowF,DewpointHighF,DewpointAvgF,DewpointLowF,HumidityHigh,HumidityAvg,HumidityLow,PressureMaxIn,PressureMinIn,WindSpeedMaxMPH,WindSpeedAvgMPH,GustSpeedMaxMPH,PrecipitationSumIn<br>
';  

*/	
    $doneHeader = false;
		if(!$doHeader) {$doneHeader = true;}
		$outrecs = '';
		if(isset($J['observations']['tz'])) {
			date_default_timezone_set($J['observations']['tz']);
		}
		foreach ($J['observations'] as $i => $obs) {
		  $rec = array();
//			$rec[] = $obs['obsTimeLocal'];
      $epoch = $obs['epoch'];
			if(strlen($epoch > 10)) {$epoch = substr($epoch,0,10);}
			$rec[] = date('Y-m-d H:i:s',$epoch);
			$rec[] = $obs[$U]['tempHigh'];
			$rec[] = $obs[$U]['tempAvg'];
			$rec[] = $obs[$U]['tempLow'];
			$rec[] = $obs[$U]['dewptHigh'];
			$rec[] = $obs[$U]['dewptAvg'];
			$rec[] = $obs[$U]['dewptLow'];
			$rec[] = $obs['humidityHigh'];
			$rec[] = $obs['humidityAvg'];
			$rec[] = $obs['humidityLow'];
			$rec[] = $obs[$U]['pressureMax']; 
			$rec[] = $obs[$U]['pressureMin'];
			$rec[] = $obs[$U]['windspeedHigh'];
			$rec[] = $obs[$U]['windspeedAvg'];
			$rec[] = $obs[$U]['windgustHigh'];
//			$rec[] = $compass[round($obs['winddirAvg'] / 22.5) % 16];
//			$rec[] = $obs['winddirAvg'];
//			$rec[] = $obs[$U]['precipRate']; // this may not be correct...
//			$rec[] = 'n/a'; // no conditions available
//			$rec[] = 'n/a'; // no Clouds available
			$rec[] = $obs[$U]['precipTotal'];
			$rec[] = isset($obs['solarRadiationHigh'])?$obs['solarRadiationHigh']:'';
			$rec[] = 'n/a';
			$rec[] = $obs['obsTimeUtc'];
			if(!$doneHeader) {
			  $outrecs .= $heading;
				$doneHeader = true;
			}
			$outrecs .= join(',',$rec)."\n<br>\n";
		} // end observations loop
	   return($outrecs);
	} // end month processing
	
} // end WUJSON_decode

// fetch function
function WUJCSV_fetchUrlWithoutHanging($url,$useFopen=false,$useHeader='') {
// get contents from one URL and return as string 
  global $Status, $needCookie;
  
  $overall_start = time();
  if (! $useFopen) {
   // Set maximum number of seconds (can have floating-point) to wait for feed before displaying page without feed
   $numberOfSeconds=6;   

// Thanks to Curly from ricksturf.com for the cURL fetch functions

  $data = '';
  $domain = parse_url($url,PHP_URL_HOST);
  $theURL = str_replace('nocache','?'.$overall_start,$url);        // add cache-buster to URL if needed
  $Status .= "<!-- curl fetching '$theURL' -->\n";
  $ch = curl_init();                                           // initialize a cURL session
  curl_setopt($ch, CURLOPT_URL, $theURL);                         // connect to provided URL
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);                 // don't verify peer certificate
  curl_setopt($ch, CURLOPT_USERAGENT, 
    'Mozilla/5.0 (WXDailyHistory.php - saratoga-weather.org)');
  $reqHeader = array (
         "Accept: text/html,text/plain"
     );
	if(!empty($useHeader)) {$reqHeader[] = $useHeader;}
	
  curl_setopt($ch,CURLOPT_HTTPHEADER,$reqHeader);                          // request 

  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $numberOfSeconds);  //  connection timeout
  curl_setopt($ch, CURLOPT_TIMEOUT, $numberOfSeconds);         //  data timeout
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);              // return the data transfer
  curl_setopt($ch, CURLOPT_NOBODY, false);                     // set nobody
  curl_setopt($ch, CURLOPT_HEADER, true);                      // include header information
//  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);              // follow Location: redirect
//  curl_setopt($ch, CURLOPT_MAXREDIRS, 1);                      //   but only one time
  if (isset($needCookie[$domain])) {
    curl_setopt($ch, $needCookie[$domain]);                    // set the cookie for this request
    curl_setopt($ch, CURLOPT_COOKIESESSION, true);             // and ignore prior cookies
    $Status .=  "<!-- cookie used '" . $needCookie[$domain] . "' for GET to $domain -->\n";
  }

  $data = curl_exec($ch);                                      // execute session

  if(curl_error($ch) <> '') {                                  // IF there is an error
   $Status .= "<!-- curl Error: ". curl_error($ch) ." -->\n";        //  display error notice
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
    " RC=".$cinfo['http_code'];
	if(isset($cinfo['primary_ip'])) {
		$Status .= " dest=".$cinfo['primary_ip'] ;
	}
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
  if($cinfo['http_code'] <> '200') {
    $Status .= "<!-- headers returned:\n".$headers."\n -->\n"; 
  }
  return $content;                                                 // return headers+contents

 } else {
//   print "<!-- using file_get_contents function -->\n";
   $STRopts = array(
	  'http'=>array(
	  'method'=>"GET",
	  'protocol_version' => 1.1,
	  'header'=>"Cache-Control: no-cache, must-revalidate\r\n" .
				"Cache-control: max-age=0\r\n" .
				"Connection: close\r\n" .
				"User-agent: Mozilla/5.0 (WXDailyHistory.php - saratoga-weather.org)\r\n" .
				"Accept: text/html,text/plain\r\n"
	  ),
	  'https'=>array(
	  'method'=>"GET",
	  'protocol_version' => 1.1,
	  'header'=>"Cache-Control: no-cache, must-revalidate\r\n" .
				"Cache-control: max-age=0\r\n" .
				"Connection: close\r\n" .
				"User-agent: Mozilla/5.0 (WXDailyHistory.php - saratoga-weather.org)\r\n" .
				"Accept: text/html,text/plain\r\n"
	  )
	);
	
   $STRcontext = stream_context_create($STRopts);

   $T_start = WUJCSV_fetch_microtime();
   $xml = file_get_contents($url,false,$STRcontext);
   $T_close = WUJCSV_fetch_microtime();
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
   return($xml);
 }

}    // end WUJCSV_fetchUrlWithoutHanging
// ------------------------------------------------------------------

function WUJCSV_fetch_microtime()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}
   
// ----------------------------------------------------------

// end WXDailyHistory.php
