<?php

// This is the questions page

$output['title'] = "Questions";

switch ($action) {

  case "viewthread":

    break;


  default:
    // no action = list newest questions
    if (!array_key_exists("viewforum", $member) ||
        (array_key_exists("viewforum", $member) && $member['viewforum'] == 0)) {
      $_GET['act'] = "";
      $output['alerttype'] = "alert-danger";
      $output['alert'] = $lang['permission-denied'];
      break;
    }

    $threads = DB::query("SELECT forum_threads.* FROM forum_threads WHERE category=1 ORDER BY sendtime DESC LIMIT 10");
    if (empty($threads)) {
      $output['alerttype'] = "alert-danger";
      $output['alert'] = "0 questions found";
      break;
    }

    foreach ($threads as $k => $v) {
      $threads[$k]['author'] = getUserInfo($threads[$k]['author']);
      $threads[$k]['sendtime'] = toUserTime($v['sendtime']);
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
