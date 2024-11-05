<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

function parseViewCount($viewCount)
{
    $viewCount = str_replace(',', '', $viewCount);
    if (strpos($viewCount, 'k') !== false) {
        return (int)(floatval($viewCount) * 1000);
    } elseif (strpos($viewCount, 'm') !== false) {
        return (int)(floatval($viewCount) * 1000000);
    } elseif (strpos($viewCount, 'b') !== false) {
        return (int)(floatval($viewCount) * 1000000000);
    }
    return (int)$viewCount;
}

function parseCSV($filePath)
{
    $data = [
        'influencers' => [],
        'videos' => [],
        'hashtags' => [],
        'total_comments' => [] // To store total comments
    ];

    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        $header = fgetcsv($handle);
        if ($header) {
            $nameIndex = array_search("Influencer Name", $header);
            $platformIndex = array_search("Platform", $header);
            $subCountIndex = array_search("Subscriber count", $header);
            $videoTitleIndex = array_search("Video Title", $header);
            $videoViewsIndex = array_search("Video views", $header);
            $videoLikesIndex = array_search("Video likes", $header);
            $videoDurationIndex = array_search("Video duration", $header);

            // Capture total comments
            for ($i = 1; $i <= 5; $i++) {
                $totalCommentsIndex = array_search("video{$i} total comments", $header);
                if ($totalCommentsIndex !== false) {
                    $data['total_comments'][$i] = 0; // Initialize total comments
                }
            }

            $hashtagIndices = [];
            for ($i = 1; $i <= 5; $i++) {
                $hashtagIndices[$i] = array_search("Hashtags used v$i", $header);
            }

            while (($row = fgetcsv($handle)) !== FALSE) {
                if (!empty($row[$nameIndex])) {
                    $data['influencers'][] = [
                        'name' => htmlspecialchars($row[$nameIndex]),
                        'platform' => htmlspecialchars($row[$platformIndex]),
                        'subscribers' => htmlspecialchars($row[$subCountIndex]),
                    ];
                }

                if (!empty($row[$videoTitleIndex])) {
                    $data['videos'][] = [
                        'title' => htmlspecialchars($row[$videoTitleIndex]),
                        'views' => parseViewCount($row[$videoViewsIndex]),
                        'likes' => htmlspecialchars($row[$videoLikesIndex]),
                        'duration' => htmlspecialchars($row[$videoDurationIndex]),
                    ];
                }

                // Capture total comments
                foreach ($data['total_comments'] as $i => &$commentCount) {
                    $totalCommentsIndex = array_search("video{$i} total comments", $header);
                    if ($totalCommentsIndex !== false && !empty($row[$totalCommentsIndex])) {
                        $commentCount = htmlspecialchars($row[$totalCommentsIndex]); // Store total comments
                    }
                }

                foreach ($hashtagIndices as $index) {
                    if ($index !== false && !empty($row[$index])) {
                        $data['hashtags'][$index][] = htmlspecialchars($row[$index]);
                    }
                }
            }
        }
        fclose($handle);
    }
    return $data;
}

$fileId1 = isset($_GET['file1']) ? intval($_GET['file1']) : 0;
$fileId2 = isset($_GET['file2']) ? intval($_GET['file2']) : 0;

$fileNames = [];
if ($fileId1) {
    $stmt = $con->prepare("SELECT file_name FROM csv_data WHERE id = ?");
    $stmt->bind_param("i", $fileId1);
    $stmt->execute();
    $stmt->bind_result($fileNames[0]);
    $stmt->fetch();
    $stmt->close();
}

if ($fileId2) {
    $stmt = $con->prepare("SELECT file_name FROM csv_data WHERE id = ?");
    $stmt->bind_param("i", $fileId2);
    $stmt->execute();
    $stmt->bind_result($fileNames[1]);
    $stmt->fetch();
    $stmt->close();
}

$data1 = [];
$data2 = [];
$filePaths = [];
$videoData1 = [];
$videoData2 = [];
$hashtagsData1 = [];
$hashtagsData2 = [];
$totalCommentsData1 = [];
$totalCommentsData2 = []; // For total comments

foreach ($fileNames as $index => $fileName) {
    if ($fileName) {
        $filePath = 'uploads/' . htmlspecialchars($fileName);
        if (file_exists($filePath)) {
            $data = parseCSV($filePath);
            if ($index == 0) {
                $data1 = $data['influencers'];
                $videoData1 = $data['videos'];
                $hashtagsData1 = $data['hashtags'];
                $totalCommentsData1 = $data['total_comments']; // Capture total comments for file 1
            } else {
                $data2 = $data['influencers'];
                $videoData2 = $data['videos'];
                $hashtagsData2 = $data['hashtags'];
                $totalCommentsData2 = $data['total_comments']; // Capture total comments for file 2
            }
            $filePaths[] = $filePath;
        }
    }
}

