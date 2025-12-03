<?php
include '../config.php';

if (!isLoggedIn() || !isClient()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['product_id'] ?? 0;

// Get product details
$product_query = "SELECT * FROM products WHERE id = $product_id";
$product_result = $conn->query($product_query);

if ($product_result->num_rows == 0) {
    header("Location: products.php");
    exit();
}

$product = $product_result->fetch_assoc();

// Handle bid submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bid_amount = sanitize($_POST['bid_amount']);
    $bid_token = rand(100000, 999999);
    
    // Validate bid amount
    if ($bid_amount < $product['minimumBid']) {
        $error = "Bid amount must be at least " . formatCurrency($product['minimumBid']);
    } elseif ($bid_amount <= $product['productPrice']) {
        $error = "Bid amount must be higher than current price";
    } else {
        // Insert bid
        $insert_query = "INSERT INTO clients_bid_tbl (bidToken, bidderName, bidderItem, itemId, itemPrice, bidAmount, bidStatus, bidDate) 
                        VALUES ('$bid_token', '$user_id', '" . $product['productImgLoc'] . "', '$product_id', '" . $product['productPrice'] . "', '$bid_amount', 'In-progress', NOW())";
        
        if ($conn->query($insert_query)) {
            // Update product price
            $conn->query("UPDATE products SET productPrice = '$bid_amount' WHERE id = $product_id");
            $success = "Bid placed successfully!";
        } else {
            $error = "Failed to place bid: " . $conn->error;
        }
    }
}

// Image path with fallback
$imgPath = $product['productImgLoc'];
$fullPath = '../' . $imgPath;
$defaultImg = '../assets/images/default-product.jpg';
$displayImg = (!empty($imgPath) && file_exists($fullPath)) ? $imgPath : $defaultImg;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Bid - Online Bidding System</title>
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
            .input-field { @apply px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-electric-blue transition-all; }
            .countdown { @apply text-2xl font-bold text-bright-orange; }
            .countdown-label { @apply text-sm text-gray-400; }
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
                <h1 class="text-2xl font-bold">Bidding <span class="text-bright-orange">Portal</span></h1>
            </div>
            <nav class="hidden md:flex space-x-8">
                <a href="dashboard.php" class="font-medium hover:text-bright-orange transition-colors">Dashboard</a>
                <a href="products.php" class="font-medium hover:text-bright-orange transition-colors">Browse</a>
                <a href="my_bids.php" class="font-medium hover:text-bright-orange transition-colors">My Bids</a>
                <a href="add_product.php" class="font-medium hover:text-bright-orange transition-colors">Sell</a>
                <a href="profile.php" class="font-medium hover:text-bright-orange transition-colors">Profile</a>
                <a href="../logout.php" class="font-medium hover:text-bright-orange transition-colors">Logout</a>
            </nav>
            <button class="md:hidden text-xl" id="mobileMenuButton"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <!-- HERO -->
    <section class="py-12 relative overflow-hidden">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-3">
                <span class="text-gradient">Place Your Bid</span>
            </h2>
            <p class="text-lg text-gray-300">Secure your item • <?php echo date('d M Y, H:i'); ?> PH Time (UTC+8)</p>
        </div>
    </section>

    <!-- MAIN CARD -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-2xl p-6 md:p-8 card-hover max-w-5xl mx-auto">

                <!-- MESSAGES -->
                <?php if(isset($error)): ?>
                    <div class="mb-6 p-3 bg-red-500/20 border border-red-500/50 text-red-300 rounded-lg text-sm flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($success)): ?>
                    <div class="mb-6 p-3 bg-green-500/20 border border-green-500/50 text-green-300 rounded-lg text-sm flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- PRODUCT INFO + BID FORM -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <!-- LEFT: PRODUCT DETAILS -->
                    <div class="space-y-5">
                        <div class="bg-white/5 rounded-xl p-5 border border-white/10">
                            <img src="../<?php echo htmlspecialchars($displayImg); ?>" 
                                 alt="<?php echo htmlspecialchars($product['productName']); ?>"
                                 class="w-full h-64 object-cover rounded-lg shadow-lg mb-4">
                            <h3 class="text-2xl font-bold text-bright-orange"><?php echo htmlspecialchars($product['productName']); ?></h3>
                            <p class="text-sm text-gray-400 mt-1">Category: <span class="text-electric-blue"><?php echo htmlspecialchars($product['category']); ?></span></p>
                        </div>

                        <div class="bg-white/5 rounded-xl p-5 border border-white/10 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-300">Current Price</span>
                                <span class="text-xl font-bold text-bright-orange"><?php echo formatCurrency($product['productPrice']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Minimum Bid</span>
                                <span class="text-green-400 font-medium"><?php echo formatCurrency($product['minimumBid']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Bidding Ends</span>
                                <div class="text-right">
                                    <div id="countdown" class="countdown">--:--:--</div>
                                    <div class="countdown-label">until end</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/5 rounded-xl p-5 border border-white/10">
                            <p class="text-sm text-gray-300 leading-relaxed"><?php echo nl2br(htmlspecialchars($product['itemDesc'])); ?></p>
                        </div>
                    </div>

                    <!-- RIGHT: BID FORM -->
                    <div class="space-y-6">
                        <div class="bg-white/5 rounded-xl p-6 border border-white/10">
                            <h4 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <i class="fas fa-gavel text-electric-blue"></i> Enter Your Bid
                            </h4>
                            <form method="POST" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Bid Amount (PHP)</label>
                                    <input type="number" name="bid_amount" 
                                           placeholder="Enter amount..." 
                                           min="<?php echo $product['minimumBid']; ?>" 
                                           step="0.01" required 
                                           class="input-field w-full text-lg">
                                    <p class="text-xs text-gray-400 mt-1">
                                        Must be at least <?php echo formatCurrency($product['minimumBid']); ?> and higher than current price
                                    </p>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                                    <button type="submit" 
                                            class="flex-1 py-3 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-bold text-center hover:opacity-90 transition-all btn-glow flex items-center justify-center gap-2">
                                        <i class="fas fa-paper-plane"></i> Place Bid
                                    </button>
                                    <a href="products.php" 
                                       class="flex-1 py-3 bg-gray-700 rounded-lg font-medium text-center hover:bg-gray-600 transition-all flex items-center justify-center gap-2">
                                        <i class="fas fa-arrow-left"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 text-yellow-300 text-sm">
                            <p class="flex items-center gap-2">
                                <i class="fas fa-info-circle"></i>
                                <strong>Tip:</strong> Highest bid wins when auction ends. You will be notified via email.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-10 bg-gray-900 text-center text-gray-400 text-sm mt-12">
        <div class="container mx-auto px-4">
            <p>© 2025 Online Bidding System • Client Portal • <?php echo date('d M Y, H:i'); ?> PH Time (UTC+8)</p>
        </div>
    </footer>

    <!-- MOBILE MENU & COUNTDOWN -->
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

            // Live Countdown
            const endTime = new Date("<?php echo $product['productEnd']; ?>").getTime();
            const countdownEl = document.getElementById('countdown');

            const updateCountdown = () => {
                const now = new Date().getTime();
                const distance = endTime - now;

                if (distance <= 0) {
                    countdownEl.innerHTML = "EXPIRED";
                    countdownEl.classList.add('text-red-400');
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                let timeStr = "";
                if (days > 0) timeStr += `${days}d `;
                if (hours > 0 || days > 0) timeStr += `${hours}h `;
                timeStr += `${minutes}m ${seconds}s`;

                countdownEl.innerHTML = timeStr;
            };

            updateCountdown();
            setInterval(updateCountdown, 1000);

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