<?php
$username = $_GET['username'];
$file_path = './cookies/'.$username.'.txt';
if (file_exists($file_path)) {
        if (unlink($file_path)) {
            echo true;
        } else {
            echo false;
        }
} else {
    echo false;
}