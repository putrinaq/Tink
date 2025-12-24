<?php
session_start();
require_once 'config.php';

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // 2. Find the Active Cart
        $stmt = $pdo->prepare("SELECT CART_ID FROM CART WHERE CUSTOMER_ID = ? AND CART_STATUS = 'active'");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            $cart_id = $cart['CART_ID'];

            // 3. Calculate Order Total (Crucial Step!)
            // The Admin panel needs a total amount to display
            $stmtItems = $pdo->prepare("SELECT SUM(CARTITEM_QUANTITY * CARTITEM_PRICE) FROM CARTITEM WHERE CART_ID = ?");
            $stmtItems->execute([$cart_id]);
            $subtotal = $stmtItems->fetchColumn() ?: 0;

            // Apply Shipping Logic (Matching your account.php logic)
            $shipping = ($subtotal > 200) ? 0 : 15;
            $final_total = $subtotal + $shipping;

            // 4. INSERT INTO `ORDER` TABLE (This makes it show in Admin)
            // Note: We use backticks `order` because 'order' is a reserved SQL keyword
            $insertOrder = $pdo->prepare("INSERT INTO `order` (CUSTOMER_ID, CART_ID, ORDER_TOTAL, ORDER_STATUS) VALUES (?, ?, ?, 'completed')");
            $insertOrder->execute([$user_id, $cart_id, $final_total]);
            
            // Get the new Order ID for the thank you page
            $new_order_id = $pdo->lastInsertId();

            // 5. Update Old Cart Status (So it shows in Customer History)
            $update_stmt = $pdo->prepare("UPDATE CART SET CART_STATUS = 'completed' WHERE CART_ID = ?");
            $update_stmt->execute([$cart_id]);

            // 6. Create a NEW Active Cart for the user
            $new_cart_stmt = $pdo->prepare("INSERT INTO CART (CUSTOMER_ID, CART_STATUS) VALUES (?, 'active')");
            $new_cart_stmt->execute([$user_id]);

            // 7. Redirect to Thank You Page
            header("Location: thankyou.php?order_id=" . $new_order_id);
            exit;
        } else {
            // Cart was empty or not found
            header("Location: cart.php?error=empty");
            exit;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: cart.php");
    exit;
}
?>