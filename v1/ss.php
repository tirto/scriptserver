<?php
/* CONSTANTS */
$VERSION="v1";
// ZZZ set this to appropriate value
//$CACHE_EXPIRE_SECS = 604800; # one week
$CACHE_EXPIRE_SECS = 86400 ; # one day 
//$CACHE_EXPIRE_SECS = 1;
$PROD_RSID = "aca-ncnu-prod";
$DEV_RSID = "aca-ncnu-dev";
$CSAA_PROD_HOSTS = array("csaa.com","csaaquote.com","aaa4insurance.com","ww2.aaa.com","pay.goaaa.com","pleasantholidays.com","aaa005.revelex.com","buyacar.go.aaa.com");

include "/home/aaametrics/$VERSION/includes/db.inc";
include "/home/aaametrics/$VERSION/includes/aaa_tag_settings.inc";
include "/home/aaametrics/$VERSION/includes/aaa_tag_pageinfo.inc";
include "/home/aaametrics/$VERSION/includes/aaa_tag_partnertag_info.inc";
include "/home/aaametrics/$VERSION/includes/aaa_tag_pagetag_info.inc";
include "/home/aaametrics/$VERSION/includes/aaa_tag_market.inc";
include "/home/aaametrics/$VERSION/includes/omniture.inc";

require_once "/home/aaametrics/log4php/Logger.php";
Logger::configure('/home/aaametrics/conf/scriptserver.properties');
$logger = Logger::getLogger("scriptserver");

// helper functions
function is_omitted($page_url) {
  global $logger;
  // ZZZ add regex and move to db (or APC store or memcache for faster performance)
  // $omitted_urls = get_omitted_urls();
  // canonicalize the page_urls (no http(s)://www.)
  $omitted_urls = array('csaa.com/admin/build/block',
                        'csaa.com/discounts/membersavingsfromaaahtml',
                        'csaa.com/file_browser',
                        'csaa.com/file_gallery',
                        'csaa.com/portal/site/CSAA/menuitem.acb236f9dc8c67360600af9592278a0c',
                        'csaa.com/portal/site/CSAA/template.travellogin',
                        'csaa.com/sites/default/files/csaa_favicon.ico',
                        'csaa.com/sites/travel/admin',
                        'csaa.com/sites/users/admin',
                        'csaa.com/sites/users/gwnzril',
                        'csaa.com/discounts/8547utahjazz-entryformhtml'
                        );

  foreach($omitted_urls as $omitted_url) {
    //$logger->debug("omitted_url is $omitted_url");
    if (strpos($page_url, $omitted_url) !== FALSE) {
      $logger->debug("$page_url is omitted");
      return true;
    }
  }
  $logger->debug("$page_url is not omitted");

  return false;
}


function get_market_from_db($zipcode) {
  global $logger;
  $logger->debug("start get_market_from_db");
  $market = 'n/a';
  $db = new aaa_tag_market($logger);
  $market = $db->get_market($zipcode);
  $logger->debug("market = $market");
  $logger->debug("end get_market_from_db");
  return $market;
}


function get_page_tag_from_db($page_id) {
  global $logger;
  $logger->debug("start get_page_tag_from_db");
  $page_tag = '';
  $db = new aaa_tag_pagetag_info($logger);
  $page_tag = $db->get_page_tag($page_id);
  $logger->debug("end get_page_tag_from_db");
  return $page_tag;
}


function get_partner_tag_from_db($partner) {
  global $logger;
  $logger->debug("start get_partner_tag_from_db");
  $partner_tag = '';
  $db = new aaa_tag_partnertag_info($logger);
  $partner_tag = $db->get_partner_tag($partner);
  $logger->debug("end get_partner_tag_from_db");
  return $partner_tag;
}

function get_partner_rsid_from_db($page_url, $referrer='', $query_param='', $referrer_query_param='') {
  global $logger;
  $logger->debug("start get_partner_rsid_from_db");
  $partner_from_db = '';
  $rsid_from_db = '';
  $page_id = '';
  $db = new aaa_tag_pageinfo($logger);
  list($partner_from_db,$rsid_from_db, $page_id) = $db->get_partner_rsid($page_url, $referrer, $query_param, $referrer_query_param);
  $logger->debug("end get_partner_rsid_from_db");
  return array($partner_from_db,$rsid_from_db,$page_id);
}

