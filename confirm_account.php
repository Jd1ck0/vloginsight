<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userConfirmationCode = mysqli_real_escape_string($con, $_POST['code']);
    if (!empty($userConfirmationCode)) {
        $checkCodeQuery = "SELECT * FROM user_info WHERE confirmation_code = '$userConfirmationCode'";
        $result = mysqli_query($con, $checkCodeQuery);
        if ($result && mysqli_num_rows($result) > 0) {
            $updateStatusQuery = "UPDATE user_info SET status = 'verified' WHERE confirmation_code = '$userConfirmationCode'";
            if (mysqli_query($con, $updateStatusQuery)) {
                echo '<script> alert("Account confirmed!")
                window.location.href = "login.php";</script></script>';
            } else {
                echo "Error updating confirmation status: " . mysqli_error($con);
            }
        } else {

            echo '<script> alert("Invalid or already confirmed confirmation code")</script>';
        }
    } else {

        echo '<script> alert("Please enter a confirmation code")</script>';
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
    <a href="register.php" style="position: absolute; top: 10px; left: 50px; text-decoration: none; color: black;">
    <svg width="54" height="74" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 12H4" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M10 18L4 12L10 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    </a>
    <div class="form-container">
    <h1> Verify Your Email</h1>
    <form action="confirm_account.php" method="POST">
    <input type="text" id="code" placeholder = "Insert Code" name="code" required>
    <button type = "submit"> Submit </button>
    </div>

</body>