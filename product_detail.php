<?php
include 'config.php';

$product_id = $_GET['id'] ?? 0;

// Use prepared statement for security
$stmt = $conn->prepare("SELECT p.*, u.firstName, u.lastName, u.contact as sellerContact 
                       FROM products p 
                       JOIN users u ON p.productOwner = u.id 
                       WHERE p.id = ? AND p.productStatus = 'active'");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();

if ($product_result->num_rows == 0) {
    header("Location: products.php");
    exit();
}

$product = $product_result->fetch_assoc();

// Get bids
$bids_stmt = $conn->prepare("SELECT b.*, u.firstName, u.lastName 
                            FROM clients_bid_tbl b 
                            JOIN users u ON b.bidderName = u.id 
                            WHERE b.itemId = ? 
                            ORDER BY b.bidAmount DESC 
                            LIMIT 10");
$bids_stmt->bind_param("i", $product_id);
$bids_stmt->execute();
$bids_result = $bids_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['productName']); ?> - Online Bidding System</title>
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
                    fontFamily: { 'inter': ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer base { html { scroll-behavior: smooth; } body { font-family: 'Inter', sans-serif; } }
        @layer utilities {
            .glass-effect { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
            .text-gradient { background: linear-gradient(90deg, #3b82f6, #f97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
            .card-hover { transition: all 0.3s ease; }
            .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
            .btn-glow:hover { box-shadow: 0 0 15px rgba(249,115,22,0.5); }
            .countdown { @apply text-3xl font-bold text-bright-orange; }
            .table-container { @apply overflow-x-auto; }
            table { @apply w-full text-sm; }
            th { @apply px-3 py-2 text-xs font-medium text-gray-300 uppercase bg-white/5; }
            td { @apply px-3 py-2 border-t border-white/10; }
            .status { @apply px-2 py-1 rounded-full text-xs font-medium; }
            .status.winning { @apply bg-green-500/20 text-green-300; }
            .status.losing { @apply bg-red-500/20 text-red-300; }
            .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 1000; backdrop-filter: blur(5px); align-items: center; justify-content: center; }
            .modal.active { display: flex; }
            .modal-content { transform: scale(0.7); opacity: 0; transition: all 0.3s ease; }
            .modal.active .modal-content { transform: scale(1); opacity: 1; }
            .modal-content-register { max-width: 420px; width: 90vw; }
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

    <!-- HERO -->
    <section class="py-12 relative overflow-hidden">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-3">
                <span class="text-gradient"><?php echo htmlspecialchars($product['productName']); ?></span>
            </h2>
            <p class="text-lg text-gray-300">Bidding ends in <span id="countdown" class="countdown">Loading...</span></p>
        </div>
    </section>

    <!-- PRODUCT CARD -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-2xl p-6 md:p-8 card-hover max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Image -->
                    <div class="lg:col-span-1">
                        <img src="<?php echo htmlspecialchars($product['productImgLoc']); ?>" 
                             alt="<?php echo htmlspecialchars($product['productName']); ?>" 
                             class="w-full h-96 object-cover rounded-xl shadow-lg">
                    </div>

                    <!-- Info -->
                    <div class="lg:col-span-1 space-y-5">
                        <div>
                            <p class="text-sm text-gray-400">Category</p>
                            <p class="text-lg font-medium"><?php echo htmlspecialchars($product['category']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Current Price</p>
                            <p class="text-3xl font-bold text-bright-orange"><?php echo formatCurrency($product['productPrice']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Minimum Bid</p>
                            <p class="text-xl font-medium"><?php echo formatCurrency($product['minimumBid']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Seller</p>
                            <p class="font-medium"><?php echo htmlspecialchars($product['firstName'] . ' ' . $product['lastName']); ?></p>
                            <p class="text-xs text-gray-400"><?php echo htmlspecialchars($product['sellerContact']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Bidding Ends</p>
                            <p class="text-lg font-medium"><?php echo date('M d, Y H:i', strtotime($product['productEnd'])); ?></p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="lg:col-span-1 flex flex-col justify-center space-y-4">
                        <?php if(isLoggedIn() && isClient()): ?>
                            <a href="client/bid.php?product_id=<?php echo $product['id']; ?>" 
                               class="block w-full py-3 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-bold text-center hover:opacity-90 btn-glow">
                                <i class="fas fa-gavel mr-2"></i> Place Bid
                            </a>
                        <?php elseif(!isLoggedIn()): ?>
                            <button onclick="openModal('loginModal')" 
                                    class="block w-full py-3 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-bold text-center hover:opacity-90 btn-glow">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login to Bid
                            </button>
                        <?php endif; ?>
                        <a href="products.php" 
                           class="block w-full py-3 glass-effect rounded-lg font-medium text-center hover:bg-white/20 transition-all">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DESCRIPTION -->
    <section class="py-8">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="glass-effect rounded-2xl p-6 card-hover">
                <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-electric-blue"></i> Product Description
                </h3>
                <p class="text-gray-300 leading-relaxed"><?php echo nl2br(htmlspecialchars($product['itemDesc'])); ?></p>
            </div>
        </div>
    </section>

    <!-- BIDS HISTORY -->
    <section class="py-8">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="glass-effect rounded-2xl p-6 card-hover">
                <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-gavel text-bright-orange"></i> Recent Bids
                </h3>
                <?php if($bids_result->num_rows > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Bidder</th>
                                    <th>Bid Amount</th>
                                    <th>Bid Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $highest_bid = $bids_result->num_rows > 0 ? $bids_result->fetch_assoc()['bidAmount'] : 0;
                                $bids_result->data_seek(0);
                                while($bid = $bids_result->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td class="font-medium"><?php echo htmlspecialchars($bid['firstName'] . ' ' . $bid['lastName']); ?></td>
                                        <td class="font-bold text-bright-orange"><?php echo formatCurrency($bid['bidAmount']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($bid['bidDate'])); ?></td>
                                        <td>
                                            <span class="status <?php echo $bid['bidAmount'] == $highest_bid ? 'winning' : 'losing'; ?>">
                                                <?php echo $bid['bidAmount'] == $highest_bid ? 'Leading' : 'Outbid'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-400 py-8">No bids yet. <strong>Be the first to bid!</strong></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

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
                    <p class="text-gray-400 mt-2">Sign in to place your bid</p>
                </div>
                <form action="login.php" method="POST" class="space-y-4">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-3 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange">
                    </div>
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange">
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-semibold hover:opacity-90 btn-glow">
                        Sign In
                    </button>
                </form>
                <div class="text-center mt-6">
                    <p class="text-gray-400">Don't have an account? 
                        <button type="button" onclick="switchToRegister()" class="text-bright-orange hover:underline font-medium">Sign up</button>
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
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
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
                        <button type="button" onclick="switchToLogin()" class="text-bright-orange hover:underline font-medium">Sign in</button>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        // Modal Functions
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
            if (e.target.classList.contains('modal')) {
                closeModal(e.target.id);
            }
        });

        // Countdown
        const endTime = new Date("<?php echo $product['productEnd']; ?>").getTime();
        const countdownEl = document.getElementById('countdown');

        function updateCountdown() {
            const now = Date.now();
            const diff = (endTime - now) / 1000;

            if (diff <= 0) {
                countdownEl.innerHTML = '<span class="text-red-400">Ended!</span>';
                return;
            }

            const days = Math.floor(diff / 86400);
            const hours = Math.floor((diff % 86400) / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = Math.floor(diff % 60);

            let html = '';
            if (days > 0) html += `${days}d `;
            html += `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            countdownEl.innerHTML = html;
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Mobile Menu
        let isOpen = false;
        const mobileBtn = document.getElementById('mobileMenuButton');
        const nav = document.querySelector('nav.hidden.md\\:flex');
        mobileBtn?.addEventListener('click', () => {
            isOpen = !isOpen;
            nav.classList.toggle('hidden', !isOpen);
            if (isOpen) {
                ['absolute', 'top-16', 'left-0', 'right-0', 'bg-deep-blue', 'p-6', 'flex-col', 'space-y-4'].forEach(cls => nav.classList.add(cls));
            } else {
                ['absolute', 'top-16', 'left-0', 'right-0', 'bg-deep-blue', 'p-6', 'flex-col', 'space-y-4'].forEach(cls => nav.classList.remove(cls));
            }
        });
    </script>
</body>
</html>