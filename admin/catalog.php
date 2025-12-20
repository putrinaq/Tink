<?php
session_start();

// Check if the admin_id session variable exists
if (!isset($_SESSION['admin_id'])) {
    // If not logged in, redirect to the login page
    header('Location: login.php');
    exit;
}

require_once '../config.php'; // Your database connection

// --- HANDLE FORM SUBMISSIONS ---

// Add/Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_product' || $action === 'edit_product') {
        $item_name = trim($_POST['item_name']);
        $item_description = trim($_POST['item_description']);
        $item_category = $_POST['item_category'];
        $item_price = floatval($_POST['item_price']);
        $item_stock = intval($_POST['item_stock']);
        $designer_id = $_POST['designer_id'];
        $item_material = trim($_POST['item_material']);

        // Image upload handling
        $item_image = null;
        if ($_FILES['item_image']['size'] > 0) {
            $upload_dir = '/uploads/products/';
            $file_ext = strtolower(pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (in_array($file_ext, $allowed_ext) && $_FILES['item_image']['size'] <= $max_size) {
                $filename = 'item_' . time() . '.' . $file_ext;
                if (move_uploaded_file($_FILES['item_image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $upload_dir . $filename)) {
                    $item_image = $upload_dir . $filename;
                }
            }
        }

        try {
            if ($action === 'add_product') {
                $stmt = $pdo->prepare("
                    INSERT INTO ITEM (DESIGNER_ID, ITEM_CATEGORY, ITEM_NAME, ITEM_DESCRIPTION, 
                                     ITEM_MATERIAL, ITEM_PRICE, ITEM_STOCK, ITEM_IMAGE)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $designer_id,
                    $item_category,
                    $item_name,
                    $item_description,
                    $item_material,
                    $item_price,
                    $item_stock,
                    $item_image
                ]);
                $success_msg = "Product added successfully!";
            } else {
                $item_id = intval($_POST['item_id']);
                if ($item_image) {
                    $stmt = $pdo->prepare("
                        UPDATE ITEM SET DESIGNER_ID=?, ITEM_CATEGORY=?, ITEM_NAME=?, 
                                       ITEM_DESCRIPTION=?, ITEM_MATERIAL=?, ITEM_PRICE=?, 
                                       ITEM_STOCK=?, ITEM_IMAGE=?
                        WHERE ITEM_ID=?
                    ");
                    $stmt->execute([
                        $designer_id,
                        $item_category,
                        $item_name,
                        $item_description,
                        $item_material,
                        $item_price,
                        $item_stock,
                        $item_image,
                        $item_id
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE ITEM SET DESIGNER_ID=?, ITEM_CATEGORY=?, ITEM_NAME=?, 
                                       ITEM_DESCRIPTION=?, ITEM_MATERIAL=?, ITEM_PRICE=?, 
                                       ITEM_STOCK=?
                        WHERE ITEM_ID=?
                    ");
                    $stmt->execute([
                        $designer_id,
                        $item_category,
                        $item_name,
                        $item_description,
                        $item_material,
                        $item_price,
                        $item_stock,
                        $item_id
                    ]);
                }
                $success_msg = "Product updated successfully!";
            }
        } catch (Exception $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }

    // Delete Product
    if ($action === 'delete_product') {
        $item_id = intval($_POST['item_id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM ITEM WHERE ITEM_ID = ?");
            $stmt->execute([$item_id]);
            $success_msg = "Product deleted successfully!";
        } catch (Exception $e) {
            $error_msg = "Error deleting product: " . $e->getMessage();
        }
    }

    // Update Stock
    if ($action === 'update_stock') {
        $item_id = intval($_POST['item_id']);
        $new_stock = intval($_POST['item_stock']);
        try {
            $stmt = $pdo->prepare("UPDATE ITEM SET ITEM_STOCK = ? WHERE ITEM_ID = ?");
            $stmt->execute([$new_stock, $item_id]);
            $success_msg = "Stock updated successfully!";
        } catch (Exception $e) {
            $error_msg = "Error updating stock: " . $e->getMessage();
        }
    }
}

// --- FETCH DATA ---

// Get search/filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'item_id_desc';
$items_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

// Build query
$where_clause = "1=1";
$params = [];
if ($search) {
    $where_clause .= " AND (ITEM_NAME LIKE ? OR ITEM_DESCRIPTION LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category_filter) {
    $where_clause .= " AND ITEM_CATEGORY = ?";
    $params[] = $category_filter;
}

// Sort mapping
$sort_map = [
    'item_id_desc' => 'ORDER BY i.ITEM_ID DESC',
    'item_id_asc' => 'ORDER BY i.ITEM_ID ASC',
    'name_asc' => 'ORDER BY i.ITEM_NAME ASC',
    'price_low' => 'ORDER BY i.ITEM_PRICE ASC',
    'price_high' => 'ORDER BY i.ITEM_PRICE DESC',
    'stock_low' => 'ORDER BY i.ITEM_STOCK ASC',
    'recent' => 'ORDER BY i.ITEM_DATE DESC'
];
$order_clause = $sort_map[$sort_by] ?? $sort_map['item_id_desc'];

// Get total items count
$count_sql = "SELECT COUNT(*) as total FROM ITEM i WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_items = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_items / $items_per_page);

// Get items
$items_sql = "
    SELECT i.*, d.DESIGNER_NAME 
    FROM ITEM i 
    JOIN DESIGNER d ON i.DESIGNER_ID = d.DESIGNER_ID 
    WHERE $where_clause 
    $order_clause 
    LIMIT " . intval($items_per_page) . " OFFSET " . intval($offset) . "
";
$items_stmt = $pdo->prepare($items_sql);
$items_stmt->execute($params);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get designers
$designers = $pdo->query("SELECT * FROM DESIGNER ORDER BY DESIGNER_NAME")->fetchAll(PDO::FETCH_ASSOC);

// Get inventory statistics
$inv_stats = $pdo->query("
    SELECT 
        COUNT(*) as total_products,
        SUM(ITEM_STOCK) as total_stock,
        AVG(ITEM_PRICE) as avg_price,
        MIN(ITEM_PRICE) as min_price,
        MAX(ITEM_PRICE) as max_price
    FROM ITEM
")->fetch(PDO::FETCH_ASSOC);

// Get low stock items
$low_stock = $pdo->query("
    SELECT ITEM_ID, ITEM_NAME, ITEM_STOCK 
    FROM ITEM 
    WHERE ITEM_STOCK <= 15 
    ORDER BY ITEM_STOCK ASC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Get category breakdown
$category_stats = $pdo->query("
    SELECT ITEM_CATEGORY, COUNT(*) as count, SUM(ITEM_STOCK) as total_stock 
    FROM ITEM 
    GROUP BY ITEM_CATEGORY 
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items/Catalog Management - TINK Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/dashboard.css">

</head>

<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">
            <svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 288 149.67">
                <defs>
                    <style>
                    .cls-1 {
                        fill: #000;
                        stroke-width: 0px;
                    }
                    </style>
                </defs>
                <path class="cls-1"
                    d="M108.85,55.5h-.66c-4.05-14.45-12.56-14.49-23.14-14.49v66.68c0,5.53,5.29,9.31,10.02,9.31v.93h-36.91v-.93c4.73,0,10.02-3.78,10.02-9.31V41.01c-10.57,0-19.15.04-23.19,14.49h-.68l.24-15.54h64.13l.18,15.54Z" />
                <path class="cls-1"
                    d="M123.07,61.07c-4.47.41-5.34,1.44-5.69,6.69-.01.2-.3.2-.31,0-.35-5.25-1.22-6.28-5.69-6.69-.22-.02-.22-.35,0-.37,4.47-.41,5.34-1.44,5.69-6.69.01-.2.3-.2.31,0,.35,5.25,1.22,6.28,5.69,6.69.22.02.22.35,0,.37Z" />
                <path class="cls-1"
                    d="M49.34,35.16c-1.32.12-1.57.43-1.68,1.99,0,.06-.09.06-.09,0-.1-1.57-.36-1.87-1.68-1.99-.07,0-.07-.1,0-.11,1.32-.12,1.57-.43,1.68-1.99,0-.06.09-.06.09,0,.1,1.57.36,1.87,1.68,1.99.07,0,.07.1,0,.11Z" />
                <path class="cls-1"
                    d="M39.75,41.47c-1.52.15-1.82.52-1.94,2.43,0,.07-.1.07-.11,0-.12-1.91-.41-2.28-1.94-2.43-.08,0-.08-.13,0-.13,1.52-.15,1.82-.52,1.94-2.43,0-.07.1-.07.11,0,.12,1.91.41,2.28,1.94,2.43.08,0,.08.13,0,.13Z" />
                <path class="cls-1"
                    d="M44.89,34.59c-2.86.26-3.42.89-3.64,4.16,0,.12-.19.12-.2,0-.22-3.27-.78-3.9-3.64-4.16-.14-.01-.14-.22,0-.23,2.86-.26,3.42-.89,3.64-4.16,0-.12.19-.12.2,0,.22,3.27.78,3.9,3.64,4.16.14.01.14.22,0,.23Z" />
                <path class="cls-1"
                    d="M140.41,111.44h0c-3.72,2.98-10.76,8.06-16.25,8.06-6.75,0-11.59-5.28-11.81-11.28v-24.56c-.21-3.4-1.68-6.97-6.83-6.97v-.82c9.34-1.17,20.13-6.58,20.13-6.58h.68v36.26c.28,4.99,2.5,8.07,6.49,8.07,3.09,0,4.82-1.22,6.94-3.11h0c.09-.07.2-.12.33-.12.31,0,.55.26.55.58,0,.19-.1.36-.24.46Z" />
                <path class="cls-1"
                    d="M255.75,111.12c0,.2-.1.38-.24.47-.49.38-1.02.82-1.6,1.27-.26.18-.53.38-.8.58-.7.52-1.48,1.06-2.28,1.59-.38.25-.76.5-1.15.74-3.31,2.04-7.06,3.79-10.27,3.79-6.75,0-8.9-4.58-11.81-11.28l-5.52-13.94-8.45,8.27v2.97c.28,4.99,2.5,8.07,6.49,8.07,3.09,0,4.82-1.22,6.94-3.11h0c.09-.07.2-.12.33-.12.31,0,.55.26.55.58,0,.19-.1.36-.24.46h0c-3.72,2.98-10.76,8.06-16.25,8.06-6.75,0-11.59-5.28-11.81-11.28v-54.39c-.21-3.4-1.68-6.97-6.83-6.97v-.82c9.34-1.17,20.13-6.58,20.13-6.58h.68v61.69l8.04-7.84,9.34-9.11c.66-.65,1.23-1.3,1.69-2,.88-1.3,1.36-2.77,1.36-4.7,0-3.64-1.8-6.34-6.53-6.34,0,0-.25-.06-.25-.43s.25-.5.25-.5h23.62s.33.13.33.48-.33.46-.33.46c-5.06.11-13.73,7.16-17.55,11.86l-.42.41,8.36,21.07c1.52,3.85,3.8,8.19,6.52,9.16,1.97.69,3.62-.62,5.06-1.6.37-.24.72-.52,1.08-.83l.68-.57h.01c.09-.09.19-.13.31-.13.3,0,.54.25.54.56Z" />
                <path class="cls-1"
                    d="M199.23,111.1c0,.2-.1.38-.25.47-.48.38-1.01.82-1.59,1.28-.26.18-.54.38-.8.57-.71.53-1.48,1.06-2.28,1.59-.38.25-.76.49-1.15.73-3.31,2.04-7.06,3.8-10.27,3.8-6.75,0-11.59-5.28-11.81-11.28v-23.93c0-5.64-2.25-9.16-6.51-9.16-3.63,0-5.65,1.91-8.27,4.3v26.07c.29,5,2.5,8.07,6.49,8.07,3.09,0,4.82-1.22,6.94-3.11.1-.07.21-.12.33-.12.31,0,.55.26.55.58,0,.19-.09.35-.23.46h0c-3.72,2.97-10.76,8.06-16.25,8.06-6.74,0-11.59-5.28-11.81-11.28v-24.56c-.07-1.19-.3-2.39-.78-3.45,3.45-1.15,5.27-5.13,5.27-5.13,0,0,0-.01-.01-.02.1-.27.38-1.12.54-2.26,4.77-1.74,8.29-3.5,8.29-3.5h.67v8.61c3.48-2.84,11.08-8.6,16.93-8.6,6.75,0,11.59,5.28,11.81,11.28v17.34h-.01v6.59c0,5.64,2.25,9.16,6.52,9.16,2.07,0,3.62-.63,5.05-1.6.36-.24.71-.52,1.09-.83l.68-.57h.01c.1-.08.19-.12.31-.12.31,0,.55.26.55.56Z" />
                <path class="cls-1"
                    d="M146.66,67.66c-1.72-3.43-6.09-4.94-6.09-4.94,0,0-.1.25-.22.65-.37-.16-.6-.25-.6-.25,0,0-1.6,4.05.12,7.49.33.66.76,1.26,1.24,1.77-.72-.13-1.46-.18-2.21-.08-3.9.52-6.41,4.66-6.41,4.66,0,0,.34.29.93.69-.25.45-.38.74-.38.74,0,0,2.12,1.48,4.73,2,1.04.21,2.16.26,3.25-.01.19-.04.36-.1.54-.17,3.45-1.15,5.27-5.13,5.27-5.13,0,0,0-.01-.01-.02.1-.27.38-1.12.54-2.26.2-1.46.2-3.37-.68-5.13ZM140.51,70.33c-.96-1.96-.76-4.19-.46-5.59-.24,1.49-.31,3.58.64,5.46.54,1.09,1.35,1.97,2.18,2.68-.13-.04-.26-.09-.39-.15-.78-.64-1.48-1.43-1.96-2.4ZM133.53,76.82c.78-1.05,2.76-3.41,5.45-3.78.02-.01.03-.01.05-.01.02-.01.04-.01.08,0-.04,0-.09,0-.13.01-.05.01-.1.02-.14.03-2.42.58-4.11,2.57-5.02,3.95-.11-.07-.2-.13-.29-.2ZM145.71,75.46c-.66,1.12-2.17,3.28-4.42,4.06-.03.01-.05.02-.09.03-.12.03-.24.08-.37.1,0,.01-.01.01-.01.01h-.02c-.13.03-.26.07-.39.08-.05.01-.12.02-.19.03h-.1s-.08.01-.1.01c-.01,0-.02.01-.03,0-.16.02-.32.03-.47.03-.07,0-.13.01-.19,0-.1-.01-.2-.01-.32-.01-.11,0-.21-.01-.32-.02h-.02c-.25-.02-.5-.07-.76-.11t-.02-.01c-.19-.03-.36-.08-.53-.12-.19-.04-.36-.09-.54-.16-1.18-.36-2.2-.89-2.78-1.23.01-.02.02-.03.04-.06h0c.1-.19.22-.4.38-.64.26-.4.61-.86,1.03-1.33.08-.09.16-.19.25-.28.84-.89,1.95-1.72,3.3-2.04.87-.2,1.83-.2,2.89,0,.43.09.84.2,1.24.34.02.01.03.01.05.02.82.26,1.55.61,2.09.9t.01.01c.18.09.33.17.45.25-.02.04-.05.09-.09.15ZM145.57,74.32h.01s.07.03.1.06c-.04-.01-.07-.02-.11-.06ZM146.2,74.76c-.14-.11-.31-.22-.5-.36.2.1.37.2.53.29-.02.02-.02.04-.02.07ZM146.42,73.79h0c-.04.18-.08.33-.11.45-.19-.09-.41-.19-.65-.31-.19-.1-.38-.21-.6-.34-1.27-.74-2.88-1.94-3.73-3.65-.98-1.96-.77-4.18-.47-5.58.05-.26.11-.48.16-.67v-.04c1.24.56,3.82,1.95,4.98,4.3.89,1.77.8,3.74.56,5.13-.04.26-.09.5-.14.72Z" />
                <path class="cls-1"
                    d="M44.64,60.23c.07.09.14.16.23.23-.08.07-.16.14-.23.23-.07-.09-.14-.16-.23-.23.08-.07.16-.14.23-.23M44.65,64.97l.16.05c.26.08.43.31.43.58s-.17.5-.43.58l-.16.05-.16-.05c-.26-.08-.43-.31-.43-.58s.17-.5.43-.58l.16-.05M44.65,69.44l.16.05c.26.08.43.31.43.58s-.17.5-.43.58l-.16.05-.16-.05c-.26-.08-.43-.31-.43-.58s.17-.5.43-.58l.16-.05M44.64,76.64c.23.47.54.78.93.99-.42.22-.71.54-.93.99-.21-.45-.51-.77-.93-.99.39-.21.7-.52.93-.99M44.65,54.73c-.18,0-.33.15-.33.34v4.64c-.21.41-.56.58-1.21.66-.11.01-.11.17,0,.19.65.09,1.01.26,1.21.66v3.27c-.47.15-.82.58-.82,1.11s.35.96.82,1.11v2.26c-.47.15-.82.58-.82,1.11s.35.96.82,1.11v4.72c-.28,1.04-.83,1.41-2.04,1.58-.17.02-.17.28,0,.31,1.59.23,2.04.78,2.23,2.73,0,.09.07.13.13.13s.12-.04.13-.13c.19-1.95.64-2.5,2.23-2.73.17-.02.17-.28,0-.31-1.18-.17-1.74-.52-2.02-1.5v-4.8c.47-.15.82-.58.82-1.11s-.35-.96-.82-1.11v-2.26c.47-.15.82-.58.82-1.11s-.35-.96-.82-1.11v-3.3c.21-.38.56-.54,1.19-.63.11-.01.11-.17,0-.19-.63-.09-.98-.25-1.19-.63v-4.67c0-.19-.15-.34-.33-.34h0Z" />
                <path class="cls-1"
                    d="M251.51,70.75h0,0M251.51,75.7l.12.1s0,0,.01,0c0,0,0,0-.01,0l-.12.1-.12-.1s0,0-.01,0c0,0,0,0,.01,0l.12-.1M251.51,79.89l.22.09c.25.09.4.32.4.58s-.16.49-.4.58l-.22.09-.22-.09c-.25-.09-.4-.32-.4-.58s.16-.49.4-.58l.22-.09M251.51,84.79l.03.07c.14.29.34.49.59.62-.25.13-.45.33-.59.62l-.03.07v-.18l-.06-.09c-.13-.18-.28-.31-.47-.42.2-.11.35-.24.47-.42l.06-.09v-.18M251.51,90.08l.06.09c.1.15.21.26.36.36-.18.11-.31.25-.41.43-.1-.19-.24-.33-.41-.43.14-.09.26-.21.36-.36l.06-.09M251.51,70.41c-.19,0-.35.16-.35.35v4.79c-.16.14-.41.2-.8.24-.05,0-.05.06,0,.07.4.03.64.1.8.24v3.56c-.37.14-.63.49-.63.9s.26.76.63.9v3.41c-.23.32-.59.47-1.26.54-.11.01-.11.14,0,.15.67.07,1.03.22,1.26.54v3.87c-.18.28-.47.41-.97.48-.09.01-.09.14,0,.15.88.12,1.14.4,1.24,1.44,0,.04.04.07.07.07s.07-.02.07-.07c.11-1.04.36-1.32,1.24-1.44.09-.01.09-.14,0-.15-.5-.07-.78-.2-.97-.48v-3.72c.21-.44.59-.61,1.36-.7.11-.01.11-.14,0-.15-.77-.08-1.15-.26-1.36-.7v-3.26c.37-.14.63-.49.63-.9s-.26-.76-.63-.9v-3.56c.16-.14.41-.2.8-.24.05,0,.05-.06,0-.07-.4-.03-.64-.1-.8-.24v-4.79c0-.19-.16-.35-.35-.35h0Z" />
                <path class="cls-1"
                    d="M192.82,49.55c.1.19.24.33.42.42-.18.1-.32.23-.42.42-.1-.19-.24-.33-.42-.42.18-.1.32-.23.42-.42M192.82,53.05c.1.19.24.33.42.42-.18.1-.32.23-.42.42-.1-.19-.24-.33-.42-.42.18-.1.32-.23.42-.42M192.82,62.19c.28,1.49.89,2.14,2.37,2.44-1.46.29-2.08.9-2.37,2.39-.29-1.5-.9-2.1-2.37-2.39,1.47-.3,2.08-.95,2.36-2.44M192.82,46.06c-.09,0-.16.07-.16.16v2.98c-.16.52-.5.67-1.34.73-.05,0-.05.07,0,.08.84.06,1.18.21,1.34.73v1.97c-.16.52-.5.67-1.34.73-.05,0-.05.07,0,.08.84.07,1.18.21,1.34.73v7.1c-.26,2.4-.9,2.95-3.47,3.17-.14.01-.14.21,0,.22,2.78.24,3.31.84,3.53,3.77,0,.06.05.09.1.09s.09-.03.1-.09c.22-2.94.75-3.54,3.53-3.77.14-.01.14-.21,0-.22-2.57-.22-3.21-.77-3.47-3.17v-7.1c.16-.52.5-.67,1.34-.73.05,0,.05-.07,0-.08-.84-.07-1.18-.21-1.34-.73v-1.97c.16-.52.5-.67,1.34-.73.05,0,.05-.07,0-.08-.84-.06-1.18-.21-1.34-.73v-2.98c0-.09-.07-.16-.16-.16h0Z" />
            </svg>
        </div>
        <nav>
            <ul>
                <li>
                    <a href="/admin/dashboard.php"><i class='bx bxs-dashboard'></i> <span>Dashboard</span></a>
                </li>
                <li class="active">
                    <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            viewBox="0 0 24 24">
                            <!--Boxicons v3.0.6 https://boxicons.com | License  https://docs.boxicons.com/free-->
                            <path
                                d="m21.45 11.11-3-1.5-2.68-1.34-.03-.03-1.34-2.68-1.5-3c-.34-.68-1.45-.68-1.79 0l-1.5 3-1.34 2.68-.03.03-2.68 1.34-3 1.5c-.34.17-.55.52-.55.89s.21.72.55.89l3 1.5 2.68 1.34.03.03 1.34 2.68 1.5 3c.17.34.52.55.89.55s.72-.21.89-.55l1.5-3 1.34-2.68.03-.03 2.68-1.34 3-1.5c.34-.17.55-.52.55-.89s-.21-.72-.55-.89ZM19.5 1.5l-.94 2.06-2.06.94 2.06.94.94 2.06.94-2.06 2.06-.94-2.06-.94z">
                            </path>
                        </svg> </i> <span>Items/Catalog</span></a>
                </li>
                <li>
                    <a href="/admin/customers.php"><i class='bx bxs-user-circle'></i> <span>Customers</span></a>
                </li>
                <li>
                    <a href="/admin/orders.php"><i class='bx bxs-shopping-bags'></i> <span>Orders</span></a>
                </li>
                <li>
                    <a href="/admin/designers.php"><i class='bx bxs-palette'></i> <span>Designers</span></a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header>
            <h2>Items/Catalog Management</h2>
            <div class="user-actions">
                <span>Admin</span>
                <a href="/admin/logout.php" class="logout"><i class='bx bx-log-out-circle'></i> Log Out</a>
            </div>
        </header>

        <!-- ALERTS -->
        <?php if (isset($success_msg)): ?>
        <div class="alert alert-success">
            <i class='bx bx-check-circle'></i>
            <?php echo htmlspecialchars($success_msg); ?>
        </div>
        <?php endif; ?>
        <?php if (isset($error_msg)): ?>
        <div class="alert alert-error">
            <i class='bx bx-x-circle'></i>
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
        <?php endif; ?>

        <div class="catalog-container">
            <!-- STATISTICS CARDS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label"><i class='bx bxs-package'></i> Total Products</div>
                    <div class="stat-value"><?php echo $inv_stats['total_products']; ?></div>
                    <div class="stat-change">Active items in catalog</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label"><i class='bx bxs-cube'></i> Total Stock</div>
                    <div class="stat-value"><?php echo number_format($inv_stats['total_stock']); ?></div>
                    <div class="stat-change">Units across all products</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label"><i class='bx bx-dollar'></i> Average Price</div>
                    <div class="stat-value">RM <?php echo number_format($inv_stats['avg_price'], 2); ?></div>
                    <div class="stat-change">Min: $<?php echo number_format($inv_stats['min_price'], 2); ?> | Max:
                        $<?php echo number_format($inv_stats['max_price'], 2); ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-label"><i class='bx bx-alarm-exclamation'></i> Low Stock Items</div>
                    <div class="stat-value"><?php echo count($low_stock); ?></div>
                    <div class="stat-change">Items with ≤15 units</div>
                </div>
            </div>

            <!-- LOW STOCK ALERT -->
            <?php if (!empty($low_stock)): ?>
            <div class="low-stock-list">
                <h4><i class='bx bx-alarm-exclamation'></i> Low Stock Alert</h4>
                <?php foreach ($low_stock as $item): ?>
                <div class="stock-item">
                    <span><?php echo htmlspecialchars($item['ITEM_NAME']); ?></span>
                    <span class="stock-status stock-low"><?php echo $item['ITEM_STOCK']; ?> units</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- FILTERS & ACTIONS -->
            <div class="filters-section">
                <form method="GET"
                    style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; width: 100%;">
                    <div class="filter-group">
                        <label>Search Products</label>
                        <input type="text" name="search" placeholder="Name or description..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">All Categories</option>
                            <option value="Necklaces" <?php echo $category_filter === 'Necklaces' ? 'selected' : ''; ?>>
                                Necklaces</option>
                            <option value="Earrings" <?php echo $category_filter === 'Earrings' ? 'selected' : ''; ?>>
                                Earrings</option>
                            <option value="Bracelets" <?php echo $category_filter === 'Bracelets' ? 'selected' : ''; ?>>
                                Bracelets</option>
                            <option value="Rings" <?php echo $category_filter === 'Rings' ? 'selected' : ''; ?>>Rings
                            </option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Sort By</label>
                        <select name="sort">
                            <option value="item_id_desc" <?php echo $sort_by === 'item_id_desc' ? 'selected' : ''; ?>>
                                Newest First</option>
                            <option value="name_asc" <?php echo $sort_by === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)
                            </option>
                            <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price
                                (Low to High)</option>
                            <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price
                                (High to Low)</option>
                            <option value="stock_low" <?php echo $sort_by === 'stock_low' ? 'selected' : ''; ?>>Stock
                                (Low First)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-filter">
                        <i class='bx bx-search'></i> Filter
                    </button>
                </form>

                <button class="btn-add-product" onclick="openAddProductModal()">
                    <i class='bx bx-plus'></i> Add Product
                </button>
            </div>

            <!-- PRODUCTS TABLE -->
            <div class="products-table">
                <div class="table-header">
                    <h3>Product Inventory</h3>
                    <span style="font-size: 0.9rem; color: #6b7280;">Showing
                        <?php echo ($page - 1) * $items_per_page + 1; ?> -
                        <?php echo min($page * $items_per_page, $total_items); ?> of <?php echo $total_items; ?>
                        products</span>
                </div>

                <div class="products-list">
                    <div class="product-row" style="background: #f9fafb; font-weight: 600; position: sticky; top: 0;">
                        <div></div>
                        <div>Product Name</div>
                        <div>Category</div>
                        <div>Price</div>
                        <div>Stock</div>
                        <div>Designer</div>
                        <div>Actions</div>
                    </div>

                    <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                    <div class="product-row">
                        <div class="product-image">
                            <?php if ($item['ITEM_IMAGE']): ?>
                            <img src="<?php echo htmlspecialchars($item['ITEM_IMAGE']); ?>"
                                alt="<?php echo htmlspecialchars($item['ITEM_NAME']); ?>">
                            <?php else: ?>
                            <i class='bx bx-image-alt' style="font-size: 1.8rem; color: #ccc;"></i>
                            <?php endif; ?>
                        </div>

                        <div class="product-name"><?php echo htmlspecialchars($item['ITEM_NAME']); ?></div>

                        <div>
                            <span class="category-badge"><?php echo htmlspecialchars($item['ITEM_CATEGORY']); ?></span>
                        </div>

                        <div>RM <?php echo number_format($item['ITEM_PRICE'], 2); ?></div>

                        <div>
                            <span class="stock-status <?php
                                                                echo $item['ITEM_STOCK'] > 30 ? 'stock-high' : ($item['ITEM_STOCK'] > 15 ? 'stock-medium' : 'stock-low');
                                                                ?>">
                                <?php echo $item['ITEM_STOCK']; ?>
                            </span>
                        </div>

                        <div><?php echo htmlspecialchars($item['DESIGNER_NAME']); ?></div>

                        <div class="action-buttons">
                            <button class="btn-icon btn-edit" title="Edit"
                                onclick="openEditProductModal(<?php echo $item['ITEM_ID']; ?>)">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn-icon btn-delete" title="Delete"
                                onclick="deleteProduct(<?php echo $item['ITEM_ID']; ?>, '<?php echo htmlspecialchars($item['ITEM_NAME']); ?>')">
                                <i class='bx bx-trash'></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div style="padding: 40px 20px; text-align: center; color: #6b7280;">
                        <i class='bx bx-inbox' style="font-size: 3rem; display: block; margin-bottom: 10px;"></i>
                        <p>No products found. Try adjusting your filters or add a new product.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PAGINATION -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a
                    href="?page=1&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&sort=<?php echo urlencode($sort_by); ?>">First</a>
                <a
                    href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&sort=<?php echo urlencode($sort_by); ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i === $page): ?>
                <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                <a
                    href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&sort=<?php echo urlencode($sort_by); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <a
                    href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&sort=<?php echo urlencode($sort_by); ?>">Next</a>
                <a
                    href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&sort=<?php echo urlencode($sort_by); ?>">Last</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- ADD/EDIT PRODUCT MODAL -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Product</h2>
                <button class="btn-close" onclick="closeProductModal()">&times;</button>
            </div>

            <form id="productForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add_product">
                <input type="hidden" name="item_id" id="itemId" value="">

                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="item_name" id="itemName" required>
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="item_description" id="itemDescription" required></textarea>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="item_category" id="itemCategory" required>
                        <option value="">Select Category</option>
                        <option value="Necklaces">Necklaces</option>
                        <option value="Earrings">Earrings</option>
                        <option value="Bracelets">Bracelets</option>
                        <option value="Rings">Rings</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Price *</label>
                        <input type="number" name="item_price" id="itemPrice" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="item_stock" id="itemStock" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Material *</label>
                    <input type="text" name="item_material" id="itemMaterial" placeholder="e.g., 925 Sterling Silver"
                        required>
                </div>

                <div class="form-group">
                    <label>Designer *</label>
                    <select name="designer_id" id="designerId" required>
                        <option value="">Select Designer</option>
                        <?php foreach ($designers as $designer): ?>
                        <option value="<?php echo $designer['DESIGNER_ID']; ?>">
                            <?php echo htmlspecialchars($designer['DESIGNER_NAME']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Product Image (JPG, PNG, max 5MB)</label>
                    <input type="file" name="item_image" id="itemImage" accept=".jpg,.jpeg,.png">
                    <small style="color: #6b7280; margin-top: 5px; display: block;">Optional. Upload a product image for
                        better presentation.</small>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeProductModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const productModal = document.getElementById('productModal');
    const productForm = document.getElementById('productForm');

    function openAddProductModal() {
        document.getElementById('modalTitle').textContent = 'Add New Product';
        document.getElementById('formAction').value = 'add_product';
        document.getElementById('itemId').value = '';
        productForm.reset();
        productModal.classList.add('active');
    }

    function openEditProductModal(itemId) {
        // Fetch product data via AJAX
        fetch(`/admin/api/get-product.php?item_id=${itemId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalTitle').textContent = 'Edit Product';
                document.getElementById('formAction').value = 'edit_product';
                document.getElementById('itemId').value = data.ITEM_ID;
                document.getElementById('itemName').value = data.ITEM_NAME;
                document.getElementById('itemDescription').value = data.ITEM_DESCRIPTION;
                document.getElementById('itemCategory').value = data.ITEM_CATEGORY;
                document.getElementById('itemPrice').value = data.ITEM_PRICE;
                document.getElementById('itemStock').value = data.ITEM_STOCK;
                document.getElementById('itemMaterial').value = data.ITEM_MATERIAL;
                document.getElementById('designerId').value = data.DESIGNER_ID;
                productModal.classList.add('active');
            })
            .catch(error => alert('Error loading product: ' + error));
    }

    function closeProductModal() {
        productModal.classList.remove('active');
    }

    function deleteProduct(itemId, itemName) {
        if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="item_id" value="${itemId}">
                `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Close modal when clicking outside
    productModal.addEventListener('click', (e) => {
        if (e.target === productModal) {
            closeProductModal();
        }
    });

    // Auto-close alerts after 4 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    });
    </script>
</body>

</html>