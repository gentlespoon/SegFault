<?php

define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
require(ROOT."core/core.php");

switch($_GET['ud']) {
  case "upvote":
    break;
  case "downvote":
    break;
  default:
    exit("? ud");
}

if (!is_numeric($_GET['tid'])) {
  exit("? tid");
} else {
  if ($_GET['tid']>0) {
    $cond = "tid";
    $table = "forum_threads";
  }
}

if (!is_numeric($_GET['pid'])) {
  exit("? pid");
} else {
  if ($_GET['pid']>0) {
    $cond = "pid";
    $table = "forum_posts";
  }
}

DB::query("UPDATE ".$table." SET ".$_GET['ud']." = ".$_GET['ud']."+1 WHERE ".$cond." = %i", $_GET['tid']+$_GET['pid']);

$newScore = DB::query("SELECT upvote, downvote FROM ".$table." WHERE ".$cond."= %i", $_GET['tid']+$_GET['pid'])[0];

echo json_encode($newScore);
