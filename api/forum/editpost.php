<?php

$result = array('success' => 0, 'message' => "Unknown Error");

if (!array_key_exists('pid', $_GET) || !is_numeric($_GET['pid'])) {
  api_write(0, "Invalid pid");
}

if (!array_key_exists('content', $_GET) || empty($_GET['content'])) {
  api_write(0, "Invalid content");
}

$result = forum::edit(array('type' => "post", 'id' => $_GET['pid']), $_GET['content']);

api_write($result);
