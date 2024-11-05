<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php"); 
    exit();
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VlogInsight</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    .header-area {
    background-color: #FFDFD6;
    padding: 10px 20px;
    border-bottom: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    position: absolute;
    left: 0px;
    top: 0px;
    width: 95%;
    }
    .header-area h2 {
    margin: 0;
    font-size: 24px;
    }
    
    </style>
   
</head>
<body>
<?php 
    include ('include/sidebar.html');
    include('include/header.html');
?>

    <div class="content-area">
        <div class="header-area">
            <h2 style="margin-left: 30px;">Upload File</h2>
        </div>
        <div class="upload-container" style = "margin-top: 100px;">
            <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data">
                <input type="file" name="csvfile" id="csvfile" accept=".csv">
                <h3 style="color: white;">File With .CSV Format Only</h3>
                <button type="submit" class="upload-button">Upload</button>
            </form>
        </div>
    </div>
</body>
</html>
