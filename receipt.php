<?php 
session_start();
require 'connect.php';

date_default_timezone_set('Asia/Manila');

// Retrieve order details from the session
$items = isset($_SESSION["order_receipt"]) ? $_SESSION["order_receipt"] : [];
$subtotal = isset($_SESSION["order_total"]) ? $_SESSION["order_total"] : 0;
$payment = isset($_SESSION["payment_amount"]) ? $_SESSION["payment_amount"] : 0;
$change = isset($_SESSION["change_due"]) ? $_SESSION["change_due"] : 0;

$total = $subtotal;

// Count total quantity of all items
$totalItems = 0;
foreach ($items as $item) {
    $totalItems += $item['qty'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="admin/img/images.png" />
    <title>Receipt</title>
    <style>
        body { font-family: 'Courier New', monospace; text-align: center; background: white; padding: 10px; }
        #receipt-container { max-width: 300px; margin: auto; padding: 10px; border: 1px solid #000; background: white; }
        hr { border: none; border-top: 1px dashed black; }
        .header { display: flex; align-items: center; justify-content: center; gap: 10px; }
        .header img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .barcode { font-size: 20px; margin-top: 10px; }
        .print-btn { margin-top: 15px; padding: 8px 15px; font-size: 14px; background: #333; color: white; border: none; cursor: pointer; }
        .print-btn:hover { background: #555; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>

<div id="receipt-container">
    <div class="header">
        <img src="admin/img/images.png" alt="Logo">
        <h3>DON MACCHIATOS</h3>
    </div>
    <hr>
    <p><?= date("m/d/Y h:i:s A") ?></p>
    <p>CHECK: <?= rand(100000, 999999) ?> &nbsp;&nbsp;&nbsp; CUST: <?= rand(1, 100) ?></p>
    <hr>

    <?php if (!empty($items)): ?>
        <?php foreach ($items as $item): ?>
            <p><?= strtoupper(htmlspecialchars($item['name'])) ?> .......... Php <?= number_format($item['price'] * $item['qty'], 2) ?></p>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No items purchased.</p>
    <?php endif; ?>

    <hr>
    <p><strong>Total Items: <?= $totalItems ?></strong></p>
    <p><strong>Total: Php <?= number_format($total, 2) ?></strong></p>
    <p>Payment: Php <?= number_format($payment, 2) ?></p>
    <p>Change: Php <?= number_format($change, 2) ?></p>
    <hr>

    <p>X: _________________________<br>SIGNATURE</p>
    <p>THANKS FOR SHOPPING WITH US</p>
    <div class="barcode"><?= rand(100000000000000, 999999999999999) ?></div>
    <p>&lt;&lt;&lt; CUSTOMER COPY &gt;&gt;&gt;</p>
</div>

<button class="print-btn" onclick="window.print()">Print Receipt</button>

</body>
</html>
