<?php
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "vloginsight";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


if (isset($_GET['id'])) {
    $fileId = intval($_GET['id']); 

    $sql = "DELETE FROM csv_data WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $fileId);

    if ($stmt->execute()) {
        header("Location: uploads.php?message=success");
        exit(); 
    } else {
      
        echo "Error deleting file: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "No file ID provided.";
}

$conn->close();
?>
