<?php

$result = array('success' => 0, 'message' => "Unknown Error");

if (!array_key_exists('tid', $_GET) || !is_numeric($_GET['tid'])) {
  api_write(0, "Invalid tid");
}

if (!array_key_exists('content', $_GET) || empty($_GET['content'])) {
  api_write(0, "Invalid content");
}

$result = forum::edit(array('type' => "thread", 'id' => $_GET['tid']), $_GET['content']);

api_write($result);
