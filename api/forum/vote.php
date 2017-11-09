<?php
  if (
    !array_key_exists("ud", $_GET) ||
    !array_key_exists("tid", $_GET) ||
    !array_key_exists("pid", $_GET)
  ) {
    api_write(0, "Insufficient arguments");
  }

  switch($_GET['ud']) {
    case "upvote":
      break;
    case "downvote":
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
    }
  }

  if (!is_numeric($_GET['pid'])) {
    api_write(0, "Invalid pid");
  } else {
    if ($_GET['pid']>0) {
      $cond = "pid";
      $table = "forum_posts";
    }
  }

  if ($_GET['pid']==0 && $_GET['tid']==0) {
    api_write(0, "Invalid pid and tid");
  }

  DB::query("UPDATE ".$table." SET ".$_GET['ud']." = ".$_GET['ud']."+1 WHERE ".$cond." = %i", $_GET['tid']+$_GET['pid']);

  $newScore = DB::query("SELECT upvote, downvote FROM ".$table." WHERE ".$cond."= %i", $_GET['tid']+$_GET['pid'])[0];

  api_write(1, $newScore);
