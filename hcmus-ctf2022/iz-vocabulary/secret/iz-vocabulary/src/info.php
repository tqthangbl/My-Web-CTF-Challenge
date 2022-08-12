<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
}

echo file_get_contents("/xml/".$_SESSION['xml_path']);
?>