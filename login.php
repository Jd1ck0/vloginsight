<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VlogInSight</title>
    <link rel = "stylesheet" href = "css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="title">
        <span class ="v1">Vlog</span><span class = "s1">Insight</span>
    </div>
    <div class="main-container">
        <div class="login-container">
            <img src = "pic/user.png" class = "ui">
            <form action="login-process.php" method="post">
                <input type="text" id="username" placeholder = "username" name="username" required>
                <input type="password" id="password" placeholder = "password" name="password" required>
                <p style = "text-align: center"> Don't have an account? <a href="register.php">Register</a> </p>
                <button type="submit">Login</button>
                <p style = "text-align: center"> <a href= 'forgot.php'>Forgot password</a> </p>
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
