<?php

class member {

  public static function register($username, $password, $email) {
    // validate username;
    if ($username=="") {
      return ["success" => 0, "message" => $GLOBALS['lang']["blank-username"]];
    } else {
      $validUsername = member::validUsername($username);
      if (!$validUsername['success']) {
        return $validUsername;
      }
    }
    // validate password
    if ($password=="") {
      return ["success" => 0, "message" => "Empty password"];
    }
    // validate email
    if ($email=="") {
      return ["success" => 0, "message" => "Empty email"];
    } else {
      $validEmail = member::validEmail($email);
      if (!$validEmail['success']) {
        return $validEmail;
      }
    }
    // generate password salt and encrypt password
    $salt = randomStr(6);
    $encryptedPassword = md5($password.$salt);

    DB::query("INSERT INTO member (username, email, password, salt, regdate, regip) VALUES ( %s , %s , %s, %s, %s, %s)", $username, $email, $encryptedPassword, $salt, $GLOBALS['now'], $_SERVER['REMOTE_ADDR']);

    return ["success" => 1, "message" => ""];
  }



  public static function usernameCensor($string) {
    $list = str_split("!@#$%^&*{[(<>)]};'\" `~?/\\|=+");
    $restrictedNames = DB::query("SELECT word FROM member_restrictname");
    foreach ($restrictedNames as $k => $v) { array_push($list, $v['word']); }
    foreach ($list as $k => $v) {
      if ( strpos($string, $v) !== false ) { return $v; }
    }
    return false;
  }



  public static function validUsername($username) {
    // check if username contains illegal characters
    $illegalChar = member::usernameCensor($username);
    if ($illegalChar) {
      return ["success" => 0, "message" => "Illegal username containing ".$illegalChar];
    }
    // check for duplicate username
    $existedUser = DB::query("SELECT uid FROM member WHERE username=%s", $username);
    if (!empty($existedUser)) {
      return ["success" => 0, "message" => $GLOBALS['lang']["username-dup"]];
    }
    return ["success" => 1, "message" => ""];
  }



