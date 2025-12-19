<?php
require_once '../config.php';

/* ===============================
   BASIC ADMIN PROTECTION
================================ */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ===============================
   DASHBOARD STATISTICS
================================ */

// Total products
$q = $conn->query("SELECT COUNT(*) AS total FROM products");
$totalProducts = $q->fetch_assoc()['total'];

// Total customers
$q = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'");
$totalCustomers = $q->fetch_assoc()['total'];

// Total orders
$q = $conn->query("SELECT COUNT(*) AS total FROM orders");
$totalOrders = $q->fetch_assoc()['total'];

// Total revenue
$q = $conn->query("
    SELECT SUM(total_amount) AS revenue 
    FROM orders 
    WHERE status != 'cancelled'
");
$revenue = $q->fetch_assoc()['revenue'] ?? 0;

/* ===============================
   RECENT ORDERS
================================ */
$recentOrders = $conn->query("
    SELECT o.id, u.name, o.total_amount, o.status, o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
");

/* ===============================
   INVENTORY
================================ */
$products = $conn->query("
    SELECT p.id, p.name, p.price, p.stock, c.name AS category
    FROM products p
    JOIN categories c ON p.category_id = c.id
");

/* ===============================
   TOP CUSTOMERS
================================ */
$topCustomers = $conn->query("
    SELECT u.name, u.email, COUNT(o.id) AS total_orders,
           SUM(o.total_amount) AS total_spent,
           MIN(o.created_at) AS join_date
    FROM users u
    JOIN orders o ON u.id = o.user_id
    WHERE u.role = 'customer'
    GROUP BY u.id
    ORDER BY total_spent DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>TINK Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/dashboard.css" </head>

<body>
    <div class="container">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="logo">💎 TINK</div>
            <ul class="nav-menu">
                <li><a class="active">Dashboard</a></li>
                <li><a>Catalog</a></li>
                <li><a>Orders</a></li>
                <li><a>Customers</a></li>
                <li><a>Reports</a></li>
            </ul>
        </aside>

        <!-- CONTENT -->
        <div>
            <header class="header">
                <h2>Admin Dashboard</h2>
                <form action="../actions/logout.php" method="post">
                    <button class="logout">Logout</button>
                </form>
            </header>

            <main class="main">

                <!-- STATS -->
                <div class="stats">
                    <div class="card">
                        <h3>Total Products</h3>
                        <div class="number"><?= $totalProducts ?></div>
                    </div>
                    <div class="card">
                        <h3>Total Customers</h3>
                        <div class="number"><?= $totalCustomers ?></div>
                    </div>
                    <div class="card">
                        <h3>Total Orders</h3>
                        <div class="number"><?= $totalOrders ?></div>
                    </div>
                    <div class="card">
                        <h3>Total Revenue</h3>
                        <div class="number">RM<?= number_format($revenue, 2) ?></div>
                    </div>
                </div>

                <!-- RECENT ORDERS -->
                <div class="section">
                    <h3>Recent Orders</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($o = $recentOrders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $o['id'] ?></td>
                                    <td><?= htmlspecialchars($o['name']) ?></td>
                                    <td>RM<?= number_format($o['total_amount'], 2) ?></td>
                                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                                    <td>
                                        <span class="badge <?= $o['status'] ?>">
                                            <?= ucfirst($o['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- INVENTORY -->
                <div class="section">
                    <h3>Inventory</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = $products->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $p['id'] ?></td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= $p['category'] ?></td>
                                    <td>RM<?= number_format($p['price'], 2) ?></td>
                                    <td>
                                        <span class="badge <?= $p['stock'] <= 10 ? 'low' : 'confirmed' ?>">
                                            <?= $p['stock'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- TOP CUSTOMERS -->
                <div class="section">
                    <h3>Top Customers</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($c = $topCustomers->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['name']) ?></td>
                                    <td><?= htmlspecialchars($c['email']) ?></td>
                                    <td><?= $c['total_orders'] ?></td>
                                    <td>RM<?= number_format($c['total_spent'], 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            </main>
        </div>
    </div>
</body>

</html>