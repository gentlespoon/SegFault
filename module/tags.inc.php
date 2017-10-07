<?php

$output['title'] = "Tags";

function getTagsThreadsCount($tagid) {
  $tagsCondition =  "tags LIKE '".$tagid.",%' OR ".   // as first tag
                    "tags LIKE '%,".$tagid.",%' OR ".  // as middle tag
                    "tags LIKE '%,".$tagid."' OR ".   // as last tag
                    "tags LIKE '".$tagid."'";        // as only tag
  // echo $tagsCondition."<br />";
  // printv(DB::query("SELECT * FROM forum_threads WHERE ".$tagsCondition));
  return DB::query("SELECT COUNT(*) FROM forum_threads WHERE ".$tagsCondition)[0]['COUNT(*)'];
}

$tags = DB::query("SELECT * FROM forum_tags");
foreach ($tags as $k => $v) {
  $output['tags'][$v['tagid']] = [
    "tagname" => $v['tagname'],
    "count" => getTagsThreadsCount($v['tagid']),
    "description" => $v['description'],
  ];
}



template("tags");
