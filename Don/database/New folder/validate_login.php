<?php
session_start();
include "connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users_info WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];

            echo json_encode(["status" => "success", "message" => "Login successful!", "redirect" => "index2.php"]);
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect password."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No account found with this email."]);
    }

    $stmt->close();
}
$conn->close();
?>