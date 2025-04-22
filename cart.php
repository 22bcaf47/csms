<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Cloth Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        header {
            background: #1a1a1a;
            color: #fff;
            padding: 1.5rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        header h1 {
            font-size: 2rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        nav {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
            position: relative;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: background 0.3s ease;
        }

        nav a:hover {
            background: #ff6b6b;
        }

        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ff6b6b;
            color: #fff;
            border-radius: 50%;
            padding: 0.2rem 0.5rem;
            font-size: 0.9rem;
        }

        .cart-section {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .cart-section h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #333;
            text-align: center;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .cart-table th, .cart-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .cart-table th {
            background: #f4f4f4;
            font-weight: 600;
            color: #333;
        }

        .cart-table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 0.3rem 0.6rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .quantity-btn:hover {
            background: #0056b3;
        }

        .remove-btn {
            background: #ff6b6b;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .remove-btn:hover {
            background: #e55a5a;
        }

        .cart-total {
            text-align: right;
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .checkout-btn {
            display: block;
            width: 200px;
            margin: 0 auto;
            padding: 1rem;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 1.1rem;
            cursor: pointer;
            text-align: center;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .checkout-btn:hover {
            background: #218838;
            transform: scale(1.05);
        }

        .empty-cart {
            text-align: center;
            font-size: 1.2rem;
            color: #666;
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .cart-table th, .cart-table td {
                font-size: 0.9rem;
                padding: 0.8rem;
            }

            .cart-table img {
                width: 60px;
                height: 60px;
            }

            nav {
                flex-direction: column;
                gap: 1rem;
            }

            header h1 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .cart-table {
                display: block;
                overflow-x: auto;
            }

            .cart-table th, .cart-table td {
                font-size: 0.8rem;
                padding: 0.5rem;
            }

            .cart-table img {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Shopping Cart</h1>
        <nav>
            <a href="collection.php">Home</a>
            <a href="cart.php" class="cart-link">Cart <span class="cart-count" id="cartCount">0</span></a>
            <a href="logout.html">Logout</a>
        </nav>
    </header>

    <section class="cart-section">
        <h2>Your Cart</h2>
        <table class="cart-table" id="cartTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="cartItems"></tbody>
        </table>
        <div class="cart-total" id="cartTotal">Total: $0.00</div>
        <form id="paymentForm" action="payment.php" method="POST">
            <input type="hidden" name="cartData" id="cartData">
            <button type="submit" class="checkout-btn">Go to Payment</button>
        </form>
        <div class="empty-cart" id="emptyCart" style="display: none;">Your cart is empty.</div>
    </section>

    <footer>
        <p>© 2025 Cloth Store. All rights reserved.</p>
    </footer>

    <script>
        // DOM elements
        const cartItems = document.getElementById("cartItems");
        const cartTotal = document.getElementById("cartTotal");
        const emptyCart = document.getElementById("emptyCart");
        const cartCount = document.getElementById("cartCount");
        const paymentForm = document.getElementById("paymentForm");
        const cartDataInput = document.getElementById("cartData");

        // Cart management
        function getCart() {
            return JSON.parse(localStorage.getItem("cart")) || [];
        }

        function saveCart(cart) {
            localStorage.setItem("cart", JSON.stringify(cart));
            updateCartCount();
            displayCart();
        }

        function updateCartCount() {
            const cart = getCart();
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
        }

        function updateQuantity(id, quantity) {
            const cart = getCart();
            const item = cart.find(item => item.id === id);
            if (item) {
                if (quantity > 0) {
                    item.quantity = quantity;
                } else {
                    const index = cart.indexOf(item);
                    cart.splice(index, 1);
                }
                saveCart(cart);
            }
        }

        function removeFromCart(id) {
            const cart = getCart();
            const index = cart.findIndex(item => item.id === id);
            if (index !== -1) {
                cart.splice(index, 1);
                saveCart(cart);
            }
        }

        function displayCart() {
            const cart = getCart();
            cartItems.innerHTML = "";
            if (cart.length === 0) {
                emptyCart.style.display = "block";
                cartTotal.style.display = "none";
                paymentForm.style.display = "none";
                return;
            }

            emptyCart.style.display = "none";
            cartTotal.style.display = "block";
            paymentForm.style.display = "block";

            cart.forEach(item => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>
                        <img src="${item.image || 'https://via.placeholder.com/80?text=No+Image'}" 
                             alt="${item.name}" 
                             onerror="this.src='https://via.placeholder.com/80?text=Image+Error'">
                        ${item.name}
                    </td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                            <span>${item.quantity}</span>
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                        </div>
                    </td>
                    <td>$${(item.price * item.quantity).toFixed(2)}</td>
                    <td>
                        <button class="remove-btn" onclick="removeFromCart(${item.id})">Remove</button>
                    </td>
                `;
                cartItems.appendChild(row);
            });

            const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            cartTotal.textContent = `Total: $${total.toFixed(2)}`;
        }

        // Handle form submission
        paymentForm.addEventListener('submit', (e) => {
            const cart = getCart();
            if (cart.length === 0) {
                e.preventDefault();
                alert('Your cart is empty!');
                return;
            }
            cartDataInput.value = JSON.stringify(cart);
        });

        // Initialize page
        document.addEventListener("DOMContentLoaded", () => {
            // Load Google Fonts
            const link = document.createElement("link");
            link.href = "https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap";
            link.rel = "stylesheet";
            document.head.appendChild(link);

            // Display cart
            displayCart();
            updateCartCount();
        });
    </script>
</body>
</html>