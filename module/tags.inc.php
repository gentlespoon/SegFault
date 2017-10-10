<?php

$output['title'] = "Tags";

$tags = DB::query("SELECT * FROM forum_tags ORDER BY count DESC");
foreach ($tags as $k => $v) {
  $output['tags'][$v['tagid']] = [
    "tagname" => $v['tagname'],
    "count" => $v['count'],
    "description" => $v['description'],
  ];
}

template("tags");
