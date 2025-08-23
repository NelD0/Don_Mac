<?php
session_start();
include 'connect.php'; // Database connection

$cart = isset($_SESSION["cart"]) ? $_SESSION["cart"] : [];

$totalPrice = 0;
foreach ($cart as $item) {
    $totalPrice += $item["price"] * $item["qty"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm_order"])) {
    $payment = floatval($_POST["payment"] ?? 0);

    if ($payment < $totalPrice) {
        echo "insufficient";
        exit();
    }

    $_SESSION["order_receipt"] = $cart;
    $_SESSION["order_total"] = $totalPrice;
    $_SESSION["payment_amount"] = $payment;
    $_SESSION["change_due"] = $payment - $totalPrice;

    $today = date('Y-m-d H:i:s');
    foreach ($cart as $item) {
        $productId = $item["id"];
        $quantity = $item["qty"];

        $stmt = $conn->prepare("INSERT INTO sales (product_id, quantity, sale_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $productId, $quantity, $today);
        $stmt->execute();
        $stmt->close();
    }

    $_SESSION["cart"] = [];
    echo "success";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="icon" href="admin/img/images.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
            color: #f1f1f1;
            font-family: 'Segoe UI', sans-serif;
        }
        
.checkout-btn {
    background: #0069d9;
    border: none;
    padding: 15px 30px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 15px;
    margin-top: 20px;
}

.checkout-btn:hover {
    background: #0056b3;
}

.checkout ul {
    font-size: 25px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 5px;
    margin: auto;
    width: 50%;
    padding: 15px 0;
    border-bottom: 1px solid #444;
}

a:hover, button:hover {
    transition: background-color 0.3s ease-in-out;
}

button:focus {
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.8);
}
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:hsl(280, 52.50%, 23.10%);">
    <div class="container">
        <a class="navbar-brand" href="#">Checkout</a>
        <div class="d-flex">
            <a href="index2.php" class="btn btn-outline-light me-2">Home</a>
            <a href="cart.php" class="btn btn-outline-light me-2">Cart</a>
        </div>
    </div>
</nav>
    <div class="container my-4">
        <section class="checkout">
            <h2>Order Summary</h2>
            <?php if (!empty($cart)): ?>
                <ul>
                    <?php foreach ($cart as $item): ?>
                        <li>
                            <?= htmlspecialchars($item["name"]) . " - Php" . number_format($item["price"], 2) . " x " . $item["qty"]; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <h3>Total: Php<?= number_format($totalPrice, 2); ?></h3>

                <label for="payment">Payment Amount (Php):</label>
                <input type="number" id="payment" min="<?= $totalPrice ?>" step="0.01" placeholder="Enter payment amount" required>

                <br><br>
                <button onclick="confirmOrder()" class="checkout-btn">Confirm Order</button>
            <?php else: ?>
                <p>Your cart is empty. <a href="index2.php">Go back to menu</a></p>
            <?php endif; ?>
        </section>
    </div>

    <script>
    function confirmOrder() {
        const payment = parseFloat(document.getElementById("payment").value);
        const total = <?= $totalPrice ?>;

        if (isNaN(payment) || payment < total) {
            Swal.fire("Insufficient Payment", "Please enter a payment amount that covers the total.", "warning");
            return;
        }

        Swal.fire({
            title: "Confirm Order?",
            text: `You are paying Php${payment.toFixed(2)} for a total of Php${total.toFixed(2)}.`,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, place order!"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("checkout.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "confirm_order=true&payment=" + encodeURIComponent(payment)
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === "success") {
                        Swal.fire(
                            "Order Confirmed!",
                            "Your order has been placed successfully.",
                            "success"
                        ).then(() => {
                            window.location.href = "receipt.php";
                        });
                    } else if (data.trim() === "insufficient") {
                        Swal.fire("Insufficient Payment", "The amount is not enough to cover the order.", "error");
                    } else {
                        Swal.fire("Error", "There was an issue confirming your order.", "error");
                    }
                });
            }
        });
    }
    </script>
</body>
</html>
