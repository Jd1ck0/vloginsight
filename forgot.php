<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
    background: linear-gradient(to bottom, #E7833B, #2E55DF);
    }
        h1 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 400px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 1 5 100px rgba(11, 0, 168, 9);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #333;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #555;
        }
        .os{
            position: absolute;
            left: 600px;
            top: 100px;
            color: black;
            font-size: 20px;
        }
    </style>
</head>
<body>
<a href="login.php" style="position: absolute; top: 10px; left: 50px; text-decoration: none; color: black;">
    <svg width="54" height="74" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 12H4" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M10 18L4 12L10 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
   
</a>
    <div style = "margin: 200px auto;">
    <h1>Input Your Email</h1>
   <h1> To Reset Your Password</h1>
    <form action="fpverify.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <input type="submit" name="forgot_password" value="Reset Password">
    </form>
    </div>
</body>
</html>
