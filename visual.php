<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

$fileId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT file_name FROM csv_data WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $fileId);
$stmt->execute();
$stmt->bind_result($fileName);
$stmt->fetch();
$stmt->close();

$influencerData = [];
$videoData = [];
$commentsData = [];
$hashtagsData = [];
$videoCommentsData = [];


if ($fileName) {
    $filePath = 'uploads/' . htmlspecialchars($fileName);
    if (file_exists($filePath)) {
        $handle = fopen($filePath, 'r');
        if ($handle !== false) {
            $header = fgetcsv($handle);
            if ($header) {
                $nameIndex = array_search("Influencer Name", $header);
                $platformIndex = array_search("Platform", $header);
                $subCountIndex = array_search("Subscriber count", $header);
                $videoLikesIndex = array_search("Video likes", $header);
                $videoDurationIndex = array_search("Video duration", $header);

                $hashtagIndices = [];
                for ($i = 1; $i <= 5; $i++) {
                    $hashtagIndices[$i] = array_search("Hashtags used v$i", $header);
                }

                $commentIndices = [];
                for ($i = 1; $i <= 5; $i++) {
                    $commentIndices[$i] = array_search("video{$i} total comments", $header);
                }

                while (($data = fgetcsv($handle)) !== false) {
                    if (!empty($data[$nameIndex])) {
                        $influencerData[] = [
                            'name' => $nameIndex !== false ? htmlspecialchars($data[$nameIndex]) : '',
                            'platform' => $platformIndex !== false ? htmlspecialchars($data[$platformIndex]) : '',
                            'subscriber_count' => $subCountIndex !== false ? htmlspecialchars($data[$subCountIndex]) : '',
                        ];
                    }

                    $videoTitleIndex = array_search("Video Title", $header);
                    $videoViewsIndex = array_search("Video views", $header);
                    $videoTitle = $videoTitleIndex !== false ? htmlspecialchars($data[$videoTitleIndex]) : '';
                    $videoViews = $videoViewsIndex !== false ? htmlspecialchars($data[$videoViewsIndex]) : '0';
                    $videoLikes = $videoLikesIndex !== false ? parseViews(htmlspecialchars($data[$videoLikesIndex])) : 0;
                    $videoDuration = $videoDurationIndex !== false ? htmlspecialchars($data[$videoDurationIndex]) : 'N/A';

                    if ($videoTitle) {
                        $videoData[] = [
                            'title' => $videoTitle,
                            'views' => $videoViews,
                            'likes' => (int)$videoLikes,
                            'duration' => $videoDuration,
                        ];
                    }

                    foreach ($hashtagIndices as $i => $index) {
                        if ($index !== false && !empty($data[$index])) {
                            $hashtagsData[$i][] = htmlspecialchars($data[$index]);
                        }
                    }


                    foreach ($commentIndices as $i => $index) {
                        if ($index !== false && !empty($data[$index])) {
                            $videoCommentsData[$i][] = htmlspecialchars($data[$index]);
                        }
                    }
                }
            }
            fclose($handle);
        }
    }

    $output = [];
    $retval = null;
    exec("node C:/xampp/htdocs/caps/js/likertScale.js \"$filePath\"", $output, $retval);

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
}

$averageViews = 0;
if (count($videoData) > 0) {
    $totalViews = array_sum(array_map('parseViews', array_column($videoData, 'views')));
    $averageViews = $totalViews / count($videoData);
}

$likertScoreCounts = array_fill(1, 5, 0);
if (!empty($commentsData)) {
    foreach ($commentsData as $videoComments) {
        foreach ($videoComments as $commentData) {
            $likertScore = $commentData['likertScore'] ?? 0;
            if ($likertScore >= 1 && $likertScore <= 5) {
                $likertScoreCounts[$likertScore]++;
            }
        }
    }
}

$totalScores = 0;
$totalCount = 0;

foreach ($likertScoreCounts as $score => $count) {
    $totalScores += $score * $count;
    $totalCount += $count;
}

