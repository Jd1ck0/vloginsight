<?php
session_start(); 

$con = mysqli_connect("localhost", "root", "", "vloginsight", 3306);

if (!$con) {
    die("Could not connect: " . mysqli_connect_error());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userConfirmationCode = $_POST['code'];

    if (!empty($userConfirmationCode)) {

        $checkCodeQuery = "SELECT * FROM user_info WHERE forgot_password_code = '$userConfirmationCode'";
        $result = mysqli_query($con, $checkCodeQuery);

        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $_SESSION['reset_password_code'] = $userConfirmationCode;
                header("Location: forgotpassword.php");
                exit();
            } else {
                $invalidCode = true;
            }
        } else {
            $errorMessage = "Query failed - " . mysqli_error($con);
        }
    } else {
        $errorMessage = "Please enter a confirmation code";
    }
}

mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VlogInSight</title>
    <link rel = "stylesheet" href = "css/verify.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="form-container">
    <h1> Reset Your Password</h1>
    <form action="confirm_fp.php" method="POST">
    <input type="text" id="code" placeholder = "Insert Code" name="code" required>
    <button type = "submit"> Submit </button>
    </div>

</body>