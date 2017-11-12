<?php

class forum {
  
  /**
   * @param  thread id
   * @return [success, thread content]
   */
  public static function getThread($tid) {
    if (!$GLOBALS['curUser']['viewthread']) error($GLOBALS['lang']['permission-denied']);
    $result = DB::query("SELECT forum_threads.*, member.username, member.avatar, member.uid FROM forum_threads LEFT JOIN member ON member.uid=forum_threads.uid WHERE tid=%i", $tid);
    if (!empty($result)) {
      $thread = $result[0];
      $thread['tags'] = explode(",", $thread['tags']);
      // $thread['author'] = member::getUserInfo($thread['uid']);
      $thread['sendtime'] = toUserTime($thread['sendtime']);
      return ["success" => 1, "message" => $thread];
    } else {
      return ["success" => 0, "message" => $GLOBALS['lang']["invalid-thread-id"]];
    }
  }



  public static function getThreadCount() {
  }


  /**
   * @param  thread id
   * @return [success, post count in this thread]
   */
  public static function getPostCount($tid) {
    $posts = DB::query("SELECT count(*) FROM forum_posts WHERE tid=%i", $tid)[0]["count(*)"];
    return ["success" => 1, "message" => $posts];
  }


  /**
   * @param  thread id
   * @param  how many posts to get? default = 5
   * @param  offset
   * @return [success, $posts[]]
   */
  public static function getPosts($tid, $count=5, $offset) {
    $posts = DB::query("SELECT forum_posts.*, member.username, member.avatar, member.uid FROM forum_posts LEFT JOIN member ON member.uid=forum_posts.uid WHERE tid=%i ORDER BY upvote DESC LIMIT %i OFFSET %i", $tid, $count, $offset);
    foreach($posts as $k => $post) {
      // $posts[$k]['author'] = member::getUserInfo($post['uid']);
      // $posts[$k]['author'] = ["username" => $post['username'], "avatar" => $post['avatar']];
      $posts[$k]['sendtime'] = toUserTime($post['sendtime']);
      // $posts[$k]['content'] = htmlentities($post['content']);
      $posts[$k]['content'] = $post['content'];
    }
    // printv($posts);
    return ["success" => 1, "message" => $posts];
  }


  /**
   * @param  post id
   * @return [success, post content]
   */
  public static function getPost($pid) {
    if (!$GLOBALS['curUser']['viewthread']) error($GLOBALS['lang']['permission-denied']);
    $result = DB::query("SELECT forum_posts.*, member.username, member.avatar, member.uid FROM forum_posts LEFT JOIN member ON member.uid=forum_posts.uid WHERE pid=%i", $pid);
    if (!empty($result)) {
      $post = $result[0];
      // $post['author'] = member::getUserInfo($post['uid']);
      $post['sendtime'] = toUserTime($post['sendtime']);
      return ["success" => 1, "message" => $post];
    } else {
      return ["success" => 0, "message" => $GLOBALS['lang']["invalid-thread-id"]];
    }
  }


  /**
   * @param  thread id
   * @param  thread content
   * @return [success, message]
   */
  public static function newPost($tid, $content) {
    if (!$GLOBALS['curUser']['newpost']) {
      return ["success" => 0, "message" => $GLOBALS['lang']['permission-denied']];
    }
    if (DB::query("INSERT INTO forum_posts (tid, content, sendtime, uid) VALUES (%i, %s, %i, %i)", $tid, $content, $GLOBALS['now'], $_SESSION['uid'])) {
      return ["success" => 1, "message" => $GLOBALS['lang']["new-post-success"]];
    } else {
      return ["success" => 0, "message" => "newPost failed"];
    }
  }


  /**
   * @param  title
   * @param  content
   * @param  tags[]
   * @return [success, thread id]
   */
  public static function newThread($title, $content, $tags) {
    if (!$GLOBALS['curUser']['newthread']) {
      return ["success" => 0, "message" => $GLOBALS['lang']['permission-denied']];
    }
    if (DB::query("INSERT INTO forum_threads (title, content, tags, sendtime, uid) VALUES (%s, %s, %s, %i, %i)", $title, $content, $tags, $GLOBALS['now'], $_SESSION['uid'])) {
      // do not get the last inserted id
      // since in high concurrency the last inserted id may be different
      // use last tid from the uid instead
      $tid = DB::query("SELECT tid FROM forum_threads WHERE uid=%i ORDER BY sendtime DESC", $_SESSION['uid'])[0]['tid'];
      return ["success" => 1, "message" => $tid];
    } else {
      return ["success" => 0, "message" => "newThread failed"];
    }
  }








};
