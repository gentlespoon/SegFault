<?php

define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
require(ROOT."core/core.php");

//@param id of post to edit
//@param old content of post to be appended
//@param new content to append onto old
//@return 0 on failure, 1 otherwise
function EditPost($pid, $oldContent, $newContent) {
  //We avoid using CONCAT here as that would require using
  //DB::sqleval with user-submitted data, which isn't safe
  $newContent = $oldContent."<br />".$newContent;

  $table = "forum_posts";
  $set = array("content" => $newContent); //what we are setting with this update
  $cond = "pid=%i";

  DB::update($table, $set, $cond, $pid);

  return DB::affectedRows(); //if no rows changed, already editted
}

$result = array('success' => 0);

if (!is_numeric($_GET['pid'])) {
  exit("? pid");
}

if (empty($_GET['content'])) {
  exit("? content");
}

$getPostResult = forum::getPost($_GET['pid']);

if ($getPostResult['success'] !== 1) {
  exit("Could not get post info");
}

$post = $getPostResult['message'];

if ($GLOBALS['curUser']['gid'] < 2 && $GLOBALS['curUser']['uid'] !== $post['author']['uid']) {
  exit("Insufficient Permissions");
}

$result['success'] = EditPost($post['pid'], $post['content'], $_GET['content']);

echo json_encode($result);
