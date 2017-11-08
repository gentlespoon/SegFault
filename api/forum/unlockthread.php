<?php

//@param id of thread to unlock
//@return 0 on failure, 1 otherwise
function UnlockThread($tid) {
  $table = "forum_threads";
  $set = array('open' => 1); //what we are setting with this update
  $cond = "tid=%i";

  DB::update($table, $set, $cond, $tid);

  return DB::affectedRows(); //if no rows changed, already unlocked
}

$result = 0;

if (!array_key_exists('tid', $_GET) || !is_numeric($_GET['tid'])) {
  exit("? tid");
}

if ($GLOBALS['curUser']['gid'] < 2) {
  exit("Insufficient Permissions");
}

$result = UnlockThread($_GET['tid']);

echo api_write($result);
