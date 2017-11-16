<?php

// This is the entry point of the entire site, basically a router

define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
require(ROOT."core/core.php");

// path => filename (/module/*.inc.php)
$route = [
  "member",
  "questions",
  "tags",
  "update"
];

$GLOBALS['output']['activeNav'] = [];
foreach($route as $v) $GLOBALS['output']['activeNav'][$v] = "";

if (array_key_exists("path", $_GET)) $path = explode("/", $_GET['path']);
else $path = ["questions"]; // default page

if ($path[0] == "api") {
  unset($path[0]);
  $file = ROOT."api/".implode("/", $path).".php";
  if (is_file($file)) {
    require_once($file);
  }
  else {
    header("HTTP/1.0 400 Bad Request");
  }
}
else {
  if (in_array($path[0], $route)) {
    $action = "";
    if (isset($path[1])) $action = $path[1];
    $GLOBALS['output']['activeNav'][$path[0]] = "active";
    require_once("module/".$path[0].".inc.php");
  } else {
    error("404 Not Found", 404);
  }
}
