<?php

$output['title'] = "Tags";

$tags = DB::query("SELECT * FROM forum_tags");
foreach ($tags as $k => $v) {
  $output['tags'][$v['tagid']] = [
    "tagname" => $v['tagname'],
    "count" => $v['count'],
    "description" => $v['description'],
  ];
}

template("tags");
