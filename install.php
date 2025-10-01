<?php
session_start();

// Database connection without selecting database first
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS adept_play");
    $pdo->exec("USE adept_play");
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            wallet_balance DECIMAL(10,2) DEFAULT 0.00,
            upi_id VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create admin table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create tournaments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tournaments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            game_name VARCHAR(100) NOT NULL,
            entry_fee DECIMAL(10,2) NOT NULL,
            prize_pool DECIMAL(10,2) NOT NULL,
            match_time DATETIME NOT NULL,
            room_id VARCHAR(100) NULL,
            room_password VARCHAR(100) NULL,
            commission_percentage DECIMAL(5,2) DEFAULT 20.00,
            status ENUM('Upcoming', 'Live', 'Completed') DEFAULT 'Upcoming',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create participants table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS participants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            tournament_id INT NOT NULL,
            joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
            UNIQUE KEY unique_participation (user_id, tournament_id)
        )
    ");
    
    // Create transactions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            type ENUM('credit', 'debit') NOT NULL,
            description TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create deposits table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS deposits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            transaction_id VARCHAR(255) NOT NULL,
            status ENUM('Pending', 'Completed', 'Rejected') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create withdrawals table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS withdrawals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            status ENUM('Pending', 'Completed', 'Rejected') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create settings table for UPI details
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            upi_id VARCHAR(255) NULL,
            qr_code VARCHAR(255) NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Insert default admin
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO admin (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $hashedPassword]);
    
    // Insert default settings
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (id) VALUES (1)");
    $stmt->execute();
    
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Installation Complete - Adept Play</title>
        <script src='https://cdn.tailwindcss.com'></script>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    </head>
    <body class='bg-gray-900 text-white min-h-screen flex items-center justify-center'>
        <div class='bg-gray-800 p-8 rounded-lg shadow-lg max-w-md w-full'>
            <div class='text-center mb-6'>
                <i class='fas fa-check-circle text-green-400 text-5xl mb-4'></i>
                <h1 class='text-2xl font-bold'>Installation Complete!</h1>
            </div>
            <div class='space-y-4'>
                <div class='bg-gray-700 p-4 rounded'>
                    <h3 class='font-semibold mb-2'>Default Admin Credentials:</h3>
                    <p><strong>Username:</strong> admin</p>
                    <p><strong>Password:</strong> admin123</p>
                </div>
                <p class='text-yellow-400 text-sm'>Please change the default password after first login.</p>
                <a href='login.php' class='block w-full bg-green-600 text-white py-3 rounded text-center font-semibold hover:bg-green-700'>
                    Go to Login
                </a>
            </div>
        </div>
    </body>
    </html>
    ";
    
} catch(PDOException $e) {
    die("
    <div class='bg-red-600 text-white p-8 rounded-lg shadow-lg max-w-md mx-auto mt-8'>
        <h1 class='text-2xl font-bold mb-4'>Installation Failed</h1>
        <p>Error: " . $e->getMessage() . "</p>
        <p class='mt-4'>Please check your database credentials and try again.</p>
    </div>
    ");
}
?>
