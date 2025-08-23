<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include './../connect.php';

// Get total users count
$sql_total = "SELECT COUNT(*) AS total FROM user";
$result_total = $conn->query($sql_total);
$row_total = $result_total->fetch_assoc();
$totalUsers = $row_total['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="icon" href="img/images.png" />
    <link href="css/styles.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="header">
     <div class="logo">
        <img src="img/images.png">
    </div>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
</div>

<div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="action-button create-button">Dashboard</a></li>
                </ul>
            </nav>
        </aside>

<!-- Main Content -->
<main class="main-content">

    <!-- User List Table -->
    <h2>Admin</h2>
    <table>
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php 
        // Fetch user data
        $sql = "SELECT id, name, email FROM user";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['name']}</td>
                        <td>{$row['email']}</td>
                        <td>
                            <a href='edituser.php?id={$row['id']}' class='edit-button' style='background: #3498db; padding: 8px 12px; border-radius: 5px; color: white; text-decoration: none;'>Edit</a>
                            <a href='javascript:void(0);' onclick='confirmDelete({$row['id']})' class='delete-button' style='background: #e74c3c; padding: 8px 12px; border-radius: 5px; color: white; text-decoration: none;'>Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No users found</td></tr>";
        }

        $conn->close();
        ?>
    </table>
</main>

<script>
    function confirmDelete(userId) {
        Swal.fire({
            title: "Are you sure?",
            text: "This action cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#e74c3c",
            cancelButtonColor: "#7f8c8d",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "delete.php?id=" + userId;
            }
        });
    }

    // Show alert after deletion
    <?php if (isset($_SESSION['alert'])): ?>
        Swal.fire({
            title: "<?php echo $_SESSION['alert']['title']; ?>",
            text: "<?php echo $_SESSION['alert']['text']; ?>",
            icon: "<?php echo $_SESSION['alert']['icon']; ?>",
            confirmButtonColor: "#1abc9c"
        });
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>
</script>
</body>
</html>
