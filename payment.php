<?php
session_start();

const DB_CONFIG = [
    'host' => 'my-mysql',
    'dbname' => 'cms',
    'username' => 'root',
    'password' => 'root'
];

// Check if PDO MySQL driver is available
if (!in_array('mysql', PDO::getAvailableDrivers())) {
    http_response_code(500);
    exit(json_encode(['error' => 'PDO MySQL driver not found. Please enable or install it.']));
}

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8', DB_CONFIG['host'], DB_CONFIG['dbname']);
    $pdo = new PDO($dsn, DB_CONFIG['username'], DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartData'])) {
    $cart = json_decode($_POST['cartData'], true);
    $userId = $_SESSION['user_id'] ?? 1;

    if (empty($cart)) {
        $error = "Cart is empty!";
    } else {
        try {
            $pdo->beginTransaction();
            $total = array_reduce($cart, fn($sum, $item) => $sum + $item['price'] * $item['quantity'], 0);
            $stmt = $pdo->prepare('INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $total, 'pending']);
            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, dress_id, quantity, price_at_time) VALUES (?, ?, ?, ?)');
            foreach ($cart as $item) {
                $dressStmt = $pdo->prepare('SELECT stock FROM dresses WHERE id = ?');
                $dressStmt->execute([$item['id']]);
                $dress = $dressStmt->fetch();
                if (!$dress || $dress['stock'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for dress ID {$item['id']}");
                }
                $itemStmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
                $pdo->prepare('UPDATE dresses SET stock = stock - ? WHERE id = ?')->execute([$item['quantity'], $item['id']]);
            }

            $pdo->commit();
            $_SESSION['order_id'] = $orderId;
            $success = "Order placed successfully! Select a payment method.";
        } catch (Exception $e) {
            $pdo->rollBack();
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

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'INSERT INTO payments (order_id, payment_method, upi_id, card_number, card_expiry, card_cvv, payment_status) 
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $params = [$orderId, $method];
        switch ($method) {
            case 'upi':
                $upiId = $_POST['upi_id'] ?? '';
                if (empty($upiId)) throw new Exception('UPI ID is required');
                $params[] = $upiId;
                $params[] = null;
                $params[] = null;
                $params[] = null;
                break;
            case 'card':
                $cardNumber = $_POST['card_number'] ?? '';
                $cardExpiry = $_POST['card_expiry'] ?? '';
                $cardCvv = $_POST['card_cvv'] ?? '';
                if (empty($cardNumber) || empty($cardExpiry) || empty($cardCvv)) {
                    throw new Exception('All card details are required');
                }
                $params[] = null;
                $params[] = substr($cardNumber, -4);
                $params[] = $cardExpiry;
                $params[] = null;
                break;
            case 'cod':
                $params[] = null;
                $params[] = null;
                $params[] = null;
                $params[] = null;
                break;
            default:
                throw new Exception('Invalid payment method');
        }
        $params[] = 'completed';

        $stmt->execute($params);
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute(['completed', $orderId]);
        $pdo->commit();

        unset($_SESSION['order_id']);
        echo json_encode(['success' => 'Payment successful! Redirecting to collection...']);
    } catch (Exception $e) {
        $pdo->rollBack();
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
