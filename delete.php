<?php
session_start();

include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


if (isset($_GET['id'])) {
    $fileId = intval($_GET['id']);

    $sql = "DELETE FROM csv_data WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $fileId);

    if ($stmt->execute()) {
        header("Location: uploads.php?message=success");
        exit();
    } else {

        echo "Error deleting file: " . $con->error;
    }

    $stmt->close();
} else {
    echo "No file ID provided.";
}

$con->close();
