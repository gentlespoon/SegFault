<?php
  if (
    array_key_exists("tid", $_GET) && $_GET['tid']!="" &&
    array_key_exists("offset", $_GET) && $_GET['offset']!=""
  ) {
    api_write(forum::getPosts($_GET['tid'], 5, $_GET['offset']));
  } else {
    api_write( ["success"=>0, "msg"=>"Insufficient arguments"] );
  }
