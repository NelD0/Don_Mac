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
    <style>
    body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #1b1b41, #0a1f44);
    display: flex;
    flex-direction: column;
    color: white;
}

.header {
    font-family: 'Times New Roman', Times, serif;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgb(9, 9, 37), rgb(19, 31, 54));
    padding: 8px 6px;
    position: relative;
}

.header h2 {
    margin-right: 69%;
}

.logo {
    margin-left: 12%;
    background: white;
    border-radius: 50% 50%;
    height: 50px;
    width: 50px;
    position: relative;
    overflow: hidden;
}

.logo>img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.dashboard-container {
    display: flex;
    height: 100vh;
}

.sidebar {
    width: 15%;
    min-width: 200px;
    background: linear-gradient(135deg, #0a1f44, #1f3b73);
    color: white;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 20px;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar li {
    margin-bottom: 10px;
}

.sidebar a {
    text-decoration: none;
    background: linear-gradient(135deg, #1f3b73, #2979ff);
    color: white;
    padding: 10px;
    border-radius: 5px;
    display: block;
    text-align: center;
    transition: 0.3s ease;
}

.sidebar a:hover {
    background: linear-gradient(135deg, #2979ff, #1f3b73);
}

.main-content {
    flex-grow: 1;
    padding: 30px;
    background: linear-gradient(135deg, #0a1f44, #1b1b41);
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.product-form-container, .product-list-container {
    background: linear-gradient(135deg, #222, #333);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.5);
}

.product-form-container input, .product-form-container button {
    padding: 10px;
    font-size: 1rem;
    border: none;
    border-radius: 5px;
    margin-top: 10px;
}

.product-form-container button {
    background: linear-gradient(135deg, #1abc9c, #16a085);
    color: white;
    cursor: pointer;
}

.product-form-container button:hover {
    background: linear-gradient(135deg, #16a085, #1abc9c);
}

table {
    width: 100%;
    border-collapse: collapse;
    background: linear-gradient(135deg, #222, #333);
    color: white;
    border-radius: 10px;
    overflow: hidden;
}

table th, table td {
    padding: 15px;
    text-align: left;
}

table th {
    background: #1f3b73;
}

table tr:hover {
    background: #333;
    transition: 0.3s ease;
}

.product-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.edit-button, .delete-button {
    padding: 5px 10px;
    border-radius: 5px;
    color: white;
    border: none;
    cursor: pointer;
}

.edit-button {
    background: #2980b9;
}

.delete-button {
    background: #e74c3c;
}

.edit-button:hover {
    background: #3498db;
}

.delete-button:hover {
    background: #c0392b;
}
.charts-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 20px;
}

canvas {
    max-width: 600px;
    max-height: 300px;
    background: white;
    padding: 10px;
    border-radius: 8px;
}


        </style>
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
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $products->fetch_assoc()) : ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td>
                        <img src="../img/<?= !empty($row['img_path']) ? htmlspecialchars($row['img_path']) : 'default.png'; ?>" 
                             alt="Product Image" class="product-img">
                    </td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['price']); ?></td>
                    <td><?= htmlspecialchars($row['qty']); ?></td>
                    <td class="action-buttons">
                        <button onclick="editProduct(<?= $row['id']; ?>)" class="edit-button">Edit</button>
                        <button class="delete-button" onclick="confirmDelete(<?= $row['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>

<script>
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

document.getElementById("product_id").addEventListener("change", function() {
    let id = this.value.trim();
    if (id) {
        fetch("product.php?id=" + id)
        .then(response => response.json())
        .then(data => {
            if (data.id) {
                // Auto-fill values (but DO NOT disable fields anymore)
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


// Redirect to edit page
function editProduct(id) {
    window.location.href = "edit_product.php?id=" + id;
}

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
