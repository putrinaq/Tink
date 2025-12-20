<?php
// login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=1">
    <link rel="stylesheet" href="assets/css/login.css?v=1">

</head>

<body class="product-page">
<header>
    <div class="logo">
    <img src="assets/images/logo2.png" alt="Tink" width="102" height="41.5">
</div>

    <nav class="main-nav">
        <a href="bracelets.php" class="nav-link active">BRACELETS</a>
        <a href="necklaces.php" class="nav-link">NECKLACES</a>
        <a href="earrings.php" class="nav-link">EARRINGS</a>
        <a href="rings.php" class="nav-link">RINGS</a>
        <a href="charms.php" class="nav-link">CHARMS</a>
        <a href="designers.php" class="nav-link">DESIGNERS</a>
    </nav>

<div class="header-right">
        <div class="search-bar">
            <input type="text" placeholder="Search our store">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
<div class="icons">
    <a href="products.php">
        <i class="fa-solid fa-bag-shopping"></i>
    </a>

    <a href="signup.php">
        <i class="fa-regular fa-user"></i>
    </a>
</div>
</div>

    <script>
    const searchBox = document.querySelector('.search-box');
    const searchIcon = searchBox.querySelector('i');
    const searchInput = searchBox.querySelector('input');

    searchIcon.addEventListener('click', () => {
        searchBox.classList.toggle('active');
        searchInput.focus();
    });
</script>
</header>

<!-- ==== Content ==== -->
 <div class="login-page">
    <!-- LEFT IMAGE SECTION -->
    <div class="login-image">
        <img src="assets/images/login.png" alt="Tink Jewelry">
    </div>

    <!-- RIGHT LOGIN FORM -->
    <div class="login-form-wrapper">
        <div class="login-form">
            <h1>Login</h1>
            <p class="subtitle">Welcome Back!</p>

            <form action="login_process.php" method="POST">
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </div>
</div>

<!-- ===== Footer ===== -->
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
</boby>
</html>