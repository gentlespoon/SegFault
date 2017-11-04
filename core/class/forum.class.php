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
      return ["success" => 1, "message" => $thread];
    } else {
      return ["success" => 0, "message" => $GLOBALS['lang']["invalid-thread-id"]];
    }
  }



  public static function getThreadCount() {
  }



  public static function getPostCount($tid) {
    $posts = DB::query("SELECT count(*) FROM forum_posts WHERE tid=%i", $tid)[0]["count(*)"];
    return ["success" => 1, "message" => $posts];
  }



  public static function getPosts($tid, $count, $offset) {
    $posts = DB::query("SELECT * FROM forum_posts WHERE tid=%i ORDER BY sendtime ASC LIMIT %i OFFSET %i", $tid, $count, $offset);
    foreach($posts as $k => $post) {
      $posts[$k]['author'] = member::getUserInfo($post['uid']);
      $posts[$k]['sendtime'] = toUserTime($post['sendtime']);
      // $posts[$k]['content'] = htmlentities($post['content']);
      $posts[$k]['content'] = $post['content'];
    }
    return ["success" => 1, "message" => $posts];
  }



  public static function post($tid, $content) {
    if (!$GLOBALS['curUser']['newpost']) {
      return ["success" => 0, "message" => $GLOBALS['lang']['permission-denied']];
    }
    DB::query("INSERT INTO forum_posts (tid, content, sendtime, uid) VALUES (%i, %s, %i, %i)", $tid, $content, $GLOBALS['now'], $_SESSION['uid']);
    return ["success" => 1, "message" => $GLOBALS['lang']["new-post-success"]];
  }












};
