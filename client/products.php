<?php
include '../config.php';

if (!isLoggedIn() || !isClient()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Products - Online Bidding System</title>

    <!-- Tailwind + Fonts + Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'deep-blue': '#0f172a',
                        'electric-blue': '#3b82f6',
                        'bright-orange': '#f97316',
                        'glass': 'rgba(255,255,255,0.1)'
                    },
                    fontFamily: { 'inter': ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer base { html { scroll-behavior: smooth; } body { font-family: 'Inter', sans-serif; } }
        @layer utilities {
            .glass-effect { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
            .text-gradient { background: linear-gradient(90deg, #3b82f6, #f97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
            .card-hover { transition: all 0.3s ease; }
            .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
            .btn-glow:hover { box-shadow: 0 0 15px rgba(249,115,22,0.5); }
            .status { @apply px-3 py-1 rounded-full text-xs font-bold; }
            .status.active { @apply bg-bright-orange text-white; }
            .status.bid { @apply bg-yellow-500/20 text-yellow-300 border border-yellow-500/40; }
            .countdown { @apply text-sm font-mono text-bright-orange; }
        }
    </style>
</head>
<body class="bg-deep-blue text-white min-h-screen">

    <!-- HEADER -->
    <header class="sticky top-0 z-50 glass-effect py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center">
                    <i class="fas fa-gavel text-white"></i>
                </div>
                <h1 class="text-2xl font-bold">Online <span class="text-bright-orange">Bidding</span></h1>
            </div>

            <nav class="hidden md:flex space-x-8">
                <a href="dashboard.php" class="font-medium hover:text-bright-orange transition-colors">Dashboard</a>
                <a href="products.php" class="font-medium text-bright-orange">Browse</a>
                <a href="my_bids.php" class="font-medium hover:text-bright-orange transition-colors">My Bids</a>
                <a href="add_product.php" class="font-medium hover:text-bright-orange transition-colors">Sell</a>
                <a href="profile.php" class="font-medium hover:text-bright-orange transition-colors">Profile</a>
                <a href="../logout.php" class="font-medium hover:text-bright-orange transition-colors">Logout</a>
            </nav>

            <button class="md:hidden text-xl" id="mobileMenuButton">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="py-16 md:py-20 relative overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-electric-blue opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-bright-orange opacity-10 blur-3xl"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Browse <span class="text-gradient">Active Auctions</span>
                </h2>
                <p class="text-xl text-gray-300">Find and bid on amazing products</p>
            </div>

            <!-- SEARCH & FILTER -->
            <div class="max-w-4xl mx-auto mt-10 flex flex-col md:flex-row gap-4">
                <input type="text" id="searchInput" 
                       class="flex-1 px-5 py-3 bg-white/10 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-bright-orange text-white placeholder-gray-400" 
                       placeholder="Search products...">
                <select id="categoryFilter" 
                        class="px-5 py-3 bg-white/10 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-bright-orange text-white">
                    <option value="all">All Categories</option>
                    <?php
                    $categories = $conn->query("SELECT DISTINCT category FROM products WHERE productStatus = 'active'");
                    while($cat = $categories->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($cat['category']) . '">' . htmlspecialchars($cat['category']) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </section>

    <!-- PRODUCTS GRID -->
    <section class="py-16">
        <div class="container mx-auto px-4">
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
                        $bid_check = $conn->query("SELECT * FROM clients_bid_tbl WHERE bidderName = $user_id AND itemId = " . $row['id']);
                        $has_bid = $bid_check->num_rows > 0;

                        // Image path with fallback
                        $imgPath = $row['productImgLoc'];
                        $fullPath = '../' . $imgPath;
                        $defaultImg = '../assets/images/default-product.jpg';
                        $displayImg = (!empty($imgPath) && file_exists($fullPath)) ? $imgPath : $defaultImg;
                        ?>
                        <div class="product-card glass-effect rounded-2xl overflow-hidden card-hover" 
                             data-category="<?= htmlspecialchars($row['category']) ?>">
                            <div class="relative">
                                <img src="../<?= htmlspecialchars($displayImg) ?>" 
                                     alt="<?= htmlspecialchars($row['productName']) ?>" 
                                     class="w-full h-56 object-cover">
                                <div class="absolute top-4 right-4">
                                    <span class="status active">Active</span>
                                </div>
                                <?php if ($has_bid): ?>
                                    <div class="absolute top-4 left-4">
                                        <span class="status bid">You Bid</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($row['productName']) ?></h3>
                                <p class="text-sm text-gray-400 mb-1">Category: <?= htmlspecialchars($row['category']) ?></p>
                                <p class="text-gray-300 text-sm mb-3 line-clamp-2"><?= htmlspecialchars(substr($row['itemDesc'], 0, 100)) ?>...</p>
                                
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <p class="text-sm text-gray-400">Current Price</p>
                                        <p class="text-2xl font-bold text-bright-orange"><?= formatCurrency($row['productPrice']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-400">Min Bid</p>
                                        <p class="text-lg font-semibold"><?= formatCurrency($row['minimumBid']) ?></p>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-sm text-gray-400 mb-4">
                                    <span>Seller: <?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></span>
                                    <div class="text-right">
                                        <div class="countdown" data-end="<?= $row['productEnd'] ?>">--:--</div>
                                        <div class="text-xs">until end</div>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <a href="bid.php?product_id=<?= $row['id'] ?>" 
                                       class="flex-1 py-3 bg-gradient-to-r from-electric-blue to-bright-orange rounded-xl font-semibold text-center hover:opacity-90 transition-all btn-glow">
                                        Place Bid
                                    </a>
                                    <a href="../product_detail.php?id=<?= $row['id'] ?>" 
                                       class="flex-1 py-3 glass-effect rounded-xl font-semibold text-center hover:bg-white/20 transition-all">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="col-span-3 text-center py-16">
                            <div class="glass-effect rounded-2xl p-8 max-w-md mx-auto">
                                <i class="fas fa-box-open text-5xl text-gray-500 mb-4"></i>
                                <h3 class="text-xl font-bold mb-2">No Active Auctions</h3>
                                <p class="text-gray-400">Check back later for new listings.</p>
                            </div>
                          </div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-12 bg-gray-900 mt-16">
        <div class="container mx-auto px-4 text-center text-gray-400 text-sm">
            <p>© 2025 Online Bidding System • Client Portal • <?php echo date('d M Y, H:i'); ?> PH Time (UTC+8)</p>
        </div>
    </footer>

    <!-- SCRIPTS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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

            // Search & Filter
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const products = document.querySelectorAll('.product-card');

            function filterProducts() {
                const search = searchInput.value.toLowerCase();
                const category = categoryFilter.value;

                products.forEach(product => {
                    const title = product.querySelector('h3').textContent.toLowerCase();
                    const desc = product.querySelector('.line-clamp-2').textContent.toLowerCase();
                    const prodCategory = product.dataset.category;

                    const matchesSearch = title.includes(search) || desc.includes(search);
                    const matchesCategory = category === 'all' || prodCategory === category;

                    product.style.display = matchesSearch && matchesCategory ? 'block' : 'none';
                });
            }

            searchInput.addEventListener('input', filterProducts);
            categoryFilter.addEventListener('change', filterProducts);

            // Live Countdown for each product
            document.querySelectorAll('.countdown').forEach(el => {
                const endTime = new Date(el.dataset.end).getTime();
                const update = () => {
                    const now = new Date().getTime();
                    const distance = endTime - now;

                    if (distance <= 0) {
                        el.innerHTML = "EXPIRED";
                        el.classList.add('text-red-400');
                        return;
                    }

                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    el.innerHTML = `${hours}h ${minutes}m ${seconds}s`;
                };
                update();
                setInterval(update, 1000);
            });

            // Card Animations
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.card-hover').forEach(card => {
                card.style.opacity = 0;
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>