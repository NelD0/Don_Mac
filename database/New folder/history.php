<?php 
include 'connect.php';

// Function to display order history
function displayOrderHistory($conn) {
    $sql = "
        SELECT 
            oh.*, 
            p.name, 
            p.price, 
            p.img_path
        FROM order_history oh
        JOIN product_list p ON oh.product_id = p.id
        ORDER BY oh.order_time DESC
    ";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        echo '<table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Item Name</th>
                        <th>Price (Php)</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Date/Time</th>
                    </tr>
                </thead>
                <tbody>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td><img src="' . htmlspecialchars($row['img_path']) . '" alt="' . htmlspecialchars($row['name']) . '" width="50"></td>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . number_format($row['price'], 2) . '</td>
                    <td>' . $row['quantity'] . '</td>
                    <td>' . number_format($row['total_price'], 2) . '</td>
                    <td>' . date("F d, Y - h:i A", strtotime($row['order_time'])) . '</td>
                </tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>No orders found.</p>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order History</title>
    <link href="css/style.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        header h1 { margin: 0; }
        nav ul { list-style: none; padding: 0; display: flex; gap: 15px; }
        nav ul li a { text-decoration: none; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        img { border-radius: 5px; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Order History</h1>
        <nav>
            <ul>
                <li><a href="index2.php">Home</a></li>
                <li><a href="cart.php">Cart</a></li>
            </ul>
        </nav>
    </header>

    <section class="history">
        <?php displayOrderHistory($conn); ?>
    </section>
</div>
</body>
</html>
