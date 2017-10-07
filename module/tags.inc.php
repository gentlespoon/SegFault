<?php

$output['title'] = "Tags";

$tags = DB::query("SELECT * FROM forum_tags");
foreach ($tags as $k => $v) {
  $output['tags'][$v['tagid']] = [
    "tagname" => $v['tagname'],
    "count" => DB::query("SELECT COUNT(*) FROM forum_threads WHERE ".makeLikeCond("tags", $v['tagid']))[0]['COUNT(*)'],
    "description" => $v['description'],
  ];
}



template("tags");