$hashtagsOutput1 = '';
$hashtagsOutput2 = '';

function formatHashtags($hashtagsData)
{
    $output = '';
    foreach ($hashtagsData as $index => $hashtags) {
        $output .= "<strong>Hashtags used <br><br>";
        $output .= implode(', ', $hashtags) . "<br><br>";
    }
    return $output;
}

$hashtagsOutput1 = formatHashtags($hashtagsData1);
$hashtagsOutput2 = formatHashtags($hashtagsData2);

$titles1 = array_column($videoData1, 'title');
$views1 = array_column($videoData1, 'views');
$titles2 = array_column($videoData2, 'title');
$views2 = array_column($videoData2, 'views');

function calculateAverageViews($views)
{
    if (count($views) === 0) {
        return 0;
    }
    return array_sum($views) / count($views);
}

$output = [];
$retval = null;

// Ensure both file paths are provided to the Node.js script
if (count($filePaths) == 2) {
    $filePath1 = $filePaths[0];
    $filePath2 = $filePaths[1];

    // Pass both file paths to the Node.js script
    exec("node js/likertScale.js \"$filePath1\" \"$filePath2\"", $output, $retval);

    if ($retval === 0) {
        $jsonFilePath = 'CommentsScale/commentsData.json';
        if (file_exists($jsonFilePath)) {
            $jsonData = file_get_contents($jsonFilePath);
            $commentsData = json_decode($jsonData, true);
        } else {
            echo "Output JSON file not found.";
        }
    } else {
        echo "Script execution failed with return value: $retval<br>";
        echo "Error output:<br>" . nl2br(htmlspecialchars(implode("\n", $output)));
    }
} else {
    echo "Please upload both CSV files.";
}

