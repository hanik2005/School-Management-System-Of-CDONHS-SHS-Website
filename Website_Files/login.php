<?php
include '../Back_End_Files/PHP_Files/login_backend.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cagayan De Oro National High School Senior High</title>
    <link rel="icon" href="../Assets/LOGO.png" type="image/jpg">
    <link rel="stylesheet" href="../Design/login_design.css">
    <script src="../Back_End_Files/JSCRIPT_Files/login_script.js"></script>
    <script src="../Back_End_Files/JSCRIPT_Files/login_captcha.js"></script>
    
</head>
<body>
    <div class="login-wrapper">
        <div class="left-panel">
            <img src="../Assets/LOGO.png" alt="School Logo" class="logo">
            <h1>Cagayan De Oro National High School Senior High</h1>
            <p>2nd 3rd St, Cagayan De Oro City, 9000 Misamis Oriental</p>
        </div>
        <div class="right-panel">
            <div class="login-form">
                <h2>Sign In</h2>
                <?php if (isset($_GET['error'])): ?>
                    <?php if ($_GET['error'] == 'captcha'): ?>
                        <p class="error">Incorrect captcha answer. Please try again.</p>
                    <?php elseif ($_GET['error'] == 'invalid'): ?>
                        <p class="error">Invalid username or password.</p>
                    <?php endif; ?>
                <?php endif; ?>
                <form action="../Back_End_Files/PHP_Files/login_backend.php" method="POST" autocomplete="off">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required autocomplete="off">
                    <label for="password">Password:</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required  autocomplete="new-password">
                        <img id="toggleIcon" src="../Assets/NotVisible.png" alt="Toggle Password" onclick="togglePassword()">
                    </div>
                    <label id="captchaLabel" for="captcha">Solve Authentication:</label>
                    <input type="text" id="captcha" name="captcha" required>
                    <input type="hidden" id="correct_sum" name="correct_sum">
                    <input type="submit" value="Login">
                </form>
                <p><a href="forgot_password.php">Forgot Password?</a></p>
            </div>
        </div>
    </div>
</body>
</html>