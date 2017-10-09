<?php

// This is the questions page

$output['title'] = "Questions";


$tags = DB::query("SELECT * FROM forum_tags");
foreach ($tags as $k => $v) {
  $output['tags'][$v['tagid']] = $v['tagname'];
}


$additionalSearchCondition = "";


switch ($action) {




  case "ask":

    break;








  case "viewthread":
    if (!$member['viewthread']) {
      error($lang['permission-denied']);
    }

    // printv($path);
    if (is_numeric($path[2])) {

      // fetch thread
      $thread = DB::query("SELECT * FROM forum_threads WHERE tid=%i", $path[2])[0];
      $thread['tags'] = explode(",", $thread['tags']);
      $thread['author'] = getUserInfo($thread['author']);
      $thread['sendtime'] = toUserTime($thread['sendtime']);
      $thread['content'] = htmlentities($thread['content']);
      $output['thread'] = $thread;

      // fetch posts
      $posts = DB::query("SELECT * FROM forum_posts WHERE tid=%i ORDER BY sendtime ASC", $path[2]);
      foreach($posts as $k => $post) {
        $posts[$k]['author'] = getUserInfo($post['author']);
        $posts[$k]['sendtime'] = toUserTime($post['sendtime']);
        $posts[$k]['content'] = htmlentities($post['content']);
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
      $additionalSearchCondition .= "AND author=".$_GET['uid'];
    }
    // do not break here!!! let it go to default branch and search!


  default:
  // no action = list newest questions
    $action = "search";

    if (!$member['viewforum']) {
      $output['threads'] = [];
      alert($lang['permission-denied'], "alert-danger");
      break;
    }


    $sql = "SELECT forum_threads.* FROM forum_threads WHERE category=1 ".$additionalSearchCondition." ORDER BY sendtime DESC LIMIT 10";
    // echo $sql."<br />";
    $threads = DB::query($sql);
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
      $threads[$k]['content'] = htmlentities($threads[$k]['content']);
      $threads[$k]['author'] = getUserInfo($threads[$k]['author']);
      $threads[$k]['sendtime'] = toUserTime($v['sendtime']);
      // get last response info
      $threads[$k]['lastreply'] = DB::query("SELECT member.username, forum_posts.sendtime, forum_posts.author FROM forum_posts LEFT JOIN member ON member.uid=forum_posts.author WHERE tid=%i ORDER BY sendtime DESC LIMIT 1", $threads[$k]['tid']);
      if (!empty($threads[$k]['lastreply'])) {
        $threads[$k]['lastreply'] = $threads[$k]['lastreply'][0];
        $threads[$k]['lastreply']['sendtime'] = toUserTime($threads[$k]['lastreply']['sendtime']);
      }
    }

    $output['threads'] = $threads;
    break;

}










template("questions");
