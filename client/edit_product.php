<?php
include '../config.php';

if (!isLoggedIn() || !isClient()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'] ?? 0;

// Get product details and verify ownership
$product_query = "SELECT * FROM products WHERE id = $product_id AND productOwner = $user_id";
$product_result = $conn->query($product_query);

if ($product_result->num_rows == 0) {
    header("Location: my_products.php");
    exit();
}

$product = $product_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = sanitize($_POST['productName']);
    $category = sanitize($_POST['category']);
    $itemDesc = sanitize($_POST['itemDesc']);
    $minimumBid = sanitize($_POST['minimumBid']);
    $productPrice = sanitize($_POST['productPrice']);
    $productEnd = sanitize($_POST['productEnd']);
    
    // Handle image upload
    $productImgLoc = $product['productImgLoc'];
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
        $target_dir = "../uploads/products/";
        $imageFileType = strtolower(pathinfo($_FILES["productImage"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $target_file)) {
            $productImgLoc = 'uploads/products/' . $new_filename;
        }
    }
    
    $query = "UPDATE products SET 
              productName = '$productName',
              category = '$category',
              itemDesc = '$itemDesc',
              minimumBid = '$minimumBid',
              productPrice = '$productPrice',
              productEnd = '$productEnd',
              productImgLoc = '$productImgLoc'
              WHERE id = $product_id AND productOwner = $user_id";
    
    if ($conn->query($query)) {
        $success = "Product updated successfully!";
        // Refresh product data
        $product_result = $conn->query($product_query);
        $product = $product_result->fetch_assoc();
    } else {
        $error = "Failed to update product: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit My Product - Online Bidding System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Online Bidding System</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php">Browse Products</a></li>
                    <li><a href="my_bids.php">My Bids</a></li>
                    <li><a href="add_product.php">Sell Product</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="auth-form">
                <h2>Edit My Product</h2>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="productName">Product Name:</label>
                        <input type="text" id="productName" name="productName" value="<?php echo $product['productName']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php
                            $categories = $conn->query("SELECT * FROM categories");
                            while($cat = $categories->fetch_assoc()) {
                                $selected = ($cat['title'] == $product['category']) ? 'selected' : '';
                                echo '<option value="' . $cat['title'] . '" ' . $selected . '>' . $cat['title'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="itemDesc">Product Description:</label>
                        <textarea id="itemDesc" name="itemDesc" required><?php echo $product['itemDesc']; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="minimumBid">Minimum Bid:</label>
                        <input type="number" id="minimumBid" name="minimumBid" value="<?php echo $product['minimumBid']; ?>" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="productPrice">Current Price:</label>
                        <input type="number" id="productPrice" name="productPrice" value="<?php echo $product['productPrice']; ?>" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="productEnd">Bidding End Date & Time:</label>
                        <input type="datetime-local" id="productEnd" name="productEnd" value="<?php echo date('Y-m-d\TH:i', strtotime($product['productEnd'])); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="productImage">Product Image:</label>
                        <input type="file" id="productImage" name="productImage" accept="image/*">
                        <?php if($product['productImgLoc']): ?>
                            <div class="current-image">
                                <p>Current Image:</p>
                                <img src="../<?php echo $product['productImgLoc']; ?>" alt="Current product image" width="100">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="my_products.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Online Bidding System</p>
        </div>
    </footer>
</body>
</html>