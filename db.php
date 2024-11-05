<?php
$servername = "localhost";
$username = "u297599468_vloginsight";
$password = "Vloginsight2024";
$dbname = "u297599468_vloginsight_db";

// local

// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "miles";

$con = new mysqli($servername, $username, $password, $dbname);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
