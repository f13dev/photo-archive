<?php

function create_token($string)
{
    if (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
        return md5($string.$_SESSION['usertoken']);
    }

    // Only logged in users can be validated
    return 'false';
}

function validate_token($string, $token = null)
{
    if ($token == null) {
        $request_method = ($_SERVER['REQUEST_METHOD'] === 'POST') ? INPUT_POST : INPUT_GET;
        $token = filter_input($request_method, 'token');
    }
    if (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
        $valid = create_token($string);
        return ($valid == $token) ? true : false;
    }

    // Only logged in users can be validated
    return false;
}

function esc_out($string)
{
    return htmlspecialchars($string, ENT_QUOTES);
}