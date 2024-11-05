<style>
    #imeds {
        width: 440px;
        height: 440px;
        margin-left: 420px;

    }

    .success-message {
        margin-top: 10px;
        margin-bottom: 20px;
        font-size: 85px;
        color: green;
        text-align: center;
    }

    #home-link {
        display: block;
        margin-top: 20px;
        text-decoration: none;
        background-color: #4CAF50;
        color: white;
        padding: 18px 42px;
        border-radius: 5px;
        text-align: center;
        font-size: 45px;

    }
</style>


<?php
include 'db.php';
function generateRandomCode($length = 8)
{
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

require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';



$mail = new PHPMailer(true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $newCode = generateRandomCode();

    $checkEmailQuery = "SELECT * FROM `user_info` WHERE email = '$email'";
    $resultEmail = mysqli_query($con, $checkEmailQuery);

    if (mysqli_num_rows($resultEmail) > 0) {

        $sql = "UPDATE `user_info` SET forgot_password_code = '$newCode' WHERE email = '$email'";

        if (mysqli_query($con, $sql)) {
            try {

                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'joshua.gonzales.212@gmail.com';
                $mail->Password   = 'owtjjgqjqjrmpwes';
                $mail->SMTPSecure = 'ssl';
                $mail->Port       = 465;

                $mail->setFrom('no-reply@vloginsight.com', 'VlogInsight');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Code';
                $mail->Body    = "Your password reset code is: <b>$newCode</b>";
                $mail->AltBody = 'Your password reset code is: ' . $newCode;

                $mail->send();
                echo '<script>alert("Please check your email for the password reset code."); window.location.href = "login.php";</script>';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error: " . mysqli_error($con);
        }
    } else {
        echo '<script>alert("Email not found."); window.location.href = "forgot_password.php";</script>';
    }
}

mysqli_close($con);
?>