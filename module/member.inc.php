<?php

$output['title'] = "Member";

if (!array_key_exists("redirect", $_GET)) {
  $_GET['redirect'] = "/";
}

switch ($action) {


  case "register":
    $output['title'] = $lang['register'];
    if (array_key_exists("username", $_POST)) {
      if (array_key_exists("password", $_POST)) {
        if ($_SESSION['uid'] < 1) {
          // begin registration

          // if empty username
          if ($_POST['username'] == "") {
            // Invalid username with marks
            alert($lang['blank-username'], "alert-danger");
            break;
          }

          // check for marks in username
          $list = "!@#$%^&*{[(<>)]};'\" `~?/\\|=+";
          $list = str_split($list);
          $unamecheck = usernameCensor($_POST['username'], $list);
          if ($unamecheck) {
            // Invalid username with marks
            alert($lang['invalid-username-1'].$unamecheck.$lang['invalid-username-3'], "alert-danger");
            break;
          }

          // check for restricted usernames
          $r = DB::query("SELECT word FROM member_restrictname");
          $list = [];
          foreach ($r as $k => $v) {
            array_push($list, $v['word']);
          }
          $unamecheck = usernameCensor($_POST['username'], $list);
          if ($unamecheck) {
            // Invalid username with marks
            alert($lang['invalid-username-2'].$unamecheck.$lang['invalid-username-3'], "alert-danger");
            break;
          }

          // check for duplicate usernames
          $r = DB::query("SELECT uid FROM member WHERE username=%s", $_POST['username']);
          if (!empty($r)) {
            // Username already registered
            alert($lang['username-dup'], "alert-danger");
            break;
          }

          // generate password salt
          $salt = randomStr(6);

          // encrypt password
          $encryptedPassword = md5($_POST['password'].$salt);

          // register this new user
          DB::query("INSERT INTO member (username, email, password, salt, regdate) VALUES ( %s , %s , %s , %s, %s)", $_POST['username'], $_POST['email'], $encryptedPassword, $salt, time());

          // fetch the new user's uid
          $uid = DB::query("SELECT uid FROM member WHERE username=%s", $_POST['username'])[0]['uid'];

          // log this new user in
          $_SESSION['uid'] = $uid;

          // insert login history
          DB::query("INSERT INTO member_loginhistory (uid, logindate, success, ip) VALUES ( %i, %s, %s, %s)", $uid, time(), 1, $_SERVER['REMOTE_ADDR']);

          // clear failed login count
          DB::query("UPDATE member SET lastlogin= %s, failcount=0 WHERE uid= %i", time(), $uid);

          // fetch userinfo
          $member = getUserInfo();

          alert($settings['registered-welcome'], "alert-success");

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
      } else {
        // Some fields do not exist
        alert($lang['empty-field'], "alert-danger");
        break;
      }
    }
    break;







  case "login":
    $title = $lang['login'];
    if (array_key_exists("username", $_POST)) {
      if (array_key_exists("password", $_POST)) {
        if ($_SESSION['uid'] < 1) {

          // check ipfail count
          $r = DB::query("SELECT lasttrial, count FROM member_failedip WHERE ip=%s", $_SERVER['REMOTE_ADDR']);
          if (!empty($r)) {
            if ($r[0]['count']>10) {
              if (($r[0]['lasttrial']+3600*24) > time()) {
                // if temp ip ban still enforce
                alert($lang['ip-ban-temp1'].(int)( (($r[0]['lasttrial']+3600*24) - time())/3600 ).$lang['ip-ban-temp2'], "alert-danger");
                break;
              }
            }
          }

          // fetch userinfo
          $r = DB::query("SELECT uid, password, salt, failcount FROM member WHERE username= %s", $_POST['username']);
          if (empty($r)) {
            // User does not exist
            alert($lang['username-dne'], "alert-danger");
            break;
          }

          // calculate fail login penalty time
          $bantime = 10*pow(2, $r[0]['failcount']);

          // fetch last trial time
          $s = DB::query("SELECT logindate FROM member_loginhistory WHERE uid=%i ORDER BY logindate DESC", $r[0]['uid']);
          if (!empty($s)) {
            if (($s[0]['logindate']+$bantime)>time()) {
              // login failed penalty

              alert($lang['fail-penalty1'].($s[0]['logindate']+$bantime-time())." (/".$bantime.")".$lang['fail-penalty2'], "alert-danger");
              break;
            }
          }

          // if no penalty, check password
          $encryptedPassword = md5($_POST['password'].$r[0]['salt']);
          if ($encryptedPassword == $r[0]['password']) {

            // credentials correct
            // log this user in
            $_SESSION['uid'] = $r[0]['uid'];
            // fetch userinfo
            $member = getUserInfo();

            // insert login history
            DB::query("INSERT INTO member_loginhistory (uid, logindate, success, ip) VALUES (%i, %s, %i, %s)", $_SESSION['uid'], time(), 1, $_SERVER['REMOTE_ADDR']);
            // clear loginfail count
            DB::query("UPDATE member SET lastlogin=%s, failcount=0 WHERE uid=%i", time(), $_SESSION['uid']);
            // clear ipfail count
            $t = DB::query("SELECT ip, lasttrial, count, attempted FROM member_failedip WHERE ip=%s", $_SERVER['REMOTE_ADDR']);
            if (empty($t)) {
              DB::query("INSERT INTO member_failedip (ip, lasttrial, count) VALUES (%s, %s, 0) ON DUPLICATE KEY UPDATE count=0, lasttrial=%s", $_SERVER['REMOTE_ADDR'], time(), time());
            }
            alert($lang['logged-in'], "alert-success");
            redirect(3, $_GET['redirect']);


          } else {

            // Incorrect credentials
            // insert login history
            DB::query("INSERT INTO member_loginhistory (uid, logindate, success, ip) VALUES (%i, %s, %i, %s)", $r[0]['uid'], time(), 0, $_SERVER['REMOTE_ADDR']);
            // increase loginfail count
            DB::query("UPDATE member SET failcount=failcount+1 WHERE uid=%i", $r[0]['uid']);
            // increase ipfail count
            $t = DB::query("SELECT ip, lasttrial, count, attempted FROM member_failedip WHERE ip=%s", $_SERVER['REMOTE_ADDR']);
            if (empty($t)) {
              DB::query("INSERT INTO member_failedip (ip, lasttrial, count) VALUES (%s, %s, 0) ON DUPLICATE KEY UPDATE count=0, lasttrial=%s", $_SERVER['REMOTE_ADDR'], time(), time());
            } else {
              $attempted = $t[0]['attempted'];
              if (strlen($attempted)!=0) {
                $attempted .= ", ";
              }
              $attempted .= $_POST['username'];
              DB::query("UPDATE member_failedip SET lasttrial=".time().", count=count+1, attempted=%s WHERE ip=%s", $attempted, $_SERVER['REMOTE_ADDR']);
            }
            alert($lang['invalid-cred'], "alert-danger");
            break;
          }
        } else {
          // Already logged in, do not allow re-register
          alert($lang['logged-in'], "alert-success");
          redirect(3, $_GET['redirect']);
        }
      } else {
        // Some fields do not exist
        alert($lang['empty-field'], "alert-danger");
        break;
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

        alert($lang['modprofile'].$lang['success'], "alert-success");

        // refresh userinfo
        $member = getUserInfo();
      }
      // select columns that are allowed to modify
      $member_fields = DB::query("SELECT * FROM member WHERE uid=%i",$_SESSION['uid']);
      $member['fields'] = $member_fields[0];
      unset($member['fields']['salt']);
      break;








  case "logout":
    $_SESSION['username'] = "";
    $_SESSION['uid'] = 0;
    alert($lang['logged-out'], "alert-success");
    break;







  default:
    // display member list

    // fetch usergroup information
    $uGroupInfo = [];
    $uGroupInfoTmp = DB::query("SELECT * FROM member_groups");
    foreach ($uGroupInfoTmp as $k => $v) {
      $uGroupInfo[$v['groupid']] = $v['groupname'];
    }

    // fetch member list
    $output['memberlist'] = DB::query("SELECT * FROM member");
    foreach ($output['memberlist'] as $k => $v) {
      unset($output['memberlist'][$k]['password']);
      unset($output['memberlist'][$k]['salt']);
      unset($output['memberlist'][$k]['salt']);
      $output['memberlist'][$k]['regdate'] = toUserTime($output['memberlist'][$k]['regdate']);
      $output['memberlist'][$k]['lastlogin'] = toUserTime($output['memberlist'][$k]['lastlogin']);
      $output['memberlist'][$k]['usergroup'] = $uGroupInfo[$output['memberlist'][$k]['groupid']];
      unset($output['memberlist'][$k]['groupid']);
      unset($output['memberlist'][$k]['failcount']);
    }
    unset($output['memberlist'][0]);
}

template("member");
