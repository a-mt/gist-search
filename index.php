<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require __DIR__ . '/inc.config.php';

//+--------------------------------------------------------
//| AUTH
//+--------------------------------------------------------

if(isset($_GET['logout'])) {
    session_destroy();
    header('Location: /');
    exit;
}

if(isset($_GET['sync'])) {
    $i = 1;
    while(true) {
        if(!isset($_SESSION["gists_" . $i])) {
            break;
        }
        unset($_SESSION["gists_" . $i]);
        $i++;
    }
    header('Location: /');
    exit;
}

// Ask API for login
if(isset($_POST["login"])) {
    unset($_SESSION["error"]);
    unset($_SESSION["success"]);

    $_SESSION["tmpcode"] = md5(PASSPHRASE . time());

    // https://developer.github.com/apps/building-oauth-apps/scopes-for-oauth-apps/
    header('Location:https://github.com/login/oauth/authorize' .
            '?client_id=' . CLIENT_ID .
            '&state=' . $_SESSION["tmpcode"] .
            '&scope=gist');
    exit;
}

// Check token is valid
if(isset($_SESSION["token"]) && !isset($_SESSION["username"])) {
    $c = curl_init('https://api.github.com/applications/' . CLIENT_ID . '/tokens/' . $_SESSION["token"]);

    curl_setopt($c, CURLOPT_HTTPHEADER, array('User-Agent: File search'));
    curl_setopt($c, CURLOPT_USERPWD, CLIENT_ID . ":" . CLIENT_SECRET);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    if($response = curl_exec($c)) {
        $json = json_decode($response, true);

        if(isset($json["user"])) {
            $_SESSION["username"] = $json["user"]["login"];
        } else {
            unset($_SESSION["token"]);
        }
    }
}

//+--------------------------------------------------------
//| GET GISTS
//+--------------------------------------------------------

function curl_header(&$h) {
    return function($c, $header_line) use(&$h) {
        if(strpos($header_line, ':')) {
            list($name, $content) = explode(": ", $header_line, 2);
            $h[$name] = trim($content);
        }
        return strlen($header_line);
    };
}

function get_gists($i = 1) {

    // Retrieve cached data
    if(isset($_SESSION["gists_$i"])) {
        print $_SESSION["gists_$i"];
        flush();
        ob_flush();

        $has_next = isset($_SESSION["gists_" . ($i + 1)]);

    // Query Github
    } else {
        $h = [];
        $c = curl_init('https://api.github.com/users/a-mt/gists?page=' . $i);

        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            'User-Agent: Search gist',
            'Authorization: token ' . $_SESSION["token"]
        ));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($c, CURLOPT_HEADERFUNCTION, curl_header($h));

        // Query url
        if(!$response = curl_exec($c)) {
            return;
        }
        $_SESSION["gists_$i"] = $response;
        print $response;
        flush();
        ob_flush();

        // Check status code
        $status_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        if($status_code != 200) {
            return;
        }

        // Is last page ?
        $has_next = isset($h['Link']) && preg_match('/rel="next"/', $h['Link']);
    }
    if($has_next) {
        return get_gists($i + 1);
    }
}

if(isset($_GET['get_gists'])) {
    return get_gists();
}

//+--------------------------------------------------------
//| RENDER HTML
//+--------------------------------------------------------

require __DIR__ . '/inc.tpl.php';
unset($_SESSION["error"]);
unset($_SESSION["success"]);