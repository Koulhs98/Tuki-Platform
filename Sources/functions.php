<?php

function GetMimeType($file) {

    $mimes = array(
        'gif' => 'image/gif',
        'html' => 'text/html',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'js' => 'application/x-javascript',
        'zip' => 'application/zip',
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'blend' => 'application/blender',
        'rar' => 'application/x-rar-compressed',
        "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "doc" => "application/msword",
        "pdf" => "application/pdf",
        "odt" => "application/vnd.oasis.opendocument.text",
        "txt" => "text/plain"
    );

    return $mimes[File_ext($file)];
}

function Detect_mobile_browser() {
    $android = strpos($_SERVER['HTTP_USER_AGENT'], 'Android');
    $iphone = strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone');
    $BlackBerry = strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry');
    $ipod = strpos($_SERVER['HTTP_USER_AGENT'], 'iPod');
    $webos = strpos($_SERVER['HTTP_USER_AGENT'], 'webOS');

    if (($android || $BlackBerry || $iphone || $ipod || $webos) == TRUE) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function filename_sanitize($filename) {
    $return = str_replace(array(
        "ö",
        "Ö",
        "ä",
        "Ä",
        "å",
        "Å",
        " ",
            ), array(
        "o",
        "O",
        "a",
        "A",
        "a",
        "A",
        "_",
            ), $filename);
    return preg_replace("/([^\.a-zA-Z0-9_-])/", "", $return);
}

function filesize_r($path) {
    if (!file_exists($path)) {
        return 0;
    }
    if (is_file($path)) {
        return filesize($path);
    }
    $ret = 0;
    foreach (glob($path . "/*") as $fn) {
        $ret += filesize_r($fn);
    }
    return $ret;
}

function rgb2hex($rgb) {
    $hex = '#';
    $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
    $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
    $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
    return $hex;
}

function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);

    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    $rgb = array($r, $g, $b);
    return $rgb; // returns an array with the rgb values
}


function File_ext($file) {
    return end(explode('.', $file));
}

function logged_in_redirect() {
    if (logged_in() === true) {
        header('Location: ' . SYSTEM_DOMAIN);
        exit();
    }
}

function login_protect() {
    if (logged_in() === false) {
        die(header('Location: ' . SYSTEM_DOMAIN . 'login/' . $_GET['page'] . '/' . $_GET['p']));
    }
}

function LogIn($username, $password) {
    global $mysqliconnection;

    if (logged_in() === TRUE) {
        return 5;
    }

    if (is_empty($username) === FALSE and is_empty($password) === FALSE) {
        $username = $mysqliconnection->real_escape_string($username);
        $password = $mysqliconnection->real_escape_string($password);
        $Sha1_password = sha1($username . $password);
        $query = "SELECT * FROM `" . MYSQL_TABLE_PREFIX . MYSQL_USERS_TABLE . "` WHERE `username` = '$username' AND `password` = '$Sha1_password'";
        if ($result = $mysqliconnection->query($query)) {
            $rows = $result->num_rows;
            $return = FALSE;
            while ($row = $result->fetch_assoc()) {
                if ($rows == 1 && $row['allow_login'] == 1) {
                    $_SESSION['ID'] = $row['id'];
                    $_SESSION['USERNAME'] = $username;
                    $_SESSION['LAST_LOGIN'] = user_data($row['id'], 'latest_visit');
                    ActivityLog('Logged in', 1);
                    $return = TRUE;     // Login was successful
                } else {
                    $return = FALSE;    // Login failed
                }
            }
            $result->free();
            if ($return === TRUE) {
                $query = 'UPDATE ' . MYSQL_TABLE_PREFIX . MYSQL_USERS_TABLE . ' SET `latest_visit` = UNIX_TIMESTAMP(NOW()) WHERE `id` = ' . $_SESSION['ID'];
                if (!$mysqliconnection->query($query)) {
                    ErrorLog('Query failed = ' . $query, 3);
                    die('Query failed');
                }
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                if (user_data($_SESSION['ID'], 'ip') != $ip) {
                    $query = 'UPDATE ' . MYSQL_TABLE_PREFIX . MYSQL_USERS_TABLE . ' SET `ip` = \'' . $ip . '\' WHERE `id` = ' . $_SESSION['ID'];
                    ActivityLog('User logged in with different ip old(' . user_data($_SESSION['ID'], 'ip') . ') new(' . $ip . ')', 2);
                    if (!$mysqliconnection->query($query)) {
                        die('Query failed');
                    }
                    ActivityLog('User logged in with different ip', 2);
                }
            }
            return $return;
        }
    } elseif (is_empty($username) === TRUE and is_empty($password) === FALSE) {
        $return = 1;   //  Missing Username
    } elseif (is_empty($username) === FALSE and is_empty($password) === TRUE) {
        $return = 2;   //  Missing Password
    } elseif (is_empty($username) === TRUE and is_empty($password) === TRUE) {
        $return = 3;   //  Missing Everything
    }
    return $return;
}

