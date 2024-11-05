<?php

$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "vloginsight";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = $_POST['username'];
$pass = $_POST['password'];

$sql = "SELECT * FROM user_info WHERE username = ? AND password = ? AND status = 'verified'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user, $pass);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    session_start();
    $_SESSION['username'] = $user;
    echo '<script>alert("Log-in Successfully"); window.location.href = "home.php";</script>';
    exit();
} else {
    echo '<script>alert("Log-in Failed or Account Not Verified"); window.location.href = "login.php";</script>';
}

$stmt->close();
$conn->close();
?>
