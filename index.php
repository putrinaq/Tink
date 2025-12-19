<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tink Jewelry</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=1">
    <link rel="stylesheet" href="assets/css/home.css?v=1">

</head>

<body >

<!-- ===== Top Banner ===== -->
<div class="top-banner">
    Up To 30% OFF For Christmas Gift
</div>

<!-- ===== Header ===== -->
<header>
    <div class="logo">Tink</div>

    <nav class="main-nav">
        <a href="bracelets.php" class="nav-link active">BRACELETS</a>
        <a href="necklaces.php" class="nav-link">NECKLACES</a>
        <a href="earrings.php" class="nav-link">EARRINGS</a>
        <a href="rings.php" class="nav-link">RINGS</a>
        <a href="charms.php" class="nav-link">CHARMS</a>
        <a href="designers.php" class="nav-link">DESIGNERS</a>
    </nav>

    <div class="nav-icons">
        <!-- Search -->
        <div class="search-box">
            <input type="text" placeholder="Search our store" />
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>

        <!-- Cart & User -->
        <i class="fa-solid fa-bag-shopping"></i>
        <i class="fa-regular fa-user"></i>
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

<!-- ===== Hero Section ===== -->
<section class="hero" style="background-image: url('assets/images/hero.png');">
    <div class="hero-text">
        <h1>
            HANDMADE JEWELRY FOR<br>
            MOMENTS THAT MATTER
        </h1>
    </div>
</section>

<!-- ===== Trending Section ===== -->
<section class="section">
    <h2>Trending Jewellery</h2>

    <div class="products">

        <div class="product">
            <img src="assets/images/ring1.png" alt="Little Flower Ring">
            <h4>"Little Flower" Adjustable Ring</h4>
            <div class="price">MYR 33</div>
            <div class="material">Silver-plated copper</div>
        </div>

        <div class="product">
            <img src="assets/images/bracelet1.png" alt="Ocean Wave Bracelet">
            <h4>Ocean Wave Elegance Bracelet</h4>
            <div class="price">MYR 48</div>
            <div class="material">Silver sterling & resin</div>
        </div>

        <div class="product">
            <img src="assets/images/earring1.png" alt="Ocean Whisper Earrings">
            <h4>Ocean Whisper Earrings</h4>
            <div class="price">MYR 29</div>
            <div class="material">Hypoallergenic alloy</div>
        </div>

        <div class="product">
            <img src="assets/images/ring2.png" alt="Eternal Bloom Ring">
            <h4>Eternal Bloom Ring</h4>
            <div class="price">MYR 39</div>
            <div class="material">Silver Sterling</div>
        </div>

    </div>
</section>

<!-- ===== Features ===== -->
<section class="features" style="background-image: url('assets/images/wave.png');">
    <div class="feature-box">

        <div class="feature">
            <i class="fa-regular fa-gem"></i>
            <h4>Quality Materials</h4>
            <p>Lasting shine & skin-safe</p>
        </div>

        <div class="feature">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <h4>Customize</h4>
            <p>Personalised just for you</p>
        </div>

        <div class="feature">
            <i class="fa-solid fa-truck"></i>
            <h4>Fast Delivery</h4>
            <p>Right to your door</p>
        </div>

        <div class="feature">
            <i class="fa-solid fa-dollar-sign"></i>
            <h4>Affordable Price</h4>
            <p>Style within budget</p>
        </div>

    </div>
</section>

<!-- ===== Premium Packaging ===== -->
<section class="promo-section">
    <div class="promo-image" style="background-image: url('assets/images/packaging.png');"></div>

    <div class="promo-content">
        <h3>Premium Gift Packaging</h3>
        <em>Always Complimentary</em>
        <p>
            Whether it’s for someone you love or for yourself, every order comes
            beautifully wrapped with premium gift packaging.
        </p>
        <button>Get Premium Packaging</button>
    </div>
</section>

<!-- ===== Custom Engraving ===== -->
<section class="promo-section">
    <div class="promo-content">
        <h3>Custom Engraving Service</h3>
        <em>Available on pendants, ring bands, and bracelets</em>
        <p>
            Add names, initials, dates, or short messages for a personal touch.
            Your jewelry should say something special.
        </p>
        <button>Design Your Moment</button>
    </div>

    <div class="promo-image" style="background-image: url('assets/images/engraving.png');"></div>
</section>

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

</body>
</html>
