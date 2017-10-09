<?php

define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
require(ROOT."core/core.php");


$tags = DB::query("SELECT tagid FROM forum_tags");
foreach ($tags as $k => $v) {
  DB::query("UPDATE forum_tags SET count=(SELECT COUNT(*) FROM forum_threads WHERE ".makeLikeCond("tags", $v['tagid']).") WHERE tagid=".$v['tagid']);
}

echo "0";

// $sql = "UPDATE forum_tags SET count=(SELECT COUNT(*) FROM forum_threads WHERE forum_tags.tagid LIKE '%,forum_tags.tagid,%')";
