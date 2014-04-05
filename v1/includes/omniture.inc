<?php
require_once ('/home/aaametrics/log4php/Logger.php');
class omniture {
function __construct($logger) {
  $this->logger = Logger::getLogger('omniture');
}

/* my production membership id, need a test one */
function mdebug($text) {
  if (preg_match('/429 005 113750031 4/',$text)) {
    error_log($text);
  }
}

function breakup_vars($vars_string) {
  $array=explode("|",$vars_string);
  foreach ($array as $i=>$stuff) {
    // note that the delim is : (colon) here
    $stuff=explode(":",$stuff);
    $array[$stuff[0]]=$stuff[1];
    unset($array[$i]);
  }

  return $array;
}

function lookup_page_standard_vars($standard_vars) {
  $svars = '';
  $this->mdebug("standard_vars=$standard_vars");
  if ($standard_vars) {
    $svars_array = $this->breakup_vars($standard_vars);
    if (isset($svars_array['mv_visitorID'])) {
      $membershipId = $svars_array['mv_visitorID'];
      $this->mdebug("mv_visitorID = ". $membershipId);
      $svars .= "s.prop4='$membershipId';\n";
      $svars .= "s.eVar3='$membershipId';\n";
    }
  }

  return $svars;
}

function lookup_page_custom_vars($custom_vars) {
  return '';
}

function construct_track_js($pgname,$market,$partner) {
  $js = '';
  $pgname = trim($pgname);
  $this->logger->debug(">>>>>> pgname=$pgname");
  $market = strlen($market) > 0? $market : 'unknown_market';
  $this->logger->debug(">>>>>> market=$market");
   
  $pgnameArray = explode(":",$pgname);
  $line_of_biz = isset($pgnameArray[0])? $pgnameArray[0] : '';
  $content_area = isset($pgnameArray[1])? $pgnameArray[1] : '';
  $section = isset($pgnameArray[2])? $pgnameArray[2] : '';
  $subsection = isset($pgnameArray[3])? $pgnameArray[3] : '';
  $subsubsection = isset($pgnameArray[4])? $pgnameArray[4] : '';
  $channel = $line_of_biz;
  if ($partner != 'csaa') {
    $partner = $content_area;
  }
  $hier1 = str_replace(':',',',$pgname);
  $hier2 = $market . ',' . $hier1;
  $this->logger->debug("line_of_biz=$line_of_biz");
  $this->logger->debug("content_area=$content_area");
  $this->logger->debug("section=$section");
  $this->logger->debug("channel=$channel");
  $this->logger->debug("subsection=$subsection");
  $this->logger->debug("subsubsection=$subsubsection");
  $this->logger->debug("market=$market");
  $js .= "s.eVar13='$partner';\n";
  $js .= "s.prop16='$partner';\n";
  $js .= "s.eVar14='$section';\n";
  $js .= "s.prop6='$subsection';\n";
  $js .= "s.hier1='$hier1';\n";
  $js .= "s.hier2='$hier2';\n";
  $js .= "s.channel='$channel';\n";
  $js .= "s.eVar15='$market';\n";
  $js .= "s.prop18='$market';\n";
  return array($js,$channel);
}

function get_track_js($svars, $page_url, $report_suite, $partner, $page_svars_input,$zipcode='',$club_id='',$csaa_vars='', $page_name_override='',$market='n/a') {
  $track_js = '';
  $channel = '';
  $pgname = isset($page_name_override)? $page_name_override : $page_url;
  $report_suite = empty($report_suite) ? 'aca-ncnu-prod' : $report_suite;
  $track_js .= "\n<!--\n";
  $track_js .= "s_account='$report_suite';\n";
  $track_js .= "s=s_gi(s_account);\n";

  // csaa_logic variables
  if ($csaa_vars) {
    $this->logger->debug("setting csaa_vars");
    //error_log("setting csaa_vars");
    foreach($csaa_vars as $key=>$value) {
      $this->logger->debug("$key='$value';");
      //error_log("$key='$value';");
      $track_js .= "$key='$value';\n";
    }
  }
 
  // zipcode
  if (isset($zipcode)) {
    $track_js .= "s.zip='$zipcode';\n";
  }

  // page standard vars
  if ($page_svars_input) {
    $page_svars = $this->lookup_page_standard_vars($page_svars_input);
    $track_js .= $page_svars;
  }

  $page_name_found = 0;
  // db standard vars
  if ($svars) {
    foreach($svars as $svar) {
      if (isset($svar['var_name']) && $svar['var_name'] == 's.pageName') {
        $page_name = trim($svar['var_setting']);
        list($page_js,$channel) = $this->construct_track_js($page_name,$market,$partner);
        $track_js .= $page_js;
        $page_name_found = 1;
      }
      if (isset($svar['var_name']) && $svar['var_name'] == 's.channel') {
        $channel = trim($svar['var_setting']);
      }
      $track_js .= $svar['var_name'] . "='" . $svar['var_setting'] ."';\n";
    }
  }
  else {
    $this->logger->debug("page_name_found = $page_name_found");
    if (!$page_name_found) { 
      $this->logger->debug("setting page_name_override=$page_name_override");
      if ($page_name_override) {
        $page_name_override = trim($page_name_override);
        $track_js .= "s.pageName='$page_name_override';\n";
        $channel='global-utilities';
        $track_js .= "s.channel='$channel';\n";
      }
      else {
        $this->logger->warn("mapping is missing for $partner:$page_url");
        $track_js .= "s.pageName='$partner:ss_nomap:$page_url';\n";
      }
    }
  }

  $this->logger->debug("channel=$channel");
  if (!$channel) {
    $track_js .= "s.channel='ss_nochannel';\n";
  }

  $track_js .= "var s_code=s.t();if(s_code)document.write(s_code);\n";
  $track_js .= "if(navigator.appVersion.indexOf('MSIE')>=0)document.write(unescape('%3C')+'\!-'+'-')\n";
  $track_js .= "//-->\n";
  $this->logger->debug("pgname = $pgname");
  //$this->logger->debug("track_js = $track_js");
  return array($track_js,$pgname);
}

function get_tl_js ($config, $pgname, $page_cvars_input) {
  // custom link tracking javascript
  // custom vars
  // e.g <input type=submit value="Get Quote"/>
  // objtype = submit
  // objname = input
  // objDefaultVal = "Get Quote"
  // objpgname = "insurance:insurity:additional-discounts
  // objtagname = "insurance:insurity:additional-discounts:submit:Get Quote
  $objtype = isset($config['objtype'])? $config['objtype']:'';
  $objname = isset($config['objname'])? $config['objname']:'';
  $objDefaultVal = isset($config['objDefaultVal'])? $config['objDefaultVal']:'';
  $objtagname = $pgname . ":" .  $objtype . ":" .  $objDefaultVal;

  // omniture vars
  $linkTrackVars = isset($config['s.linkTrackVars'])? $config['s.linkTrackVars']:'';
  $linkTrackEvents = isset($config['s.linkTrackEvents'])? $config['s.linkTrackEvents']:'';
  $products = isset($config['s.products'])? $config['s.products']:'';
  $events = isset($config['s.events'])? $config['s.events']:'';

  // page custom vars
  if ($page_cvars_input) {
    $page_cvars = $this->lookup_page_custom_vars($page_cvars_input);
  }

  // custom javascripts
  // s.tl(targ,"o",objtagnam) 
  // "other" custom links, "d" is for "download", "e" for "exit"
  $TL_JS = <<<END
<!--
YUI().use("node", function(Y) {
var interceptClick = function(e) {
   var targ;
   if (!e) var e = window.event;
   if (e.target) targ = e.target;
   else if (e.srcElement) targ = e.srcElement;
   if (targ.nodeType == 3) targ = targ.parentNode;
   if (targ) {
      var type = targ._node.type.toLowerCase();
      var name = targ._node.nodeName.toLowerCase();
      var val = targ._node.defaultValue;
      if (type == "$objtype" && name == "$objname" && val == "$objDefaultVal") {
         s.linkTrackVars="$linkTrackVars";
         s.linkTrackEvents="$linkTrackEvents";
         s.events="$events";
         s.products="$products";
         s.tl(targ,"o","$objtagname");
      }
   }
}
Y.on("click", interceptClick, 'body');
});
-->
END;

//  $this->logger->debug("TL_JS=$TL_JS");
  return $TL_JS;
}
}
?>
