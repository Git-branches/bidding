<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $securityWord = sanitize($_POST['securityWord']);
    
    $query = "SELECT * FROM users WHERE email = '$email' AND securityWord = '$securityWord'";
    $result = $conn->query($query);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $success = "Please contact administrator to reset your password. Your security word is correct.";
    } else {
        $error = "Invalid email or security word!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Online Bidding System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Online Bidding System</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="auth-form">
                <h2>Forgot Password</h2>
                <?php if(isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="securityWord">Security Word:</label>
                        <input type="text" id="securityWord" name="securityWord" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Verify Identity</button>
                </form>
                <p>Remember your password? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </main>
</body>
</html>