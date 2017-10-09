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

echo $output[1];

//
// TODO:
//
// $tags => [
//   tag0 => [
//     tagname =>
//     description =>
//     count =>
//   ],
//   tag1 => [
//     tagname =>
//     description =>
//     count =>
//   ],
//   tag2 => [
//     tagname =>
//     description =>
//     count =>
//   ],
// ]
//
//
// How do we sort $tags by count?
//

template("tags");
