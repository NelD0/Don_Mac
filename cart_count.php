<?php
include 'connect.php';

$sql = "SELECT COUNT(*) AS count FROM cart";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo $row["count"]; // Output cart count
$conn->close();
?>
