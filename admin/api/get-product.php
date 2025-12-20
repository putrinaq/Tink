<?php

/**
 * API Endpoint: Get Product Data
 * Path: /admin/api/get-product.php
 * Used for: Loading product details in edit modal
 */

require_once '../../config.php';
// session_start();

// // Check admin authentication
// if (!isset($_SESSION['admin_id'])) {
//     http_response_code(401);
//     echo json_encode(['error' => 'Unauthorized']);
//     exit;
// }

// Validate request
if (!isset($_GET['item_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing item_id parameter']);
    exit;
}

$item_id = intval($_GET['item_id']);

try {
    // Fetch product
    $stmt = $pdo->prepare("
        SELECT i.*, d.DESIGNER_ID 
        FROM ITEM i
        JOIN DESIGNER d ON i.DESIGNER_ID = d.DESIGNER_ID
        WHERE i.ITEM_ID = ?
    ");
    $stmt->execute([$item_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    // Return product data
    header('Content-Type: application/json');
    echo json_encode($product);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
