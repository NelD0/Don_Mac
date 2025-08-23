<?php
    include "connect.php";

// Get form data
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$full_name = $_POST['full_name'];
$address = $_POST['address'];
$contact_number = $_POST['contact_number'];
$birthdate = $_POST['birthdate'];
$age = $_POST['age'];
$gender = $_POST['gender'];

// Ensure the contact number is exactly 11 digits long and contains only digits
if (preg_match("/^09\d{9}$/", $contact_number)) {
    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users_info WHERE email = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
    } else {
        // Calculate age from birthdate
        $birthdate_obj = new DateTime($birthdate);
        $today = new DateTime();
        $age_diff = $today->diff($birthdate_obj);
        $age = $age_diff->y;

        // Store the formatted birthdate in a variable
        $formatted_birthdate = $birthdate_obj->format('Y-m-d');

        // Insert into database using prepared statements
        $stmt = $conn->prepare("INSERT INTO users_info (email, password, full_name, address, contact_number, birthdate, age, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssssssis", $email, $password, $full_name, $address, $contact_number, $formatted_birthdate, $age, $gender);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Registration successful!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
        }
    }

    $stmt->close();
} else {
    echo "Invalid contact number.";
}

$conn->close();
?>
