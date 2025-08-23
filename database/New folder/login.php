<?php
session_start();
include "connect.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="admin/img/images.png" />
    <style> 
   body {
    font-family:'Times New Roman', Times, serif;
    padding: 0%;
    margin: 0%;
    background: url('admin/img/images.png') no-repeat center center/cover;
    color: white;
    text-align: center;
    height: 100vh !important;
}

h2 {
    text-align: center;
    margin-top: 5%;
    color: #d1e8ff;
}

.row { /* login.php */
    background:rgba(5, 21, 49, 0.61);
    border-radius: 12px;
    box-shadow: 0 10px 20px rgba(20, 83, 134, 0.83);
    top: 15%;
    position: relative;
    width: 150%;
    max-width: 350px;
    padding: 20px;
    margin: auto;
}

form {
    display: flex;
    flex-direction: column;
}

input {
    padding: 10px;
    border: 1px solid #1f3b73;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    outline: none;
}

input:focus {
    border-color: #2979ff;
    box-shadow: 0 0 10px #2979ff;
}

button {
    padding: 10px;
    background-color: #1f3b73;
    color: white;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    transition: background 0.3s ease;
}

button:hover {
    background-color: #2979ff;
}

a {
    margin-top: 2%;
    text-align: center;
    position: relative;
    color: #72EDF2;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

select {
    height: 37px;
    border-radius: 4px;
    background: #1f3b73;
    color: white;
    border: 1px solid #2979ff;
    padding: 5px;
}

select:focus {
    border-color: #72EDF2;
    box-shadow: 0 0 10px #72EDF2;
}

        </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="row">
        <h2>Login Form</h2>
        <form id="loginForm">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <button type="submit">Login</button>
            <a href="index2.php" class="action-button create-button">Home</a>
            <p>Don't have an account? <a href="./register.php">Register</a></p>
        </form>
    </div>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    checkLockStatus();
});

document.getElementById("loginForm").addEventListener("submit", async function (event) {
    event.preventDefault();

    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;
    let formData = new FormData();
    formData.append("email", email);
    formData.append("password", password);

    let attempts = parseInt(localStorage.getItem("attempts")) || 0;
    let lockedUntil = parseInt(localStorage.getItem("lockedUntil")) || 0;
    let lockTime = 30 * 1000; // 30 seconds lock time
    let currentTime = new Date().getTime();

    if (lockedUntil && currentTime < lockedUntil) {
        let remainingTime = Math.ceil((lockedUntil - currentTime) / 1000);
        Swal.fire({
            icon: "error",
            title: "Locked Out",
            text: `Please wait ${remainingTime} seconds before trying again.`,
        });
        return;
    }

    try {
        let response = await fetch("validate_login.php", {
            method: "POST",
            body: formData,
        });

        let result = await response.json();

        if (result.status === "success") {
            localStorage.setItem("attempts", 0);
            localStorage.removeItem("lockedUntil");

            Swal.fire({
                icon: "success",
                title: "Login Successful",
                text: "Redirecting...",
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = " index2.php";
            });

        } else {
            attempts++;
            localStorage.setItem("attempts", attempts);

            let remainingAttempts = 3 - attempts;
            if (remainingAttempts <= 0) {
                lockUserOut(lockTime);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Login Failed",
                    text: `Incorrect credentials. You have ${remainingAttempts} attempts left.`,
                });
            }
        }
    } catch (error) {
        console.error("Login error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Something went wrong! Please try again.",
        });
    }
});

function lockUserOut(lockTime) {
    let lockUntilTime = new Date().getTime() + lockTime;
    localStorage.setItem("lockedUntil", lockUntilTime);
    localStorage.setItem("attempts", 0); 

    Swal.fire({
        title: "Too Many Attempts",
        html: `Please wait <b>${lockTime / 1000}</b> seconds before retrying.`,
        timer: lockTime,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
            let timerInterval = setInterval(() => {
                const content = Swal.getHtmlContainer();
                if (content) {
                    const b = content.querySelector("b");
                    if (b) {
                        let remainingTime = Math.ceil((lockUntilTime - new Date().getTime()) / 1000);
                        b.textContent = remainingTime;
                    }
                }
            }, 1000);
        },
        willClose: () => {
            localStorage.removeItem("lockedUntil");
        }
    });
}

function checkLockStatus() {
    let lockedUntil = parseInt(localStorage.getItem("lockedUntil")) || 0;
    let currentTime = new Date().getTime();

    if (lockedUntil && currentTime < lockedUntil) {
        let remainingTime = Math.ceil((lockedUntil - currentTime) / 1000);
        Swal.fire({
            icon: "error",
            title: "Locked Out",
            text: `Please wait ${remainingTime} seconds before trying again.`,
        });
    }
}

</script>
</body>
</html>
