<?php
############################################################################
# A Project of TNET Services, Inc. and Saratoga-Weather.org (AJAX/PHP template set)
############################################################################
#
#   Project:    Sample Included Website Design
#   Module:     sample.php
#   Purpose:    Sample Page
#   Authors:    Kevin W. Reed <kreed@tnet.com>
#               TNET Services, Inc.
#
#         Copyright:        (c) 1992-2007 Copyright TNET Services, Inc.
############################################################################
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA
############################################################################
#        This document uses Tab 4 Settings
############################################################################
require_once("Settings.php");
require_once("common.php");
############################################################################
$TITLE= $SITE['organ'] . " - WU Station History";
$showGizmo = true;  // set to false to exclude the gizmo
include("top.php");
############################################################################

# array of css style names, when adding a new style ...
# edit this to add a new name, also add a new color array below,
# weather-screen-[NAME]-[narrow or wide].css

$styles_array_all = array(
array('NAME' => 'black',  'TAB_IMG'=> 'GrayGradient.png',    'BORDER_COLOR'=> 'white','LINK_COLOR'=> '#663300', 'BACKGROUND'=> '#BFBFBF', 'HEAD_COLOR' => 'white', 'HEAD_BACKGROUND' => '#4A4A4A',), # black
array('NAME' => 'blue',   'TAB_IMG'=> 'LtBlueGradient.png',  'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#336699', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#96C6F5',), # blue
array('NAME' => 'dark',   'TAB_IMG'=> 'LtRedGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#FE4242', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'white', 'HEAD_BACKGROUND' => '#4A4A4A',), # dark
array('NAME' => 'fall',   'TAB_IMG'=> 'BrownGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> 'black',   'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#C5C55B',), # fall
array('NAME' => 'green',  'TAB_IMG'=> 'GreenGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#00745B', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#B1CBA0',), # green
array('NAME' => 'icetea', 'TAB_IMG'=> 'LtYellowGradient.png','BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#786032', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#FFE991',), # icetea
array('NAME' => 'mocha',  'TAB_IMG'=> 'BrownGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#112637', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#D7E2E8',), # mocha
array('NAME' => 'orange', 'TAB_IMG'=> 'BrownGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#B36900', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#E9C781',), # orange
array('NAME' => 'pastel', 'TAB_IMG'=> 'LtBlueGradient.png',  'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#336699', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#96C6F5',), # pastel
array('NAME' => 'purple', 'TAB_IMG'=> 'BrownGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#993333', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#CCBFAD',), # purple
array('NAME' => 'red',    'TAB_IMG'=> 'LtRedGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#993333', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#CCBFAD',), # red
array('NAME' => 'salmon', 'TAB_IMG'=> 'BrownGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#46222E', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#A4CB32',), # salmon
array('NAME' => 'silver', 'TAB_IMG'=> 'GrayGradient.png',    'BORDER_COLOR'=> 'white','LINK_COLOR'=> '#336699', 'BACKGROUND'=> '#CACACA', 'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#999999',), # silver
array('NAME' => 'spring', 'TAB_IMG'=> 'BrownGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#666666', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#A9D26A',), # spring
array('NAME' => 'taupe',  'TAB_IMG'=> 'BrownGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> 'black',   'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#75B7BF',), # taupe
array('NAME' => 'teal',   'TAB_IMG'=> 'GreenGradient.png',   'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#006666', 'BACKGROUND'=> 'white',   'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#DAE4E6',), # teal
);

# default
$menu_colors = array(
array('NAME' => 'unknown', 'TAB_IMG'=> 'GrayGradient.png',  'BORDER_COLOR'=> '#CCC', 'LINK_COLOR'=> '#336699', 'BACKGROUND'=> 'white', 'HEAD_COLOR' => 'black', 'HEAD_BACKGROUND' => '#4A4A4A',),
);

if (isset($SITE['CSSscreen']) && preg_match("/^[a-z0-9-]{1,50}.css$/i", $SITE['CSSscreen'])) {
  preg_match("/^weather-screen-([a-z0-9]+)-(wide|narrow).css$/i", $SITE['CSSscreen'], $matches);
  $style_color = trim($matches[1]);
}

foreach ($styles_array_all as $v) {
     if($style_color == $v['NAME']) {
       $menu_colors = $v;
       break;
     }
}
?>
<!-- begin wuhistory CSS definition -->
<style type="text/css">
/* Basic Setup and commonly used elements */
#wuwrap { background-color: #EEE; color: #000; font-size: 10px; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; margin-top: 0px; margin-left: 0px; margin-right: 0px; text-align: left;}

/* These control the "general links throuout the page */
#wuwrap a { color: <?php echo $menu_colors['LINK_COLOR']?>; text-decoration: underline; }
#wuwrap a:hover { color: <?php echo $menu_colors['LINK_COLOR']?>; text-decoration: none; }

#wuwrap #content { background-color: <?php echo $menu_colors['BACKGROUND']?>; padding-left: 10px; padding-right: 5px; clear: both; width: 100%; }

/* Some general purpose styles */
#wuwrap TD.vaT { vertical-align: top; }
#wuwrap TR.vaT TD { vertical-align: top; }
#wuwrap TD.vaM { vertical-align: middle; }
#wuwrap TR.vaM TD { vertical-align: middle; }
#wuwrap #full { width: 99%; }
#wuwrap .full { width: 99%; }
#wuwrap .center { margin-left: auto; margin-right: auto; }
#wuwrap .taL { text-align: left; }
#wuwrap .taC { text-align: center; }
#wuwrap .taR { text-align: right; }
#wuwrap .nobr { white-space: nowrap; }
#wuwrap .tm10 { margin-top: 10px; }
#wuwrap .tm20 { margin-top: 20px; }
#wuwrap .b { font-weight: bold; }
#wuwrap .fwN { font-weight: normal; }
@media print {
  .noprint { display: none; }
}

/*  The top heading */
#wuwrap .heading { border: 0px; padding: 0px; margin: 0px; font-size: 18px; font-weight: bold; font-style: normal; color: #000; }

/*  The black line etc below the heading */
#wuwrap .titleBar { padding: 2px; background-color: <?php echo $menu_colors['BACKGROUND']?>; color: #333; border-top: 1px solid #333; margin-bottom: 10px; }

/* The colored bar at the top of each section */
#wuwrap .colorTop { border-width: 0px 0px 0px 0px; border-collapse: collapse; border-spacing: 0px; table-layout: fixed; width: 100%; }
#wuwrap .colorTop .hLeft { background-color: white; color: black; padding:0; height:20px; width:20px; border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-left: 1px solid #808080; }
#wuwrap .colorTop .hCenter { background-color: white; color: black; height: 20px; line-height: 20px; width: auto; font-size: 14px; font-weight: bold; border-top: 1px solid #808080; border-bottom: 1px solid #808080; }
#wuwrap .colorTop .hRight { background-color: white; color: black; padding:0; height:20px; width:20px; font-size: 12px; border-top: 1px solid #808080; border-bottom: 1px solid #808080; border-right: 1px solid #808080; }
#wuwrap .colorTop .hMenu { background-color: white; color: black; padding:0; height:20px; font-size: 12px; text-align: right; border-top: 1px solid #808080; border-bottom: 1px solid #808080; }
#wuwrap .colorTop .sLeft { padding: 0; height: 6px; width:20px; }
#wuwrap .colorTop .sCenter { padding: 0; height: 6px; line-height: 6px; font-size: 6px; }
#wuwrap .colorTop .sRight { padding: 0; height: 6px; width:20px;  }

/* The menu for selecting units.  This must be an id in order to gain precedence */
#unitmenu a:link {color: <?php echo $menu_colors['LINK_COLOR']?>; text-decoration: underline;}
#unitmenu a:visited{color: <?php echo $menu_colors['LINK_COLOR']?>; text-decoration: underline;}
#unitmenu A:hover {color: <?php echo $menu_colors['LINK_COLOR']?>; text-decoration: none;}
#unitmenu A:active {color: <?php echo $menu_colors['LINK_COLOR']?>; text-decoration: none;}

/* The sides of the main colored box */
#wuwrap .colorBox { border-right: 1px solid #808080; border-left: 1px solid #808080; margin: 0; padding: 0 5px 0 5px; }
#wuwrap .colorBox .noGap { height: 1px; line-height: 1px; }

/* Closes off the bottom of the main box */
#wuwrap .colorBottom { height: 10px; width: 100%; }
#wuwrap .colorBottom .bLeft DIV { height: 10px; width: 10px; border-top: 1px solid #808080; }
#wuwrap .colorBottom .bCenter DIV { height: 10px; border-top: 1px solid #808080; }
#wuwrap .colorBottom .bRight DIV { height: 10px; width: 10px; border-top: 1px solid #808080; }

/* The Date selector */
#wuwrap .selectorBox { background-color: #F5F5F5; border-top: 1px solid #CCC; }

/* The table that holds the date selector(s) */
#wuwrap .dateTable SPAN { margin: 0 10px 0 10px; white-space: nowrap; }
#wuwrap .dateTable TD { vertical-align: middle; padding: 1px; }
#wuwrap .dateTable A { color: <?php echo $menu_colors['LINK_COLOR']?>; display: block; width: 100px; margin: 2px; text-align: center; }
#wuwrap .dateTable .dateForm { margin-left: auto; margin-right: auto; }
#wuwrap .dateTable .dateForm SELECT { margin: 1px; }

/* The tabs for daily, weekly etc. */
#wuwrap #typeTable { margin-top: 10px; background-color: white;}
#wuwrap #typeTable TD { width: 20%; text-align: center; padding: 2px; }
#wuwrap #typeTable TD.activeTab { color: #000; border-top: 1px solid #A2A2A2; border-right: 1px solid #A2A2A2; border-left: 1px solid #A2A2A2; font-weight: bold; background: #FFF url(./wuicons/<?php echo $menu_colors['TAB_IMG']?>) repeat-x top; font-size: 13px; }
#wuwrap #typeTable TD.inactiveTab { border-bottom: 1px solid #A2A2A2; }
#wuwrap #typeTable TD.inactiveTab A { color: <?php echo $menu_colors['LINK_COLOR']?>; }

/* The table for the top summary section */
#wuwrap .summaryTable { width: 100%;}
#wuwrap .summaryTable THEAD TR TD { border-bottom: 1px solid #999; font-weight: bold; padding: 2px; background-color: <?php echo $menu_colors['HEAD_BACKGROUND']?>; color: <?php echo $menu_colors['HEAD_COLOR']?>;}
#wuwrap .summaryTable TBODY TR TD { border-bottom: 1px solid <?php echo $menu_colors['BORDER_COLOR']?>; padding: 3px; }
#wuwrap .summaryTable TBODY TR:hover TD { background-color: #FFC; }

/* The lower table when in "today" mode */
#wuwrap .dailyTable { width: 100%; }
#wuwrap .dailyTable THEAD TR TD { padding: 5px; background-color: #CCC; color: #000; font-weight: bold; border-top: 1px solid #EEE; border-left: 1px solid #EEE; border-right: 1px solid #999; border-bottom: 1px solid #999; background-image: url(./wuicons/mgray50.png); background-repeat: repeat-x; }
#wuwrap .dailyTable TBODY TR TD { color: #333; padding: 1px; padding-left: 5px; padding: 5px; border-bottom: 1px solid <?php echo $menu_colors['BORDER_COLOR']?>; }
#wuwrap .dailyTable TBODY TR:hover TD { background-color: #FFC; }

/*  Lower table when in week, month, year etc mode  */
#wuwrap .obsTable THEAD TR TD { background-color: <?php echo $menu_colors['HEAD_BACKGROUND']?>; color: <?php echo $menu_colors['HEAD_COLOR']?>; font-weight: bold; border-top: 5px solid #FFF; padding: 2px; text-align: center; }
#wuwrap .obsTable TBODY TR TD { border-bottom: 1px dotted #CCC; padding: 3px; text-align: center; }
#wuwrap .obsTable TBODY TD.date { text-align:left; }
#wuwrap .obsTable TBODY TR TD.HdgLt { border-bottom: 2px solid #CCC; }
#wuwrap .obsTable TBODY TR TD.HdgDk { border-bottom: 2px solid #CCC; background-color: #F5F5F5; }
#wuwrap .obsTable TBODY TR TD.Left {  border-left: 1px solid #CCC; }
#wuwrap .obsTable TBODY TR TD.Right {  border-right: 1px solid #CCC; }
#wuwrap .obsTable TBODY TR TD.BodyDk { background-color: #F5F5F5; }
#wuwrap .obsTable TBODY TR:hover TD { background-color: #FFC; }

/* The "Page Top links */
#wuwrap .pageTop { text-align: right; margin: 10px 0 10px 0; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 11px; }
#wuwrap .pageTop A { color: #00F; }

/* Optional right Column if used */
#wuwrap .rightCol { margin-left: 10px; }
#wuwrap .rightCol .rtTop  { width: 300px; border: 0; margin: 0; padding: 0; border-bottom: 1px solid #808080; }
#wuwrap .rightCol .rtBottom  { width: 300px; border: 0; margin: 0; padding: 0; border-top: 1px solid #808080; }
#wuwrap .rightCol .contentBox { width: 300px; border: 0; margin: 0; padding: 0; border-right: 1px solid #808080; border-left: 1px solid #808080; }

/* The link to the csv file at the bottom of the page */
#wuwrap #csvLink a { background-color: white; color: <?php echo $menu_colors['LINK_COLOR']?>; display: inline; padding: 1px 10px 1px 10px; border: 1px solid #808080; margin: 10px 0 10px 0; text-decoration: underline; }
#wuwrap #csvLink a:hover { border: 1px solid #808080; background-color: white; color: <?php echo $menu_colors['LINK_COLOR']?>;}

#wuwrap .bottomBar { background-color: <?php echo $menu_colors['HEAD_BACKGROUND']?>; color: <?php echo $menu_colors['HEAD_COLOR']?>; padding-left: 5px; padding-top: 2px; padding-bottom: 2px; margin: 0px; font-size: 13px; font-weight: bold; border: 1px solid #808080; }


/* Wunderground logo */
#wuwrap .logo { height: 25px; width: 53px; background-image: url(./wuicons/logo_footer.gif); background-repeat: no-repeat; }

/* End WU-History.css */
</style>
<meta name="robots" content="INDEX, NOFOLLOW"/>

</head>
<body>
<?php
############################################################################
include("header.php");
############################################################################
include("menubar.php");
############################################################################
?>
<!-- begin of javascript 'go to top' arrow -->

<div id="floatdiv" class="jsupoutline">
    <a href="#header" title="Goto Top of Page" class="jsuparrow">
    <img src="<?php echo $SITE['imagesDir']; ?>toparrow.gif" alt="^^"
        style="border: 0px;" /></a>

</div>
<script src="floatTop.js" type="text/javascript"></script>

<!-- end of javascript 'go to top' arrow -->

<div id="main-copy">

<?php include_once("WU-History-inc.php"); ?>

</div><!-- end main-copy -->

<?php
############################################################################
include("footer.php");
############################################################################
# End of Page
############################################################################
?>