<?php

$GLOBALS['output']['title'] = "Tags";

$tags = tags::getTags();
$favTags = tags::getFavTags($_SESSION['uid']);

$sortedTags = [];
foreach($favTags as $tagid => $taginfo) {
  $sortedTags[$tagid] = $taginfo;
  unset($tags[$tagid]);
}
foreach($tags as $tagid => $taginfo) {
  $sortedTags[$tagid] = $taginfo;
}

$GLOBALS['output']['tags'] = $sortedTags;


template("tags");