function get_track_js_from_db($partner, $page_url, $rsid, $page_id, $page_svars_input='', $page_cvars_input='',$zipcode='',$club_id='',$csaa_vars='',$page_name_override='',$market='n/a') {
  global $logger;
  $logger->debug("start get_track_js_from_db");
  $db = new aaa_tag_settings($logger);

  $svars = array();
  $cvars = array();
  try {
    if ($page_id) {
      $result = $db->load($partner, $page_id, 'omniture');
      $svars = $result['svars'];
      $cvars = $result['cvars'];
    }
  }
  catch (Exception $e) {
    $logger->error("db query failed!");
  }

  $omniclass = new omniture($logger);
  
  // standard variables
  list($page_js,$pgname) = $omniclass->get_track_js($svars, $page_url, $rsid, $partner, $page_svars_input, $zipcode, $club_id, $csaa_vars,$page_name_override,$market);

  // custom variables
  if ($cvars) {
    foreach($cvars as $cvar) {
      if (isset($cvar['var_name']) && $cvar['var_setting']) {
        $config[$cvar['var_name']] = $cvar['var_setting'];
        $logger->debug('cvar_name='.$cvar['var_name']);
        $logger->debug('cvar_setting='.$cvar['var_setting']);
      }
    }
    // tl stands for track link
    $tl_js = ($omniclass->get_tl_js($config,$pgname,$page_cvars_input));
    $logger->debug($tl_js);
    $page_js .= $tl_js;
  }
  $logger->debug('end get_track_js_from_db');
  return $page_js;
}

function breakup_vars ($cookie_string, $delim="=") {
  global $logger;
  $array=explode("|",$cookie_string);
  foreach ($array as $i=>$stuff) {
    $stuff=explode($delim,$stuff);
    $array[$stuff[0]]=$stuff[1];
    unset($array[$i]);
  }
  return $array;
}

// get data from db, if not supplied by the query params
//   - partner
//   - rsid
//   - tracking javascript
//   - partner tags javascript
function get_from_db($page_url,$partner,$page_svars_input,$page_cvars_input,$omniture_disabled,$zipcode,$club_id,$csaa_vars,$page_name_override,$rsid_override) {
  global $logger;
  $rsid = $rsid_override;
  $page_id = '';
  $logger->debug("start get from db");
  $track_js = '';
  $market = '';

  list($partner_from_db,$rsid_from_db,$page_id) = get_partner_rsid_from_db($page_url);

  if($zipcode) {
    $market = get_market_from_db($zipcode);
  }
  if (!$partner) {
    $partner = $partner_from_db;
  }
  if (!$rsid) {
    $rsid = $rsid_from_db;
  }
  if ($omniture_disabled) {
    $page_track_js = '';
  }
  else {
    $page_track_js = get_track_js_from_db($partner,$page_url,$rsid,$page_id,$page_svars_input,$page_cvars_input,$zipcode,$club_id,$csaa_vars,$page_name_override,$market);
  }
  $page_tag = get_page_tag_from_db($page_id);
  if ($page_tag) {
    $track_js .= $page_tag;
  }

  $partner_tag = get_partner_tag_from_db($partner);
  if ($partner_tag) {
    $track_js .= $partner_tag;
  }

  $track_js .= $page_track_js;
  return $track_js;
}

function set_ss_cookie($iplong, $membership_id) {
  global $logger;
  // cookie handling
  $cookie_name = '_aaam'; // aaametrics cookie
  $pid_cookie = "pid=$iplong";
  $mid_cookie = '';
  $cookie_val = ''; 

  if (isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name])) {
    $logger->debug("found _aaam cookie");
    $user_cookie = breakup_vars($_COOKIE[$cookie_name]);
    $pid_not_set = 0;
    $mid_not_set = 0;
    // prospect id
    if (!isset($user_cookie['pid'])) {
      $pid_not_set = 1;
      $cookie_val = $pid_cookie;
      $logger->debug("setting pid cookie_val=$cookie_val");
    }
    // membership id
    if (!isset($user_cookie['mid'])) {
      if ($membership_id) {
        $cookie_val .= "|mid=$membership_id";
        $mid_not_set = 1;
      }
    }

    if ($pid_not_set || $mid_not_set) {
      setcookie($cookie_name,$cookie_val, time() + 31536000, '/');
    }
  }
  else {
    $logger->debug("_aaam cookie not found");
    $cookie_val = $pid_cookie;
    if ($membership_id) {
      $cookie_val .= "|mid=$membership_id";
    }
    $logger->debug("setting cookie $cookie_val");
    setcookie($cookie_name,$cookie_val, time() + 31536000, '/');
  }
}

