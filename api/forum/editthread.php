<?php

//@param id of thread to edit
//@param old content of thread to be appended
//@param new content to append onto old
//@return 0 on failure, 1 otherwise
function EditThread($tid, $oldContent, $newContent) {
  //We avoid using CONCAT here as that would require using
  //DB::sqleval with user-submitted data, which isn't safe
  //Escape user-submitted data so that we can use sqleval
  //allowing for an atomic update to avoid race conditions
  $newContent = "'" . DB::get()->real_escape_string(strval("<br />".$newContent)) . "'";

  $table = "forum_threads";
  $set = array('content' => DB::sqleval("CONCAT(content, ".$newContent.")")); //what we are setting with this update
  $cond = "tid=%i";

  DB::update($table, $set, $cond, $tid);

  return DB::affectedRows(); //if no rows changed, already editted
}

$result = 0;

if (!array_key_exists('tid', $_GET) || !is_numeric($_GET['tid'])) {
  api_write(0, "Invalid tid");
}

if (!array_key_exists('content', $_GET) || empty($_GET['content'])) {
  api_write(0, "Invalid content");
}

$getThreadResult = forum::getThread($_GET['tid']);

if ($getThreadResult['success'] !== 1) {
    api_write(0, "Cannot get thread");
}

$thread = $getThreadResult['message'];

if ($GLOBALS['curUser']['gid'] < 2 && $GLOBALS['curUser']['uid'] !== $thread['author']['uid']) {
  api_write(0, $GLOBALS['lang']["permission-denied"]);
}

$result = EditThread($thread['tid'], $thread['content'], $_GET['content']);

api_write($result);
