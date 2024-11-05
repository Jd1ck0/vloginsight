<?php
$fileName = isset($_GET['file']) ? $_GET['file'] : null;

if ($fileName) {
    $output = [];
    $retval = null;t
    $filePath = 'C:/xampp/htdocs/caps/uploads/' . $fileName;
    exec("node C:/xampp/htdocs/caps/js/likertScale.js \"$filePath\"", $output, $retval);

    if ($retval === 0) {
        $jsonFilePath = 'CommentsScale/commentsData.json';
        if (file_exists($jsonFilePath)) {
            $jsonData = file_get_contents($jsonFilePath);
            $results = json_decode($jsonData, true);
            
            foreach ($results as $video => $comments) {
                echo "<h3>Comments for $video</h3>";
                foreach ($comments as $commentData) {
                    echo "Comment: " . htmlspecialchars($commentData['comment']) . "<br>";
                    echo "Likert score: " . ($commentData['likertScore'] !== null ? $commentData['likertScore'] : 'N/A') . "<br><br>";
                }
            }
        } else {
            echo "Output JSON file not found.";
        }
    } else {
        echo "Script execution failed with return value: $retval<br>";
        echo "Error output:<br>" . nl2br(htmlspecialchars(implode("\n", $output)));
    }
} else {
    echo "No file specified.";
}
?>
