<?php
session_start();

const DB_CONFIG = [
    'host' => 'localhost',
    'port' => '3307',
    'dbname' => 'cms',
    'username' => 'root',
    'password' => 'root'
];

try {
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', DB_CONFIG['host'], DB_CONFIG['port'], DB_CONFIG['dbname']);
    $pdo = new PDO($dsn, DB_CONFIG['username'], DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Cloth Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh; 
            padding: 2rem; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between;
        }
        header { 
            background: #1a1a1a; 
            color: #fff; 
            padding: 1.5rem; 
            text-align: center; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); 
        }
        header h1 { font-size: 2rem; text-transform: uppercase; }
        .payment-container { 
            max-width: 600px; 
            margin: 2rem auto; 
            background: #fff; 
            border-radius: 12px; 
            padding: 2rem; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
            text-align: center; 
        }
        .payment-container h2 { font-size: 1.8rem; color: #333; margin-bottom: 1.5rem; }
        .message { font-size: 1.2rem; margin-bottom: 1rem; }
        .success { color: #28a745; }
        .error { color: #ff6b6b; }
        .payment-options { display: flex; gap: 1rem; justify-content: center; margin-bottom: 1.5rem; }
        .payment-btn { 
            background: #007bff; 
            color: #fff; 
            border: none; 
            padding: 0.8rem 1.5rem; 
            border-radius: 25px; 
            cursor: pointer; 
            transition: background 0.3s ease; 
        }
        .payment-btn.active, .payment-btn:hover { background: #0056b3; }
        .payment-form { display: flex; flex-direction: column; gap: 1rem; }
        .payment-form input { 
            padding: 0.8rem; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-size: 1rem; 
            width: 100%; 
        }
        .payment-form .submit-btn { 
            background: #28a745; 
            padding: 1rem; 
            font-size: 1.1rem; 
            transition: background 0.3s ease, transform 0.2s ease; 
        }
        .payment-form .submit-btn:hover { background: #218838; transform: scale(1.05); }
        .hidden { display: none; }
        footer { 
            background: #1a1a1a; 
            color: #fff; 
            text-align: center; 
            padding: 1.5rem; 
        }
        .popup { 
            position: fixed; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%); 
            background: #fff; 
            padding: 2rem; 
            border-radius: 10px; 
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3); 
            text-align: center; 
            display: none; 
            z-index: 1000;
        }
        .popup.show { display: block; }
    </style>
</head>
<body>
    <header>
        <h1>Payment</h1>
    </header>

    <section class="payment-container">
        <h2>Complete Your Payment</h2>
        <?php if (isset($success)): ?>
            <p class="message success"><?php echo htmlspecialchars($success); ?></p>
        <?php elseif (isset($error)): ?>
            <p class="message error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="payment-options">
            <button class="payment-btn" data-method="upi">UPI</button>
            <button class="payment-btn" data-method="card">Card</button>
            <button class="payment-btn" data-method="cod">Cash on Delivery</button>
        </div>

        <form id="paymentForm" class="payment-form" method="POST">
            <div id="upiFields" class="hidden">
                <input type="text" name="upi_id" placeholder="Enter UPI ID (e.g., user@upi)" required>
            </div>
            <div id="cardFields" class="hidden">
                <input type="text" name="card_number" placeholder="Card Number (e.g., 1234 5678 9012 3456)" required>
                <input type="text" name="card_expiry" placeholder="Expiry Date (MM/YY)" required>
                <input type="text" name="card_cvv" placeholder="CVV (3-4 digits)" required>
            </div>
            <input type="hidden" name="payment_method" id="paymentMethod" value="">
            <button type="submit" class="submit-btn">Pay Now</button>
        </form>
    </section>

    <footer>
        <p>© 2025 Cloth Store. All rights reserved.</p>
    </footer>

    <div id="popup" class="popup">
        <h3>Payment Status</h3>
        <p id="popupMessage"></p>
    </div>

    <script>
        const paymentButtons = document.querySelectorAll('.payment-btn');
        const paymentForm = document.getElementById('paymentForm');
        const paymentMethodInput = document.getElementById('paymentMethod');
        const upiFields = document.getElementById('upiFields');
        const cardFields = document.getElementById('cardFields');
        const popup = document.getElementById('popup');
        const popupMessage = document.getElementById('popupMessage');

        paymentButtons.forEach(button => {
            button.addEventListener('click', () => {
                paymentButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const method = button.dataset.method;
                paymentMethodInput.value = method;

                upiFields.classList.add('hidden');
                cardFields.classList.add('hidden');
                upiFields.querySelector('input').required = false;
                cardFields.querySelectorAll('input').forEach(input => input.required = false);

                if (method === 'upi') {
                    upiFields.classList.remove('hidden');
                    upiFields.querySelector('input').required = true;
                }
                if (method === 'card') {
                    cardFields.classList.remove('hidden');
                    cardFields.querySelectorAll('input').forEach(input => input.required = true);
                }
                console.log('Selected payment method:', method);
            });
        });

        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(paymentForm);
            console.log('Form data:', Object.fromEntries(formData));

            try {
                const response = await fetch('payment.php', {
                    method: 'POST',
                    body: formData
                });
                const text = await response.text();
                console.log('Raw response:', text);
                const result = JSON.parse(text);

                popupMessage.textContent = result.success || result.error;
                popup.classList.add('show');
                if (result.success) {
                    localStorage.removeItem('cart');
                    setTimeout(() => {
                        popup.classList.remove('show');
                        window.location.href = 'collection.php';
                    }, 2000);
                } else {
                    setTimeout(() => popup.classList.remove('show'), 3000);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                popupMessage.textContent = 'An error occurred: ' + error.message;
                popup.classList.add('show');
                setTimeout(() => popup.classList.remove('show'), 3000);
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            console.log('Page loaded, order_id:', '<?php echo $_SESSION['order_id'] ?? 'none'; ?>');
            <?php if (isset($success)): ?>
                localStorage.removeItem('cart');
            <?php endif; ?>
        });
    </script>
</body>
</html>