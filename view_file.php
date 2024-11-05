<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

if (isset($_GET['id'])) {
    $fileId = $_GET['id'];

    $stmt = $con->prepare("SELECT file_name, file_data FROM csv_data WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $stmt->bind_result($fileName, $fileData);
    $stmt->fetch();

    if ($fileName && $fileData) {
        $rows = explode("\n", trim($fileData));
        $table = '<table>';

        if (!empty($rows[0])) {
            $header = str_getcsv(array_shift($rows));
            $table .= '<tr>';
            foreach ($header as $col) {
                $table .= '<th>' . htmlspecialchars(trim($col)) . '</th>';
            }
            $table .= '</tr>';
        }

        foreach ($rows as $row) {

            $cols = str_getcsv($row);
            $table .= '<tr>';
            foreach ($cols as $col) {
                $table .= '<td>' . htmlspecialchars(trim($col)) . '</td>';
            }
            $table .= '</tr>';
        }
        $table .= '</table>';

        include('include/sidebar.html');
        include('include/header-printreports.html');

        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>View CSV - ' . htmlspecialchars($fileName) . '</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                }
              .c-area {
               padding: 20px; 
               position:absolute; 
               top: 100px; 
               left: 300px; 
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table, th, td {
                    border: 1px solid #ccc;
                }
                th, td {
                    padding: 10px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                }
                .view-button {
                    display: inline-block;
                    padding: 10px 20px;
                    margin-top: 20px;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    transition: background-color 0.3s;
                }
                .view-button:hover {
                    background-color: #0056b3;
                }
                @media (max-width: 600px) {
                    table {
                        font-size: 14px;
                    }
                }
            </style>
        </head>
        <body>
        <div class="c-area">
            <a href="uploads/' . htmlspecialchars($fileName) . '" download class="view-button">Download CSV</a>
            <h2>View CSV File: ' . htmlspecialchars($fileName) . '</h2>
            ' . $table . '
        </div>
        </body>
        </html>';
    } else {
        echo "File not found.";
    }

    $stmt->close();
} else {
    echo "No file specified.";
}

$con->close();
