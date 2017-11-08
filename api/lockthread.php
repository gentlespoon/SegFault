<?php

define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
require(ROOT."core/core.php");

//@param id of thread to lock
//@return 0 on failure, 1 otherwise
function LockThread($tid) {
  $table = "forum_threads";
  $set = array('open' => 2); //what we are setting with this update
  $cond = "tid=%i";

  DB::update($table, $set, $cond, $tid);

  return DB::affectedRows(); //if no rows changed, already locked
}

$result = array('success' => 0);

if (!is_numeric($_GET['tid'])) {
  exit("? tid");
}

if ($GLOBALS['curUser']['gid'] < 2) {
  exit("Insufficient Permissions");
}

$result['success'] = LockThread($_GET['tid']);

echo json_encode($result);
