<?php
  if (
    !array_key_exists("username", $_GET) || $_GET['username']=="" ||
    !array_key_exists("password", $_GET) || $_GET['password']==""
  ) {
    api_write(0, "Insufficient arguments");
  }

  api_write(member::login($_GET['username'], $_GET['password']));
