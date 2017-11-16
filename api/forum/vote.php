<?php

  if ($GLOBALS['curUser']['uid'] === "0") {
    api_write(0, "Insufficient permissions");
  }

  if (
    !array_key_exists("ud", $_GET) ||
    !array_key_exists("tid", $_GET) ||
    !array_key_exists("pid", $_GET)
  ) {
    api_write(0, "Insufficient arguments");
  }

  switch($_GET['ud']) {
    case "upvote":
      $vote = 1;
      break;
    case "downvote":
      $vote = -1;
      break;
    default:
      api_write(0, "Invalid ud");
  }

  if (!is_numeric($_GET['tid'])) {
    api_write(0, "Incorrect tid");
  } else {
    if ($_GET['tid']>0) {
      $cond = "tid";
      $table = "forum_threads";
      $value = $_GET['tid'];
    }
  }

  if (!is_numeric($_GET['pid'])) {
    api_write(0, "Invalid pid");
  } else {
    if ($_GET['pid']>0) {
      $cond = "pid";
      $table = "forum_posts";
      $value = $_GET['pid'];
    }
  }

  if ($_GET['pid']==0 && $_GET['tid']==0) {
    api_write(0, "Invalid pid and tid");
  }

  $votes_table = "forum_votes";
  
  $curVote = DB::query("SELECT * FROM %l0 WHERE %l1 = %i2 AND uid = %i3", $votes_table, $cond, $value, $GLOBALS['curUser']['uid']);
  
  if (count($curVote) > 1 || count($curVote) < 0) { //if we have more than 1 entry for a users vote on a post/thread, something has gone horribly wrong
    api_write(0, "Database error");
  }
  else if (count($curVote) === 0) { //if no entry for voting on this post/thread by this user, make it
    DB::query("INSERT INTO %l0 (uid, tid, pid, vote, mutex) 
               VALUES (%i1, %i2, %i3, %i4, 0)", 
               $votes_table, 
               $GLOBALS['curUser']['uid'], $_GET['tid'], $_GET['pid'], $vote);
    DB::query("UPDATE %l0 SET %l1 = %l1+1 WHERE %l2 = %i3", $table, $_GET['ud'], $cond, $value);
  }
  else { //update user's vote on this post/thread
    //First we attempt to mutex the row
    DB::query("UPDATE %l0 SET mutex = 1 WHERE %l1 = %i2 AND uid = %i3", $votes_table, $cond, $value, $GLOBALS['curUser']['uid']);

    if (DB::affectedRows() === 0) { //If we fail, abort
      api_write(0, "Vote already processing");
    }

    $curVote = $curVote[0];
    settype($curVote['vote'], "int"); //DB query returns things as strings, we want vote to be an int

    //Determine if cancelling old vote or changing old vote
    if ($curVote['vote'] === $vote) {
      $newVote = 0;
    }
    else {
      $newVote = $vote;
    }

    //update total vote count for post/thread
    if ($curVote['vote'] === 1) {
      DB::query("UPDATE %l0 SET upvote = upvote-1 WHERE %l1 = %i2", $table, $cond, $value);
    }
    else if ($curVote['vote'] === -1) {
      DB::query("UPDATE %l0 SET downvote = downvote-1 WHERE %l1 = %i2", $table, $cond, $value);
    }

    if ($newVote !== 0) {
      DB::query("UPDATE %l0 SET %l1 = %l1+1 WHERE %l2 = %i3", $table, $_GET['ud'], $cond, $value);
    }

    //update entry in relation table, release mutex
    DB::query("UPDATE %l0 SET vote = %i4, mutex = 0 WHERE %l1 = %i2 AND uid = %i3", $votes_table, $cond, $value, $GLOBALS['curUser']['uid'], $newVote);
  }

  $newScore = DB::query("SELECT upvote, downvote FROM ".$table." WHERE ".$cond."= %i", $_GET['tid']+$_GET['pid'])[0];

  api_write(1, $newScore);
