<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();
include 'connect.php';

$errorMessage = "";
$remaining = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!preg_match("/^[a-zA-Z]+$/", $username)) {
        $errorMessage = "Username must contain letters only.";
    } elseif ($username === '' || $password === '') {
        $errorMessage = "Please fill in both fields.";
    } else {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = 0;
        }

        $currentTime = time();
        $lockoutDuration = 120; // 2 minutes

        if ($_SESSION['login_attempts'] >= 5 && ($currentTime - $_SESSION['last_attempt_time'] < $lockoutDuration)) {
            $remaining = $lockoutDuration - ($currentTime - $_SESSION['last_attempt_time']);
            $errorMessage = "Too many login attempts. Try again in <span id='countdown'>$remaining</span> seconds.";
        } else {
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows === 1) {
                    $stmt->bind_result($id, $fetchedUsername, $storedHashedPassword);
                    $stmt->fetch();

                    if (password_verify($password, $storedHashedPassword)) {
                        $_SESSION['login_attempts'] = 0;
                        $_SESSION['user_id'] = $id;
                        $_SESSION['username'] = $fetchedUsername;
                        session_regenerate_id(true);
                        echo "<script>alert('Login successful!'); window.location.href = 'dashboard.php';</script>";
                        exit();
                    } else {
                        $_SESSION['login_attempts'] += 1;
                        $_SESSION['last_attempt_time'] = $currentTime;
                        $errorMessage = "Incorrect password.";
                    }
                } else {
                    $_SESSION['login_attempts'] += 1;
                    $_SESSION['last_attempt_time'] = $currentTime;
                    $errorMessage = "Username not found.";
                }
                $stmt->close();
            } else {
                $errorMessage = "Server error. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Login</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      height: 100vh;
      background: linear-gradient(-45deg, #6a11cb, #2575fc, #ff4e50, #f9d423);
      background-size: 400% 400%;
      animation: gradientBG 12s ease infinite;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .login-box {
      backdrop-filter: blur(15px);
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      padding: 2rem;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.8s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-15px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .form-control {
      border-radius: 12px;
    }

    .form-label {
      font-weight: 600;
    }

    .btn-gradient {
      background: linear-gradient(to right, #6a11cb, #2575fc);
      color: white;
      border: none;
      font-weight: 600;
      border-radius: 12px;
      transition: 0.3s ease;
    }

    .btn-gradient:hover {
      background: linear-gradient(to right, #5b0eb2, #1f64e0);
    }

    .password-wrapper {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      top: 70%;
      right: 15px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #333;
    }

    .error-message {
      color: #ffcccc;
      font-size: 0.9rem;
      text-align: center;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h3 class="text-center mb-4 text-primary">Welcome Back</h3>
    <form method="POST" action="">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" pattern="[A-Za-z]+" title="Letters only" required
          oninput="this.value = this.value.replace(/[^A-Za-z]/g, '')" placeholder="Enter username">
      </div>

      <div class="mb-3 password-wrapper">
        <label class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
        <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
      </div>

      <?php if (!empty($errorMessage)): ?>
        <div class="error-message"><?= $errorMessage ?></div>
      <?php endif; ?>

      <div class="d-grid mt-3">
        <button class="btn btn-gradient" type="submit">Login</button>
      </div>

      <div class="text-center mt-3">
        <a href="register.php">Don't have an account? Register</a>
      </div>
    </form>
  </div>

  <script>
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    toggle.addEventListener('click', function () {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('bi-eye');
      this.classList.toggle('bi-eye-slash');
    });
  </script>
</body>
</html>