// we source the zipcode from AAAMETRICS global js var instead
function get_zipcode_clubid() {
  global $logger;
  $zipcode = '';
  $club_id = '';
  $logger->debug(">>>>> getting zipcode");
  if (isset($_COOKIE['zipcode'])) {
    $logger->debug("cookie zipcode = ".$_COOKIE['zipcode']);
    $zcookie = explode("|",$_COOKIE['zipcode']);
    $zipcode = $zcookie[0];
    $club_id = $zcookie[1];
  }
  elseif (isset($_COOKIE['aaa_zipcode'])) {
    $logger->debug("cookie zipcode = ".$_COOKIE['aaa_zipcode']);
    $zipcode = $_COOKIE['aaa_zipcode'];
  }
  else {
    $logger->debug("cookie zipcode not set");
  }

  return array($zipcode,$club_id);
}

/* start logic per partner */
/* travelocity specific logic */
function travelocity_logic($query) {
  global $logger;
  $travelocity_rsid = $DEV_RSID;
  if (preg_match('/src=10021453/',$query)) {
    $travelocity_rsid = $PROD_RSID;
    $logger->debug("travelocity_rsid=$travelocity_rsid");
  }
  return $travelocity_rsid;
}
 
/* csaa specific logic */
function csaa_logic($host, $path, $referrer) {
  global $logger;
  global $CSAA_PROD_HOSTS;
  global $PROD_RSID;
  global $DEV_RSID;
  $vars = array();
  $page_name = '';
  $rsid = $PROD_RSID;
  $logger->debug("host=$host");
  $logger->debug("path=$path");
  $paths = explode("/",$path);
  $path1 = isset($paths[1])? $paths[1] : '';
  $path2 = isset($paths[2])? $paths[2] : '';
  $path3 = isset($paths[3])? $paths[3] : '';
  $logger->debug("path1=$path1");
  $logger->debug("path2=$path2");
  $logger->debug("path3=$path3");
  $logger->debug("processing search");

  /* determine report suite */
  $logger->debug("host=$host");
  if (in_array($host, $CSAA_PROD_HOSTS)) {
    $rsid = $PROD_RSID;
  }
  else {
    $rsid = $DEV_RSID;
  }

  // Use cases:
  // I. capture keyword search term
  // get the apachesolr path
  // e.g. http://www.csaa.com/search/apachesolr_search/car%20insurance
  // host: csaa.com (we stripped the http://www. already)
  // path1: search
  // path2: apachesolr_search
  // path3: search keyword (the var that you want to capture in eVar2 and prop2)
  if ($path1 && $path1=="search" && $path2 && $path2=="apachesolr_search") {
    $page_name = 'global:search-result';
    $vars['s.eVar1'] = 'global-search';
    $vars['s.prop1'] = 'global-search';
    $vars['s.eVar2'] = $path3;
    $vars['s.prop2'] = $path3;
    $vars['s.events'] = 'event10';
  }

  // II. capture campaign id from referrer URL
  // get cmp from referrer query 
  // did not work ZZZ needs to fix
  // when user -> google -> aaa.com, the referrer is google
  // and if it has a cmpgn, get the cmpid from the url's query
  if ($path1=="zipcode-entry") {
    $logger->debug(">>>>>>>>>>>>>>>processing zipcode-entry");
    $logger->debug(">>>>>>>>>>>>>>>>referrer=$referrer");
    $referrer_url = parse_url($referrer);
    if (!empty($referrer_url)) {
      $referrer_query = isset($referrer_url['query'])? $referrer_url['query']:'';
      $logger->debug(">>>>>>>>>>>>>>>>referrer_query=$referrer_query");
      parse_str($referrer_query, $output);
      $logger->debug("output=$output");
      $cmp = isset($output['cmp'])? $output['cmp'] : ''; 
      $logger->debug(">>>>>>>>>>>>>>>>referrer_cmp=$cmp");
      if ($cmp) {
        $vars['s.campaign'] = $cmp;
      }
    }
    $logger->debug(">>>>>>>>>>>>>>> end processing zipcode-entry");
  }

  // III. truncation rules
  // these are the edge cases not in the db 
  // because there are we'd like to truncate the policy #, etc.
  // we exclude the host (csaa.com)
  // if the paths contains 'my-aaa' use custom hierarchy
  // 1. remove the policy # and set the pagename to 'global-utilites' 
  // 2. replace the "/" with ":", and append it to global-utilities
  // e.g. url is csaa.com/my-aaa/insurance/automatic-payment/modify/123
  //      the pagename becomes global-utilites:my-aaa:insurance:automatic-payment:modify
  // note 123 is the policy #
  $special_paths = array('my-aaa/insurance/automatic-payment/modify',
                         'my-aaa/insurance/automatic-payment/cancel',
                         'my-aaa/insurance/automatic-payment/new',
                         'my-aaa/insurance/auto-policy',
                         'my-aaa/insurance/auto-policy-details',
                         'my-aaa/insurance/enable-failed',
                         'my-aaa/insurance/enable-failed-newform',
                         'my-aaa/insurance/enabled-failed-newform',
                         'my-aaa/insurance/one-time-payment',
                         'my-aaa/insurance/one-time-payment-alt',
                         'my-aaa/insurance/one-time-payment-with-renewal',
                         'my-aaa/insurance/payment/autopay-enabled',
                         'user/password/reset',
                         'discounts/map'
                         );
  $canon_path = dirname($path);
  $logger->debug("canon_path = $canon_path");
  // 20110415 tirto fixed truncation rule's logic
  foreach($special_paths as $special_path) {
    $logger->debug("special_path is $special_path");
    if (strpos($canon_path, $special_path) !== FALSE) {
      $logger->debug("$canon_path matches $special_path");
      $page_name = "global-utilities:" . str_replace('/',':',$special_path);
      $logger->debug("page_name = $page_name");
    }
  }
  return array($page_name,$vars,$rsid);
}
 
