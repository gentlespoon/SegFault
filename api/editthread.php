<?php

define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
require(ROOT."core/core.php");

//@param id of thread to edit
//@param old content of thread to be appended
//@param new content to append onto old
//@return 0 on failure, 1 otherwise
function EditThread($tid, $oldContent, $newContent) {
  //We avoid using CONCAT here as that would require using
  //DB::sqleval with user-submitted data, which isn't safe
  $newContent = $oldContent."<br />".$newContent;

  $table = "forum_threads";
  $set = array('content' => $newContent); //what we are setting with this update
  $cond = "tid=%i";

  DB::update($table, $set, $cond, $tid);

  return DB::affectedRows(); //if no rows changed, already editted
}

$result = array('success' => 0);

if (!is_numeric($_GET['tid'])) {
  exit("? tid");
}

if (empty($_GET['content'])) {
  exit("? content");
}

$getThreadResult = forum::getThread($_GET['tid']);

if ($getThreadResult['success'] !== 1) {
    exit("Could not get thread info");
}

$thread = $getThreadResult['message'];

if ($GLOBALS['curUser']['gid'] < 2 && $GLOBALS['curUser']['uid'] !== $thread['author']['uid']) {
  exit("Insufficient Permissions");
}

$result['success'] = EditThread($thread['tid'], $thread['content'], $_GET['content']);

echo json_encode($result);
