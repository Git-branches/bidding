<?php
// Script para auto-create ng mga needed directories
$directories = [
    'uploads/products',
    'profilepics',
    'admin',
    'client'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "Created directory: $dir<br>";
    } else {
        echo "Directory already exists: $dir<br>";
    }
}

echo "All directories created successfully!";
?>