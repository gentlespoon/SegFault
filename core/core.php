<?php

date_default_timezone_set("UTC");

$now = time();

$querycount = 0;

// mute all error before we initialize debug level
error_reporting(E_ALL);

// start session before initializing debug level, so we can make debug level persist through the session.
if(!isset($_SESSION)) {
  session_start();
}

// define document root
if (!defined("ROOT")) {
  define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
}

// to measure runtime
if (!isset($_starttime)) {
  $_starttime = microtime(true);
}

// load DB/Locale/Datetime configuration
if (file_exists(ROOT."config/config.php")) {
  require_once(ROOT."config/config.php");
}
else {
    echo "FATAL ERROR: Configuration file <span style='font-family: monospace'>config/config.php</span> does not exist.";
    die();
}

// load language file
// separate code and locales
$locale = $config['locale'];
require_once(ROOT."locales/".$locale.".php");



// using meekrodb in procedural way
require_once(ROOT."core/lib/meekrodb.2.3.class.php");
DB::$host = $config['db']['host'];
DB::$port = $config['db']['port'];
DB::$user = $config['db']['username'];
DB::$password = $config['db']['password'];
DB::$dbName = $config['db']['dbname'];
DB::$encoding = $config['db']['charset'];



// import functions
require_once(ROOT."core/function.inc.php");




// fetch site settings
$rs_settings = DB::query("SELECT * FROM common_settings");
$settings = [];
foreach($rs_settings as $k => $v) {
  $settings[$v['name']] = $v['data'];
}



// initialize output
if (!isset($output)) {
  $output = [];
}


// init $_GET['act']
if (!array_key_exists("act", $_GET)) {
  $_GET['act'] = "";
}


// initialize user session
if (!array_key_exists("uid", $_SESSION)) {
  $_SESSION['uid'] = 0;
}


// fetch current userinfo
$member = getUserInfo();