/* aaaquote logic */

/* mexins logic */

/* end logic per partner */

/* helper function to help debug script after going to prod */
/* since it's hard to debug because everything is going to scriptserver.log */
function ddebug($host,$text) {
  if ($host == "limasalle.com") {
    error_log($text);
  }
}

function pdebug($path,$text) {
  if (preg_match('/zipcode-entry/',$path)) {
    error_log($text);
  }
}


function udebug($url,$text) {
  if (preg_match('/tirto/',$url)) {
    error_log($text);
  }
}

function mdebug($text) {
  if (preg_match('/429 005 542398271 4/',$text)) {
    error_log($text);
  }
}

function getMembershipId($standard_vars) {
  $membershipId = '';
  if ($standard_vars) {
    $svars_array = breakup_vars($standard_vars, $delim=":");
    if (isset($svars_array['mv_visitorID'])) {
      $membershipId = $svars_array['mv_visitorID'];
      return $membershipId;
    }
  }
  return $membershipId;
}

// end of helper functions


//
// main script
//
$logger->debug("################## start ##################");
$time_start = microtime(true);

/* start response output */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Cookies");
header("Content-type: text/javascript");

// for XHR request
$request_method = $_SERVER['REQUEST_METHOD'];
$logger->debug("request_method=$request_method");
if ($request_method == 'OPTIONS') {
  // authenticated
  $logger->debug("do nothing");
  return;
}

// get the input params
$page_url = urldecode(filter_input(INPUT_GET, 'page', FILTER_SANITIZE_URL));
$omniture_disabled = urldecode(filter_input(INPUT_GET, 'omniture_disabled'));
$partner = filter_input(INPUT_GET, 'partner', FILTER_SANITIZE_STRING);
$rsid = filter_input(INPUT_GET, 'rsid', FILTER_SANITIZE_STRING);
// get referrer from arg, not from client browser
$referrer = urldecode(filter_input(INPUT_GET, 'referrer', FILTER_SANITIZE_URL));
// getting referrer from client won't work
//$referrer = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']:'';
// membership_id and ipaddress are used for ss cookie
$membership_id = filter_input(INPUT_GET, 'mid', FILTER_SANITIZE_STRING);
$iplong = isset($_SERVER['REMOTE_ADDR'])? ip2long($_SERVER['REMOTE_ADDR']):'';
// these ones from mv_* coming from AAAMETRICS global var js
$page_svars_input = filter_input(INPUT_GET, 'svars', FILTER_SANITIZE_STRING);
$page_cvars_input = filter_input(INPUT_GET, 'cvars', FILTER_SANITIZE_STRING);

$logger->debug("partner=$partner");
$logger->debug("omniture_disabled=$omniture_disabled");

// 20110128 disabled omniture tracking for travelocity
// due to s_code.js version H22 s_sq cookie issue
// so we overwrite omniture_disabled flag
if ($partner == "travelocity") {
  $omniture_disabled = 1;
  $logger->debug("disabled omniture");
  // ZZZ remove this when you want to enabled partner tag in script server
  // for travelocity partner tags currently added directly to wctravel footer
  //$partner_tag = get_partner_tag_from_db($partner);
  //$logger->debug("partner_tag=$partner_tag");
  //if ($partner_tag) {
  //  echo ($partner_tag);
  //}
  return; 
}

