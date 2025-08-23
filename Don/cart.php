<?php
session_start();
include 'connect.php'; // Database connection

// Handle Add, Remove, and Count actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"];

    // Add item to cart
    if ($action === "add") {
        $name = $_POST["name"];
        $price = $_POST["price"];
        $qty = (int)$_POST["qty"];
        $img = $_POST["img"];

        $stmt = $conn->prepare("SELECT id, qty FROM product_list WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && $row["qty"] >= $qty) {
            $productId = $row["id"];
            $newQty = $row["qty"] - $qty;

            $updateStmt = $conn->prepare("UPDATE product_list SET qty = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $newQty, $productId);
            $updateStmt->execute();

            if (!isset($_SESSION["cart"])) {
                $_SESSION["cart"] = [];
            }

            $found = false;
            foreach ($_SESSION["cart"] as &$item) {
                if ($item["id"] === $productId) {
                    $item["qty"] += $qty;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $_SESSION["cart"][] = [
                    "id" => $productId,
                    "name" => $name,
                    "price" => $price,
                    "qty" => $qty,
                    "img" => $img
                ];
            }

            echo json_encode([
                "status" => "success",
                "newQty" => $newQty,
                "cartCount" => array_sum(array_column($_SESSION["cart"], "qty"))
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Not enough stock."]);
        }
        exit;
    }

    // Remove item from cart
    if ($action === "remove") {
        $id = (int)$_POST["id"];
        if (isset($_SESSION["cart"])) {
            foreach ($_SESSION["cart"] as $key => $item) {
                if ($item["id"] === $id) {
                    $updateStock = $conn->prepare("UPDATE product_list SET qty = qty + ? WHERE id = ?");
                    $updateStock->bind_param("ii", $item["qty"], $id);
                    $updateStock->execute();
                    unset($_SESSION["cart"][$key]);
                    $_SESSION["cart"] = array_values($_SESSION["cart"]);
                    echo json_encode([
                        "status" => "success",
                        "cartCount" => array_sum(array_column($_SESSION["cart"], "qty"))
                    ]);
                    exit;
                }
            }
        }
        echo json_encode(["status" => "error", "message" => "Item not found."]);
        exit;
    }

    // Count cart items
    if ($action === "count") {
        echo json_encode([
            "cartCount" => isset($_SESSION["cart"]) ? array_sum(array_column($_SESSION["cart"], "qty")) : 0
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Don Cart</title>
    <link rel="icon" href="admin/img/images.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
            color: #f1f1f1;
            font-family: 'Segoe UI', sans-serif;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #3a3a3a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #555;
            color: #fff;
        }

        h2 {
            color: #f8f8f8;
            margin-top: 20px;
        }

        .checkout-btn {
            margin-top: 20px;
        }

        .checkout-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            updateCartCount();

            document.addEventListener("click", function (event) {
                if (event.target.classList.contains("remove-btn")) {
                    let itemId = event.target.getAttribute("data-id");
                    Swal.fire({
                        title: "Are you sure?",
                        text: "Do you want to remove this item from the cart?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, remove it!",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch("cart.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: `action=remove&id=${encodeURIComponent(itemId)}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === "success") {
                                    event.target.closest(".cart-item").remove();
                                    updateCartCount();
                                    Swal.fire("Removed!", "Item has been removed from your cart.", "success");
                                } else {
                                    Swal.fire("Error", data.message, "error");
                                }
                            });
                        }
                    });
                }
            });

            function updateCartCount() {
                fetch("cart.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "action=count"
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("cart-count").innerText = data.cartCount;
                });
            }
        });
    </script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:hsl(280, 52.50%, 23.10%);">
    <div class="container">
        <a class="navbar-brand" href="#">Don Cart</a>
        <div class="d-flex">
            <a href="index2.php" class="btn btn-outline-light me-2">Home</a>
            <h4><span class="text-white">Cart: <span id="cart-count">0</span></span></h4>
        </div>
    </div>
</nav>

<div class="container my-4">
    <h2>Your Cart</h2>
    <?php if (!empty($_SESSION["cart"])): ?>
        <?php foreach ($_SESSION["cart"] as $item): ?>
            <div class="cart-item">
                <span><strong><?= htmlspecialchars($item["name"]) ?></strong></span>
                <span>₱<?= number_format($item["price"], 2) ?></span>
                <span>Qty: <?= $item["qty"] ?></span>
                <button class="btn btn-danger btn-sm remove-btn" data-id="<?= $item["id"] ?>">Remove</button>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>

    <form action="checkout.php" method="POST">
        <button type="submit" class="btn btn-success checkout-btn" <?= empty($_SESSION["cart"]) ? 'disabled' : '' ?>>Proceed to Checkout</button>
    </form>
</div>
</body>
</html>
