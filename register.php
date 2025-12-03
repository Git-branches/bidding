<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = sanitize($_POST['firstName']);
    $lastName = sanitize($_POST['lastName']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $contact = sanitize($_POST['contact']);
    $address = sanitize($_POST['address']);
    $securityWord = sanitize($_POST['securityWord']);
    
    // Check if email already exists
    $checkQuery = "SELECT id FROM users WHERE email = '$email'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        // Default values - FIXED THE BASE64_ENCODE SYNTAX
        $userMoney = base64_encode("100000"); // Starting balance
        $amountRcvble = base64_encode("0");
        $userType = 2; // Client
        $profilePic = "profilepics/default.jpg";
        
        $query = "INSERT INTO users (firstName, lastName, userMoney, amountRcvble, password, email, contact, address, userType, securityWord, profilePic) 
                  VALUES ('$firstName', '$lastName', '$userMoney', '$amountRcvble', '$password', '$email', '$contact', '$address', '$userType', '$securityWord', '$profilePic')";
        
        if ($conn->query($query)) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['user_type'] = $userType;
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;
            
            header("Location: client/dashboard.php");
            exit();
        } else {
            $error = "Registration failed: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Bidding System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Online Bidding System</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="auth-form">
                <h2>Create New Account</h2>
                <?php if(isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="firstName">First Name:</label>
                        <input type="text" id="firstName" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name:</label>
                        <input type="text" id="lastName" name="lastName" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number:</label>
                        <input type="text" id="contact" name="contact" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <textarea id="address" name="address" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="securityWord">Security Word:</label>
                        <input type="text" id="securityWord" name="securityWord" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </main>
</body>
</html>