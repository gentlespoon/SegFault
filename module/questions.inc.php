<?php

// This is the questions page

$GLOBALS['output']['title'] = "Questions";

$GLOBALS['output']['tags'] = tags::getTags();
$GLOBALS['output']['favTags'] = tags::getFavTags($_SESSION['uid']);

$additionalSearchCondition = "";

switch ($action) {

  case "advice":
    $GLOBALS['output']['title'] = "Before asking a question...";
    break;



  case "asking":
  $GLOBALS['output']['title'] = "Ask your question";
    if (!$GLOBALS['curUser']['newthread']) {
      error($GLOBALS['lang']['permission-denied'], 403);
    }
    break;



  case "asked":
    $result = forum::newThread($_POST['title'], $_POST['editedHTML'], $_POST['tags']);
    if ($result['success']) {
      $GLOBALS['output']['title'] = "Asked Successfully";
      alert($GLOBALS['lang']['new-thread-success'], GREEN);
      redirect(5, "/questions/viewthread/".$result['message']);
    } else {
      $GLOBALS['output']['title'] = "Error";
      error($result['message'], RED);
    }    
    break;



  case "answer":
    $result = forum::newPost($_POST['tid'], $_POST['editedHTML']);
    if ($result['success']) {
      $GLOBALS['output']['title'] = "Answered";
      alert($result['message'], GREEN);
      redirect(5, "/questions/viewthread/".$_POST['tid']);
    } else {
      $GLOBALS['output']['title'] = "Error";
      error($result['message'], RED);
    }
    break;



  case "viewthread":
    if (!is_numeric($path[2])) exit($GLOBALS['lang']['illegal-thread']);
      $tid = $path[2];
      $result = forum::getThread($tid);
      if ($result['success']) {
        $GLOBALS['output']['thread'] = $result['message'];
      } else {
        error($result['message'], RED);
      }

      // fetch posts
      $GLOBALS['output']['postCount'] = forum::getPostCount($tid)['message'];
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

    $GLOBALS['output']['threadCount'] = DB::query("SELECT count(*) FROM forum_threads WHERE visible<=%i ".$additionalSearchCondition, $GLOBALS['curUser']['gid'])[0]["count(*)"];

    $offset = 0;
    // this SQL can be unsafe!!
    $sql = "SELECT forum_threads.*, member.username, member.uid FROM forum_threads LEFT JOIN member ON member.uid=forum_threads.uid WHERE visible<=%i ".$additionalSearchCondition." ORDER BY sendtime DESC LIMIT 20 OFFSET %i";
    // echo $sql."<br />";
    $threads = DB::query($sql, $GLOBALS['curUser']['gid'], $offset);
    if (empty($threads)) {
      $GLOBALS['output']['threads'] = [];
      alert("No Records", BLUE);
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
      // $threads[$k]['author'] = member::getUserInfo($threads[$k]['uid']);
      $threads[$k]['sendtime'] = toUserTime($v['sendtime']);
      // get last response info
      $threads[$k]['lastreply'] = DB::query("SELECT member.username, forum_posts.sendtime, forum_posts.uid FROM forum_posts LEFT JOIN member ON member.uid=forum_posts.uid WHERE tid=%i ORDER BY sendtime DESC LIMIT 1", $threads[$k]['tid']);
      if (!empty($threads[$k]['lastreply'])) {
        $threads[$k]['lastreply'] = $threads[$k]['lastreply'][0];
        $threads[$k]['lastreply']['sendtime'] = toUserTime($threads[$k]['lastreply']['sendtime']);
      }
    }

    // printv($threads);
    $GLOBALS['output']['threads'] = $threads;
    break;

}










template("questions");
