<?php

class forum {

  public static function getThread($tid) {
    if (!$GLOBALS['curUser']['viewthread']) error($GLOBALS['lang']['permission-denied']);
    $result = DB::query("SELECT * FROM forum_threads WHERE tid=%i", $tid);
    if (!empty($result)) {
      $thread = $result[0];
      $thread['tags'] = explode(",", $thread['tags']);
      $thread['author'] = member::getUserInfo($thread['uid']);
      $thread['sendtime'] = toUserTime($thread['sendtime']);

      // printv($thread);
      return ["success" => 1, "message" => $thread];
    } else {
      return ["success" => 0, "message" => $GLOBALS['lang']["invalid-thread-id"]];
    }
  }



  public static function getPosts($tid, $offset) {
    $posts = DB::query("SELECT * FROM forum_posts WHERE tid=%i ORDER BY sendtime ASC LIMIT 4 OFFSET %i", $tid, $offset);
    foreach($posts as $k => $post) {
      $posts[$k]['author'] = member::getUserInfo($post['uid']);
      $posts[$k]['sendtime'] = toUserTime($post['sendtime']);
      // $posts[$k]['content'] = htmlentities($post['content']);
      $posts[$k]['content'] = $post['content'];
    }
    return ["success" => 1, "message" => $posts];
  }
















};
