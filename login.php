<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | Tink</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cust-login.css">

    <style>
    .error-message {
        color: #d9534f;
        background-color: #fdf7f7;
        border: 1px solid #e0b4b4;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        font-size: 0.9rem;
        text-align: center;
    }

    /* Optional: Basic reset for fieldset to look good immediately */
    fieldset.input-group {
        border: 1px solid #ccc;
        /* Border around the input area */
        border-radius: 4px;
        padding: 5px 12px;
        margin-bottom: 20px;
    }

    fieldset.input-group legend {
        font-size: 0.85rem;
        padding: 0 5px;
        /* Spacing around text so border doesn't touch it */
        color: #1a1a1a;
        font-weight: 500;
    }

    fieldset.input-group input {
        border: none;
        /* Remove input border so fieldset acts as the border */
        width: 100%;
        outline: none;
        background: transparent;
        padding: 5px 0;
    }
    </style>
</head>

<body class="product-page">

    <?php include 'components/header.php'; ?>

    <div class="login-page">
        <div class="login-image">
            <img src="assets/images/login.png" alt="Tink Jewelry">
        </div>

        <div class="login-form-wrapper">
            <div class="login-form">
                <h1>Login</h1>
                <p class="subtitle">Welcome Back!</p>

                <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
                <?php endif; ?>

                <form action="login_process.php" method="POST">
                    <fieldset class="input-group">
                        <legend>Email</legend>
                        <input type="email" name="email" required>
                    </fieldset>

                    <fieldset class="input-group">
                        <legend>Password</legend>
                        <input type="password" name="password" required>
                    </fieldset>

                    <button type="submit" class="login-btn">Login</button>
                </form>

                <div style="text-align: center; margin-top: 15px; font-size: 0.9rem;">
                    Don't have an account? <a href="signup.php" style="color: #333; font-weight: 600;">Sign up</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <h4>Info</h4>
                <a href="#">Terms & Conditions</a>
                <a href="#">Privacy & Policy</a>
                <a href="#">FAQ</a>
            </div>

            <div>
                <h4>Customer Service</h4>
                <p><i class="fa-solid fa-phone"></i> 013-8974568</p>
                <p><i class="fa-solid fa-envelope"></i> tink@gmail.com</p>
            </div>

            <div>
                <h4>Follow Us</h4>
                <div class="footer-social">
                    <i class="fa-brands fa-facebook"></i>
                    <i class="fa-brands fa-instagram"></i>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            © <?php echo date("Y"); ?> Tink. All Rights Reserved
        </div>
    </footer>

</body>

</html>