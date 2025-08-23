<?php
session_start();
include './../connect.php'; // Include database connection

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['alert'] = [
        'title' => 'Error!',
        'text' => 'No user ID provided!',
        'icon' => 'error'
    ];
    header("Location: dashboard.php");
    exit;
}

$id = $_GET['id'];

// Prepare DELETE query
$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['alert'] = [
        'title' => 'Deleted!',
        'text' => 'User has been deleted successfully.',
        'icon' => 'success'
    ];
} else {
    $_SESSION['alert'] = [
        'title' => 'Error!',
        'text' => 'Failed to delete user.',
        'icon' => 'error'
    ];
}

$stmt->close();
$conn->close();

// Redirect back to dashboard
header("Location: users.php");
exit;
?>
