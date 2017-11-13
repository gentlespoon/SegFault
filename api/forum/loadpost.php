<?php
  if (
    !array_key_exists("tid", $_GET) || !is_numeric($_GET['tid']) ||
    !array_key_exists("offset", $_GET) || !is_numeric($_GET['offset'])
  ) {
    api_write(0, "Insufficient arguments");
  }

  $result = forum::getPosts($_GET['tid'], 5, $_GET['offset']);
  $result['isModerator'] = $GLOBALS['curUser']['gid']>=2 ? true : false;
  api_write($result);
