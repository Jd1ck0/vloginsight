<?php
$con = mysqli_connect("localhost", "root", "", "vloginsight", 3306);

if (!$con) {
    die("Could not connect: " . mysqli_connect_error());
}

function generateRandomCode($length = 5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $code;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'C:\Xampp\htdocs\PHPMailer\PHPMailer\src\Exception.php';
require 'C:\Xampp\htdocs\PHPMailer\PHPMailer\src\PHPMailer.php';
require 'C:\Xampp\htdocs\PHPMailer\PHPMailer\src\SMTP.php';

$mail = new PHPMailer(true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmpassword'];

    $confirmationCode = generateRandomCode();

    $checkEmailQuery = "SELECT * FROM `user_info` WHERE email = '$email'";
    $resultEmail = mysqli_query($con, $checkEmailQuery);

   
    $checkUsernameQuery = "SELECT * FROM `user_info` WHERE username = '$username'";
    $resultUsername = mysqli_query($con, $checkUsernameQuery);

    if ($password !== $confirm_password) {
        echo '<script>alert("Passwords do not match."); window.location.href = "register.php";</script>';
        exit; 
    }

    if (mysqli_num_rows($resultEmail) > 0) {
        echo '<script>alert("Email is already associated with an account."); window.location.href = "register.php";</script>';
    } elseif (mysqli_num_rows($resultUsername) > 0) {
        echo '<script>alert("Username is already taken."); window.location.href = "register.php";</script>';
    } else {
        $sql = "INSERT INTO `user_info` (`username`, `email`, `password`, `confirmation_code`)
                VALUES ('$username', '$email', '$password', '$confirmationCode')";

        if (mysqli_query($con, $sql)) {
            try {
                echo '<script>alert("Please check your email for confirmation."); window.location.href = "confirm_account.php";</script>';

                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'joshua.gonzales.212@gmail.com';
                $mail->Password   = 'owtjjgqjqjrmpwes';
                $mail->SMTPSecure = 'ssl';
                $mail->Port       = 465;

                $mail->setFrom($email, 'VlogInsight');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'VlogInsight';
                $mail->Body    = "Your confirmation code: <b>$confirmationCode</b>";
                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                $mail->send();
                echo 'Message has been sent';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error: " . mysqli_error($con);
        }
    }
}

mysqli_close($con);
?>
