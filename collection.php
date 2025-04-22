<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dress Collection - Cloth Store</title>
    <style>
        * {
           
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

        .filter-section {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .filter-section h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .filter-btn {
            padding: 0.6rem 1.5rem;
            margin: 0.5rem;
            border: none;
            background: #007bff;
            color: #fff;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            transition: transform 0.2s ease, background 0.3s ease;
        }

        .filter-btn:hover {
            background: #0056b3;
            transform: scale(1.05);
        }

        .filter-btn.active {
            background: #28a745;
            transform: scale(1.05);
        }

        .dress-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            opacity: 0;
            animation: fadeIn 1s ease forwards;
        }

        .dress-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            position: relative;
            animation: slideUp 0.5s ease forwards;
            animation-delay: calc(var(--index) * 0.1s);
        }

        .dress-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .dress-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .dress-card:hover img {
            transform: scale(1.1);
        }

        .dress-info {
            padding: 1.5rem;
            text-align: center;
        }

        .dress-info h3 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            color: #222;
        }

        .dress-info p {
            color: #666;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .add-to-cart {
            background: #ff6b6b;
            color: #fff;
            border: none;
            padding: 0.8rem;
            border-radius: 25px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            transition: background 0.3s ease, transform 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .add-to-cart::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .add-to-cart:hover::before {
            left: 100%;
        }

        .add-to-cart:hover {
            background: #e55a5a;
            transform: scale(1.05);
        }

        .loader {
            text-align: center;
            font-size: 1.2rem;
            color: #333;
            padding: 2rem;
            display: none;
        }

        footer {
            background: #1a1a1a;
            color: #fff;
            text-align: center;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dress-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            }

            nav {
                flex-direction: column;
                gap: 1rem;
            }

            header h1 {
                font-size: 1.5rem;
            }

            .filter-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .dress-card img {
                height: 200px;
            }

            .dress-info h3 {
                font-size: 1.2rem;
            }

            .dress-info p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Our Dress Collection</h1>
        <nav>
            <a href="collection.php">Home</a>
            <a href="cart.php" class="cart-link">Cart <span class="cart-count" id="cartCount">0</span></a>
            <a href="login.php">Logout</a>
        </nav>
    </header>

    <section class="filter-section">
        <h2>Filter by Category</h2>
        <button class="filter-btn active" data-category="all">All</button>
        <button class="filter-btn" data-category="casual">Casual</button>
        <button class="filter-btn" data-category="formal">Formal</button>
        <button class="filter-btn" data-category="party">Party</button>
    </section>

    <div class="loader" id="loader">Loading...</div>
    <section class="dress-grid" id="dressGrid"></section>

   

    <script>
        // Sample dress data
        const dresses = [
            {
                id: 1,
                name: "Summer Breeze Dress",
                category: "casual",
                price: 29.99,
                image: "th.jpeg"
            },
            {
                id: 2,
                name: "Evening Elegance Gown",
                category: "formal",
                price: 89.99,
                image: "Evening Elegance Gown.jpg"
            },
            {
                id: 3,
                name: "Party Sparkle Dress",
                category: "party",
                price: 49.99,
                image: "Party Sparkle Dress.jpeg"
            },
            {
                id: 4,
                name: "Casual Floral Dress",
                category: "casual",
                price: 34.99,
                image: "Casual Floral Dress.jpeg"
            },
            {
                id: 5,
                name: "Formal Black Dress",
                category: "formal",
                price: 79.99,
                image: "Formal Black Dress.jpeg"
            },
            {
                id: 6,
                name: "Party Red Dress",
                category: "party",
                price: 59.99,
                image: "Party Red Dress.png"
            }
        ];

        // DOM elements
        const dressGrid = document.getElementById("dressGrid");
        const loader = document.getElementById("loader");
        const filterButtons = document.querySelectorAll(".filter-btn");
        const cartCount = document.getElementById("cartCount");

        // Cart management
        function getCart() {
            return JSON.parse(localStorage.getItem("cart")) || [];
        }

        function saveCart(cart) {
            localStorage.setItem("cart", JSON.stringify(cart));
            updateCartCount();
        }

        function addToCart(dressId) {
            const dress = dresses.find(d => d.id === dressId);
            const cart = getCart();
            const existingItem = cart.find(item => item.id === dressId);

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({ ...dress, quantity: 1 });
            }

            saveCart(cart);
            alert(`${dress.name} added to cart!`);
        }

        function updateCartCount() {
            const cart = getCart();
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
        }

        // Display dresses
        function displayDresses(dressesToShow) {
            dressGrid.innerHTML = "";
            loader.style.display = "block";
            dressGrid.style.opacity = "0";

            setTimeout(() => {
                loader.style.display = "none";
                dressesToShow.forEach((dress, index) => {
                    const dressCard = document.createElement("div");
                    dressCard.classList.add("dress-card");
                    dressCard.style.setProperty("--index", index);
                    dressCard.innerHTML = `
                        <img src="${dress.image || 'https://via.placeholder.com/300?text=No+Image'}" 
                             alt="${dress.name}" 
                             onerror="this.src='https://via.placeholder.com/300?text=Image+Error'">
                        <div class="dress-info">
                            <h3>${dress.name}</h3>
                            <p>$${dress.price.toFixed(2)}</p>
                            <button class="add-to-cart" onclick="addToCart(${dress.id})">Add to Cart</button>
                        </div>
                    `;
                    dressGrid.appendChild(dressCard);
                });
                dressGrid.style.opacity = "1";
            }, 500);
        }

        // Filter dresses
        function filterDresses(category) {
            const filteredDresses = category === "all" 
                ? dresses 
                : dresses.filter(dress => dress.category === category);
            displayDresses(filteredDresses);
        }

        // Initialize page
        document.addEventListener("DOMContentLoaded", () => {
            // Load Google Fonts
            const link = document.createElement("link");
            link.href = "https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap";
            link.rel = "stylesheet";
            document.head.appendChild(link);

            // Display all dresses
            displayDresses(dresses);

            // Update cart count
            updateCartCount();

            // Filter button listeners
            filterButtons.forEach(button => {
                button.addEventListener("click", () => {
                    filterButtons.forEach(btn => btn.classList.remove("active"));
                    button.classList.add("active");
                    const category = button.getAttribute("data-category");
                    filterDresses(category);
                });
            });
        });
    </script>
</body>
</html>