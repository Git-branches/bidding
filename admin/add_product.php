<?php
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Auto-create uploads directory if not exists
$upload_dir = "../uploads/products/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = sanitize($_POST['productName']);
    $category = sanitize($_POST['category']);
    $itemDesc = sanitize($_POST['itemDesc']);
    $minimumBid = sanitize($_POST['minimumBid']);
    $productPrice = sanitize($_POST['productPrice']);
    $productEnd = sanitize($_POST['productEnd']);
    $productOwner = sanitize($_POST['productOwner']);
    
    // Handle image upload
    $productImgLoc = 'products/default.jpg';
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
        $target_dir = "../uploads/products/";
        
        // Create directory if not exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["productImage"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        // Check if file is an actual image
        $check = getimagesize($_FILES["productImage"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $target_file)) {
                $productImgLoc = 'uploads/products/' . $new_filename;
                $success = "Product image uploaded successfully!";
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error = "File is not an image.";
        }
    }
    
    $query = "INSERT INTO products (productOwner, productStatus, productName, category, itemDesc, bidCount, minimumBid, productPrice, productEnd, productImgLoc) 
              VALUES ('$productOwner', 'active', '$productName', '$category', '$itemDesc', 0, '$minimumBid', '$productPrice', '$productEnd', '$productImgLoc')";
    
    if ($conn->query($query)) {
        $success = "Product added successfully!";
    } else {
        $error = "Failed to add product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Panel</title>
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
            .select-field { @apply px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-electric-blue transition-all; }
            .textarea-field { @apply px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-electric-blue resize-none h-32; }
            .file-input { @apply file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gradient-to-r file:from-electric-blue file:to-bright-orange file:text-white hover:file:opacity-90 cursor-pointer; }
            .preview-img { @apply w-full max-w-xs h-40 object-cover rounded-lg shadow-md border border-white/20; }
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
                <a href="../index.php" class="font-medium hover:text-bright-orange transition-colors">Home</a>
                <a href="dashboard.php" class="font-medium hover:text-bright-orange transition-colors">Dashboard</a>
                <a href="products.php" class="font-medium text-bright-orange">Products</a>
                <a href="users.php" class="font-medium hover:text-bright-orange transition-colors">Users</a>
                <a href="bids.php" class="font-medium hover:text-bright-orange transition-colors">Bids</a>
                <a href="categories.php" class="font-medium hover:text-bright-orange transition-colors">Categories</

a>
                <a href="../logout.php" class="font-medium hover:text-bright-orange transition-colors">Logout</a>
            </nav>
            <button class="md:hidden text-xl" id="mobileMenuButton"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <!-- HERO -->
    <section class="py-12 relative overflow-hidden">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-3">
                <span class="text-gradient">Add New Product</span>
            </h2>
            <p class="text-lg text-gray-300">List a new item for bidding • <?php echo date('d M Y, H:i'); ?> NL Time (CET)</p>
        </div>
    </section>

    <!-- MAIN CARD -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-2xl p-6 md:p-8 card-hover max-w-4xl mx-auto">

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

                <!-- FORM -->
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- LEFT COLUMN -->
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Product Name</label>
                                <input type="text" name="productName" placeholder="Enter product name..." required 
                                       class="input-field w-full">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                                <select name="category" required class="select-field w-full">
                                    <option value="">Select Category</option>
                                    <?php
                                    $categories = $conn->query("SELECT * FROM categories");
                                    while($cat = $categories->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($cat['title']) . '">' . htmlspecialchars($cat['title']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Product Owner</label>
                                <select name="productOwner" required class="select-field w-full">
                                    <option value="">Select Owner</option>
                                    <?php
                                    $users = $conn->query("SELECT * FROM users WHERE userType = 2");
                                    while($user = $users->fetch_assoc()) {
                                        echo '<option value="' . $user['id'] . '">' . htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Minimum Bid</label>
                                <input type="number" name="minimumBid" placeholder="0.00" step="0.01" required 
                                       class="input-field w-full">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Starting Price</label>
                                <input type="number" name="productPrice" placeholder="0.00" step="0.01" required 
                                       class="input-field w-full">
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Bidding End Date & Time</label>
                                <input type="datetime-local" name="productEnd" required 
                                       class="input-field w-full">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Product Description</label>
                                <textarea name="itemDesc" placeholder="Describe the product..." required 
                                          class="textarea-field w-full"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Product Image</label>
                                <input type="file" name="productImage" accept="image/*" class="file-input w-full">
                                <p class="text-xs text-gray-400 mt-1">Supported: JPG, JPEG, PNG, GIF</p>
                                
                                <!-- LIVE PREVIEW -->
                                <div id="imagePreview" class="hidden mt-3 p-3 bg-white/5 rounded-lg border border-white/10">
                                    <p class="text-xs text-gray-400 mb-2">Preview:</p>
                                    <img id="previewImg" src="" alt="Preview" class="preview-img">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-6">
                        <button type="submit" 
                                class="flex-1 py-3 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-bold text-center hover:opacity-90 transition-all btn-glow flex items-center justify-center gap-2">
                            <i class="fas fa-plus-circle"></i> Add Product
                        </button>
                        <a href="products.php" 
                           class="flex-1 py-3 bg-gray-700 rounded-lg font-medium text-center hover:bg-gray-600 transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-10 bg-gray-900 text-center text-gray-400 text-sm mt-12">
        <div class="container mx-auto px-4">
            <p>© 2025 Online Bidding System • Admin Panel • <?php echo date('d M Y, H:i'); ?> NL Time (CET)</p>
        </div>
    </footer>

    <!-- MOBILE MENU & IMAGE PREVIEW -->
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

            // Live Image Preview
            const fileInput = document.querySelector('input[name="productImage"]');
            const previewContainer = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');

            fileInput?.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewContainer.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.classList.add('hidden');
                }
            });

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