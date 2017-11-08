<?php

//@param id of thread to lock
//@return 0 on failure, 1 otherwise
function LockThread($tid) {
  $table = "forum_threads";
  $set = array('open' => 2); //what we are setting with this update
  $cond = "tid=%i";

  DB::update($table, $set, $cond, $tid);

  return DB::affectedRows(); //if no rows changed, already locked
}

$result = 0;

if (!array_key_exists('tid', $_GET) || !is_numeric($_GET['tid'])) {
  exit("? tid");
}

if ($GLOBALS['curUser']['gid'] < 2) {
  exit("Insufficient Permissions");
}

$result = LockThread($_GET['tid']);

echo api_write($result);
