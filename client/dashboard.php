<?php
require_once '../config.php';

if (!isLoggedIn() || !isClient()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// === USER INFO ===
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// === STATS ===
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM clients_bid_tbl WHERE bidderName = ? AND bidStatus = 'In-progress'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bids_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE productOwner = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$products_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM clients_bid_tbl WHERE bidderName = ? AND bidStatus = 'Won'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$winning_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Online Bidding System</title>

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
                        'neon-orange': '#fb923c',
                        'glass': 'rgba(255, 255, 255, 0.1)',
                    },
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    backdropBlur: {
                        'xs': '2px',
                    }
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
            .text-gradient {
                background: linear-gradient(90deg, #3b82f6, #f97316);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
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
            .status {
                @apply px-3 py-1 rounded-full text-xs font-bold;
            }
            .status.in-progress { @apply bg-yellow-500/20 text-yellow-300; }
            .status.won { @apply bg-green-500/20 text-green-300; }
            .status.active { @apply bg-bright-orange text-white; }
            .status.sold { @apply bg-gray-500/20 text-gray-300; }
        }
    </style>
</head>
<body class="bg-deep-blue text-white min-h-screen">

    <!-- HEADER (Same as index.php) -->
    <header class="sticky top-0 z-50 glass-effect py-4">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center">
                        <i class="fas fa-gavel text-white"></i>
                    </div>
                    <h1 class="text-2xl font-bold">Online <span class="text-bright-orange">Bidding</span></h1>
                </div>

                <nav class="hidden md:flex space-x-8">
                    <a href="dashboard.php" class="font-medium text-bright-orange">Dashboard</a>
                    <a href="products.php" class="font-medium hover:text-bright-orange transition-colors duration-300">Browse</a>
                    <a href="my_bids.php" class="font-medium hover:text-bright-orange transition-colors duration-300">My Bids</a>
                    <a href="add_product.php" class="font-medium hover:text-bright-orange transition-colors duration-300">Sell</a>
                    <a href="profile.php" class="font-medium hover:text-bright-orange transition-colors duration-300">Profile</a>
                    <a href="../logout.php" class="font-medium hover:text-bright-orange transition-colors duration-300">Logout</a>
                </nav>

                <button class="md:hidden text-xl" id="mobileMenuButton">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- HERO SECTION (Dashboard Welcome) -->
    <section class="hero py-16 md:py-20 relative overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-electric-blue opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-bright-orange opacity-10 blur-3xl"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Welcome back, <span class="text-gradient"><?= htmlspecialchars($user['firstName']) ?>!</span>
                </h2>
                <p class="text-xl text-gray-300">Track your bids, sales, and wins in one place.</p>
            </div>
        </div>
    </section>

    <!-- STATS CARDS (Like index.php "How It Works") -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
                <div class="glass-effect rounded-2xl p-6 text-center card-hover">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center text-white text-xl font-bold mx-auto mb-4">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <p class="text-gray-400 text-sm">Account Balance</p>
                    <p class="text-2xl font-bold text-bright-orange mt-1">
                        <?= formatCurrency(getUserMoney($user['userMoney'])) ?>
                    </p>
                </div>

                <div class="glass-effect rounded-2xl p-6 text-center card-hover">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center text-white text-xl font-bold mx-auto mb-4">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <p class="text-gray-400 text-sm">Active Bids</p>
                    <p class="text-2xl font-bold mt-1"><?= $bids_count ?></p>
                </div>

                <div class="glass-effect rounded-2xl p-6 text-center card-hover">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center text-white text-xl font-bold mx-auto mb-4">
                        <i class="fas fa-box"></i>
                    </div>
                    <p class="text-gray-400 text-sm">My Products</p>
                    <p class="text-2xl font-bold mt-1"><?= $products_count ?></p>
                </div>

                <div class="glass-effect rounded-2xl p-6 text-center card-hover">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 flex items-center justify-center text-white text-xl font-bold mx-auto mb-4">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <p class="text-gray-400 text-sm">Winning Bids</p>
                    <p class="text-2xl font-bold text-green-400 mt-1"><?= $winning_count ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- ACTIVE BIDS & MY PRODUCTS (Two Columns) -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-8">

                <!-- MY ACTIVE BIDS -->
                <div class="glass-effect rounded-2xl p-6 overflow-hidden card-hover">
                    <h3 class="text-xl font-bold mb-5 flex items-center gap-2">
                        <i class="fas fa-gavel text-bright-orange"></i> My Active Bids
                    </h3>
                    <div class="space-y-4">
                        <?php
                        $stmt = $conn->prepare("
                            SELECT b.*, p.productName, p.productPrice, p.productImgLoc 
                            FROM clients_bid_tbl b 
                            JOIN products p ON b.itemId = p.id 
                            WHERE b.bidderName = ? AND b.bidStatus = 'In-progress'
                            ORDER BY b.bidDate DESC LIMIT 5
                        ");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()): ?>
                                <div class="flex items-center justify-between p-4 glass-effect rounded-xl hover:bg-white/5 transition">
                                    <div class="flex items-center gap-3">
                                        <img src="../<?= htmlspecialchars($row['productImgLoc']) ?>" alt="" class="w-12 h-12 rounded-lg object-cover">
                                        <div>
                                            <p class="font-medium text-sm"><?= htmlspecialchars($row['productName']) ?></p>
                                            <p class="text-xs text-gray-400">My Bid: <?= formatCurrency($row['bidAmount']) ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400">Current: <?= formatCurrency($row['productPrice']) ?></p>
                                        <span class="status in-progress mt-1 inline-block">In-progress</span>
                                    </div>
                                    <a href="../products.php?view=<?= $row['itemId'] ?>" class="ml-4 text-electric-blue hover:text-bright-orange text-sm font-medium">
                                        View
                                    </a>
                                </div>
                            <?php endwhile;
                        else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-gavel text-4xl text-gray-500 mb-3"></i>
                                <p class="text-gray-400">No active bids yet.</p>
                                <a href="products.php" class="text-bright-orange text-sm hover:underline">Browse Products →</a>
                            </div>
                        <?php endif;
                        $stmt->close();
                        ?>
                    </div>
                </div>

                <!-- MY PRODUCTS FOR SALE -->
                <div class="glass-effect rounded-2xl p-6 overflow-hidden card-hover">
                    <h3 class="text-xl font-bold mb-5 flex items-center gap-2">
                        <i class="fas fa-box text-bright-orange"></i> My Products for Sale
                    </h3>
                    <div class="space-y-4">
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM products WHERE productOwner = ? ORDER BY datePosted DESC LIMIT 5");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                                $bid_stmt = $conn->prepare("SELECT COUNT(*) as count FROM clients_bid_tbl WHERE itemId = ?");
                                $bid_stmt->bind_param("i", $row['id']);
                                $bid_stmt->execute();
                                $bid_count = $bid_stmt->get_result()->fetch_assoc()['count'];
                                $bid_stmt->close();
                        ?>
                                <div class="flex items-center justify-between p-4 glass-effect rounded-xl hover:bg-white/5 transition">
                                    <div>
                                        <p class="font-medium text-sm"><?= htmlspecialchars($row['productName']) ?></p>
                                        <p class="text-xs text-gray-400">Price: <?= formatCurrency($row['productPrice']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400"><?= $bid_count ?> bid<?= $bid_count == 1 ? '' : 's' ?></p>
                                        <span class="status <?= strtolower($row['productStatus']) ?> mt-1 inline-block">
                                            <?= ucfirst($row['productStatus']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile;
                        else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-box-open text-4xl text-gray-500 mb-3"></i>
                                <p class="text-gray-400">No products listed.</p>
                                <a href="add_product.php" class="text-bright-orange text-sm hover:underline">Sell Something →</a>
                            </div>
                        <?php endif;
                        $stmt->close();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER (Same as index.php) -->
    <footer class="py-12 bg-gray-900 mt-16">
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Online Bidding System. All rights reserved.</p>
            </div>
    </footer>

    <!-- MOBILE MENU SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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

            // Animate cards on scroll
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
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>