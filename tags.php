<?php

define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
require(ROOT."core/core.php");



$output['title'] = "Tags";

$tags = DB::query("SELECT * FROM tags");
foreach ($tags as $k => $v) {
  $output['tags'][$v['tagid']] = [
    "tagname" => $v['tagname'],
    "count" => $v['count']
  ];
}



template("tags");
