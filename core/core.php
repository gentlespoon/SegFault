<?php

date_default_timezone_set("UTC");
$GLOBALS['now'] = time();
$GLOBALS['queryCount'] = 0;
// show ALL errors because we are developing.
error_reporting(E_ALL);
// start a session
if (!isset($_SESSION)) session_start();
// should be already defined in the entry index.php file
if (!defined("ROOT")) define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
// to measure runtime
if (!isset($GLOBALS['startTime'])) $GLOBALS['startTime'] = microtime(true);

// load DB/Locale/Datetime configuration
if (file_exists(ROOT."config/config.php")) {
  require_once(ROOT."config/config.php");
} else {
  exit("FATAL ERROR: Configuration file <span style='font-family: monospace'>config/config.php</span> does not exist.");
}
// load language file, not necessary for this project, but always a good practice to separate code and translations
// import functions
require_once(ROOT."core/function.inc.php");
require_once(ROOT."core/constant.php");
foreach (scandir(ROOT.'core/class') as $filename) {
  $path = ROOT.'core/class/'.$filename;
  if (is_file($path)) require_once $path;
}
require_once(ROOT."locales/".$config['locale'].".php");


// using meekrodb in procedural way
// require_once(ROOT."core/lib/meekrodb.2.3.class.php");
DB::$host = $config['db']['host'];
DB::$port = $config['db']['port'];
DB::$user = $config['db']['username'];
DB::$password = $config['db']['password'];
DB::$dbName = $config['db']['dbname'];
DB::$encoding = $config['db']['charset'];

// initialize output
if (!isset($GLOBALS['output'])) $GLOBALS['output'] = [ "alert" => [], "title" => ""];

// initialize user session if first time visitor
if (!array_key_exists("uid", $_SESSION)) {
  $_SESSION['uid'] = 0;
  $_SESSION['visitCounter']=-1;
}

$_SESSION['visitCounter']++;

// fetch current userinfo
$GLOBALS['curUser'] = member::getUserInfo();
