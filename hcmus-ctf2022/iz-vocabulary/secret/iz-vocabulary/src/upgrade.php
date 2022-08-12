<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
}

if ($under_maintenance === true) {
    echo "Under maintenance";
    exit();
}
else {
    //
}
?>