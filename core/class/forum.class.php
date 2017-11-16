<?php

class forum {

  //Wrapper around real_escape_string
  //Taken from DB class in MeekroDB
  //Used by forum to escape user-supplied data before
  //passing to sqleval to prevent SQL injection
  protected static function sqlEscape($str) {
    return "'" . DB::get()->real_escape_string(strval($str)) . "'";
  }

  protected static function validIDArray($idArr) {
    return array_key_exists('type', $idArr) && ($idArr['type'] === "post" || $idArr['type'] === "thread") && 
           array_key_exists('id', $idArr) && is_numeric($idArr['id']);
  }

  protected static function postType($idArr) {
    if (!forum::validIDArray($idArr)) {
      return "error";
    }
    return $idArr['type'];
  }

  protected static function validPost($idArr) {
    if (!forum::validIDArray($idArr)) {
      return FALSE;
    }

    if (forum::postType($idArr) === "thread") {
      return forum::getThreadInfo($idArr['id'])['success'] === 1;
    }
    else {
      return forum::getPostInfo($idArr['id'])['success'] === 1;
    }
  }

  protected static function userCanEdit($idArr) {
    if (!forum::validIDArray($idArr)) {
      return FALSE;
    }

    if ($GLOBALS['curUser']['gid'] > 1) {
      return TRUE;
    }

    $post = forum::postType($idArr) === "thread" ? forum::getThreadInfo($idArr['id']) : forum::getPostInfo($idArr['id']);

    if ($post['success'] === 0) {
      return FALSE;
    }

    $post = $post['message'];

    $timeSinceCreation = time() - $post['sendtime'];
    $timeSinceCreation /= 60; //number of minutes since edit

    if ($post['author']['uid'] === $GLOBALS['curUser']['uid'] && $timeSinceCreation < 15) {
      return TRUE;
    }
  }

  /**
   * @param  thread id
   * @return [success, thread content]
   */
  protected static function getThreadInfo($tid) {
    if (!$GLOBALS['curUser']['viewthread']) error($GLOBALS['lang']['permission-denied']);
    $result = DB::query("SELECT * FROM forum_threads WHERE tid=%i", $tid);
    if (!empty($result)) {
      $thread = $result[0];
      $thread['tags'] = explode(",", $thread['tags']);
      $thread['author'] = member::getUserInfo($thread['uid']);
      return ["success" => 1, "message" => $thread];
    } else {
      return ["success" => 0, "message" => $GLOBALS['lang']["invalid-thread-id"]];
    }
  }

  /**
   * @param  post id
   * @return [success, post content]
   */
  protected static function getPostInfo($pid) {
    if (!$GLOBALS['curUser']['viewthread']) error($GLOBALS['lang']['permission-denied']);
    $result = DB::query("SELECT * FROM forum_posts WHERE pid=%i", $pid);
    if (!empty($result)) {
      $post = $result[0];
      $post['author'] = member::getUserInfo($post['uid']);
      return ["success" => 1, "message" => $post];
    } else {
      return ["success" => 0, "message" => $GLOBALS['lang']["invalid-thread-id"]];
    }
  }

  //@param 'idArr' {'type' => "thread/post", 'id' => id}
  public static function edit($idArr, $content) {
    if (!forum::validIDArray($idArr))
    {
      return array('success' => 0, 'message' => "improper 'idArr' parameter");
    }
    if (empty($content)) {
      return array('success' => 0, 'message' => "'content' parameter cannot be empty");
    }

    if (!forum::validPost($idArr)) {
      return array('success' => 0, 'message' => "invalid thread/post");
    }

    if (!forum::userCanEdit($idArr)) {
      return array('success' => 0, 'message' => "insufficient permissions");
    }

    $newContent = $content."<br /><p>- <i>Edited by ".$GLOBALS['curUser']['username']." at ".toUserTime(time())."</i></p>";
    
    $set = array("content" => $newContent); //what we are setting with this update
    $table = forum::postType($idArr) === "thread" ? "forum_threads" : "forum_posts";
    $cond = forum::postType($idArr) === "thread" ? "tid=%i" : "pid=%i";
    DB::update($table, $set, $cond, $idArr['id']);
    
    if (DB::affectedRows() === 0) {
      return array('success' => 0, 'message' => "database error");
    }

    return array('success' => 1, 'message' => "updated successfully");
  }



  /**
   * @param  thread id
   * @return [success, thread content]
   */
  public static function getThread($tid) {
    if (!$GLOBALS['curUser']['viewthread']) error($GLOBALS['lang']['permission-denied']);
    $result = DB::query("SELECT forum_threads.*, member.username, member.avatar, member.uid FROM forum_threads LEFT JOIN member ON member.uid=forum_threads.uid WHERE tid=%i AND visible<=%i", $tid, $GLOBALS['curUser']['gid']);
    if (!empty($result)) {
      $thread = $result[0];
      $thread['tags'] = explode(",", $thread['tags']);
      //$thread['author'] = member::getUserInfo($thread['uid']);
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
    $posts = DB::query("SELECT count(*) FROM forum_posts WHERE tid=%i AND visible<=%i", $tid, $GLOBALS['curUser']['gid'])[0]["count(*)"];
    return ["success" => 1, "message" => $posts];
  }


  /**
   * @param  thread id
   * @param  how many posts to get? default = 5
   * @param  offset
   * @return [success, $posts[]]
   */
  public static function getPosts($tid, $count=5, $offset) {
    $posts = DB::query("SELECT forum_posts.*, member.username, member.avatar, member.uid FROM forum_posts LEFT JOIN member ON member.uid=forum_posts.uid WHERE tid=%i AND visible<=%i ORDER BY upvote DESC LIMIT %i OFFSET %i", $tid, $GLOBALS['curUser']['gid'], $count, $offset);
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
      //$post['author'] = member::getUserInfo($post['uid']);
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
      DB::query("UPDATE member SET threads=threads+1 WHERE uid=%i", $GLOBALS['curUser']['uid']);
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
      DB::query("UPDATE member SET threads=threads+1 WHERE uid=%i", $GLOBALS['curUser']['uid']);
      $tags = explode(',', $tags);
      foreach ($tags as $key => $value) {
        DB::query("INSERT INTO forum_update (tagid, uid, tid) VALUES (%i, %i, %i)", $value, $_SESSION['uid'], $tid);
      }

      return ["success" => 1, "message" => $tid];
    } else {
      return ["success" => 0, "message" => "newThread failed"];
    }
  }


};
