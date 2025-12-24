<?php
// index.php
require_once 'config.php';

// 2. fetch 4 random "Trending" items from DB
$sql = "SELECT * FROM ITEM WHERE ITEM_ACTIVE = 1 ORDER BY RAND() LIMIT 4";
$stmt = $pdo->query($sql);
$trending_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tink Jewelry</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=1">
    <link rel="stylesheet" href="assets/css/home.css?v=1">

</head>

<body>



    <!-- ===== Header ===== -->
    <?php include 'components/header.php'; ?>

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
            <?php if(count($trending_items) > 0): ?>
                <?php foreach($trending_items as $item): ?>
                    <div class="product">
                        <a href="product_detail.php?id=<?= $item['ITEM_ID'] ?>">
                            <img src="<?= htmlspecialchars($item['ITEM_IMAGE']) ?>" alt="<?= htmlspecialchars($item['ITEM_NAME']) ?>">
                        </a>
                        <h4><?= htmlspecialchars($item['ITEM_NAME']) ?></h4>
                        <div class="price">MYR <?= number_format($item['ITEM_PRICE'], 2) ?></div>
                        <div class="material"><?= htmlspecialchars($item['ITEM_MATERIAL']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No trending items available.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===== Features ===== -->
    <section class="features" style="background-image: url('assets/images/wave.jpg');">
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
            <em>Available on pendants, ring bands, and
                bracelet plates—add names, initials, dates,
                or short messages for a personal touch</em>
            <p>
                Your jewelry should say something special.
                With our engraving service, every piece becomes uniquely
                yours — crafted with care to celebrate your story.
            </p>
            <button>Design Your Moment</button>
        </div>

        <div class="promo-image" style="background-image: url('assets/images/engraving.png');"></div>
    </section>

    <!-- ===== Footer ===== -->
     <?php include 'components/footer.php';?>
    
</body>
</html>