$averageViews1 = calculateAverageViews($views1);
$averageViews2 = calculateAverageViews($views2);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VlogInsight - Comparison</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .data-container,
        .c-views-graph,
        .c-video-info-container,
        .c-average-views-container,
        .c-hashtags-used,
        .c-total-comments-container,
        .c-average-comments-container,
        .c-comments-container {
            width: 50%;
            border: solid 3px black;
            background-color: #FEF9F2;
            padding: 20px;
            margin: 10px 0;
            font-size: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-left: 500px;
            margin-top: 75px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .c-video-info-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .video-info-row {
            display: flex;
            justify-content: space-between;
        }

        .video-info-column {
            flex: 1;
            margin: 0 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #FEF9F2;
            border-radius: 8px;
        }

        .video-info-item {
            margin-bottom: 15px;
        }

        .average-views-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            font-size: 22px;
        }

        .c-total-comments-container {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <?php
    include('include/sidebar_c.html');
    include('include/header-graph.html');
    ?>

    <div id="c-influencer-data-container-1" class="data-container">
        <h2>Influencer Data from <?php echo isset($fileNames[0]) ? htmlspecialchars($fileNames[0]) : 'File 2'; ?></h2>
        <?php if (count($data1) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Influencer Name</th>
                        <th>Platform</th>
                        <th>Subscriber Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data1 as $row): ?>
                        <tr>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['platform']; ?></td>
                            <td><?php echo $row['subscribers']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No data found in File 1.</p>
        <?php endif; ?>
    </div>

    <div id="c-influencer-data-container-2" class="data-container">
        <h2>Influencer Data from <?php echo isset($fileNames[1]) ? htmlspecialchars($fileNames[1]) : 'File 2'; ?></h2>
        <?php if (count($data2) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Influencer Name</th>
                        <th>Platform</th>
                        <th>Subscriber Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data2 as $row): ?>
                        <tr>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['platform']; ?></td>
                            <td><?php echo $row['subscribers']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No data found in File 2.</p>
        <?php endif; ?>
    </div>

    <div class="c-video-info-container">
        <h2>Video Information</h2>
        <div class="video-info-row">
            <div class="video-info-column">
                <h3>File: <?php echo isset($fileNames[0]) ? htmlspecialchars($fileNames[0]) : 'File 1'; ?></h3>
                <?php foreach ($videoData1 as $video): ?>
                    <div class="video-info-item">
                        <p><b>Title:</b> <?php echo $video['title']; ?></p>
                        <p><b>Likes:</b> <?php echo $video['likes']; ?></p>
                        <p><b>Duration:</b> <?php echo $video['duration']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="video-info-column">
                <h3>File: <?php echo isset($fileNames[1]) ? htmlspecialchars($fileNames[1]) : 'File 2'; ?></h3>
                <?php foreach ($videoData2 as $video): ?>
                    <div class="video-info-item">
                        <p><b>Title:</b> <?php echo $video['title']; ?></p>
                        <p><b>Likes:</b> <?php echo $video['likes']; ?></p>
                        <p><b>Duration: </b> <?php echo $video['duration']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="c-views-graph">
        <h1 style="font-size:30">Views Per Video</h1>
        <canvas id="c-videoChart" width="500" height="400"></canvas>
    </div>

    <div class="c-average-views-container">
        <h1 style="font-size: 30px;">AVERAGE VIEWS</h1>
        <div class="average-views-row">
            <div class="average-views-column">
                <h3>File: <?php echo isset($fileNames[0]) ? htmlspecialchars($fileNames[0]) : 'File 1'; ?></h3>
                <p><strong>Average Views:</strong> <?php echo number_format($averageViews1); ?></p>
            </div>
            <div class="average-views-column">
                <h3>File: <?php echo isset($fileNames[1]) ? htmlspecialchars($fileNames[1]) : 'File 2'; ?></h3>
                <p><strong>Average Views:</strong> <?php echo number_format($averageViews2); ?></p>
            </div>
        </div>
    </div>

    <div class="c-total-comments-container">
        <h1 style="font-size: 30px;">TOTAL COMMENTS</h1>
        <div class="average-views-row">
            <div class="average-views-column">
                <h3>File: <?php echo htmlspecialchars($fileNames[0]); ?></h3>
                <?php
                $totalComments1 = 0;
                foreach ($totalCommentsData1 as $index => $comments):
                    $totalComments1 += intval($comments);
                ?>
                    <p class="video-info-item"><?php echo 'video' . ($index + 0) . ' = ' . htmlspecialchars($comments); ?></p>
                <?php endforeach; ?>
            </div>
            <div class="average-views-column">
                <h3>File: <?php echo htmlspecialchars($fileNames[1]); ?></h3>
                <?php
                $totalComments2 = 0;
                foreach ($totalCommentsData2 as $index => $comments):
                    $totalComments2 += intval($comments);
                ?>
                    <p class="video-info-item"><?php echo 'video' . ($index + 0) . ' = ' . htmlspecialchars($comments); ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="c-average-comments-container">
        <h1 style="font-size:30px;">Average Comments</h1>
        <?php
        $videoCount1 = count($totalCommentsData1);
        $videoCount2 = count($totalCommentsData2);

        $averageComments1 = $videoCount1 > 0 ? round($totalComments1 / $videoCount1, 2) : 0;
        echo "<p><b>File: " . htmlspecialchars($fileNames[0]) . " - Average Comments: </b><br><br>" . $averageComments1 . "</p>";

        $averageComments2 = $videoCount2 > 0 ? round($totalComments2 / $videoCount2, 2) : 0;
        echo "<p><b>File: " . htmlspecialchars($fileNames[1]) . " - Average Comments: </b><br><br>" . $averageComments2 . "</p>";
        ?>
    </div>

    <div class="c-comments-container">
    </div>


    <div class="c-hashtags-used">
        <h1 style="font-size: 30px;">HASHTAGS USED</h1>
        <h3>File: <?php echo isset($fileNames[0]) ? htmlspecialchars($fileNames[0]) : 'File 1'; ?></h3>
        <?php echo $hashtagsOutput1; ?>
        <h3>File: <?php echo isset($fileNames[1]) ? htmlspecialchars($fileNames[1]) : 'File 2'; ?></h3>
        <?php echo $hashtagsOutput2; ?>
    </div>


    <script>
        //**Views*/
        const titles1 = <?php echo json_encode($titles1); ?>;
        const views1 = <?php echo json_encode($views1); ?>;
        const titles2 = <?php echo json_encode($titles2); ?>;
        const views2 = <?php echo json_encode($views2); ?>;
        const labels = [];
        for (let i = 0; i < titles1.length; i++) {
            labels.push(`Video ${i + 1}`);
        }
        for (let i = 5; i < titles2.length; i++) {
            labels.push(`Video ${titles1.length + i + 1}`);
        }

        const ctx = document.getElementById('c-videoChart').getContext('2d');

        const chartData = {
            labels: labels,
            datasets: [{
                    label: '<?php echo isset($fileNames[0]) ? htmlspecialchars($fileNames[0]) : 'File 1'; ?>',
                    data: views1,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: true
                },
                {
                    label: '<?php echo isset($fileNames[1]) ? htmlspecialchars($fileNames[1]) : 'File 2'; ?>',
                    data: views2,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1,
                    fill: true
                }
            ]
        };

        const config = {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: false,
                        text: 'Video Views Comparison'
                    },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {

                                const index = tooltipItems[0].dataIndex;
                                if (index < titles1.length) {
                                    return titles1[index];
                                } else {
                                    return titles2[index - titles1.length];
                                }
                            }
                        }
                    }
                }
            },
        };

        const videoChart = new Chart(ctx, config);
    </script>

</body>

</html>