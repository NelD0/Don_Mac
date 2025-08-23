<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["message"])) {
    include 'connect.php'; // make sure the path is correct

    $message = strtolower(trim($_POST["message"]));
    $response = "Your question is way too far. Please ask another question related to our system! Try asking about our menu!";

    if (strpos($message, "menu") !== false) {
        $response = "The menu includes: Matcha-Berry, Donya Berry, Black Forest, Caramel Macchiatos, and Don Machatos 🍹";
    } elseif (strpos($message, "time") !== false || strpos($message, "hours") !== false) {
        $response = "We're always open online. Our walk-in store opens at 8 AM and closes at 9 PM! ☕";
    } elseif (strpos($message, "location") !== false) {
        $response = "We are located at Kalye-47 Poblacion, Madridejos, Cebu 📍";
    } elseif (strpos($message, "order") !== false) {
        $response = "You can place your order right from the menu above! 🛒";
    } elseif (strpos($message, "hi") !== false || strpos($message, "hello") !== false) {
        $response = "Hi there! How can I help you today? ☕";
    } elseif (strpos($message, "food") !== false) {
        $response = "We only serve Iced Coffee. Thank you! ☕";
    } elseif (strpos($message, "burger") !== false) {
        $response = "Sorry, but we only serve Iced Coffee. Thank you! ☕";
    } elseif (strpos($message, "much") !== false) {
        $response = "The price of each product is ₱39 ☕";
    } elseif (strpos($message, "best") !== false || strpos($message, "best seller") !== false) {
        // Fetch the best-selling product dynamically
        $query = "
            SELECT p.name AS product_name
            FROM sales s
            JOIN product_list p ON s.product_id = p.id
            GROUP BY s.product_id
            ORDER BY SUM(s.quantity) DESC
            LIMIT 1
        ";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response = "Our best seller Iced Coffee is " . $row['product_name'] . " ☕";
        } else {
            $response = "Sorry, we couldn't find our best-selling product right now. ☕";
        }
    } elseif (strpos($message, "don macchatios") !== false || strpos($message, "don machatos") !== false) {
        $response = "☕ *Don Macchatios*
        A bold fusion of rich espresso and velvety caramel, Don Macchatios is our signature iced coffee crafted for true coffee lovers. Served over ice and finished with a creamy layer of milk foam, it's the perfect balance of sweetness and strength — a refreshing pick-me-up with every sip.";
    }

    echo $response;
    exit;
}
?>
