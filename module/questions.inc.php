<?php

// This is the questions page

$output['title'] = "Questions";


$tags = DB::query("SELECT * FROM forum_tags");
foreach ($tags as $k => $v) {
  $output['tags'][$v['tagid']] = $v['tagname'];
}


$additionalSearchCondition = "";


switch ($action) {


  case "advice":
    $output['title'] = "Before asking a question...";
    break;



  case "asking":
  $output['title'] = "Ask your question";
    if (!$member['newthread']) {
      error($lang['permission-denied'], 403);
    }
    break;


  case "asked":
    if (!$member['newthread']) {
      error($lang['permission-denied'], 403);
    }
    DB::query("INSERT INTO forum_threads (title, content, tags, sendtime, uid) VALUES (%s, %s, %s, %i, %i)", $_POST['title'], $_POST['editedHTML'], $_POST['tags'], $now, $_SESSION['uid']);
    // do not get the last inserted id
    // since in high concurrency the last inserted id may be different
    // use last tid from the uid instead
    $tid = DB::query("SELECT tid FROM forum_threads WHERE uid=%i ORDER BY sendtime DESC", $_SESSION['uid'])[0]['tid'];
    $output['title'] = "Asked Successfully";
    alert($lang['new-thread-success'], "alert-success");
    redirect(5, "/questions/viewthread/".$tid);
    break;



  case "answer":
      if (!$member['newpost']) {
        error($lang['permission-denied'], 403);
      }
      DB::query("INSERT INTO forum_posts (tid, content, sendtime, uid) VALUES (%i, %s, %i, %i)", $_POST['tid'], $_POST['editedHTML'], $now, $_SESSION['uid']);
      $output['title'] = "Answered Successfully";
      alert($lang['new-post-success'], "alert-success");
      redirect(5, "/questions/viewthread/".$_POST['tid']);
      break;




  case "viewthread":
    if (!$member['viewthread']) {
      error($lang['permission-denied']);
    }

    // printv($path);
    if (is_numeric($path[2])) {

      // fetch thread
      $thread = DB::query("SELECT * FROM forum_threads WHERE tid=%i", $path[2]);
      if (!empty($thread)) {
        $thread = $thread[0];
        $thread['tags'] = explode(",", $thread['tags']);
        $thread['author'] = member::getUserInfo($thread['uid']);
        $thread['sendtime'] = toUserTime($thread['sendtime']);
        // $thread['content'] = htmlentities($thread['content']);
        // $thread['content'] = $thread['content'];
        $output['thread'] = $thread;
      }

      // fetch posts
      $posts = DB::query("SELECT * FROM forum_posts WHERE tid=%i ORDER BY sendtime ASC", $path[2]);
      foreach($posts as $k => $post) {
        $posts[$k]['author'] = member::getUserInfo($post['uid']);
        $posts[$k]['sendtime'] = toUserTime($post['sendtime']);
        // $posts[$k]['content'] = htmlentities($post['content']);
        $posts[$k]['content'] = $post['content'];
      }
      $output['posts'] = $posts;

      // printv($output['thread']);
      // printv($output['posts']);
    } else {
      error($lang['illegal-thread']);
    }
    break;











  case "search":
    if (array_key_exists("keyword", $_GET)) {
      $additionalSearchCondition .= "AND ((".makeLikeCond("title", $_GET['keyword'], " ", true).") OR (".makeLikeCond("content", $_GET['keyword'], " ", true)."))";
    } elseif (array_key_exists("tag", $_GET)) {
      $additionalSearchCondition .= "AND (".makeLikeCond("tags", $_GET['tag'], ",").")";
    } elseif (array_key_exists("uid", $_GET)) {
      $additionalSearchCondition .= "AND uid=".$_GET['uid'];
    } elseif (array_key_exists("username", $_GET)) {
      $uid = DB::query("SELECT uid FROM member WHERE username=%s", $_GET['username']);
      if (!empty($uid)) {
        $uid = $uid[0]['uid'];
      }
      $additionalSearchCondition .= "AND uid=".$uid;
    }
    // do not break here!!! let it go to default branch and search!


  default:
  // no action = list newest questions
    $action = "search";


    $sql = "SELECT forum_threads.* FROM forum_threads WHERE visible<=%i ".$additionalSearchCondition." ORDER BY sendtime DESC LIMIT 10";
    // echo $sql."<br />";
    $threads = DB::query($sql, $member['gid']);
    if (empty($threads)) {
      $output['threads'] = [];
      alert("No Records", "alert-info");
      break;
    }

    foreach ($threads as $k => $v) {
      $threads[$k]['tags'] = explode(",", $threads[$k]['tags']);
      // if Question description is longer than $summaryCharLimit, cut it at the nearest whitespace and append ...
      $summaryCharLimit = 300;
      if (strlen($threads[$k]['content'])>$summaryCharLimit) {
        $threads[$k]['content'] = substr($threads[$k]['content'], 0, strpos($threads[$k]['content'], " ", $summaryCharLimit-10))." ...";
      }
      $threads[$k]['content'] = strip_tags($threads[$k]['content']);
      $threads[$k]['author'] = member::getUserInfo($threads[$k]['uid']);
      $threads[$k]['sendtime'] = toUserTime($v['sendtime']);
      // get last response info
      $threads[$k]['lastreply'] = DB::query("SELECT member.username, forum_posts.sendtime, forum_posts.uid FROM forum_posts LEFT JOIN member ON member.uid=forum_posts.uid WHERE tid=%i ORDER BY sendtime DESC LIMIT 1", $threads[$k]['tid']);
      if (!empty($threads[$k]['lastreply'])) {
        $threads[$k]['lastreply'] = $threads[$k]['lastreply'][0];
        $threads[$k]['lastreply']['sendtime'] = toUserTime($threads[$k]['lastreply']['sendtime']);
      }
    }

    $output['threads'] = $threads;
    break;

}










template("questions");
