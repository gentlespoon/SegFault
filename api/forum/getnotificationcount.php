<?php
//get a list new threads
//printv($notSeen[0]["count(*)"]);
api_write(1, DB::query("SELECT count(*) FROM forum_update WHERE uid=%i", $GLOBALS['curUser']['uid'])[0]["count(*)"]);
// if ($notSeen[0][0])
// 	api_write(1, $notSeen);
// else
// 	api_write(0, "no new threads");

