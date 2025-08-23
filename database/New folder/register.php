<?php
        include "connect.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="admin/img/images.png" />
    <title>Registration</title>
    <style>
        body {
    align-items: center;
    justify-items: center;
    color: #fff;
    font-family:'Times New Roman', Times, serif;
    padding: 0%;
    margin: 0%;
    background: url('admin/img/images.png') no-repeat center center/cover;
    text-align: center;
    height: 100vh !important;
}

h2 {
    text-align: center;
    margin-top: 5%;
    color: #d1e8ff;
}

.container { /* register.php */
    background: rgba(10, 31, 68, 0.8);
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
    position: relative;
    width: 250%;
    max-width: 450px;
    padding: 15px;
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
    <!-- Include SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="container">
    <h2>Registration Form</h2>
    <form id="registrationForm" action="register_process.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Example@gmail.com" required><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Password at least 8 characters" required><br>

        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" placeholder="Full Name" required oninput="this.value=this.value.replace(/[^a-zA-Z\s]/g,'');"><br>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" placeholder="Address" required><br>

        <label for="contact_number">Contact Number:</label>
        <input type="tel" id="contact_number" name="contact_number" placeholder="0912345678" maxlength="11" pattern="\d{11}" required oninput="this.value=this.value.replace(/[^0-9]/g,'');"><br>

        <label for="birthdate">Birthdate:</label>
        <input type="date" id="birthdate" name="birthdate" required onchange="calculateAge()"><br>

        <label for="age">Age:</label>
        <input type="text" id="age" name="age" readonly><br>

        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select><br>

        <button type="submit">Register</button>
        <p>Already have an account? <a href="./login.php">Login here</a></p>
    </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('registrationForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent form submission

            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;
            var fullName = document.getElementById('full_name').value;
            var address = document.getElementById('address').value;
            var contactNumber = document.getElementById('contact_number').value;
            var birthdate = document.getElementById('birthdate').value;
            var gender = document.getElementById('gender').value;

            if (password.length < 8) {
                Swal.fire({
                    title: 'Error',
                    text: 'Password must be at least 8 characters long.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return; // Prevent form submission
            }

            if (!email || !fullName || !address || !contactNumber || !birthdate || !gender) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please fill out all fields correctly.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return; // Prevent form submission
            }

            var formData = new FormData(this);
            
            fetch('register_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Registration Successful!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to login page or perform another action
                            window.location.href = './login.php';
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        });

        function calculateAge() {
            var birthdate = document.getElementById('birthdate').value;
            if (birthdate) {
                var today = new Date();
                var birthDate = new Date(birthdate);
                var age = today.getFullYear() - birthDate.getFullYear();
                var monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                document.getElementById('age').value = age;
            }
        }
    </script>
</body>
</html>