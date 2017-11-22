<?php

$GLOBALS['output']['title'] = "Tags";

$tags = tags::getTags();
$favTags = tags::getFavTags($_SESSION['uid']);

$sortedTags = [];

foreach($tags as $tagid => $taginfo) {
  $sortedTags[$tagid] = $taginfo;
  $sortedTags[$tagid]['isFav'] = 0;
}

foreach($favTags as $tagid => $taginfo) {
  $sortedTags[$tagid] = $taginfo;
  unset($tags[$tagid]);
  $sortedTags[$tagid]['isFav'] = 1;
}

$GLOBALS['output']['tags'] = $sortedTags;

template("tags");
