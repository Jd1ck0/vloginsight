<?php
// Start the session and set up the database connection
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vloginsight";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get file IDs from the query string
$fileId1 = isset($_GET['file1']) ? intval($_GET['file1']) : 0;
$fileId2 = isset($_GET['file2']) ? intval($_GET['file2']) : 0;

// Fetch file names from the database based on file IDs
$fileNames = [];

if ($fileId1) {
    $stmt = $conn->prepare("SELECT file_name FROM csv_data WHERE id = ?");
    $stmt->bind_param("i", $fileId1);
    $stmt->execute();
    $stmt->bind_result($fileNames[0]);
    $stmt->fetch();
    $stmt->close();
}

if ($fileId2) {
    $stmt = $conn->prepare("SELECT file_name FROM csv_data WHERE id = ?");
    $stmt->bind_param("i", $fileId2);
    $stmt->execute();
    $stmt->bind_result($fileNames[1]);
    $stmt->fetch();
    $stmt->close();
}

// Check if both files exist
$filePaths = [];
foreach ($fileNames as $fileName) {
    if ($fileName) {
        $filePaths[] = 'uploads/' . htmlspecialchars($fileName);
    }
}

// Execute Node.js script if both files are available
if (count($filePaths) === 2) {
    $filePath1 = $filePaths[0];
    $filePath2 = $filePaths[1];

    $output = [];
    $retval = null;

    // Execute the Node.js script and pass the file paths as arguments
    exec("node C:/xampp/htdocs/caps/js/c-likertScale.js \"$filePath1\" \"$filePath2\"", $output, $retval);

    // Display the return value and output
    echo "<pre>";
    echo "Return value: $retval\n";
    echo "Output:\n" . implode("\n", $output) . "\n";
    echo "</pre>";

    // Check if the JSON file was generated
    $jsonFilePath = 'CommentsScale/c-commentsData.json';
    if (file_exists($jsonFilePath)) {
        $jsonData = file_get_contents($jsonFilePath);
        $commentsData = json_decode($jsonData, true);

        if (empty($commentsData)) {
            echo "The JSON file is empty or not in the expected format.";
        }
    } else {
        echo "Output JSON file not found.\n";
    }
} else {
    echo "Please upload both CSV files.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparison Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            display: flex;
            justify-content: space-between;
        }
        .file-column {
            width: 48%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .file-name {
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<h1>Comparison of Comments Data</h1>

<div class="container">
    <!-- First file column -->
    <div class="file-column">
        <div class="file-name">
            <h3><?php echo htmlspecialchars($fileNames[0]); ?></h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Video</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($commentsData[$fileNames[0]])) {
                    foreach ($commentsData[$fileNames[0]] as $video => $comments) {
                        echo "<tr><td>" . htmlspecialchars($video) . "</td><td>" . htmlspecialchars($comments) . "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No data available for this file.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Second file column -->
    <div class="file-column">
        <div class="file-name">
            <h3><?php echo htmlspecialchars($fileNames[1]); ?></h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Video</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($commentsData[$fileNames[1]])) {
                    foreach ($commentsData[$fileNames[1]] as $video => $comments) {
                        echo "<tr><td>" . htmlspecialchars($video) . "</td><td>" . htmlspecialchars($comments) . "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No data available for this file.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
