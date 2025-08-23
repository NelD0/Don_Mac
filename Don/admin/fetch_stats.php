<?php
include './../connect.php';

// Initialize default response
$response = [
    "totalUsers" => 0,
    "totalProducts" => 0,
    "totalCartItems" => 0
];

// Check if database connection is valid
if (!$conn) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Get total users
$userQuery = $conn->query("SELECT COUNT(*) as count FROM user");
if ($userQuery) {
    $response["totalUsers"] = $userQuery->fetch_assoc()["count"] ?? 0;
}

// Get total products
$productQuery = $conn->query("SELECT COUNT(*) as count FROM products");
if ($productQuery) {
    $response["totalProducts"] = $productQuery->fetch_assoc()["count"] ?? 0;
}

// Get total cart items (sum of quantities)
$cartQuery = $conn->query("SELECT COALESCE(SUM(qty), 0) as count FROM cart");
if ($cartQuery) {
    $response["totalCartItems"] = $cartQuery->fetch_assoc()["count"];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
