<?php
session_start();
include 'connect.php'; // Database connection

// --- Fetch dashboard data ---
function fetchDashboardData($conn) {
    // Total users
    $totalUsers = fetchSingleValue($conn, "SELECT COUNT(*) AS total FROM user");
    
    // Total products
    $totalProducts = fetchSingleValue($conn, "SELECT COUNT(*) AS total FROM product_list");
    
    // Cart items
    $check_qty_column = $conn->query("SHOW COLUMNS FROM cart LIKE 'qty'");
    if ($check_qty_column->num_rows > 0) {
        $totalCartItems = fetchSingleValue($conn, "SELECT COALESCE(SUM(qty), 0) AS total FROM cart");
    } else {
        $totalCartItems = isset($_SESSION["cart"])
            ? array_sum(array_column($_SESSION["cart"], "qty"))
            : fetchSingleValue($conn, "SELECT COUNT(*) AS total FROM cart");
    }

    // Monthly sales
    $salesData = array_fill(0, 12, 0);
    $salesQuery = "SELECT MONTH(sale_date) AS month, COUNT(id) AS total_sales FROM sales GROUP BY MONTH(sale_date)";
    $salesResult = $conn->query($salesQuery);
    if ($salesResult) {
        while ($row = $salesResult->fetch_assoc()) {
            $salesData[$row['month'] - 1] = (int)$row['total_sales'];
        }
    }

    // Best selling products
    $bestSalesNames = [];
    $bestSalesQuantities = [];
    $bestSalesQuery = "
        SELECT p.name AS product_name, SUM(s.quantity) AS total_quantity
        FROM sales s
        JOIN product_list p ON s.product_id = p.id
        GROUP BY s.product_id
        ORDER BY total_quantity DESC
        LIMIT 5
    ";
    $bestSalesResult = $conn->query($bestSalesQuery);
    if ($bestSalesResult) {
        while ($row = $bestSalesResult->fetch_assoc()) {
            $bestSalesNames[] = $row['product_name'];
            $bestSalesQuantities[] = (int)$row['total_quantity'];
        }
    }

    return [
        'totalUsers' => $totalUsers,
        'totalProducts' => $totalProducts,
        'totalCartItems' => $totalCartItems,
        'salesData' => $salesData,
        'bestSalesNames' => $bestSalesNames,
        'bestSalesQuantities' => $bestSalesQuantities,
    ];
}

// Utility function to fetch single value
function fetchSingleValue($conn, $sql) {
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row ? $row['total'] : 0;
    }
    return 0;
}

// --- Cart and order logic ---
$cart = isset($_SESSION["cart"]) ? $_SESSION["cart"] : [];

$totalPrice = 0;
foreach ($cart as $item) {
    $totalPrice += $item["price"] * $item["qty"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm_order"])) {
    $_SESSION["order_receipt"] = $cart; // Store order for receipt
    $_SESSION["order_total"] = $totalPrice; // Store total

    $_SESSION["cart"] = []; // Clear the cart

    echo "success";
    exit();
}

// Example: If you want to use the dashboard data elsewhere
$dashboardData = fetchDashboardData($conn);
// Now you can access like: $dashboardData['totalUsers'], etc.
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const checkoutButton = document.querySelector(".checkout-btn");
            const confirmOrderButton = document.querySelector(".confirm-order-btn");

            function updateCheckoutButton() {
                if (document.querySelectorAll(".cart-item").length === 0) {
                    checkoutButton.disabled = true;
                    confirmOrderButton.disabled = true;
                } else {
                    checkoutButton.disabled = false;
                    confirmOrderButton.disabled = false;
                }
            }

            document.addEventListener("click", function (event) {
                if (event.target.classList.contains("remove-btn")) {
                    let cartItem = event.target.parentElement;
                    let itemId = event.target.getAttribute("data-id");

                    fetch("remove_item.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "id=" + itemId
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === "success") {
                            cartItem.remove();
                            updateCheckoutButton();
                        } else {
                            alert("Error removing item.");
                        }
                    });
                }
            });

            confirmOrderButton.addEventListener("click", function () {
                if (confirm("Are you sure you want to confirm your order?")) {
                    fetch("cart.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "confirm_order=true"
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === "order_confirmed") {
                            alert("Order confirmed!");
                            location.reload();
                        } else {
                            alert("Error confirming order.");
                        }
                    });
                }
            });

            updateCheckoutButton();
        });
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1> Cart</h1>
            <nav>
                <ul>
                    <li><a href="order.php">Home</a></li>
                    <li><a href="admin.php">Admin</a></li>
                </ul>
            </nav>
        </header>

        <section class="cart">
            <h2>Your Cart</h2>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="cart-item">
                        <span>' . htmlspecialchars($row["name"]) . '</span>
                        <span>Php' . number_format($row["price"], 2) . '</span>
                        <button class="remove-btn" data-id="' . $row["id"] . '">Remove</button>
                    </div>';
                }
            } else {
                echo "<p>Your cart is empty.</p>";
            }
            $conn->close();
            ?>
            <button class="checkout-btn">Proceed to Checkout</button>
            <button class="confirm-order-btn">Confirm Order</button>
        </section>
    </div>
</body>
</html>