function permission_name_to_id($name) {
    global $mysqliconnection;
    $name = $mysqliconnection->real_escape_string($name);
    if ($result = $mysqliconnection->query('SELECT `id` FROM `' . MYSQL_TABLE_PREFIX . MYSQL_PERMISSIONS_TABLE . '` WHERE `name` = \'' . $name . '\'')) {
        return $result->fetch_assoc()['id'];
    }
}

function has_permission($permission, $user_id = NULL) {
    global $mysqliconnection;
    if (empty($_SESSION['ID']) && $user_id === NULL) {
        return FALSE;
    } elseif ($user_id === NULL) {
        $user = $_SESSION['ID'];
    } else {
        $user = $user_id;
    }
    $permission_id = permission_name_to_id($permission);
    if (CheckIfLinked(MYSQL_TABLE_PREFIX . MYSQL_PERMISSIONS_TABLE, MYSQL_TABLE_PREFIX . MYSQL_GROUP_TABLE, $permission_id, user_data($user, 'group'), 'group_permission') === TRUE) {
        if (CheckIfLinked(MYSQL_TABLE_PREFIX . MYSQL_PERMISSIONS_TABLE, MYSQL_TABLE_PREFIX . MYSQL_USERS_TABLE, $permission_id, $user, 'permission_deny') === FALSE) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        if (CheckIfLinked(MYSQL_TABLE_PREFIX . MYSQL_PERMISSIONS_TABLE, MYSQL_TABLE_PREFIX . MYSQL_USERS_TABLE, $permission_id, $user, 'permission_allow') === TRUE) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

function user_data($user_id, $data) {
    global $mysqliconnection;
    $user_id = (int) $user_id;
    $names = array('`id`', '`ip`', '`timezone`', '`username`', '`password`', '`email`', '`first`', '`last`', '`puh`', '`zip`', '`address`', '`priority`', '`active`', '`allow_login`', '`latest_visit`', '`group`', '`cv`', '`avatar`');
    $fields = implode(', ', $names);
    if (in_array('`' . $data . '`', $names)) {
        $query = 'SELECT ' . $fields . ' FROM ' . MYSQL_TABLE_PREFIX . MYSQL_USERS_TABLE . ' WHERE `id` = ' . $user_id;
        if ($result = $mysqliconnection->query($query)) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $return = $row[$data];
            return $return;
            $result->free();
        }
    }
}

function GetPersonalSetting($user_id, $setting, $reference) {
    global $mysqliconnection;
    $setting = $mysqliconnection->real_escape_string($setting);
    $reference = $mysqliconnection->real_escape_string($reference);
    if ($result = $mysqliconnection->query('SELECT `value` FROM `' . MYSQL_TABLE_PREFIX . MYSQL_PERSONAL_SETTINGS_TABLE . '` WHERE `user_id` = ' . $user_id . ' AND `setting` = \'' . $setting . '\' AND `reference` = \'' . $reference . '\' ')) {
        return $result->fetch_all()[0][0];
    }
}

function group_data($group_id, $data) {
    global $mysqliconnection;
    $names = array('`id`', '`name`', '`display_name_fi`');
    $fields = '' . implode(', ', $names) . '';
    if (in_array('`' . $data . '`', $names)) {
        $query = "SELECT $fields FROM `" . MYSQL_TABLE_PREFIX . MYSQL_GROUP_TABLE . "` WHERE `id` = $group_id";
        if ($result = $mysqliconnection->query($query)) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $return = $row[$data];
            return $return;
            $result->free();
        }
    }
}

function renameFile($oldname, $newname) {
    $ext = File_ext($oldname);
    return $newname . '.' . $ext;
}

function logged_in() {
    return (isset($_SESSION['ID'])) ? true : false;
}

