<?php

header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
function extractCookies($text) {
    $cookies = [];
    $lines = explode("\n", $text);

    // Iterate over lines
    foreach ($lines as $line) {
        $tokens = preg_split('/\t/', trim($line));

        // We only care for valid cookie definition lines
        if (count($tokens) == 7) {
            // Trim the tokens
            $tokens = array_map('trim', $tokens);

            $cookie = [];

            // Extract the data
            $cookie['domain'] = str_replace('#HttpOnly_', '', $tokens[0]);
            $cookie['path'] = $tokens[2];
            $cookie['secure'] = "True";


            $cookie['name'] = $tokens[5];
            $cookie['value'] = $tokens[6];

            // Record the cookie
            $cookies[] = $cookie;
        }
    }

    return $cookies;
}
$username = $_GET['username'];
$text = file_get_contents("./cookies/".$username.".txt");
$cookies = extractCookies($text);
echo json_encode($cookies);

?>