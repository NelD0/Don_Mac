<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Don Macchaitos</title>
    <link rel="icon" href="admin/img/images.png" />
    <style>
         body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('img/img.jpg') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }
        .overlay {
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 10px;
        }
        h1 {
            font-size: 2.5em;
        }
        p {
            font-size: 1.2em;
        }
        .order-btn {
            display: inline-block;
            padding: 10px 20px;
            background: red;
            color: white;
            text-decoration: none;
            font-size: 1.2em;
            border-radius: 5px;
        }
        .order-btn:hover {
            background: darkred;
        }
        </style>
<body>
    <div class="overlay">
        <h1>You want Ice Coffe Dringks</h1>
        <p>Enjoy our delicious Dringks</p>
        <a href="index2.php" class="order-btn">Order Now</a>
    </div>
</body>
</html>