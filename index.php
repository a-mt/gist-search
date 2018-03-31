<?php
session_start();
require __DIR__ . '/inc.config.php';

define("DEBUG", false);

//+--------------------------------------------------------
//| AUTH
//+--------------------------------------------------------

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
//| RENDER HTML
//+--------------------------------------------------------

require __DIR__ . '/inc.tpl.php';
unset($_SESSION["error"]);
unset($_SESSION["success"]);