function CheckIfLinked($table1, $table2, $fromID, $toID, $reference) {
    global $mysqliconnection;
    $table1 = $mysqliconnection->real_escape_string($table1);
    $table2 = $mysqliconnection->real_escape_string($table2);
    $reference = $mysqliconnection->real_escape_string($reference);
    if ($result = $mysqliconnection->query('SELECT `id` FROM `' . MYSQL_TABLE_PREFIX . MYSQL_LINKING_TABLE . '` WHERE `table1` = \'' . $table1 . '\' AND `table2` = \'' . $table2 . '\' AND `id1` = ' . $fromID . ' AND `id2` = ' . $toID . ' AND `reference` = \'' . $reference . '\'')) {
        if ($result->num_rows > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

function randomPassword($length = 8) {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789#_-";
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass);
}

function html_tidy($buffer) {
    $tidy = new tidy;
    $config = array(
        'indent' => true,
        'output-xhtml' => true,
        'wrap' => 200,
        'clean' => true
    );
    $tidy->parseString($buffer, $config, 'utf8');
    $tidy->cleanRepair();
    $input = $tidy;
    return $input;
}

function GetAllLinkedIds($table1, $table2, $idcolumn, $LinkedtoId, $reference) {
    global $mysqliconnection;
    $table1 = $mysqliconnection->real_escape_string($table1);
    $table2 = $mysqliconnection->real_escape_string($table2);
    $reference = $mysqliconnection->real_escape_string($reference);
    if ($idcolumn === 1) {
        $returnidcolumn = 'id2';
    } else {
        $returnidcolumn = 'id1';
    }
    $linkedarray = array();
    if ($result = $mysqliconnection->query('SELECT `' . $returnidcolumn . '` FROM `' . MYSQL_TABLE_PREFIX . MYSQL_LINKING_TABLE . '` WHERE `table1` = \'' . $table1 . '\' AND `table2` = \'' . $table2 . '\' AND `id' . $idcolumn . '` = ' . $LinkedtoId . ' AND `reference` = \'' . $reference . '\'')) {
        while ($row = $result->fetch_assoc()) {
            array_push($linkedarray, $row[$returnidcolumn]);
        }
        return $linkedarray;
    }
}

function output_unix($unix) {
    return ucwords(strftime("%d. %B %Y %k:%M", $unix));
}

function GetIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function ActivityLog($event, $status) {
    global $mysqliconnection;
    $status = (int) $status;
    $event = $mysqliconnection->real_escape_string($event);
    $user_agent = $mysqliconnection->real_escape_string($_SERVER['HTTP_USER_AGENT']);
    $user_name = (isset($_SESSION['ID'])) ? user_data($_SESSION['ID'], 'username') : NULL;

    $query = 'INSERT INTO `' . MYSQL_DB . '`.`' . MYSQL_TABLE_PREFIX . MYSQL_ACTIVITYLOG_TABLE . '` (`id`, `user`, `ip`, `event`, `time`, `state`, `user_agent`, `from`) VALUES (NULL, \'' . $user_name . '\', \'' . GetIP() . '\', \'' . $event . '\', UNIX_TIMESTAMP(), \'' . $status . '\', \'' . $user_agent . '\', \'File: "' . debug_backtrace()[0]['file'] . '", Line: ' . debug_backtrace()[0]['line'] . '\');';

    if (!$mysqliconnection->query($query)) {
        die('Query failed at activitylog');
    }
}

function ErrorLog($error, $status) {
    global $mysqliconnection;
    $status = (int) $status;
    $error = $mysqliconnection->real_escape_string($error);
    $user_agent = $mysqliconnection->real_escape_string($_SERVER['HTTP_USER_AGENT']);
    $user_name = (isset($_SESSION['ID'])) ? user_data($_SESSION['ID'], 'username') : NULL;

    $query = 'INSERT INTO `' . MYSQL_DB . '`.`' . MYSQL_TABLE_PREFIX . MYSQL_ERRORLOG_TABLE . '` (`id`, `user`, `ip`, `action`, `time`, `state`, `user_agent`, `from`) VALUES (NULL, \'' . $user_name . '\', \'' . GetIP() . '\', \'' . $error . '\', UNIX_TIMESTAMP(), \'' . $status . '\', \'' . $user_agent . '\', \'File: "' . debug_backtrace()[0]['file'] . '", Line: ' . debug_backtrace()[0]['line'] . '\');';
    if (!$mysqliconnection->query($query)) {
        die('Query failed at errorlog');
    }
}

function generate_rnd_color($from = 0, $to = 255) {
    $r = rand($from, $to);
    $g = rand($from, $to);
    $b = rand($from, $to);
    return array($r, $g, $b);
}

function is_empty($input) {
    if (empty($input) or $input === NULL or $input === "" or $input === FALSE or ! isset($input)) {
        return TRUE;
    } else {
        return FALSE;
    }
}