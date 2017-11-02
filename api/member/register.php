<?php
  if (
    array_key_exists("username", $_GET) &&
    array_key_exists("password", $_GET) &&
    array_key_exists("email", $_GET)
  ) {
    echo json_encode( member::register($_GET['username'], $_GET['password'], $_GET['email']) );
  } else {
    echo json_encode( ["success"=>0, "msg"=>"Insufficient arguments"] );
  }
