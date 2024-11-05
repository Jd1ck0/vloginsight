<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php"); 
    exit();
}
$current_user = $_SESSION['username'];

$sql = "SELECT cd.id, cd.file_name, cd.uploaded_at 
        FROM csv_data cd
        JOIN user_info ui ON cd.uploaded_by = ui.username
        WHERE cd.uploaded_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$result = $stmt->get_result();

if (isset($_GET['message']) && $_GET['message'] === 'success') {
    echo "<script>alert('File successfully deleted.');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VlogInsight - Uploaded Files</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .file-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }
        .file-card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            width: 100%;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px auto;
        }
        .file-icon {
            font-size: 40px;
            color: green;
            margin-bottom: 10px;
        }
        .file-id, .file-name, .file-uploaded-at {
            margin: 5px 0;
        }
        .file-id {
            color: #6c757d;
        }
        .file-name {
            font-weight: bold;
        }
        .file-uploaded-at {
            color: #6c757d;
            font-size: 0.9em;
        }
        .view-button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 0.9em;
            margin-top: 10px;
            cursor: pointer;
        }
        .view-button:hover {
            background-color: green;
        }
        .c-area {
            position: absolute;
            top: 20%;
            left: 35%;
            width: 800px;
            height: 500px;
            padding-top: 0px; 
            box-sizing: border-box;
        }
        .cbutton{
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            text-decoration: none;
            font-size:20px;
            margin-left: 20px;
            cursor: pointer;
            width: 280px;
            height: 60px;
        }
        .cbutton:hover{
            background-color: green;
        }
        .select-button{
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 0.9em;
            margin-top: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php 
    include ('include/sidebar.html');
    include('include/header-fileuploads.html');
?>

<div class="c-area">
        <button class="cbutton">Select 2 files to compare</button>
        <div class="file-container">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $fileId = htmlspecialchars($row['id']);
                    $fileName = htmlspecialchars($row['file_name']);
                    $fileUploadedAt = htmlspecialchars($row['uploaded_at']);
                    echo '<div class="file-card">';
                    echo '<div class="file-icon"><i class="fas fa-file-csv"></i></div>';
                    echo '<div class="file-id">File ID: ' . $fileId . '</div>';
                    echo '<div class="file-name">' . $fileName . '</div>';
                    echo '<div class="file-uploaded-at">Uploaded at: ' . $fileUploadedAt . '</div>';
                    echo '<a href="view_file.php?id=' . $fileId . '" class="view-button">View</a>';
                    echo '<a href="visual.php?id=' . $fileId . '" class="view-button" style="margin-top: 15px;">Graph</a>';
                    echo '<a href="delete.php?id=' . $fileId . '" class="view-button" style="margin-top: 15px;" onclick="return confirm(\'Are you sure you want to delete this file?\');">Delete</a>';
                    echo '<button class="select-button" data-id="' . $fileId . '" style="display: none; margin-top: 15px;">Select</button>';
                    echo '</div>'; 
                }
            } else {
                echo "<p>No files found.</p>";
            }
            ?>
        </div>
    </div>

<script>
    let selectedFiles = [];
    document.querySelector('.cbutton').addEventListener('click', function() {
        document.querySelectorAll('.select-button').forEach(selectButton => {
            selectButton.style.display = selectButton.style.display === "none" ? "inline-block" : "none";
        });
    });
    document.querySelectorAll('.select-button').forEach(button => {
        button.addEventListener('click', function() {
            const fileId = this.getAttribute('data-id');
            if (selectedFiles.includes(fileId)) {
                selectedFiles = selectedFiles.filter(id => id !== fileId);
                this.style.backgroundColor = ''; 
            } else {
                if (selectedFiles.length < 2) {
                    selectedFiles.push(fileId);
                    this.style.backgroundColor = 'lightgreen'; 
                } else {
                    alert('You can only select 2 files at a time.');
                }
            }
            if (selectedFiles.length === 2) {
                window.location.href = 'comparison.php?file1=' + selectedFiles[0] + '&file2=' + selectedFiles[1];
            }
        });
    });
</script>


</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
