<?php
session_start();

$servername = "my-mysql";
$username = "root";
$password = "root";
$dbname = "cms";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartData'])) {
    $cart = json_decode($_POST['cartData'], true);
    $userId = $_SESSION['user_id'] ?? 1;

    if (empty($cart)) {
        $error = "Cart is empty!";
    } else {
        $conn->begin_transaction();
        try {
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("id", $userId, $total);
            $stmt->execute();
            $orderId = $stmt->insert_id;
            $stmt->close();

            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, dress_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
            $itemStmt->bind_param("iiid", $orderId, $dressId, $quantity, $priceAtTime);

            foreach ($cart as $item) {
                $dressId = $item['id'];
                $quantity = $item['quantity'];
                $priceAtTime = $item['price'];

                $stockCheck = $conn->prepare("SELECT stock FROM dresses WHERE id = ?");
                $stockCheck->bind_param("i", $dressId);
                $stockCheck->execute();
                $result = $stockCheck->get_result();
                $dress = $result->fetch_assoc();
                $stockCheck->close();

                if (!$dress || $dress['stock'] < $quantity) {
                    throw new Exception("Insufficient stock for dress ID $dressId");
                }

                $itemStmt->execute();

                $updateStock = $conn->prepare("UPDATE dresses SET stock = stock - ? WHERE id = ?");
                $updateStock->bind_param("ii", $quantity, $dressId);
                $updateStock->execute();
                $updateStock->close();
            }

            $itemStmt->close();
            $conn->commit();

            $_SESSION['order_id'] = $orderId;
            $success = "Order placed successfully! Select a payment method.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to place order: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['cartData'])) {
    header('Content-Type: application/json');
    $orderId = $_SESSION['order_id'] ?? null;
    if (!$orderId) {
        exit(json_encode(['error' => 'No order found. Please place an order first.']));
    }

    $method = $_POST['payment_method'] ?? null;
    if (!$method) {
        exit(json_encode(['error' => 'No payment method selected']));
    }

    $conn->begin_transaction();
    try {
        $upiId = $cardNumber = $cardExpiry = $cardCvv = null;
        if ($method === 'upi') {
            $upiId = $_POST['upi_id'] ?? '';
            if (empty($upiId)) throw new Exception('UPI ID is required');
        } elseif ($method === 'card') {
            $cardNumber = $_POST['card_number'] ?? '';
            $cardExpiry = $_POST['card_expiry'] ?? '';
            $cardCvv = $_POST['card_cvv'] ?? '';
            if (empty($cardNumber) || empty($cardExpiry) || empty($cardCvv)) {
                throw new Exception('All card details are required');
            }
            $cardNumber = substr($cardNumber, -4); // store only last 4 digits
        } elseif ($method !== 'cod') {
            throw new Exception('Invalid payment method');
        }

        $stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, upi_id, card_number, card_expiry, card_cvv, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'completed')");
        $stmt->bind_param("isssss", $orderId, $method, $upiId, $cardNumber, $cardExpiry, $cardCvv);
        $stmt->execute();
        $stmt->close();

        $updateOrder = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $updateOrder->bind_param("i", $orderId);
        $updateOrder->execute();
        $updateOrder->close();

        $conn->commit();
        unset($_SESSION['order_id']);
        echo json_encode(['success' => 'Payment successful! Redirecting to collection...']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => 'Payment failed: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment | Cloth Store</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 10px; }
        h1 { text-align: center; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="container">
    <h1>Payment Page</h1>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <h3>Select Payment Method</h3>
        <label><input type="radio" name="payment_method" value="upi" required> UPI</label><br>
        <input type="text" name="upi_id" placeholder="Enter UPI ID"><br><br>

        <label><input type="radio" name="payment_method" value="card" required> Card</label><br>
        <input type="text" name="card_number" placeholder="Card Number"><br>
        <input type="text" name="card_expiry" placeholder="MM/YY"><br>
        <input type="text" name="card_cvv" placeholder="CVV"><br><br>

        <label><input type="radio" name="payment_method" value="cod" required> Cash on Delivery</label><br><br>

        <button type="submit">Pay Now</button>
    </form>
</div>
</body>
</html>
