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
  api_write(0, "Invalid tid");
}

if ($GLOBALS['curUser']['gid'] < 2) {
  api_write(0, $GLOBALS['lang']['permission-denied']);
}

$result = LockThread($_GET['tid']);

api_write($result);
