<?php
  if (!array_key_exists("tagid", $_GET) || !is_numeric($_GET['tagid'])) {
    api_write(0, "Invalid tagid");
  }

  $favTag = DB::query("SELECT * FROM forum_favtags WHERE tagid=%i AND uid=%i", $_GET['tagid'], $GLOBALS['curUser']['uid']);

  if (!empty($favTag)) {
    $result = DB::query("DELETE FROM forum_favtags WHERE tagid=%i AND uid=%i", $_GET['tagid'], $GLOBALS['curUser']['uid']);
    if ($result) {
      api_write(1, "removeFavTag OK");
    } else {
      api_write(0, "removeFavTag Failed");
    }
  } else {
    api_write(0, "Not favTag");
  }
