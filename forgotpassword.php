<?php
include 'db.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo '<script>alert("Passwords do not match.");window.location.href = "forgotpassword.php";</script>';
        exit;
    }


    $stmt = $con->prepare("UPDATE user_info SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $password, $username);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo '<script>alert("Password reset successfully.");window.location.href = "login.php";</script>';
        } else {
            echo '<script>alert("Invalid Username")</script>';
        }
    } else {
        echo '<script>alert("Error updating password.")</script>';
    }

    $stmt->close();
}
$con->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VlogInSight</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="title">
        <span class="v1">Vlog</span><span class="s1">Insight</span>
    </div>
    <div class="main-container">
        <div class="login-container">
            <form action="forgotpassword.php" method="post">
                <h2 style="text-align:center;">RESET PASSWORD </h2>
                <input type="text" id="username" placeholder="username" name="username" required>
                <input type="password" id="password" placeholder="new password" name="password" required>
                <input type="password" id="confirm_password" placeholder="confirm password" name="confirm_password" required>
                <button type="submit">RESET</button>
            </form>
        </div>
        <div class="image-container">
            <h3>Welcome User!</h3>
            <img src="pic/login1.png" alt="Sample Image">
            <h4>Unveiling Trends: Vlogger Popularity on Facebook and Youtube Through Data Analysis</h4>
        </div>
    </div>
</body>

</html>