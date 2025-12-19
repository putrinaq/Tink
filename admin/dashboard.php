<?php
require_once '../config.php';
// --- 1. FETCH DATA FOR WIDGETS ---

// KPI 1: Total Revenue (Successful payments)
$stmt = $pdo->query("SELECT SUM(PAYMENT_AMOUNT) as total FROM PAYMENT WHERE PAYMENT_STATUS = 'successful'");
$revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// KPI 2: Total Orders
$stmt = $pdo->query("SELECT COUNT(*) as total FROM `ORDER`");
$totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// KPI 3: Total Customers
$stmt = $pdo->query("SELECT COUNT(*) as total FROM CUSTOMER");
$totalCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// --- 2. FETCH DATA FOR TABLE (Recent Orders) ---
// We join ORDER -> CART -> CUSTOMER to get names
$sql = "SELECT 
            o.ORDER_ID, 
            c.CUSTOMER_NAME, 
            c.CUSTOMER_EMAIL, 
            o.ORDER_TOTALAMOUNT, 
            o.ORDER_STATUS,
            o.ORDER_DATE
        FROM `ORDER` o
        JOIN CART ca ON o.CART_ID = ca.CART_ID
        JOIN CUSTOMER c ON ca.CUSTOMER_ID = c.CUSTOMER_ID
        ORDER BY o.ORDER_DATE DESC 
        LIMIT 5";
$recentOrders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tink Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/brands/boxicons-brands.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>

<body>

    <aside class="sidebar">
        <div class="logo">
            <h1>Tink</h1>
        </div>
        <nav>
            <ul>
                <li class="active"><a href="#">Dashboard</a></li>
                <li><a href="#">Items / Catalog</a></li>
                <li><a href="#">Customers</a></li>
                <li><a href="#">Orders</a></li>
                <li><a href="#">Designers</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header>
            <h2>Dashboard</h2>
            <div class="user-actions">
                <span>Admin</span>
                <a href="#" class="logout">Log Out</a>
            </div>
        </header>

        <div class="kpi-grid">
            <div class="card">
                <div class="card-header">
                    <h3>Total Revenue</h3>
                    <span class="more-options">...</span>
                </div>
                <div class="card-body">
                    <div class="number">$<?php echo number_format($revenue, 2); ?></div>
                    <div class="trend positive">+12% ↗</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Total Orders</h3>
                    <span class="more-options">...</span>
                </div>
                <div class="card-body">
                    <div class="number"><?php echo $totalOrders; ?></div>
                    <div class="trend positive">+3 ↗</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Total Customers</h3>
                    <span class="more-options">...</span>
                </div>
                <div class="card-body">
                    <div class="number"><?php echo $totalCustomers; ?></div>
                    <div class="trend positive">+4% ↗</div>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="card chart-container">
                <h3>Revenue Overview (2025)</h3>
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="card chart-container">
                <h3>Order Status</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <div class="table-section">
            <h3>Recent Orders</h3>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['ORDER_ID']; ?></td>
                            <td><?php echo htmlspecialchars($order['CUSTOMER_NAME']); ?></td>
                            <td><?php echo htmlspecialchars($order['CUSTOMER_EMAIL']); ?></td>
                            <td>$<?php echo number_format($order['ORDER_TOTALAMOUNT'], 2); ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($order['ORDER_STATUS']); ?>">
                                    <?php echo ucfirst($order['ORDER_STATUS']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Dummy data for visual effect (you can hook this to PHP similarly later)
        const ctx1 = document.getElementById('revenueChart');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [1200, 1900, 3000, 5000, 2000, 3000],
                    borderColor: '#ff9f43',
                    backgroundColor: 'rgba(255, 159, 67, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        const ctx2 = document.getElementById('statusChart');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Delivered', 'Pending', 'Cancelled'],
                datasets: [{
                    data: [12, 5, 2],
                    backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c']
                }]
            }
        });
    </script>
</body>

</html>