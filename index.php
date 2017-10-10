<?php

// This is the entry point of the entire site
//
// Basically a router

define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
require(ROOT."core/core.php");


// path => filename (/module/*.inc.php)
$route = [
  "jobs" => "jobs",
  "member" => "member",
  "questions" => "questions",
  "tags" => "tags",
];

$output['activeNav'] = [];
foreach($route as $k => $v) {
  $output['activeNav'][$k] = "";
}


// /$1/$2/$3
// $1 = moduleName (in /module)
// $2 = action
// $3... = real $_GET

if (array_key_exists("path", $_GET)) {
  $path = explode("/", $_GET['path']);
} else {
  $path = ["questions"]; // default page
}

$action = "";
if (isset($path[1])) {
  $action = $path[1];
}


if (array_key_exists($path[0], $route)) {
  $output['activeNav'][$path[0]] = "active";
  require_once("module/".$route[$path[0]].".inc.php");
} else {
  error("404 Not Found", 404);
}
