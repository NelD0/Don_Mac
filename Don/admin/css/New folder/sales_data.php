<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include 'connect.php';

// Function to fetch sales grouped by month
$salesData = array_fill(0, 12, 0); // Initialize array for 12 months

$salesQuery = "SELECT MONTH(sale_date) AS month, COUNT(id) AS total_sales 
               FROM sales 
               WHERE product_id IN (SELECT id FROM product_list)
               GROUP BY MONTH(sale_date)";

$salesResult = $conn->query($salesQuery);
if ($salesResult) {
    while ($row = $salesResult->fetch_assoc()) {
        $salesData[$row['month'] - 1] = (int)$row['total_sales']; // Adjust for zero-based index
    }
}

echo json_encode($salesData);
?>