  public static function validEmail($email) {
    // check to see if this is an email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return ["success" => 0, "message" => "Illegal email address"];
    }
    // check for duplicate email
    $existedUser = DB::query("SELECT uid FROM member WHERE email=%s", $email);
    if (!empty($existedUser)) {
      return ["success" => 0, "message" => $GLOBALS['lang']["email-dup"]];
    }
    return ["success" => 1, "message" => ""];
  }



  public static function login($username, $password) {
    if ($username == "") {
      return ["success" => 0, "message" => $GLOBALS['lang']["blank-username"]];
    }
    // Check if this IP has recent failed login history
    $failedCount = DB::query("SELECT count(*) FROM member_loginhistory WHERE IP=%s AND success=0 AND login_date>%i", $_SERVER['REMOTE_ADDR'], $GLOBALS['now']-3600*12)[0]['count(*)'];
    if ($failedCount >= 10) {
      // If more than 10 failed attempts in 12 hours, consider this IP trying to brute force. Temporarily ban this IP
      return ["success" => 0, "message" => $GLOBALS['lang']['too-many-failed-attempts']];
    }
    // Record this login attempt
    DB::query("INSERT INTO member_loginhistory (username, login_date, ip) VALUES (%s, %i, %s)", $username, $GLOBALS['now'], $_SERVER['REMOTE_ADDR']);
    $historyId = DB::query("SELECT id FROM member_loginhistory WHERE username=%s AND ip=%s", $username, $_SERVER['REMOTE_ADDR'])[0]['id'];
    // Fetch userinfo
    $attemptedUser = DB::query("SELECT uid, password, salt, failcount, lastattempt FROM member WHERE username=%s", $username);
    if (empty($attemptedUser)) {
      // if username does not exist
      return ["success" => 0, "message" => $GLOBALS['lang']["username-dne"]];
    }
    $attemptedUser = $attemptedUser[0];
    // If this user has many failed attempts, give him a time penalty
    if ($attemptedUser['failcount'] > 2) {
      $bantime = 5*pow(2, $attemptedUser['failcount']);
      if ($attemptedUser['lastattempt'] > $GLOBALS['now']-$bantime) {
        // if this user still in failed attempt penalty
        return ["success" => 0, "message" => $GLOBALS['lang']['fail-penalty1'].($attemptedUser['lastattempt']+$bantime-$GLOBALS['now'])." (/".$bantime.")".$GLOBALS['lang']['fail-penalty2']];
      }
    }
    // If the user is not in penalty, encrypt the password and compare
    $encryptedPassword = md5($password.$attemptedUser['salt']);
    if ($encryptedPassword != $attemptedUser['password']) {
      // Incorrect Password
      DB::query("UPDATE member SET lastattempt=%i, failcount=failcount+1 WHERE uid=%i", $GLOBALS['now'], $attemptedUser['uid']);
      return ["success" => 0, "message" => $GLOBALS['lang']['invalid-cred']];
    } else {
      // Password OK
      $_SESSION['uid'] = $attemptedUser['uid'];
      DB::query("UPDATE member_loginhistory SET success=1, uid=%i WHERE id=%i", $_SESSION['uid'], $historyId);
      DB::query("UPDATE member SET lastattempt=%i, failcount=0 WHERE uid=%i", $GLOBALS['now'], $_SESSION['uid']);
      $GLOBALS['curUser'] = member::getUserInfo();
      return ["success" => 1, "message" => ""];
    }
  }



  public static function logout() {
    $_SESSION['username'] = "";
    $_SESSION['uid'] = 0;
    $GLOBALS['curUser'] = member::getUserInfo();
  }



  // modify user profile
  public static function modProfile($data) {
    $uid = $data['uid'];
    unset($data['uid']);
    $fields = member::getAvailableFields($uid);
    // printv($fields);
    foreach ($data as $key => $value) {
      if (!$fields[$key]) {
        return ["success" => 0, "message" => "Illegal DB field: ".$key];
      }
    }
    DB::query("UPDATE member SET %? WHERE uid=%i", $data, $uid);
    return ["success" => 1, "message" => $GLOBALS['lang']["modprofile-done"]];
    $GLOBALS['curUser'] = getUserInfo();
  }



  public static function getFields($uid) {
    $fields = member::getAvailableFields($uid);
    if (!empty($fields)) {
      $fieldStr = "";
      $editable = 0;
      foreach($fields as $k => $v) {
        $fieldStr .= $k." ,";
        $editable += $v;
      }
      $fieldStr = substr($fieldStr, 0, -2);
      $result = DB::query("SELECT ".$fieldStr." FROM member WHERE uid=%i", $uid)[0];
      if (!empty($result)) {
        foreach ($result as $k => $v) {
          $fields[$k] = ["value" => $v, "editable" => $fields[$k]];
        }
      }
      $fields['editable'] = $editable;
      return ["success" => 1, "message" => $fields];
    } else {
      return ["success" => 1, "message" => []];
    }
  }



  private static function getAvailableFields($uid) {
    // default permission for viewing curUser's own profile
    // -1:--   0:r-   1:rw
    $fields = [
      'uid' => 0,
      'avatar' => 1,
      'username' => 1,
      'email' => 1,
      // 'password' => 1,
      // 'salt' => 0,
      'posts' => 0,
      'threads' => 0,
      'gid' => 0,
      'regdate' => 0,
      'lastlogin' => 0,
      'lastattempt' => 0,
      'failcount' => 0,
      'regip' => 0,
    ];
    if ($uid != $_SESSION['uid']) {
      if (!$GLOBALS['curUser']['viewprofile']) {
        // if curUser is not allowed to view other's profile
        return []; // return an empty array
      }
      if (!$GLOBALS['curUser']['modprofile']) {
        // if not admin, do not allow modprofile, make all fields readonly
        foreach ($fields as $k => $v) {
          $fields[$k] = 0;
        }
      }
    }
    if ($GLOBALS['curUser']['admin']) {
      $fields['gid'] = 1;
    }
    return $fields;
  }



  // fetch userinfo
  public static function getUserInfo($uid=NULL) {
    if ($uid == NULL) $uid = $_SESSION['uid'];
    $userInfo = DB::query("SELECT member_groups.*, member.* FROM member_groups LEFT JOIN member ON member_groups.gid = member.gid WHERE member.uid=%i", $uid)[0];
    return $userInfo;
  }



  public static function getUGroupInfo() {
    $groups = DB::query("SELECT * FROM member_groups");
    foreach ($groups as $row) {
      $groups[$row['gid']] = $row;
    }
    return $groups;
  }










}
