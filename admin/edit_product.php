<?php 
require './../connect.php'; // Database connection

$id = $_GET['id'] ?? null;
$product = null;

if ($id) {
    $result = $conn->query("SELECT * FROM product_list WHERE id = $id");
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "<script>alert('Product not found!'); window.location.href='product.php';</script>";
        exit;
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'] ?? 0;
    $img_path = $product['img_path']; // Keep existing image unless changed

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
        }
    }

    // Update product with quantity
    $sql = "UPDATE product_list SET  img_path='$img_path', name='$name', price='$price' WHERE id=$id";

    if ($conn->query($sql)) {
        echo json_encode(["status" => "success", "message" => "Product updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="icon" href="img/images.png" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #0a1f44 10%, #1f3b73 100%);
            color: #fff;
            margin: 0;
            flex-direction: column;
        }
        .row {
            background: linear-gradient(135deg, #1f3b73 10%, #0a1f44 100%);
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(20, 83, 134, 0.6);
            padding: 20px;
            width: 100%;
            max-width: 350px;
            text-align: center;
        }
        input, select {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #1f3b73;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            outline: none;
        }
        button {
            width: 95%;
            padding: 10px;
            background-color: #1f3b73;
            color: white;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: background 0.3s ease;
            font-size: 16px;
        }
        button:hover {
            background-color: #2979ff;
        }
    </style>
</head>
<body>
    <h2>Edit Product</h2>
    <div class="row">
        <form id="edit-product-form" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $product['id'] ?? '' ?>">
            <input type="file" name="image" accept="image/*">
            <img src="../img/<?= htmlspecialchars($product['img_path'] ?? 'default.png') ?>" width="100" alt="Product Image">
            <input type="text" name="name" value="<?= $product['name'] ?? '' ?>" placeholder="Product Name" required>
            <input type="number" step="0.01" name="price" value="<?= $product['price'] ?? '' ?>" placeholder="Price" required>
            <button type="submit">Update Product</button>
        </form>
    </div>

    <script>
    document.getElementById("edit-product-form").addEventListener("submit", function(event) {
        event.preventDefault(); 

        let formData = new FormData(this);

        fetch("", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                Swal.fire({
                    title: "Updated!",
                    text: data.message,
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "product.php"; 
                });
            } else {
                Swal.fire("Error!", data.message, "error");
            }
        })
        .catch(() => Swal.fire("Error!", "Something went wrong.", "error"));
    });
    </script>
</body>
</html>
