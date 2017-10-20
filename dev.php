<?php

define("ROOT", $_SERVER['DOCUMENT_ROOT']."/");
require(ROOT."core/core.php");



print_r(DB::query("SELECT * FROM member"));
