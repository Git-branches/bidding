<?php
// PHP logic remains untouched, as requested.
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    
    // WARNING: Using simple concatenation in the query below leads to SQL Injection vulnerability.
    // In a real application, prepared statements are mandatory.
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($query);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Simple password verification (in real app, use password_verify)
        if ($password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['userType'];
            $_SESSION['first_name'] = $user['firstName'];
            $_SESSION['last_name'] = $user['lastName'];
            $_SESSION['email'] = $user['email'];
            
            redirectBasedOnUserType();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Bidding System</title>
    <!-- Using a professional font stack -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- All styling is embedded here for the professional look -->
    <style>
        :root {
            --color-primary: #00796B; /* Dark Teal - Professional & Trustworthy */
            --color-primary-dark: #005A51;
            --color-accent: #FFC107; /* Amber/Gold */
            --color-background: #f4f7f9;
            --color-card-bg: #ffffff;
            --color-text-dark: #212121;
            --color-text-light: #4c4c4c;
            --color-error: #D32F2F;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-background);
            color: var(--color-text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* --- Header Styling --- */
        header {
            background-color: var(--color-primary);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 1.75rem;
            font-weight: 700;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        nav a:hover {
            background-color: var(--color-primary-dark);
        }

        /* --- Main Content & Form Styling --- */
        main {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 0;
        }

        .auth-form {
            background-color: var(--color-card-bg);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 420px;
            width: 100%;
            border-top: 5px solid var(--color-accent); /* Accent border for importance */
        }

        .auth-form h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--color-primary-dark);
            text-align: center;
        }
        
        .auth-form p:last-child {
            margin-top: 1.5rem;
            text-align: center;
            color: var(--color-text-light);
        }

        .auth-form p a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .auth-form p a:hover {
            text-decoration: underline;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-text-light);
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus {
            border-color: var(--color-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 121, 107, 0.2);
        }

        .btn-primary {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: var(--color-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }

        .btn-primary:hover {
            background-color: var(--color-primary-dark);
        }

        .btn-primary:active {
            transform: scale(0.99);
        }

        /* --- Alert Styling --- */
        .alert-error {
            background-color: #ffebee; /* Light red background */
            color: var(--color-error);
            border: 1px solid var(--color-error);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Online Bidding System</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="auth-form">
                <h2>Sign In to Bid</h2>
                <?php if(isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login to Your Account</button>
                </form>
                <p>Don't have an account? <a href="register.php">Register now</a></p>
            </div>
        </div>
    </main>
    <!-- Footer could be added here for completeness, but keeping it minimal as requested. -->
</body>
</html>