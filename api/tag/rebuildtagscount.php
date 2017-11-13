<?php
  $tags = DB::query("SELECT tagid FROM forum_tags");
  foreach ($tags as $k => $v) {
    DB::query("UPDATE forum_tags SET count=(SELECT COUNT(*) FROM forum_threads WHERE ".makeLikeCond("tags", $v['tagid'], ",").") WHERE tagid=".$v['tagid']);
  }
  echo json_encode(["success" => 1, "message" => ""]);
