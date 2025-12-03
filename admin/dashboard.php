<?php
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$users_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE userType = 2")->fetch_assoc()['count'];
$products_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$active_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE productStatus = 'active'")->fetch_assoc()['count'];
$total_bids = $conn->query("SELECT COUNT(*) as count FROM clients_bid_tbl")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Bidding System</title>
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
            .stat-number { @apply text-4xl font-bold text-bright-orange; }
            .table-container { @apply overflow-x-auto; }
            table { @apply w-full text-sm; }
            th { @apply px-3 py-2 text-xs font-medium text-gray-300 uppercase bg-white/5; }
            td { @apply px-3 py-2 border-t border-white/10; }
            .status { @apply px-2 py-1 rounded-full text-xs font-medium; }
            .status.active { @apply bg-green-500/20 text-green-300; }
            .status.inactive { @apply bg-gray-500/20 text-gray-300; }
            .status.sold { @apply bg-red-500/20 text-red-300; }
        }
    </style>
</head>
<body class="bg-deep-blue text-white min-h-screen">

    <!-- HEADER -->
    <header class="sticky top-0 z-50 glass-effect py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white"></i>
                </div>
                <h1 class="text-2xl font-bold">Admin <span class="text-bright-orange">Panel</span></h1>
            </div>
            <nav class="hidden md:flex space-x-8">
                <a href="dashboard.php" class="font-medium text-bright-orange">Dashboard</a>
                <a href="products.php" class="font-medium hover:text-bright-orange transition-colors">Products</a>
                <a href="user.php" class="font-medium hover:text-bright-orange transition-colors">Users</a>
                <a href="bids.php" class="font-medium hover:text-bright-orange transition-colors">Bids</a>
                <a href="categories.php" class="font-medium hover:text-bright-orange transition-colors">Categories</a>
                <a href="../logout.php" class="font-medium hover:text-bright-orange transition-colors">Logout</a>
            </nav>
            <button class="md:hidden text-xl" id="mobileMenuButton"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <!-- HERO -->
    <section class="py-12 relative overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-electric-blue opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-bright-orange opacity-10 blur-3xl"></div>
        <div class="container mx-auto px-4 text-center relative z-10">
            <h2 class="text-4xl md:text-5xl font-bold mb-3">
                Welcome back, <span class="text-gradient"><?php echo htmlspecialchars($_SESSION['first_name']); ?>!</span>
            </h2>
            <p class="text-lg text-gray-300">System Overview • <?php echo date('d M Y, H:i'); ?> NL Time</p>
        </div>
    </section>

    <!-- STATS GRID -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="glass-effect p-6 rounded-xl card-hover text-center md:text-left">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-300">Total Clients</h3>
                            <p class="stat-number"><?php echo number_format($users_count); ?></p>
                        </div>
                        <i class="fas fa-users text-4xl text-electric-blue opacity-50"></i>
                    </div>
                </div>

                <div class="glass-effect p-6 rounded-xl card-hover text-center md:text-left">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-300">Total Products</h3>
                            <p class="stat-number"><?php echo number_format($products_count); ?></p>
                        </div>
                        <i class="fas fa-box text-4xl text-bright-orange opacity-50"></i>
                    </div>
                </div>

                <div class="glass-effect p-6 rounded-xl card-hover text-center md:text-left">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-300">Active Products</h3>
                            <p class="stat-number"><?php echo number_format($active_products); ?></p>
                        </div>
                        <i class="fas fa-check-circle text-4xl text-green-400 opacity-50"></i>
                    </div>
                </div>

                <div class="glass-effect p-6 rounded-xl card-hover text-center md:text-left">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-300">Total Bids</h3>
                            <p class="stat-number"><?php echo number_format($total_bids); ?></p>
                        </div>
                        <i class="fas fa-gavel text-4xl text-purple-400 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- RECENT TABLES -->
    <section class="py-8">
        <div class="container mx-auto px-4 grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- RECENT PRODUCTS -->
            <div class="glass-effect rounded-2xl p-6 card-hover">
                <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-box text-bright-orange"></i> Recent Products
                </h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM products ORDER BY datePosted DESC LIMIT 5";
                            $result = $conn->query($query);
                            
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td class="font-medium">' . htmlspecialchars($row['productName']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                                    echo '<td>' . formatCurrency($row['productPrice']) . '</td>';
                                    echo '<td><span class="status ' . $row['productStatus'] . '">' . ucfirst($row['productStatus']) . '</span></td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="4" class="text-center text-gray-400 py-4">No products found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RECENT BIDS -->
            <div class="glass-effect rounded-2xl p-6 card-hover">
                <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-gavel text-electric-blue"></i> Recent Bids
                </h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Bidder</th>
                                <th>Product</th>
                                <th>Bid Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT b.*, u.firstName, u.lastName, p.productName 
                                     FROM clients_bid_tbl b 
                                     JOIN users u ON b.bidderName = u.id 
                                     JOIN products p ON b.itemId = p.id 
                                     ORDER BY b.bidDate DESC LIMIT 5";
                            $result = $conn->query($query);
                            
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td class="font-medium">' . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['productName']) . '</td>';
                                    echo '<td>' . formatCurrency($row['bidAmount']) . '</td>';
                                    echo '<td>' . date('M d, Y', strtotime($row['bidDate'])) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="4" class="text-center text-gray-400 py-4">No bids found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-10 bg-gray-900 text-center text-gray-400 text-sm mt-12">
        <div class="container mx-auto px-4">
            <p>© 2025 Online Bidding System • Admin Panel • <?php echo date('d M Y, H:i'); ?> NL Time</p>
        </div>
    </footer>

    <!-- MOBILE MENU & ANIMATION -->
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

            const observer = new IntersectionObserver(e => e.forEach(f => {
                if (f.isIntersecting) {
                    f.target.style.opacity = 1;
                    f.target.style.transform = 'translateY(0)';
                }
            }), { threshold: 0.1 });
            document.querySelectorAll('.card-hover').forEach(c => {
                c.style.opacity = 0;
                c.style.transform = 'translateY(20px)';
                c.style.transition = 'all 0.5s ease';
                observer.observe(c);
            });
        });
    </script>
</body>
</html>