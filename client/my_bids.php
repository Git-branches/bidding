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
    <title>My Bids - Online Bidding System</title>

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
            .status.lost { @apply bg-red-500/20 text-red-300; }
            .status.active { @apply bg-bright-orange text-white; }
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
                    <a href="dashboard.php" class="font-medium hover:text-bright-orange transition-colors duration-300">Dashboard</a>
                    <a href="products.php" class="font-medium hover:text-bright-orange transition-colors duration-300">Browse</a>
                    <a href="my_bids.php" class="font-medium text-bright-orange">My Bids</a>
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

    <!-- HERO SECTION -->
    <section class="hero py-16 md:py-20 relative overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-electric-blue opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-bright-orange opacity-10 blur-3xl"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    My <span class="text-gradient">Bids</span>
                </h2>
                <p class="text-xl text-gray-300">Track all your bidding activity in one place</p>
            </div>
        </div>
    </section>

    <!-- BIDS TABLE -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-2xl overflow-hidden card-hover">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white/5 border-b border-white/10">
                            <tr>
                                <th class="text-left py-4 px-6 font-medium">Product</th>
                                <th class="text-right py-4 px-6 font-medium">My Bid</th>
                                <th class="text-right py-4 px-6 font-medium">Current Price</th>
                                <th class="text-center py-4 px-6 font-medium">Status</th>
                                <th class="text-center py-4 px-6 font-medium">Bid Date</th>
                                <th class="text-center py-4 px-6 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT b.*, p.productName, p.productPrice, p.productImgLoc, p.productEnd 
                                     FROM clients_bid_tbl b 
                                     JOIN products p ON b.itemId = p.id 
                                     WHERE b.bidderName = ? 
                                     ORDER BY b.bidDate DESC";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $status_class = strtolower($row['bidStatus']);
                                    $status_class = $status_class === 'in-progress' ? 'in-progress' : $status_class;
                                    ?>
                                    <tr class="border-b border-white/5 hover:bg-white/5 transition">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <img src="<?= htmlspecialchars($row['productImgLoc']) ?>" alt="<?= htmlspecialchars($row['productName']) ?>" class="w-12 h-12 rounded-lg object-cover">
                                                <div>
                                                    <p class="font-medium"><?= htmlspecialchars($row['productName']) ?></p>
                                                    <p class="text-xs text-gray-400">Ends: <?= date('M d, Y H:i', strtotime($row['productEnd'])) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-right py-4 px-6 font-medium text-bright-orange">
                                            <?= formatCurrency($row['bidAmount']) ?>
                                        </td>
                                        <td class="text-right py-4 px-6 text-gray-300">
                                            <?= formatCurrency($row['productPrice']) ?>
                                        </td>
                                        <td class="text-center py-4 px-6">
                                            <span class="status <?= $status_class ?>">
                                                <?= ucfirst(str_replace('-', ' ', $row['bidStatus'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-center py-4 px-6 text-sm text-gray-400">
                                            <?= date('M d, Y H:i', strtotime($row['bidDate'])) ?>
                                        </td>
                                        <td class="text-center py-4 px-6">
                                            <?php if ($row['bidStatus'] === 'In-progress'): ?>
                                                <a href="bid.php?product_id=<?= $row['itemId'] ?>" class="inline-block px-4 py-2 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-medium text-sm hover:opacity-90 transition-all duration-300 btn-glow">
                                                    Bid Again
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-500 text-xs">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center py-16 text-gray-400">
                                        <i class="fas fa-gavel text-5xl mb-4 text-gray-500"></i>
                                        <p class="text-lg">You haven't placed any bids yet.</p>
                                        <a href="products.php" class="text-bright-orange hover:underline text-sm mt-2 inline-block">Browse Products →</a>
                                    </td>
                                </tr>
                                <?php
                            }
                            $stmt->close();
                            ?>
                        </tbody>
                    </table>
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

    <!-- MOBILE MENU + ANIMATION SCRIPT -->
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

            // Animate table card on scroll
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