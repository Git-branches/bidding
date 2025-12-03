<?php
include '../config.php';

if (!isLoggedIn() || !isClient()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// === GET USER DATA - USE profilePic FIELD INSTEAD OF avatar ===
$stmt = $conn->prepare("SELECT id, firstName, lastName, email, contact, address, securityWord, userMoney, 
                              COALESCE(profilePic, '../assets/img/default-avatar.png') AS profilePic,
                              COALESCE(date_created, created_at, NOW()) AS reg_date
                       FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$success = $error = '';

// === GET ALL AVATARS FROM PROFILEPICS FOLDER ===
$avatar_dir = "../profilepics/";
$available_avatars = [];
if (is_dir($avatar_dir)) {
    $files = scandir($avatar_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $file_path = $avatar_dir . $file;
            // Check if it's an image file
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed_extensions) && is_file($file_path)) {
                $available_avatars[] = "profilepics/" . $file;
            }
        }
    }
}

// === HANDLE FORM ===
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = sanitize($_POST['firstName']);
    $lastName = sanitize($_POST['lastName']);
    $email = sanitize($_POST['email']);
    $contact = sanitize($_POST['contact']);
    $address = sanitize($_POST['address']);
    $securityWord = sanitize($_POST['securityWord']);
    $selected_avatar = sanitize($_POST['selected_avatar'] ?? $user['profilePic']);

    // === UPLOAD NEW AVATAR TO PROFILEPICS FOLDER ===
    if (isset($_FILES['new_avatar']) && $_FILES['new_avatar']['error'] == 0) {
        $target_dir = "../profilepics/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $file_tmp = $_FILES['new_avatar']['tmp_name'];
        $file_name = $_FILES['new_avatar']['name'];
        $file_size = $_FILES['new_avatar']['size'];

        // Validate it's a real image
        $check = @getimagesize($file_tmp);
        if ($check === false) {
            $error = "File is not a valid image.";
        } elseif ($file_size > 5 * 1024 * 1024) { // 5MB max
            $error = "Image too large (max 5MB).";
        } else {
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_name = "user_{$user_id}_" . time() . "_" . bin2hex(random_bytes(4)) . ".$ext";
            $target = $target_dir . $new_name;

            if (move_uploaded_file($file_tmp, $target)) {
                $selected_avatar = "profilepics/" . $new_name;

                // Optional: delete old avatar if it's in profilepics folder
                $old = "../" . $user['profilePic'];
                if (strpos($user['profilePic'], 'profilepics/') !== false && file_exists($old)) {
                    @unlink($old);
                }
            } else {
                $error = "Upload failed. Check folder permissions.";
            }
        }
    }

    // === SAVE TO DB - UPDATE profilePic FIELD INSTEAD OF avatar ===
    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE users SET 
            firstName = ?, lastName = ?, email = ?, contact = ?, address = ?, securityWord = ?, profilePic = ?
            WHERE id = ?");
        $stmt->bind_param("sssssssi", $firstName, $lastName, $email, $contact, $address, $securityWord, $selected_avatar, $user_id);

        if ($stmt->execute()) {
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;
            $_SESSION['profilePic'] = $selected_avatar;
            $success = "Profile updated!";
            $user['profilePic'] = $selected_avatar;
        } else {
            $error = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$reg_date = date('M Y', strtotime($user['reg_date'] ?? 'now'));

// Helper function for user money
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Online Bidding System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { 'deep-blue': '#0f172a', 'electric-blue': '#3b82f6', 'bright-orange': '#f97316', 'glass': 'rgba(255,255,255,0.1)' }, fontFamily: { 'inter': ['Inter', 'sans-serif'] } } } }
    </script>
    <style type="text/tailwindcss">
        @layer base { html { scroll-behavior: smooth; } body { font-family: 'Inter', sans-serif; } }
        @layer utilities {
            .glass-effect { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
            .text-gradient { background: linear-gradient(90deg, #3b82f6, #f97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
            .card-hover { transition: all 0.3s ease; }
            .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
            .btn-glow:hover { box-shadow: 0 0 15px rgba(249,115,22,0.5); }
            .input-focus:focus { @apply ring-2 ring-bright-orange outline-none; }
            .avatar-preview { @apply w-32 h-32 rounded-full object-cover border-4 border-white/20 shadow-lg; }
            .avatar-option { @apply w-20 h-20 rounded-full object-cover border-2 border-white/30 cursor-pointer transition-all hover:scale-110 hover:border-bright-orange; }
            .avatar-option.selected { @apply border-4 border-bright-orange scale-110; }
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
                <h1 class="text-2xl font-bold">Online <span class="text-bright-orange">Bidding</span></h1>
            </div>
            <nav class="hidden md:flex space-x-8">
                <a href="dashboard.php" class="font-medium hover:text-bright-orange transition-colors">Dashboard</a>
                <a href="products.php" class="font-medium hover:text-bright-orange transition-colors">Browse</a>
                <a href="my_bids.php" class="font-medium hover:text-bright-orange transition-colors">My Bids</a>
                <a href="add_product.php" class="font-medium hover:text-bright-orange transition-colors">Sell</a>
                <a href="profile.php" class="font-medium text-bright-orange">Profile</a>
                <a href="../logout.php" class="font-medium hover:text-bright-orange transition-colors">Logout</a>
            </nav>
            <button class="md:hidden text-xl" id="mobileMenuButton"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <!-- HERO -->
    <section class="py-12 relative overflow-hidden">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-3">My <span class="text-gradient">Profile</span></h2>
            <p class="text-lg text-gray-300">Upload or pick any image!</p>
        </div>
    </section>

    <!-- HORIZONTAL CARD -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="glass-effect rounded-2xl p-6 md:p-8 card-hover max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <!-- LEFT: Avatar + Balance -->
                    <div class="md:col-span-1 flex flex-col items-center md:items-start space-y-6">
                        <div class="text-center md:text-left">
                            <img id="avatarPreview" 
                                 src="../<?php echo htmlspecialchars($user['profilePic']); ?>" 
                                 alt="Profile Picture" 
                                 class="avatar-preview mx-auto md:mx-0">
                            
                            <!-- Upload Button -->
                            <label class="block mt-3 text-sm text-bright-orange cursor-pointer hover:underline">
                                <input type="file" name="new_avatar" accept="image/*" class="hidden" onchange="previewUpload(this)">
                            </label>

                            <!-- Gallery Button -->
                            <button type="button" onclick="openAvatarModal()" class="block mt-2 text-sm text-electric-blue hover:underline">
                                <i class="fas fa-images mr-1"></i> Pick from Gallery
                            </button>

                            <p class="text-xs text-gray-400 mt-1">Any format • Max 5MB</p>
                        </div>

                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-5 w-full text-center md:text-left border border-white/10">
                            <h3 class="text-lg font-bold mb-1">Account Balance</h3>
                            <p class="text-3xl font-bold text-bright-orange"><?php echo formatCurrency(getUserMoney($user['userMoney'])); ?></p>
                            <p class="text-xs text-gray-400 mt-1">Ready for bidding</p>
                        </div>

                        <p class="text-sm text-gray-400 text-center md:text-left">
                            Member since <strong><?php echo $reg_date; ?></strong>
                        </p>
                    </div>

                    <!-- RIGHT: Form -->
                    <div class="md:col-span-2 space-y-5">
                        <?php if ($success): ?>
                            <div class="p-3 bg-green-500/20 border border-green-500/50 text-green-300 rounded-lg text-sm flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="p-3 bg-red-500/20 border border-red-500/50 text-red-300 rounded-lg text-sm flex items-center gap-2">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="selected_avatar" id="selectedAvatarInput" value="<?php echo htmlspecialchars($user['profilePic']); ?>">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-300 mb-1">First Name</label>
                                    <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required class="w-full px-3 py-2 bg-gray-800 rounded-lg input-focus text-white text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-300 mb-1">Last Name</label>
                                    <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required class="w-full px-3 py-2 bg-gray-800 rounded-lg input-focus text-white text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-300 mb-1">Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-3 py-2 bg-gray-800 rounded-lg input-focus text-white text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-300 mb-1">Contact Number</label>
                                <input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" required class="w-full px-3 py-2 bg-gray-800 rounded-lg input-focus text-white text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-300 mb-1">Delivery Address</label>
                                <textarea name="address" rows="2" required class="w-full px-3 py-2 bg-gray-800 rounded-lg input-focus text-white text-sm resize-none"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-300 mb-1">Security Word</label>
                                <input type="text" name="securityWord" value="<?php echo htmlspecialchars($user['securityWord']); ?>" required class="w-full px-3 py-2 bg-gray-800 rounded-lg input-focus text-white text-sm">
                            </div>

                            <button type="submit" class="w-full py-3 bg-gradient-to-r from-electric-blue to-bright-orange rounded-lg font-bold text-sm hover:opacity-90 transition-all btn-glow flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- AVATAR GALLERY MODAL -->
    <div id="avatarModal" class="modal">
        <div class="modal-content w-full max-w-4xl mx-4">
            <div class="glass-effect rounded-2xl p-6 relative">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Pick from Gallery</h3>
                    <button onclick="closeAvatarModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-4 max-h-96 overflow-y-auto p-2">
                    <?php foreach ($available_avatars as $avatar): ?>
                        <img src="../<?php echo htmlspecialchars($avatar); ?>" 
                             alt="Avatar" 
                             class="avatar-option <?php echo $avatar === $user['profilePic'] ? 'selected' : ''; ?>" 
                             onclick="selectAvatar('<?php echo htmlspecialchars($avatar); ?>', this)">
                    <?php endforeach; ?>
                    <?php if (empty($available_avatars)): ?>
                        <p class="col-span-full text-center text-gray-400 py-8">No images available in profilepics folder.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="py-10 bg-gray-900 text-center text-gray-400 text-sm">
        <div class="container mx-auto px-4">
            <p>© 2025 Online Bidding System. All rights reserved.</p>
        </div>
    </footer>

    <!-- JS -->
    <script>
        // Modal functions
        function openAvatarModal() {
            document.getElementById('avatarModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeAvatarModal() {
            document.getElementById('avatarModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function selectAvatar(path, el) {
            document.getElementById('avatarPreview').src = '../' + path;
            document.getElementById('selectedAvatarInput').value = path;
            document.querySelectorAll('.avatar-option').forEach(e => e.classList.remove('selected'));
            el.classList.add('selected');
            closeAvatarModal();
        }

        function previewUpload(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('avatarPreview').src = e.target.result;
                    document.getElementById('selectedAvatarInput').value = ''; // Will be saved on submit
                };
                reader.readAsDataURL(file);
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                closeAvatarModal();
            }
        });

        // Mobile Menu
        document.getElementById('mobileMenuButton')?.addEventListener('click', () => {
            const nav = document.querySelector('nav.hidden.md\\:flex');
            nav.classList.toggle('hidden');
            nav.classList.toggle('absolute');
            nav.classList.toggle('top-16');
            nav.classList.toggle('left-0');
            nav.classList.toggle('right-0');
            nav.classList.toggle('bg-deep-blue');
            nav.classList.toggle('p-6');
            nav.classList.toggle('flex-col');
            nav.classList.toggle('space-y-4');
            nav.classList.toggle('space-x-0');
        });
    </script>
</body>
</html>