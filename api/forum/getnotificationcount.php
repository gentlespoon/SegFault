<?php
//get a list new threads
$notSeen = DB::query("SELECT tid FROM forum_update WHERE uid=%i", $GLOBALS['curUser']['uid']);
//printv($notSeen);
if (!empty($notSeen))
	api_write(1, count($notSeen));
else
	api_write(0, "no new threads");

