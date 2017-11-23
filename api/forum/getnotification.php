<?php

 $notSeen = DB::query("SELECT forum_update.tid, forum_threads.title FROM forum_update LEFT JOIN forum_threads ON forum_threads.tid=forum_update.tid WHERE forum_update.uid=%i LIMIT 15", $GLOBALS['curUser']['uid']);
//printv($notSeen);
 $tidArray = [];
 foreach ($notSeen as $key => $value) {
 	$temp = [$value['tid'], $value['title']];
 	if (!in_array($temp, $tidArray)) {
 		array_push($tidArray, $temp);
 	}
 }
 DB::query("DELETE FROM forum_update WHERE uid = %i", $GLOBALS['curUser']['uid']);


api_write(1, $tidArray);
// printv($tidArray);