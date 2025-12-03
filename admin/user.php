<?php
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'delete' && $user_id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $user_id");
        $message = "User deleted successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
            .user-type { @apply px-2 py-1 rounded-full text-xs font-medium; }
            .user-type.admin { @apply bg-purple-500/20 text-purple-300; }
            .user-type.client { @apply bg-blue-500/20 text-blue-300; }
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
                <a href="users.php" class="font-medium text-bright-orange">Users</a>
                <a href="bids.php" class="font-medium hover:text-bright-orange transition-colors">Bids</a>
                <a href="categories.php" class="font-medium hover:text-bright-orange transition-colors">Categories</a>
                <a href="../logout.php" class="font-medium hover:text-bright-orange transition-colors">Logout</a>
            </nav>
            <button class="md:hidden text-xl" id="mobileMenuButton"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <!-- HERO -->
    <section class="py-12 relative overflow-hidden">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-3">
                <span class="text-gradient">Manage Users</span>
            </h2>
            <p class="text-lg text-gray-300">User Directory • <?php echo date('d M Y, H:i'); ?> NL Time (CET)</p>
        </div>
    </section>

    <!-- MAIN CARD -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-2xl p-6 md:p-8 card-hover max-w-7xl mx-auto">

                <!-- SUCCESS MESSAGE -->
                <?php if(isset($message)): ?>
                    <div class="mb-5 p-3 bg-green-500/20 border border-green-500/50 text-green-300 rounded-lg text-sm flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- TABLE -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Balance</th>
                                <th>User Type</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM users ORDER BY date_created DESC";
                            $result = $conn->query($query);
                            
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td class="font-medium">' . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['contact']) . '</td>';
                                    echo '<td class="text-bright-orange font-bold">' . formatCurrency(getUserMoney($row['userMoney'])) . '</td>';
                                    echo '<td><span class="user-type ' . ($row['userType'] == 1 ? 'admin' : 'client') . '">' . ($row['userType'] == 1 ? 'Admin' : 'Client') . '</span></td>';
                                    echo '<td>' . date('M d, Y', strtotime($row['date_created'])) . '</td>';
                                    echo '<td>';
                                    if ($row['id'] != $_SESSION['user_id']) {
                                        echo '<a href="?action=delete&id=' . $row['id'] . '" class="btn-small bg-red-600 text-white" onclick="return confirm(\'Are you sure?\')"><i class="fas fa-trash"></i> Delete</a>';
                                    } else {
                                        echo '<span class="text-gray-400 text-xs"><i class="fas fa-user-shield"></i> Current User</span>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="7" class="text-center text-gray-400 py-8">No users found</td></tr>';
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