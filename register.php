<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VlogInSight</title>
    <link rel = "stylesheet" href = "css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="title">
        <span class ="v1">Vlog</span><span class = "s1">Insight</span>
    </div>
    <div class="main-container">
        <div class="login-container">
            <h1 style ="text-align:center"> REGISTRATION </h1>
            <form action="verify.php" method="post">
                <input type="text" id="username" placeholder = "username" name="username" required>
                <input type="password" id="password" placeholder = "password" name="password" required>
                <input type="password" id="confirmpassword" placeholder = "confirm password" name="confirmpassword" required>
                <input type="text" id="email" placeholder = "email" name="email" required>
                <button class = "login-button">Sign up </button>
                <p style = "text-align:center;"> <a href = "login.php"> Already have an account?</a></p>
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
