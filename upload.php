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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csvfile'])) {

    $fileName = basename($_FILES['csvfile']['name']);
    $fileTmpName = $_FILES['csvfile']['tmp_name'];
    $fileError = $_FILES['csvfile']['error'];
    $uploadDir = 'uploads/'; 

    if ($fileError !== UPLOAD_ERR_OK) {
        echo '<script>alert("Error: File upload error"); window.location.href = "home.php";</script>';
        exit();
    }

    if (pathinfo($fileName, PATHINFO_EXTENSION) !== 'csv') {
        echo '<script>alert("Error: Only CSV files are allowed"); window.location.href = "home.php";</script>';
        exit();
    }

    if (!is_readable($fileTmpName)) {
        echo '<script>alert("Error: File is not accessible"); window.location.href = "home.php";</script>';
        exit();
    }

    $ytHeader = [
        "Influencer Name", "Platform", "Subscriber count", 
        "Video Title", "Video views", "Video likes", "Video duration", 
        "video1 comments", "video1 comment likes", 
        "video2 comments", "video2 comment likes", "video3 comments", 
        "video3 comment likes", "video4 comments", "video4 comment likes", 
        "video5 comments", "video5 comment likes", "video1 total comments", 
        "video2 total comments", "video3 total comments", "video4 total comments", 
        "video5 total comments", "Hashtags used v1", "Hashtags used v2", 
        "Hashtags used v3", "Hashtags used v4", "Hashtags used v5"
    ];

    $fbHeader = [
        "Influencer Name", "Platform", "Subscriber count", 
        "Video Title", "Video views", "Video likes", "Video duration", 
        "video1 comments", "video1 comment likes", 
        "video2 comments", "video2 comment likes", "video3 comments", 
        "video3 comment likes", "video4 comments", "video4 comment likes", 
        "video5 comments", "video5 comment likes", "video1 total comments", 
        "video2 total comments", "video3 total comments", "video4 total comments", 
        "video5 total comments", "like", "love", "care", "haha", "wow", "sad", "angry", 
        "Hashtags used v1", "Hashtags used v2", "Hashtags used v3", "Hashtags used v4", 
        "Hashtags used v5"
    ];

    $handle = fopen($fileTmpName, 'r');
    if ($handle !== false) {
        $header = fgetcsv($handle);
        if ($header === false) {
            echo '<script>alert("Error: Unable to read the CSV header."); window.location.href = "home.php";</script>';
            exit();
        }

        $header = array_map('trim', $header);

        if ($header !== $ytHeader && $header !== $fbHeader) {
            echo '<script>alert("Error: Uploaded CSV does not match the required format. Please read and follow our guideline."); window.location.href = "home.php";</script>';
            fclose($handle);
            exit();
        }
        $fileData = file_get_contents($fileTmpName);
        fclose($handle);

        $uploadedBy = $_SESSION['username'];

        $stmt = $conn->prepare("INSERT INTO csv_data (file_name, file_data, uploaded_by) VALUES (?, ?, ?)");
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("sss", $fileName, $fileData, $uploadedBy); 
        
        if ($stmt->execute()) {
            $targetFilePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($fileTmpName, $targetFilePath)) {
                echo '<script>alert("File successfully uploaded and stored."); window.location.href = "home.php";</script>';
            } else {
                echo '<script>alert("File uploaded but could not be moved to the target directory."); window.location.href = "home.php";</script>';
            }
        } else {
            echo '<script>alert("Error: Unable to store the file in the database"); window.location.href = "home.php";</script>' . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo '<script>alert("Error: Unable to read the uploaded file."); window.location.href = "home.php";</script>';
        exit();
    }
} else {
    echo '<script>alert("Invalid Request"); window.location.href = "home.php";</script>';
}

$conn->close();
?>
