<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include './../connect.php';

// Fetch user ID from the URL parameter
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch user data
    $sql = "SELECT name, email FROM user WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }
} else {
    echo "No user ID specified.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Update the user details
    $updateSql = "UPDATE user SET name = ?, email = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('ssi', $name, $email, $userId);

    if ($updateStmt->execute()) {
        // Set session alert for success
        $_SESSION['alert'] = ['title' => 'Success', 'text' => 'User updated successfully!', 'icon' => 'success'];
        // Redirect after successful update (after alert is dismissed)
        header("Location: edituser.php?id=" . $userId);
        exit();
    } else {
        // Set session alert for failure
        $_SESSION['alert'] = ['title' => 'Error', 'text' => 'Failed to update user.', 'icon' => 'error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="icon" href="img/images.png" />
    <link href="css/styles.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        .form-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-container button {
            padding: 10px 20px;
            background-color: #3498db;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #2980b9;
        }
        .form-container a {
            display: block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit User</h2>
    <form action="edituser.php?id=<?php echo $userId; ?>" method="POST">
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required placeholder="Full Name">
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder="Email">
        <button type="submit">Update</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</div>

<script>
    // Show alert after form submission
    <?php if (isset($_SESSION['alert'])): ?>
        Swal.fire({
            title: "<?php echo $_SESSION['alert']['title']; ?>",
            text: "<?php echo $_SESSION['alert']['text']; ?>",
            icon: "<?php echo $_SESSION['alert']['icon']; ?>",
            confirmButtonColor: "#1abc9c"
        }).then(function() {
            // Redirect after alert is closed
            window.location.href = "logout.php"; // Redirect after update
        });
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>
</script>

</body>
</html>
