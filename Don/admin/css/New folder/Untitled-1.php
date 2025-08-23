<?php
require './../connect.php'; // Database connection

// Handle product create, update, and delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $price = $_POST['price'] ?? 0;
    $qty = $_POST['qty'] ?? 0;
    $img_path = 'default.png';

    // Handle Image Upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../img/";
        $fileName = basename($_FILES['image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png'];
        $newFileName = uniqid() . "." . $fileExt;
        $targetFilePath = $targetDir . $newFileName;

        if (!in_array($fileExt, $allowedExt)) {
            echo json_encode(["status" => "error", "message" => "Invalid file type."]);
            exit;
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            echo json_encode(["status" => "error", "message" => "File is too large."]);
            exit;
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $img_path = $newFileName;
        } else {
            echo json_encode(["status" => "error", "message" => "Image upload failed."]);
            exit;
        }
    }

    if ($id) {
        // 🔥 **Modify qty to ADD to existing qty instead of replacing it**
        $sql = "UPDATE product_list SET 
                    img_path = '$img_path', 
                    name = '$name', 
                    price = '$price', 
                    qty = qty + $qty 
                WHERE id = $id";
    } else {
        // Insert new product
        $sql = "INSERT INTO product_list (img_path, name, price, qty) VALUES ('$img_path', '$name', '$price', '$qty')";
    }

    if ($conn->query($sql)) {
        $new_id = $id ? $id : $conn->insert_id;
        echo json_encode(["status" => "success", "message" => "Product saved successfully!", "id" => $new_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
    exit;
}

// Delete product
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($conn->query("DELETE FROM product_list WHERE id=$id")) {
        echo json_encode(["status" => "success", "message" => "Product deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete product."]);
    }
    exit;
}

// Fetch a single product
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM product_list WHERE id=$id");

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["status" => "error", "message" => "Product not found."]);
    }
    exit;
}

// Fetch all products
$products = $conn->query("SELECT * FROM product_list");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="icon" href="img/images.png" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="css/stylese.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="dashboard.php" class="action-button">Home</a></li>
        </ul>
    </div>

    <div class="main-content">
        <!-- Left Side: Add/Update Form -->
        <div class="product-form-container">
            <h2>Add / Update Product</h2>
            <form id="product-form" enctype="multipart/form-data">
                <input type="number" name="id" id="product_id" placeholder="Product ID">
                <input type="file" name="image" id="image" accept="image/*">
                <input type="text" name="name" id="name" placeholder="Product Name" required>
                <input type="number" step="0.01" name="price" id="price" placeholder="Price" required>
                <input type="number" name="qty" id="qty" placeholder="Quantity" required>
                <button type="submit">Save Product</button>
            </form>
        </div>

        <!-- Right Side: Product List -->
        <div class="product-list-container">
            <h2>Product List</h2>
            <table>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $products->fetch_assoc()) : ?>
                <tr>
                    <td>
                        <img src="../img/<?= !empty($row['img_path']) ? htmlspecialchars($row['img_path']) : 'default.png'; ?>" 
                             alt="Product Image" class="product-img">
                    </td>
                    <td><?= $row['name']; ?></td>
                    <td><?= $row['price']; ?></td>
                    <td><?= $row['qty']; ?></td>
                    <td>
                        <button class="edit-button" onclick="loadProduct(<?= $row['id']; ?>)">Edit</button>
                        <button class="delete-button" onclick="confirmDelete(<?= $row['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <script>
// Handle form submission
// Handle form submission
document.getElementById("product-form").addEventListener("submit", function(event) {
    event.preventDefault();
    let formData = new FormData(this);

    fetch("", { method: "POST", body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            Swal.fire({
                title: "Success!",
                text: data.message,
                icon: "success",
                confirmButtonText: "OK"
            }).then(() => {
                window.location.href = "product.php"; // ✅ Redirect after saving
            });
        } else {
            Swal.fire("Error!", data.message, "error");
        }
    })
    .catch(() => Swal.fire("Error!", "Something went wrong.", "error"));
});


// Auto-fill product details when ID is entered
document.getElementById("product_id").addEventListener("change", function() {
    let id = this.value.trim();
    if (id) {
        fetch("product.php?id=" + id)
        .then(response => response.json())
        .then(data => {
            if (data.id) {
                document.getElementById("name").value = data.name;
                document.getElementById("price").value = data.price;
                document.getElementById("qty").value = data.qty;
            } else {
                Swal.fire("Error!", "Product not found.", "error");
            }
        })
        .catch(() => Swal.fire("Error!", "Failed to fetch product details.", "error"));
    }
});


// Delete product
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will not be able to recover this product!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("?delete=" + id)
            .then(() => { window.location.reload(); });
        }
    });
}
</script>
</body>
</html>
