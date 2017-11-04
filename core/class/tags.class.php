<?php

class tags {

  public static function getTags() {
    $tags = [];
    $result = DB::query("SELECT * FROM forum_tags ORDER BY count DESC");
    foreach ($result as $k => $v) {
      $tags[$v['tagid']] = [
        "tagname" => $v['tagname'],
        "count" => $v['count'],
        "description" => $v['description'],
      ];
    }
    return $tags;
  }



  public static function getFavTags($uid) {
    $result = DB::query("SELECT forum_favtags.tagid, forum_tags.* FROM forum_favtags LEFT JOIN forum_tags ON forum_tags.tagid=forum_favtags.tagid WHERE uid=%i ORDER BY forum_tags.count DESC", $uid);
    $tags = [];
    foreach ($result as $k => $v) {
      $tags[$v['tagid']] = [
        "tagname" => $v['tagname'],
        "count" => $v['count'],
        "description" => $v['description'],
      ];
    }
    return $tags;
  }



};
