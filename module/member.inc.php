<?php

$output['title'] = "Member";

if (!array_key_exists("redirect", $_GET)) {
  $_GET['redirect'] = "/";
}

switch ($action) {


  case "register":
    $output['title'] = $lang['register'];
    if (array_key_exists("username", $_POST)) {
      // when user submits a reg
      // username will not be empty
      // so we know it is submitting, instead of requesting for a reg form
      if (array_key_exists("password", $_POST)) {
        // if both field exists

        if ($_SESSION['uid'] < 1) {
          // If user is currently guest

          // if empty username
          if ($_POST['username'] == "") {
            alert($lang['blank-username'], "alert-danger");
            break;
          }

          // check for marks in username
          $list = "!@#$%^&*{[(<>)]};'\" `~?/\\|=+";
          $list = str_split($list);
          if (usernameCensor($_POST['username'], $list)) {
            alert($lang['invalid-username-1'].$unamecheck.$lang['invalid-username-3'], "alert-danger");
            break;
          }
          // check for restricted usernames
          $restrictedNames = DB::query("SELECT word FROM member_restrictname");
          $list = [];
          foreach ($restrictedNames as $k => $v) {
            array_push($list, $v['word']);
          }
          if (usernameCensor($_POST['username'], $list)) {
            alert($lang['invalid-username-2'].$unamecheck.$lang['invalid-username-3'], "alert-danger");
            break;
          }

          // check for duplicate username
          $existedUser = DB::query("SELECT uid FROM member WHERE username=%s", $_POST['username']);
          if (!empty($existedUser)) {
            alert($lang['username-dup'], "alert-danger");
            break;
          }
          // check for duplicate email
          $existedUser = DB::query("SELECT uid FROM member WHERE email=%s", $_POST['email']);
          if (!empty($existedUser)) {
            alert($lang['email-dup'], "alert-danger");
            break;
          }

          // if both Username and Email OK

          // generate password salt
          $salt = randomStr(6);

          // encrypt password
          $encryptedPassword = md5($_POST['password'].$salt);

          // register this new user
          DB::query("INSERT INTO member (username, email, password, salt, regdate, lastattempt) VALUES ( %s , %s , %s , %s, %s, %s)", $_POST['username'], $_POST['email'], $encryptedPassword, $salt, $now, $now);

          // log this user in
          $member = getUserInfo();
          $_SESSION['uid'] = DB::query("SELECT uid FROM member WHERE username=%s", $_POST['username'])[0]['uid'];
          DB::query("INSERT INTO member_loginhistory (uid, username, login_date, success, ip) VALUES (%i, %s, %s, %s, %s)", $_SESSION['uid'], $member['username'], $now, 1, $_SERVER['REMOTE_ADDR']);
          DB::query("UPDATE member SET lastlogin= %s, failcount=0 WHERE uid= %i", $now, $_SESSION['uid']);
          alert($lang['registered-welcome'], "alert-success");

          // redirect user to fill their profile
          // redirect(5, "/member/modprofile");

          // redirect user to previous page
          redirect(5, $_GET['redirect']);

        } else {
          // Already logged in, do not allow re-register
          alert($lang['logged-in'], "alert-success");
          redirect(3, $_GET['redirect']);
          break;
        }
      }
    }
    break;







  case "login":
    $title = $lang['login'];
    if (array_key_exists("username", $_POST)) {
      // when user submits a login
      // username will not be empty
      // so we know it is submitting, instead of requesting for a login form
      if (array_key_exists("password", $_POST)) {
        // if both field exists

        if ($_SESSION['uid'] < 1) {
          // If user is currently guest

          // Check if this IP has recent failed login history
          $failedCount = DB::query("SELECT count(*) FROM member_loginhistory WHERE IP=%s AND success=0 AND login_date>%i", $_SERVER['REMOTE_ADDR'], $now-3600*12)[0]['count(*)'];
          if ($failedCount >= 10) {
            // More than 10 failed attempts in 12 hours.
            // Consider this IP trying to brute force.
            // Temporarily ban this IP
            alert($lang['too-many-failed-attempts'], "alert-danger");
            break;
          }

          // Record this login attempt
          DB::query("INSERT INTO member_loginhistory (username, login_date, ip) VALUES (%s, %i, %s)", $_POST['username'], $now, $_SERVER['REMOTE_ADDR']);
          $historyId = DB::query("SELECT id FROM member_loginhistory WHERE username=%s AND ip=%s", $_POST['username'], $_SERVER['REMOTE_ADDR'])[0]['id'];

          // Check if this username exists
          $attemptedUser = DB::query("SELECT uid, password, salt, failcount, lastattempt FROM member WHERE username=%s", $_POST['username']);
          if (empty($attemptedUser)) {
            // if username does not exist
            alert($lang['username-dne'], "alert-danger");
            break;
          }

          // If the user exists
          $attemptedUser = $attemptedUser[0];

          // If this user has many failed attempts
          // Give him a time penalty
          if ($attemptedUser['failcount'] > 2) {
            $bantime = 5*pow(2, $attemptedUser['failcount']);
            if ($attemptedUser['lastattempt'] > $now-$bantime) {
              // if this user still in failed attempt penalty
              alert($lang['fail-penalty1'].($attemptedUser['lastattempt']+$bantime-$now)." (/".$bantime.")".$lang['fail-penalty2'], "alert-danger");
              break;
            }
          }

          // If the user exists and is not in penalty, encrypt the password and compare
          $encryptedPassword = md5($_POST['password'].$attemptedUser['salt']);
          if ($encryptedPassword != $attemptedUser['password']) {
            // Incorrect Password
            DB::query("UPDATE member SET lastattempt=%i, failcount=failcount+1 WHERE uid=%i", $now, $attemptedUser['uid']);
            alert($lang['invalid-cred'], "alert-danger");

          } else {
            // Password OK
            $_SESSION['uid'] = $attemptedUser['uid'];
            $member = getUserInfo($_SESSION['uid']);
            DB::query("UPDATE member_loginhistory SET success=1, uid=%i WHERE id=%i", $_SESSION['uid'], $historyId);
            DB::query("UPDATE member SET lastattempt=%i, failcount=0 WHERE uid=%i", $now, $_SESSION['uid']);
            alert($lang['logged-in'], "alert-success");
            redirect(3, $_GET['redirect']);
          }

        } else {
          // if user is not guest (already logged in)
          alert($lang['logged-in'], "alert-success");
          redirect(3, $_GET['redirect']);
        }


      }
    }
    break;








    case "modprofile":
      $title = $lang['modprofile'];
      if (array_key_exists("username", $_POST)) {
        // fetch table keys
        $tablekeys = DB::query("SELECT * FROM member WHERE uid=%i", $_SESSION['uid']);
        foreach ($_POST as $k => $v) {
          // verify if key exists
          if(array_key_exists($k, $tablekeys[0])) {
            // if the key exists, then it is safe to update
            DB::query("UPDATE member SET ".$k."=%s WHERE uid=%i", $v, $_SESSION['uid']);
          } else {
            // $_POST contains illegal keys
            error("????????? ILLEGAL KEY ???", "alert-danger");
          }
        }

        alert($lang['modprofile-done'], "alert-success");

        // refresh userinfo
        $member = getUserInfo();
      }
      // select columns that are allowed to modify
      $member_fields = DB::query("SELECT * FROM member WHERE uid=%i",$_SESSION['uid']);
      $member['fields'] = $member_fields[0];
      unset($member['fields']['salt']);
      unset($member['fields']['posts']);
      unset($member['fields']['threads']);
      unset($member['fields']['gid']);
      unset($member['fields']['regdate']);
      unset($member['fields']['lastlogin']);
      unset($member['fields']['lastattempt']);
      unset($member['fields']['failcount']);
      unset($member['fields']['regip']);
      break;








  case "logout":
    $_SESSION['username'] = "";
    $_SESSION['uid'] = 0;
    alert($lang['logged-out'], "alert-success");
    redirect(5, $_GET['redirect']);
    break;







  default:
    // display member list

    // fetch usergroup information
    $uGroupInfo = [];
    $uGroupInfoTmp = DB::query("SELECT * FROM member_groups");
    foreach ($uGroupInfoTmp as $k => $v) {
      $uGroupInfo[$v['gid']] = $v['gname'];
    }

    // fetch member list
    $output['memberlist'] = DB::query("SELECT * FROM member");
    foreach ($output['memberlist'] as $k => $v) {
      unset($output['memberlist'][$k]['password']);
      unset($output['memberlist'][$k]['salt']);
      unset($output['memberlist'][$k]['salt']);
      $output['memberlist'][$k]['regdate'] = toUserTime($output['memberlist'][$k]['regdate']);
      $output['memberlist'][$k]['lastlogin'] = toUserTime($output['memberlist'][$k]['lastlogin']);
      $output['memberlist'][$k]['usergroup'] = $uGroupInfo[$output['memberlist'][$k]['gid']];
      unset($output['memberlist'][$k]['gid']);
      unset($output['memberlist'][$k]['failcount']);
      unset($output['memberlist'][$k]['lastattempt']);
      unset($output['memberlist'][$k]['regip']);
    }
    unset($output['memberlist'][0]);
}

template("member");
