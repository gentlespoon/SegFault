<?php

define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
require(ROOT."core/core.php");

//@param id of thread to remove
//@return 0 on failure, 1 otherwise
function RemoveThread($tid) {
  $table = "forum_threads";
  $set = array('visible' => 99, 'open' => 99); //what we are setting with this update
  $cond = "tid=%i";

  DB::update($table, $set, $cond, $tid);

  return DB::affectedRows(); //if no rows changed, already removed
}

$result = array('success' => 0);

if (!is_numeric($_GET['tid'])) {
  exit("? tid");
}

$getThreadResult = forum::getThread($_GET['tid']);

if ($getThreadResult['success'] !== 1) {
    exit("Could not get thread info");
}

$thread = $getThreadResult['message'];

if ($GLOBALS['curUser']['gid'] < 2 && $GLOBALS['curUser']['uid'] !== $thread['author']['uid']) {
  exit("Insufficient Permissions");
}

$result['success'] = RemoveThread($thread['tid']);

echo json_encode($result);
