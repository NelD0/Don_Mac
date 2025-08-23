<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include './../connect.php';

function fetchSingleValue($conn, $sql) {
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    } else {
        error_log("Query failed or returned no results: $sql");
        return 0;
    }
}

$totalUsers = fetchSingleValue($conn, "SELECT COUNT(*) AS total FROM user");
$totalProducts = fetchSingleValue($conn, "SELECT COUNT(*) AS total FROM product_list");

$check_qty_column = $conn->query("SHOW COLUMNS FROM cart LIKE 'qty'");
if ($check_qty_column->num_rows > 0) {
    $totalCartItems = fetchSingleValue($conn, "SELECT COALESCE(SUM(qty), 0) AS total FROM cart");
} else {
    $totalCartItems = isset($_SESSION["cart"]) 
        ? array_sum(array_column($_SESSION["cart"], "qty")) 
        : fetchSingleValue($conn, "SELECT COUNT(*) AS total FROM cart");
}

$salesData = array_fill(0, 12, 0);
$salesResult = $conn->query("SELECT MONTH(sale_date) AS month, COUNT(sale_id) AS total_sales FROM sales GROUP BY MONTH(sale_date)");
if ($salesResult) {
    while ($row = $salesResult->fetch_assoc()) {
        $salesData[$row['month'] - 1] = (int)$row['total_sales'];
    }
}

$bestSalesNames = [];
$bestSalesQuantities = [];
$bestSalesResult = $conn->query("SELECT p.name AS product_name, SUM(s.quantity) AS total_quantity FROM sales s JOIN product_list p ON s.product_id = p.id GROUP BY s.product_id ORDER BY total_quantity DESC LIMIT 5");
if ($bestSalesResult) {
    while ($row = $bestSalesResult->fetch_assoc()) {
        $bestSalesNames[] = $row['product_name'];
        $bestSalesQuantities[] = (int)$row['total_quantity'];
    }
}

$monthSalesLabels = [];
$monthSalesData = [];

$currentYear = date('Y');
$currentMonth = date('m');
$daysInMonth = date('t');

for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = "$currentYear-$currentMonth-" . str_pad($day, 2, "0", STR_PAD_LEFT);
    $monthSalesLabels[] = date('M d', strtotime($date));
    $sql = "SELECT COUNT(*) AS total FROM sales WHERE DATE(sale_date) = '$date'";
    $monthSalesData[] = fetchSingleValue($conn, $sql);
}

// Total Revenue calculation
$totalRevenue = fetchSingleValue($conn, "
    SELECT SUM(s.quantity * p.price) AS total 
    FROM sales s 
    JOIN product_list p ON s.product_id = p.id
");

// Total Sales for this month calculation
$totalSalesThisMonth = fetchSingleValue($conn, "
    SELECT SUM(s.quantity * p.price) AS total 
    FROM sales s
    JOIN product_list p ON s.product_id = p.id
    WHERE MONTH(s.sale_date) = $currentMonth
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="icon" href="img/images.png" />
    <link href="css/styles.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="header">
    <div class="logo"><img src="img/images.png" alt="Logo"></div>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['name']); ?>!</h2>
</div>

<div class="dashboard-container">
    <aside class="sidebar">
        <h2>Dashboard</h2>
        <p style="font-size: 18px; font-weight: bold;">🗓️ Today: <?= date("F d, Y - l"); ?></p>
        <nav>
            <ul>
                <li><a href="product.php" class="action-button create-button">Product</a></li>
                <li><a href="users.php" class="action-button create-button">Admin</a></li>
                <li><a href="#" class="logout-button" onclick="confirmLogout()">Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
    <div class="chart-container">
        <div class="chart-box">
            <p class="chart-label">Total Products</p>
            <div class="chart-inner">
                <canvas id="productsChart"></canvas>
                <div class="chart-number"><?= $totalProducts; ?></div>
            </div>
        </div>
        <div class="chart-box">
            <p class="chart-label">Total Items in Cart</p>
            <div class="chart-inner">
                <canvas id="cartChart"></canvas>
                <div class="chart-number"><?= $totalCartItems; ?></div>
            </div>
        </div>
        <div class="chart-box">
            <p class="chart-label">Total Revenue</p>
            <div class="chart-inner">
                <canvas id="revenueChart"></canvas>
                <div class="chart-number">₱<?= number_format($totalRevenue, 2); ?></div>
            </div>
        </div>
        <div class="chart-box">
            <p class="chart-label">Total Sales (This Month)</p>
            <div class="chart-inner">
                <canvas id="salesChart"></canvas>
                <div class="chart-number">₱<?= number_format($totalSalesThisMonth, 2); ?></div>
            </div>
        </div>
    </div>

    <div class="sales-charts-flex">
        <div class="sales-chart-box">
            <p>📆 This Month's Sales (Daily)</p>
            <canvas id="monthSalesChart"></canvas>
        </div>
        <div class="sales-chart-box">
            <p>🏆 Best Sales</p>
            <canvas id="bestSalesChart"></canvas>
        </div>
        <div class="sales-chart-box">
            <p>📊 Monthly Sales</p>
            <canvas id="monthlySalesChart"></canvas>
        </div>
    </div>
    </main>
</div>

<script>
function confirmLogout() {
    Swal.fire({
        title: "Are you sure?",
        text: "You will be logged out!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#e74c3c",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, logout!"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "logout.php";
        }
    });
}

