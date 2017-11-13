<?php
  if (!array_key_exists("tagid", $_GET) || !is_numeric($_GET['tagid'])) {
    api_write(0, "Invalid tagid");
  }

  $favTag = DB::query("SELECT * FROM forum_favtags WHERE tagid=%i AND uid=%i", $_GET['tagid'], $GLOBALS['curUser']['uid']);

  if (empty($favTag)) {
    $result = DB::query("INSERT INTO forum_favtags (tagid, uid) VALUES (%i, %i)", $_GET['tagid'], $GLOBALS['curUser']['uid']);
    if ($result) {
      api_write(1, "addFavTag OK");
    } else {
      api_write(0, "addFavTag Failed");
    }
  } else {
    api_write(0, "Already favTag");
  }
