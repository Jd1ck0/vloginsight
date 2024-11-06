<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VlogInSight</title>
    <link rel = "stylesheet" href = "css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    #termsModal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }

    .modalContent {
        background-color: white;
      padding: 20px;
      border-radius: 8px;
      width: 80%;
      max-width: 600px;
      max-height: 70vh; 
      overflow-y: auto; 
      position: relative;
    }

    .closeBtn {
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: #ff0000;
      color: white;
      border: none;
      border-radius: 50%;
      padding: 5px;
      cursor: pointer;
    }
  </style>
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
                <label> <br><br>
                <input type="checkbox" id="termsCheckbox"> I agree to the 
                <a href="#" id="showTermsLink">Terms and Conditions</a>.
                </label>
            </form>
        </div>
        <div class="image-container">
            <h3>Welcome User!</h3>
            <img src="pic/login1.png" alt="Sample Image">
            <h4>Unveiling Trends: Vlogger Popularity on Facebook and Youtube Through Data Analysis</h4>
        </div>
    </div>
    <div id="termsModal">
    <div class="modalContent">
      <button class="closeBtn" id="closeBtn">&times;</button>
      <h2>Terms and Conditions</h2>
      <h5>Last updated: [November 06,2024]</h5>
      <h3> Welcome to VlogInsight! Please read these Terms and Conditions 
        carefully before registering an account or using our services. By accessing or using our website, 
        you agree to be bound by these Terms.</h3>
      <h3> 1. Acceptance of Terms </h3>
      <p>By creating an account and registering with your email, username, and password on 
        VlogInsight, you agree to comply with and be legally bound by these Terms. If you do not 
        agree with any part of these Terms, do not proceed with the registration process.</p>
      <h3> 2. Account Registration </h3>
      <p> To access certain features of our website, you must create an account. During registration, 
        you agree to provide accurate, current, and complete information, including:</p>
      <h4>  > Email Address </h4>
      <h4>  > Username </h4>
      <h4>  > Password </h4>       
      <p>You are responsible for maintaining the confidentiality of your account credentials, including your username and password. 
        You agree to notify us immediately of any unauthorized access or use of your account.</p>
      <h3> 3. Eligibility </h3>
      <p> To register for an account, you must:</p>
      <p> > Be at least [insert age, e.g., 13] years old or the legal age of majority in your jurisdiction. </p>
      <p> > Have the legal capacity to enter into this agreement.</p>
      <p> If you are registering on behalf of an organization or company, you represent and warrant that you have the authority 
        to bind that organization or company to these Terms.</p>
      <h3> 4. Use of the Website </h3>
      <p> By registering on our website, you agree to use the site only for lawful purposes and in accordance with these Terms.
         You may not use our website in a manner that could damage, disable, overburden, or impair the operation of the website 
         or interfere with any other user's access.</p>
      <h3> 5. Privacy </h3>
      <p> We respect your privacy. Our Privacy Policy outlines how we collect, use, and protect your personal data. </p>
      <h3> 6. User Responsibilities </h3>
      <p> You agree not to: </p>
      <p> > Use false or misleading information to create an account.</p>
      <p> > Share your account credentials with others or permit others to access your account. </p>
      <p> > Engage in any activity that violates any applicable law or the rights of others.</p>
      <h3> 7. Suspension or Termination of Account </h3>
      <p> We reserve the right to suspend or terminate your account if you violate these Terms or if we believe, in our sole discretion, 
        that your activities are harmful to the website, other users, or the service in any way. </p>
      <h3> 8. Modifications to Terms </h3>
      <p> We may update or modify these Terms at any time. We will notify you of any material changes, and your continued use of the 
        website after such changes constitutes your acceptance of the updated Terms. It is your responsibility to review these Terms 
        periodically for any changes.</p>
      <h3> 9. Limitation of Liability </h3>
      <p> To the maximum extent permitted by law, VlogInsight shall not be liable for any damages arising from your use of the 
        website or any services provided therein. </p>
      <h3> 10. Governing Law </h3>
      <p> These Terms shall be governed by and construed in accordance with the laws of Philippines, without regard to 
        its conflict of law principles.</p>
      <h3> 11. Contact Information </h3>
      <p> If you have any questions or concerns regarding these Terms, please contact us at: </p>
        <h5> Email: vloginsight@gmail.com</h5>



    </div>
  </div>

  <script>
    const showTermsLink = document.getElementById('showTermsLink');
    const termsModal = document.getElementById('termsModal');
    const closeBtn = document.getElementById('closeBtn');
    const termsCheckbox = document.getElementById('termsCheckbox');

    showTermsLink.addEventListener('click', function(event) {
      event.preventDefault(); 
      termsModal.style.display = 'flex';
    });

    closeBtn.addEventListener('click', function() {
      termsModal.style.display = 'none';
    });

    termsCheckbox.addEventListener('change', function() {
      if (!termsCheckbox.checked) {
        termsModal.style.display = 'none';
      }
    });
  </script>

</body>
</html>
