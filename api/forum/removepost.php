<?php

//@param id of post to remove
//@return 0 on failure, 1 otherwise
function RemovePost($pid) {
  $table = "forum_posts";
  $set = array('visible' => 99); //what we are setting with this update
  $cond = "pid=%i";

  DB::update($table, $set, $cond, $pid);
  
  return DB::affectedRows(); //if no rows changed, already removed
}

$result = 0;

if (!array_key_exists('pid', $_GET) || !is_numeric($_GET['pid'])) {
  api_write(0, "Invalid pid");
}

$getPostResult = forum::getPost($_GET['pid']);

if ($getPostResult['success'] !== 1) {
  api_write(0, "Cannot get post");
}

$post = $getPostResult['message'];

if ($GLOBALS['curUser']['gid'] < 2 && $GLOBALS['curUser']['uid'] !== $post['author']['uid']) {
  api_write(0, $GLOBALS['lang']["permission-denied"]);
}

$result = RemovePost($post['pid']);

api_write($result);
