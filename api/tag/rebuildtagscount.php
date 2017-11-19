<?php
  $tags = DB::query("SELECT tagid FROM forum_tags");
  foreach ($tags as $k => $v) {
    DB::query("UPDATE forum_tags SET count=(SELECT COUNT(*) FROM forum_threads WHERE %l) WHERE tagid=%i", makeWhereLikeCond("tags", $v['tagid'], ","), $v['tagid']);
  }
  echo json_encode(["success" => 1, "message" => ""]);
