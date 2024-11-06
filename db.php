<?php
// $servername = "127.0.0.1";
// $username = "vlogInsight";
// $password = "vlogInsight";
// $dbname = "vlogInsight";


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vloginsight";

$con = new mysqli($servername, $username, $password, $dbname);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
