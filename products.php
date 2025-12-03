<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Online Bidding System</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Theme -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'deep-blue': '#0f172a',
                        'electric-blue': '#3b82f6',
                        'bright-orange': '#f97316',
                        'neon-orange': '#fb923c',
                        'glass': 'rgba(255, 255, 255, 0.1)',
                    },
                    fontFamily: { 'inter': ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer base {
            html { scroll-behavior: smooth; }
            body { font-family: 'Inter', sans-serif; }
        }
        @layer utilities {
            .glass-effect {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            .card-hover {
                transition: all 0.3s ease;
            }
            .card-hover:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
            }
            .btn-glow:hover {
                box-shadow: 0 0 15px rgba(249, 115, 22, 0.5);
            }
            .countdown {
                font-family: 'Courier New', monospace;
                font-weight: bold;
                letter-spacing: 1px;
            }
            .modal {
                display: none;
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100%;
                background: rgba(0, 0, 0, 0.7);
                z-index: 1000;
                backdrop-filter: blur(5px);
                align-items: center;
                justify-content: center;
            }
            .modal.active {
                display: flex;
            }
            .modal-content {
                transform: scale(0.7);
                opacity: 0;
                transition: all 0.3s ease;
            }
            .modal.active .modal-content {
                transform: scale(1);
                opacity: 1;
            }
            .modal-content-register {
                max-width: 420px;
                width: 90vw;
            }
        }
    </style>
</head>
<body class="bg-deep-blue text-white min-h-screen">

    <!-- HEADER -->
    <header class="sticky top-0 z-50 glass-effect py-4 border-b border-white/10">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center">
                        <i class="fas fa-gavel text-white"></i>
                    </div>
                    <h1 class="text-2xl font-bold">Online <span class="text-bright-orange">Bidding</span></h1>
                </div>

                <nav class="hidden md:flex space-x-8">
                    <a href="index.php" class="font-medium hover:text-bright-orange transition-colors">Home</a>
                    <a href="products.php" class="font-medium text-bright-orange">Products</a>
                    <?php if(isLoggedIn()): ?>
                        <?php if(isAdmin()): ?>
                            <a href="admin/dashboard.php" class="font-medium hover:text-bright-orange">Admin</a>
                        <?php else: ?>
                            <a href="client/dashboard.php" class="font-medium hover:text-bright-orange">Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php" class="font-medium hover:text-bright-orange">Logout</a>
                    <?php else: ?>
                        <button onclick="openModal('loginModal')" class="font-medium hover:text-bright-orange">Login</button>
                        <button onclick="openModal('registerModal')" class="font-medium hover:text-bright-orange">Register</button>
                    <?php endif; ?>
                </nav>

                <button class="md:hidden text-xl" id="mobileMenuButton">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- MAIN -->
    <main class="py-12 md:py-16">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Available <span class="text-bright-orange">Products</span></h2>
                <p class="text-gray-400 max-w-2xl mx-auto">Bid on exclusive items before time runs out!</p>
            </div>

            <!-- SEARCH & FILTER -->
            <div class="max-w-4xl mx-auto mb-10 flex flex-col md:flex-row gap-4">
                <input type="text" id="searchInput" placeholder="Search products..." class="flex-1 px-5 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-bright-orange">
                <select id="categoryFilter" class="px-5 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-bright-orange">
                    <option value="all">All Categories</option>
                    <?php
                    $categories = $conn->query("SELECT DISTINCT category FROM products WHERE productStatus = 'active'");
                    while($cat = $categories->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($cat['category']) . '">' . htmlspecialchars($cat['category']) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <!-- PRODUCTS GRID -->
            <div id="productsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $query = "SELECT p.*, u.firstName, u.lastName 
                         FROM products p 
                         JOIN users u ON p.productOwner = u.id 
                         WHERE p.productStatus = 'active' 
                         ORDER BY p.datePosted DESC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $endTime = strtotime($row['productEnd']);
                        $timeLeft = $endTime - time();
                        $isEnded = $timeLeft <= 0;
                ?>
                        <div class="product-card glass-effect rounded-2xl overflow-hidden card-hover" data-category="<?= htmlspecialchars($row['category']) ?>">
                            <div class="relative">
                                <img src="<?= htmlspecialchars($row['productImgLoc']) ?>" alt="<?= htmlspecialchars($row['productName']) ?>" class="w-full h-56 object-cover">
                                <div class="absolute top-3 right-3 <?= $isEnded ? 'bg-red-600' : 'bg-bright-orange' ?> text-white px-3 py-1 rounded-full text-xs font-bold">
                                    <?= $isEnded ? 'Ended' : 'Live' ?>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold mb-1"><?= htmlspecialchars($row['productName']) ?></h3>
                                <p class="text-sm text-gray-400 mb-2"><?= htmlspecialchars($row['category']) ?></p>
                                <p class="text-gray-300 text-sm mb-3 line-clamp-2"><?= htmlspecialchars(substr($row['itemDesc'], 0, 100)) ?>...</p>

                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400 text-xs">Current Bid</span>
                                        <span class="font-bold text-bright-orange"><?= formatCurrency($row['productPrice']) ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400 text-xs">Min Bid</span>
                                        <span class="font-medium"><?= formatCurrency($row['minimumBid']) ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400 text-xs">Seller</span>
                                        <span class="text-sm"><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></span>
                                    </div>
                                </div>

                                <!-- COUNTDOWN -->
                                <div class="countdown text-center py-2 mb-4 rounded-lg bg-white/5 <?= $isEnded ? 'text-red-400' : 'text-electric-blue' ?>" 
                                     data-endtime="<?= $row['productEnd'] ?>">
                                    <?= $isEnded ? 'Bidding Ended' : '<span class="days">00</span>d : <span class="hours">00</span>h : <span class="minutes">00</span>m : <span class="seconds">00</span>s' ?>
                                </div>

                                <!-- ACTIONS -->
                                <div class="flex gap-2">
                                    <?php if(isLoggedIn() && isClient() && !$isEnded): ?>
                                        <a href="client/bid.php?product_id=<?= $row['id'] ?>" class="flex-1 py-2.5 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-medium text-center hover:opacity-90 btn-glow">
                                            Place Bid
                                        </a>
                                    <?php elseif(!isLoggedIn() && !$isEnded): ?>
                                        <button onclick="openModal('loginModal')" class="flex-1 py-2.5 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-medium text-center hover:opacity-90 btn-glow">
                                            Login to Bid
                                        </button>
                                    <?php endif; ?>
                                    <a href="product_detail.php?id=<?= $row['id'] ?>" class="flex-1 py-2.5 glass-effect rounded-lg font-medium text-center hover:bg-white/20 transition-all">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<div class="col-span-3 text-center py-16"><div class="glass-effect rounded-2xl p-8 max-w-md mx-auto"><i class="fas fa-box-open text-5xl text-gray-400 mb-4"></i><h3 class="text-xl font-bold mb-2">No Products</h3><p class="text-gray-400">Check back later.</p></div></div>';
                }
                ?>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="py-10 bg-gray-900 mt-16 border-t border-gray-800">
        <div class="container mx-auto px-4 text-center text-gray-400">
            <p>&copy; 2025 Online Bidding System. All rights reserved.</p>
        </div>
    </footer>

    <!-- LOGIN MODAL -->
    <div id="loginModal" class="modal">
        <div class="modal-content w-full max-w-md mx-4">
            <div class="glass-effect rounded-2xl p-8 relative">
                <button onclick="closeModal('loginModal')" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold">Welcome Back</h2>
                    <p class="text-gray-400 mt-2">Sign in to your account</p>
                </div>
                <form action="login.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-3 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange">
                    </div>
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange">
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded bg-gray-800 border-gray-700 text-bright-orange focus:ring-bright-orange">
                            <span class="ml-2 text-sm text-gray-300">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-bright-orange hover:underline">Forgot password?</a>
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-semibold hover:opacity-90 btn-glow">
                        Sign In
                    </button>
                </form>
                <div class="text-center mt-6">
                    <p class="text-gray-400">Don't have an account? 
                        <button onclick="switchToRegister()" class="text-bright-orange hover:underline font-medium">Sign up</button>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- REGISTER MODAL -->
    <div id="registerModal" class="modal">
        <div class="modal-content modal-content-register mx-4">
            <div class="glass-effect rounded-2xl p-6 md:p-8 relative">
                <button onclick="closeModal('registerModal')" class="absolute top-3 right-3 text-gray-400 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <div class="text-center mb-6">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-plus text-white text-lg"></i>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold">Create Account</h2>
                    <p class="text-gray-400 mt-2 text-sm">Join the bidding community</p>
                </div>
                <form action="register.php" method="POST" class="space-y-3 text-sm">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-300 text-xs font-medium mb-1">First Name</label>
                            <input type="text" name="firstName" required class="w-full px-3 py-2 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange text-sm">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-xs font-medium mb-1">Last Name</label>
                            <input type="text" name="lastName" required class="w-full px-3 py-2 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-300 text-xs font-medium mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-300 text-xs font-medium mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-300 text-xs font-medium mb-1">Confirm Password</label>
                        <input type="password" name="confirmPassword" required class="w-full px-3 py-2 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange text-sm">
                    </div>
                    <label class="flex items-start text-xs">
                        <input type="checkbox" required class="rounded bg-gray-800 border-gray-700 text-bright-orange focus:ring-bright-orange mt-0.5">
                        <span class="ml-2 text-gray-300">I agree to the <a href="#" class="text-bright-orange hover:underline">Terms</a> and <a href="#" class="text-bright-orange hover:underline">Privacy</a></span>
                    </label>
                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-semibold text-sm hover:opacity-90 btn-glow">
                        Create Account
                    </button>
                </form>
                <div class="text-center mt-4 text-xs">
                    <p class="text-gray-400">Already have an account? 
                        <button onclick="switchToLogin()" class="text-bright-orange hover:underline font-medium">Sign in</button>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        // Modal Controls
        function openModal(id) {
            document.getElementById(id).classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        function switchToRegister() { closeModal('loginModal'); setTimeout(() => openModal('registerModal'), 300); }
        function switchToLogin() { closeModal('registerModal'); setTimeout(() => openModal('loginModal'), 300); }

        document.addEventListener('click', e => {
            if (e.target.classList.contains('modal')) closeModal(e.target.id);
        });

        // Search & Filter
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const products = document.querySelectorAll('.product-card');

        function filterProducts() {
            const search = searchInput.value.toLowerCase();
            const category = categoryFilter.value;

            products.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const desc = card.querySelector('.line-clamp-2').textContent.toLowerCase();
                const cat = card.dataset.category;

                const matchesSearch = name.includes(search) || desc.includes(search);
                const matchesCategory = category === 'all' || cat === category;

                card.style.display = matchesSearch && matchesCategory ? 'block' : 'none';
            });
        }

        searchInput.addEventListener('input', filterProducts);
        categoryFilter.addEventListener('change', filterProducts);

        // Live Countdown
        function updateCountdowns() {
            document.querySelectorAll('.countdown').forEach(timer => {
                const endTime = new Date(timer.dataset.endtime).getTime();
                const now = new Date().getTime();
                const diff = endTime - now;

                if (diff <= 0) {
                    timer.innerHTML = 'Bidding Ended';
                    timer.classList.add('text-red-400');
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                timer.innerHTML = `<span class="days">${days.toString().padStart(2,'0')}</span>d : ` +
                                  `<span class="hours">${hours.toString().padStart(2,'0')}</span>h : ` +
                                  `<span class="minutes">${minutes.toString().padStart(2,'0')}</span>m : ` +
                                  `<span class="seconds">${seconds.toString().padStart(2,'0')}</span>s`;
            });
        }

        setInterval(updateCountdowns, 1000);
        updateCountdowns();

        // Mobile Menu
        const mobileBtn = document.getElementById('mobileMenuButton');
        const nav = document.querySelector('nav.hidden.md\\:flex');
        if (mobileBtn && nav) {
            mobileBtn.addEventListener('click', () => {
                nav.classList.toggle('hidden');
                nav.classList.toggle('absolute');
                nav.classList.toggle('top-16');
                nav.classList.toggle('left-0');
                nav.classList.toggle('right-0');
                nav.classList.toggle('bg-deep-blue');
                nav.classList.toggle('p-6');
                nav.classList.toggle('flex-col');
                nav.classList.toggle('space-y-4');
            });
        }
    </script>
</body>
</html>