$logger->debug("page_url=$page_url");
$logger->debug("rsid=$rsid");
$logger->debug("referrer=$referrer");
$logger->debug("membership_id=$membership_id");
$logger->debug("page_svars_input=$page_svars_input");
$logger->debug("page_cvars_input=$page_cvars_input");

// set script server cookie to track prospect id and membership id
// when both present, we use the prospect id to identify users who become club member
if (strlen($membership_id)==0 && strlen($page_svars_input) > 0) {
  $membership_id = getMembershipId($page_svars_input);
}

// membership_id is now being passed by AAAMETRICS global js var
// there is no need to set cookie for now
//set_ss_cookie($iplong, $membership_id);

// init csaa specific vars 
$csaa_vars = array();
$page_name_override = '';
$rsid_override = '';
// page url handling
if ($page_url) {
  $parsed_url = parse_url($page_url);
  if ($parsed_url) {
    // canonicalize the host by removing the leading 'www.'
    $host = isset($parsed_url['host']) ? preg_replace('/^www./','',$parsed_url['host']) : '';
    // canonicalize the path by removing sessionid
    $path = preg_replace('/;jsessionid=(.*)/','', $parsed_url['path']);
    $page_url = $host . $path;
    $page_url = rtrim($page_url, '/');

    // 20110411 tirto added check if page_url is omitted
    if (is_omitted($page_url)) {
      echo("<!-- omit $page_url -->");
      return;
    }
    $logger->debug("page_url=$page_url");
    if ($host) {
      $query = isset($parsed_url['query']) ? $parsed_url['query']:'';
      if ($partner == "travelocity") {
        // travelocity special logic
        $rsid_override = travelocity_logic($query);
      }
      elseif ($partner == "csaa") {
        $logger->debug("executing csaa special logic");
        // csaa special logic
        list($page_name_override,$csaa_vars,$rsid_override) = csaa_logic($host, $path, $referrer);
      }
    }
  }
}
else {
  $page_url = "ss_unknown_page";
}

// get zip and club from cookie
list($zipcode,$club_id) = get_zipcode_clubid();
$logger->debug("zipcode=$zipcode");
$logger->debug("club_id=$club_id");

//ddebug($host, "1 --- rsid=$rsid");
//ddebug($host, "2 --- partner$partner");
//ddebug($host, "3 --- rsid_override=$rsid_override");

// override the rsid with the value from special logic functions 
// from now on we use rsid_override to build tracking js
$rsid_override = ($rsid_override)? $rsid_override : $rsid;
//ddebug($host, "4 --- rsid_override=$rsid_override");

// memcache handling
$mkey = 'req:'. $partner . ':' . $zipcode . ':' . $page_url . ':' . $referrer;
$logger->debug("mkey=$mkey");
$memcache = '';
$track_js = '';

$mc_connect = 0;
// check if we can connect to memcache
try {
  $memcache = new Memcache;
  if ($memcache->connect('localhost', 11211)) {
    $mc_connect = 1;
    $logger->debug("connected to memcache");
  }
  else {
    $logger->error("couldn't connect");
  }
}
catch (Exception $e) {
  $logger->error("unable to connect to memcache, $e->getMessage()");
}

if ($mc_connect) {
  // ZZZ don't forget to comment out
  //$memcache->delete($mkey);
  $track_js = $memcache->get($mkey);
  if (!$track_js) {
    $logger->info("init cache for $mkey");
    $track_js = get_from_db($page_url,$partner,$page_svars_input,$page_cvars_input,$omniture_disabled,$zipcode,$club_id,$csaa_vars,$page_name_override,$rsid_override);
    $logger->debug("track_js=$track_js");
    $memcache->add($mkey, $track_js, MEMCACHE_COMPRESSED, $CACHE_EXPIRE_SECS);
    $logger->debug("set cache for $mkey");
  }
  else {
    $logger->debug("found $mkey in cache");
  }
}
else {
  // unable to connect to memcache
  // call get_from_db directly
  $logger->warn("memcache is null, get data from db");
  $track_js = get_from_db($page_url,$partner,$page_svars_input,$page_cvars_input,$omniture_disabled,$zipcode,$club_id,$csaa_vars,$page_name_override,$rsid_override);
}
echo ($track_js);

$elapsed_time = microtime(true) - $time_start;
$elapsed_time = $elapsed_time * 1000;
$logger->info("served analytics for ". $partner . ":" .  $page_url . " in $elapsed_time msecs");
$logger->debug("################## end ##################");
$logger->debug("");
?>
