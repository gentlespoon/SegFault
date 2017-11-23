<?php
  if (
    !array_key_exists("search", $_GET) || empty($_GET['search']) ||
    !array_key_exists("offset", $_GET) || !is_numeric($_GET['offset']) ||
    !array_key_exists("count", $_GET) || !is_numeric($_GET['count'])
  ) {
    api_write(0, "Insufficient arguments");
  }

  $search = new search();
  $search->addCond("visible<=%i", $GLOBALS['curUser']['gid']);
  $search->addSearchConditions($_GET['search']);
  $result = forum::getThreads($_GET['count'], $_GET['offset'], $search->getWhereCond());
  $result['isModerator'] = $GLOBALS['curUser']['gid']>=2 ? true : false;
  api_write($result);
