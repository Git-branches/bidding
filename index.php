<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bidding System</title>
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
            html {
                scroll-behavior: smooth;
            }
            body {
                font-family: 'Inter', sans-serif;
            }
        }
        
        @layer utilities {
            .glass-effect {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
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
            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                z-index: 1000;
                backdrop-filter: blur(5px);
            }
            .modal.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .modal-content {
                transform: scale(0.7);
                opacity: 0;
                transition: all 0.3s ease;
            }
            .modal.active .modal-content {
                transform: scale(1);
                opacity: 1;
            }

            .modal-content-register {
                max-width: 420px;
                width: 90vw;
            }

            /* === LOGO ANIMATIONS === */
            @keyframes spin-slow {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }

            @keyframes pulse-glow {
                0%, 100% {
                    box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.7);
                }
                50% {
                    box-shadow: 0 0 20px 10px rgba(249, 115, 22, 0);
                }
            }

            @keyframes bounce-gavel {
                0%, 100% {
                    transform: rotate(0deg) translateY(0);
                }
                25% {
                    transform: rotate(-15deg) translateY(-2px);
                }
                50% {
                    transform: rotate(0deg) translateY(0);
                }
                75% {
                    transform: rotate(15deg) translateY(-2px);
                }
            }

            @keyframes slide-in {
                from {
                    opacity: 0;
                    transform: translateX(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            .logo-container {
                animation: slide-in 0.8s ease-out;
            }

            .logo-circle {
                animation: pulse-glow 2s ease-in-out infinite;
                transition: all 0.3s ease;
            }

            .logo-container:hover .logo-circle {
                animation: spin-slow 3s linear infinite, pulse-glow 2s ease-in-out infinite;
            }

            .logo-icon {
                transition: all 0.3s ease;
            }

            .logo-container:hover .logo-icon {
                animation: bounce-gavel 0.6s ease-in-out;
            }

            .logo-text {
                transition: all 0.3s ease;
            }

            .logo-container:hover .logo-text {
                letter-spacing: 0.05em;
                transform: translateX(5px);
            }

            .logo-text .text-bright-orange {
                transition: all 0.3s ease;
                display: inline-block;
            }

            .logo-container:hover .logo-text .text-bright-orange {
                transform: scale(1.1);
            }

            /* === HERO TYPING ANIMATION === */
            @keyframes blink {
                0%, 100% { opacity: 1; }
                50% { opacity: 0; }
            }

            .typing-text {
                overflow: hidden;
                white-space: nowrap;
                border-right: 3px solid #f97316;
                animation: blink 0.7s step-end infinite;
                display: inline-block;
                min-width: 2px;
                background: linear-gradient(90deg, #3b82f6, #f97316);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                font-weight: bold;
            }

            .typing-container {
                display: inline-block;
                min-height: 1.2em;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .hero-subtitle {
                animation: fadeInUp 1s ease-out 2s both;
            }
        }
    </style>
</head>
<body class="bg-deep-blue text-white font-inter">
    <!-- Header with glassmorphism effect -->
    <header class="sticky top-0 z-50 glass-effect py-4">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <!-- Logo with Animation -->
                <div class="flex items-center space-x-2 logo-container cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center logo-circle">
                        <i class="fas fa-gavel text-white logo-icon"></i>
                    </div>
                    <h1 class="text-2xl font-bold logo-text">Project Bidding  <span class="text-bright-orange">Procurement</span></h1>
                </div>

                <!-- Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <a href="index.php" class="font-medium hover:text-bright-orange transition-colors duration-300">Home</a>
                    <a href="products.php" class="font-medium hover:text-bright-orange transition-colors duration-300">Products</a>
                    <?php if(isLoggedIn()): ?>
                        <?php if(isAdmin()): ?>
                            <a href="admin/dashboard.php" class="font-medium hover:text-bright-orange transition-colors duration-300">Admin Dashboard</a>
                        <?php else: ?>
                            <a href="client/dashboard.php" class="font-medium hover:text-bright-orange transition-colors duration-300">My Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php" class="font-medium hover:text-bright-orange transition-colors duration-300">Logout</a>
                    <?php else: ?>
                        <button onclick="openModal('loginModal')" class="font-medium hover:text-bright-orange transition-colors duration-300">Login</button>
                        <button onclick="openModal('registerModal')" class="font-medium hover:text-bright-orange transition-colors duration-300">Register</button>
                    <?php endif; ?>
                </nav>

                <!-- Mobile menu button -->
                <button class="md:hidden text-xl" id="mobileMenuButton">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero py-20 md:py-28 relative overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-electric-blue opacity-10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-bright-orange opacity-10 blur-3xl"></div>
            
            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-4xl md:text-6xl font-bold mb-6">
                        Online <span class="text-gradient typing-container"><span id="typingText" class="typing-text">Bidding System</span></span>
                    </h2>
                    <p class="text-xl text-gray-300 mb-10 max-w-2xl mx-auto hero-subtitle">
                        Bid on amazing products and win great deals!
                    </p>
                    <?php if(!isLoggedIn()): ?>
                        <div class="cta-buttons flex flex-col sm:flex-row justify-center gap-4">
                            <button onclick="openModal('registerModal')" class="px-8 py-4 bg-gradient-to-r from-electric-blue to-bright-orange rounded-xl font-semibold text-lg hover:opacity-90 transition-all duration-300 btn-glow flex items-center justify-center gap-2">
                                <i class="fas fa-rocket"></i>
                                Get Started
                            </button>
                            <a href="products.php" class="px-8 py-4 glass-effect rounded-xl font-semibold text-lg hover:bg-white hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center gap-2">
                                <i class="fas fa-search"></i>
                                Browse Products
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-16 md:py-24 bg-gradient-to-b from-deep-blue to-gray-900">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">How <span class="text-bright-orange">Online Bidding</span> Works</h2>
                    <p class="text-gray-400 max-w-2xl mx-auto">Our streamlined process makes bidding simple and secure.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div class="glass-effect rounded-2xl p-6 text-center card-hover">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center text-white text-xl font-bold mx-auto mb-4">1</div>
                        <h3 class="text-xl font-bold mb-3">Register & Verify</h3>
                        <p class="text-gray-400">Create your account and complete verification.</p>
                    </div>
                    <div class="glass-effect rounded-2xl p-6 text-center card-hover">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center text-white text-xl font-bold mx-auto mb-4">2</div>
                        <h3 class="text-xl font-bold mb-3">Browse & Bid</h3>
                        <p class="text-gray-400">Explore auctions and place your bids.</p>
                    </div>
                    <div class="glass-effect rounded-2xl p-6 text-center card-hover">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center text-white text-xl font-bold mx-auto mb-4">3</div>
                        <h3 class="text-xl font-bold mb-3">Win & Complete</h3>
                        <p class="text-gray-400">Win the bid and receive your item securely.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="py-16 md:py-24">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">Featured <span class="text-bright-orange">Products</span></h2>
                    <p class="text-gray-400 max-w-2xl mx-auto">Don't miss out on these hot items.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php
                    $query = "SELECT * FROM products WHERE productStatus = 'active' ORDER BY datePosted DESC LIMIT 3";
                    $result = $conn->query($query);
                    
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<div class="glass-effect rounded-2xl overflow-hidden card-hover">';
                            echo '<div class="relative">';
                            echo '<img src="' . $row['productImgLoc'] . '" alt="' . $row['productName'] . '" class="w-full h-56 object-cover">';
                            echo '<div class="absolute top-4 right-4 bg-bright-orange text-white px-3 py-1 rounded-full text-sm font-medium">';
                            echo '<i class="fas fa-bolt mr-1"></i> Active';
                            echo '</div></div>';
                            echo '<div class="p-6">';
                            echo '<h3 class="text-xl font-bold mb-2">' . $row['productName'] . '</h3>';
                            echo '<div class="flex justify-between items-center mb-4">';
                            echo '<div><p class="text-gray-400 text-sm">Current Price</p>';
                            echo '<p class="text-2xl font-bold text-bright-orange">' . formatCurrency($row['productPrice']) . '</p></div>';
                            echo '<div class="text-right"><p class="text-gray-400 text-sm">Min Bid</p>';
                            echo '<p class="text-lg font-semibold">' . formatCurrency($row['minimumBid']) . '</p></div></div>';
                            echo '<a href="products.php?view=' . $row['id'] . '" class="w-full py-3 bg-gradient-to-r from-electric-blue to-bright-orange rounded-xl font-semibold hover:opacity-90 transition-all duration-300 btn-glow block text-center">View Details</a>';
                            echo '</div></div>';
                        }
                    } else {
                        echo '<div class="col-span-3 text-center py-12"><div class="glass-effect rounded-2xl p-8 max-w-md mx-auto">';
                        echo '<i class="fas fa-box-open text-4xl text-gray-400 mb-4"></i>';
                        echo '<h3 class="text-xl font-bold mb-2">No Products</h3>';
                        echo '<p class="text-gray-400">Check back later.</p></div></div>';
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="py-12 bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-electric-blue to-bright-orange flex items-center justify-center">
                            <i class="fas fa-gavel text-white text-sm"></i>
                        </div>
                        <h2 class="text-xl font-bold">Online<span class="text-bright-orange">Bidding</span></h2>
                    </div>
                    <p class="text-gray-400 mb-4">Secure, fast, and fun online auctions.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-bright-orange"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-bright-orange"><i class="fab fa-facebook text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-bright-orange"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-bright-orange"><i class="fab fa-linkedin text-xl"></i></a>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-bright-orange">Home</a></li>
                        <li><a href="products.php" class="text-gray-400 hover:text-bright-orange">Products</a></li>
                        <li><a href="#how-it-works" class="text-gray-400 hover:text-bright-orange">How It Works</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-bright-orange">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-bright-orange">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-bright-orange">Privacy Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Newsletter</h3>
                    <p class="text-gray-400 mb-4">Get updates on new auctions.</p>
                    <div class="flex">
                        <input type="email" placeholder="Your email" class="px-4 py-2 bg-gray-800 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-bright-orange w-full">
                        <button class="px-4 py-2 bg-gradient-to-r from-electric-blue to-bright-orange rounded-r-lg font-medium"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Online Bidding System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
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
                    <p class="text-gray-400 mt-2">Sign in to your account</p>
                </div>
                <form action="login.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-3 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange">
                    </div>
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-bright-orange">
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded bg-gray-800 border-gray-700 text-bright-orange focus:ring-bright-orange">
                            <span class="ml-2 text-sm text-gray-300">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-bright-orange hover:underline">Forgot password?</a>
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-semibold hover:opacity-90 transition-all duration-300 btn-glow">
                        Sign In
                    </button>
                </form>
                <div class="text-center mt-6">
                    <p class="text-gray-400">Don't have an account? 
                        <button onclick="switchToRegister()" class="text-bright-orange hover:underline font-medium">Sign up</button>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
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
                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-semibold text-sm hover:opacity-90 transition-all duration-300 btn-glow">
                        Create Account
                    </button>
                </form>
                <div class="text-center mt-4 text-xs">
                    <p class="text-gray-400">Already have an account? 
                        <button onclick="switchToLogin()" class="text-bright-orange hover:underline font-medium">Sign in</button>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
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
            if (e.target.classList.contains('modal')) closeModal(e.target.id);
        });

        document.addEventListener('DOMContentLoaded', () => {
            // SIMPLE TYPING ANIMATION - WORKING VERSION
            const texts = ["Bidding System", "Procurement", "Deals & Savings"];
            const typingElement = document.getElementById('typingText');
            let textIndex = 0;
            let charIndex = 0;
            let isDeleting = false;
            let typingSpeed = 100;

            function type() {
                const currentText = texts[textIndex];
                
                if (isDeleting) {
                    // Deleting text
                    typingElement.textContent = currentText.substring(0, charIndex - 1);
                    charIndex--;
                    typingSpeed = 50;
                } else {
                    // Typing text
                    typingElement.textContent = currentText.substring(0, charIndex + 1);
                    charIndex++;
                    typingSpeed = 100;
                }

                // Check if finished typing current text
                if (!isDeleting && charIndex === currentText.length) {
                    // Wait before starting to delete
                    typingSpeed = 2000;
                    isDeleting = true;
                } else if (isDeleting && charIndex === 0) {
                    // Finished deleting, move to next text
                    isDeleting = false;
                    textIndex = (textIndex + 1) % texts.length;
                    typingSpeed = 500;
                }

                setTimeout(type, typingSpeed);
            }

            // Start the typing animation after a short delay
            setTimeout(() => {
                type();
            }, 1000);

            // Card animations
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

            document.querySelectorAll('.card-hover').forEach(card => {
                card.style.opacity = 0;
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(card);
            });

            // Mobile menu
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
                    nav.classList.toggle('p-4');
                    nav.classList.toggle('flex-col');
                    nav.classList.toggle('space-y-4');
                });
            }
        });
    </script>
</body>
</html>