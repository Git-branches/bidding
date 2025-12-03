<?php
include '../config.php';

if (!isLoggedIn() || !isClient()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $productName = sanitize($_POST['productName']);
    $category = sanitize($_POST['category']);
    $itemDesc = sanitize($_POST['itemDesc']);
    $minimumBid = floatval($_POST['minimumBid']);
    $productPrice = floatval($_POST['productPrice']);
    $productEnd = sanitize($_POST['productEnd']);

    // Validate required fields
    if (empty($productName) || empty($category) || empty($itemDesc) || $minimumBid <= 0 || $productPrice <= 0 || empty($productEnd)) {
        $error = "All fields are required and must be valid.";
    } else {
        // Handle image upload
        $productImgLoc = 'products/default.jpg';
        if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
            $target_dir = "../uploads/products/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

            $imageFileType = strtolower(pathinfo($_FILES["productImage"]["name"], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($imageFileType, $allowed)) {
                $error = "Only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
            } elseif ($_FILES["productImage"]["size"] > 5 * 1024 * 1024) {
                $error = "Image must be less than 5MB.";
            } else {
                $new_filename = uniqid('prod_') . '.' . $imageFileType;
                $target_file = $target_dir . $new_filename;
                if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $target_file)) {
                    $productImgLoc = 'uploads/products/' . $new_filename;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }

        if (empty($error)) {
            // Use prepared statement (SQL Injection Safe)
            // FIXED: Count the number of parameters correctly
            $stmt = $conn->prepare("INSERT INTO products 
                (productOwner, productStatus, productName, category, itemDesc, bidCount, minimumBid, productPrice, productEnd, productImgLoc) 
                VALUES (?, 'active', ?, ?, ?, 0, ?, ?, ?, ?)");
            
            // FIXED: Correct number of parameters - 8 parameters total
            // Types: i (user_id), s (productName), s (category), s (itemDesc), d (minimumBid), d (productPrice), s (productEnd), s (productImgLoc)
            $stmt->bind_param("isssddss", $user_id, $productName, $category, $itemDesc, $minimumBid, $productPrice, $productEnd, $productImgLoc);

            if ($stmt->execute()) {
                $success = "Product listed successfully!";
                // Clear form fields on success
                $_POST = array();
            } else {
                $error = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Get categories for dropdown
$categories = [];
$cat_result = $conn->query("SELECT title FROM categories ORDER BY title");
if ($cat_result) {
    while ($cat = $cat_result->fetch_assoc()) {
        $categories[] = $cat['title'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Product - Online Bidding System</title>

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
            .input-focus:focus {
                @apply ring-2 ring-bright-orange outline-none;
            }
            .preview-img {
                @apply w-full h-full object-cover rounded-xl border-2 border-dashed border-gray-600;
            }
            .form-input {
                @apply w-full px-4 py-3 bg-gray-800 rounded-xl input-focus text-white placeholder-gray-400;
            }
        }
    </style>
</head>
<body class="bg-deep-blue text-white min-h-screen">

    <!-- HEADER -->
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
                    <a href="my_bids.php" class="font-medium hover:text-bright-orange transition-colors duration-300">My Bids</a>
                    <a href="add_product.php" class="font-medium text-bright-orange">Sell</a>
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
                    Sell Your <span class="text-gradient">Product</span>
                </h2>
                <p class="text-xl text-gray-300">List your item and start receiving bids instantly</p>
            </div>
        </div>
    </section>

    <!-- HORIZONTAL CARD FORM SECTION -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-2xl p-6 md:p-8 card-hover max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- LEFT COLUMN: Image Upload -->
                    <div class="flex flex-col space-y-6">
                        <div class="text-center lg:text-left">
                            <h3 class="text-2xl font-bold mb-2">Product Image</h3>
                            <p class="text-gray-400 mb-4">Upload a clear photo of your product</p>
                        </div>

                        <!-- Image Upload with Preview -->
                        <div class="flex-1">
                            <input type="file" name="productImage" accept="image/*" id="productImage" class="hidden">
                            <div class="relative h-64 lg:h-80">
                                <img id="imagePreview" src="../assets/img/default-product.jpg" alt="Preview" class="preview-img">
                                <label for="productImage" class="absolute inset-0 flex items-center justify-center cursor-pointer bg-black/30 hover:bg-black/50 rounded-xl transition">
                                    <div class="text-center">
                                        <i class="fas fa-camera text-4xl text-white mb-3"></i>
                                        <p class="text-lg font-medium text-white">Click to upload image</p>
                                        <p class="text-sm text-gray-300 mt-1">JPG, PNG, GIF, WEBP • Max 5MB</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Quick Tips -->
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                            <h4 class="font-bold text-bright-orange mb-2 flex items-center gap-2">
                                <i class="fas fa-lightbulb"></i>
                                Quick Tips
                            </h4>
                            <ul class="text-sm text-gray-300 space-y-1">
                                <li>• Use clear, well-lit photos</li>
                                <li>• Show multiple angles</li>
                                <li>• Include any defects or damage</li>
                                <li>• Set realistic starting prices</li>
                            </ul>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Form Fields -->
                    <div class="space-y-6">
                        <div class="text-center lg:text-left">
                            <h3 class="text-2xl font-bold mb-2">Product Details</h3>
                            <p class="text-gray-400">Fill in all required information</p>
                        </div>

                        <!-- Success / Error Alerts -->
                        <?php if ($success): ?>
                            <div class="p-4 bg-green-500/20 border border-green-500/50 text-green-300 rounded-xl text-center">
                                <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="p-4 bg-red-500/20 border border-red-500/50 text-red-300 rounded-xl text-center">
                                <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" class="space-y-5">
                            <!-- Product Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Product Name <span class="text-red-400">*</span>
                                </label>
                                <input type="text" name="productName" value="<?= htmlspecialchars($_POST['productName'] ?? '') ?>" required 
                                       class="form-input" placeholder="e.g. iPhone 15 Pro Max 256GB">
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Category <span class="text-red-400">*</span>
                                </label>
                                <select name="category" required class="form-input">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>" <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Description <span class="text-red-400">*</span>
                                </label>
                                <textarea name="itemDesc" required rows="3" 
                                          class="form-input resize-none" 
                                          placeholder="Describe your product features, condition, and any important details..."><?= htmlspecialchars($_POST['itemDesc'] ?? '') ?></textarea>
                            </div>

                            <!-- Price Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Starting Price (₱) <span class="text-red-400">*</span>
                                    </label>
                                    <input type="number" name="productPrice" step="0.01" min="0.01" 
                                           value="<?= htmlspecialchars($_POST['productPrice'] ?? '') ?>" required 
                                           class="form-input" placeholder="1000.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Minimum Bid (₱) <span class="text-red-400">*</span>
                                    </label>
                                    <input type="number" name="minimumBid" step="0.01" min="0.01" 
                                           value="<?= htmlspecialchars($_POST['minimumBid'] ?? '') ?>" required 
                                           class="form-input" placeholder="100.00">
                                </div>
                            </div>

                            <!-- End Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Bidding Ends <span class="text-red-400">*</span>
                                </label>
                                <input type="datetime-local" name="productEnd" 
                                       value="<?= htmlspecialchars($_POST['productEnd'] ?? '') ?>" required 
                                       class="form-input">
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="w-full py-4 bg-gradient-to-r from-electric-blue to-bright-orange rounded-xl font-bold text-lg hover:opacity-90 transition-all duration-300 btn-glow flex items-center justify-center gap-3">
                                <i class="fas fa-rocket"></i>
                                List Product for Bidding
                            </button>

                            <p class="text-xs text-gray-400 text-center">
                                By listing your product, you agree to our terms of service
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-12 bg-gray-900 mt-16">
        <div class="container mx-auto px-4">
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>© 2025 Online Bidding System. All rights reserved.</p>
            </div>
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

            // Image Preview
            const input = document.getElementById('productImage');
            const preview = document.getElementById('imagePreview');
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Set minimum datetime to current time
            const now = new Date();
            const timezoneOffset = now.getTimezoneOffset() * 60000; // Offset in milliseconds
            const localTime = new Date(now.getTime() - timezoneOffset);
            const minDateTime = localTime.toISOString().slice(0, 16);
            
            const dateInput = document.querySelector('input[type="datetime-local"]');
            if (dateInput) {
                dateInput.min = minDateTime;
                
                // Set default value to 7 days from now if empty
                if (!dateInput.value) {
                    const futureDate = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
                    const futureLocalTime = new Date(futureDate.getTime() - timezoneOffset);
                    dateInput.value = futureLocalTime.toISOString().slice(0, 16);
                }
            }

            // Animate form card
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