function createDoughnut(chartId, value, color) {
    new Chart(document.getElementById(chartId), {
        type: 'doughnut',
        data: {
            labels: ['Count', 'Remaining'],
            datasets: [{
                data: [value, Math.max(100 - value, 10)],
                backgroundColor: [color, '#e0e0e0'],
                borderWidth: 2
            }]
        },
        options: {
            cutout: '70%',
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });
}

createDoughnut('productsChart', <?= $totalProducts; ?>, '#8E1616');
createDoughnut('cartChart', <?= $totalCartItems; ?>, '#dc3545');
createDoughnut('revenueChart', <?= $totalRevenue; ?>, '#28a745');
createDoughnut('salesChart', <?= $totalSalesThisMonth; ?>, '#17a2b8');

new Chart(document.getElementById('monthSalesChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($monthSalesLabels); ?>,
        datasets: [{
            label: 'Daily Sales',
            data: <?= json_encode($monthSalesData); ?>,
            fill: true,
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.2)',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

new Chart(document.getElementById('bestSalesChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($bestSalesNames); ?>,
        datasets: [{
            label: 'Units Sold',
            data: <?= json_encode($bestSalesQuantities); ?>,
            backgroundColor: ['#ff6384','#36a2eb','#ffce56','#4bc0c0','#9966ff']
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

new Chart(document.getElementById('monthlySalesChart'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Monthly Sales',
            data: <?= json_encode($salesData); ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
<style>
.chart-label {
    margin-bottom: 8px;
    font-weight: bold;
    font-size: 16px;
    color: white;
    text-align: center;
}

.chart-inner {
    position: relative;
    width: 200px;
    height: 200px;
    margin: auto;
}

.chart-inner canvas {
    width: 100%;
    height: 100%;
    background: transparent;
}

.chart-number {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 18px;
    font-weight: bold;
    color: white;
    text-align: center;
}

.chart-container {
    margin-top: 10px;
    display: flex;
    justify-content: space-around;
    align-items: center;
    flex-wrap: wrap;
}

/* Small circle charts */
.chart-box {
    position: relative;
    width: 200px;
    height: 200px;
    text-align: center;
}

.chart-box canvas {
    width: 100%;
    height: 100%;
    background: transparent;
}

.chart-box p {
    margin-top: 1px;
    font-weight: bold;
}

/* Sales charts area (Today Sales, Best Sales, Monthly Sales) */
.sales-charts-flex {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
    margin-top: 30px;
    padding: 0 20px;
    max-width: 1000px;
    margin-left: auto;
    margin-right: auto;
}

/* Each chart box inside the sales section */
.sales-chart-box {
    background: white;
    border-radius: 10px;
    padding: 10px;
    flex: 1 1 calc(33.33% - 20px); /* Each takes 1/3 of the row */
    box-sizing: border-box;
    min-width: 300px;
    text-align: center;
}

.sales-chart-box p {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 12px;
    color: #2c3e50;
    border-bottom: 2px solid #ddd;
    padding-bottom: 6px;
}

/* Force same canvas size for all 3 sales charts */
.sales-chart-box canvas {
    height: 200px !important;
    width: 100% !important;
    max-width: 400px;
    margin: 0 auto;
}
</style>
</body>
</html>
