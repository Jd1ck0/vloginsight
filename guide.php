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
        .c-area {
            padding: 20px;
            position: absolute;
            top: 100px;
            left: 300px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 2px solid black;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .download-button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #091057;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .download-button:hover {
            background-color: #629584;
        }
    </style>
</head>
<body>
<?php 
    include ('include/sidebar.html');
    include('include/header-guide.html');
?>

<div class="c-area">
    <h1>Guidelines</h1>

    <?php
    function displayCSV($csvFile, $label) {
        if (file_exists($csvFile)) {
            if (($handle = fopen($csvFile, 'r')) !== FALSE) {
                echo "<h2>$label</h2>";
                echo '<table>';
                $header = fgetcsv($handle); 
                if ($header) {
                    echo '<tr>';
                    foreach ($header as $heading) {
                        echo '<th>' . htmlspecialchars($heading) . '</th>';
                    }
                    echo '</tr>';
                }

                while (($data = fgetcsv($handle)) !== FALSE) {
                    echo '<tr>';
                    foreach ($data as $cell) {
                        echo '<td>' . htmlspecialchars($cell) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
                fclose($handle);
            } else {
                echo '<p>Error opening the CSV file.</p>';
            }
        } else {
            echo '<p>CSV file does not exist.</p>';
        }
    }
    displayCSV('guideline/Guideline_YT.csv', 'YouTube Guideline');
    displayCSV('guideline/Guideline_FB.csv', 'Facebook Guideline');
    ?>
    <br><br><br><br>
    <a href="guideline/Guideline_YT.csv" class="download-button" download>Download YouTube Guideline CSV</a>
    <a href="guideline/Guideline_FB.csv"  style = "margin-left: 200px;" class="download-button" download>Download Facebook Guideline CSV</a>
</div>

</body>
</html>
