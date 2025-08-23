
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <link rel="icon" href="img/images.png" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Login</title>
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
        <button onclick="goBackToOrder()" style="margin-top: 10px;">Back to Order Page</button>
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
        let response = await fetch("validate.php", {
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
                window.location.href = "dashboard.php";
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


function goBackToOrder() {
    window.location.href = "./../index2.php"; // Redirect to order page
}

</script>

</body>
</html>