$averageLikertScore = $totalCount > 0 ? round($totalScores / $totalCount, 2) : 0;

function getLikertDescription($score)
{
    if ($score >= 1 && $score <= 1.4) {
        return "Very Negative";
    } elseif ($score >= 1.5 && $score <= 2.4) {
        return "Negative";
    } elseif ($score >= 2.5 && $score <= 3.4) {
        return "Neutral";
    } elseif ($score >= 3.5 && $score <= 4.4) {
        return "Positive";
    } elseif ($score >= 4.5 && $score <= 5) {
        return "Very Positive";
    } else {
        return "N/A";
    }
}

$averageDescription = getLikertDescription($averageLikertScore);

function parseViews($view)
{
    if (is_string($view)) {
        $lowerView = strtolower(trim($view));
        if (strpos($lowerView, 'k') !== false) {
            return (int)(floatval($lowerView) * 1000);
        } elseif (strpos($lowerView, 'm') !== false) {
            return (int)(floatval($lowerView) * 1000000);
        } elseif (strpos($lowerView, 'b') !== false) {
            return (int)(floatval($lowerView) * 1000000000);
        }
    }
    return (int)str_replace(',', '', $view);
}


$videoDataJson = json_encode($videoData);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VlogInsight</title>
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

        .influencer-container {
            width: 50%;
            border: solid 3px black;
            background-color: #FEF9F2;
            padding: 20px;
            margin: 10px 0;
            font-size: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;

        }

        .video-chart-container,
        .average-container,
        .comments-container,
        .comments-graph-container,
        .hashtags-container,
        .comments-average-container,
        .videos-container,
        .comments-details-container,
        .total-comments-container,
        .average-comments-container {
            width: 50%;
            border: solid 3px black;
            background-color: #FEF9F2;
            padding: 20px;
            margin: 10px 0;
            font-size: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .video-chart-container {
            text-align: center;
        }

        canvas {
            margin-top: 20px;
            background-color: white;
        }

        .average-container {
            text-align: center;
            height: 95px;
        }

        .comments-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .video-comment {
            width: 100%;
            max-width: 600px;
            margin: 10px 0;
            padding: 10px;
            background-color: #F9F9F9;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .comment-graph {
            width: 100%;
            height: 300px;
            display: none;
            background-color: transparent;
        }

        .likert-description {
            margin-top: 10px;
            font-size: 14px;
        }

        .graph-container {
            display: none;
        }

        .influencer-container,
        .video-chart-container,
        .average-container,
        .comments-container,
        .comments-graph-container,
        .hashtags-container,
        .comments-average-container,
        .videos-container,
        .comments-details-container,
        .total-comments-container,
        .average-comments-container {
            margin-left: 500px;
            margin-top: 75px;
        }
    </style>
</head>

<body>
    <?php
    include('include/sidebar_graph.html');
    include('include/header-graph.html');
    ?>


    <div class="influencer-container">
        <h2 style="text-align:center;">Influencer Information</h2>
        <?php if (!empty($influencerData)): ?>
            <?php foreach ($influencerData as $influencer): ?>
                <div class="influencer-info" style="padding: 10px; margin-top: 20px;">
                    <div><b>Name:</b> <?php echo $influencer['name']; ?></div>
                    <div><b>Platform:</b> <?php echo $influencer['platform']; ?></div>
                    <div><b>Subscribers:</b> <?php echo $influencer['subscriber_count']; ?></div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div>No influencer data found for this file.</div>
        <?php endif; ?>
    </div>

    <div class="videos-container">
        <h3>Video Information</h3>
        <?php if (!empty($videoData)): ?>
            <?php foreach ($videoData as $index => $video): ?>
                <div class="video-info" style="margin: 10px 0; padding: 10px; background-color: #F9F9F9; border: 1px solid #ccc; border-radius: 5px;">
                    <h4><?php echo "Video " . ($index + 1) . ":"; ?></h4>
                    <div><b>Title:</b> <?php echo $video['title']; ?></div>
                    <div><b>Likes:</b> <?php echo number_format($video['likes']); ?></div>
                    <div><b>Duration:</b> <?php echo $video['duration']; ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div>No video data found for this file.</div>
        <?php endif; ?>
    </div>

    <div class="video-chart-container">
        <h1>Views Per Video</h1>
        <canvas id="videoChart" width="500" height="400"></canvas>
    </div>

    <div class="average-container">
        <h3>Average Views of <?php echo count($videoData); ?> Videos</h3>
        <h2 style="font-size:20px;"><?php echo number_format(round($averageViews, 2)); ?> Views</h2>
    </div>

    <div class="hashtags-container">
        <h3>Hashtags Used</h3>
        <?php if (!empty($hashtagsData)): ?>
            <ul>
                <?php foreach ($hashtagsData as $version => $hashtags): ?>
                    <li><strong>Video <?php echo $version; ?>:</strong>
                        <ul>
                            <?php foreach ($hashtags as $hashtag): ?>
                                <li><?php echo $hashtag; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div>No hashtags data available.</div>
        <?php endif; ?>
    </div>

    <div class="total-comments-container" style="text-align: center;">
        <h3>Total comments</h3>
        <?php
        $totalComments = 0;
        $videoCount = 0;

        if (!empty($videoCommentsData)): ?>
            <ul>
                <?php foreach ($videoCommentsData as $version => $totalcomments): ?>
                    <li><strong>Video <?php echo $version; ?>:</strong>
                        <?php

                        $commentCount = array_sum($totalcomments);
                        $totalComments += $commentCount;
                        $videoCount++;
                        ?>
                        <?php echo $commentCount; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div>No data available.</div>
        <?php endif; ?>
    </div>

    <div class="average-comments-container" style="text-align: center;">
        <h3>Average Comments</h3>
        <?php

        if ($videoCount > 0) {
            $averageComments = $totalComments / $videoCount;
            echo "<p>" . round($averageComments, 2) . "</p>";
        } else {
            echo "<p>No comments available to calculate average.</p>";
        }
        ?>
        <p><strong>Total Comments: <?php echo $totalComments; ?></strong></p>
        <p><strong>Video Count: <?php echo $videoCount; ?></strong></p>
    </div>


    <div class="comments-container">
        <?php if (!empty($commentsData)): ?>
            <?php foreach ($commentsData as $video => $comments): ?>
                <div class="video-comment" style="position: relative;">
                    <h4><?php echo "Comments for $video:"; ?></h4>
                    <i class="graph-icon fas fa-chart-bar" onclick="toggleGraph(event, <?php echo htmlspecialchars(json_encode($comments)); ?>)" style="cursor: pointer; position: absolute; top: 0; right: 0;"></i>
                    <div class="comment-content">
                        <?php foreach ($comments as $index => $commentData): ?>
                            <div class="comment">
                                <b>Comment <?php echo ($index + 1); ?>:</b> <?php echo htmlspecialchars($commentData['comment']); ?>
                            </div>
                            <div><b>Likert score:</b> <?php echo ($commentData['likertScore'] !== null ? $commentData['likertScore'] : 'N/A'); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="graph-container">
                        <canvas class="comment-graph"></canvas>
                        <div class="likert-description" style="display: none;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div>No comments data available.</div>
        <?php endif; ?>

    </div>

    <div class="comments-graph-container">
        <h1>Overall Comments Likert Scale</h1>
        <canvas id="commentsChart-likert" width="500" height="400" style="background-color: #f9f9f9;"></canvas>
    </div>

    <div class="comments-average-container">
        <h3 style="text-align: center;">The Average Likert Score of all comments is: <?php echo number_format($averageLikertScore, 2) . ' (' . $averageDescription . ')'; ?></h3>
    </div>


    <script>
        //**Likert Scale*/
        function getLikertDescription(score) {
            switch (score) {
                case 5:
                    return "Very Positive";
                case 4:
                    return "Positive";
                case 3:
                    return "Neutral";
                case 2:
                    return "Negative";
                case 1:
                    return "Very Negative";
                default:
                    return "N/A";
            }
        }

        function toggleGraph(event, comments) {
            event.stopPropagation();

            const videoCommentDiv = event.target.closest('.video-comment');
            const graphContainer = videoCommentDiv.querySelector('.graph-container');
            const canvas = videoCommentDiv.querySelector('.comment-graph');
            const likertDescription = videoCommentDiv.querySelector('.likert-description');
            const ctx = canvas.getContext('2d');
            const labels = comments.map((commentData, index) => `Comment ${index + 1}`);
            const likertScores = comments.map(commentData => commentData.likertScore || 0);

            const validScores = likertScores.filter(score => score !== 0);
            const averageScore = validScores.length > 0 ? (validScores.reduce((sum, score) => sum + score, 0) / validScores.length).toFixed(2) : 'N/A';

            const averageDescription = validScores.length > 0 ? getLikertDescription(Math.round(averageScore)) : "N/A";

            const scaleDescription = `
        5: Very Positive<br>
        4: Positive<br>
        3: Neutral<br>
        2: Negative<br>
        1: Very Negative<br>
        <b>The Average Likert Scale is:</b> ${averageScore} (${averageDescription})
    `;


            likertDescription.innerHTML = scaleDescription;


            if (graphContainer.style.display === 'block') {
                graphContainer.style.display = 'none';
                likertDescription.style.display = 'none';
                if (canvas.chart) {
                    canvas.chart.destroy();
                    canvas.chart = null;
                }
            } else {

                graphContainer.style.display = 'block';
                likertDescription.style.display = 'block';
                canvas.width = canvas.clientWidth;
                canvas.height = 300;

                canvas.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Likert Scores',
                            data: likertScores,
                            backgroundColor: 'rgba(75, 192, 192, 0.5)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                labels: {
                                    color: 'rgba(0, 0, 0, 1)',
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                min: 1,
                                max: 5,
                                title: {
                                    display: true,
                                    text: 'Likert Score',
                                    color: 'rgba(0, 0, 0, 1)'
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)',
                                },
                                ticks: {
                                    min: 1,
                                    max: 5,
                                    stepSize: 1
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)',
                                },
                            }
                        },
                        layout: {
                            padding: {
                                left: 20,
                                right: 20,
                                top: 20,
                                bottom: 20
                            }
                        }
                    }
                });
            }
        }
    </script>

    <script>
        //**Views*/
        const videoData = <?php echo json_encode($videoData); ?>;
        const currentFileId = "<?php echo $fileId; ?>";
        const labels = videoData.map((_, index) => `Video ${index + 1}`);
        const views = videoData.map(video => parseViews(video.views));
        const tooltips = videoData.map(video => video.title);

        const defaultColors = ['#000000', '#000000', '#000000', '#000000', '#000000'];
        const savedColors = labels.map((_, index) => localStorage.getItem(`bar-color-${currentFileId}-${index + 1}`) || defaultColors[index]);

        const ctx = document.getElementById('videoChart').getContext('2d');
        const videoChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Video Views',
                    data: views,
                    backgroundColor: savedColors,
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value >= 1000 ? (value / 1000).toFixed(1) + 'k' : value;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                const videoTitle = tooltips[tooltipItem.dataIndex];
                                const viewsCount = views[tooltipItem.dataIndex].toLocaleString();
                                return [
                                    videoTitle,
                                    `${viewsCount} Views`
                                ];
                            }
                        }
                    }
                }
            }
        });

        function parseViews(view) {
            if (typeof view === 'string') {
                const lowerView = view.toLowerCase();
                if (lowerView.endsWith('k')) {
                    return parseFloat(lowerView.slice(0, -1)) * 1000;
                } else if (lowerView.endsWith('m')) {
                    return parseFloat(lowerView.slice(0, -1)) * 1000000;
                } else if (lowerView.endsWith('b')) {
                    return parseFloat(lowerView.slice(0, -1)) * 1000000000;
                }
            }
            return parseInt(view.replace(/,/g, ''), 10) || 0;
        }

        function updateColorVideo(index) {
            const color = document.getElementById(`bar-color-${index + 1}`).value;
            videoChart.data.datasets[0].backgroundColor[index] = color;
            videoChart.update();

            localStorage.setItem(`bar-color-${currentFileId}-${index + 1}`, color);
        }

        for (let i = 1; i <= 5; i++) {
            const colorPicker = document.getElementById(`bar-color-${i}`);
            colorPicker.value = savedColors[i - 1];
            colorPicker.addEventListener('input', () => updateColorVideo(i - 1));
        }

        function loadColorsForFile(fileId) {
            for (let i = 1; i <= 5; i++) {
                const savedColor = localStorage.getItem(`bar-color-${fileId}-${i}`);
                savedColors[i - 1] = savedColor || defaultColors[i - 1];
                document.getElementById(`bar-color-${i}`).value = savedColors[i - 1];
            }
            videoChart.data.datasets[0].backgroundColor = savedColors;
            videoChart.update();
        }

        function switchFile(newFileId) {
            currentFileId = newFileId;
            loadColorsForFile(currentFileId);
        }
    </script>

    <script>
        //**Overall LikertScale */

        const fileId = "<?php echo $fileId; ?>";

        const likertLabels = ['1 (Very Negative)', '2 (Negative)', '3 (Neutral)', '4 (Positive)', '5 (Very Positive)'];
        const likertCounts = <?php echo json_encode(array_values($likertScoreCounts)); ?>;
        const defaultColorsLikert = ['rgba(75, 192, 192, 0.5)', 'rgba(75, 192, 192, 0.5)', 'rgba(75, 192, 192, 0.5)', 'rgba(75, 192, 192, 0.5)', 'rgba(75, 192, 192, 0.5)'];

        const savedColorsLikert = likertLabels.map((_, index) => localStorage.getItem(`likertColor${fileId}-${index + 1}-likert`) || defaultColorsLikert[index]);

        const commentsCtxLikert = document.getElementById('commentsChart-likert').getContext('2d');
        const commentsChartLikert = new Chart(commentsCtxLikert, {
            type: 'bar',
            data: {
                labels: likertLabels,
                datasets: [{
                    label: 'Number of Comments',
                    data: likertCounts,
                    backgroundColor: savedColorsLikert,
                    borderColor: savedColorsLikert.map(color => color.replace('0.5', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount of Comments',
                            font: {
                                size: 12,
                                family: 'Arial',
                                weight: 'normal',
                                color: 'rgba(0, 0, 0, 1)'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)',
                        },
                        ticks: {
                            min: 0,
                            max: Math.max(...likertCounts) + 1,
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)',
                        },
                    }
                },
                layout: {
                    padding: {
                        left: 20,
                        right: 20,
                        top: 20,
                        bottom: 20
                    }
                }
            }
        });

        function updateColorLikert(index) {
            const color = document.getElementById(`bar-color-likert-${index + 1}`).value;
            commentsChartLikert.data.datasets[0].backgroundColor[index] = color;
            commentsChartLikert.data.datasets[0].borderColor[index] = color;
            commentsChartLikert.update();

            localStorage.setItem(`likertColor${fileId}-${index + 1}-likert`, color);
        }


        for (let i = 0; i < likertLabels.length; i++) {
            const colorPicker = document.getElementById(`bar-color-likert-${i + 1}`);
            colorPicker.value = savedColorsLikert[i];
            colorPicker.addEventListener('input', () => updateColorLikert(i));
        }
    </script>
</body>

</html>

<?php
$con->close();
?>