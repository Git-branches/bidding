<?php
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        $title = sanitize($_POST['title']);
        $conn->query("INSERT INTO categories (title) VALUES ('$title')");
        $message = "Category added successfully!";
    }
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $category_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        $conn->query("DELETE FROM categories WHERE id = $category_id");
        $message = "Category deleted successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Panel</title>
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
            .table-container { @apply overflow-x-auto; }
            table { @apply w-full text-sm; }
            th { @apply px-3 py-2 text-xs font-medium text-gray-300 uppercase bg-white/5; }
            td { @apply px-3 py-2 border-t border-white/10; }
            .btn-small { @apply px-3 py-1 text-xs rounded-lg font-medium transition-all; }
            .btn-small:hover { @apply opacity-80; }
            .input-field { @apply px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-electric-blue; }
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
                <a href="dashboard.php" class="font-medium hover:text-bright-orange transition-colors">Dashboard</a>
                <a href="products.php" class="font-medium hover:text-bright-orange transition-colors">Products</a>
                <a href="user.php" class="font-medium hover:text-bright-orange transition-colors">Users</a>
                <a href="bids.php" class="font-medium hover:text-bright-orange transition-colors">Bids</a>
                <a href="categories.php" class="font-medium text-bright-orange">Categories</a>
                <a href="../logout.php" class="font-medium hover:text-bright-orange transition-colors">Logout</a>
            </nav>
            <button class="md:hidden text-xl" id="mobileMenuButton"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <!-- HERO -->
    <section class="py-12 relative overflow-hidden">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-3">
                <span class="text-gradient">Manage Categories</span>
            </h2>
            <p class="text-lg text-gray-300">Organize your product catalog • <?php echo date('d M Y, H:i'); ?> NL Time (CET)</p>
        </div>
    </section>

    <!-- MAIN CARD -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-2xl p-6 md:p-8 card-hover max-w-4xl mx-auto">

                <!-- SUCCESS MESSAGE -->
                <?php if(isset($message)): ?>
                    <div class="mb-6 p-3 bg-green-500/20 border border-green-500/50 text-green-300 rounded-lg text-sm flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- ADD CATEGORY FORM -->
                <div class="mb-8 p-5 bg-white/5 rounded-xl border border-white/10">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-electric-blue"></i> Add New Category
                    </h3>
                    <form method="POST" class="flex flex-col sm:flex-row gap-3">
                        <input type="text" name="title" placeholder="Enter category name..." required 
                               class="input-field flex-1">
                        <button type="submit" name="add_category" 
                                class="px-5 py-2 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-bold text-sm hover:opacity-90 transition-all btn-glow flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </form>
                </div>

                <!-- CATEGORIES TABLE -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM categories ORDER BY title";
                            $result = $conn->query($query);
                            
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td class="text-center font-mono text-xs text-gray-400">' . $row['id'] . '</td>';
                                    echo '<td class="font-medium">' . htmlspecialchars($row['title']) . '</td>';
                                    echo '<td>';
                                    echo '<a href="?action=delete&id=' . $row['id'] . '" class="btn-small bg-red-600 text-white" onclick="return confirm(\'Are you sure?\')"><i class="fas fa-trash"></i> Delete</a>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="3" class="text-center text-gray-400 py-8">No categories found</td></tr>';
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
            <p>© 2025 Online Bidding System • Admin Panel • <?php echo date('d M Y, H:i'); ?> NL Time (CET)</p>
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